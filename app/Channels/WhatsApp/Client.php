<?php
namespace App\Channels\WhatsApp;


Class Client{

    public $secret;
    public $sender;
    public $recipient;
    public $type;
    public $message;
    public $account_id;

    public function __construct($account_id,$recipient,$type,$message)
    {
        $this->secret = (auth()->user()->api_secret != null) ? auth()->user()->api_secret : "9e5ec7c5f8db3aa044f788a15ac60e824bc93699";
        $this->recipient = $recipient;
        $this->type = $type;
        $this->message = $message;
        $this->account_id = $account_id;

    }
    public function sendWhatsAppSms(){
        $chat = [
            "secret" => $this->secret, // your API secret from (Tools -> API Keys) page
            "account" => $this->account_id,
            "recipient" => $this->recipient,
            "type" => $this->type,
            "message" => $this->message->content
        ];
        $cURL = curl_init("https://whatsapp.h9crm.com/api/send/whatsapp");
        curl_setopt($cURL, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($cURL, CURLOPT_POSTFIELDS, $chat);
        $response = curl_exec($cURL);
        curl_close($cURL);

         $result = json_decode($response, true);
         return $result;
    }

}
