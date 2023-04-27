<?php

namespace App\DataTables;

use Carbon\Carbon;
use App\Models\WooCommerce;
use App\DataTables\BaseDataTable;
use App\Models\WooCommerceOrder;
use Yajra\DataTables\Html\Button;
use Yajra\DataTables\Html\Column;
use Illuminate\Support\Facades\DB;

class WooCommerceDataTable extends BaseDataTable
{
    public $no;

    public function __construct()
    {
        parent::__construct();
        $this->no = 1;
    }

    /**
     * Build DataTable class.
     *
     * @param mixed $query Results from query() method.
     * @return \Yajra\DataTables\DataTableAbstract
     */
    public function dataTable($query)
    {
        return datatables()
            ->eloquent($query)
            ->addIndexColumn()

            ->addColumn('order_number', function ($row) {
                return $this->no++;

            })
            ->editColumn('status', function ($row) {

                if (in_array('admin', user_roles())) {
                    $status = '<select class="form-control select-picker order-status" data-order-id="' . $row->order_id . '" ' . (in_array($row->status, ['refunded', 'canceled']) ? 'disabled' : '') . '>';

                    if (in_array($row->status, ['pending', 'failed', 'on-hold', 'processing'])) {
                        $status .= '<option value="pending" ' . ($row->status == 'pending' ? 'selected' : '') . ' data-content="<i class=\'fa fa-circle mr-2 text-warning\'></i> ' . __('app.pending') . '">' . __('app.pending') . '</option>';
                    }

                    if (in_array($row->status, ['on-hold', 'pending', 'processing', 'failed'])) {
                        $status .= '<option value="on-hold" ' . ($row->status == 'on-hold' ? 'selected' : '') . ' data-content="<i class=\'fa fa-circle mr-2 text-info\'></i> ' . __('app.on-hold') . '">' . __('app.on-hold') . '</option>';
                    }

                    if (in_array($row->status, ['failed', 'pending',])) {
                        $status .= '<option value="failed" ' . ($row->status == 'failed' ? 'selected' : '') . ' data-content="<i class=\'fa fa-circle mr-2 text-dark\'></i> ' . __('app.failed') . '">' . __('app.failed') . '</option>';
                    }

                    if (in_array($row->status, ['processing', 'pending', 'on-hold', 'failed'])) {
                        $status .= '<option value="processing" ' . ($row->status == 'processing' ? 'selected' : '') . ' data-content="<i class=\'fa fa-circle mr-2 text-primary\'></i> ' . __('app.processing') . '">' . __('app.processing') . '</option>';
                    }

                    if (in_array($row->status, ['completed', 'pending', 'on-hold', 'failed', 'processing'])) {
                        $status .= '<option value="completed" ' . ($row->status == 'completed' ? 'selected' : '') . ' data-content="<i class=\'fa fa-circle mr-2 text-success\'></i> ' . __('app.completed') . '">' . __('app.completed') . '</option>';
                    }

                    if (in_array($row->status, ['canceled', 'on-hold', 'pending', 'failed', 'processing'])) {
                        $status .= '<option value="cancelled" ' . ($row->status == 'cancelled' ? 'selected' : '') . ' data-content="<i class=\'fa fa-circle mr-2 text-red\'></i> ' . __('app.canceled') . '">' . __('app.canceled') . '</option>';
                    }

                    if (in_array($row->status, ['refunded', 'completed'])) {
                        $status .= '<option value="refunded" ' . ($row->status == 'refunded' ? 'selected' : '') . ' data-content="<i class=\'fa fa-circle mr-2 \'></i> ' . __('app.refunded') . '">' . __('app.refunded') . '</option>';
                    }

                    $status .= '</select>';
                }
                else {
                    $status = match ($row->status) {
                        'pending' => ' <i class="fa fa-circle mr-1 text-warning f-10"></i>' . __('app.' . $row->status),
                        'on-hold' => ' <i class="fa fa-circle mr-1 text-info f-10"></i>' . __('app.' . $row->status),
                        'failed' => ' <i class="fa fa-circle mr-1 text-dark f-10"></i>' . __('app.' . $row->status),
                        'processing' => ' <i class="fa fa-circle mr-1 text-primary f-10"></i>' . __('app.' . $row->status),
                        'completed' => ' <i class="fa fa-circle mr-1 text-success f-10"></i>' . __('app.' . $row->status),
                        'canceled' => ' <i class="fa fa-circle mr-1 text-red f-10"></i>' . __('app.' . $row->status),
                        default => ' <i class="fa fa-circle mr-1 f-10"></i>' . __('app.' . $row->status),
                    };
                }

                return $status;
            })
            ->addColumn('total', function ($row) {
                return $row->total;

            })
            ->addColumn('order_date', function ($row) {
                return $row->order_date;

            })
            ->addColumn('order_status', function ($row) {
                return ucfirst($row->status);
            })
            ->orderColumn('order_number', 'created_at $1')
            ->rawColumns(['status', 'total', 'order_number','order_date']);
    }

