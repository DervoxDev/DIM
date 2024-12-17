@if(isset($invoice))
    <!DOCTYPE html>
    <html>
    <head>
        <meta charset="utf-8">
        <title>Invoice {{ $invoice->reference_number }}</title>
        <style>
            /* Your CSS styles */
        </style>
    </head>
    <body>
        <div class="invoice">
            @if(isset($invoice->team))
                <div class="company-details">
                    <h2>{{ $invoice->team->name }}</h2>
                    <!-- Other company details -->
                </div>
            @endif

            <div class="invoice-header">
                <h1>INVOICE</h1>
                <div class="invoice-info">
                    <p><strong>Invoice #:</strong> {{ $invoice->reference_number }}</p>
                    <p><strong>Date:</strong> {{ $invoice->issue_date->format('d/m/Y') }}</p>
                    <!-- Other invoice header details -->
                </div>
            </div>

            @if(isset($invoice->invoiceable))
                <div class="client-details">
                    <!-- Client details -->
                </div>
            @endif

            @if(isset($invoice->items) && count($invoice->items) > 0)
                <table class="items-table">
                    <thead>
                        <tr>
                            <th>Description</th>
                            <th>Quantity</th>
                            <th>Price</th>
                            <th>Total</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($invoice->items as $item)
                            <tr>
                                <td>{{ $item->description }}</td>
                                <td>{{ $item->quantity }}</td>
                                <td>{{ number_format($item->price, 2) }}</td>
                                <td>{{ number_format($item->total, 2) }}</td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            @endif

            <div class="invoice-totals">
                <p><strong>Subtotal:</strong> {{ number_format($invoice->subtotal, 2) }}</p>
                <p><strong>Tax:</strong> {{ number_format($invoice->tax, 2) }}</p>
                <p><strong>Total:</strong> {{ number_format($invoice->total, 2) }}</p>
            </div>
        </div>
    </body>
    </html>
@else
    <p>Error: Invoice data not found</p>
@endif
