<style>
    .task_view{
        border: 0px !important;
    }
    .action-hover:hover{
        background-color: #ffffff !important;
    }
</style>
<div class="modal-header">
    <h5 class="modal-title">@lang('app.total') @lang('app.leave') ( {{$multipleLeaves[0]->user->name}} )</h5>
    <button type="button" class="close" data-dismiss="modal" aria-hidden="true">×</button>
</div>
<div class="col-lg-12 col-md-12 ntfcn-tab-content-left w-100 p-0">
    <x-table class="table-sm-responsive table mb-0">
        <x-slot name="thead">
            <th>@lang('app.leaveDate')</th>
            <th>@lang('app.leaveType')</th>
            <th>@lang('app.status')</th>
            <th class="text-right pr-20">@lang('app.action')</th>
        </x-slot>

        @forelse($multipleLeaves as $leave)
            <tr class="row{{ $leave->id }}">
                <td>
                    {{\Carbon\Carbon::parse($leave->leave_date)->translatedFormat(company()->date_format)}}
                </td>
                <td>
                    <span class="badge badge-success" style="background-color:{{$leave->type->color}}">{{ $leave->type->type_name }}</span>
                </td>
                <td>
                    @php
                        if ($leave->status == 'approved') {
                            $class = 'text-light-green';
                            $status = __('app.approved');
                        }
                        else if ($leave->status == 'pending') {
                            $class = 'text-yellow';
                            $status = __('app.pending');
                        }
                        else {
                            $class = 'text-red';
                            $status = __('app.rejected');
                        }
                    @endphp

                    <i class="fa fa-circle mr-1 {{$class}} f-10"></i> {{$status}}
                </td>
                    <td class="text-right">
                        @if ($leave->status == 'pending')
                            <div class="task_view">
                                <a class="dropdown-item leave-action-approved action-hover" data-leave-id={{ $leave->id }}
                                    data-leave-action="approved" data-toggle="tooltip" data-original-title="@lang('app.approve')" data-leave-type-id="{{ $leave->leave_type_id }}" href="javascript:;">
                                        <i class="fa fa-check mr-2"></i>
                                </a>
                            </div>
                            <div class="task_view mt-1 mt-lg-0 mt-md-0">
                                <a class="dropdown-item leave-action-reject action-hover" data-leave-id={{ $leave->id }}
                                    data-leave-action="rejected" data-toggle="tooltip" data-original-title="@lang('app.reject')" data-leave-type-id="{{ $leave->leave_type_id }}"  href="javascript:;">
                                        <i class="fa fa-times mr-2"></i>
                                </a>
                            </div>
                        @endif
                        <div class="task_view mt-1 mt-lg-0 mt-md-0">
                            <a data-leave-id={{$leave->id}} data-type="multiple-leave" data-unique-id="{{$leave->unique_id}}"
                                class="dropdown-item delete-table-row action-hover"  data-toggle="tooltip" data-original-title="@lang('app.delete')" href="javascript:;">
                                <i class="fa fa-trash mr-2"></i>
                            </a>
                        </div>
                    </td>
            </tr>
        @empty
            <tr>
                <td colspan="4">
                    <x-cards.no-record icon="user" :message="__('messages.noAgentAdded')" />
                </td>
            </tr>
        @endforelse
    </x-table>
</div>
<div class="modal-footer">
    <x-forms.button-cancel data-dismiss="modal" class="border-0 mr-3">@lang('app.cancel')</x-forms.button-cancel>
</div>
