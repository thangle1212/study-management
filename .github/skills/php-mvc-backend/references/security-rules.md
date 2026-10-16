# Security reference

## Baseline rules

- Hash passwords with `password_hash()` and verify with `password_verify()`; never store or compare plaintext passwords.
- Keep database credentials, signing keys, encryption keys, and service tokens in environment/deployment configuration, not source or schema dumps.
- Regenerate the session identifier after authentication and configure secure, HttpOnly, SameSite cookies where deployment permits.
- Enforce authorization and resource ownership on the server for every read and mutation.
- Protect state-changing browser requests with CSRF tokens and use POST/PATCH/DELETE semantics rather than destructive GET links.
- Restrict CORS to known origins when credentials or private data are involved.
- Validate request content type and JSON parsing failures explicitly.
- Use prepared statements or an equivalent parameterized query API.
- Escape output for its context: HTML text, attributes, URLs, JavaScript, CSV, and other export formats.
- Return generic production errors; log detailed exceptions server-side with a correlation identifier.
- Expire and revoke tokens according to the application's logout and session requirements.

## Review method

When reviewing an existing project, label each result:

1. **Verified risk** — direct evidence in source, with file and line references.
2. **Potential risk** — plausible but requiring runtime/configuration confirmation.
3. **Recommendation** — desired future control, not proof of a current defect.

Never claim that an application is secure because a control was not found. Never include secret values in a report.

## State and transaction safety

For resource reservation or other concurrent workflows:

- Recheck availability inside the transaction.
- Prefer an atomic conditional update or row-level lock.
- Make the transaction boundary cover all related state changes.
- Define rollback behavior for each external or compensating action.
- Test duplicate submissions and concurrent requests.

## Scope

This reference provides architecture-level security guidance. It is not a substitute for a dedicated penetration test, dependency audit, deployment review, or threat model when those are required.
