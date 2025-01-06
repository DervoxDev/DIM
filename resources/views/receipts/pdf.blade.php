<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Receipt {{ $sale->reference_number }}</title>
    <style>
        /* Thermal receipt style */
        body {
            font-family: 'Courier New', monospace;
            width: 302px; /* 80mm at 96 DPI */
            margin: 0 auto;
            padding: 10px;
            font-size: 9pt;
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
        }
        .items td { 
            padding: 3px 0;
            font-size: 8pt;
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
        <p>Tel: {{ $sale->team->phone }}</p>
        <p>Receipt #: {{ $sale->reference_number }}</p>
        <p>Date: {{ $sale->sale_date->format('d/m/Y H:i') }}</p>
    </div>

    <table class="items">
        <tr>
            <th>Item</th>
            <th>Qty</th>
            <th>Price</th>
            <th>Total</th>
        </tr>
        @foreach($sale->items as $item)
        <tr>
            <td>{{ $item->product->name }}</td>
            <td>{{ $item->quantity }}</td>
            <td>{{ number_format($item->unit_price, 2) }}</td>
            <td>{{ number_format($item->total_price, 2) }}</td>
        </tr>
        @endforeach
    </table>

    <div class="totals">
        <p>Subtotal: {{ number_format($sale->total_amount - $sale->tax_amount, 2) }}</p>
        <p>Tax: {{ number_format($sale->tax_amount, 2) }}</p>
        @if($sale->discount_amount > 0)
        <p>Discount: -{{ number_format($sale->discount_amount, 2) }}</p>
        @endif
        <p><strong>Total: {{ number_format($sale->total_amount, 2) }}</strong></p>
    </div>

    <div class="footer">
        <p>Thank you for your purchase!</p>
    </div>
</body>
</html>
