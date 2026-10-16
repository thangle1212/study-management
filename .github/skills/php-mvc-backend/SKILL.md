---
name: php-mvc-backend
description: "Use when designing, analyzing, refactoring, or implementing PHP MVC backends: modular controllers, thin controllers, services, routing, database access, authentication, authorization, validation, security, and incremental delivery."
argument-hint: "Describe the PHP MVC feature, architecture review, or refactoring target."
---

# PHP MVC Backend Architecture

## Purpose

Design and evolve maintainable PHP MVC backends using domain-oriented modules, explicit responsibilities, stable routes, secure data access, and verifiable incremental changes. Use the current codebase as the source of truth; do not impose framework-specific conventions that the project does not support.

This skill is suitable for frameworkless PHP applications and can be adapted to a framework. It favors evolutionary refactoring over rewrites.

## When to use

- Create a PHP MVC backend or a new domain module.
- Analyze an existing MVC codebase before implementation.
- Split a large controller into clear, domain-oriented controllers.
- Extract business workflows into services.
- Add or review routes, models, validation, authentication, authorization, or database transactions.
- Refactor legacy PHP MVC code while preserving public URLs and behavior.
- Review a backend change for architecture, security, and verification gaps.

## Non-negotiable boundaries

- Inspect the repository before changing it.
- Treat verified source-code behavior, recommendations, and new conventions as separate categories.
- Do not invent framework features, layers, routes, tables, or security guarantees that are not evidenced.
- Do not expose, copy, or commit secrets, credentials, tokens, or personal data.
- Preserve existing routes, status values, session contracts, and user-visible behavior unless the request explicitly changes them.
- Do not rewrite a legacy application merely to make it look more modern.
- Surface errors using the repository's established mechanism; do not silently convert failures into successful-looking results.
- Do not modify unrelated files or pre-existing problems.

## Target architecture

Use the following as a target, adapting names and layers to the project:

```text
app/
├── Controllers/
│   ├── Customer/
│   │   ├── DashboardController.php
│   │   ├── ResourceController.php
│   │   ├── BookingController.php
│   │   └── AccountController.php
│   ├── Admin/
│   └── Api/
├── Services/
├── Models/              # or Repositories/, according to the project
├── Requests/            # optional input validation/form objects
├── Policies/            # optional authorization rules
├── Middleware/
├── Routes/
├── Helpers/
└── Views/
```

The exact names are not mandatory. A controller must represent a coherent workflow or domain responsibility, not merely a slice of line numbers.

## Controller decomposition rule

Never keep unrelated customer, administrator, booking, payment, export, and account workflows in one role-based controller solely because they share an actor. Split by cohesive capability:

- `DashboardController`: dashboard composition and navigation data.
- `ResourceController`: searching, listing, and displaying a domain resource.
- `BookingController`: starting and managing a booking workflow.
- `CartController`: cart-specific read and mutation actions.
- `CheckoutController`: checkout and payment workflow.
- `TicketController`: issued-ticket retrieval, changes, cancellation, and export.
- `AccountController`: profile, credentials, and account lifecycle.

Use domain-appropriate names. For example, `ProductController` is preferable to `ProductSearchController` when the module will also own details and selection. Do not create a controller for every method when the methods form one cohesive workflow.

### Thin-controller responsibilities

Controllers may:

1. Receive the request and identify the action.
2. Apply authentication and authorization guards.
3. Parse and perform basic request validation.
4. Call a service/model use case.
5. Select a view, JSON response, or redirect.
6. Map expected failures to the project's response format.

Controllers should not own long multi-model workflows, transaction orchestration, inventory/payment rules, export algorithms, or reusable validation.

### Service responsibilities

Create a service when a use case coordinates multiple models, changes multiple records, requires a transaction, has several workflow steps, or is reused by HTML and API entrypoints. A service should own the business operation and transaction boundary where appropriate. Keep response rendering and HTTP redirects outside the service.

## Required workflow

### 1. Reconnaissance

Before coding, inspect:

- Entry points and web/API dispatch.
- Route normalization and base-path handling.
- Controllers and their responsibilities.
- Models/repositories and database connection ownership.
- Views/templates and output escaping.
- Authentication, session, authorization, and token handling.
- Validation and error conventions.
- Configuration, migrations/schema, helpers, tests, and documentation.

