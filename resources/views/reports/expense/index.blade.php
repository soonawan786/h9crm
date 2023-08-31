@extends('layouts.app')

@push('datatable-styles')
    <script src="{{ asset('vendor/jquery/frappe-charts.min.iife.js') }}"></script>
    @include('sections.datatable_css')
@endpush

@push('styles')
    <style>
        .action-bar{
            float: right;
        }
    </style>
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

        <!-- EXPENSE CATEGORY START -->
        <div class="select-box d-flex  py-2 px-lg-2 px-md-2 px-0 border-right-grey border-right-grey-sm-0">
            <p class="mb-0 pr-2 f-14 text-dark-grey d-flex align-items-center">@lang('app.category')</p>
            <div class="select-status">
                <select class="form-control select-picker" name="category" id="category_id" data-live-search="true"
                    data-size="8">
                    <option value="all">@lang('app.all')</option>
                    @foreach ($categories as $category)
                        <option value="{{ $category->id }}">{{ $category->category_name }}</option>
                    @endforeach
                </select>
            </div>
        </div>
        <!-- EXPENSE CATEGORY END -->

        <!-- CLIENT START -->
        <div class="select-box d-flex  py-2 px-lg-2 px-md-2 px-0 border-right-grey border-right-grey-sm-0">
            <p class="mb-0 pr-2 f-14 text-dark-grey d-flex align-items-center">@lang('app.employee')</p>
            <div class="select-status">
                <select class="form-control select-picker" name="employee" id="employee_id" data-live-search="true"
                    data-size="8">
                    <option value="all">@lang('app.all')</option>
                    @foreach ($employees as $employee)
                        <x-user-option :user="$employee" />
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
                <x-cards.widget :title="__('modules.dashboard.totalExpenses')" value="0" icon="coins"
                    widgetId="totalExpense" />
            </div>
            <div class="col-md-8">
                <div class="d-block d-lg-flex d-md-flex justify-content-between action-bar" id="reports">
                    <div class="btn-group mt-3 mt-lg-0 mt-md-0 ml-lg-3" role="group">
                        <a href="{{ route('expense-report.index') }}" class="btn btn-secondary f-14 btn-active" data-toggle="tooltip"
                            data-original-title="@lang('app.menu.expenseReport')"><i class="side-icon bi bi-list-ul"></i></a>

                        <a href="{{ route('expense-report.expense_category_report') }}" class="btn btn-secondary f-14" data-toggle="tooltip"
                            data-original-title="@lang('modules.expenseCategory.expenseCategoryReport')"><i class="side-icon bi bi-receipt"></i></a>

                    </div>
                </div>
            </div>
        </div>

        <div class="row mb-4">
            <div class="col-lg-6">

                <div class="d-flex flex-column">
                    <!-- EXPENSE STATUS START -->
                    <x-cards.data id="e" :title="__($pageTitle)">
                    </x-cards.data>
                    <!-- EXPENSE STATUS END -->
                </div>

                <div id="table-actions" class="flex-grow-1 align-items-center mt-4">
                <button id="custom-print-btn" style="padding: 8px 17px;font-size: 14px;margin-left: 2rem;"
                            class="btn btn-secondary"><i class="fa fa-print"></i> Print</button>
                    </div>
            </div>
            </div>
            <div class="col-lg-6">
                <x-cards.data :title="__($categoryTitle)">
                    <div id="expense-chart-card"></div>
                </x-cards.data>
            </div>
        </div>

        <!-- Task Box Start -->
        <div class="d-flex flex-column w-tables rounded mt-4 bg-white table-responsive">
            {!! $dataTable->table(['class' => 'table table-hover border-0 w-100']) !!}
        </div>
        <!-- Task Box End -->
    </div>
    <!-- CONTENT WRAPPER END -->

@endsection

