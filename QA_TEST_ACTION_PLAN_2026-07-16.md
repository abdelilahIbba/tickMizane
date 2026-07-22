# TickMizane QA Action Plan and Test Implementation Backlog

Date: 2026-07-16
Owner: QA/Test Engineering
Status: Proposed backlog for immediate execution

## 1. Purpose

This document converts the latest full-suite run and coverage audit into a strict, prioritized test implementation backlog with file-by-file specs and effort estimates.

Primary goals:
1. Restore deterministic green baseline.
2. Close high-risk business gaps first (payment and stock correctness).
3. Add enforcement and side-effect tests (not only happy paths).
4. Add concurrency and lifecycle protections for multi-role operations.

## 2. Baseline (Current State)

Latest repeated full-suite results:
- Total tests: 196
- Passing: 192
- Failing: 4
- Skipped: 0 observed
- Stable failing set across repeated runs (no flaky behavior observed)
- Runtime baseline: about 68 to 74 seconds

Known failing tests (must be addressed first):
1. Tests/Feature/Payment/PaymentProcessingTest.php
- cashier can process cash payment
- cashier can process card payment
- cashier can process mixed payment
- Cause: redirect URL assertions outdated after discount params were added.

2. Tests/Feature/Workflow/CommandToPaymentWorkflowTest.php
- server kitchen cashier payment flow completes for a table
- Cause: test assumes second order creation, but current waiter flow merges into existing active order.

## 3. Execution Rules (Strict QA)

1. Do not weaken assertions to force green.
2. If behavior changed intentionally, update tests to assert the new contract explicitly.
3. For every new controller-flow test, assert all critical side effects:
- DB state
- related records
- table lifecycle state
- historique logging where required
4. For every middleware/permission test, include deny-path assertions.
5. For every critical feature, include at least one failure-path test.
6. Mark any flaky behavior immediately and quarantine with a ticket if reproducible.

## 4. Priority Model

Priority order: P0 > P1 > P2 > P3
- P0: Direct financial/inventory correctness risk
- P1: Workflow and authorization integrity risk
- P2: Operational and observability risk
- P3: Lower-risk admin and documentation surfaces

Effort scale:
- XS: 1 to 2 hours
- S: 3 to 5 hours
- M: 6 to 10 hours
- L: 11 to 16 hours

## 5. Backlog (File-by-File Test Specs)

## P0-01 Stabilize existing payment redirect assertions

Target test file:
- tests/Feature/Payment/PaymentProcessingTest.php

Related app files:
- app/Http/Controllers/CashierPosController.php

Test specs:
1. Update redirect assertions to include discount_percent and discount_amount when defaulted to 0.
2. Assert redirect query consistency for cash, card, and mixed payment paths.
3. Keep current side-effect assertions for paid status, table release, and paiement creation.

Acceptance criteria:
- All three failing payment tests pass without reducing coverage.

Estimated effort: XS

## P0-02 Stabilize waiter->kitchen->cashier workflow contract

Target test file:
- tests/Feature/Workflow/CommandToPaymentWorkflowTest.php

Related app files:
- app/Http/Controllers/WaiterController.php
- app/Services/OrderService.php

Test specs:
1. Replace new-order assumption with explicit assertion of merge behavior when an active order exists.
2. Assert resulting detail line count and total after second submission.
3. Keep downstream assertions for kitchen ready, cashier pending aggregation, payment, and receipt.

Acceptance criteria:
- Workflow test passes and validates intended merge contract.

Estimated effort: S

## P0-03 Supplier order lifecycle side-effects

New target test file:
- tests/Feature/Supplier/SupplierOrderLifecycleTest.php

Related app files:
- app/Http/Controllers/OrderController.php
- app/Http/Controllers/CommandeController.php
- app/Services/OrderService.php

Test specs:
1. Create supplier order with multiple products and assert pending status plus detail rows.
2. Update supplier order and assert recalculated totals.
3. Receive supplier order and assert stock increase per detail line.
4. Cancel pending supplier order and assert no stock movements are applied.
5. Attempt cancel after receive and assert rejection path.

