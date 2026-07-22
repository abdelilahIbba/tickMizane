# QA Backlog Converted to GitHub Issues

Source plan: QA_TEST_ACTION_PLAN_2026-07-16.md
Date: 2026-07-16

How to use:
1. Create one GitHub issue per section below.
2. Copy title + body exactly.
3. Apply suggested labels, priority, and estimate.

---

## Issue 1

Title:
[P0][QA] Stabilize PaymentProcessing redirect assertions with discount params

Labels:
qa, tests, payment, p0, regression

Estimate:
XS (1-2h)

Body:

Context:
Three failing tests in tests/Feature/Payment/PaymentProcessingTest.php assert an outdated redirect URL shape.
Current controller contract includes discount_percent and discount_amount query params, even when zero.

Scope checklist:
- [ ] Update redirect assertions for cash payment path.
- [ ] Update redirect assertions for card payment path.
- [ ] Update redirect assertions for mixed payment path.
- [ ] Preserve existing side-effect assertions (paid status, table release, paiement records).
- [ ] Re-run focused test file and then full suite.

Files:
- tests/Feature/Payment/PaymentProcessingTest.php
- app/Http/Controllers/CashierPosController.php

Acceptance criteria:
- All three failing tests in PaymentProcessingTest pass.
- Assertions reflect current redirect contract including discount params.
- No test coverage reduction.

Definition of done:
- Focused file passes.
- Full suite passes or only known unrelated failures remain.

---

## Issue 2

Title:
[P0][QA] Stabilize waiter to kitchen to cashier workflow merge contract

Labels:
qa, tests, workflow, p0, waiter, kitchen, cashier

Estimate:
S (3-5h)

Body:

Context:
tests/Feature/Workflow/CommandToPaymentWorkflowTest.php assumes second waiter submit creates a new order.
Current behavior merges into active kitchen order for the table.

Scope checklist:
- [ ] Replace new-order-id assumption with explicit merge-behavior assertion.
- [ ] Assert resulting detail count and total after second submit.
- [ ] Keep downstream asserts for kitchen ready, cashier pending aggregation, settlement, and receipt.
- [ ] Validate table state consistency.

Files:
- tests/Feature/Workflow/CommandToPaymentWorkflowTest.php
- app/Http/Controllers/WaiterController.php
- app/Services/OrderService.php

Acceptance criteria:
- Workflow test validates intended merge behavior and passes.
- Existing flow coverage remains intact (no removed checks).

Definition of done:
- Focused workflow test file passes.
- No regression introduced in payment workflow tests.

---

## Issue 3

Title:
[P0][QA] Add supplier order lifecycle side-effect tests

Labels:
qa, tests, supplier, inventory, p0

Estimate:
M (6-10h)

Body:

Context:
Supplier order create/update/receive/cancel flows are business-critical and currently under-covered for stock side effects.

Scope checklist:
- [ ] Add create supplier order test with multi-line details and pending status.
- [ ] Add update supplier order test and assert recalculated totals.
- [ ] Add receive supplier order test and assert per-line stock increases.
- [ ] Add cancel pending supplier order test and assert no stock side effects.
- [ ] Add cancel-after-receive rejection test.

Files:
- tests/Feature/Supplier/SupplierOrderLifecycleTest.php (new)
- app/Http/Controllers/OrderController.php
- app/Http/Controllers/CommandeController.php
- app/Services/OrderService.php

Acceptance criteria:
- Receive and cancel side effects are fully asserted.
- Stock quantities and stock movements are deterministic in tests.

Definition of done:
- New feature test file passes.
- No inventory-related regression in existing checkout tests.

---

## Issue 4

Title:
[P0][QA] Add unit tests for StockService correctness and guardrails

Labels:
qa, tests, stock, inventory, unit, p0

Estimate:
M (6-10h)

Body:

Context:
Stock correctness and valuation are core risk areas; direct StockService coverage is missing.

Scope checklist:
- [ ] Test stock-in increases quantity and records movement.
- [ ] Test stock-out decreases quantity and records movement.
- [ ] Test negative-stock prevention where expected.
- [ ] Test low-stock detection logic.
- [ ] Test stock valuation/statistics numerical correctness.

Files:
- tests/Unit/Services/StockServiceTest.php (new)
- app/Services/StockService.php
- app/Models/StockMovement.php

Acceptance criteria:
- Core stock math and movement recording are covered by deterministic unit tests.
- Failure paths are included, not only happy paths.

Definition of done:
- New unit test file passes.
- Existing stock-related feature tests remain green.

---

## Issue 5

Title:
[P0][QA] Add settlement parity tests for vente vs commande payment paths

