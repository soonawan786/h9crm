@extends('layouts.app')
@push('styles')
    <style>
        .form_custom_label {
            justify-content: left;
        }

        .ace_gutter {
            z-index: 1 !important;
        }

        .thumbnail.selected p {
            color: white;
            text-align: center;
            margin-top: 10px;
            font-weight: bold;
        }

        .thumbnail p {
            text-align: center;
            margin-top: 10px;
            font-weight: bolder;
        }
    </style>
    <link rel="stylesheet" href="{{ asset('vendor/css/bootstrap-colorpicker.css') }}"/>
    <link rel="stylesheet" href="{{ asset('vendor/css/image-picker.min.css') }}">
@endpush
@section('content')
    <!-- SETTINGS START -->
    <div class="w-100 d-flex">

        <x-super-admin.front-setting-sidebar :activeMenu="$activeSettingMenu"/>

        <x-setting-card>

            <x-slot name="header">
                <div class="s-b-n-header" id="tabs">
                    <h2 class="f-21 font-weight-normal text-capitalize border-bottom-grey mb-0 p-20">
                        @lang($pageTitle)</h2>
                </div>
            </x-slot>

            <!-- LEAVE SETTING START -->
            <div class="col-lg-12 col-md-12 ntfcn-tab-content-left w-100 p-4">
                <div class="row">
                    <div class="col-lg-12">
                        <div class="form-group">
                            <x-forms.label fieldId="theme"
                                           :fieldLabel="__('superadmin.selectTheme')" fieldRequired="true">
                            </x-forms.label>
                            <select name="theme" class="image-picker image-picker-theme show-labels show-html">
                                <option data-img-src="{{ asset('img/old-design.jpg') }}"
                                        @if ($global->front_design == 0) selected @endif value="0">
                                    @lang('superadmin.theme1')
                                </option>

                                <option data-img-src="{{ asset('img/new-design.jpg') }}" data-toggle="tooltip"
                                        data-original-title="Edit" @if ($global->front_design == 1) selected @endif
                                        value="1">@lang('superadmin.theme2')
                                </option>
                            </select>
                        </div>
                    </div>

                    @if (!module_enabled('Subdomain'))
                        <div class="col-lg-12" id="login_ui_box">
                            <div class="form-group">
                                <x-forms.label fieldId="login_ui"
                                               :fieldLabel="__('app.login'). ' ' .__('superadmin.theme')"
                                               fieldRequired="true">
                                </x-forms.label>
                                <select name="login_ui" id="login_ui"
                                        class="image-picker show-labels show-html login-theme" style="color: white">
                                    <option data-img-src="{{ asset('img/old-login.jpg') }}"
                                            @if ($global->login_ui == 0) selected @endif value="0">
                                        @lang('superadmin.theme1')
                                    </option>

                                    <option data-img-src="{{ asset('img/new-login.jpg') }}" data-toggle="tooltip"
                                            data-original-title="Edit" @if ($global->login_ui == 1) selected @endif
                                            value="1">@lang('superadmin.theme2')
                                    </option>

                                </select>
                            </div>
                        </div>
                    @endif
                    <div class="col-lg-4 pt-5">
                        <x-forms.checkbox :checked="$global->frontend_disable"
                                          :fieldLabel="__('superadmin.superadmin.disableFrontendSite')"
                                          :popover="__('superadmin.frontDisableInfo')"
                                          fieldName="frontend_disable" fieldId="frontend_disable"/>
                    </div>

                    <div class="col-lg-4 @if ($global->frontend_disable)  d-none @endif" id="set-homepage-div">
                        <x-forms.select fieldId="setup_homepage" :fieldLabel="__('superadmin.superadmin.setupHomepage')"
                                        fieldName="setup_homepage">
                            <option @if ($global->setup_homepage == 'default') selected @endif value="default">
                                @lang('superadmin.superadmin.defaultLanding')</option>
                            <option @if ($global->setup_homepage == 'signup') selected @endif value="signup">
                                @lang('app.signUp')</option>
                            <option @if ($global->setup_homepage == 'login') selected @endif value="login">
                                @lang('app.login')</option>
                            <option @if ($global->setup_homepage == 'custom') selected @endif value="custom">
                                @lang('superadmin.superadmin.loadCustomUrl')</option>
                        </x-forms.select>
                    </div>
                    <div class="col-lg-6">
                        <x-forms.select fieldId="default_language"
                                        :popover="__('superadmin.defaultLanguagePopover')"
                                        :fieldLabel="__('superadmin.frontCms.defaultLanguage')"
                                        fieldName="default_language">

                            @foreach($languageSettings as $language)
                                <option {{ $frontDetail->locale == $language->language_code ? 'selected' : '' }}
                                        data-content="<span class='flag-icon flag-icon-{{ ($language->language_code == 'en') ? 'gb' : strtolower($language->flag_code) }} flag-icon-squared'></span> {{ $language->language_name }}"
                                        value="{{ $language->language_code }}">{{ $language->language_name }}</option>
                            @endforeach
                        </x-forms.select>
                    </div>
                    <div class="col-lg-6 @if ($global->front_design != 0) d-none @endif" id="primary_color_div">
                        <div class="form-group my-3">
                            <x-forms.label fieldId="primary_color"
                                           :fieldLabel="__('superadmin.frontCms.primaryColor')">
                            </x-forms.label>
                            <x-forms.input-group class="color-picker">
                                <input type="text" class="form-control height-35 f-14 header_color"
                                       autocomplete="off"
                                       value="{{ $frontDetail->primary_color }}" id="primary_color"
                                       placeholder="{{ __('placeholders.colorPicker') }}" name="primary_color">

                                <x-slot name="append">
                                    <span class="input-group-text height-35 colorpicker-input-addon"><i></i></span>
                                </x-slot>
                            </x-forms.input-group>
                        </div>
                    </div>
                    <div
                        class="col-lg-6 @if ($global->frontend_disable || (!$global->frontend_disable && $global->setup_homepage != 'custom')) d-none @endif"
                        id="home_custom_url">
                        <x-forms.text :fieldLabel="__('superadmin.superadmin.customUrl')"
                                      fieldName="custom_homepage_url"
                                      :fieldValue="$global->custom_homepage_url"
                                      fieldId="custom_homepage_url" fieldRequired="true"/>
                    </div>
                </div>
            </div>
            <!-- LEAVE SETTING END -->

            <x-slot name="action">
                <!-- Buttons Start -->
                <div class="w-100 border-top-grey">
                    <x-setting-form-actions>
                        <x-forms.button-primary id="save-form" class="mr-3" icon="check">@lang('app.update')
                        </x-forms.button-primary>
                    </x-setting-form-actions>
                </div>
                <!-- Buttons End -->
            </x-slot>
        </x-setting-card>

    </div>
    <!-- SETTINGS END -->
