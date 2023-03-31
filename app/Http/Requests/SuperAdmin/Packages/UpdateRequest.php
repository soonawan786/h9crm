<?php

namespace App\Http\Requests\SuperAdmin\Packages;

use App\Models\SuperAdmin\GlobalPaymentGatewayCredentials;
use App\Models\SuperAdmin\Package;
use App\Models\SuperAdmin\StripeSetting;
use Illuminate\Foundation\Http\FormRequest;

class UpdateRequest extends FormRequest
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
        $data = [
            'currency_id' => 'required|exists:global_currencies,id',
            'name' => 'required|unique:packages,name,' . $this->route('package'),
            'max_employees' => 'required|numeric',
            'max_storage_size' => 'required|gte:-1',
            'storage_unit' => 'required|in:gb,mb',
        ];

        if ($this->get('no_of_days')) {
            $data['no_of_days'] = 'sometimes|required';
            $data['trial_message'] = 'sometimes|required';

            return $data;
        }

        $package = Package::find($this->route('package'));

        if($package->default === 'yes'){
            return $data;
        }

        $data['description'] = 'required';

        if (!$this->has('is_free')) {
            $data['annual_price'] = 'required';
            $data['monthly_price'] = 'required';


            $gateways = GlobalPaymentGatewayCredentials::first();

            if(($this->get('annual_price') > 0 && $this->get('monthly_price') > 0 ) && $gateways->razorpay_status == 'active'){
                $data['razorpay_annual_plan_id'] = 'required';
                $data['razorpay_monthly_plan_id'] = 'required';
            }

            if($this->get('annual_price') > 0 && $this->get('monthly_price') > 0 && $gateways->stripe_status == 'active'){
                $data['stripe_annual_plan_id'] = 'required';
                $data['stripe_monthly_plan_id'] = 'required';
            }
        }

        return $data;
    }

}
