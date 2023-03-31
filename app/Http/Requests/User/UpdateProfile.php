<?php

namespace App\Http\Requests\User;

use App\Http\Requests\CoreRequest;

class UpdateProfile extends CoreRequest
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
        $setting = companyOrGlobalSetting() ?? null;
        $rules = [
            'name' => 'required|max:50',
            'password' => 'nullable|min:8|max:50',
            'image' => 'image|max:2048',
            'mobile' => 'nullable|numeric',
            'date_of_birth' => 'nullable|date|before_or_equal:' . ($setting ? now($setting->timezone)->toDateString() : null),
            'email' => [
                'required',
                'email:rfc',
                'unique:user_auths,email,' . user()->user_auth_id . ',id',
            ],
        ];

        return $rules;
    }

    /**
     * Get the validation messages that apply to the request.
     *
     * @return array
     */
    public function messages()
    {
        return [
            'image.image' => 'Profile picture should be an image',
        ];
    }

}

