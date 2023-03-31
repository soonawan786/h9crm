<?php

namespace App\Http\Middleware;

use App\Models\User;
use App\Scopes\ActiveScope;
use App\Scopes\CompanyScope;
use Closure;
use Illuminate\Http\Request;

class MultiCompanySelect
{

    /**
     * Handle an incoming request.
     *
     * @param \Illuminate\Http\Request $request
     * @param \Closure(\Illuminate\Http\Request): (\Illuminate\Http\Response|\Illuminate\Http\RedirectResponse) $next
     * @return \Illuminate\Http\Response|\Illuminate\Http\RedirectResponse
     */
    public function handle(Request $request, Closure $next)
    {

        if (session()->get('user_company_count') > 1 && !session()->has('multi_company_selected')) {
            return redirect(route('superadmin.superadmin.workspaces'));
        }

        if (!session()->has('impersonate') && !session()->has('stop_impersonate')) {
            $user = user();

            if ($user) {
                $user->last_login = now();
                /* @phpstan-ignore-line */
                $user->saveQuietly();
            }


            if (company()) {
                $company = company();
                $company->last_login = now();
                /* @phpstan-ignore-line */
                $company->saveQuietly();
            }
        }

        return $next($request);
    }

}
