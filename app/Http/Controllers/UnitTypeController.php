<?php

namespace App\Http\Controllers;

use App\Helper\Reply;
use App\Models\Company;
use App\Models\Invoice;
use App\Models\Product;
use App\Models\Estimate;
use App\Models\Proposal;
use App\Models\UnitType;
use App\Models\BaseModel;
use Illuminate\Http\Request;
use App\Http\Requests\UnitTypeRequest;

class UnitTypeController extends AccountBaseController
{
    /**
     * Display a listing of the resource.p
     *
     * @return \Illuminate\Http\Response
     */

    public function __construct()
    {
        parent::__construct();
        $this->pageTitle = 'app.menu.financeSettings';
        $this->activeSettingMenu = 'invoice_settings';
    }

    public function index()
    {
        return view('invoice-settings.ajax.units');
    }

    public function create()
    {
        return view('invoice-settings.ajax.unit-type');
    }

    public function store(UnitTypeRequest $request)
    {
        $this->addPermission = user()->permission('manage_project_category');
        abort_403(!in_array($this->addPermission, ['all', 'added']));

        $unit_type = new UnitType();
        $unit_type->unit_type = $request->unit_type;
        $unit_type->save();

        $unit_types = UnitType::all();

        $options = BaseModel::options($unit_types, $unit_type, 'unit_type');

        return Reply::successWithData(__('messages.unitTypeAdded'), ['data' => $options]);

    }

    public function edit($id)
    {
        $this->unitType = UnitType::findOrFail($id);
        return view('invoice-settings.ajax.unit-edit', $this->data);
    }

    public function setDefaultUnit()
    {
        UnitType::where('default', 1)->update(['default' => 0]);

        $unitType = UnitType::findOrFail(request()->unitID);
        $unitType->default = 1;
        $unitType->save();
        session()->forget('invoice_setting');
        return Reply::success(__('messages.updateSuccess'));
    }

    public function update(UnitTypeRequest $request, $id)
    {
        $unitType = UnitType::findOrFail($id);
        $unitType->unit_type = strip_tags($request->unit_type);
        $unitType->save();

        $categories = UnitType::all();
        $options = BaseModel::options($categories, null, 'unit_type');

        return Reply::successWithData(__('messages.updateSuccess'), ['data' => $options]);
    }

    public function destroy($id)
    {

        $unitData = UnitType::where('company_id', company()->id)
        ->where('default', 1)->first();

        Product::where('company_id', company()->id)
        ->where('unit_id', $id)->update(['unit_id' => $unitData->id]);

        Invoice::where('company_id', company()->id)
        ->where('unit_id', $id)->update(['unit_id' => $unitData->id]);

        Proposal::where('company_id', company()->id)
        ->where('unit_id', $id)->update(['unit_id' => $unitData->id]);

        Estimate::where('company_id', company()->id)
        ->where('unit_id', $id)->update(['unit_id' => $unitData->id]);

        UnitType::destroy($id);

        return Reply::successWithData(__('messages.deleteSuccess'), ['data' => $unitData]);

    }

    public function units()
    {
        $this->unitTypes = UnitType::all();
        return view('invoice-settings.ajax.units', $this->data);
    }

}