Acceptance criteria:
- Receive/cancel side effects are deterministic and verified for stock correctness.

Estimated effort: M

## P0-04 Stock service correctness and guardrails

New target test file:
- tests/Unit/Services/StockServiceTest.php

Related app files:
- app/Services/StockService.php
- app/Models/StockMovement.php

Test specs:
1. Stock in increases quantity and writes movement.
2. Stock out decreases quantity and writes movement.
3. Negative stock prevention is enforced where expected.
4. Low-stock threshold detection returns expected products.
5. Stock valuation/statistics output is numerically correct.

Acceptance criteria:
- Core stock math and movement recording are fully covered by unit tests.

Estimated effort: M

## P0-05 Payment service divergence protection (vente vs commande)

New target test file:
- tests/Unit/Services/PaymentServiceSettlementTest.php

Related app files:
- app/Services/PaymentService.php
- app/Services/OrderService.php
- app/Http/Controllers/CashierPosController.php
- app/Http/Controllers/VenteController.php

Test specs:
1. Validate kitchen commande settlement path independently.
2. Validate vente settlement path independently.
3. Assert both paths produce consistent payment metadata and status outcomes.
4. Add refund/cancel scenario tests if supported behavior exists.
5. Assert no double-payment on already settled entities.

Acceptance criteria:
- Divergent regressions between vente and commande paths are caught by tests.

Estimated effort: L

## P1-01 Kitchen status transition validation matrix

New target test file:
- tests/Feature/Workflow/KitchenStatusTransitionTest.php

Related app files:
- app/Http/Controllers/KitchenController.php
- app/Services/OrderService.php
- app/Models/Commande.php

Test specs:
1. Valid progression: en_cuisine -> en_preparation -> pret -> servi -> payee.
2. Invalid transition attempts are rejected with expected response.
3. annule at each stage is tested for allowed/blocked behavior.
4. Side effects on table state are asserted.

Acceptance criteria:
- Transition rules are explicit and enforced by automated tests.

Estimated effort: M

## P1-02 Middleware deny-path enforcement

New target test files:
- tests/Feature/Middleware/CheckRoleMiddlewareTest.php
- tests/Feature/Middleware/CheckPermissionMiddlewareTest.php

Related app files:
- app/Http/Middleware/CheckRole.php
- app/Http/Middleware/CheckPermission.php
- routes/web.php

Test specs:
1. Unauthorized role access returns expected denial behavior.
2. Missing permission access returns expected denial behavior.
3. Allowed role/permission path still succeeds.
4. Verify permission change is enforced on subsequent request.

Acceptance criteria:
- Access control deny paths are directly tested and deterministic.

Estimated effort: M

## P1-03 Table lifecycle consistency under cashier/waiter actions

New target test file:
- tests/Feature/Tables/TableLifecycleConsistencyTest.php

Related app files:
- app/Http/Controllers/TableController.php
- app/Http/Controllers/WaiterController.php
- app/Http/Controllers/CashierPosController.php

Test specs:
1. Occupy -> order -> pay -> release state sequence remains consistent.
2. Cancel flow updates table state correctly.
3. Transfer and cashout actions preserve table/order invariants.
4. No orphan current_vente_id/current order linkage after release.

Acceptance criteria:
- Table state machine remains consistent across common actions.

Estimated effort: M

## P1-04 Concurrency conflict scenarios

New target test file:
- tests/Integration/Concurrency/OrderPaymentConcurrencyTest.php

Related app files:
- app/Http/Controllers/WaiterController.php
- app/Http/Controllers/CashierPosController.php
- app/Services/OrderService.php

Test specs:
1. Two waiter submissions for same table during active order window.
2. Two cashier payment attempts for same order nearly simultaneously.
3. Assert idempotent settlement behavior and no duplicate payment records.
4. Assert final table state correctness.

Acceptance criteria:
- Race-condition-sensitive flows are protected by reproducible tests.

