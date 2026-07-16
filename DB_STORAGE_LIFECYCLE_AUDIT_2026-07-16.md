# TickMizane Schema and Data Lifecycle Audit

Date: 2026-07-16
Environment analyzed: local PostgreSQL 16 container (techmizane)
Scope: schema, high-growth tables, index/storage overhead, lifecycle/archiving, Redis usage, reporting read patterns

## 1. Baseline Snapshot (Current State)

Live row counts (high-growth set):
- historiques: 3,666
- vente_details: 2,038
- ventes: 683
- commande_details: 20
- commandes: 11
- paiements: 10
- stock_movements: 9

Table and index footprint (high-growth set):
- historiques: table 528 kB + indexes 488 kB
- vente_details: table 176 kB + indexes 192 kB
- ventes: table 64 kB + indexes 200 kB
- paiements: table 8 kB + indexes 144 kB
- commandes: table 8 kB + indexes 112 kB
- stock_movements: table 8 kB + indexes 96 kB

Key observation:
- Even at low volume, indexes dominate storage on transactional tables (expected during early growth but indicates index over-provisioning risk at scale).

## 2. Ranked Actions by Storage Impact x Effort

Scoring model:
- Storage impact: 1 to 5
- Effort/risk: 1 to 5
- Priority: Impact x inverse effort (high impact + low effort first)

| Rank | Action | Impact | Effort | Priority | Type |
|---|---|---:|---:|---:|---|
| 1 | Drop clear duplicate/redundant indexes after verification window | 5 | 2 | 10 | Migration |
| 2 | Add archive lifecycle for historiques (hot/cold split) | 5 | 2 | 10 | Job + migration |
| 3 | Stop storing always-empty historique tracking columns or start populating intentionally | 4 | 2 | 8 | Code + migration |
| 4 | Move session/cache growth fully out of PostgreSQL | 4 | 2 | 8 | Config |
| 5 | Introduce financial archive schema for closed ventes/commandes/paiements | 5 | 3 | 7 | Migration + jobs |
| 6 | Add stock movement summarization and monthly snapshots | 4 | 3 | 6 | Job + schema |
| 7 | Tighten oversized varchars and enum-like strings | 3 | 3 | 5 | Migration |
| 8 | Introduce partitioning for very large append tables (phase 2) | 5 | 4 | 5 | Structural migration |
| 9 | Pre-aggregated reporting tables to reduce historical full scans | 3 | 2 | 6 | Job + schema |

## 3. Detailed Findings and Fixes

## 3.1 Duplicate or Redundant Indexes (High Write + Storage Waste)

Evidence from live index definitions and usage:
- Many indexes show idx_scan=0 in current stats, including known overlap patterns.
- Redundant pairs where one composite already covers leading columns in PostgreSQL.

Candidate drop list (after monitoring and query-plan verification):
1. commandes_type_status_index
- Table: commandes
- Why redundant: covered by commandes_type_status_created_at_idx
- Current size: 16 kB

2. paiements_method_index
- Table: paiements
- Why redundant: covered by paiements_method_created_at_idx
- Current size: 16 kB

3. paiements_status_index
- Table: paiements
- Why redundant: covered by paiements_status_created_at_idx
- Current size: 16 kB

4. historiques_user_id_index
- Table: historiques
- Why redundant: covered by historiques_user_created_at_idx
- Current size: 48 kB

5. historiques_table_name_index
- Table: historiques
- Why redundant: covered by historiques_table_record_idx
- Current size: 56 kB

6. jobs_queue_index
- Table: jobs
- Why redundant: covered by jobs_queue_reserved_available_idx
- Current size: 16 kB

7. notifications_notifiable_type_notifiable_id_index
- Table: notifications
- Why redundant: covered by notifications_notifiable_read_at_idx
- Current size: 8 kB

8. settings_group_index
- Table: settings
- Why redundant: covered by settings_group_key_index
- Current size: 16 kB

9. user_permissions_user_id_module_action_index
- Table: user_permissions
- Why redundant: exact duplicate of existing unique index on same columns
- Current size: 8 kB

