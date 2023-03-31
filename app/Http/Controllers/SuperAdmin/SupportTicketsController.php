<?php

namespace App\Http\Controllers\SuperAdmin;

use App\Models\User;
use App\Helper\Reply;
use App\Models\Company;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Models\SuperAdmin\SupportTicket;
use App\Models\SuperAdmin\SupportTicketType;
use App\Models\SuperAdmin\SupportTicketReply;
use App\Http\Controllers\AccountBaseController;
use App\DataTables\SuperAdmin\SupportTicketDataTable;
use App\Http\Requests\SuperAdmin\SupportTickets\StoreRequest;

class SupportTicketsController extends AccountBaseController
{

    public function __construct()
    {
        parent::__construct();
        $this->pageTitle = 'superadmin.menu.supportTicket';
    }

    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */

    public function index(SupportTicketDataTable $dataTable)
    {
        if (!request()->ajax()) {
            $this->types = SupportTicketType::all();
            $this->superadmins = user()->is_superadmin ? User::allSuperAdmin() : [];
        }

        return $dataTable->render('super-admin.support-tickets.index', $this->data);
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        $this->pageTitle = __('modules.tickets.addTicket');
        $this->view = 'super-admin.support-tickets.ajax.create';
        $this->types = SupportTicketType::all();
        $this->superadmins = User::allSuperAdmin();
        $this->companines = Company::all();

        if (request()->ajax()) {
            $html = view($this->view, $this->data)->render();

            return Reply::dataOnly(['status' => 'success', 'html' => $html, 'title' => $this->pageTitle]);
        }

        return view('super-admin.support-tickets.create', $this->data);
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param StoreRequest $request
     * @return \Illuminate\Http\Response
     */
    public function store(StoreRequest $request)
    {
        $ticket = new SupportTicket();
        $ticket->subject = $request->subject;
        $ticket->description = $request->description;
        $ticket->status = 'open';

        if ($request->requested_for) {
            $companyUser = User::where('company_id', $request->requested_for)->orderBy('created_at', 'asc')->first();
            $ticket->user_id = $companyUser->id;
            $ticket->company_id = $request->requested_for;
        }

        $ticket->created_by = $this->user->id;

        $ticket->agent_id = $request->agent_id;

        if (is_null($request->agent_id)) {
            $superAdmin = User::firstSuperAdmin();
            $ticket->agent_id = $superAdmin ? $superAdmin->id : null;
        }

        $ticket->support_ticket_type_id = $request->type_id;
        $ticket->priority = $request->priority ?? 'low';
        $ticket->save();

        // Save first message
        $reply = new SupportTicketReply();
        $reply->message = $request->description;
        $reply->support_ticket_id = $ticket->id;
        $reply->user_id = $this->user->id; // Current logged in user
        $reply->save();

        return Reply::successWithData(__('superadmin.ticketAddSuccess'), ['redirectUrl' => route('superadmin.support-tickets.index'), 'replyID' => $reply->id]);
    }

    /**
     * Display the specified resource.
     *
     * @param int $id
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {
        $this->ticket = SupportTicket::with('requester', 'requester.supportTickets', 'reply', 'reply.files', 'reply.user')->findOrFail($id);
        $this->pageTitle = __('app.menu.ticket') . '#' . $this->ticket->id;
        $this->superadmins = User::allSuperAdmin();

        $this->types = SupportTicketType::all();
        $this->ticketChart = $this->ticketChartData($this->ticket->user_id);

        return view('super-admin.support-tickets.edit', $this->data);
    }

    public function ticketChartData($id)
    {
        $labels = ['open', 'pending', 'resolved', 'closed'];
        $data['labels'] = [__('app.open'), __('app.pending'), __('app.resolved'), __('app.closed')];
        $data['colors'] = ['#D30000', '#FCBD01', '#2CB100', '#1d82f5'];
        $data['values'] = [];

        foreach ($labels as $label) {
            $data['values'][] = SupportTicket::where('user_id', $id)->where('status', $label)->count();
        }

        return $data;
    }

    public function applyQuickAction(Request $request)
    {
        switch ($request->action_type) {
        case 'delete':
            $this->deleteRecords($request);

            return Reply::success(__('messages.deleteSuccess'));
        case 'change-status':
            $this->changeBulkStatus($request);

            return Reply::success(__('messages.updateSuccess'));
        default:
            return Reply::error(__('messages.selectAction'));
        }
    }

    protected function deleteRecords($request)
    {
        SupportTicket::whereIn('id', explode(',', $request->row_ids))->delete();
    }

    protected function changeBulkStatus($request)
    {
        SupportTicket::whereIn('id', explode(',', $request->row_ids))->update(['status' => $request->status]);
    }

    public function updateOtherData(Request $request, $id)
    {
        $ticket = SupportTicket::findOrFail($id);
        $ticket->agent_id = $request->agent_id;
        $ticket->support_ticket_type_id = $request->type_id;
        $ticket->priority = $request->priority;
        $ticket->status = $request->status;
        $ticket->save();

        return Reply::success(__('messages.updateSuccess'));
    }

    /**
     * Update the specified resource in storage.
     *
     * @param \Illuminate\Http\Request $request
     * @param int $id
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, $id)
    {
        $ticket = SupportTicket::findOrFail($id);
        $ticket->status = $request->status;
        $ticket->save();

        $message = str_replace('<p><br></p>', '', trim($request->message));

        if ($message != '') {
            $reply = new SupportTicketReply();
            $reply->message = $request->message;
            $reply->support_ticket_id = $ticket->id;
            $reply->user_id = $this->user->id; // Current logged in user
            $reply->save();

            return Reply::successWithData(__('messages.ticketReplySuccess'), ['reply_id' => $reply->id]);
        }

        return Reply::dataOnly(['status' => 'success']);
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param int $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
        abort_403(!user()->is_superadmin);
        SupportTicket::destroy($id);

        return Reply::success(__('messages.deleteSuccess'));
    }

}
