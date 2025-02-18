<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <title>{{ __('receipt.receipt_number') }} {{ $sale->reference_number }}</title>
    <style>
        /* Thermal receipt style */
        body {
            font-family: 'Courier New', monospace;
            width: 302px; /* 80mm at 96 DPI */
            margin: 0 auto;
            padding: 10px;
            font-size: 9pt;
            direction: {{ in_array(app()->getLocale(), ['ar']) ? 'rtl' : 'ltr' }};
        }
        .header { 
            text-align: center; 
            margin-bottom: 10px; 
        }
        .header h2 {
            margin: 5px 0;
            font-size: 12pt;
        }
        .header p {
            margin: 3px 0;
        }
        .items { 
            width: 100%; 
            margin: 10px 0;
            border-collapse: collapse;
        }
        .items th {
        border-top: 1px solid #000;
        border-bottom: 1px solid #000;
        padding: 3px 0;
        text-align: left; /* Align header text to left */
    }
    
    .items td {
        padding: 3px 0;
        padding-left: 3px; /* Add left padding to content cells */
        font-size: 8pt;
        text-align: left; /* Align content to left */
    }
        .totals { 
            margin: 10px 0; 
            text-align: right;
            border-top: 1px solid #000;
            padding-top: 5px;
        }
        .totals p {
            margin: 3px 0;
        }
        .footer { 
            text-align: center; 
            font-size: 8pt; 
            margin-top: 10px;
            border-top: 1px solid #000;
            padding-top: 5px;
        }
        /* Add more styles as needed */
    </style>
</head>
<body>
    <div class="header">
    <h2>{{ $sale->team->name }}</h2>
        <p>{{ $sale->team->address }}</p>
        <p>{{ __('receipt.tel') }}: {{ $sale->team->phone }}</p>
        <p>{{ __('receipt.receipt_number') }}: {{ $sale->reference_number }}</p>
        <p>{{ __('receipt.date') }}: {{ $sale->sale_date->format('d/m/Y H:i') }}</p>
    </div>

    <table class="items">
        <tr>
        <th>{{ __('receipt.item') }}</th>
            <th>{{ __('receipt.quantity') }}</th>
            <th>{{ __('receipt.tax') }}</th>
            <th>{{ __('receipt.price') }}</th>
            <th>{{ __('receipt.total') }}</th>
        </tr>
        @foreach($sale->items as $item)
        <tr>
            <td>{{ $item->product->name }}</td>
            <td>{{ $item->quantity }}</td>
            <td>{{ number_format($item->tax_amount, 2) }}</td>
            <td>{{ number_format($item->unit_price, 2) }}</td>
            <td>{{ number_format($item->total_price, 2) }}</td>
        </tr>
        @endforeach
    </table>

    <div class="totals">
        <p>{{ __('receipt.subtotal') }}: {{ number_format($sale->total_amount - $sale->tax_amount, 2) }} {{ __('receipt.currency') }}</p>
        <p>{{ __('receipt.tax') }}: {{ number_format($sale->tax_amount, 2) }} {{ __('receipt.currency') }}</p>
        @if($sale->discount_amount > 0)
        <p>{{ __('receipt.discount') }}: -{{ number_format($sale->discount_amount, 2) }} {{ __('receipt.currency') }}</p>
        @endif
        <p><strong>{{ __('receipt.total') }}: {{ number_format($sale->total_amount, 2) }} {{ __('receipt.currency') }}</strong></p>
    </div>

    <div class="footer">
        <p>{{ __('receipt.thank_you') }}</p>
    </div>
</body>
</html>