Estimated effort: L

## P2-01 Receipt and ticket PDF validation

New target test file:
- tests/Feature/Reports/ReceiptAndTicketPdfTest.php

Related app files:
- app/Http/Controllers/CashierPosController.php
- app/Http/Controllers/CashierPosController.php (ticket report methods)

Test specs:
1. Receipt endpoint returns PDF content type and non-empty payload.
2. Ticket report PDF endpoint returns PDF content type and non-empty payload.
3. Unauthorized role access to report endpoints is denied.

Acceptance criteria:
- PDF generation is validated beyond status-code-only checks.

Estimated effort: S

## P2-02 Historique coverage for key business actions

New target test file:
- tests/Feature/Audit/HistoriqueCriticalActionsTest.php

Related app files:
- app/Traits/LogsHistorique.php
- app/Models/Historique.php

Test specs:
1. Payment completion writes historique.
2. Supplier order receive writes historique.
3. Permission update writes historique.
4. Password reset writes historique.

Acceptance criteria:
- Critical audit trail actions are guaranteed by tests.

Estimated effort: S

## P2-03 Queue and event behavior realism

New target test file:
- tests/Integration/Async/QueueAndBroadcastBehaviorTest.php

Related app files:
- app/Events/NewKitchenOrder.php
- app/Notifications/LowStockNotification.php
- queue-related command/job files

Test specs:
1. Keep existing fake-based behavior tests.
2. Add at least one integration-style async execution test for queueable behavior (without only relying on Queue::fake).
3. Verify event/notification payload integrity.

Acceptance criteria:
- Queue-dependent behavior has at least one realistic execution path tested.

Estimated effort: M

## P3-01 Wifi QR settings coverage

New target test file:
- tests/Feature/Settings/WifiQrControllerTest.php

Related app files:
- app/Http/Controllers/Settings/WifiQrController.php
- routes/web.php

Test specs:
1. Admin can open wifi-qr settings page.
2. Admin can save valid wifi settings.
3. Validation errors on invalid payload.
4. Non-admin denial path.

Acceptance criteria:
- Wifi QR settings routes are no longer untested.

Estimated effort: S

## P3-02 Documentation visibility and docs viewer coverage

New target test files:
- tests/Feature/Settings/DocumentationVisibilityTest.php
- tests/Feature/Docs/DocumentationViewerTest.php

Related app files:
- app/Http/Controllers/Settings/DocumentationController.php
- app/Http/Controllers/DocumentationController.php
- routes/web.php

Test specs:
1. Admin visibility toggles work and persist.
2. Authenticated docs viewer routes respect visibility.
3. Invalid slug handling and permission boundaries are tested.

Acceptance criteria:
- Documentation admin/viewer flows are covered by feature tests.

Estimated effort: M

## 6. Delivery Plan (Suggested Sequence)

Wave 1 (P0 baseline restore, 1 to 2 days)
1. P0-01
2. P0-02
3. Re-run full suite and lock baseline

Wave 2 (financial/inventory hardening, 2 to 4 days)
1. P0-03
2. P0-04
3. P0-05

Wave 3 (workflow and access integrity, 2 to 4 days)
1. P1-01
2. P1-02
3. P1-03
4. P1-04

Wave 4 (operational confidence and lower-risk surfaces, 2 to 3 days)
1. P2-01
2. P2-02
3. P2-03
4. P3-01
5. P3-02

## 7. QA Gate Criteria per PR

Each PR must satisfy:
1. New tests include happy path + failure path + side-effect assertion.
2. No reduction in existing assertions for touched flows.
3. Full suite green in container runtime.
4. No unresolved flaky behavior introduced.
5. Test runtime impact recorded if over +10% from baseline.

## 8. Definition of Done for This Backlog

Done when:
1. All P0 and P1 items are implemented and merged.
2. Full suite is stable green across 3 consecutive full runs.
3. P2 and P3 items are either implemented or explicitly deferred with approved risk acceptance.
4. QA report updated with final coverage matrix and residual risk log.
