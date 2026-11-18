<?php

namespace NettSite\LaravelUserSwitcher\Livewire;

use Illuminate\Support\Facades\Auth;
use Livewire\Attributes\On;
use Livewire\Component;
use NettSite\LaravelUserSwitcher\UserSwitcherGate;

class UserSwitcherWidget extends Component
{
    public bool $open = false;

    public function toggle(): void
    {
        $this->open = ! $this->open;
    }

    /** Lets another page (e.g. a "Switch to" button on a users list) trigger a switch without reaching into this component. */
    #[On('user-switcher:switch')]
    public function switchTo(string $id): void
    {
        abort_unless(UserSwitcherGate::allowed(), 403);

        $userModel = config('user-switcher.user_model');
        $target = $userModel::findOrFail($id);

        if (! session()->has('user-switcher.original_id')) {
            session()->put('user-switcher.original_id', Auth::id());
        }

        Auth::login($target);
        session()->regenerate();

        $this->redirect(url()->previous(), navigate: false);
    }

    public function stop(): void
    {
        abort_unless(UserSwitcherGate::allowed(), 403);

        $originalId = session()->pull('user-switcher.original_id');
        $userModel = config('user-switcher.user_model');
        $original = $originalId ? $userModel::find($originalId) : null;

        if ($original) {
            Auth::login($original);
        }

        session()->regenerate();

        $this->redirect(url()->previous(), navigate: false);
    }

    public function render()
    {
        if (! UserSwitcherGate::allowed()) {
            return view('user-switcher::empty');
        }

        $userModel = config('user-switcher.user_model');
        $labelAttribute = config('user-switcher.label_attribute', 'name');
        $identifierAttribute = config('user-switcher.identifier_attribute', 'email');

        // Sorted in PHP, not via ->orderBy(): the label attribute is often a
        // computed accessor rather than a real column, and an SQL ORDER BY
        // on a nonexistent column fails loudly on strict databases (though
        // not on SQLite, which silently tolerates it).
        $users = $userModel::query()->get()->sortBy(fn ($user) => $user->{$labelAttribute});

        return view('user-switcher::widget', [
            'users' => $users,
            'labelAttribute' => $labelAttribute,
            'identifierAttribute' => $identifierAttribute,
            'isImpersonating' => session()->has('user-switcher.original_id'),
        ]);
    }
}
