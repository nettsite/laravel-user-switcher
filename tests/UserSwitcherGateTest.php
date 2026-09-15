<?php

use NettSite\LaravelUserSwitcher\UserSwitcherGate;

it('is disallowed by default', function (): void {
    expect(UserSwitcherGate::allowed())->toBeFalse();
});

it('is allowed when enabled and in an allowed environment', function (): void {
    config()->set('user-switcher.enabled', true);
    config()->set('user-switcher.environments', ['testing']);

    expect(UserSwitcherGate::allowed())->toBeTrue();
});

it('is disallowed when enabled but in a disallowed environment', function (): void {
    config()->set('user-switcher.enabled', true);
    config()->set('user-switcher.environments', ['production']);

    expect(UserSwitcherGate::allowed())->toBeFalse();
});

it('lets the authorization_callback override enabled/environments entirely', function (): void {
    config()->set('user-switcher.enabled', false);
    config()->set('user-switcher.authorization_callback', fn () => true);

    expect(UserSwitcherGate::allowed())->toBeTrue();
});