Labels:
qa, tests, payment, p0, settlement

Estimate:
L (11-16h)

Body:

Context:
Vente settlement and kitchen commande settlement can diverge over time; parity tests are needed.

Scope checklist:
- [ ] Add tests for kitchen commande settlement path.
- [ ] Add tests for vente settlement path.
- [ ] Assert consistent payment metadata and final statuses across both paths.
- [ ] Add refund/cancel tests if supported by current behavior.
- [ ] Add guard test for already-settled entities (no double payment).

Files:
- tests/Unit/Services/PaymentServiceSettlementTest.php (new)
- app/Services/PaymentService.php
- app/Services/OrderService.php
- app/Http/Controllers/CashierPosController.php
- app/Http/Controllers/VenteController.php

Acceptance criteria:
- Divergence between settlement paths is detected by automated tests.
- Business-critical failure cases are covered.

Definition of done:
- New settlement test suite passes.
- Existing payment tests remain green.

---

## Issue 6

Title:
[P1][QA] Add kitchen status transition matrix tests

Labels:
qa, tests, workflow, kitchen, p1

Estimate:
M (6-10h)

Body:

Context:
Status transition enforcement is a key workflow integrity point and needs explicit matrix testing.

Scope checklist:
- [ ] Test valid progression en_cuisine -> en_preparation -> pret -> servi -> payee.
- [ ] Test invalid/out-of-order transitions are rejected.
- [ ] Test annule behavior at each stage.
- [ ] Assert side effects on table lifecycle and payment readiness.

Files:
- tests/Feature/Workflow/KitchenStatusTransitionTest.php (new)
- app/Http/Controllers/KitchenController.php
- app/Services/OrderService.php
- app/Models/Commande.php

Acceptance criteria:
- Transition rules are explicit, enforced, and covered by tests.
- Failure paths are validated with expected responses.

Definition of done:
- New transition matrix test file passes.

---

## Issue 7

Title:
[P1][QA] Add deny-path middleware tests for role and permission enforcement

Labels:
qa, tests, authz, middleware, p1

Estimate:
M (6-10h)

Body:

Context:
Current tests validate some authorization outcomes but lack direct middleware deny-path coverage.

Scope checklist:
- [ ] Add CheckRole deny-path tests.
- [ ] Add CheckPermission deny-path tests.
- [ ] Add allow-path controls for both middlewares.
- [ ] Add test verifying permission updates are enforced on subsequent requests.

Files:
- tests/Feature/Middleware/CheckRoleMiddlewareTest.php (new)
- tests/Feature/Middleware/CheckPermissionMiddlewareTest.php (new)
- app/Http/Middleware/CheckRole.php
- app/Http/Middleware/CheckPermission.php
- routes/web.php

Acceptance criteria:
- Unauthorized access is explicitly tested and denied as expected.
- Updated permissions affect next request behavior in tests.

Definition of done:
- Both middleware test files pass.

---

## Issue 8

Title:
[P1][QA] Add table lifecycle consistency tests across waiter and cashier actions

Labels:
qa, tests, tables, workflow, p1

Estimate:
M (6-10h)

Body:

Context:
Table occupancy lifecycle is cross-cutting and must remain consistent through order/payment/cancel flows.

Scope checklist:
- [ ] Test occupy -> order -> pay -> release sequence.
- [ ] Test cancel side effects on table state.
- [ ] Test transfer and cashout invariants.
- [ ] Assert no orphan current_vente_id or dangling active order linkage.

Files:
- tests/Feature/Tables/TableLifecycleConsistencyTest.php (new)
- app/Http/Controllers/TableController.php
- app/Http/Controllers/WaiterController.php
- app/Http/Controllers/CashierPosController.php

Acceptance criteria:
- Table lifecycle invariants are enforced by tests.
- Side-effect regressions are detectable.

Definition of done:
- New table lifecycle test file passes.

---

## Issue 9

Title:
[P1][QA] Add concurrency conflict tests for same order and same table operations

Labels:
qa, tests, concurrency, p1, integration

Estimate:
L (11-16h)

Body:

Context:
Multi-user race conditions are high-risk in live POS scenarios.

Scope checklist:
- [ ] Simulate two waiter actions on same table/order window.
- [ ] Simulate two cashier payment attempts on same order.
- [ ] Assert idempotent settlement behavior.
- [ ] Assert no duplicate paiements and correct final table/order state.

Files:
- tests/Integration/Concurrency/OrderPaymentConcurrencyTest.php (new)
- app/Http/Controllers/WaiterController.php
- app/Http/Controllers/CashierPosController.php
- app/Services/OrderService.php

