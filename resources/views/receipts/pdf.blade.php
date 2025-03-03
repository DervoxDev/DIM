<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Receipt {{ $sale->reference_number }}</title>
    <style>
        @page {
            margin: 40pt 10pt 10pt 10pt; /* Extra large top margin */
        }
        
        body {
            font-family: Courier, monospace;
            font-size: 10pt;
            line-height: 1.2;
            padding: 0;
            margin: 0;
        }
        
        .receipt-content {
            padding-top: 50pt; /* Extreme top padding */
        }
        
        .text-center {
            text-align: center;
        }
        
        table {
            width: 100%;
            border-collapse: collapse;
            margin: 10pt 0;
        }
        
        .header {
            margin-bottom: 10pt;
            padding-top: 30pt; /* Additional top padding */
        }
        
        .header h2 {
            margin: 5pt 0;
            font-size: 12pt;
        }
        
        .header p {
            margin: 3pt 0;
        }
        
        .items th {
            border-top: 1pt solid #000;
            border-bottom: 1pt solid #000;
            padding: 3pt 1pt;
            text-align: left;
            font-size: 9pt;
        }
        
        .items td {
            padding: 3pt 1pt;
            font-size: 8pt;
        }
        
        .totals {
            text-align: right;
            margin-top: 10pt;
            border-top: 1pt solid #000;
            padding-top: 5pt;
        }
        
        .totals p {
            margin: 3pt 0;
        }
        
        .footer {
            text-align: center;
            margin-top: 15pt;
            border-top: 1pt solid #000;
            padding-top: 5pt;
        }
        
        /* Multiple spacers for extra safety */
        .spacer-1 { height: 20pt; }
        .spacer-2 { height: 20pt; }
        .spacer-3 { height: 20pt; }
    </style>
</head>
<body>
    <!-- Multiple spacer divs to push content down -->
    <div class="spacer-1"></div>
    <div class="spacer-2"></div>
    <div class="spacer-3"></div>
    
    <div class="receipt-content">
        <div class="header text-center">
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
            <p>Subtotal: {{ number_format($sale->total_amount - $sale->tax_amount, 2) }} DH</p>
            <p>Tax: {{ number_format($sale->tax_amount, 2) }} DH</p>
            @if($sale->discount_amount > 0)
            <p>Discount: -{{ number_format($sale->discount_amount, 2) }} DH</p>
            @endif
            <p><strong>Total: {{ number_format($sale->total_amount, 2) }} DH</strong></p>
        </div>
        
        <div class="footer">
            <p>Thank you for your business</p>
        </div>
    </div>
</body>
</html>