Record evidence with file paths and line locations when available. Identify existing conventions before introducing new ones.

### 2. Responsibility map

Group current actions by capability, not by file order:

```text
Dashboard      -> DashboardController
Search/details -> ResourceController
Create/update  -> ResourceController or a focused workflow controller
Multi-step use case -> WorkflowController + WorkflowService
Account         -> AccountController
```

For each group, record routes, views, models, session keys, authorization rules, side effects, and transaction requirements.

### 3. Change plan

Before editing, define:

- Files/classes to add or change.
- Old-to-new action and route mapping.
- Service extraction boundaries.
- Compatibility constraints.
- Data and transaction invariants.
- Validation and authorization checks.
- Targeted verification commands/tests.

If the requested scope is ambiguous or a design choice changes public behavior, ask before implementing.

### 4. Incremental implementation

Use this order unless repository conventions require another:

1. Add the new controller/service structure.
2. Move or extract one cohesive workflow with minimal behavior change.
3. Update the router and dependency loading.
4. Search all route, form, view, redirect, and controller references.
5. Remove obsolete code only after references and tests pass.
6. Repeat for the next independent workflow.

For legacy applications without an autoloader or dependency injection, preserve `require_once` and construction conventions first. Introduce infrastructure separately rather than mixing it with a feature refactor.

### 5. Verification

Run the smallest relevant checks, then broaden when needed:

- PHP syntax/lint for changed files.
- Route and class/reference search.
- Existing unit/integration tests.
- Authorization and ownership checks.
- Transaction rollback and invariant checks.
- Form action/API contract checks.
- Diff review for unrelated changes and secret leakage.

Report what was actually run and distinguish untested assumptions.

## Layer rules

### Routing

Keep public paths stable during refactoring. Route dispatch should map a path and HTTP method to one focused action. Reject unsupported methods explicitly. Keep web and API response conventions separate. If a hand-written dispatcher is used, keep matching order deterministic and test overlapping patterns.

### Models and database access

Models/repositories own persistence operations, not redirects or view rendering. Prefer parameterized queries for all variable input. Centralize connection setup and configuration. Keep transaction boundaries around complete business operations; do not split an invariant across unrelated controller calls. Use atomic conditional updates or row locks when concurrent changes can oversell a resource.

### Authentication and authorization

Authenticate at the entry point or middleware boundary, then authorize the resource operation. Enforce ownership in the query/use case, not only in the view. Regenerate session identifiers after login. Store password hashes, verify with the platform password API, and keep token secrets outside source control. Use explicit token expiry/revocation semantics when logout must invalidate API credentials.

### Validation and errors

Validate presence, type, format, range, ownership, and domain invariants at the boundary and again in the service when required. Use a consistent error shape for HTML and API responses. Log unexpected failures server-side without exposing stack traces, SQL, secrets, or internal paths to clients. Do not catch broad exceptions unless the handler can recover, rollback, and report the failure correctly.

### Views and output

Pass prepared view data from controllers/services. Escape untrusted values in HTML text, attributes, URLs, JavaScript, and exported formats using context-appropriate encoders. Keep business rules and database calls out of templates.

## References

- Read [architecture.md](./references/architecture.md) for the source-derived architecture model and generalized target structure.
- Read [refactoring-workflow.md](./references/refactoring-workflow.md) when splitting controllers or extracting services.
- Read [security-rules.md](./references/security-rules.md) when implementing or reviewing authentication, authorization, input/output handling, sessions, tokens, or state-changing actions.

## Completion checklist

- [ ] Existing architecture and conventions were inspected.
- [ ] Controller responsibilities are cohesive and not grouped only by user role.
- [ ] Complex multi-model workflows have an explicit service/transaction boundary.
- [ ] Routes, views, forms, redirects, and API contracts remain mapped.
- [ ] Authentication, authorization, and ownership are enforced server-side.
- [ ] Inputs are validated and outputs are escaped in context.
- [ ] Database operations are parameterized and concurrency assumptions are explicit.
- [ ] Failures are visible, recoverable where appropriate, and not silently ignored.
- [ ] Targeted validation was run and its result was reported.
- [ ] No secrets, unrelated changes, or unverified claims were added.