    public function ajax()
    {
        return $this->dataTable($this->query())
            ->make(true);
    }

    /**
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function query()
    {
        $request = $this->request();

        $model =  WooCommerceOrder::select('id', 'order_id', 'order_date','total','status')->where('added_by',user()->id);
        if ($request->startDate !== null && $request->startDate != 'null' && $request->startDate != '') {
            $startDate = Carbon::createFromFormat($this->company->date_format, $request->startDate)->toDateString();
            $model = $model->where(DB::raw('DATE(woo_commerce_orders.`order_date`)'), '>=', $startDate);
        }

        if ($request->endDate !== null && $request->endDate != 'null' && $request->endDate != '') {
            $endDate = Carbon::createFromFormat($this->company->date_format, $request->endDate)->toDateString();
            $model = $model->where(DB::raw('DATE(woo_commerce_orders.`order_date`)'), '<=', $endDate);
        }

        if ($request->status != 'all' && !is_null($request->status)) {
            $model = $model->where('woo_commerce_orders.status', '=', $request->status);
        }


        if ($request->searchText != '') {
            $model->where(function ($query) {
                $query->where('woo_commerce_orders.order_id', 'like', '%' . request('searchText') . '%')
                    ->orWhere('woo_commerce_orders.total', 'like', '%' . request('searchText') . '%');
            });
        }


        return $model;
    }

    /**
     * Optional method if you want to use html builder.
     *
     * @return \Yajra\DataTables\Html\Builder
     */
    public function html()
    {
        return $this->setBuilder('orders-table', 0)
            ->parameters([
                'initComplete' => 'function () {
                    window.LaravelDataTables["orders-table"].buttons().container()
                    .appendTo( "#table-actions")
                }',
                'fnDrawCallback' => 'function( oSettings ) {
                    $("body").tooltip({
                        selector: \'[data-toggle="tooltip"]\'
                    });

                }',
            ])
            ->buttons(Button::make(['extend' => 'excel', 'text' => '<i class="fa fa-file-export"></i> ' . trans('app.exportExcel')]));
    }

    /**
     * Get columns.
     *
     * @return array
     */
    protected function getColumns()
    {
        return [
            __('app.id') => ['data' => 'id', 'name' => 'id', 'visible' => false, 'title' => __('app.id')],
            __('app.order') . '#' => ['data' => 'order_number', 'width' => '10%', 'name' => 'order_number', 'exportable' => false, 'title' => __('app.order') . '#'],
            __('modules.invoices.total') => ['data' => 'total', 'width' => '30%', 'name' => 'total', 'class' => 'text-right', 'title' => __('modules.invoices.total')],
            __('modules.orders.orderDate') => ['data' => 'order_date', 'name' => 'order_date', 'title' => __('modules.orders.orderDate')],
            __('app.status') => ['data' => 'status', 'name' => 'status', 'width' => '30%', 'exportable' => false, 'title' => __('app.status')],
            __('order_status') => ['data' => 'order_status', 'name' => 'order_status', 'width' => '10%', 'visible' => false, 'title' => __('app.status')],
        ];

    }

    /**
     * Get filename for export.
     *
     * @return string
     */
    protected function filename()
    {
        return 'WooOrders_' .now()->format('Y-m-d-H-i-s');
    }

}
