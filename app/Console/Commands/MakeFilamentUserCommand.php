<?php

namespace App\Console\Commands;

use Filament\Commands\MakeUserCommand;

class MakeFilamentUserCommand extends MakeUserCommand
{
    protected $hidden = true;

    protected function getUserData(): array
    {
        $userData = parent::getUserData();

        return array_merge($userData, [
            'is_admin' => true,
            'role' => 'admin',
            'wallet_balance' => 0,
        ]);
    }
}
