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
                                            <div class="col-lg-6">
                                                <div class="form-group my-3 mr-0 mr-lg-2 mr-md-2">
                                                    <label class="f-14 text-dark-grey mb-12" data-label="true" for="api_secret">Api Secret
                                                            <sup class="f-14 mr-1">*</sup>

                                                    </label>

                                                    <input type="text" class="form-control height-35 f-14" placeholder="" value="{{ auth()->user()->api_secret ?? old('api_secret') }}" name="api_secret" id="api_secret" autocomplete="off">
                                                    @if ($errors->has('api_secret'))
                                                        <span class="text-danger">{{ $errors->first('api_secret') }}</span>
                                                    @endif
                                                </div>
                                            </div>
                                            {{-- <div class="col-lg-6">
                                                <div class="form-group my-3 mr-0 mr-lg-2 mr-md-2">
                                                    <label class="f-14 text-dark-grey mb-12" data-label="false" for="whatsapp_number">WhatsApp Number
                                                        <sup class="f-14 mr-1">*(Number Must start with +92)</sup>

                                                    </label>

                                                    <input type="text" class="form-control height-35 f-14" placeholder="+92123456789" value="{{ $whats_app->whatsapp_number ?? old('whatsapp_number') }}" name="whatsapp_number" id="whatsapp_number" autocomplete="off">
                                                    @if ($errors->has('whatsapp_number'))
                                                        <span class="text-danger">{{ $errors->first('whatsapp_number') }}</span>
                                                    @endif
                                                </div>
                                            </div> --}}
                                        </div>
                                        {{-- <div class="row">
                                            <div class="col-lg-6">
                                                <div class="form-group my-3 mr-0 mr-lg-2 mr-md-2">
                                                    <label class="f-14 text-dark-grey mb-12 ml-4">Activate WhatsApp Number</label>

                                                    <select class="form-control ml-4" style="width:50%;" name="" id="active_number">

                                                        <option value="">Select WhatsApp Number</option>
                                                        @foreach ($whats_app_numbers as $number)
                                                            <option value="{{ $number->id }}" {{ ($number->status==1)?'selected':'' }}>{{ $number->whatsapp_number }}</option>
                                                        @endforeach
                                                    </select>
                                                </div>
                                            </div>
                                        </div> --}}

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
            var url = "{{ route('whatsapp.store') }}";

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
        $('#active_number').change(function () {
            var whats_app_number_id = $(this).val();
            var url = "{{ route('whatsapp.status.change') }}";

            $.easyAjax({
                url: url,
                // container: '#active_number',
                type: "POST",
                // disableButton: true,
                // blockUI: true,
                // buttonSelector: "#active_number",
                data: {
                    whats_app_number_id: whats_app_number_id,
                    _token: '{{ csrf_token() }}'
                },
            })
        });

    </script>
@endpush
