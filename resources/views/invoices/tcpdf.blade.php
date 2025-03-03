<style>
    /* Modern design with TCPDF compatibility */
    body {
        font-family: helvetica;
        color: #334155;
        font-size: 9pt;
        line-height: 1.5;
    }
    
    /* Color palette */
    .primary-color { color: #4f46e5; }
    .secondary-color { color: #475569; }
    .success-color { color: #22c55e; }
    .warning-color { color: #f59e0b; }
    .danger-color { color: #ef4444; }
    .light-bg { background-color: #f8fafc; }
    
    /* Layout elements */
    .container {
        width: 100%;
    }
    
    /* Custom border styles */
    .border-bottom {
        border-bottom: 1px solid #e2e8f0;
    }
    
    .border-top {
        border-top: 1px solid #e2e8f0;
    }
    
    /* Text styles */
    .text-right { text-align: right; }
    .text-center { text-align: center; }
    .text-lg { font-size: 12pt; }
    .text-xl { font-size: 14pt; }
    .text-2xl { font-size: 18pt; }
    .font-bold { font-weight: bold; }
    
    /* Spacing */
    .mt-1 { margin-top: 4pt; }
    .mt-2 { margin-top: 8pt; }
    .mt-4 { margin-top: 16pt; }
    .mb-1 { margin-bottom: 4pt; }
    .mb-2 { margin-bottom: 8pt; }
    .mb-4 { margin-bottom: 16pt; }
    .p-2 { padding: 8pt; }
    .p-3 { padding: 12pt; }
    .p-4 { padding: 16pt; }
    
    /* Tables */
    .table {
        width: 100%;
        border-collapse: collapse;
    }
    
    /* Header section */
    .invoice-header {
        margin-bottom: 20pt;
    }
    
    .logo {
        max-width: 150pt;
        max-height: 45pt;
    }
    
    .invoice-title {
        color: #4f46e5;
        font-size: 24pt;
        font-weight: bold;
    }
    
    /* Info boxes */
    .info-box {
        background-color: #f8fafc;
        border-radius: 4pt;
        padding: 12pt;
        margin-bottom: 15pt;
    }
    
    .info-box-title {
        color: #475569;
        font-weight: bold;
        margin-bottom: 4pt;
        text-transform: uppercase;
        font-size: 8pt;
        letter-spacing: 1pt;
    }
    
    /* Status badges */
    .badge {
        display: inline-block;
        padding: 4pt 8pt;
        border-radius: 12pt;
        font-size: 8pt;
        text-transform: uppercase;
        font-weight: bold;
        letter-spacing: 0.5pt;
    }
    
    .badge-draft { background-color: #e0f2fe; color: #0369a1; }
    .badge-sent { background-color: #fef9c3; color: #854d0e; }
    .badge-paid { background-color: #dcfce7; color: #166534; }
    .badge-cancelled { background-color: #fee2e2; color: #991b1b; }
    
    /* Items table */
    .items-table {
        width: 100%;
        border-collapse: collapse;
        margin: 20pt 0;
    }
    
    .items-table th {
        background-color: #4f46e5;
        color: white;
        font-weight: bold;
        text-align: left;
        padding: 8pt;
        font-size: 9pt;
    }
    
    .items-table td {
        padding: 8pt;
        border-bottom: 1px solid #e2e8f0;
        vertical-align: top;
    }
    
    .items-table tr:nth-child(even) td {
        background-color: #f8fafc;
    }
    
    /* Totals section */
    .totals-section {
        margin-top: 20pt;
    }
    
    .totals-table {
        width: 40%;
        margin-left: 60%;
    }
    
    .totals-table td {
        padding: 4pt;
    }
    
    .total-line {
        font-size: 11pt;
        font-weight: bold;
        color: #4f46e5;
        border-top: 2px solid #4f46e5;
    }
    
    /* Notes section */
    .notes-section {
        margin-top: 25pt;
        background-color: #f8fafc;
        border-radius: 4pt;
        padding: 12pt;
    }
    
    .notes-title {
        font-weight: bold;
        margin-bottom: 6pt;
        text-transform: uppercase;
        font-size: 8pt;
        letter-spacing: 1pt;
        color: #475569;
    }
    
    /* Footer */
    .footer {
        margin-top: 30pt;
        border-top: 1px solid #e2e8f0;
        padding-top: 10pt;
        text-align: center;
        color: #94a3b8;
        font-size: 8pt;
    }
</style>

<!-- INVOICE DOCUMENT START -->
<div class="container">
    <!-- TOP HEADER: LOGO AND INVOICE TITLE -->
    <table class="table invoice-header">
        <tr>
            <td width="50%" style="vertical-align: top;">
                @if(isset($invoice->team->logo_data_url))
                <img class="logo" src="{{ $invoice->team->logo_data_url }}" alt="{{ $invoice->team->name }}">
                @else
                <div class="text-xl primary-color font-bold">{{ $invoice->team->name }}</div>
                @endif
            </td>
            <td width="50%" style="text-align: right; vertical-align: top;">
                <div class="invoice-title">{{ __('invoice.' . ($invoice->invoiceable_type === 'App\Models\Sale' ? 'invoice' : 'bill')) }}</div>
                <div class="mt-1 text-lg">#{{ $invoice->reference_number }}</div>
                <div class="mt-2">
                    <span class="badge badge-{{ $invoice->status }}">
                        {{ __('invoice.status.' . $invoice->status) }}
                    </span>
                </div>
            </td>
        </tr>
    </table>
    
    <!-- COMPANY AND CLIENT INFO -->
    <table class="table">
        <tr>
            <td width="48%" style="vertical-align: top;">
                <div class="info-box">
                    <div class="info-box-title">{{ __('invoice.from') }}</div>
                    <div class="font-bold mb-1">{{ $invoice->team->name }}</div>
                    @if($invoice->team->address)
                    <div>{{ $invoice->team->address }}</div>
                    @endif
                    @if($invoice->team->phone)
                    <div>{{ $invoice->team->phone }}</div>
                    @endif
                    @if($invoice->team->email)
                    <div>{{ $invoice->team->email }}</div>
                    @endif
                    @if($invoice->team->tax_number)
                    <div class="mt-2">{{ __('invoice.tax_number') }}: <strong>{{ $invoice->team->tax_number }}</strong></div>
                    @endif
                </div>
            </td>
            <td width="4%"></td>
            <td width="48%" style="vertical-align: top;">
                @php
                    $hasClient = $invoice->invoiceable_type === 'App\Models\Sale' && isset($invoice->invoiceable->client);
                    $hasSupplier = $invoice->invoiceable_type === 'App\Models\Purchase' && isset($invoice->invoiceable->supplier);
                    
                    // Get entity data from meta_data if invoiceable is null
                    $entityData = null;
                    if ($invoice->invoiceable_type === 'App\Models\Sale') {
                        $entityData = $invoice->invoiceable?->client ?? (object) ($invoice->meta_data['client'] ?? [
                            'name' => $invoice->meta_data['client_name'] ?? '[Deleted Client]',
                            'contact_person' => $invoice->meta_data['client_contact_person'] ?? '',
                            'address' => $invoice->meta_data['client_address'] ?? '',
                            'email' => $invoice->meta_data['client_email'] ?? '',
                            'phone' => $invoice->meta_data['client_phone'] ?? '',
                            'tax_number' => $invoice->meta_data['client_tax_number'] ?? ''
                        ]);
                    } else {
                        $entityData = $invoice->invoiceable?->supplier ?? (object) ($invoice->meta_data['supplier'] ?? [
                            'name' => $invoice->meta_data['supplier_name'] ?? '[Deleted Supplier]',
                            'contact_person' => $invoice->meta_data['supplier_contact_person'] ?? '',
                            'address' => $invoice->meta_data['supplier_address'] ?? '',
                            'email' => $invoice->meta_data['supplier_email'] ?? '',
                            'phone' => $invoice->meta_data['supplier_phone'] ?? '',
                            'tax_number' => $invoice->meta_data['supplier_tax_number'] ?? ''
                        ]);
                    }
                @endphp
                
                @if($hasClient || $hasSupplier)
                <div class="info-box">
                    <div class="info-box-title">
                        {{ $invoice->invoiceable_type === 'App\Models\Sale' ? __('invoice.bill_to') : __('invoice.supplier') }}
                    </div>
                    <div class="font-bold mb-1">{{ $entityData->name }}</div>
                    @if($entityData->contact_person)
                    <div>{{ __('invoice.attn') }}: {{ $entityData->contact_person }}</div>
                    @endif
                    @if($entityData->address)
                    <div>{{ $entityData->address }}</div>
                    @endif
                    @if($entityData->phone)
                    <div>{{ __('invoice.phone') }}: {{ $entityData->phone }}</div>
                    @endif
                    @if($entityData->email)
                    <div>{{ __('invoice.email') }}: {{ $entityData->email }}</div>
                    @endif
                    @if($entityData->tax_number)
                    <div class="mt-2">{{ __('invoice.tax_number') }}: <strong>{{ $entityData->tax_number }}</strong></div>
                    @endif
                </div>
                @endif
            </td>
        </tr>
    </table>
    
    <!-- INVOICE DETAILS & DATES -->
    <table class="table mt-4">
        <tr>
            <td width="32%" style="vertical-align: top;">
                <div class="info-box">
                    <div class="info-box-title">{{ __('invoice.issue_date') }}</div>
                    <div class="text-center font-bold">
                        @if($invoice->issue_date)
                            {{ $invoice->issue_date instanceof \DateTime ? $invoice->issue_date->format('d/m/Y') : date('d/m/Y', strtotime($invoice->issue_date)) }}
                        @else
                            -
                        @endif
                    </div>
                </div>
            </td>
            <td width="2%"></td>
            <td width="32%" style="vertical-align: top;">
                <div class="info-box">
                    <div class="info-box-title">{{ __('invoice.due_date') }}</div>
                    <div class="text-center font-bold">
                        @if($invoice->due_date)
                            {{ $invoice->due_date instanceof \DateTime ? $invoice->due_date->format('d/m/Y') : date('d/m/Y', strtotime($invoice->due_date)) }}
                        @else
                            -
                        @endif
                    </div>
                </div>
            </td>
            <td width="2%"></td>
            <td width="32%" style="vertical-align: top;">
                @if($invoice->meta_data['payment_terms'] ?? false)
                <div class="info-box">
                    <div class="info-box-title">{{ __('invoice.payment_terms') }}</div>
                    <div class="text-center">{{ $invoice->meta_data['payment_terms'] }}</div>
                </div>
                @endif
            </td>
        </tr>
    </table>
    
    <!-- ITEMS TABLE -->
    <table class="items-table" cellspacing="0" cellpadding="0">
        <thead>
            <tr>
                <th width="40%">{{ __('invoice.description') }}</th>
                <th width="10%">{{ __('invoice.quantity') }}</th>
                <th width="15%">{{ __('invoice.unit_price') }}</th>
                <th width="10%">{{ __('invoice.tax') }}</th>
                <th width="10%">{{ __('invoice.discount') }}</th>
                <th width="15%">{{ __('invoice.total') }}</th>
            </tr>
        </thead>
        <tbody>
            @foreach($invoice->items as $index => $item)
            <tr>
                <td>
                    <div class="font-bold">{{ $item->description }}</div>
                    @if($item->notes)
                    <div style="font-size: 8pt; color: #64748b; margin-top: 3pt;">{{ $item->notes }}</div>
                    @endif
                </td>
                <td>{{ $item->quantity }}</td>
                <td>{{ number_format($item->unit_price, 2) }}</td>
                <td>{{ number_format($item->tax_amount ?? 0, 2) }}</td>
                <td>{{ number_format($item->discount_amount ?? 0, 2) }}</td>
                <td class="font-bold">{{ number_format($item->total_price, 2) }}</td>
            </tr>
            @endforeach
        </tbody>
    </table>
    
    <!-- TOTALS SECTION -->
    <div class="totals-section">
        <table class="totals-table">
            <tr>
                <td width="60%">{{ __('invoice.subtotal') }}:</td>
                <td width="40%" align="right">{{ number_format($invoice->meta_data['subtotal'] ?? 0, 2) }} DH</td>
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
            <tr class="total-line">
                <td>{{ __('invoice.total_amount') }}:</td>
                <td align="right">{{ number_format($invoice->total_amount, 2) }} DH</td>
            </tr>
        </table>
    </div>
    
    <!-- NOTES SECTION -->
    @if($invoice->notes)
    <div class="notes-section">
        <div class="notes-title">{{ __('invoice.notes') }}</div>
        <div>{{ $invoice->notes }}</div>
    </div>
    @endif
    
    <!-- THANK YOU MESSAGE -->
    <div class="mt-4 text-center">
        <div class="text-lg primary-color font-bold">{{ __('invoice.thank_you') }}</div>
    </div>
    
    <!-- FOOTER -->
    <div class="footer">
        <div>{{ $invoice->team->name }} &copy; {{ date('Y') }}</div>
        <div>{{ __('invoice.generated_on') }} {{ now()->format('d/m/Y H:i:s') }}</div>
    </div>
</div>
