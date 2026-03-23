# AGENTS.md

## Project Overview

This project is an accounting form ingesting a few differents sources of xls and csv file, store them into a sqlite database and allow for an excel export matching the previous format

**Key Technologies:**
- **PHP 8.3+** with Laravel framework
- **SQLite** database with Eloquent ORM

---

## Code Quality Requirements

**Every commit must pass both phpcs and phpstan before being pushed.**

### PHP CodeSniffer (phpcs)

```bash
composer phpcs
# or directly:
vendor/bin/phpcs --standard=phpcs.xml
```

- Coding standard is defined in `phpcs.xml` at the project root.
- Do **not** suppress sniff violations with `// phpcs:ignore` unless absolutely unavoidable — if you do, add a comment explaining why.

### PHPStan

```bash
composer phpstan
# or directly:
vendor/bin/phpstan analyse
```

- Configuration is in `phpstan.neon` at the project root.
- Do **not** add `@phpstan-ignore` annotations unless absolutely unavoidable — if you do, add a comment explaining why.

### Running both together before committing

```bash
composer phpcs && composer phpstan
```

Both commands must exit with code `0`. If either fails, fix all reported issues before committing.

---

## Workflow

1. Make your changes.
2. Run `composer phpcs && composer phpstan` and fix any issues.
3. Run `composer test` to ensure no tests are broken.
4. Commit only when all three pass cleanly.

The GitHub Actions CI pipeline (`.github/workflows/tests.yml`) enforces the same checks on every push and pull request.