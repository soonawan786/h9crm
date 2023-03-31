<?php

namespace App\DataTables\SuperAdmin;

use Carbon\Carbon;
use App\DataTables\BaseDataTable;
use Yajra\DataTables\Html\Button;
use Yajra\DataTables\Html\Column;
use Illuminate\Support\Facades\DB;
use Yajra\DataTables\EloquentDataTable;
use App\Models\SuperAdmin\SupportTicket;

class SupportTicketDataTable extends BaseDataTable
{

    /**
     * Build DataTable class.
     *
     * @param mixed $query Results from query() method.
     * @return \Yajra\DataTables\DataTableAbstract
     */
    public function dataTable($query)
    {
        return (new EloquentDataTable($query))
            ->addColumn('check', function ($row) {
                return '<input type="checkbox" class="select-table-row" id="datatable-row-' . $row->id . '"  name="datatable_ids[]" value="' . $row->id . '" onclick="dataTableRowCheck(' . $row->id . ')">';
            })
            ->addIndexColumn()
            ->addColumn('action', function ($row) {
                $action = '<div class="task_view">';

                $action .= '<div class="dropdown">
                    <a class="task_view_more d-flex align-items-center justify-content-center dropdown-toggle" type="link"
                        id="dropdownMenuLink-' . $row->id . '" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                        <i class="icon-options-vertical icons"></i>
                    </a>
                    <div class="dropdown-menu dropdown-menu-right" aria-labelledby="dropdownMenuLink-' . $row->id . '" tabindex="0">';

                    $action .= '<a href="' . route('superadmin.support-tickets.show', [$row->id]) . '" class="dropdown-item"><i class="fa fa-eye mr-2"></i>' . __('app.view') . '</a>';

                if(user()->is_superadmin) {
                    $action .= '<a class="dropdown-item delete-table-row" href="javascript:;" data-ticket-id="' . $row->id . '">
                            <i class="fa fa-trash mr-2"></i>
                            ' . trans('app.delete') . '
                        </a>';
                }

                $action .= '</div>
                        </div>
                    </div>';

                return $action;
            })
            ->addColumn('others', function ($row) {
                $others = '';

                if (!is_null($row->agent)) {
                    $others .= '<div class="mb-2">' . __('modules.tickets.agent') . ': ' . (is_null($row->agent_id) ? '-' : ucwords($row->agent->name)) . '</div> ';
                }

                $badgeClass = match ($row->status) {
                    'open' => 'badge-danger',
                    'pending' => 'badge-warning',
                    'resolved' => 'badge-success',
                    'closed' => 'badge-primary',
                    default => 'badge-secondary',
                };
                $others .= '<div>' . __('app.status') . ': <label class="badge ' . $badgeClass . '">' . __('app.' . $row->status) . '</label></div> ';
                $others .= '<div>' . __('modules.tasks.priority') . ': ' . __('app.' . $row->priority) . '</div> ';

                return $others;
            })

            ->editColumn('subject', function ($row) {
                return '<a href="' . route('superadmin.support-tickets.show', $row->id) . '" class="text-darkest-grey" >' . ucfirst($row->subject) . '</a>';
            })
            ->addColumn('name', function ($row) {
                return $row->requester?->name ?? '--';
            })
            ->editColumn('user_id', function ($row) {
                $name = $row->requester?->name;
                $company = $row->company?->company_name;

                if ($company) {
                    $name .= ' ( '.$company.' )';
                }

                return ucwords($name ?? '--');
            })
            ->editColumn('created_at', function ($row) {
                return $row->created_at->timezone(global_setting()->timezone)->format(global_setting()->date_format . ' ' . global_setting()->time_format);
            })
            ->setRowId(function ($row) {
                return 'row-' . $row->id;
            })
            ->rawColumns(['others', 'action', 'subject', 'check'])
            ->removeColumn('agent_id')
            ->removeColumn('channel_id')
            ->removeColumn('type_id')
            ->removeColumn('deleted_at');
    }

    /**
     * @param SupportTicket $model
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function query(SupportTicket $model)
    {
        $request = $this->request();
        $model = $model->with(['agent', 'requester', 'company']);

        if ($request->startDate) {
            $startDate = Carbon::createFromFormat(global_setting()->date_format, $request->startDate)->toDateString();
            $model->whereDate('created_at', '>=', $startDate);
        }

        if ($request->endDate) {
            $endDate = Carbon::createFromFormat(global_setting()->date_format, $request->endDate)->toDateString();
            $model->whereDate('created_at', '<=', $endDate);
        }

        if ($request->agentId && $request->agentId != 'all') {
            $model->where('agent_id', $request->agentId);
        }

        if ($request->self && $request->self == 'yes' && $request->agentId == 'all') {
            $model->where('agent_id', user()->id);
        }

        if ($request->ticketStatus && $request->ticketStatus != 'all') {
            if ($request->ticketStatus == 'unassigned') {
                $model->whereNull('agent_id');
            } else {
                $model->where('status', $request->ticketStatus);
            }
        }

        if ($request->priority && $request->priority != 'all') {
            $model->where('priority', $request->priority);
        }

        if ($request->typeId && $request->typeId != 'all') {
            $model->where('support_ticket_type_id', $request->typeId);
        }

        if ($request->searchText) {
            $model->where(function ($query) use ($request) {
                $query->where('subject', 'like', '%' . $request->searchText . '%')
                    ->orWhere('id', 'like', '%' . $request->searchText . '%')
                    ->orWhere('status', 'like', '%' . $request->searchText . '%')
                    ->orWhere('priority', 'like', '%' . $request->searchText . '%')
                    ->orWhereHas('requester', function ($query) use ($request) {
                        $query->where('name', 'like', '%' . $request->searchText . '%');
                    });
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
        return $this->setBuilder('supportticket-table', 5)
            ->parameters([
                'initComplete' => 'function () {
                    window.LaravelDataTables["supportticket-table"].buttons().container()
                    .appendTo("#table-actions")
                }',
                'fnDrawCallback' => 'function( oSettings ) {
                    $("body").tooltip({
                        selector: \'[data-toggle="tooltip"]\'
                    })
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
            'check' => [
                'title' => '<input type="checkbox" name="select_all_table" id="select-all-table" onclick="selectAllTable(this)">',
                'exportable' => false,
                'orderable' => false,
                'searchable' => false,
                'visible' => !in_array('admin', user_roles())
            ],
            __('modules.tickets.ticket') . ' #' => ['data' => 'id', 'name' => 'id', 'title' => __('modules.tickets.ticket') . ' #'],
            __('modules.tickets.ticketSubject')  => ['data' => 'subject', 'name' => 'subject', 'title' => __('modules.tickets.ticketSubject')],
            __('app.name') => ['data' => 'name', 'name' => 'user_id', 'visible' => false, 'title' => __('app.name')],
            __('modules.tickets.requesterName') => ['data' => 'user_id', 'name' => 'name', 'visible' => true, 'exportable' => false, 'title' => __('modules.tickets.requesterName')],
            __('modules.tickets.requestedOn') => ['data' => 'created_at', 'name' => 'created_at', 'title' => __('modules.tickets.requestedOn')],
            __('app.others') => ['data' => 'others', 'name' => 'others', 'sortable' => false, 'title' => __('app.others')],
            Column::computed('action', __('app.action'))
                ->exportable(false)
                ->printable(false)
                ->orderable(false)
                ->searchable(false)
                ->addClass('text-right pr-20')
        ];
    }

    /**
     * Get filename for export.
     *
     * @return string
     */
    protected function filename()
    {
        return 'tickets_' .now()->format('Y-m-d-H-i-s');
    }

}