10. ventes_user_id_index
- Table: ventes
- Potentially redundant: covered by ventes_user_status_created_at_idx for most patterns
- Current size: 16 kB
- Keep until production query telemetry confirms no user-only index dependency.

Estimated immediate storage reclaimed (safe subset excluding ventes_user_id_index): ~184 kB in current dataset; scales linearly and materially at millions of rows.

Implementation notes:
- Use DROP INDEX CONCURRENTLY in production.
- Keep a rollback migration recreating dropped indexes.
- Validate with pg_stat_statements before final drop on production.

## 3.2 Historique Growth Pattern and Column Waste

Tables/columns:
- historiques.ip_address, historiques.user_agent, historiques.old_values, historiques.new_values, historiques.device_type

Current utilization:
- All five columns are 100% NULL in 3,666 rows.

Root cause:
- Schema added tracking columns, but write path in LogsHistorique currently does not persist these values.

Fix options:
Option A (recommended for storage):
- Drop the always-empty columns now.
- Reintroduce later only if legal/audit requirement demands them.

Option B (if audit needs richer logs):
- Keep columns but write compact diffs only, not full snapshots.
- Never store full unchanged row snapshots by default.

Cost estimate:
- Currently minimal due NULL compression, but wide JSON/text columns become a major TOAST growth vector once populated.

## 3.3 Historique Retention and Archive Policy (Critical)

Problem:
- historiques is already largest table by total size and grows with every business action.

Proposed lifecycle:
- Hot table: keep last 6 to 12 months in public.historiques
- Warm archive: move older rows to archive.historiques_YYYYMM
- Cold storage: optional monthly export (Parquet/CSV) to object storage

Archiving job:
1. Insert-select old rows into archive partition/table.
2. Verify row count and checksum (count + min/max id/date).
3. Delete archived rows from hot table in batches.
4. Run VACUUM ANALYZE on hot table.

Business/legal sign-off:
- Audit policy must define minimum retention for operational logs.
- No destructive delete before approved retention period and validated archive restore test.

## 3.4 Financial Tables: Archive, Do Not Delete

Tables in scope:
- ventes, paiements, commandes, vente_details, commande_details

Constraint respected:
- Do not delete financial records outright.

Closed record definition (proposed):
- ventes: status in (paid, cancelled) and updated_at older than retention threshold
- kitchen commandes: status in (payee, annule) and updated_at older than retention threshold
- paiements: linked to closed ventes/commandes and older than threshold

Proposed policy:
- Hot operational window: 18 months in public schema
- Archive window: move older closed records to archive schema monthly
- Legal retention: preserve archived fiscal records for duration required by Moroccan accounting/tax obligations (business/legal to confirm exact years)

Archive structure:
- archive.ventes
- archive.vente_details
- archive.commandes
- archive.commande_details
- archive.paiements

Integrity rules:
- Archive parent and children in same transaction/batch window.
- Maintain original IDs and created/updated timestamps.
- Keep read access path for historical reports.

## 3.5 Data Type Tightening (Moderate Storage Gain)

Findings:
- Many bounded-domain columns use varchar(255) where much smaller types are enough.

Candidates:
- status, role, method, type, reason fields across users/ventes/commandes/paiements/stock_movements/tables
- module and action in user_permissions
- table_name and action in historiques can be bounded

Fix:
- Convert to constrained varchar(n) or PostgreSQL enum/domain where stable.
- Example: role varchar(20), status varchar(20), method varchar(20), reason varchar(20), module varchar(40), action varchar(40).

Risk:
- Requires migration with value-validation before altering type length.

## 3.6 Derived Data Duplication Review

Observations:
- vente_details.total_line is derivable from quantity * price.
- ventes.total is derivable from details sum.
- commandes.total is derivable from detail lines.

Recommendation:
- Keep current denormalized totals for runtime performance (receipts/reports).
- Add consistency guardrails:
  - periodic reconciliation job
  - optional check constraints where feasible

