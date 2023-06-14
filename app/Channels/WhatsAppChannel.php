<?php
namespace App\Channels;

use App\Channels\WhatsApp\Client;
use Illuminate\Notifications\Notification;
use App\Models\WhatsApp;

class WhatsAppChannel
{
    public function send($notifiable, Notification $notification)
    {
        $message = $notification->toWhatsApp($notifiable);
        //dd($message);
        $type = 'text';
        $recipient = $notifiable->routeNotificationFor('WhatsApp');
        //dd($message,$recipient);
        $apiSecret = auth()->user()->api_secret;
        $account_id = $this->getWhatsAppNumber($apiSecret);

        $whatsAppClient = new Client($account_id,$recipient,$type,$message);

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
