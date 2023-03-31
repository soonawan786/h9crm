<?php

namespace App\Actions\Fortify;

use App\Http\Controllers\AccountBaseController;
use App\Models\Company;
use App\Models\Role;
use App\Models\User;
use App\Notifications\NewCustomer;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Notification;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;
use Laravel\Fortify\Contracts\CreatesNewUsers;

class CreateNewUser implements CreatesNewUsers
{

    use PasswordValidationRules;

    /**
     * Validate and create a newly registered user.
     *
     * @param  array  $input
     * @return \App\Models\UserAuth
     */
    public function create(array $input)
    {
        Validator::make($input, [
            'name' => ['required', 'string', 'max:255'],
            'email' => [
                'required',
                'string',
                'email',
                'max:255',
                Rule::unique(User::class),
            ],
            'password' => 'required|min:8',
        ])->validate();
        // Is worksuite
        $company = Company::first();

        $user = User::create([
            'company_id' => $company->id,
            'name' => $input['name'],
            'email' => $input['email'],
            'admin_approval' => !$company->admin_client_signup_approval,
        ]);

        $userAuth = $user->userAuth()->create(['email' => $input['email'], 'password' => bcrypt($input['password'])]);
        $user->user_auth_id = $userAuth->id;
        $user->saveQuietly();

        $data = $input;
        $data['email_notifications'] = 1;
        $user->clientDetails()->create(['company_name' => $company->company_name]);

        $role = Role::where('company_id', $company->id)->where('name', 'client')->select('id')->first();
        $user->attachRole($role->id);

        $user->insertUserRolePermission($role->id);

        $log = new AccountBaseController();

        // Log search
        $log->logSearchEntry($user->id, $user->name, 'clients.show', 'client');

        if (!is_null($user->email)) {
            $log->logSearchEntry($user->id, $user->email, 'clients.show', 'client');
        }

        if (!is_null($user->clientDetails->company_name)) {
            $log->logSearchEntry($user->id, $user->clientDetails->company_name, 'clients.show', 'client');
        }

        Notification::send(User::allAdmins($user->company->id), new NewCustomer($user));

        return $userAuth;

    }

}
