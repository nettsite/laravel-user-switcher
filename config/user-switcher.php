<?php

return [

    /*
     * Master switch. Off by default so it can never accidentally ship
     * enabled — turn it on per-environment via USER_SWITCHER_ENABLED.
     */
    'enabled' => env('USER_SWITCHER_ENABLED', false),

    /*
     * Which app environments the widget is allowed to run in, even when
     * enabled=true. Ignored when authorization_callback is set.
     */
    'environments' => explode(',', env('USER_SWITCHER_ENVIRONMENTS', 'local,testing')),

    /*
     * The Eloquent model to list and switch between.
     */
    'user_model' => env('USER_SWITCHER_USER_MODEL', 'App\\Models\\User'),

    /*
     * Attributes shown for each user in the switcher list. These are read
     * via property access ($user->{$attribute}), so a computed accessor
     * (e.g. a `name` derived from first_name/last_name) works fine — the
     * list is sorted in PHP for the same reason, not via an SQL ORDER BY.
     */
    'label_attribute' => 'name',
    'identifier_attribute' => 'email',

    /*
     * Custom authorization logic, e.g.:
     *
     * 'authorization_callback' => fn (\Illuminate\Http\Request $request) =>
     *     $request->user()?->can('users.manage') ?? false,
     *
     * When set, this replaces the enabled+environments check entirely.
     * Note: a closure here makes this config file un-cacheable
     * (`artisan config:cache`) — fine for a dev-only tool, but don't ship
     * this config cached in a real environment.
     */
    'authorization_callback' => null,

];
