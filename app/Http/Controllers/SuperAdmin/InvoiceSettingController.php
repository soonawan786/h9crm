<?php

namespace App\Http\Controllers\SuperAdmin;

use App\Helper\Files;
use App\Helper\Reply;
use App\Http\Controllers\AccountBaseController;
use App\Models\SuperAdmin\GlobalInvoiceSetting;
use App\Http\Requests\SuperAdmin\GlobalInvoiceSetting\UpdateInvoiceSetting;

class InvoiceSettingController extends AccountBaseController
{

    public function __construct()
    {
        parent::__construct();

        $this->pageTitle = 'app.menu.financeSettings';
        $this->activeSettingMenu = 'global_invoice_settings';
    }

    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {

        $this->invoiceSetting = GlobalInvoiceSetting::first();
        $this->view = 'super-admin.invoice-settings.ajax.general';

        if (request()->ajax()) {
            $html = view($this->view, $this->data)->render();

            return Reply::dataOnly(['status' => 'success', 'html' => $html, 'title' => $this->pageTitle]);
        }

        return view('super-admin.invoice-settings.index', $this->data);
    }

    /**
     * @param UpdateInvoiceSetting $request
     * @return array
     * @throws \Froiden\RestAPI\Exceptions\RelatedResourceNotFoundException
     * @throws \Illuminate\Contracts\Filesystem\FileNotFoundException
     */
    public function update(UpdateInvoiceSetting $request)
    {
        $setting = GlobalInvoiceSetting::first();
        $setting->template              = $request->template;
        $setting->locale                = $request->locale;
        $setting->authorised_signatory   = $request->has('show_authorised_signatory') ? 1 : 0;
        $setting->invoice_terms         = $request->invoice_terms;

        if ($request->hasFile('logo')) {
            Files::deleteFile($setting->logo, 'app-logo');
            $setting->logo = Files::upload($request->logo, 'app-logo');
        }

        if ($request->hasFile('authorised_signatory_signature')) {
            Files::deleteFile($setting->authorised_signatory_signature, 'app-logo');
            $setting->authorised_signatory_signature = Files::upload($request->authorised_signatory_signature, 'app-logo');
        }

        $setting->save();

        cache()->forget('global_invoice_setting');

        return Reply::success(__('messages.updateSuccess'));
    }

}
