<?php

namespace App\DataTables\SuperAdmin;

use App\DataTables\BaseDataTable;
use App\Models\User;
use App\Scopes\ActiveScope;
use App\Scopes\CompanyScope;
use Yajra\DataTables\Html\Column;
use Yajra\DataTables\Services\DataTable;

class SuperAdminDataTable extends BaseDataTable
{

    /**
     * Build DataTable class.
     *
     * @param mixed $query Results from query() method.
     * @return \Yajra\DataTables\DataTableAbstract
     */
    public function dataTable($query)
    {
        $firstSuperAdmin = User::firstSuperAdmin();

        return datatables()
            ->eloquent($query)
            ->addIndexColumn()
            ->addColumn('action', function ($row) use ($firstSuperAdmin) {
                $action = '<div class="task_view">

                <div class="dropdown">
                    <a class="task_view_more d-flex align-items-center justify-content-center dropdown-toggle" type="link"
                        id="dropdownMenuLink-' . $row->id . '" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                        <i class="icon-options-vertical icons"></i>
                    </a>
                    <div class="dropdown-menu dropdown-menu-right" aria-labelledby="dropdownMenuLink-' . $row->id . '" tabindex="0">';
                $action .= '<a class="dropdown-item openRightModal" href="' . route('superadmin.superadmin.edit', $row->id) . '" >
                    <i class="fa fa-edit mr-2"></i>
                    ' . trans('app.edit') . '
                </a>';

                if (user()->id != $row->id && $firstSuperAdmin->id != $row->id) {
                    $action .= '<a class="dropdown-item delete-table-row" href="javascript:;" data-toggle="tooltip"  data-superadmin-id="' . $row->id . '">
                            <i class="fa fa-trash mr-2"></i>
                            ' . trans('app.delete') . '
                        </a>';
                }

                $action .= '</div>
                </div>
            </div>';

                return $action;
            })
            ->editColumn('status', function ($row) {
                $status = $row->status == 'active' ? 'text-light-green' : 'text-red';
                return '<i class="fa fa-circle mr-1 ' . $status . ' f-10"></i>' . __('app.' . $row->status);
            })
            ->rawColumns(['action', 'status']);
    }

    /**
     * Get query source of dataTable.
     *
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function query(User $model)
    {
        $searchText = request('searchText');

        return $model->newQuery()
            ->withoutGlobalScopes([ActiveScope::class, CompanyScope::class])
            ->where(function ($query) use ($searchText) {
                $query->where('name', 'like', "%$searchText%")
                    ->orWhere('email', 'like', "%$searchText%");
            })
            ->where('is_superadmin', 1)
            ->whereNull('company_id');
    }


    /**
     * Optional method if you want to use html builder.
     *
     * @return \Yajra\DataTables\Html\Builder
     */
    public function html()
    {
        return $this->setBuilder('superadmin-table', 0)
            ->parameters([
                'initComplete' => 'function () {
                   window.LaravelDataTables["superadmin-table"].buttons().container()
                    .appendTo("#table-actions")
                }',
                'fnDrawCallback' => 'function( oSettings ) {
                    $("body").tooltip({
                        selector: \'[data-toggle="tooltip"]\'
                    })
                }',
            ]);
    }

    /**
     * Get columns.
     *
     * @return array
     */
    protected function getColumns()
    {
        return [
            '#' => ['data' => 'id', 'name' => 'id', 'visible' => false],
            __('app.name') => ['data' => 'name', 'name' => 'name', 'title' => __('app.name')],
            __('app.email') => ['data' => 'email', 'name' => 'email', 'title' => __('app.email')],
            __('app.status') => ['data' => 'status', 'name' => 'status', 'title' => __('app.status')],
            Column::computed('action', __('app.action'))
                ->exportable(false)
                ->printable(false)
                ->orderable(false)
                ->searchable(false)
                ->width(150)
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
        return 'super-admins_' .now()->format('Y-m-d-H-i-s');
    }

}
