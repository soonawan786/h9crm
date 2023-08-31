@extends('layouts.app')

@push('datatable-styles')
    <script src="{{ asset('vendor/jquery/frappe-charts.min.iife.js') }}"></script>
    @include('sections.datatable_css')
@endpush

@section('filter-section')

    <x-filters.filter-box>
        <!-- DATE START -->
        <div class="select-box d-flex pr-2 border-right-grey border-right-grey-sm-0">
            <p class="mb-0 pr-2 f-14 text-dark-grey d-flex align-items-center">@lang('app.duration')</p>
            <div class="select-status d-flex">
                <input type="text" class="position-relative text-dark form-control border-0 p-2 text-left f-14 f-w-500 border-additional-grey"
                    id="datatableRange2" placeholder="@lang('placeholders.dateRange')">
            </div>
        </div>
        <!-- DATE END -->

        <!-- CLIENT START -->
        <div class="select-box d-flex  py-2 px-lg-2 px-md-2 px-0 border-right-grey border-right-grey-sm-0">
            <p class="mb-0 pr-2 f-14 text-dark-grey d-flex align-items-center">@lang('app.client')</p>
            <div class="select-status">
                <select class="form-control select-picker" name="employee" id="clientID" data-live-search="true"
                    data-size="8">
                    <option value="all">@lang('app.all')</option>
                    @foreach ($clients as $client)
                        <x-user-option :user="$client" />
                    @endforeach
                </select>
            </div>
        </div>
        <!-- CLIENT END -->

        <!-- PROJECT START -->
        <div class="select-box d-flex  py-2 px-lg-2 px-md-2 px-0 border-right-grey border-right-grey-sm-0">
            <p class="mb-0 pr-2 f-14 text-dark-grey d-flex align-items-center">@lang('app.project')</p>
            <div class="select-status">
                <select class="form-control select-picker" name="project_id" id="project_id" data-live-search="true"
                    data-size="8">
                    <option value="all">@lang('app.all')</option>
                    @foreach ($projects as $project)
                        <option value="{{ $project->id }}">{{ $project->project_name }}</option>
                    @endforeach
                </select>
            </div>
        </div>
        <!-- PROJECT END -->

        <!-- RESET START -->
        <div class="select-box d-flex py-1 px-lg-2 px-md-2 px-0">
            <x-forms.button-secondary class="btn-xs d-none" id="reset-filters" icon="times-circle">
                @lang('app.clearFilters')
            </x-forms.button-secondary>
        </div>
        <!-- RESET END -->

    </x-filters.filter-box>

@endsection

@section('content')
    <!-- CONTENT WRAPPER START -->
    <div class="content-wrapper">
        <div class="row mb-4">
            <div class="col-lg-4">
                <x-cards.widget :title="__('modules.dashboard.totalEarnings')" value="0" icon="coins"
                    widgetId="totalEarnings" />
            </div>
        </div>

        <!-- Add Task Export Buttons Start -->
        <div class="d-flex flex-column">
            <!-- TASK STATUS START -->
            <x-cards.data id="task-chart-card" :title="__($pageTitle)">
            </x-cards.data>
            <!-- TASK STATUS END -->

            <div id="table-actions" class="flex-grow-1 align-items-center mt-4">
            <button id="custom-print-btn" style="padding: 8px 17px;font-size: 14px;margin-left: 2rem;"
                    class="btn btn-secondary"><i class="fa fa-print"></i> Print</button>
        </div>

        </div>

        <!-- Add Task Export Buttons End -->
        <!-- Task Box Start -->
        <div class="d-flex flex-column w-tables rounded mt-4 bg-white">

            {!! $dataTable->table(['class' => 'table table-hover border-0 w-100']) !!}

        </div>
        <!-- Task Box End -->
    </div>
    <!-- CONTENT WRAPPER END -->

@endsection

