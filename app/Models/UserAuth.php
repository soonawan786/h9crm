<?php

namespace App\Models;

use App\Notifications\VerifyEmail;
use App\Scopes\ActiveScope;
use App\Scopes\CompanyScope;
use Illuminate\Auth\Authenticatable;
use Illuminate\Auth\MustVerifyEmail as AuthMustVerifyEmail;
use Illuminate\Auth\Passwords\CanResetPassword;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Foundation\Auth\Access\Authorizable;
use Illuminate\Validation\ValidationException;
use Laravel\Fortify\TwoFactorAuthenticatable;
use Illuminate\Contracts\Auth\Access\Authorizable as AuthorizableContract;
use Illuminate\Contracts\Auth\Authenticatable as AuthenticatableContract;
use Illuminate\Contracts\Auth\CanResetPassword as CanResetPasswordContract;
use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Notifications\Notifiable;
use Illuminate\Support\Facades\DB;
use Laravel\Fortify\TwoFactorAuthenticationProvider;
use Trebol\Entrust\Traits\EntrustUserTrait;

/**
 * App\Models\UserAuth
 *
 * @property string $name
 * @property string $email
 * @property string $password
 * @property string|null $two_factor_secret
 * @property string|null $two_factor_recovery_codes
 * @property string|null $remember_token
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @mixin \Eloquent
 * @property int $two_factor_confirmed
 * @property int $two_factor_email_confirmed
 * @property string|null $salutation
 * @property string|null $two_fa_verify_via
 * @property string|null $two_factor_code when authenticator is email
 * @property \Illuminate\Support\Carbon|null $two_factor_expires_at
 * @property-read \App\Models\User|null $user
 * @property-read \App\Models\User|null $userWithoutCompany
 * @property-read \Illuminate\Database\Eloquent\Collection|\App\Models\User[] $users
 * @property-read int|null $users_count
 * @method static \Illuminate\Database\Eloquent\Builder|UserAuth newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|UserAuth newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|UserAuth query()
 */
class UserAuth extends BaseModel implements AuthenticatableContract, AuthorizableContract, CanResetPasswordContract, MustVerifyEmail
{

    use Authenticatable, Authorizable, CanResetPassword, HasFactory, TwoFactorAuthenticatable, AuthMustVerifyEmail, Notifiable;

    protected $fillable = ['email', 'password', 'remember_token'];
    protected $hidden = ['password'];
    public $dates = ['two_factor_expires_at'];

    public function users(): HasMany
    {
        return $this->hasMany(User::class, 'user_auth_id')->withoutGlobalScope(ActiveScope::class);
    }

    public function user(): HasOne
    {
        return $this->hasOne(User::class, 'user_auth_id')->withoutGlobalScope(ActiveScope::class);
    }

    public function userWithoutCompany(): HasOne
    {
        return $this->hasOne(User::class, 'user_auth_id')->withoutGlobalScope(CompanyScope::class);
    }

    public function generateTwoFactorCode()
    {
        $this->timestamps = false;
        $this->two_factor_code = rand(100000, 999999);
        $this->two_factor_expires_at = now()->addMinutes(10);
        $this->save();
    }

    public function resetTwoFactorCode()
    {
        $this->timestamps = false;
        $this->two_factor_code = null;
        $this->two_factor_expires_at = null;
        $this->save();
    }

    public function confirmTwoFactorAuth($code)
    {
        $codeIsValid = app(TwoFactorAuthenticationProvider::class)
            ->verify(decrypt($this->two_factor_secret), $code);

        if ($codeIsValid) {
            $this->two_factor_confirmed = true;
            $this->save();

            return true;
        }

        return false;
    }

    public static function createUserAuthCredentials($email, $password = null)
    {
        $checkAuth = UserAuth::where('email', $email)->first();

        if (is_null($checkAuth)) {
            if (is_null($password)) {
                $string = '1234567890ABCDEFGHIJKLMNOPQRSTUVWXYZabcefghijklmnopqrstuvwxyz';
                $password = substr(str_shuffle($string), 0, 8);
            }

            $verified_at = user()?now():null;
            $checkAuth = UserAuth::create(['email' => $email, 'password' => bcrypt($password), 'email_verified_at' => $verified_at]);
            session(['auth_pass' => $password]);

        }

        return $checkAuth;
    }

    public static function validateLoginActiveDisabled($userAuth)
    {
        $globalSetting = GlobalSetting::first();

        if ($globalSetting->company_need_approval) {
            $userCompanies = DB::select('Select count(companies.id) as company_count from companies left join users on users.company_id = companies.id where users.email = "'.$userAuth->email.'"');
            $userUnapprovedCompanies = DB::select('Select count(companies.id) as company_count from companies left join users on users.company_id = companies.id where users.email = "'.$userAuth->email.'" and companies.approved = 0');

            // Check count of all user companies and match with total unapproved companies
            if ($userCompanies[0]->company_count > 0 && $userCompanies[0]->company_count == $userUnapprovedCompanies[0]->company_count) {
                throw ValidationException::withMessages([
                    'email' => __('auth.failedCompanyUnapproved')
                ]);
            }
        }


        // Check count of all user status and match with total user
        if ($userAuth->users->where('status', 'deactive')->count() == $userAuth->users->count()) {
            throw ValidationException::withMessages([
                'email' => __('auth.failedBlocked')
            ]);
        }

        // Check count of all user login and match with total user
        if ($userAuth->users->where('login', 'disable')->count() == $userAuth->users->count()) {
            throw ValidationException::withMessages([
                'email' => __('auth.failedLoginDisabled')
            ]);
        }
    }

    public function sendEmailVerificationNotification()
    {
        $this->notify(new VerifyEmail()); // my notification
    }

}
