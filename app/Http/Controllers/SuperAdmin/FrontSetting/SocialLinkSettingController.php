<?php

namespace App\Http\Controllers\SuperAdmin\FrontSetting;

use App\Helper\Reply;
use Illuminate\Http\Request;
use App\Http\Controllers\AccountBaseController;
use App\Models\LanguageSetting;
use App\Models\SuperAdmin\FrontDetail;
use App\Models\SuperAdmin\TrFrontDetail;
use Illuminate\Support\Arr;
use App\Http\Requests\SuperAdmin\FrontSetting\UpdateFrontSettings;

class SocialLinkSettingController extends AccountBaseController
{

    public function __construct()
    {
        parent::__construct();
        $this->pageTitle = 'superadmin.frontCms.socialLinks';
        $this->activeSettingMenu = 'social_link';
    }

    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function socialLink()
    {
        $this->pageTitle = 'superadmin.frontCms.socialLinks';

        $this->frontDetail = FrontDetail::first();

        return view('super-admin.front-setting.social-links.index', $this->data);
    }

    public function socialLinkUpdate(UpdateFrontSettings $request)
    {
        $setting = FrontDetail::findOrFail($request->linkId);

        $links = [];

        foreach ($request->social_links as $name => $value) {
            $link_details = [];
            $link_details = Arr::add($link_details, 'name', $name);
            $link_details = Arr::add($link_details, 'link', $value);
            array_push($links, $link_details);
        }

        $setting->social_links = json_encode($links);

        $setting->save();

        return Reply::success(__('messages.updateSuccess'));
    }

}