@push('scripts')
    @include('sections.datatable_js')

    <script type="text/javascript">

        function getDate() {
            var start = moment().clone().startOf('month');
            var end = moment();

            $('#datatableRange2').daterangepicker({
                locale: daterangeLocale,
                linkedCalendars: false,
                startDate: start,
                endDate: end,
                ranges: daterangeConfig
            }, cb);
        }

        $(function() {
            getDate()
            $('#datatableRange2').on('apply.daterangepicker', function(ev, picker) {
                showTable();
            });
        });

    </script>


    <script>
        $('#payments-table').on('preXhr.dt', function(e, settings, data) {

            var dateRangePicker = $('#datatableRange2').data('daterangepicker');
            var startDate = $('#datatableRange2').val();

            if (startDate == '') {
                startDate = null;
                endDate = null;
            } else {
                startDate = dateRangePicker.startDate.format('{{ company()->moment_date_format }}');
                endDate = dateRangePicker.endDate.format('{{ company()->moment_date_format }}');
            }

            var projectID = $('#project_id').val();
            if (!projectID) {
                projectID = 0;
            }
            var clientID = $('#clientID').val();

            var searchText = $('#search-text-field').val();

            data['clientID'] = clientID;
            data['projectID'] = projectID;
            data['startDate'] = startDate;
            data['endDate'] = endDate;
            data['searchText'] = searchText;
        });
        const showTable = () => {
            window.LaravelDataTables["payments-table"].draw(false);
            pieChart();
        }

        $('#clientID, #project_id, #status')
            .on('change keyup',
                function() {
                    if ($('#project_id').val() != "all") {
                        $('#reset-filters').removeClass('d-none');
                        showTable();
                    } else if ($('#status').val() != "all") {
                        $('#reset-filters').removeClass('d-none');
                        showTable();
                    } else if ($('#clientID').val() != "all") {
                        $('#reset-filters').removeClass('d-none');
                        showTable();
                    } else {
                        $('#reset-filters').addClass('d-none');
                        showTable();
                    }
                });

        $('#search-text-field').on('keyup', function() {
            if ($('#search-text-field').val() != "") {
                $('#reset-filters').removeClass('d-none');
                showTable();
            }
        });

        $('#reset-filters').click(function() {
            $('#filter-form')[0].reset();
            getDate()

            $('.filter-box .select-picker').selectpicker("refresh");
            $('#reset-filters').addClass('d-none');
            showTable();
        });

        $('#reset-filters-2').click(function() {
            $('#filter-form')[0].reset();

            $('.filter-box .select-picker').selectpicker("refresh");
            $('#reset-filters').addClass('d-none');
            showTable();
        });

        function pieChart() {
            var dateRangePicker = $('#datatableRange2').data('daterangepicker');
            var startDate = $('#datatableRange2').val();

            if (startDate == '') {
                startDate = null;
                endDate = null;
            } else {
                startDate = dateRangePicker.startDate.format('{{ company()->moment_date_format }}');
                endDate = dateRangePicker.endDate.format('{{ company()->moment_date_format }}');
            }

            var data = new Array();
            var projectID = $('#project_id').val();
            var clientID = $('#clientID').val();
            var searchText = $('#search-text-field').val();

            var url = "{{ route('finance-report.chart') }}";

            $.easyAjax({
                url: url,
                container: '#task-chart-card',
                blockUI: true,
                type: "POST",
                data: {
                    startDate: startDate,
                    endDate: endDate,
                    projectID: projectID,
                    clientID: clientID,
                    _token: '{{ csrf_token() }}'
                },
                success: function(response) {
                    $('#task-chart-card .card-body').html(response.html);
                    $('#totalEarnings').html(response.totalEarnings);
                }
            });
        }
        pieChart();
        
        $('#custom-print-btn').on('click', function() {
            // Initialize an empty array to store the extracted data
            var dataToPrint = [];

            // Initialize a variable to store the total amount
            var totalAmount = 0;

            // Iterate through each row in the table
            $('#payments-table tbody tr').each(function() {
                // Extract the plain text content from each cell in the row
                var rowData = $(this).find('td').map(function() {
                    return $(this).text();
                }).get();

                // Get the amount column index (Assuming it is the 2nd column, change the index if needed)
                var amountIndex = 2;
                var amount = parseFloat(rowData[amountIndex].replace(/[^\d.-]/g,
                '')); // Remove non-numeric characters

                // Add the amount to the total
                if (!isNaN(amount)) {
                    totalAmount += amount;
                }

                // Join the formatted row data with a pipe symbol and add it to the data array
                dataToPrint.push(rowData.join(' | '));
            });

            // Format the total amount with currency formatting
            var formattedTotalAmount = '<b>Total Amount: </b>' + formatCurrency(totalAmount);

            // Function to format the amount as currency
            function formatCurrency(amount) {
                return 'Rs' + amount.toFixed(2).replace(/\B(?=(\d{3})+(?!\d))/g, ',');
            }

            // Join the rows with a line break (to separate rows) and add table head in front of the data
            var tableHeaderText = $('#payments-table thead tr th').map(function() {
                return $(this).text();
            }).get().join(' | ');

            // Join the rows with a line break
            var finalTableText = [tableHeaderText].concat(dataToPrint).join('\n');

            // Open a new window and display the table data in plain text format with CSS styling
            var printWindow = window.open('', '_blank');
            printWindow.document.write('<html><head><title>Invoice Report</title>');
            printWindow.document.write('<style>');
            printWindow.document.write('table { border-collapse: collapse; width: 100%; }');
            printWindow.document.write('th, td { border: 1px solid black; padding: 8px; text-align: left; }');
            printWindow.document.write('</style>');
            printWindow.document.write('</head><body>');
            printWindow.document.write('<table>');
            printWindow.document.write('<thead><tr>');

            // Add table header
            var headers = $('#payments-table thead tr th').map(function() {
                return '<th>' + $(this).text() + '</th>';
            }).get().join('');

            printWindow.document.write(headers);
            printWindow.document.write('</tr></thead><tbody>');

            // Add table data
            printWindow.document.write(dataToPrint.map(function(row) {
                return '<tr><td>' + row.replace(/\|/g, '</td><td>') + '</td></tr>';
            }).join(''));

            printWindow.document.write('</tbody></table>');

            // Add the total amount to the end of the printed document
            printWindow.document.write('<p style="text-align: right;">' + formattedTotalAmount + '</p>');

            printWindow.document.write('</body></html>');
            printWindow.document.close();

            // Trigger the print functionality for the new window
            printWindow.print();
        });

</script>
@endpush