Acceptance criteria:
- Concurrency hazards are reproducibly tested.
- Duplicate settlement is prevented in tested scenarios.

Definition of done:
- New concurrency integration test file passes reliably across reruns.

---

## Issue 10

Title:
[P2][QA] Add receipt and ticket PDF output validation tests

Labels:
qa, tests, reports, pdf, p2

Estimate:
S (3-5h)

Body:

Context:
Existing tests cover route success but not PDF output validity.

Scope checklist:
- [ ] Assert receipt endpoint returns PDF content type and non-empty body.
- [ ] Assert ticket report endpoint returns PDF content type and non-empty body.
- [ ] Assert unauthorized access is denied.

Files:
- tests/Feature/Reports/ReceiptAndTicketPdfTest.php (new)
- app/Http/Controllers/CashierPosController.php

Acceptance criteria:
- PDF endpoints are validated for actual file response behavior.

Definition of done:
- New PDF-focused feature tests pass.

---

## Issue 11

Title:
[P2][QA] Add critical historique logging coverage for business actions

Labels:
qa, tests, audit, historique, p2

Estimate:
S (3-5h)

Body:

Context:
Audit logging exists but critical business actions are not comprehensively covered.

Scope checklist:
- [ ] Add payment completion historique assertion.
- [ ] Add supplier receive historique assertion.
- [ ] Add permission update historique assertion.
- [ ] Add password reset historique assertion.

Files:
- tests/Feature/Audit/HistoriqueCriticalActionsTest.php (new)
- app/Traits/LogsHistorique.php
- app/Models/Historique.php

Acceptance criteria:
- Critical actions create expected historique entries.
- Assertions validate table_name/action linkage, not only row existence.

Definition of done:
- New audit-focused tests pass.

---

## Issue 12

Title:
[P2][QA] Add queue and broadcast realism tests beyond fake-only mode

Labels:
qa, tests, queue, events, p2, integration

Estimate:
M (6-10h)

Body:

Context:
Most event and queue tests use fakes/sync only. Need at least one realistic async-style path.

Scope checklist:
- [ ] Keep existing fake-based checks intact.
- [ ] Add one integration-style async execution test for queueable behavior.
- [ ] Validate event/notification payload structure.

Files:
- tests/Integration/Async/QueueAndBroadcastBehaviorTest.php (new)
- app/Events/NewKitchenOrder.php
- app/Notifications/LowStockNotification.php

Acceptance criteria:
- Queue-dependent behavior has at least one non-fake execution test.
- Event/notification payload integrity is asserted.

Definition of done:
- New async integration tests pass without increasing flakiness.

---

## Issue 13

Title:
[P3][QA] Add Wifi QR settings controller coverage

Labels:
qa, tests, settings, wifi-qr, p3

Estimate:
S (3-5h)

Body:

Context:
Wifi QR admin routes exist and are currently untested.

Scope checklist:
- [ ] Admin can access wifi-qr page.
- [ ] Admin can save valid wifi-qr settings.
- [ ] Validation errors are asserted for invalid payload.
- [ ] Non-admin denial path is covered.

Files:
- tests/Feature/Settings/WifiQrControllerTest.php (new)
- app/Http/Controllers/Settings/WifiQrController.php
- routes/web.php

Acceptance criteria:
- Wifi QR route behavior is covered for allow and deny paths.

Definition of done:
- New Wifi QR feature tests pass.

---

## Issue 14

Title:
[P3][QA] Add documentation visibility and authenticated docs viewer tests

Labels:
qa, tests, documentation, settings, p3

Estimate:
M (6-10h)

Body:

Context:
Documentation management and viewer routes are present and under-tested.

Scope checklist:
- [ ] Admin visibility toggle tests.
- [ ] Authenticated docs viewer access tests respecting visibility.
- [ ] Invalid slug and boundary behavior tests.

Files:
- tests/Feature/Settings/DocumentationVisibilityTest.php (new)
- tests/Feature/Docs/DocumentationViewerTest.php (new)
- app/Http/Controllers/Settings/DocumentationController.php
- app/Http/Controllers/DocumentationController.php
- routes/web.php

Acceptance criteria:
- Documentation admin and viewer flows are covered with positive and negative paths.

Definition of done:
- New documentation tests pass.

---

## Milestone suggestion

Milestone: QA Coverage Hardening - Wave 1 to 4

Recommended ordering:
1. Issue 1
2. Issue 2
3. Issue 3
4. Issue 4
5. Issue 5
6. Issue 6
7. Issue 7
8. Issue 8
9. Issue 9
10. Issue 10
11. Issue 11
12. Issue 12
13. Issue 13
14. Issue 14
