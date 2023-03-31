@extends('layouts.app')

@push('styles')
    <style>
        .package-value {
            background-color: rgba(0, 0, 0, 0.075);
            text-align: center;
        }
    </style>
@endpush

@section('content')

<div class="content-wrapper">

    <div class="row">

        <div class="col-12 mb-2 mt-1 text-center">
            @if (session('success'))
                <div class="alert alert-success">{{ session('success') }}</div>
                <?php Session::forget('success');?>
            @endif
            @if (session('error'))
                <div class="alert alert-danger">{{ session('error') }}</div>
                <?php Session::forget('error');?>
            @endif

            <div class="btn-group" role="group" aria-label="Basic example">
                <button type="button" class="btn btn-secondary f-16 btn-active monthly package-type" data-package-type="monthly">@lang('app.monthly')</button>

                <button type="button" class="btn btn-secondary f-16 annually package-type" data-package-type="annual">@lang('app.annually')</button>
            </div>
        </div>

        <div class="col-sm-12">
            <x-cards.data>
                <div class="table-responsive">

                    <table class="w-100 table-hover">
                        <thead>
                            <th colspan="2" class="px-4 pb-1 pt-2">&nbsp;</th>
                            @foreach($packages as $package)
                                <th @class([
                                    'package-value',
                                    '  f-16 btlr btrr'
                                ])>
                                    {{ucfirst($package->name)}}
                                </th>
                                @if(!$loop->last)
                                    <td class="px-2 py-1 w-15"></td>
                                @endif
                            @endforeach
                        </thead>
                        <tbody>
                            <tr>
                                <td colspan="2" class="px-2 py-1">&nbsp;</td>
                                @foreach($packages as $package)
                                    <td @class([
                                        'monthly-plan' => $package->monthly_status == '1',
                                        'package-value',
                                        'px-4 pb-2 f-16 font-weight-bold'
                                    ])>
                                    {{ global_currency_format($package->monthly_price, $package->currency_id) }}
                                    </td>
                                    @if(!$loop->last)
                                        <td @class([
                                            'monthly-plan' => $package->monthly_status == '1',
                                            'px-2 py-1 w-15'
                                        ])></td>
                                    @endif
                                @endforeach

                                @foreach($packages as $package)
                                    <td @class([
                                        'yearly-plan d-none' => $package->annual_status == '1',
                                        'package-value',
                                        'px-4 pb-2 f-16 font-weight-bold'
                                    ])>
                                    {{ global_currency_format($package->annual_price, $package->currency_id) }}
                                    </td>
                                    @if(!$loop->last)
                                        <td @class([
                                            'yearly-plan d-none' => $package->annual_status == '1',
                                            'px-2 py-1 w-15'
                                        ])></td>
                                    @endif
                                @endforeach
                            </tr>
                            <tr>
                                <td colspan="2" class="px-2 py-1">@lang('app.menu.employees')</td>
                                @foreach($packages as $package)
                                    <td @class([
                                        'monthly-plan' => $package->monthly_status == '1',
                                        'package-value',
                                        'px-2 py-1'
                                    ])>@lang('superadmin.maxActiveEmployee', ['maxEmployees' => $package->max_employees])
                                    </td>
                                    @if(!$loop->last)
                                        <td @class([
                                            'monthly-plan' => $package->monthly_status == '1',
                                            'px-2 py-1 w-15'
                                        ])></td>
                                    @endif
                                @endforeach
                                @foreach($packages as $package)
                                    <td @class([
                                        'yearly-plan d-none' => $package->annual_status == '1',
                                        'package-value',
                                        'px-2 py-1'
                                    ])>{{ $package->max_employees }} @lang('modules.projects.members')
                                    </td>
                                    @if(!$loop->last)
                                        <td @class([
                                            'yearly-plan d-none' => $package->annual_status == '1',
                                            'px-2 py-1 w-15'
                                        ])></td>
                                    @endif
                                @endforeach
                            </tr>
                            <tr>
                                <td colspan="2" class="px-2 py-1">@lang('superadmin.fileStorage')</td>
                                @foreach($packages as $package)
                                    <td @class([
                                        'monthly-plan' => $package->monthly_status == '1',
                                        'package-value',
                                        'px-2 py-1'
                                    ])>
                                    @if($package->max_storage_size == -1)
                                        @lang('superadmin.unlimited')
                                    @else
                                        {{ $package->max_storage_size }} {{ strtoupper($package->storage_unit) }}
                                    @endif
                                    </td>
                                    @if(!$loop->last)
                                        <td @class([
                                            'monthly-plan' => $package->monthly_status == '1',
                                            'px-2 py-1 w-15'
                                        ])></td>
                                    @endif
                                @endforeach
                                @foreach($packages as $package)
                                    <td @class([
                                        'yearly-plan d-none' => $package->annual_status == '1',
                                        'package-value',
                                        'px-2 py-1'
                                    ])>
                                    @if($package->max_storage_size == -1)
                                        @lang('superadmin.unlimited')
                                    @else
                                        {{ $package->max_storage_size }} {{ strtoupper($package->storage_unit) }}
                                    @endif
                                    </td>
                                    @if(!$loop->last)
                                        <td @class([
                                            'yearly-plan d-none' => $package->annual_status == '1',
                                            'px-2 py-1 w-15'
                                        ])></td>
                                    @endif
                                @endforeach
                            </tr>

                            @php
                                $moduleArray = [];
                                foreach($modulesData as $module) {
                                    $moduleArray[$module->module_name] = [];
                                }
                            @endphp

                            @foreach($packages as $package)
                            @if($package->monthly_status == '1')
                                @foreach((array)json_decode($package->module_in_package) as $MIP)
                                    @if (array_key_exists($MIP, $moduleArray))
                                        @php $moduleArray[$MIP][] = strtoupper(trim($package->name)); @endphp
                                    @else
                                        @php $moduleArray[$MIP] = [strtoupper(trim($package->name))]; @endphp
                                    @endif
                                @endforeach
                                @endif
                            @endforeach

                            @foreach($moduleArray as $key => $module)
                            <tr>
                                <td colspan="2" @class([
                                    'monthly-plan' => $package->monthly_status == '1',
                                    'px-2 py-1'
                                ])>
                                    @php

                                        $moduleNameNew = strval("modules.module.$key");
                                        $trans = __($moduleNameNew);

                                    @endphp

                                    @if(is_array($key))
                                        @lang($trans)
                                    @else
                                        {{ $trans }}
                                    @endif
                                </td>
                                @foreach($packages as $package)
                                <td @class([
                                    'monthly-plan' => $package->monthly_status == '1',
                                    'package-value',
                                    'px-2 py-1 f-16'
                                ])>
                                    @php $available = in_array(strtoupper(trim($package->name)), $module); @endphp
                                    <i class="bi {{ $available ? 'bi-check-circle text-success' : 'bi-x-circle text-danger'}}"></i>
                                </td>
                                    @if(!$loop->last)
                                        <td @class([
                                            'monthly-plan' => $package->monthly_status == '1',
                                            'px-2 py-1 w-15'
                                        ])></td>
                                    @endif
                                @endforeach
                            </tr>
                            @endforeach

                            @php
                                $moduleArray = [];
                                foreach($modulesData as $module) {
                                    $moduleArray[$module->module_name] = [];
                                }
                            @endphp

                            @foreach($packages as $package)
                            @if($package->annual_status == '1')
                                @foreach((array)json_decode($package->module_in_package) as $MIP)
                                    @if (array_key_exists($MIP, $moduleArray))
                                        @php $moduleArray[$MIP][] = strtoupper(trim($package->name)); @endphp
                                    @else
                                        @php $moduleArray[$MIP] = [strtoupper(trim($package->name))]; @endphp
                                    @endif
                                @endforeach
                                @endif
                            @endforeach

                            @foreach($moduleArray as $key => $module)
                            <tr>
                                <td colspan="2" @class([
                                        'yearly-plan d-none' => $package->annual_status == '1',
                                        'px-2 py-1'
                                    ])>
                                    @php

                                        $moduleNameNew = strval("modules.module.$key");
                                        $trans = __($moduleNameNew);

                                    @endphp

                                    @if(is_array($key))
                                        @lang($trans)
                                    @else
                                        {{ $trans }}
                                    @endif
                                </td>
                                @foreach($packages as $package)
                                <td @class([
                                    'yearly-plan d-none' => $package->annual_status == '1',
                                    'package-value',
                                    'px-2 py-1 f-16'
                                ])>
                                    @php $available = in_array(strtoupper(trim($package->name)), $module); @endphp
                                    <i class="bi {{ $available ? 'bi-check-circle text-success' : 'bi-x-circle text-danger'}}"></i>
                                </td>
                                    @if(!$loop->last)
                                        <td @class([
                                            'yearly-plan d-none' => $package->annual_status == '1',
                                            'px-2 py-1 w-15'
                                        ])></td>
                                    @endif
                                @endforeach
                            </tr>
                            @endforeach
                        </tbody>
                        <tfoot>
                            <tr>

                                <td colspan="2" class="px-2 py-1">&nbsp;</td>
                                @foreach($packages as $package)
                                    @if($paymentActive)
                                        <td @class([
                                                'px-2 py-1 f-18 text-center'
                                            ])>
                                            @if(($package->is_free || $paymentActive))
                                                <x-forms.button-primary @class(['purchase-plan', 'd-none' => $package->default != 'no' && company()->package_id != $package->id]) data-package-id="{{ $package->id }}" id="purchase-plan">@lang('superadmin.packages.choosePlan')</x-forms.button-primary>
                                            @endif
                                        </td>
                                        <td class="px-2 py-1 w-15"></td>
                                    @else
                                        @if($package->default == 'yes')
                                        <td @class([
                                                'px-2 py-1 f-18 text-center'
                                            ])>
                                            <x-forms.button-primary @class(['purchase-plan', 'd-none' => company()->package_id != $package->id]) data-package-id="{{ $package->id }}" id="purchase-plan">@lang('superadmin.packages.choosePlan')</x-forms.button-primary>
                                        </td>
                                        @endif
                                        @if($loop->first)
                                                <td colspan="8" class="px-2 py-1 text-center">@lang('superadmin.noPaymentOptionEnable')</td>
                                        @endif
                                    @endif
                                @endforeach

                            </tr>
                        </tfoot>
                    </table>
                </div>
            </x-cards.data>
        </div>
    </div>
