<?php

use Blemli\WebSearch\Tests\Fixtures\User;
use Blemli\WebSearch\Tests\TestCase;
use Filament\Facades\Filament;
use Filament\Panel;

uses(TestCase::class)->in(__DIR__);

function bootPanel(string $id = 'admin'): Panel
{
    $panel = Filament::getPanel($id);

    Filament::setCurrentPanel($panel);
    Filament::bootCurrentPanel();

    return $panel;
}

function loginUser(): User
{
    $user = User::create([
        'name' => 'Test User',
        'email' => uniqid() . '@example.com',
        'password' => bcrypt('secret'),
    ]);

    test()->actingAs($user);

    return $user;
}

function createPreferencesTable(): void
{
    (require __DIR__ . '/../database/migrations/create_websearch_preferences_table.php.stub')->up();
}
