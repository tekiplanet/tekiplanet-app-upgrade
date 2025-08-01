<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;
use App\Models\Transaction;
use App\Models\Setting;

class TransactionNotification extends Notification implements ShouldQueue
{
    use Queueable;

    public function __construct(protected Transaction $transaction)
    {
        //
    }

    public function via($notifiable)
    {
        return ['mail'];
    }

    public function toMail($notifiable)
    {
        $currency = [
            'code' => Setting::getSetting('default_currency', 'USD'),
            'symbol' => Setting::getSetting('currency_symbol', '$')
        ];

        return (new MailMessage)
            ->view('emails.transaction', [
                'user' => $notifiable,
                'transaction' => $this->transaction,
                'currency' => $currency,
                'greeting' => 'Hello ' . $notifiable->name . '!',                
                'closing' => 'Best regards, The TekiPlanet Team'
                
            ]);
    }
} 