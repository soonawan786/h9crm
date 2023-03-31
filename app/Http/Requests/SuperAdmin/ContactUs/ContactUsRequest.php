<?php

namespace App\Http\Requests\SuperAdmin\ContactUs;

use App\Models\GlobalSetting;
use Illuminate\Foundation\Http\FormRequest;

class ContactUsRequest extends FormRequest
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
        $global = GlobalSetting::first();
        $rules = [
            'name' => 'required',
            'email' => 'required|email',
            'message' => 'required',
        ];

        if($global->google_recaptcha_v2_status == 'active'){
            $rules['g-recaptcha-response'] = 'required';
        }
        
        return $rules;
    }

    public function messages()
    {
        return [
            'g-recaptcha-response.required' => 'Please select google recaptcha'
        ];
    }

}
