# CLAUDE.md

This file provides guidance to Claude Code (claude.ai/code) when working with code in this repository.

## Commands

```bash
composer test       # Run Pest test suite
composer analyse     # PHPStan static analysis (level 5)
composer format      # Laravel Pint code style fixer
```

Run a single test:
```bash
vendor/bin/pest tests/UserSwitcherGateTest.php
vendor/bin/pest --filter "test name"
```

## Architecture

**Package type:** Laravel package built on `spatie/laravel-package-tools`. Namespace: `NettSite\LaravelUserSwitcher`.

**Purpose:** Dev-only widget for logging in as another user without a password, to test role-based behaviour. Not the original `cdoebler/laravel-user-switcher` — written from scratch because that package caps `illuminate/*` at `^12.0` and doesn't support Laravel 13.

### Core classes

- `src/UserSwitcherGate.php` — Static `allowed(?Request $request = null): bool`. If `authorization_callback` is configured, it alone decides; otherwise falls back to `enabled` + `environments`. Called both from the widget's `render()` (hide the UI) and from `switchTo()`/`stop()` (enforce server-side, since hiding the UI is not itself a security boundary).
- `src/Livewire/UserSwitcherWidget.php` — The whole feature. `switchTo($id)` and `stop()` do `Auth::login()` + `session()->regenerate()` directly; no separate controller or routes.
- `src/LaravelUserSwitcherServiceProvider.php` — Registers config + views via Spatie's package tools, registers the Livewire component as `user-switcher`.

### Non-obvious constraint: sort in PHP, not SQL

`render()` fetches all users then sorts with `->sortBy(fn ($user) => $user->{$labelAttribute})` instead of `->orderBy($labelAttribute)`. `label_attribute` defaults to `name`, which is very often a computed Eloquent accessor (e.g. derived from `first_name`/`last_name`) rather than a real column — an `->orderBy()` on that would fail on any database that validates column existence strictly (MariaDB does; SQLite silently tolerates it, which is exactly the trap: this would pass every test on SQLite and 500 in production). Keep this PHP-side sort — don't "optimize" it into a query-level order by.

### Session key

`user-switcher.original_id` — the dot notation is a single session array key (Laravel session arrays support dot-set/get), not two separate keys.

### Testing

Orchestra Testbench. `tests/TestCase.php` creates a real `users` table via `Schema::create` (no migration file — this is a test-only fixture) and points `user-switcher.user_model` at `tests/Fixtures/User.php`, which deliberately has a computed `name` accessor (not a column) to prove the PHP-side-sort constraint above actually holds.

### Config caching

`authorization_callback` is a closure in the config file, which makes the file un-cacheable via `artisan config:cache`. This is fine for a dev-only tool (nobody runs `config:cache` locally) — documented in the config file and README, not worth engineering around.
