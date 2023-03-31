<x-auth>

    <div class="card">
        <div class="card-header">{{ __('Verify Your Email Address') }}</div>

        <div class="card-body">
            @if (session('status') == 'verification-link-sent')
                <div class="mb-4 font-medium text-sm text-success">
                    A new email verification link has been emailed to you!
                </div>
            @endif

            {{ __('Before proceeding, please check your email for a verification link.') }}
            {{ __('If you did not receive the email') }},
            <form class="d-inline" method="POST" action="{{ route('verification.send') }}">
                @csrf
                <button type="submit"
                        class="btn-primary rounded f-14 p-2 mt-3 align-baseline">{{ __('click here to request another') }}</button>
                <button type="button" class="btn-danger rounded f-14 p-2 mt-3 align-baseline" onclick="event.preventDefault();
                    document.getElementById('logout-form').submit();"><i
                        class="fa fa-power-off f-16 mr-1"></i> {{__('app.logout')}}</button>

                {{-- WORKSUITESAAS --}}
                @if(session('impersonate') && isWorksuiteSaas())
                        <x-forms.link-primary icon="stop" data-toggle="tooltip"

                                              data-original-title="{{ __('superadmin.stopImpersonationTooltip') }}"
                                              data-placement="left" :link="route('superadmin.superadmin.stop_impersonate')"
                                              class="btn-primary rounded f-14 p-2 mt-3 align-baseline mr-5">
                            @lang('superadmin.stopImpersonation')
                        </x-forms.link-primary>
                @endif
            </form>
            <form id="logout-form" action="{{ route('logout') }}" method="POST" style="display: none;">
                @csrf
            </form>

        </div>
    </div>

    <x-slot name="scripts"></x-slot>

</x-auth>
