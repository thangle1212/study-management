# Architecture reference

## Source-derived observations

The reference project is a small frameworkless PHP MVC application. The following patterns were verified in its source and are used as evidence, not as universal requirements:

- A public front controller normalizes requests and dispatches web routes.
- A separate API dispatcher handles versioned API paths and JSON responses.
- Dependencies are loaded with `require_once`; there is no visible Composer autoloader or dependency-injection container.
- HTML controllers, versioned API controllers, static MySQLi models, PHP views, helpers, configuration, and SQL schema are separated into directories.
- Models use a shared database connection and prepared statements for many variable queries.
- Session-based HTML authentication coexists with bearer-token API authentication.
- Multi-step booking and inventory workflows coordinate several models and transactions.
- Some controllers contain validation, transactions, persistence orchestration, redirects, and rendering in the same method.

These observations support a gradual move toward cohesive controllers and services. They do not prove that every project should use the same loader, database API, or token format.

## Generalized module model

Organize by a stable module boundary:

```text
Controllers/
├── Web/
│   ├── DashboardController.php
│   ├── ResourceController.php
│   └── AccountController.php
├── Admin/
└── Api/V1/
Services/
├── ResourceService.php
└── WorkflowService.php
Models/ or Repositories/
Requests/ and Policies/ (when complexity justifies them)
```

The `Web`, `Admin`, and `Api` names are examples. Use the repository's established naming if it is different.

## Responsibility test

For each controller method, ask:

1. Does it serve one coherent capability?
2. Does it coordinate more than one model or external effect?
3. Does it own a transaction or invariant?
4. Does another entrypoint need the same use case?
5. Does it mix HTTP concerns with reusable business logic?

If answers 2–5 are frequently yes, extract a service. If a controller contains several unrelated capability groups, split the controller before or alongside service extraction.

## Generic example

```php
final class OrderController
{
    public function store(): void
    {
        require_login();

        $input = OrderRequest::fromGlobals();
        $order = $this->orders->create($input, current_user_id());

        redirect('/orders/' . rawurlencode((string) $order['id']));
    }
}
```

The service should contain the operation's business rules and transaction, while the controller remains responsible for HTTP flow. Adapt syntax to the project's actual conventions; do not introduce a container solely to match this example.
