<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>{{ __('invoice.invoice') }} {{ $invoice->reference_number }}</title>
    <style>
        /* Core styles only */
        body {
            font-family: Arial, sans-serif;
            line-height: 1.4;
            color: #334155;
            font-size: 9pt;
            margin: 0;
            padding: 0;
        }
        
        @page {
            margin: 15mm;
        }
        
        .invoice-container {
            width: 100%;
        }
        
        .primary-color { color: #2563eb; }
        .secondary-color { color: #475569; }
        .bg-light { background-color: #f8fafc; }
        
        h1 {
            font-size: 18pt;
            color: #2563eb;
            margin: 0 0 5pt 0;
        }
        
        h2 {
            font-size: 14pt;
            color: #2563eb;
            margin: 0 0 5pt 0;
        }
        
        h3 {
            font-size: 10pt;
            margin: 0 0 5pt 0;
            color: #475569;
        }
        
        p { margin: 3pt 0; }
        
        .text-right { text-align: right; }
        .text-center { text-align: center; }
        .text-bold { font-weight: bold; }
        .text-sm { font-size: 8pt; }
        
        .header {
            border-bottom: 1px solid #e2e8f0;
            padding-bottom: 10pt;
            margin-bottom: 15pt;
        }
        
        .header-table,
        .info-table,
        .items-table,
        .totals-table {
            width: 100%;
            border-collapse: collapse;
        }
        
        .logo {
            max-width: 140pt;
            max-height: 45pt;
        }
        
        .invoice-info {
            background-color: #f8fafc;
            border-radius: 4pt;
            padding: 8pt;
            border: 1px solid #e2e8f0;
            margin-bottom: 15pt;
        }
        
        .info-table td {
            padding: 2pt 4pt;
            vertical-align: top;
        }
        
        .date-row td {
            padding-top: 2pt;
            padding-bottom: 2pt;
            vertical-align: middle;
        }
        
        .date-label {
            white-space: nowrap;
            width: 15%;
        }
        
        .date-value {
            white-space: nowrap;
            width: 35%;
        }
        
        .status-badge {
            display: inline-block;
            padding: 3pt 6pt;
            border-radius: 8pt;
            font-size: 7pt;
            font-weight: bold;
            text-transform: uppercase;
        }
        
        .status-draft { background-color: #dbeafe; color: #1e40af; }
        .status-sent { background-color: #fef9c3; color: #854d0e; }
        .status-paid { background-color: #dcfce7; color: #166534; }
        .status-cancelled { background-color: #fee2e2; color: #991b1b; }
        
        .items-table th {
            background-color: #2563eb;
            color: white;
            padding: 6pt;
            text-align: left;
            font-size: 8pt;
            font-weight: bold;
        }
        
        .items-table td {
            padding: 6pt;
            border-bottom: 1px solid #e2e8f0;
            vertical-align: top;
        }
        
        .items-table tr:nth-child(even) {
            background-color: #f8fafc;
        }
        
        .totals-section {
            margin-top: 15pt;
        }
        
        .totals-table {
            width: 40%;
            margin-left: 60%;
        }
        
        .totals-table td {
            padding: 4pt 6pt;
            border-top: 1px solid #e2e8f0;
        }
        
        .totals-table tr:first-child td {
            border-top: none;
        }
        
        .grand-total {
            font-weight: bold;
            color: #2563eb;
            font-size: 12pt;
            border-top: 2px solid #2563eb !important;
        }
        
        .notes-section {
            margin-top: 20pt;
            background-color: #f8fafc;
            border: 1px solid #e2e8f0;
            border-radius: 4pt;
            padding: 8pt;
        }
        
        .footer {
            margin-top: 25pt;
            text-align: center;
            font-size: 8pt;
            color: #94a3b8;
            border-top: 1px solid #e2e8f0;
            padding-top: 10pt;
        }
        
        .client-info {
            line-height: 1.5;
        }
    </style>
</head>
<body>
    <div class="invoice-container">
        <!-- HEADER SECTION WITH LOGO AND COMPANY INFO -->
        <div class="header">
            <table class="header-table">
                <tr>
                    <td width="60%" style="vertical-align: top;">
                        @if(isset($invoice->team->logo_data_url))
                            <img class="logo" src="{{ $invoice->team->logo_data_url }}" 
                                @if(isset($invoice->team->logo_width) && isset($invoice->team->logo_height))
                                width="{{ $invoice->team->logo_width }}"
                                height="{{ $invoice->team->logo_height }}"
                                @endif
                                alt="Logo">
                        @endif
                        <h2>{{ $invoice->team->name }}</h2>
                        <p class="text-sm">
                            {{ $invoice->team->address ?? '' }}<br>
                            @if($invoice->team->phone)
                                {{ $invoice->team->phone }} |
                            @endif
                            {{ $invoice->team->email ?? '' }}
                            @if($invoice->team->tax_number)
                                <br>{{ __('invoice.tax_number') }}: <strong>{{ $invoice->team->tax_number }}</strong>
                            @endif
                        </p>
                    </td>
                    <td width="40%" style="vertical-align: top; text-align: right;">
                        <h1>{{ __('invoice.invoice') }}</h1>
                        <p class="text-bold" style="font-size: 14pt; color: #2563eb;">#{{ $invoice->reference_number }}</p>
                        <p>
                            <span class="status-badge status-{{ $invoice->status }}">
                                {{ $invoice->status }}
                            </span>
                        </p>
                    </td>
                </tr>
            </table>
        </div>
        
        <!-- COMPLETELY RESTRUCTURED INFO SECTION -->
        <div class="invoice-info">
            <table class="info-table">
                <!-- First row: Issue Date and Bill To -->
                <tr class="date-row">
                    <td class="date-label text-bold">{{ __('invoice.issue_date') }}:</td>
                    <td class="date-value">
                        @if($invoice->issue_date)
                            {{ $invoice->issue_date instanceof \DateTime ? $invoice->issue_date->format('d/m/Y') : date('d/m/Y', strtotime($invoice->issue_date)) }}
                        @else
                            -
                        @endif
                    </td>
                    <td width="15%" class="text-bold">{{ $invoice->invoiceable_type === 'App\Models\Sale' ? __('invoice.bill_to') : __('invoice.supplier') }}:</td>
                    <td width="35%" rowspan="2">
                        @php
                            $hasClient = $invoice->invoiceable_type === 'App\Models\Sale' && isset($invoice->invoiceable->client);
                            $hasSupplier = $invoice->invoiceable_type === 'App\Models\Purchase' && isset($invoice->invoiceable->supplier);
                            
                            // Get entity data from meta_data if invoiceable is null
                            $entityData = null;
                            if ($invoice->invoiceable_type === 'App\Models\Sale') {
                                $entityData = $invoice->invoiceable?->client ?? (object) ($invoice->meta_data['client'] ?? [
                                    'name' => $invoice->meta_data['client_name'] ?? '[Deleted Client]',
                                    'email' => $invoice->meta_data['client_email'] ?? '',
                                    'phone' => $invoice->meta_data['client_phone'] ?? ''
                                ]);
                            } else {
                                $entityData = $invoice->invoiceable?->supplier ?? (object) ($invoice->meta_data['supplier'] ?? [
                                    'name' => $invoice->meta_data['supplier_name'] ?? '[Deleted Supplier]',
                                    'email' => $invoice->meta_data['supplier_email'] ?? '',
                                    'phone' => $invoice->meta_data['supplier_phone'] ?? ''
                                ]);
                            }
                        @endphp
                        <div class="client-info">
                            <div><strong>{{ $entityData->name }}</strong></div>
                            @if(isset($entityData->email) && $entityData->email)
                                <div>{{ $entityData->email }}</div>
                            @endif
                            @if(isset($entityData->phone) && $entityData->phone)
                                <div>{{ $entityData->phone }}</div>
                            @endif
                        </div>
                    </td>
                </tr>
                <!-- Second row: Due Date -->
                <tr class="date-row">
                    <td class="date-label text-bold">{{ __('invoice.due_date') }}:</td>
                    <td class="date-value">
                        @if($invoice->due_date)
                            {{ $invoice->due_date instanceof \DateTime ? $invoice->due_date->format('d/m/Y') : date('d/m/Y', strtotime($invoice->due_date)) }}
                        @else
                            -
                        @endif
                    </td>
                    <td>&nbsp;</td><!-- Empty cell under "Bill To:" label -->
                </tr>
            </table>
        </div>
        
        <!-- ITEMS TABLE -->
        <table class="items-table">
            <thead>
                <tr>
                    <th width="45%">{{ __('invoice.description') }}</th>
                    <th width="10%">{{ __('invoice.quantity') }}</th>
                    <th width="15%">{{ __('invoice.unit_price') }}</th>
                    <th width="10%">{{ __('invoice.tax') }}</th>
                    <th width="10%">{{ __('invoice.discount') }}</th>
                    <th width="10%" class="text-right">{{ __('invoice.total') }}</th>
                </tr>
            </thead>
            <tbody>
                @foreach($invoice->items as $item)
                <tr>
                    <td>
                        <strong>{{ $item->description }}</strong>
                        @if($item->notes)
                            <br><span class="text-sm secondary-color">{{ $item->notes }}</span>
                        @endif
                    </td>
                    <td>{{ $item->quantity }}</td>
                    <td>{{ number_format($item->unit_price, 2) }}</td>
                    <td>{{ number_format($item->tax_amount ?? 0, 2) }}</td>
                    <td>{{ number_format($item->discount_amount ?? 0, 2) }}</td>
                    <td class="text-right text-bold">{{ number_format($item->total_price, 2) }}</td>
                </tr>
                @endforeach
            </tbody>
        </table>
        
        <!-- TOTALS -->
        <div class="totals-section">
            <table class="totals-table">
                <tr>
                    <td width="60%" class="secondary-color">{{ __('invoice.subtotal') }}:</td>
                    <td width="40%" class="text-right">{{ number_format($invoice->meta_data['subtotal'] ?? 0, 2) }} DH</td>
                </tr>
                @if($invoice->tax_amount > 0)
                <tr>
                    <td class="secondary-color">{{ __('invoice.tax') }}:</td>
                    <td class="text-right">{{ number_format($invoice->tax_amount, 2) }} DH</td>
                </tr>
                @endif
                @if($invoice->discount_amount > 0)
                <tr>
                    <td class="secondary-color">{{ __('invoice.discount') }}:</td>
                    <td class="text-right">-{{ number_format($invoice->discount_amount, 2) }} DH</td>
                </tr>
                @endif
                <tr>
                    <td class="grand-total">{{ __('invoice.total_amount') }}:</td>
                    <td class="grand-total text-right">{{ number_format($invoice->total_amount, 2) }} DH</td>
                </tr>
            </table>
        </div>
        
        <!-- NOTES SECTION -->
        @if($invoice->notes)
        <div class="notes-section">
            <div class="notes-title">{{ __('invoice.notes') }}</div>
            <p>{{ $invoice->notes }}</p>
        </div>
        @endif
        
        <!-- FOOTER -->
        <div class="footer">
            <p class="text-bold primary-color">{{ __('invoice.thank_you') }}</p>
            <p>{{ $invoice->team->name }} &copy; {{ date('Y') }}</p>
            <p>{{ __('invoice.generated_on') }}: {{ now()->format('d/m/Y H:i:s') }}</p>
        </div>
    </div>
</body>
</html>
