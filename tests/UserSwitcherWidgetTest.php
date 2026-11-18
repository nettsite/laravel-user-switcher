<?php

use Illuminate\Support\Facades\Auth;
use Livewire\Livewire;
use NettSite\LaravelUserSwitcher\Livewire\UserSwitcherWidget;
use NettSite\LaravelUserSwitcher\Tests\Fixtures\User;

beforeEach(function (): void {
    config()->set('user-switcher.enabled', true);
    config()->set('user-switcher.environments', ['testing']);
});

it('renders nothing when not allowed', function (): void {
    config()->set('user-switcher.enabled', false);

    $admin = User::create(['first_name' => 'Admin', 'last_name' => 'One', 'email' => 'admin@example.com']);

    Livewire::actingAs($admin)
        ->test(UserSwitcherWidget::class)
        ->assertDontSee('Switch user');
});

it('lists users sorted by the label attribute, without relying on an SQL column', function (): void {
    $admin = User::create(['first_name' => 'Admin', 'last_name' => 'One', 'email' => 'admin@example.com']);
    User::create(['first_name' => 'Zed', 'last_name' => 'Last', 'email' => 'zed@example.com']);
    User::create(['first_name' => 'Alpha', 'last_name' => 'First', 'email' => 'alpha@example.com']);

    Livewire::actingAs($admin)
        ->test(UserSwitcherWidget::class)
        ->call('toggle')
        ->assertSeeInOrder(['Admin One', 'Alpha First', 'Zed Last']);
});

it('logs the current user out and the target user in, remembering the original', function (): void {
    $admin = User::create(['first_name' => 'Admin', 'last_name' => 'One', 'email' => 'admin@example.com']);
    $target = User::create(['first_name' => 'Regular', 'last_name' => 'User', 'email' => 'regular@example.com']);

    Livewire::actingAs($admin)
        ->test(UserSwitcherWidget::class)
        ->call('switchTo', (string) $target->id);

    expect(Auth::id())->toBe($target->id);
    expect(session('user-switcher.original_id'))->toBe($admin->id);
});

it('restores the original user on stop', function (): void {
    $admin = User::create(['first_name' => 'Admin', 'last_name' => 'One', 'email' => 'admin@example.com']);
    $target = User::create(['first_name' => 'Regular', 'last_name' => 'User', 'email' => 'regular@example.com']);

    session()->put('user-switcher.original_id', $admin->id);
    Auth::login($target);

    Livewire::actingAs($target)
        ->test(UserSwitcherWidget::class)
        ->call('stop');

    expect(Auth::id())->toBe($admin->id);
    expect(session()->has('user-switcher.original_id'))->toBeFalse();
});

it('switches when another component dispatches the switch event', function (): void {
    $admin = User::create(['first_name' => 'Admin', 'last_name' => 'One', 'email' => 'admin@example.com']);
    $target = User::create(['first_name' => 'Regular', 'last_name' => 'User', 'email' => 'regular@example.com']);

    Livewire::actingAs($admin)
        ->test(UserSwitcherWidget::class)
        ->dispatch('user-switcher:switch', id: (string) $target->id);

    expect(Auth::id())->toBe($target->id);
    expect(session('user-switcher.original_id'))->toBe($admin->id);
});

it('blocks switching when not allowed, even via a direct component call', function (): void {
    $admin = User::create(['first_name' => 'Admin', 'last_name' => 'One', 'email' => 'admin@example.com']);
    $target = User::create(['first_name' => 'Regular', 'last_name' => 'User', 'email' => 'regular@example.com']);

    config()->set('user-switcher.enabled', false);

    Livewire::actingAs($admin)
        ->test(UserSwitcherWidget::class)
        ->call('switchTo', (string) $target->id)
        ->assertForbidden();

    expect(Auth::id())->toBe($admin->id);
});
