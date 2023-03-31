<?php

namespace App\Http\Controllers\SuperAdmin;

use App\Events\NewUserEvent;
use App\Helper\Reply;
use App\Http\Requests\SuperAdmin\Register\StoreRequest;
use App\Models\Company;
use App\Models\EmployeeDetails;
use App\Models\GlobalSetting;
use App\Models\Role;
use App\Models\SuperAdmin\SeoDetail;
use App\Models\SuperAdmin\SignUpSetting;
use App\Models\SuperAdmin\TrFrontDetail;
use App\Models\UniversalSearch;
use App\Models\User;
use App\Models\UserAuth;
use App\Notifications\NewUser;
use App\Scopes\ActiveScope;
use App\Scopes\CompanyScope;
use GuzzleHttp\Client;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class CompanyRegisterController extends FrontBaseController
{

    public function index()
    {
        $this->global = GlobalSetting::first();

        if (\user()) {
            return redirect(getDomainSpecificUrl(route('login'), \user()->company));
        }

        $this->seoDetail = SeoDetail::where('page_name', 'home')->first();
        $this->pageTitle = 'Sign Up';

        $view = ($this->setting->front_design == 1) ? 'super-admin.saas.register' : 'super-admin.front.register';


        if ($this->global->frontend_disable || $this->global->setup_homepage == 'custom') {
            $view = 'super-admin.register';
        }

        $this->trFrontDetail = TrFrontDetail::where('language_setting_id', $this->localeLanguage->id)->first();
        $this->trFrontDetail = $this->trFrontDetail ?: TrFrontDetail::where('language_setting_id', $this->enLocaleLanguage->id)->first();

        $signUpCount = SignUpSetting::select('id', 'language_setting_id')->where('language_setting_id', $this->localeLanguage ? $this->localeLanguage->id : null)->count();
        $this->signUpMessage = SignUpSetting::where('language_setting_id', $signUpCount > 0 ? ($this->localeLanguage ? $this->localeLanguage->id : null) : null)->first();

        $this->registrationStatus = $this->global;

        return view($view, $this->data);
    }

    public function store(StoreRequest $request)
    {
        $company = new Company();
        $global = GlobalSetting::first();

        if ($global->google_recaptcha_v2_status == 'active' && !$this->recaptchaValidate($request)) {
            return Reply::error('Recaptcha not validated.');
        }

        $superadmin = User::withoutGlobalScopes([CompanyScope::class])
            ->whereNull('company_id')
            ->where('is_superadmin', 1)
            ->where('email', $request->email)
            ->first();

        if ($superadmin) {
            return Reply::error(__('superadmin.cannotUseEmail'));
        }

        DB::beginTransaction();
        try {
            $company->company_name = $request->company_name;
            $company->company_email = $request->email;
            $company->address = $request->company_name;
            $company->app_name = $request->company_name;

            if (module_enabled('Subdomain')) {
                $company->sub_domain = $request->sub_domain;
            }

            $company->save();

            $this->addUser($company, $request, $global);

            DB::commit();
        } catch (\Swift_TransportException $e) {
            DB::rollback();

            return Reply::error('Please contact administrator to set SMTP details to add company', 'smtp_error');
        } catch (\Exception $e) {
            DB::rollback();

            return Reply::error('Some error occurred when inserting the data. Please try again or contact support: ' . $e->getMessage());
        }

        return Reply::success(__('superadmin.signUpThankYou'));
    }

    public function getEmailVerification($code)
    {
        $this->pageTitle = 'modules.accountSettings.emailVerification';
        $this->message = User::emailVerify($code);

        return view('auth.email-verification', $this->data);
    }

    public function addUser($company, $request, $global)
    {
        // Save Admin
        $user = User::withoutGlobalScopes([CompanyScope::class, ActiveScope::class])
            ->where('company_id', $company->id)
            ->where('email', $request->email)
            ->first();

        if (is_null($user)) {
            $user = new User();
        }

        $userAuth = UserAuth::createUserAuthCredentials($request->email);

        $user->company_id = $company->id;
        $user->name = $request->name;
        $user->email = $request->email;
        $user->status = 'active';
        $user->user_auth_id = $userAuth->id;
        $user->save();

        if ($global->email_verification) {
            $userAuth->sendEmailVerificationNotification();
        }

        if ($request->password != '') {
            UserAuth::where('id', $user->user_auth_id)->update(['password' => bcrypt($request->password)]);
            $user->notify(new NewUser($user, $request->password));
        }

        if (!$user->hasRole('admin')) {

            // Attach Admin Role
            $adminRole = Role::withoutGlobalScope(CompanyScope::class)->where('name', 'admin')->where('company_id', $company->id)->first();

            $employeeRole = Role::withoutGlobalScope(CompanyScope::class)->where('name', 'employee')->where('company_id', $user->company_id)->first();

            $user->roles()->attach($adminRole->id);
            $this->addEmployeeDetails($user, $employeeRole, $company->id);

            $user->insertUserRolePermission($adminRole->id);

        }
    }

    private function addEmployeeDetails($user, $employeeRole, $companyId)
    {
        $employee = new EmployeeDetails();
        $employee->user_id = $user->id;
        $employee->company_id = $companyId;
        /* @phpstan-ignore-line */
        $employee->employee_id = 'EMP-1';
        /* @phpstan-ignore-line */
        $employee->save();

        $search = new UniversalSearch();
        $search->searchable_id = $user->id;
        $search->company_id = $companyId;
        $search->title = $user->name;
        $search->route_name = 'employees.show';
        $search->save();

        // Assign Role
        $user->roles()->attach($employeeRole->id);
        /* @phpstan-ignore-line */
    }

    public function recaptchaValidate($request)
    {
        $global = global_setting();

        if ($global->google_recaptcha_status == 'active') {
            $gRecaptchaResponseInput = 'g-recaptcha-response';
            $gRecaptchaResponse = $request->{$gRecaptchaResponseInput};

            $gRecaptchaResponse = $global->google_recaptcha_v2_status == 'active' ? $gRecaptchaResponse : $request->g_recaptcha;

            if (is_null($gRecaptchaResponse)) {
                return $this->googleRecaptchaMessage();
            }

            $secret = $global->google_recaptcha_v2_status == 'active' ? $global->google_recaptcha_v2_secret_key : $global->google_recaptcha_v3_secret_key;

            $validateRecaptcha = $this->validateGoogleRecaptcha($gRecaptchaResponse, $secret);

            if (!$validateRecaptcha) {
                return $this->googleRecaptchaMessage();
            }
        }

        return true;
    }

    public function validateGoogleRecaptcha($googleRecaptchaResponse, $secret)
    {
        $client = new Client();

        $googleRecaptchaResponse = is_null($googleRecaptchaResponse) ? '' : $googleRecaptchaResponse;

        $response = $client->post('https://www.google.com/recaptcha/api/siteverify',
            [
                'form_params' => [
                    'secret' => $secret,
                    'response' => $googleRecaptchaResponse
                ]
            ]
        );
        $body = json_decode((string)$response->getBody());

        return $body->success;
    }

    public function googleRecaptchaMessage()
    {
        throw ValidationException::withMessages([
            'g-recaptcha-response' => [__('auth.recaptchaFailed')],
        ]);
    }

}
