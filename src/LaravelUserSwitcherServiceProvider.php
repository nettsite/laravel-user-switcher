<?php

namespace NettSite\LaravelUserSwitcher;

use Livewire\Livewire;
use NettSite\LaravelUserSwitcher\Livewire\UserSwitcherWidget;
use Spatie\LaravelPackageTools\Package;
use Spatie\LaravelPackageTools\PackageServiceProvider;

class LaravelUserSwitcherServiceProvider extends PackageServiceProvider
{
    public function configurePackage(Package $package): void
    {
        $package
            ->name('laravel-user-switcher')
            ->hasConfigFile()
            ->hasViews();
    }

    public function packageBooted(): void
    {
        Livewire::component('user-switcher', UserSwitcherWidget::class);
    }
}
