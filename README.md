# Laravel User Switcher

> **Development tool — do not enable in production.**

A small floating widget for logging in as another user without a password, so
you can quickly test role-based behaviour. No dependency on the app's schema
beyond an Eloquent user model — it reads the display name and identifier via
plain property access, so a computed `name` accessor (e.g. derived from
`first_name`/`last_name`) works fine.

## Installation

```bash
composer require nettsite/laravel-user-switcher --dev
```

Publish the config:

```bash
php artisan vendor:publish --provider="NettSite\LaravelUserSwitcher\LaravelUserSwitcherServiceProvider" --tag="user-switcher-config"
```

Add the widget to your app layout (once, near the end of `<body>`):

```blade
<livewire:user-switcher />
```

## Enabling it

Disabled by default. Turn it on in your **local** `.env` only:

```dotenv
USER_SWITCHER_ENABLED=true
```

It's also restricted to `local,testing` environments by default
(`USER_SWITCHER_ENVIRONMENTS`), so enabling it accidentally in a committed
`.env.example` or a staging config still wouldn't activate it in production.

## Configuration

| Option | Description | Default |
| :--- | :--- | :--- |
| `enabled` | Master switch. | `env('USER_SWITCHER_ENABLED', false)` |
| `environments` | Comma-separated allowed environments. | `env('USER_SWITCHER_ENVIRONMENTS', 'local,testing')` |
| `user_model` | The Eloquent model to list/switch between. | `App\Models\User` |
| `label_attribute` | Property shown as the main label per user. | `name` |
| `identifier_attribute` | Property shown as the secondary line (e.g. email). | `email` |
| `authorization_callback` | Closure deciding access; overrides `enabled`/`environments` entirely when set. | `null` |

### Restricting to specific users (recommended for staging)

```php
// config/user-switcher.php
'authorization_callback' => fn (\Illuminate\Http\Request $request) =>
    $request->user()?->can('users.manage') ?? false,
```

Prefer a permission check (`->can(...)`) over a role-name check
(`->hasRole(...)`) if your app uses a role/permission package — role names
are typically user-configurable at runtime, permission strings are the
stable contract.

## How it works

- `switchTo($id)` stores the current user's ID in the session
  (`user-switcher.original_id`, only if not already impersonating — so
  switching twice doesn't lose the real original user), logs in the target
  user, and regenerates the session ID.
- `stop()` reads that stored ID back, logs the original user back in, clears
  the session key, and regenerates the session ID again.
- Both actions re-check `UserSwitcherGate::allowed()` server-side before
  doing anything, so a stale or tampered client can't switch when the gate
  says no — the widget not rendering isn't the only line of defense.

## Views

Views are published under `resources/views/vendor/user-switcher/` if
you want to restyle the widget — the shipped one uses plain Tailwind utility
classes and makes no assumption about a component library (Flux, etc.) being
present.
