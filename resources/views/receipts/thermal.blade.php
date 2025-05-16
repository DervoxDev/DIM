<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>{{ __('receipt.receipt_number') }}: {{ $sale->reference_number }}</title>
    <style>
        /* Custom styling for 80mm thermal receipt */
        body {
            font-family: 'Courier New', Courier, monospace;
            font-size: 9pt;
            margin: 0;
            padding: 0;
            width: 74mm;
        }
        .receipt {
            width: 100%;
        }
        .header {
            text-align: center;
            margin-bottom: 5mm;
        }
        h2 {
            font-size: 11pt;
            margin: 0 0 2mm 0;
        }
        p {
            margin: 0 0 1mm 0;
        }
        table {
            width: 100%;
            border-collapse: collapse;
        }
        th {
            text-align: left;
            border-top: 1px solid #000;
            border-bottom: 1px solid #000;
            font-size: 8pt;
            padding: 1mm 0;
        }
        td {
            font-size: 8pt;
            padding: 1mm 0;
        }
        .item-col { width: 40%; }
        .qty-col { width: 15%; text-align: center; }
        .tax-col { width: 15%; text-align: right; }
        .price-col { width: 15%; text-align: right; }
        .total-col { width: 15%; text-align: right; }
        
        .totals {
            text-align: right;
            border-top: 1px solid #000;
            padding-top: 1mm;
            margin-top: 3mm;
        }
        .footer {
            text-align: center;
            border-top: 1px solid #000;
            padding-top: 2mm;
            margin-top: 3mm;
            font-size: 8pt;
        }
    </style>
</head>
<body>
    <div class="receipt">
        <div class="header">
            <h2>{{ $sale->team->name }}</h2>
            <p>{{ $sale->team->address }}</p>
            <p>{{ __('receipt.tel') }}: {{ $sale->team->phone }}</p>
            <p>{{ __('receipt.receipt_number') }}: {{ $sale->reference_number }}</p>
            <p>{{ __('receipt.date') }}: {{ $sale->updated_at->format('d/m/Y H:i') }}</p>
        </div>
        
        <table>
            <tr>
                <th class="item-col">{{ __('receipt.item') }}</th>
                <th class="qty-col">{{ __('receipt.qty') }}</th>
                <th class="tax-col">{{ __('receipt.tax') }}</th>
                <th class="price-col">{{ __('receipt.price') }}</th>
                <th class="total-col">{{ __('receipt.total') }}</th>
            </tr>
            
            @foreach($sale->items as $item)
            <tr>
                <td class="item-col">{{ $item->product->name }}</td>
                <td class="qty-col">{{ $item->quantity }}</td>
                <td class="tax-col">{{ number_format($item->tax_amount, 2) }}</td>
                <td class="price-col">{{ number_format($item->unit_price, 2) }}</td>
                <td class="total-col">{{ number_format($item->total_price, 2) }}</td>
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
    </div>
</body>
</html>
