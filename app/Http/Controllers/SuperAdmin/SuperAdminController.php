<?php

namespace App\Http\Controllers\SuperAdmin;

use App\Http\Controllers\AppSettingController;
use App\Models\Role;
use App\Models\SmtpSetting;
use App\Models\User;
use App\Helper\Files;
use App\Helper\Reply;
use App\Models\UserAuth;
use App\Scopes\ActiveScope;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use App\Http\Controllers\AccountBaseController;
use App\DataTables\SuperAdmin\SuperAdminDataTable;
use App\Http\Requests\SuperAdmin\SuperAdmin\StoreRequest;
use App\Http\Requests\SuperAdmin\SuperAdmin\UpdateRequest;
use App\Models\Company;
use App\Providers\RouteServiceProvider;
use App\Scopes\CompanyScope;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class SuperAdminController extends AccountBaseController
{

    public function __construct()
    {
        parent::__construct();
        $this->pageTitle = 'superadmin.menu.superAdmin';
    }

    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index(SuperAdminDataTable $dataTable)
    {
        return $dataTable->render('super-admin.super-admin.index', $this->data);
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        $this->pageTitle = __('superadmin.superadmin.create');
        $this->view = 'super-admin.super-admin.ajax.create';

        if (request()->ajax()) {
            $html = view($this->view, $this->data)->render();

            return Reply::dataOnly(['status' => 'success', 'html' => $html, 'title' => $this->pageTitle]);
        }

        return view('super-admin.super-admin.create', $this->data);
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param StoreRequest $request
     * @return \Illuminate\Http\Response
     */
    public function store(StoreRequest $request)
    {
        DB::beginTransaction();

        $userAuth = UserAuth::createUserAuthCredentials($request->email);

        $superAdmin = new User();
        $superAdmin->name = $request->name;
        $superAdmin->is_superadmin = true;
        $superAdmin->email = $request->email;
        $superAdmin->user_auth_id = $userAuth->id;
        $superAdmin->login = 'enable';
        $superAdmin->status = 'active';

        if ($request->hasFile('image')) {
            Files::deleteFile($superAdmin->image, 'avatar');
            $superAdmin->image = Files::upload($request->image, 'avatar', 300);
        }

        $superAdmin->save();


        $userAuth->email_verified_at = now();
        $userAuth->saveQuietly();

        DB::commit();

        return Reply::redirect(route('superadmin.superadmin.index'));
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param int $id
     * @return \Illuminate\Http\Response
     */
    public function edit($id)
    {

        $this->superAdmin = User::withoutGlobalScope(ActiveScope::class)
            ->where('is_superadmin', 1)
            ->whereNull('company_id')
            ->findOrFail($id);

        $this->pageTitle = __('superadmin.superadmin.edit', ['name' => $this->superAdmin->name]);
        $this->view = 'super-admin.super-admin.ajax.edit';

        if (request()->ajax()) {
            $html = view($this->view, $this->data)->render();

            return Reply::dataOnly(['status' => 'success', 'html' => $html, 'title' => $this->pageTitle]);
        }

        return view('super-admin.super-admin.create', $this->data);
    }

    /**
     * Update the specified resource in storage.
     *
     * @param UpdateRequest $request
     * @param int $id
     * @return \Illuminate\Http\Response
     */
    public function update(UpdateRequest $request, $id)
    {
        $superAdmin = User::where('is_superadmin', 1)->whereNull('company_id')->findOrFail($id);

        $superAdmin->name = $request->name;
        $superAdmin->email = $request->email;

        $emailCountInCompanies = User::withoutGlobalScopes([ActiveScope::class, CompanyScope::class])
            ->where('email', $superAdmin->email)
            ->count();

        if ($emailCountInCompanies > 1) {
            return Reply::error(__('messages.emailCannotChange'));
        }

        // Update email in userauth also
        $superAdmin->userAuth()->update(['email' => $request->email]);

        if ($this->user->id != $superAdmin->id) {
            $superAdmin->status = $request->status;
        }

        if ($request->hasFile('image')) {
            Files::deleteFile($superAdmin->image, 'avatar');
            $superAdmin->image = Files::upload($request->image, 'avatar', 300);
        }

        $superAdmin->save();

        return Reply::redirect(route('superadmin.superadmin.index'));
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param int $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
        $totalSuperadmin = User::withoutGlobalScopes([CompanyScope::class, ActiveScope::class])
            ->where('is_superadmin', 1)
            ->whereNull('company_id')
            ->count();

        if ($totalSuperadmin == 1) {
            return Reply::error('We require one superadmin for your account. To remove this superadmin, please add an additional superadmin');
        }

        $user = User::withoutGlobalScopes([CompanyScope::class, ActiveScope::class])
            ->where('is_superadmin', 1)
            ->whereNull('company_id')
            ->findOrFail($id);
        $user->delete();

        return Reply::success(__('messages.deleteSuccess'));
    }

    public function stopImpersonate()
    {
        $userAuthId = session('impersonate');
        $companyId = session('impersonate_company_id');
        session()->flush();
        Auth::logout();

        session(['stop_impersonate' => $userAuthId]);
        Auth::loginUsingId($userAuthId);

        return redirect(route('superadmin.companies.show', $companyId));
    }

    public function workspaces()
    {
        if (session()->has('multi_company_selected')) {
            return redirect(route('dashboard'));
        }

        $this->userCompanies = User::withoutGlobalScope(CompanyScope::class)->where('email', user()->email)
            ->with('company')->select('id', 'company_id', 'login')->get();

        return view('super-admin.workspaces', $this->data);
    }

    public function chooseWorkspace(Request $request)
    {
        $userId = $request->user_id;
        $companyId = $request->company_id;

        $company = Company::findOrFail($companyId);
        $user = User::withoutGlobalScope(CompanyScope::class)->findOrFail($userId);

        if ($user->login == 'disable') {
            return Reply::error(__('superadmin.loginRestricted'));
        }

        session(['company' => $company]);
        session(['multi_company_selected' => true]);
        session(['user' => $user]);

        return Reply::dataOnly(['status' => 'success', 'redirect_url' => RouteServiceProvider::HOME]);
    }

}
