<?php
namespace App\Channels;

use App\Channels\WhatsApp\Client;
use Illuminate\Notifications\Notification;
use App\Models\WhatsApp;

class WhatsAppReferralChannel
{
    public function send($phone, Notification $notification)
    {
        $message = $notification->toWhatsApp();
        //dd($message);
        $type = 'text';
        $recipient = $phone;
        //dd($message,$recipient);
        $whatsApp = WhatsApp::where('company_id',company()->id)->where('status',1)->first();
        $apiSecret = $whatsApp->api_secret;
        $account_id = $whatsApp->account_id;

        $whatsAppClient = new Client($apiSecret,$account_id,$recipient,$type,$message);
        $response = $whatsAppClient->sendWhatsAppSms();
        //dd($response);
        return $response;
    }

    public function getWhatsAppNumber($apiSecret){

        $cURL = curl_init();
        curl_setopt($cURL, CURLOPT_URL, "https://whatsapp.h9crm.com/api/get/wa.accounts?secret={$apiSecret}");
        curl_setopt($cURL, CURLOPT_RETURNTRANSFER, true);
        $response = curl_exec($cURL);
        curl_close($cURL);

        $result = json_decode($response, true);
        if($result['data']===false){
            return null;
        }
        return  $result['data'][0]['id'];
    }
}
