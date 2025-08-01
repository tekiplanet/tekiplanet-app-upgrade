@php
    use App\Models\Setting;
@endphp
<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <title>Transaction Receipt</title>
    <style>
        @page {
            size: 8.5cm 21cm;  /* Standard receipt width and reasonable height */
            margin: 0;
        }
        body {
            font-family: 'Helvetica', sans-serif;
            margin: 0;
            padding: 0;
            background: #fff;
            color: #333;
            font-size: 12px;  /* Reduced font size for smaller receipt */
            line-height: 1.4;
        }
        .receipt {
            width: 8cm;  /* Slightly less than page width to ensure margins */
            margin: 0 auto;
            padding: 20px;  /* Reduced padding */
            border: 1px solid #ddd;
        }
        .header {
            text-align: center;
            border-bottom: 2px solid #141F78;
            padding-bottom: 15px;
            margin-bottom: 20px;
        }
        .logo {
            max-width: 120px;  /* Smaller logo */
            margin-bottom: 10px;
        }
        .receipt-title {
            font-size: 18px;  /* Smaller title */
            color: #141F78;
            margin: 8px 0;
            font-weight: bold;
        }
        .receipt-number {
            color: #666;
            font-size: 14px;
        }
        .status-stamp {
            position: absolute;
            top: 60px;
            right: 30px;
            transform: rotate(-15deg);
            font-size: 18px;  /* Smaller stamp */
            font-weight: bold;
            padding: 8px 15px;
            border: 2px solid;
            border-radius: 8px;
            opacity: 0.5;
        }
        .status-completed {
            color: #059669;
            border-color: #059669;
        }
        .status-pending {
            color: #D97706;
            border-color: #D97706;
        }
        .status-failed {
            color: #DC2626;
            border-color: #DC2626;
        }
        .status-cancelled {
            color: #4B5563;
            border-color: #4B5563;
        }
        .section {
            margin-bottom: 20px;
        }
        .section-title {
            font-size: 14px;
            font-weight: bold;
            color: #141F78;
            border-bottom: 1px solid #ddd;
            padding-bottom: 4px;
            margin-bottom: 10px;
        }
        .info-grid {
            display: grid;
            grid-template-columns: 1fr;  /* Single column for narrow receipt */
            gap: 10px;
        }
        .info-item {
            margin-bottom: 8px;
        }
        .label {
            color: #666;
            font-size: 10px;
            margin-bottom: 2px;
        }
        .value {
            font-weight: 600;
            color: #333;
            font-size: 12px;
        }
        .amount {
            font-size: 16px;
            font-weight: bold;
            color: #141F78;
        }
        .credit {
            color: #059669;
        }
        .debit {
            color: #DC2626;
        }
        .footer {
            margin-top: 20px;
            padding-top: 15px;
            border-top: 1px solid #ddd;
            text-align: center;
            color: #666;
            font-size: 10px;
        }
    </style>
</head>
<body>
    <div class="receipt">
        <div class="header">
            <img src="{{ public_path('images/logo.png') }}" alt="TekiPlanet" class="logo">
            <div class="receipt-title">Transaction Receipt</div>
            <div class="receipt-number">Ref: {{ $transaction->reference_number }}</div>
        </div>

        <!-- Status Stamp -->
        <div class="status-stamp status-{{ $transaction->status }}">
            {{ strtoupper($transaction->status) }}
        </div>

        <div class="section">
            <div class="section-title">Transaction Details</div>
            <div class="info-grid">
                <div class="info-item">
                    <div class="label">Date & Time</div>
                    <div class="value">{{ $transaction->created_at->format('M d, Y h:i A') }}</div>
                </div>
                <div class="info-item">
                    <div class="label">Type</div>
                    <div class="value">{{ ucfirst($transaction->type) }}</div>
                </div>
                <div class="info-item">
                    <div class="label">Amount</div>
                    <div class="value amount {{ $transaction->type === 'credit' ? 'credit' : 'debit' }}">
                        {{ $transaction->type === 'credit' ? '+' : '-' }} 
                        @if($settings['currency_symbol'] === '₦' || $settings['currency_symbol'] === 'NGN')
                            NGN {{ number_format($transaction->amount, 2) }}
                        @else
                            {{ $settings['currency_symbol'] }}{{ number_format($transaction->amount, 2) }}
                        @endif
                    </div>
                </div>
                <div class="info-item">
                    <div class="label">Status</div>
                    <div class="value">{{ ucfirst($transaction->status) }}</div>
                </div>
            </div>
        </div>

        <div class="section">
            <div class="section-title">Account Information</div>
            <div class="info-grid">
                <div class="info-item">
                    <div class="label">Account Holder</div>
                    <div class="value">{{ $transaction->user->full_name }}</div>
                </div>
                <div class="info-item">
                    <div class="label">Email</div>
                    <div class="value">{{ $transaction->user->email }}</div>
                </div>
                @if($transaction->payment_method)
                <div class="info-item">
                    <div class="label">Payment Method</div>
                    <div class="value">{{ $transaction->payment_method }}</div>
                </div>
                @endif
                <div class="info-item">
                    <div class="label">Current Balance</div>
                    <div class="value">
                        @if($settings['currency_symbol'] === '₦' || $settings['currency_symbol'] === 'NGN')
                            NGN {{ number_format($transaction->user->wallet_balance, 2) }}
                        @else
                            {{ $settings['currency_symbol'] }}{{ number_format($transaction->user->wallet_balance, 2) }}
                        @endif
                    </div>
                </div>
            </div>
        </div>

        @if($transaction->description)
        <div class="section">
            <div class="section-title">Description</div>
            <div class="value">{{ $transaction->description }}</div>
        </div>
        @endif

        <div class="footer">
            <p>This is an electronically generated receipt.</p>
            <p>© {{ date('Y') }} {{ $settings['site_name'] }}.</p>
            <p>{{ $settings['support_email'] }}</p>
        </div>
    </div>
</body>
</html> 