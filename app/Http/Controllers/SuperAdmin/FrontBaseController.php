<?php

namespace App\Http\Controllers\SuperAdmin;

use App\Http\Controllers\Controller;
use App\Models\GlobalSetting;
use App\Models\LanguageSetting;
use App\Models\SuperAdmin\FooterMenu;
use App\Models\SuperAdmin\FrontDetail;
use App\Models\SuperAdmin\FrontMenu;
use App\Models\SuperAdmin\FrontWidget;
use App\Models\SuperAdmin\TrFrontDetail;
use App\Models\User;

class FrontBaseController extends Controller
{

    public function __construct()
    {
        parent::__construct();

        $this->showInstall();
        $this->middleware(function ($request, $next) {

            $this->frontDetail = FrontDetail::first();
            $this->languages = LanguageSetting::where('status', 'enabled')->get();
            $this->setting = global_setting();
            $this->globalSetting = $this->setting;


            $this->locale = $this->frontDetail->locale;


            if (session()->has('language')) {
                $this->locale = session('language');
            }

            setlocale(LC_TIME, $this->locale . '_' . strtoupper($this->locale));

            $this->enLocaleLanguage = LanguageSetting::where('language_code', 'en')->first();
            $this->localeLanguage = $this->locale != 'en' ? LanguageSetting::where('language_code', $this->locale)->first() : $this->enLocaleLanguage;
            $this->localeLanguage = $this->localeLanguage ?: $this->enLocaleLanguage;

            $this->footerSettings = FooterMenu::whereNotNull('slug')
                ->where('private', 0)
                ->where('language_setting_id', $this->localeLanguage->id)
                ->get();

            $this->footerSettings = $this->footerSettings->count() > 0 ?
                $this->footerSettings :
                FooterMenu::whereNotNull('slug')->where('private', 0)
                    ->where('language_setting_id', $this->enLocaleLanguage->id)
                    ->get();

            $this->frontMenu = FrontMenu::where('language_setting_id', $this->localeLanguage->id)->first();
            $this->frontMenu = $this->frontMenu ?: FrontMenu::where('language_setting_id', $this->enLocaleLanguage->id)->first();

            $this->frontWidgets = FrontWidget::all();

            $this->detail = $this->frontDetail;

            $this->trFrontDetail = TrFrontDetail::where('language_setting_id', $this->localeLanguage->id)->first();
            $this->trFrontDetail = $this->trFrontDetail ?: TrFrontDetail::where('language_setting_id', $this->enLocaleLanguage->id)->first();

            // ACCOUNT SETUP REDIRECT
            $userTotal = User::count();

            if ($userTotal == 0) {
                return redirect()->route('login');
            }

            return $next($request);
        });

    }

}