Do not remove totals in hot path without benchmarking, since this may increase read cost significantly.

## 3.7 Nullable Columns and Sparse Usage in High-Growth Tables

Examples from live data:
- commandes.fournisseur_id currently always NULL in this dataset
- commande_details.notes currently always NULL
- paiements.notes currently always NULL

Recommendation:
- Do not drop immediately based on local dataset only.
- Mark candidates as optional feature fields and review production non-null rates over 60 to 90 days.

## 3.8 Timestamp Proliferation and Soft Deletes

Findings:
- No widespread soft delete use in current schema (good for storage control).
- created_at/updated_at exist on almost all tables.

Recommendation:
- Keep timestamps on financial/audit/operational entities.
- For tiny lookup/static tables, consider removing updated_at where not operationally needed.

## 3.9 Stock Movements: Largest Future Growth Candidate

Problem:
- stock_movements receives one row per inventory event and can outgrow core sales tables.

Plan:
1. Keep immutable granular events for recent period (for traceability).
2. Create monthly stock snapshot table:
- stock_snapshots(product_id, snapshot_date, opening_qty, closing_qty, in_qty, out_qty, adjustments_qty)
3. Archive old stock_movements beyond hot window (for example 18 months) to archive.stock_movements.

Optional optimization:
- Batch low-value repeated system adjustments into periodic aggregate events when business allows.

## 3.10 Session/Cache Growth in PostgreSQL

Current risk:
- Session/cache tables exist and can grow indefinitely if DB-backed in production.

Recommendation:
- Use Redis for session and cache stores.
- Ensure TTL/expiration remains enabled.

Redis check (current environment):
- Keyspace currently empty, indicating no unbounded Redis growth in this local run.

## 3.11 Files, PDFs, Binary Data

Findings:
- No BYTEA/blob columns in schema.
- Product/category images handled through filesystem path columns.
- DomPDF outputs are streamed/downloaded, not persisted as DB blobs.

Note:
- A base64 logo embed exists in print view rendering logic; this is generated at render time and not persisted to DB.

## 3.12 Reporting and Dashboard Read Path

Risk:
- Some reports aggregate over historical transactional tables directly.

Recommendation:
- Introduce pre-aggregated daily summary tables updated by queue job:
- sales_daily_summary
- payments_daily_summary
- stock_value_daily_snapshot

Benefit:
- Keeps read latency stable as raw tables scale into millions of rows.

## 4. Concrete Implementation Plan

Phase 0 (1 week, lowest risk)
1. Enable production telemetry:
- pg_stat_statements
- index usage snapshots
2. Implement duplicate-index drop migration (concurrent, rollback included).
3. Switch session/cache to Redis if still DB-backed in production.

Phase 1 (2 to 3 weeks)
1. Create archive schema and archive tables for historiques and financial entities.
2. Add monthly archive command + queue job with verification checks.
3. Add rehearse/restore script and retention dashboards.

Phase 2 (3 to 5 weeks)
1. Add stock snapshot table and summarization job.
2. Add daily reporting summary tables and migrate dashboards/reports to summary-first reads.
3. Execute varchar tightening migration after production value audit.

Phase 3 (optional, higher complexity)
1. Migrate largest append tables to native date partitioning.
2. Route inserts by month and detach old partitions to archive storage.

## 5. Sign-Off Required Before Execution

Business/legal sign-off required for:
1. Retention durations for fiscal/payment/stock records under Moroccan obligations.
2. Archive access SLAs for finance/audit teams.
3. Any column removals that could affect regulatory audit trails.

Technical sign-off required for:
1. Index removals after production telemetry window.
2. Report rewiring to summary tables.
3. Archive job correctness checks and restore drills.

## 6. Recommended First Change Set

If the team wants immediate safe gains, implement first:
1. Remove duplicate indexes listed in section 3.1 (concurrent migration).
2. Add archive pipeline for historiques only.
3. Add sales/payment daily summary tables and use them in dashboard/report endpoints.

These three changes provide the best storage/performance stability improvement with minimal behavioral risk.
