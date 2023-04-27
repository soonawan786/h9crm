@extends('layouts.app')

@section('content')

    <!-- SETTINGS START -->


    <div class="w-100 d-flex ">

        <x-setting-sidebar :activeMenu="$activeSettingMenu"/>

        <div class="settings-box bg-additional-grey rounded">

            @isset($alert) {{ $alert }} @endisset
            @isset($buttons) {{ $buttons }} @endisset

            <form id="customeSetting"  class="ajax-form">
                @csrf

                <input type="hidden" id="redirect_url" name="redirect_url" value="{{ route('woo.create') }}">

                <div class="s-b-inner s-b-notifications bg-white b-shadow-4 rounded">

                    <div class="s-b-n-content">
                        <div class="tab-content" id="nav-tabContent">
                            <!--  TAB CONTENT START -->
                            <div class="tab-pane fade show active" id="nav-email" role="tabpanel" aria-labelledby="nav-email-tab">
                                <div class="d-flex flex-wrap justify-content-between">
                                    <div class="col-lg-12 col-md-12 ntfcn-tab-content-left w-100 p-4 ">
                                        <div class="row">
                                            <div class="col-lg-12">
                                                <div class="form-group my-3 mr-0 mr-lg-2 mr-md-2">
                                                    <label class="f-14 text-dark-grey mb-12" data-label="true" for="website_url">Website URL
                                                            <sup class="f-14 mr-1">*</sup>

                                                            <svg class="svg-inline--fa fa-question-circle fa-w-16" data-toggle="popover" data-placement="top" data-content="Add Website Url" data-html="true" data-trigger="hover" aria-hidden="true" focusable="false" data-prefix="fa" data-icon="question-circle" role="img" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 512 512" data-fa-i2svg="" data-original-title="" title=""><path fill="currentColor" d="M504 256c0 136.997-111.043 248-248 248S8 392.997 8 256C8 119.083 119.043 8 256 8s248 111.083 248 248zM262.655 90c-54.497 0-89.255 22.957-116.549 63.758-3.536 5.286-2.353 12.415 2.715 16.258l34.699 26.31c5.205 3.947 12.621 3.008 16.665-2.122 17.864-22.658 30.113-35.797 57.303-35.797 20.429 0 45.698 13.148 45.698 32.958 0 14.976-12.363 22.667-32.534 33.976C247.128 238.528 216 254.941 216 296v4c0 6.627 5.373 12 12 12h56c6.627 0 12-5.373 12-12v-1.333c0-28.462 83.186-29.647 83.186-106.667 0-58.002-60.165-102-116.531-102zM256 338c-25.365 0-46 20.635-46 46 0 25.364 20.635 46 46 46s46-20.636 46-46c0-25.365-20.635-46-46-46z"></path></svg><!-- <i class="fa fa-question-circle" data-toggle="popover" data-placement="top" data-content="Add Website Url" data-html="true" data-trigger="hover"></i> Font Awesome fontawesome.com -->
                                                    </label>

                                                    <input type="text" class="form-control height-35 f-14" placeholder="Website URL" value="{{ $woo_commerce->website_url ??old('website_url') }}" name="website_url" id="website_url" autocomplete="off">
                                                    @if ($errors->has('website_url'))
                                                        <span class="text-danger">{{ $errors->first('website_url') }}</span>
                                                    @endif
                                                </div>
                                            </div>
                                        </div>

                                        <div class="row">
                                            <div class="col-lg-6">
                                                <div class="form-group my-3 mr-0 mr-lg-2 mr-md-2">
                                                    <label class="f-14 text-dark-grey mb-12" data-label="true" for="client_id">Client Id
                                                            <sup class="f-14 mr-1">*</sup>

                                                    </label>

                                                    <input type="text" class="form-control height-35 f-14" placeholder="" value="{{ $woo_commerce->client_id ?? old('client_id') }}" name="client_id" id="client_id" autocomplete="off">
                                                    @if ($errors->has('client_id'))
                                                        <span class="text-danger">{{ $errors->first('client_id') }}</span>
                                                    @endif
                                                </div>
                                            </div>
                                            <div class="col-lg-6">
                                                <div class="form-group my-3 mr-0 mr-lg-2 mr-md-2">
                                                    <label class="f-14 text-dark-grey mb-12" data-label="false" for="secret_key">Secret Key

                                                    </label>

                                                    <input type="text" class="form-control height-35 f-14" placeholder="" value="{{ $woo_commerce->secret_key ?? old('secret_key') }}" name="secret_key" id="secret_key" autocomplete="off">
                                                    @if ($errors->has('secret_key'))
                                                        <span class="text-danger">{{ $errors->first('secret_key') }}</span>
                                                    @endif
                                                </div>
                                            </div>
                                        </div>

                                    </div>
                                </div>
                                 <!-- Buttons Start -->
                                <div class="w-100 border-top-grey">
                                    <div class="settings-btns py-3 d-flex justify-content-start px-4">
                                        <button type="button" class="btn-primary rounded f-14 p-2 mr-3" id="save-form">
                                                <svg class="svg-inline--fa fa-check fa-w-16 mr-1" aria-hidden="true" focusable="false" data-prefix="fa" data-icon="check" role="img" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 512 512" data-fa-i2svg=""><path fill="currentColor" d="M173.898 439.404l-166.4-166.4c-9.997-9.997-9.997-26.206 0-36.204l36.203-36.204c9.997-9.998 26.207-9.998 36.204 0L192 312.69 432.095 72.596c9.997-9.997 26.207-9.997 36.204 0l36.203 36.204c9.997 9.997 9.997 26.206 0 36.204l-294.4 294.401c-9.998 9.997-26.207 9.997-36.204-.001z"></path></svg><!-- <i class="fa fa-check mr-1"></i> Font Awesome fontawesome.com -->
                                            Save
                                        </button>
                                    </div>
                                </div>
                                <!-- Buttons End -->
                            </div>
                            <!-- TAB CONTENT END -->
                        </div>
                    </div>
                </div>
            </form>
        </div>

    </div>
    <!-- SETTINGS END -->
@endsection

@push('scripts')
    <script
        src="https://maps.googleapis.com/maps/api/js?key=AIzaSyCCl9wZfCCqZ9BtkD_ItVG8dAWT9BTMVB0&callback=initMap&libraries=places&v=weekly"
        async>
    </script>
    <script>
        $('#save-form').click(function () {
            var url = "{{ route('woo.store') }}";

            $.easyAjax({
                url: url,
                container: '#customeSetting',
                type: "POST",
                disableButton: true,
                blockUI: true,
                buttonSelector: "#save-form",
                data: $('#customeSetting').serialize(),
            })
        });
    </script>
@endpush
