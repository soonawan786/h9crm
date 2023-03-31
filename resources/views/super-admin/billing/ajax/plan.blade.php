<div class="col-lg-12 col-md-12 ntfcn-tab-content-left w-100 p-4 ">

    <div class='card border'>
        <x-cards.card-header>
            <i class="bi bi-box2 mr-2"></i>@lang('superadmin.menu.planDetails')

            <x-slot name="action"></x-slot>

        </x-cards.card-header>

        <div class="card-body">
            <div class="d-flex justify-content-between">
                <div class="align-self-center">
                    <h5 class="heading-h5 font-weight-normal">@lang('superadmin.packages.currentPlan')</h5>

                    <h3 class="heading-h3 mt-2 text-primary">{{ ucfirst($company->package->name) }} @lang('superadmin.'.$company->package_type)</h3>

{{--                    <h5 class="heading-h5 mt-2 text-lightest">@lang('superadmin.packages.licenseExpiresOn')--}}
{{--                        @if (!is_null($company->licence_expire_on))--}}
{{--                            <span class="font-weight-bold">--}}
{{--                                {{ \Carbon\Carbon::parse($company->licence_expire_on)->timezone(global_setting()->timezone)->format(global_setting()->date_format) }}--}}
{{--                            </span>--}}
{{--                            <em>({{ \Carbon\Carbon::parse($company->licence_expire_on)->diffForHumans() }})</em>--}}
{{--                        @else--}}
{{--                            ----}}
{{--                        @endif--}}
{{--                    </h5>--}}
                </div>

                <div class="w-50">
                    @php
                        $storage = __('superadmin.notUsed');
                        $storageUsed = 0;
                        if ($company->file_storage_count && $company->file_storage_sum_size) {
                            if ($company->package->storage_unit == 'mb') {
                                $storage = \App\Models\SuperAdmin\Package::bytesToMB($company->file_storage_sum_size) . ' ' . __('superadmin.mb');
                                $storageUsed = App\Models\SuperAdmin\Package::bytesToGB($company->file_storage_sum_size);
                            } else {
                                $storage = round($company->file_storage_sum_size / (1000 * 1024 * 1024), 3) . ' ' . __('superadmin.mb');
                                $storageUsed = round($company->file_storage_sum_size / (1000 * 1024 * 1024), 3);
                            }
                        }

                        $maxStorage = __('superadmin.unlimited');
                        if ($company->package->max_storage_size != -1) {
                            $maxStorage = $company->package->max_storage_size . ' ' . strtoupper($company->package->storage_unit);

                            $storageUsePercent = 0;
                            if ($storageUsed > 0 && $company->package->max_storage_size > 0) {
                                $storageUsePercent = floor((($storageUsed/$company->package->max_storage_size) * 100));
                            }
                        }

                    @endphp
                     <ul class="list-group">
                        <li
                            class="list-group-item d-flex justify-content-between align-items-center f-12 text-dark-grey">
                            <span>@lang('app.menu.employees')</span>
                            <span>{{ $company->employees_count . ' / ' . $company->package->max_employees }}</span>
                        </li>
                        <li
                            class="list-group-item d-flex justify-content-between align-items-center f-12 text-dark-grey">
                            <span>@lang('superadmin.storage')</span>
                            <span>
                                @if ($company->package->max_storage_size != -1)
                                    <div class="progress">
                                        <div class="progress-bar f-10" role="progressbar" style="width: {{ $storageUsePercent }}%;" aria-valuenow="{{ $storageUsed }}" aria-valuemin="0" aria-valuemax="{{ $company->package->max_storage_size }}">{{ $storageUsePercent }}%</div>
                                    </div>
                                @endif
                                <span class="f-11">{{ $storage . ' / ' . $maxStorage }}</span>
                            </span>
                        </li>
                     </ul>
                </div>

                <div class="align-self-center">
                    <a href="{{ route('billing.upgrade_plan') }}" class='btn-primary btn btn-lg rounded'>
                        <i class="bi bi-stars"></i> @lang('superadmin.packages.upgradePlan')
                    </a>
                </div>
            </div>
        </div>
    </div>


</div>
