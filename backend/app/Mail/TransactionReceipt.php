<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;
use PDF;
use App\Models\Setting;

class TransactionReceipt extends Mailable
{
    use Queueable, SerializesModels;

    public $transaction;

    public function __construct($transaction)
    {
        $this->transaction = $transaction;
    }

    public function build()
    {
        $settings = [
            'currency_symbol' => Setting::getSetting('currency_symbol', 'NGN'),
            'site_name' => Setting::getSetting('site_name', 'TekiPlanet'),
            'support_email' => Setting::getSetting('support_email', 'support@tekiplanet.com')
        ];

        $pdf = PDF::loadView('receipts.transaction-advanced', [
            'transaction' => $this->transaction,
            'settings' => $settings
        ]);

        return $this->subject('Transaction Receipt')
                    ->view('emails.transaction-receipt')
                    ->attachData($pdf->output(), "transaction-{$this->transaction->reference_number}.pdf");
    }
} 