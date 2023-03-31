<x-auth>
    <form id="login-form" action="{{ route('login') }}" class="ajax-form" method="POST">
        {{ csrf_field() }}
        <h3 class="text-capitalize mb-4 f-w-500">@lang('app.signUp')</h3>

        @if ($errors->any())
            <div class="alert alert-danger">
                <ul>
                    @foreach ($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        <div class="form-group text-left">
            <label for="company_name">@lang('modules.client.companyName')</label>
            <input type="text" tabindex="1" name="company_name"
                   class="form-control height-50 f-14 light_text"
                   placeholder="@lang('placeholders.company')" id="company_name" autofocus>
        </div>

        @if(module_enabled('Subdomain'))
            <div class="form-group text-left">
                <label for="company_name clearfix">{{ __('subdomain::app.core.subdomain') }}</label>
                <div class="input-group">
                    <input type="text" class="form-control height-50 f-15 light_text" placeholder="subdomain"
                           name="sub_domain" id="sub_domain">
                    <div class="input-group-append">
                                                <span class="input-group-text"
                                                      id="basic-addon2">.{{ getDomain() }}</span>
                    </div>
                </div>
            </div>
        @endif

        <div class="form-group text-left">
            <label for="name">@lang('modules.profile.yourName') <sup class="f-14 mr-1">*</sup></label>
            <input type="text" tabindex="4" name="name"
                   class="form-control height-50 f-15 light_text"
                   placeholder="@lang('placeholders.name')" id="name" >
        </div>

        <div class="form-group text-left">
            <label for="email">@lang('auth.email') <sup class="f-14 mr-1">*</sup></label>
            <input tabindex="4" type="email" name="email"
                   class="form-control height-50 f-15 light_text"
                   placeholder="e.g. admin@example.com" id="email">
            <input type="hidden" id="g_recaptcha" name="g_recaptcha">
        </div>

        <div class="form-group text-left">
            <label for="password">@lang('app.password') <sup class="f-14 mr-1">*</sup> </label>
            <x-forms.input-group>
                <input type="password" name="password" id="password"
                       placeholder="@lang('placeholders.password')" tabindex="5"
                       class="form-control height-50 f-15 light_text">
                <x-slot name="append">
                    <button type="button" tabindex="6" data-toggle="tooltip"
                            data-original-title="@lang('app.viewPassword')"
                            class="btn btn-outline-secondary border-grey height-50 toggle-password">
                        <i
                            class="fa fa-eye"></i></button>
                </x-slot>
            </x-forms.input-group>
        </div>


        @if ($global->google_recaptcha_status == 'active' && $global->google_recaptcha_v2_status == 'active')
            <div class="form-group" id="captcha_container"></div>
        @endif

        @if ($errors->has('g-recaptcha-response'))
            <div class="help-block with-errors">{{ $errors->first('g-recaptcha-response') }}
            </div>
        @endif

        <button type="button" id="submit-register"
                class="btn-primary f-w-500 rounded w-100 height-50 f-18">
            @lang('app.signUp') <i class="fa fa-arrow-right pl-1"></i>
        </button>

        <a href="{{ route('login') }}"
           class="btn-secondary f-w-500 rounded w-100 height-50 f-15 mt-3">
            @lang('app.login')
        </a>
    </form>

    <x-slot name="scripts">

        <script>
            $(document).ready(function () {

                $('#submit-register').click(function () {

                    const url = "{{ route('front.signup.store') }}";

                    $.easyAjax({
                        url: url,
                        container: '.login_box',
                        disableButton: true,
                        buttonSelector: "#submit-register",
                        type: "POST",
                        blockUI: true,
                        messagePosition: "pop",
                        data: $('#login-form').serialize(),
                        success: function (response) {
                            if (response.status == 'success') {
                                $('#login-form')[0].reset();
                            }
                        }
                    })
                });

                @if (session('message'))
                Swal.fire({
                    icon: 'error',
                    text: '{{ session('message') }}',
                    showConfirmButton: true,
                    customClass: {
                        confirmButton: 'btn btn-primary',
                    },
                    showClass: {
                        popup: 'swal2-noanimation',
                        backdrop: 'swal2-noanimation'
                    },
                })
                @endif

            });
        </script>
    </x-slot>

</x-auth>
