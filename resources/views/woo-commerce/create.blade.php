@extends('layouts.app')

@section('content')
<div class="card-body">
    @if(Session::has('success'))
    <p class="alert {{ Session::get('alert-class', 'alert-info') }}">{{ Session::get('success') }}</p>
    @endif
    <form action="{{ route('woo.store') }}" method="POST">
        @csrf
        <div class="row">
            <div class="col-lg-4">
                <div class="form-group my-3 mr-0 mr-lg-2 mr-md-2">
                <label class="f-14 text-dark-grey mb-12" data-label="true" for="website_url">Website URL
                        <sup class="f-14 mr-1">*</sup>

                        <svg class="svg-inline--fa fa-question-circle fa-w-16" data-toggle="popover" data-placement="top" data-content="Add Website Url" data-html="true" data-trigger="hover" aria-hidden="true" focusable="false" data-prefix="fa" data-icon="question-circle" role="img" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 512 512" data-fa-i2svg="" data-original-title="" title=""><path fill="currentColor" d="M504 256c0 136.997-111.043 248-248 248S8 392.997 8 256C8 119.083 119.043 8 256 8s248 111.083 248 248zM262.655 90c-54.497 0-89.255 22.957-116.549 63.758-3.536 5.286-2.353 12.415 2.715 16.258l34.699 26.31c5.205 3.947 12.621 3.008 16.665-2.122 17.864-22.658 30.113-35.797 57.303-35.797 20.429 0 45.698 13.148 45.698 32.958 0 14.976-12.363 22.667-32.534 33.976C247.128 238.528 216 254.941 216 296v4c0 6.627 5.373 12 12 12h56c6.627 0 12-5.373 12-12v-1.333c0-28.462 83.186-29.647 83.186-106.667 0-58.002-60.165-102-116.531-102zM256 338c-25.365 0-46 20.635-46 46 0 25.364 20.635 46 46 46s46-20.636 46-46c0-25.365-20.635-46-46-46z"></path></svg><!-- <i class="fa fa-question-circle" data-toggle="popover" data-placement="top" data-content="Add Website Url" data-html="true" data-trigger="hover"></i> Font Awesome fontawesome.com -->
                </label>

                <input type="text" value="{{ $woo_commerce->website_url ??old('website_url') }}" class="form-control height-35 f-14" placeholder="Website URL"  name="website_url" id="website_url" autocomplete="off">

                </div>
            </div>
            <div class="col-lg-4">
                <div class="form-group my-3 mr-0 mr-lg-2 mr-md-2">
                <label class="f-14 text-dark-grey mb-12" data-label="true" for="client_id">Client Id
                        <sup class="f-14 mr-1">*</sup>

                </label>

                <input type="text" value="{{ $woo_commerce->client_id ??old('client_id') }}" class="form-control height-35 f-14" placeholder="Client Id"  name="client_id" id="client_id" autocomplete="off">

                </div>
            </div>
            <div class="col-lg-4">
                <div class="form-group my-3 mr-0 mr-lg-2 mr-md-2">
                <label class="f-14 text-dark-grey mb-12" data-label="true" for="secret_key">Secret Key
                        <sup class="f-14 mr-1">*</sup>

                </label>

                <input type="text" value="{{ $woo_commerce->secret_key ??old('secret_key') }}" class="form-control height-35 f-14" placeholder="Secret Key"  name="secret_key" id="secret_key" autocomplete="off">

                </div>
            </div>
        </div>

        <div class="form-group mb-0 text-right">

            <button type="submit" class="btn btn-sm btn-primary">Authorize</button>

        </div>
    </form>
    @if ($woo_commerce!=null)
    <a href="{{ route('woo.orders') }}" class="btn btn-sm btn-info">Fetch Orders</a>
    @endif

</div>
@endsection