@push('scripts')
@include('sections.datatable_js')
<script type="text/javascript" src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
    function setDate() {
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
</script>
<script>
    $(function() {
        setDate()
        $('#datatableRange2').on('apply.daterangepicker', function(ev, picker) {
            showTable();
        });

        function barChart() {
            var startDate = $('#datatableRange2').val();

            if (startDate == '') {
                startDate = null;
                endDate = null;
            } else {
                var dateRangePicker = $('#datatableRange2').data('daterangepicker');
                startDate = dateRangePicker.startDate.format('{{ company()->moment_date_format }}');
                endDate = dateRangePicker.endDate.format('{{ company()->moment_date_format }}');
            }

            var data = new Array();
            var projectID = $('#project_id').val();
            var employeeID = $('#employee_id').val();
            var categoryID = $('#category_id').val();
            var searchText = $('#search-text-field').val();

            var url = "{{ route('expense-report.chart') }}";

            $.easyAjax({
                url: url,
                container: '#e',
                blockUI: true,
                type: "POST",
                data: {
                    startDate: startDate,
                    endDate: endDate,
                    categoryID: categoryID,
                    projectID: projectID,
                    employeeID: employeeID,
                    _token: '{{ csrf_token() }}'
                },
                success: function(response) {
                    $('#e .card-body').html(response.html);
                    $('#expense-chart-card').html(response.html2);
                    $('#totalExpense').html(response.totalExpenses);
                }
            });
        }

        barChart();

        $('#expense-report-table').on('preXhr.dt', function(e, settings, data) {

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
            var employeeID = $('#employee_id').val();
            var categoryID = $('#category_id').val();
            var searchText = $('#search-text-field').val();

            data['categoryID'] = categoryID;
            data['employeeID'] = employeeID;
            data['projectID'] = projectID;
            data['startDate'] = startDate;
            data['endDate'] = endDate;
            data['searchText'] = searchText;
        });

        const showTable = () => {
            window.LaravelDataTables["expense-report-table"].draw(false);
            barChart();
        }

        $('#category_id, #employee_id, #project_id')
            .on('change keyup',
                function() {
                    if ($('#project_id').val() != "all") {
                        $('#reset-filters').removeClass('d-none');
                        showTable();
                    } else if ($('#category_id').val() != "all") {
                        $('#reset-filters').removeClass('d-none');
                        showTable();
                    } else if ($('#project_id').val() != "all") {
                        $('#reset-filters').removeClass('d-none');
                        showTable();
                    } else if ($('#employee_id').val() != "all") {
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
            setDate()

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

    });

        // Custom print button click event handler
        $('#custom-print-btn').on('click', function() {
            // Initialize an empty array to store the extracted data
            var dataToPrint = [];
            // Initialize a variable to store the total amount
            var totalAmount = 0;

            // Iterate through each row in the table
            $('#expense-report-table tbody tr').each(function() {
                // Extract the plain text content from each cell in the row
                var rowData = $(this).find('td').map(function() {
                    return $(this).text();
                }).get();

                // Split the "Employees" cell content to get only the employee name part
                var employeeCellContent = rowData[2].split('|')[0].trim(); // Assumes | is the separator

                // Format the table row data with the employee name
                rowData[2] = employeeCellContent;

                // Get the amount column index (Assuming it is the 2nd column, change the index if needed)
                var amountIndex = 1;
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
            var formattedTotalAmount = '<b>Total Price: </b>' + formatCurrency(totalAmount);

            // Function to format the amount as currency
            function formatCurrency(amount) {
                return 'Rs' + amount.toFixed(2).replace(/\B(?=(\d{3})+(?!\d))/g, ',');
            }

            // Join the rows with a line break (to separate rows) and add table head in front of the data
            var tableHeaderText = $('#expense-report-table thead tr th').map(function() {
                return $(this).text();
            }).get().join(' | ');

            var finalTableText = [tableHeaderText].concat(dataToPrint).join('\n');

            // Open a new window and display the table data in plain text format with CSS styling
            var printWindow = window.open('', '_blank');
            printWindow.document.write('<html><head><title>Expense Report</title>');
            printWindow.document.write('<style>');
            printWindow.document.write('table { border-collapse: collapse; width: 100%; }');
            printWindow.document.write('th, td { border: 1px solid black; padding: 8px; text-align: left; }');
            printWindow.document.write('</style>');
            printWindow.document.write('</head><body>');
            printWindow.document.write('<table>');
            printWindow.document.write('<thead><tr>');

            // Add table header
            var headers = $('#expense-report-table thead tr th').map(function() {
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
