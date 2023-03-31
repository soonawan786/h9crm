<?php

namespace App\Http\Requests\SuperAdmin\FooterSetting;

use App\Models\SuperAdmin\FooterMenu;
use Illuminate\Foundation\Http\FormRequest;

class StoreRequest extends FormRequest
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
        $rules['title'] = 'required|unique:footer_menu,name';

        if($this->get('content') == 'desc'){
            $rules['description'] = 'required';
        }
        else{
            $rules['external_link'] = 'required|url';
        }

        return $rules;
    }

}
