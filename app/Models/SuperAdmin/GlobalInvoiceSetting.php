<?php

namespace App\Models\SuperAdmin;

use Illuminate\Database\Eloquent\Model;

class GlobalInvoiceSetting extends Model
{
    protected $appends = ['logo_url','authorised_signatory_signature_url', 'is_chinese_lang'];

    public function getLogoUrlAttribute()
    {
        return (is_null($this->logo)) ? global_setting()->logo_url : asset_url('app-logo/' . $this->logo);
    }

    public function getAuthorisedSignatorySignatureUrlAttribute()
    {
        return (is_null($this->authorised_signatory_signature)) ? '' : asset_url('app-logo/' . $this->authorised_signatory_signature);
    }

    public function getIsChineseLangAttribute()
    {
        return in_array(strtolower($this->locale), ['zh-hk', 'zh-cn', 'zh-sg', 'zh-tw', 'cn']);
    }

}
