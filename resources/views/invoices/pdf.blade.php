<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>{{ __('invoice.' . ($invoice->invoiceable_type === 'App\Models\Sale' ? 'invoice' : 'bill')) }} {{ $invoice->reference_number }}</title>
    <style>
    :root {
        --primary-color: #2563eb;
        --secondary-color: #1e40af;
        --text-color: #1f2937;
        --light-gray: #f3f4f6;
        --border-color: #e5e7eb;
    }

    @page {
        size: A4;
        margin: 0;
    }

    body {
        font-family: 'Arial', sans-serif;
        line-height: 1.4;
        color: var(--text-color);
        margin: 0;
        font-size: 10pt; /* Smaller base font size */
    }

    .invoice {
        width: 210mm; /* A4 width */
        min-height: 297mm; /* A4 height */
        padding: 15mm; /* Standard printer margin */
        background: white;
        box-sizing: border-box;
    }

    .header {
        display: flex;
        justify-content: space-between;
        align-items: start;
        margin-bottom: 15pt;
        padding-bottom: 10pt;
        border-bottom: 1px solid var(--border-color);
    }
    .logo {
    max-height: 60pt; /* Adjust size as needed */
    max-width: 200pt;
    margin-bottom: 10pt;
}

    .company-details {
    flex: 1;
    display: flex;
    flex-direction: column;
}
.logo-container {
    margin-bottom: 10pt;
}

    .company-details h2 {
        color: var(--primary-color);
        margin: 0 0 5pt 0;
        font-size: 14pt;
    }

    .company-details p {
        margin: 2pt 0;
    }

    .document-info {
        text-align: right;
    }

    .document-info h1 {
        color: var(--primary-color);
        font-size: 18pt;
        margin: 0 0 5pt 0;
    }

    .entities {
        display: flex;
        justify-content: space-between;
        margin: 10pt 0;
        gap: 15pt;
    }

    .entity-box {
        flex: 1;
        padding: 10pt;
        background: var(--light-gray);
        border-radius: 4pt;
    }

    .entity-box h3 {
        margin: 0 0 5pt 0;
    }

    .entity-box p {
        margin: 2pt 0;
    }

    .dates-and-status {
        display: flex;
        justify-content: space-between;
        margin: 10pt 0;
        gap: 10pt;
    }

    .date-box {
        flex: 1;
        padding: 8pt;
        background: var(--light-gray);
        border-radius: 4pt;
        text-align: center;
        font-size: 9pt;
    }

    .status-badge {
        display: inline-block;
        padding: 4pt 8pt;
        border-radius: 999px;
        font-weight: bold;
        text-transform: uppercase;
        font-size: 8pt;
    }

    .items-table {
        width: 100%;
        border-collapse: collapse;
        margin: 10pt 0;
        font-size: 9pt;
    }

    .items-table th {
        background: var(--primary-color);
        color: white;
        padding: 6pt;
        text-align: left;
    }

    .items-table td {
        padding: 6pt;
        border-bottom: 1px solid var(--border-color);
    }

    .items-table tr:nth-child(even) {
        background: var(--light-gray);
    }

    .totals {
        margin-left: auto;
        width: 200pt;
        font-size: 9pt;
    }

    .totals table {
        width: 100%;
    }

    .totals td {
        padding: 4pt;
    }

    .totals .grand-total {
        font-size: 11pt;
        font-weight: bold;
        color: var(--primary-color);
        border-top: 1px solid var(--border-color);
    }

    .notes {
        margin: 10pt 0;
        padding: 10pt;
        background: var(--light-gray);
        border-radius: 4pt;
        font-size: 9pt;
    }

    .notes h3 {
        margin: 0 0 5pt 0;
    }

    .footer {
        margin-top: 15pt;
        padding-top: 10pt;
        border-top: 1px solid var(--border-color);
        text-align: center;
        color: #6b7280;
        font-size: 8pt;
    }

    /* Status colors */
    .status-draft { background: #dbeafe; color: #1e40af; }
    .status-sent { background: #fef9c3; color: #854d0e; }
    .status-paid { background: #dcfce7; color: #166534; }
    .status-cancelled { background: #fee2e2; color: #991b1b; }

    /* Print-specific styles */
    @media print {
        body {
            -webkit-print-color-adjust: exact;
            print-color-adjust: exact;
        }
        
        .invoice {
            margin: 0;
            padding: 15mm;
            box-shadow: none;
        }
    }
</style>
</head>
<body>
    <div class="invoice">
        <!-- Header Section -->
        <div class="header">
        <div class="company-details">
    <div class="logo-container">
        @if($invoice->team->image_path)
            <img class="logo" src="{{ storage_path('app/public/' . $invoice->team->image_path) }}" alt="Company Logo">
        @endif
    </div>
    <h2>{{ $invoice->team->name }}</h2>
    <p>{{ $invoice->team->address ?? '' }}</p>
    <p>{{ $invoice->team->phone ?? '' }}</p>
    <p>{{ $invoice->team->email ?? '' }}</p>
    @if($invoice->team->tax_number)
        <p>{{ __('invoice.tax_number') }}: {{ $invoice->team->tax_number }}</p>
    @endif
</div>
<div class="document-info">
    <h1>{{ __('invoice.' . ($invoice->invoiceable_type === 'App\Models\Sale' ? 'invoice' : 'bill')) }}</h1>
    <p><strong>{{ __('invoice.reference_number') }}:</strong> {{ $invoice->reference_number }}</p>
    <span class="status-badge status-{{ $invoice->status }}">
        {{ __('invoice.status.' . $invoice->status) }}
    </span>
</div>
        </div>

       <!-- Entity Details (Client/Supplier) -->
@php
    $hasClient = $invoice->invoiceable_type === 'App\Models\Sale' && isset($invoice->invoiceable->client);
    $hasSupplier = $invoice->invoiceable_type === 'App\Models\Purchase' && isset($invoice->invoiceable->supplier);
@endphp

@if($hasClient || $hasSupplier)
<div class="entities">
    <div class="entity-box">
    <h3>{{ $invoice->invoiceable_type === 'App\Models\Sale' ? __('invoice.bill_to') : __('invoice.supplier') }}:</h3>
        @if($hasClient)
            @php $client = $invoice->invoiceable->client @endphp
            <p><strong>{{ $client->name }}</strong></p>
            @if($client->contact_person)
                <p>{{ __('invoice.attn') }}: {{ $client->contact_person }}</p>
            @endif
            @if($client->address)
                <p>{{ $client->address }}</p>
            @endif
            @if($client->email)
                <p>{{ __('invoice.email') }}: {{ $client->email }}</p>
            @endif
            @if($client->phone)
                <p>{{ __('invoice.phone') }}: {{ $client->phone }}</p>
            @endif
            @if($client->tax_number)
                <p>{{ __('invoice.tax_number') }}: {{ $client->tax_number }}</p>
            @endif
            @elseif($hasSupplier)
    @php $supplier = $invoice->invoiceable->supplier @endphp
    <p><strong>{{ $supplier->name }}</strong></p>
    @if($supplier->contact_person)
        <p>{{ __('invoice.attn') }}: {{ $supplier->contact_person }}</p>
    @endif
    @if($supplier->address)
        <p>{{ $supplier->address }}</p>
    @endif
    @if($supplier->email)
        <p>{{ __('invoice.email') }}: {{ $supplier->email }}</p>
    @endif
    @if($supplier->phone)
        <p>{{ __('invoice.phone') }}: {{ $supplier->phone }}</p>
    @endif
    @if($supplier->tax_number)
        <p>{{ __('invoice.tax_number') }}: {{ $supplier->tax_number }}</p>
    @endif
@endif
    </div>
</div>
@endif

        <!-- Dates and Status -->
        <div class="dates-and-status">
    <div class="date-box">
        <strong>{{ __('invoice.issue_date') }}</strong><br>
        {{ $invoice->issue_date->format('d/m/Y') }}
    </div>
    <div class="date-box">
        <strong>{{ __('invoice.due_date') }}</strong><br>
        {{ $invoice->due_date->format('d/m/Y') }}
    </div>
    @if($invoice->meta_data['payment_terms'] ?? false)
    <div class="date-box">
        <strong>{{ __('invoice.payment_terms') }}</strong><br>
        {{ $invoice->meta_data['payment_terms'] }}
    </div>
    @endif
</div>

        <!-- Items Table -->
        <table class="items-table">
            <thead>
                <tr>
                <th>{{ __('invoice.description') }}</th>
            <th>{{ __('invoice.quantity') }}</th>
            <th>{{ __('invoice.unit_price') }}</th>
            <th>{{ __('invoice.tax') }}</th>
            <th>{{ __('invoice.discount') }}</th>
            <th>{{ __('invoice.total') }}</th>
                </tr>
            </thead>
            <tbody>
                @foreach($invoice->items as $item)
                <tr>
                    <td>
                        {{ $item->description }}
                        @if($item->notes)
                            <br><small>{{ $item->notes }}</small>
                        @endif
                    </td>
                    <td>{{ $item->quantity }}</td>
                    <td>{{ number_format($item->unit_price, 2) }}</td>
                    <td>{{ number_format($item->tax_amount ?? 0, 2) }}</td>
                    <td>{{ number_format($item->discount_amount ?? 0, 2) }}</td>
                    <td>{{ number_format($item->total_price, 2) }}</td>
                </tr>
                @endforeach
            </tbody>
        </table>

        <!-- Totals -->
        <div class="totals">
    <table>
        <tr>
            <td>{{ __('invoice.subtotal') }}:</td>
            <td align="right">{{ number_format($invoice->meta_data['subtotal'] ?? 0, 2) }} DH</td>
        </tr>
        @if($invoice->tax_amount > 0)
        <tr>
            <td>{{ __('invoice.tax_amount') }}:</td>
            <td align="right">{{ number_format($invoice->tax_amount, 2) }} DH</td>
        </tr>
        @endif
        @if($invoice->discount_amount > 0)
        <tr>
            <td>{{ __('invoice.discount_amount') }}:</td>
            <td align="right">-{{ number_format($invoice->discount_amount, 2) }} DH</td>
        </tr>
        @endif
        <tr class="grand-total">
            <td>{{ __('invoice.total_amount') }}:</td>
            <td align="right">{{ number_format($invoice->total_amount, 2) }} DH</td>
        </tr>
    </table>
</div>


        <!-- Notes -->
        @if($invoice->notes)
<div class="notes">
    <h3>{{ __('invoice.notes') }}:</h3>
    <p>{{ $invoice->notes }}</p>
</div>
@endif
 
<div class="footer">
    <p>{{ __('invoice.thank_you') }}</p>
    <p>{{ __('invoice.generated_on') }} {{ now()->format('d/m/Y H:i:s') }}</p>
</div>
    </div>
</body>
</html>
