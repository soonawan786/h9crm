<?php

namespace App\Notifications;

use App\Channels\Messages\WhatsAppMessage;
use App\Channels\WhatsAppReferralChannel;
use Illuminate\Bus\Queueable;
class ReferralInvoiceWhatsApp extends BaseNotification
{
    use Queueable;

    /**
     * Create a new notification instance.
     *
     * @return void
     */
    private $referralName;
    private $referralClient;

    public function __construct($referralClient,$referralName)
    {
        $this->referralClient = $referralClient;
        $this->referralName = $referralName;
        $this->company = $referralClient->company;

    }

    /**
     * Get the notification's delivery channels.
     *
     * @param  mixed  $notifiable
     * @return array
     */
    public function via()
    {
        return [WhatsAppReferralChannel::class];
    }

    /**
     * Get the mail representation of the notification.
     *
     * @param  mixed  $notifiable
     * @return \Illuminate\Notifications\Messages\MailMessage
     */
    public function toWhatsApp()
    {
        $clientName = 'Dear '. $this->referralName.', ';//'Dear '. $this->referralClient->name.', ';

        $content = __('email.referral.start_line').PHP_EOL. PHP_EOL.$clientName. PHP_EOL. PHP_EOL .__('email.referral.line1').$this->referralName.__('email.referral.line2').PHP_EOL.PHP_EOL.PHP_EOL.__('email.referral.line3').$this->referralClient->name.__('email.referral.line4').PHP_EOL.PHP_EOL.__('email.referral.line5').$this->referralClient->name.__('email.referral.line6').PHP_EOL.PHP_EOL.__('email.referral.line7').PHP_EOL.PHP_EOL.__('email.referral.line8').__('email.referral.line9').PHP_EOL.PHP_EOL.PHP_EOL.PHP_EOL.'Warm regards, '.PHP_EOL.PHP_EOL.$this->company->company_name.PHP_EOL.PHP_EOL.$this->company->company_phone;
        return (new WhatsAppMessage)
        ->content($content);

    }

    /**
     * Get the array representation of the notification.
     *
     * @param  mixed  $notifiable
     * @return array
     */
    public function toArray($notifiable)
    {
        return [
            //
        ];
    }
}
