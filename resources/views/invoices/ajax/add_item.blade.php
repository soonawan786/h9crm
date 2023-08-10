<!-- DESKTOP DESCRIPTION TABLE START -->
<div class="d-flex px-4 py-3 c-inv-desc item-row">

    <div class="c-inv-desc-table w-100 d-lg-flex d-md-flex d-block ">
        <table width="100%">
            <tbody>
                <tr class="text-dark-grey font-weight-bold f-14">
                    <td width="{{ $invoiceSetting->hsn_sac_code_show ? '40%' : '50%' }}"
                        class="border-0 inv-desc-mbl btlr">@lang('app.description')</td>
                    @if ($invoiceSetting->hsn_sac_code_show)
                        <td width="10%" class="border-0" align="right">@lang('app.hsnSac')</td>
                    @endif
                    <td width="10%" class="border-0" align="right">
                        {{ isset($items->unit) ? $items->unit->unit_type : 'Qty\hrs' }}</td>
                    <td width="10%" class="border-0" align="right">@lang('modules.invoices.unitPrice')</td>
                    <td width="10%" class="border-0" align="right">@lang('modules.invoices.remainingQuantity')</td>
                    <td width="10%" class="border-0" align="right">@lang('modules.invoices.totalQuantity')</td>
                    <td width="13%" class="border-0" align="right">@lang('modules.invoices.tax')</td>
                    <td width="17%" class="border-0 bblr-mbl" align="right">@lang('modules.invoices.amount')</td>
                </tr>
                <tr>
                    <td class="border-bottom-0 btrr-mbl btlr">
                        <input type="text" class="form-control f-14 border-0 w-100 item_name" name="item_name[]"
                            placeholder="@lang('modules.expenses.itemName')" value="{{ $items->name }}">
                    </td>
                    <td class="border-bottom-0 d-block d-lg-none d-md-none">
                        <textarea class="form-control f-14 border-0 w-100 mobile-description" name="item_summary[]"
                            placeholder="@lang('placeholders.invoices.description')">{{ strip_tags($items->description) }}</textarea>
                    </td>
                    @if ($invoiceSetting->hsn_sac_code_show)
                        <td class="border-bottom-0">
                            <input type="text" min="1"
                                class="form-control f-14 border-0 w-100 text-right hsn_sac_code"
                                data-item-id="{{ $items->id }}" value="{{ $items->hsn_sac_code }}"
                                name="hsn_sac_code[]">
                        </td>
                    @endif
                    <td class="border-bottom-0">
                        {{-- <input type="hidden" name="product_id[]" value="{{ $items->id }}-{{ $items->quantity }}"> --}}
                        <input type="hidden" class="product_quantity" name="product_quantities[{{ $items->id }}]" value="{{ $items->quantity - 1 }}">
                        <input type="number" min="1"
                            class="form-control f-14 border-0 w-100 text-right quantity"
                            data-item-id="{{ $items->id }}" value="1" name="quantity[]">
                    </td>

                    <td class="border-bottom-0">
                        <input type="number" min="1"
                            class="f-14 border-0 w-100 text-right cost_per_item form-control"
                            data-item-id="{{ $items->id }}" placeholder="{{ $items->price }}"
                            value="{{ $items->price }}" name="cost_per_item[]">
                    </td>

                    <td class="border-bottom-0 total-quantity" data-initial-total="{{ $items->quantity }}">
                        {{ $items->quantity -1 }}
                    </td>
                    <td class="border-bottom-0">
                        {{ $items->quantity }}
                    </td>
                    <td class="border-bottom-0">
                        <div class="select-others height-35 rounded border-0">
                            <select id="multiselect" name="taxes[0][]" multiple="multiple"
                                class="select-picker type customSequence border-0" data-size="3">
                                @foreach ($taxes as $tax)
                                    <option data-rate="{{ $tax->rate_percent }}"
                                        @if (isset($items->taxes) && array_search($tax->id, json_decode($items->taxes)) !== false) selected @endif value="{{ $tax->id }}">
                                        {{ strtoupper($tax->tax_name) }}:
                                        {{ $tax->rate_percent }}%</option>
                                @endforeach
                            </select>
                        </div>
                    </td>
                    <td rowspan="2" align="right" valign="top" class="bg-amt-grey btrr-bbrr">
                        <span class="amount-html" data-item-id="{{ $items->id }}">0.00</span>
                        <input type="hidden" class="amount" name="amount[]" data-item-id="{{ $items->id }}"
                            value="0">
                    </td>
                </tr>
                <tr class="d-none d-md-table-row d-lg-table-row">
                    <td colspan="{{ $invoiceSetting->hsn_sac_code_show ? '4' : '3' }}" class="dash-border-top bblr">
                        <textarea class="form-control f-14 border-0 w-100 desktop-description" name="item_summary[]"
                            placeholder="@lang('placeholders.invoices.description')">{{ strip_tags($items->description) }}</textarea>
                    </td>

                    <td class="border-left-0">
                        <input type="file" class="dropify" id="dropify" name="invoice_item_image[]"
                            data-allowed-file-extensions="png jpg jpeg" data-messages-default="test" data-height="70"
                            data-default-file="{{ $items->image_url }}" />
                        <input type="hidden" name="invoice_item_image_url[]" value="{{ $items->image_url }}">
                    </td>
                </tr>
            </tbody>
        </table>

        <a href="javascript:;" class="d-flex align-items-center justify-content-center ml-3 remove-item"><i
                class="fa fa-times-circle f-20 text-lightest"></i></a>
    </div>

    <script>
        $(function() {

            $(document).find('.dropify').dropify({
                messages: dropifyMessages
            });

            var quantity = $('#sortable').find('.quantity[data-item-id="{{ $items->id }}"]').val();
            var perItemCost = $('#sortable').find('.cost_per_item[data-item-id="{{ $items->id }}"]').val();
            var amount = (quantity * perItemCost);
            $('#sortable').find('.amount[data-item-id="{{ $items->id }}"]').val(amount);
            $('#sortable').find('.amount-html[data-item-id="{{ $items->id }}"]').html(amount);

            calculateTotal();
        });

        $(document).ready(function() {
            $('.quantity').on('change', function() {
                var $row = $(this).closest('tr');
                var newQuantity = parseInt($(this).val());
                var initialTotal = parseInt($row.find('.total-quantity').data('initial-total'));



                // Calculate the running total based on entered quantities
                var runningTotal = initialTotal;
                $(this).each(function() {
                    runningTotal -= parseInt($(this).val());
                });
                // Validate the entered quantity
                if (newQuantity <= 0) {
                    alert('Quantity must be greater than zero.');
                    $(this).val(1);
                    $row.find('.total-quantity').text(initialTotal-1);
                    var quantity = $('#sortable').find('.quantity[data-item-id="{{ $items->id }}"]').val();
                    var perItemCost = $('#sortable').find('.cost_per_item[data-item-id="{{ $items->id }}"]').val();
                    var amount = (quantity * perItemCost);
                    $('#sortable').find('.amount[data-item-id="{{ $items->id }}"]').val(amount);
                    $('#sortable').find('.amount-html[data-item-id="{{ $items->id }}"]').html(amount);
                    calculateTotal();
                    return;
                } else if (newQuantity > initialTotal) {
                    alert('Quantity cannot be greater than the remaining total.');
                    $(this).val(1);
                    $row.find('.total-quantity').text(initialTotal-1);
                    var quantity = $('#sortable').find('.quantity[data-item-id="{{ $items->id }}"]').val();
                    var perItemCost = $('#sortable').find('.cost_per_item[data-item-id="{{ $items->id }}"]').val();
                    var amount = (quantity * perItemCost);
                    $('#sortable').find('.amount[data-item-id="{{ $items->id }}"]').val(amount);
                    $('#sortable').find('.amount-html[data-item-id="{{ $items->id }}"]').html(amount);
                    calculateTotal();
                    return;
                }
                // Update the total quantity display

                $row.find('.total-quantity').text(runningTotal);
                $row.find('.product_quantity').val(runningTotal);
            });
        });
    </script>

</div>
<!-- DESKTOP DESCRIPTION TABLE END -->