</div>

@endsection

@push('scripts')
    <script src="https://checkout.razorpay.com/v1/checkout.js"></script>
    <script src="https://js.stripe.com/v3/"></script>
    <script>
         $('.monthly').click(function() {
            $('.annually').removeClass('btn-active');
            $('.monthly-plan').removeClass('d-none');
            $('.yearly-plan').addClass('d-none');
            $(this).addClass('btn-active');
             deactivateCurrentPackageButton();
        });
         $('.annually').click(function() {
            $('.monthly').removeClass('btn-active');
            $('.yearly-plan').removeClass('d-none');
            $('.monthly-plan').addClass('d-none');
            $(this).addClass('btn-active');
             deactivateCurrentPackageButton();
        });

        $('.purchase-plan').click(function() {
            var packageId = $(this).data('package-id');
            var packageType = $('.package-type.btn-active').data('package-type');

            var url = "{{ route('billing.select-package',':id') }}?type=" + packageType;
            url = url.replace(':id', packageId);
            $(MODAL_LG + ' ' + MODAL_HEADING).html('...');
            $.ajaxModal(MODAL_LG, url);
            // $.ajaxModal('#package-select-form', url);
        });

        $(document).ready(function() {
            deactivateCurrentPackageButton();
        });
        function deactivateCurrentPackageButton()
        {
            var packageType = $('.package-type.btn-active').data('package-type');
            if(packageType == '{{company()->package_type}}'){
                $('.purchase-plan').each(function() {
                    if($(this).data('package-id') == '{{company()->package_id}}'){
                        $(this).attr('disabled', true);
                        $(this).html('Current Plan');
                    }
                });
            }
            else{
                $('.purchase-plan').each(function() {
                    if($(this).data('package-id') == '{{company()->package_id}}'){
                        $(this).attr('disabled', false);
                        $(this).html('Choose Plan');
                    }
                });
            }
        }
    </script>
@endpush
