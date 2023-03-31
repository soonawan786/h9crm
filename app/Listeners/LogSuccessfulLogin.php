<?php

namespace App\Listeners;

use App\Models\User;
use App\Scopes\ActiveScope;
use App\Scopes\CompanyScope;
use Illuminate\Auth\Events\Login;

class LogSuccessfulLogin
{

    public function handle()
    {
        // WORKSUITESAAS
        if (!session()->has('impersonate') && !session()->has('stop_impersonate')) {
            $user = User::withoutGlobalScopes([CompanyScope::class, ActiveScope::class])->where('id', user()->id)->first();
            $user->last_login = now();  /* @phpstan-ignore-line */
            $user->save();
    
            if (company()) {
                $company = company();
                $company->last_login = now();  /* @phpstan-ignore-line */
                $company->saveQuietly();
            }
        }

    }

}