@endsection

@push('scripts')
    <script src="{{ asset('vendor/jquery/bootstrap-colorpicker.js') }}"></script>
    <script src="{{ asset('vendor/jquery/image-picker.min.js') }}"></script>
    <script>
        $('.color-picker').colorpicker();
        $('.image-picker').imagepicker({
            show_label: true
        });

        $(".image-picker-theme").imagepicker({
            show_label: true,
            changed: function (vale, newval) {
                if (newval == 1) {
                    $('#login_ui_box').removeClass('d-none');
                    $('#primary_color_div').addClass('d-none');
                } else {
                    $('#login_ui_box').addClass('d-none');
                    $('#primary_color_div').removeClass('d-none');
                }
            },
            initialized: function (vale) {
                if ($(".image-picker-theme").val() == 1) {
                    $('#login_ui_box').removeClass('d-none');
                    $('#primary_color_div').addClass('d-none');
                } else {
                    $('#login_ui_box').addClass('d-none');
                    $('#primary_color_div').removeClass('d-none');
                }

            }
        });


        $('#frontend_disable').change(function () {
            if ($(this).is(':checked')) {
                $('#set-homepage-div').addClass('d-none');
                $('#home_custom_url').addClass('d-none');
            } else {
                $('#set-homepage-div').removeClass('d-none');
                if ($('#setup_homepage').val() == 'custom') {
                    $('#home_custom_url').removeClass('d-none');
                }
            }
        });

        $('#setup_homepage').change(function () {
            const homepage = $(this).val();

            if (homepage === "custom") {
                $("#home_custom_url").removeClass('d-none');
            } else {
                $("#home_custom_url").addClass('d-none');
            }
        })

        @if($global->login_ui == 0)
        $(".login-background-box").removeClass('d-none');
        @else
        $(".login-background-box").addClass('d-none');
        @endif

        $('.login-theme').change(function () {
            const theme = $(this).val();

            if (theme == '0') {
                $(".login-background-box").removeClass('d-none');
            } else {
                $(".login-background-box").addClass('d-none');
            }
        })

        $('#save-form').click(function () {
            $.easyAjax({
                url: "{{ route('superadmin.front-settings.front_theme_update') }}",
                container: '#editSettings',
                blockUI: true,
                type: "POST",
                file: true,
                disableButton: true,
                buttonSelector: "#save-form",
                data: $('#editSettings').serialize(),
                success: function (response) {
                    if (response.status == 'success') {
                        window.location.href = response.redirectUrl;
                    }
                }
            })
        });
    </script>
@endpush
