# BankBird — Agent Instructions

This file tells AI coding agents (OpenAI Codex, Claude Code, Gemini, etc.) how to work with this codebase effectively.

## Project overview

BankBird is a personal finance app built on Laravel 11 + Filament v5. It imports ING bank statements (PDF), auto-matches transactions to merchants via pattern rules, and supports optional AI categorisation (Claude / OpenAI, opt-in only). It is designed primarily for local use but also supports multi-user online deployments.

There is no customer-facing frontend — the entire UI is a Filament admin panel at `/admin`.

## Stack & key versions

| Package | Version |
|---|---|
| PHP | 8.4 |
| Laravel | 11.x |
| Filament | 5.x |
| Livewire | 4.x |
| Tailwind CSS | 4.x |
| PHPUnit | 10.x |

Database: SQLite by default (local), MySQL optional (online).

## Environment setup

```bash
composer install
npm install
cp .env.example .env
php artisan key:generate
touch database/database.sqlite   # only needed for SQLite
php artisan migrate --seed
npm run build
php artisan make:filament-user   # create first admin user
```

Run the dev server:
```bash
composer run dev
```

## Project structure

```
app/
  Enums/              AccountType, TransactionType, ImportStatus
  Filament/
    Pages/            ManageSettings, MonthlyReport, CategoryDrilldown, ...
    Resources/        AccountResource, TransactionResource, MerchantResource, ImportResource, ...
  Models/             Account, Transaction, Merchant, Category, Import, AppSetting, User
  Services/
    PdfImportService          ING PDF parser + import orchestration
    MerchantPatternService    Pattern matching + retroactive sync
    AiCategorizationService   Optional Claude / OpenAI categorisation
    MerchantMapperService     Description normalisation
database/
  migrations/
  seeders/            CategorySeeder (default categories)
```

## Coding conventions

- Follow existing patterns in sibling files before writing anything new.
- PHP 8.4: use constructor property promotion, named arguments, match expressions where appropriate.
- Always declare return types and parameter types: `function foo(User $user): bool`.
- Curly braces on all control structures, even single-line bodies.
- Enum keys in TitleCase: `AccountType::Checking`.
- Model casts go in a `casts(): array` method, not a `$casts` property.
- No comments explaining *what* code does — only *why* when it's non-obvious.
- Do not add error handling for impossible scenarios. Trust framework guarantees.

## Laravel 11 specifics

- Middleware is registered in `bootstrap/app.php`, not `Kernel.php`.
- Service providers go in `bootstrap/providers.php`.
- Console commands in `app/Console/Commands/` auto-register — no manual registration needed.
- When modifying a column in a migration, include **all** previously defined attributes or they will be dropped.

## Filament v5 specifics

- Resources live in `app/Filament/Resources/`.
- Pages auto-discovered from `app/Filament/Pages/`.
- Use `Filament\Schemas\Schema` (not `Filament\Forms\Form`) for form definitions in pages.
- Use `Filament\Schemas\Components\Section` for layout sections in pages.
- The panel config is in `app/Providers/Filament/AdminPanelProvider.php`.

## Security model

- **Encrypted fields**: `accounts.iban` and `transactions.counterpart_iban` use Laravel's `encrypted` cast. Never query these columns in raw SQL — always go through Eloquent.
- **Encrypted cast + pluck**: use `->get()->pluck(...)` (collection) not `->pluck(...)` (query builder) when building IBAN lookup maps, otherwise the cast is bypassed.
- **Per-user data isolation**: `Account` and `Transaction` both have a global scope that filters by `auth()->id()`. Use `withoutGlobalScopes()` in jobs, commands, or internal recalculation methods that run outside a user request.
- **AI keys**: Claude and OpenAI API keys are stored encrypted in `app_settings` via `AppSetting::current()`. Never read them from `.env` directly in application code — use `AiCategorizationService`.
- **2FA**: Filament's TOTP `AppAuthentication` is active. The `User` model implements `HasAppAuthentication` and `HasAppAuthenticationRecovery`.

## Artisan commands

```bash
php artisan migrate              # run migrations
php artisan migrate:fresh --seed # reset + reseed (dev only)
php artisan make:filament-user   # create an admin user
php artisan tinker               # interactive REPL
php artisan route:list --except-vendor  # inspect routes
```

## Code formatting

After modifying any PHP file, run:
```bash
vendor/bin/pint --dirty
```

Pint enforces the project's code style automatically.

## Testing

```bash
php artisan test                          # all tests
php artisan test tests/Feature/Foo.php   # single file
php artisan test --filter=testName       # single test
```

- All tests are PHPUnit classes in `tests/Feature/` or `tests/Unit/`.
- Use model factories; never hardcode IDs or create records without a factory.
- Do not delete or skip existing tests without explicit approval.
- Cover happy paths, failure paths, and edge cases.

## Do not change without approval

- Composer or npm dependencies
- Database schema for existing columns (add new migrations instead)
- The `app/Providers/Filament/AdminPanelProvider.php` panel ID or path
- Any existing migration file
