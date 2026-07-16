# Changelog

## Unreleased

### Added
- Added storage-lifecycle audit report at DB_STORAGE_LIFECYCLE_AUDIT_2026-07-16.md.
- Added PostgreSQL-safe redundant index cleanup migration with concurrent drop/recreate paths.
- Added archive schema/table migrations for historiques and financial datasets.
- Added monthly archive command/job/service pipeline for historiques.
- Added monthly archive command/job/service pipeline for financial data (ventes, commandes, details, paiements) with parent-child ordering and integrity checks.
- Added archive verification command and verifier service with overlap/orphan/drift checks and optional monthly parity output.
- Added integration tests for archive commands and verifier behavior under clean and missing-table conditions.
- Added README section with CI-ready archive test commands.
