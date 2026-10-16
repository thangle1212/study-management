# Controller and service refactoring workflow

## Before editing

Create a responsibility matrix:

| Capability | Current action | Route | View/response | Models | Session/data invariants |
|---|---|---|---|---|---|
| Example workflow | `store` | `POST /resources` | redirect/JSON | resource, audit | ownership, atomic write |

Use the actual repository names in the matrix. Preserve the route column unless a route change is requested.

## Splitting a large controller

1. Inventory every public method and every route that calls it.
2. Group methods by cohesive business capability.
3. Identify shared guards, request parsing, response formatting, and helpers.
4. Create focused controllers with the smallest compatible public methods.
5. Update the dispatcher and all direct references.
6. Search views, forms, redirects, links, tests, and documentation for old paths/classes.
7. Extract multi-model workflows into services.
8. Delete obsolete code only after verification.

Do not split only by line count. A short method can still belong in the wrong controller, and a longer cohesive workflow may remain together while its business logic moves to a service.

## Service extraction

Extract a service when the operation:

- Changes multiple records that must remain consistent.
- Requires a transaction or compensating rollback.
- Has an inventory, payment, reservation, or state transition invariant.
- Is called from both HTML and API controllers.
- Is difficult to test because HTTP and persistence concerns are intertwined.

The service contract should state inputs, successful result, expected domain failures, and transaction behavior. Do not return a success-shaped fallback after a failed write.

## Compatibility strategy

- Keep public URLs and HTTP methods stable.
- Preserve session key names while migrating, or implement an explicit compatibility adapter.
- Preserve status/state values and ownership rules.
- Keep view names stable where possible.
- Do not combine a controller split with an unrelated schema rewrite.
- If a compatibility break is necessary, document the migration and test the new contract.

## Verification matrix

After each cohesive extraction, verify:

- The route reaches the new action.
- Unauthenticated users are rejected.
- Users cannot access another user's resource.
- Invalid input returns the expected error format.
- A failure midway through a multi-write operation rolls back or compensates safely.
- The success redirect/view/API response is unchanged unless requested.
- Old class and method references are gone or intentionally retained.
