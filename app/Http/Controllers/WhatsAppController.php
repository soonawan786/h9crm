<?php

namespace App\Http\Controllers;

use App\Helper\Reply;
use App\Models\EmployeeDetails;
use Illuminate\Support\Facades\Notification;
use App\Models\User;
use App\Models\WhatsApp;
use App\Notifications\BirthdayReminderWhatsApp;
use App\Notifications\TwoFactorCodeWhatsApp;
use App\Notifications\WhatsAppNotification;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;


class WhatsAppController extends  AccountBaseController
{
    public function __construct()
    {
        parent::__construct();

        $this->pageTitle = 'app.menu.whatsapp';
        $this->activeSettingMenu = 'whatsapp';

        $this->middleware(function ($request, $next) {

            abort_403(!in_array('whats_app', $this->user->modules));
            return $next($request);
        });
    }
    public function whatsapp(){
        $this->pageTitle = __('modules.whatsapp.integrate');
        $this->whats_app = WhatsApp::where('user_id',$this->user->id)->where('status',1)->first();
        $this->whats_app_numbers = WhatsApp::where('user_id',$this->user->id)->get();
        return view('whatsapp.create',$this->data);
    }
    public function whatsappStore(Request $request){
        $validateUser = Validator::make($request->all(),[
            'api_secret' => 'required',
            //'whatsapp_number' => 'required',
          ]);
          if ($validateUser->fails()) {
            return Reply::formErrors($validateUser->errors());
          }
          auth()->user()->update(['api_secret'=>$request->api_secret]);
          return Reply::success('messages.recordSaved');
        //get whatsapp account number to store or update
        $accountId = $this->getWhatsAppNumber($request->api_secret,$request->whatsapp_number);
        if($accountId !=null){
            $whatsApp = WhatsApp::updateOrCreate(
                    [
                        'user_id'   => $this->user->id,
                        'whatsapp_number'=>$request->whatsapp_number
                    ],
                    [
                        'user_id' => $this->user->id,
                        'api_secret' => $request->api_secret,
                        'whatsapp_number' => $request->whatsapp_number,
                        'account_id'=>$accountId
                    ]);
            if($whatsApp->count()==1){
                $whatsApp->update(['status'=>1]);
            }

            return Reply::success('messages.recordSaved');
        }
        else{
            $whatsAppRow = WhatsApp::where('whatsapp_number',$request->whatsapp_number)
                        ->where('user_id',$this->user->id)->first();
            if($whatsAppRow!=null){
                $whatsAppRow->delete();
                return Reply::error('WhatsApp Number not exist');
            }

            return Reply::error('WhatsApp Number not exist');
        }

    }
    public function getWhatsAppNumber($apiSecret,$phoneNumber){

        $cURL = curl_init();
        curl_setopt($cURL, CURLOPT_URL, "https://sms.legalbridge.com.pk/api/get/wa.accounts?secret={$apiSecret}");
        curl_setopt($cURL, CURLOPT_RETURNTRANSFER, true);
        $response = curl_exec($cURL);
        curl_close($cURL);

        $result = json_decode($response, true);
        if($result['data']===false){
            return null;
        }

        // do something with response
        $key = array_search($phoneNumber, array_column($result['data'], 'phone'));
        if($key === false){
            return null;
        }
        else{
            return $result['data'][$key]['id'];
        }
    }

    public function whatsappStatusChange(Request $request){
        WhatsApp::where('user_id',$this->user->id)->update(['status'=>0]);

        WhatsApp::where('id',$request->whats_app_number_id)
                        ->where('user_id',$this->user->id)->update(['status'=>1]);
        return Reply::success('messages.updateSuccess');

    }

    public function test(){

        //$users = User::allEmployees(null, false, null, $event->company->id);
        // $users = User::allEmployees(null, false, null, $this->company->id);
        // $currentDay = now()->format('m-d');
        // $event = EmployeeDetails::join('users', 'employee_details.user_id', '=', 'users.id')
        //         ->where('employee_details.company_id', $this->company->id)
        //         ->where('users.status', 'active')
        //         ->whereRaw('DATE_FORMAT(`date_of_birth`, "%m-%d") = "' . $currentDay . '"')
        //         ->orderBy('employee_details.date_of_birth')
        //         ->select('employee_details.company_id', 'employee_details.date_of_birth', 'users.name', 'users.image', 'users.id')
        //         ->get()->toArray();

        // Notification::send($users, new BirthdayReminderWhatsApp($event));
        //dd($this->user);
        $this->user->notify(new TwoFactorCodeWhatsApp());
        return 'hello';
    }
}
