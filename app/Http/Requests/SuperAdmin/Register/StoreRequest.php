<?php

namespace App\Http\Requests\SuperAdmin\Register;

use App\Http\Requests\CoreRequest;
use App\Models\Company;
use App\Models\GlobalSetting;
use App\Models\User;
use Illuminate\Validation\Rule;

class StoreRequest extends CoreRequest
{

    /**
     * Determine if the user is authorized to make this request.
     *
     * @return bool
     */
    public function authorize()
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules()
    {
        $global = global_setting();

        $rules = [
            'company_name' => 'required',
            'name' => 'required',
            'email' => 'required|email',
            'sub_domain' => module_enabled('Subdomain') ? 'required|banned_sub_domain|min:4|unique:companies,sub_domain|max:50' : '',
            'password' => 'required|confirmed|min:6',
        ];

        if (request()->password_confirmation != '') {
            $rules['password'] = 'required|confirmed|min:8';

        } else {
            $rules['password'] = 'required|min:8';
        }

        if($global->google_recaptcha_v2_status == 'active'){
            $rules['g-recaptcha-response'] = 'required';
        }

        if (Company::where('company_email', '=', request()->email)->exists()) {
            $rules['email'] = 'required|email|unique:users,email';
        }

        $user = User::where('users.email', request()->email)->first();

        if ($user) {
            $user->hasRole('employee') ? $rules['email'] = 'required|email|unique:users' : '';
        }

        return $rules;
    }

    public function messages()
    {
        return [
            'g-recaptcha-response.required' => 'Please select google recaptcha'
        ];
    }

    public function prepareForValidation()
    {
        if (empty($this->sub_domain)) {
            return;
        }

        // Add servername domain suffix at the end
        $subdomain = trim($this->sub_domain, '.') . '.' . getDomain();
        $this->merge(['sub_domain' => $subdomain]);
        request()->merge(['sub_domain' => $subdomain]);
    }

}
