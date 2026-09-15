<?php

namespace NettSite\LaravelUserSwitcher;

use Illuminate\Http\Request;

class UserSwitcherGate
{
    /**
     * Whether the current request is allowed to use the switcher.
     *
     * If `authorization_callback` is configured, it alone decides. Otherwise
     * falls back to the enabled+environments config.
     */
    public static function allowed(?Request $request = null): bool
    {
        $callback = config('user-switcher.authorization_callback');

        if (is_callable($callback)) {
            return (bool) $callback($request ?? request());
        }

        if (! config('user-switcher.enabled')) {
            return false;
        }

        return app()->environment((array) config('user-switcher.environments'));
    }
}
