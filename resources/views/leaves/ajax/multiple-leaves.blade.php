@php
$editLeavePermission = user()->permission('edit_leave');
$deleteLeavePermission = user()->permission('delete_leave');
$approveRejectPermission = user()->permission('approve_or_reject_leaves');
@endphp

<!-- ROW START -->
<div class="row">
    <!--  USER CARDS START -->
    <div class="col-xl-12 col-lg-12 col-md-12 mb-4 mb-xl-0 mb-lg-4 mb-md-0">

        <x-cards.data :title="__('modules.leaves.multiple').' '.__('app.leave')"
            otherClasses="border-0 p-0 d-flex justify-content-between align-items-center table-responsive-sm">
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
                            <td class="text-right pr-20">
                                <div class="task_view">
                                    <div class="dropdown">
                                        <a class="task_view_more d-flex align-items-center justify-content-center dropdown-toggle" type="link" id="dropdownMenuLink-41" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                                            <i class="icon-options-vertical icons"></i>
                                        </a>
                                        <div class="dropdown-menu dropdown-menu-right" aria-labelledby="dropdownMenuLink-41" tabindex="0" x-placement="bottom-end" style="position: absolute; transform: translate3d(-137px, 26px, 0px); top: 0px; left: 0px; will-change: transform;">
                                            <a href="{{route('leaves.show', $leave->id) }}?type=single" class="dropdown-item openRightModal"><i class="fa fa-eye mr-2"></i>@lang('app.view')</a>

                                            @if ($leave->status == 'pending')
                                                <a class="dropdown-item leave-action-approved" data-leave-id={{ $leave->id }}
                                                    data-leave-action="approved" data-user-id="{{ $leave->user_id }}" data-leave-type-id="{{ $leave->leave_type_id }}" href="javascript:;">
                                                    <i class="fa fa-check mr-2"></i>@lang('app.approve')
                                                </a>
                                                <a data-leave-id={{ $leave->id }}
                                                        data-leave-action="rejected" data-user-id="{{ $leave->user_id }}" data-leave-type-id="{{ $leave->leave_type_id }}" class="dropdown-item leave-action-reject" href="javascript:;">
                                                        <i class="fa fa-times mr-2"></i>@lang('app.reject')
                                                </a>
                                                @if ($editLeavePermission == 'all'
                                                || ($editLeavePermission == 'added' && user()->id == $leave->added_by)
                                                || ($editLeavePermission == 'owned' && user()->id == $leave->user_id)
                                                || ($editLeavePermission == 'both' && (user()->id == $leave->user_id || user()->id == $leave->added_by))
                                                )
                                                    <div class="mt-1 mt-lg-0 mt-md-0">
                                                        <a class="dropdown-item openRightModal" href="{{ route('leaves.edit', $leave->id) }}">
                                                            <i class="fa fa-edit mr-2"></i>@lang('app.edit')
                                                    </a>
                                                    </div>
                                                @endif
                                            @endif

                                            @if ($deleteLeavePermission == 'all'
                                            || ($deleteLeavePermission == 'added' && user()->id == $leave->added_by)
                                            || ($deleteLeavePermission == 'owned' && user()->id == $leave->user_id)
                                            || ($deleteLeavePermission == 'both' && (user()->id == $leave->user_id || user()->id == $leave->added_by)))
                                                <div class="mt-1 mt-lg-0 mt-md-0">
                                                    <a data-leave-id="{{ $leave->id }}" data-unique-id=" {{ $leave->unique_id }}"
                                                        data-duration="{{ $leave->duration }}" class="dropdown-item delete-multiple-leave" href="javascript:;">
                                                           <i class="fa fa-trash mr-2"></i>@lang('app.delete')
                                                    </a>
                                                </div>
                                            @endif
                                        </div>
                                    </div>
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

        </x-cards.data>
    </div>
    <!--  USER CARDS END -->
</div>
<!-- ROW END -->

<script>
    $('body').on('click', '.leave-action-approved', function() {
        let action = $(this).data('leave-action');
        let leaveId = $(this).data('leave-id');
        let searchQuery = "?leave_action=" + action + "&leave_id=" + leaveId;
        let url = "{{ route('leaves.show_approved_modal') }}" + searchQuery;

        $(MODAL_LG + ' ' + MODAL_HEADING).html('...');
        $.ajaxModal(MODAL_LG, url);
    });

    $('body').on('click', '.leave-action-reject', function() {
        let action = $(this).data('leave-action');
        let leaveId = $(this).data('leave-id');
        let searchQuery = "?leave_action=" + action + "&leave_id=" + leaveId;
        let url = "{{ route('leaves.show_reject_modal') }}" + searchQuery;

        $(MODAL_LG + ' ' + MODAL_HEADING).html('...');
        $.ajaxModal(MODAL_LG, url);
    });

    $('body').on('click', '.delete-multiple-leave', function() {
        var type = $(this).data('type');
        var id = $(this).data('leave-id');
        Swal.fire({
            title: "@lang('messages.sweetAlertTitle')",
            text: "@lang('messages.recoverRecord')",
            icon: 'warning',
            showCancelButton: true,
            focusConfirm: false,
            confirmButtonText: "@lang('messages.confirmDelete')",
            cancelButtonText: "@lang('app.cancel')",
            customClass: {
                confirmButton: 'btn btn-primary mr-3',
                cancelButton: 'btn btn-secondary'
            },
            showClass: {
                popup: 'swal2-noanimation',
                backdrop: 'swal2-noanimation'
            },
            buttonsStyling: false
        }).then((result) => {
            if (result.isConfirmed) {
                var url = "{{ route('leaves.destroy', ':id') }}";
                url = url.replace(':id', id);

                var token = "{{ csrf_token() }}";

                $.easyAjax({
                    type: 'POST',
                    url: url,
                    blockUI: true,
                    data: {
                        '_token': token,
                        '_method': 'DELETE'
                    },
                    success: function(response) {
                        if(response.status == "success"){
                            if(response.redirectUrl == undefined){
                                window.location.reload();
                            } else{
                                window.location.href = response.redirectUrl;
                            }
                        }
                    }
                });
            }
        });
    });
</script>
