@extends('super-admin.layouts.saas-app')
@section('header-section') @endsection

@section('content')
    <section class="sp-100 login-section" id="section-contact">
        <div class="container">
            @if($registrationStatus->registration_open == 1)
                <div class="login-box mt-5 shadow bg-white form-section">
                    <h4 class="mb-0 text-left text-uppercase">
                        @lang('app.signUp')
                    </h4>
                    {!! Form::open(['id'=>'register', 'method'=>'POST']) !!}
                    <div class="row">
                        <div class="col-12">
                            <div id="alert"></div>
                        </div>
                    </div>
                    <div id="form-box">
                        <div class="row">


                            <div class="col-12">
                                <div class="form-group mb-4">
                                    <label for="company_name">{{ __('modules.client.companyName') }}</label>
                                    <input type="text" name="company_name" id="company_name"
                                           placeholder="{{ __('modules.client.companyName') }}" class="form-control">
                                </div>
                            </div>
                            @if(module_enabled('Subdomain'))
                                <div class="col-12">
                                    <div class="form-group">
                                        <label for="company_name clearfix">{{ __('subdomain::app.core.subdomain') }}</label>
                                        <div class="input-group">
                                            <input type="text" class="form-control" placeholder="subdomain"
                                                   name="sub_domain" id="sub_domain">
                                            <div class="input-group-append">
                                                <span class="input-group-text"
                                                      id="basic-addon2">.{{ getDomain() }}</span>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            @endif
                        </div>

                        <div class="row">
                            <div class="col-6">
                                <div class="form-group mb-4">
                                    <label for="email">{{ __('modules.profile.yourName') }}</label>
                                    <input type="text" name="name" id="name" placeholder="{{ __('placeholders.name') }}"
                                           class="form-control">
                                </div>
                            </div>
                            <div class="col-6">
                                <div class="form-group mb-4">
                                    <label for="email">{{ __('modules.profile.yourEmail') }}</label>
                                    <input type="email" name="email" id="email"
                                           placeholder="{{ __('placeholders.email') }}" class="form-control">
                                </div>
                            </div>
                        </div>
                        <div class="row">
                            <div class="col-6">
                                <div class="form-group mb-4">
                                    <label for="password">{{__('modules.client.password')}}</label>
                                    <input type="password" class="form-control " id="password" name="password"
                                           placeholder="{{__('modules.client.password')}}">
                                </div>
                            </div>
                            <div class="col-6">
                                <div class="form-group mb-4">
                                    <label for="password_confirmation">{{__('app.confirmPassword')}}</label>
                                    <input type="password" class="form-control" id="password_confirmation"
                                           name="password_confirmation" placeholder="{{__('app.confirmPassword')}}">
                                </div>
                            </div>

                            <div class="col-12">
                                @if ($global->google_recaptcha_status == 'active' && $global->google_recaptcha_v2_status == 'active')
                                    <div class="form-group" id="captcha_container"></div>
                                @endif

                                <input type="hidden" id="g_recaptcha" name="g_recaptcha">

                                <button type="button"
                                        class="btn btn-custom btn-rounded text-uppercase waves-effect waves-light"
                                        id="submit-form">
                                    @lang('app.signUp')
                                </button>
                            </div>
                        </div>
                    </div>
                    {!! Form::close() !!}
                </div>
            @else
                <div class="login-box mt-5 form-section register-message">
                    <h5 class="mb-0 text-center">
                        {!! $signUpMessage->message !!}
                    </h5>
                </div>

            @endif
        </div>
    </section>
@endsection
@push('footer-script')
    <script>

        $('#submit-form').click(function () {

            $.easyAjax({
                url: '{{route('front.signup.store')}}',
                container: '.form-section',
                type: "POST",
                data: $('#register').serialize(),
                blockUI: true,
                disableButton: true,
                buttonSelector: "#submit-form",
                messagePosition: "inline",

                success: function (response) {
                    if (response.status === 'success') {
                        $('#form-box').remove();
                    } else if (response.status === 'fail') {

                        @if($global->google_recaptcha_status)
                        grecaptcha.reset();
                        @endif

                    }
                },
            })

        });
    </script>
    @if ($global->google_recaptcha_status == 'active' && $global->google_recaptcha_v2_status == 'active')
        <script src="https://www.google.com/recaptcha/api.js?onload=onloadCallback&render=explicit" async
                defer></script>
        <script>
            var gcv3;
            var onloadCallback = function () {
                // Renders the HTML element with id 'captcha_container' as a reCAPTCHA widget.
                // The id of the reCAPTCHA widget is assigned to 'gcv3'.
                gcv3 = grecaptcha.render('captcha_container', {
                    'sitekey': '{{ $global->google_recaptcha_v2_site_key }}',
                    'theme': 'light',
                    'callback': function (response) {
                        if (response) {
                            $('#g_recaptcha').val(response);
                        }
                    },
                });
            };
        </script>
    @endif
    @if ($global->google_recaptcha_status == 'active' && $global->google_recaptcha_v3_status == 'active')
        <script
            src="https://www.google.com/recaptcha/api.js?render={{ $global->google_recaptcha_v3_site_key }}"></script>
        <script>
            grecaptcha.ready(function () {
                grecaptcha.execute('{{ $global->google_recaptcha_v3_site_key }}').then(function (token) {
                    // Add your logic to submit to your backend server here.
                    $('#g_recaptcha').val(token);
                });
            });
        </script>
    @endif
@endpush
