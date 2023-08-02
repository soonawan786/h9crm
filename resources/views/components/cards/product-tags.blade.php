<!-- resources/views/components/product-tags.blade.php -->
@props(['product'])

<div class="col-12 px-0 pb-3 d-lg-flex d-md-flex d-block">
    <p class="mb-0 text-lightest f-14 w-30 text-capitalize">{{ __('modules.productTags.productTags') }}</p>
    @if ($product->tags->count() > 0)
        <p class="mb-0 text-dark-grey f-14 w-70 text-wrap">
            @foreach ($product->tags as $tag)
                <span class="badge badge-info">{{ $tag->tag_name }}</span>
            @endforeach
        </p>
    @else
        <p class="mb-0 text-dark-grey f-14 w-70 text-wrap">--</p>
    @endif
</div>
