
<div class="row">
    <div class="col-sm-12">
        <x-form id="update-company-package-form" method="PUT">

            <div class="add-company bg-white rounded">
                <h4 class="mb-0 p-20 f-21 font-weight-normal text-capitalize border-bottom-grey">@lang('app.change') @lang('superadmin.company') {{__('superadmin.package')}}</h4>
                <div class="row p-20">
                    <div class="col-md-12 mb-2">
                        <x-company :company="$company" />
                    </div>

                    <div class="col-md-4">
                        <x-forms.select fieldId="package" :fieldLabel="__('superadmin.packages.packages')" search
                                        fieldName="package">
                            @foreach($packages as $package)
                                <option value="{{ $package->id }}"
                                        @if($company->package_id == $package->id) selected @endif >{{ $package->name ?? '' }}
                                        @if($package->is_free) ({{__('superadmin.freePlan') }}) @endif

                                        @if($package->default==='no')
                                            (@lang('app.monthly'): {{global_currency_format($package->monthly_price, $package->currency_id)}},
                                            @lang('app.annually'): {{global_currency_format($package->annual_price, $package->currency_id)}})
                                        @endif

                                </option>
                            @endforeach
                        </x-forms.select>
                    </div>

                    <div class="col-md-4">
                        <x-forms.select fieldId="package_type" :fieldLabel="__('superadmin.packageType')"
                            fieldName="package_type">
                            <option value="monthly" @if($company->package_type == 'monthly') selected @endif>@lang('app.monthly')</option>
                            <option value="annual" @if($company->package_type == 'annual') selected @endif>@lang('app.annually')</option>
                        </x-forms.select>
                    </div>

                    <div class="col-md-4">
                        <x-forms.number fieldId="amount" :fieldLabel="__('app.amount')" fieldName="amount" :fieldValue="(!empty($currentPackage) ? $currentPackage->{$company->package_type . '_price'} : 0)"></x-forms.number>
                    </div>


                    <div class="col-md-4">
                        <x-forms.datepicker fieldId="pay_date" fieldRequired="true"
                            :fieldLabel="__('superadmin.paymentDate')" fieldName="pay_date"
                            :fieldPlaceholder="__('placeholders.date')" />
                    </div>

                    <div class="col-md-4">
                        <x-forms.datepicker fieldId="next_pay_date"
                            :fieldLabel="__('superadmin.nextPaymentDate')" fieldName="next_pay_date"
                            :fieldPlaceholder="__('placeholders.date')" />
                    </div>

                    <div class="col-md-4">
                        <x-forms.text fieldId="licence_expire_on" fieldReadOnly
                            :fieldLabel="__('superadmin.packages.licenseExpiresOn')" fieldName="licence_expire_on" />
                    </div>
                </div>
                <x-form-actions>
                    <x-forms.button-primary id="update-company-package" class="mr-3" icon="check">@lang('app.save')
                    </x-forms.button-primary>
                    <x-forms.button-cancel :link="route('superadmin.companies.index')" class="border-0">@lang('app.cancel')
                    </x-forms.button-cancel>
                </x-form-actions>
            </div>
        </x-form>
    </div>
</div>


<script>
    $(document).ready(function() {
        var packageInfo = @json($packageInfo);

        datepicker('#pay_date', {
            position: 'bl',
            minDate: new Date("{{ str_replace('-', '/', now()->translatedFormat('Y-m-d')) }}"),
            onSelect: function(date) {
                updateExpiryDate()
            },
            ...datepickerConfig
        });

        datepicker('#next_pay_date', {
            position: 'bl',
            minDate: new Date("{{ str_replace('-', '/', now()->translatedFormat('Y-m-d')) }}"),
            ...datepickerConfig
        });

        $('#update-company-package-form').on('change', '#package_type', function () {

            $('#amount').val(packageInfo[$('#update-company-package-form #package').val()][$('#update-company-package-form #package_type').val()]);
            updateExpiryDate();
        });


        $('#update-company-package-form').on('change', '#package', function () {
            $('#amount').val(packageInfo[$('#update-company-package-form #package').val()][$('#update-company-package-form #package_type').val()]);
        });

        function updateExpiryDate() {
            let startDate = moment($("#pay_date").val(), '{{ $global->moment_date_format }}');
            let endDate = startDate.add(1, ($('#package_type').val() === 'monthly') ? 'months' : 'year').format('{{ $global->moment_date_format }}');
            $('#licence_expire_on').val(endDate);
        }

        $('#update-company-package').click(function() {
            const url = "{{ route('superadmin.companies.update_package', [$company->id])}}";

            $.easyAjax({
                url: url,
                container: '#update-company-package-form',
                type: "POST",
                disableButton: true,
                blockUI: true,
                buttonSelector: "#update-company-package",
                data: $('#update-company-package-form').serialize()
            })
        });

        init(RIGHT_MODAL);
    });
</script>
