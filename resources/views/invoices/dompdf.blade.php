<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Invoice Template</title>
    <style>
        /* Core styles */
        body {
            font-family: Arial, sans-serif;
            line-height: 1.4;
            color: #334155;
            font-size: 9pt;
            margin: 0;
            padding: 0;
        }
        
        @page { margin: 15mm; }
        
        .invoice-container { width: 100%; }
        
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
        .items-table,
        .totals-table {
            width: 100%;
            border-collapse: collapse;
        }
        
        .logo { max-width: 50pt; max-height: 50pt; }
        
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
        
        .items-table tr:nth-child(even) { background-color: #f8fafc; }
        
        .totals-section { margin-top: 15pt; }
        
        .totals-table {
            width: 40%;
            margin-left: 60%;
        }
        
        .totals-table td {
            padding: 4pt 6pt;
            border-top: 1px solid #e2e8f0;
        }
        
        .totals-table tr:first-child td { border-top: none; }
        
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
        
        .client-info { line-height: 1.5; }
        
        .client-card {
            margin-bottom: 10pt;
            padding: 8pt;
            border: 1px solid #e2e8f0;
            background-color: #f8fafc;
            border-radius: 4pt;
        }
        
        .tax-info {
            margin-top: 5pt;
            font-size: 8pt;
            color: #475569;
        }
        
        .tax-label {
            font-weight: bold;
            margin-right: 4pt;
        }
        
        .invoice-title {
            text-align: center;
            margin: 0 0 10pt 0;
        }
        
        .date-table {
            width: 100%;
            margin-top: 10pt;
            border-top: 1px solid #e2e8f0;
            padding-top: 10pt;
        }
        
        .date-table td { padding: 3pt 0; }
        
        .date-label {
            text-align: right;
            padding-right: 10pt;
            font-weight: bold;
            width: 40%;
        }
        
        .date-value { width: 60%; }
    </style>
</head>
<body>
    <div class="invoice-container">
        <!-- HEADER WITH MINIMUM DATA -->
        <div class="header">
            <table class="header-table">
                <tr>
                    <td width="50%" style="vertical-align: top;">
                        <!-- Company side - MINIMAL -->
                        <h2>{{ $invoice->team->name }}</h2>
                    </td>
                    
                    <td width="50%" style="vertical-align: top;">
                        <!-- Invoice info side - MINIMAL -->
                        <h1 class="invoice-title">Invoice #{{ $invoice->reference_number }}</h1>
                        
                        <!-- Client card - ADDING CLIENT NAME -->
                        <div class="client-card">
                            @php
                                $clientName = '';
                                $clientEmail = '';
                                $clientPhone = '';
                                $clientAddress = '';
                                $taxNumber = '';
                                $ifNumber = '';
                                $rcNumber = '';
                                $cnssNumber = '';
                                $tpNumber = '';
                                $nisNumber = '';
                                $nifNumber = '';
                                $aiNumber = '';
                                
                                // Try to get from meta_data
                                if (isset($invoice->meta_data['contact']) && 
                                    isset($invoice->meta_data['contact']['data'])) {
                                    
                                    $data = $invoice->meta_data['contact']['data'];
                                    
                                    // Client name
                                    if (isset($data['name']) && is_string($data['name'])) {
                                        $clientName = $data['name'];
                                    }
                                    
                                    // Client email
                                    if (isset($data['email']) && is_string($data['email'])) {
                                        $clientEmail = $data['email'];
                                    }
                                    
                                    // Client phone
                                    if (isset($data['phone']) && is_string($data['phone'])) {
                                        $clientPhone = $data['phone'];
                                    }
                                    
                                    // Client address
                                    if (isset($data['address']) && is_string($data['address'])) {
                                        $clientAddress = $data['address'];
                                    }
                                    
                                    // Tax number
                                    if (isset($data['tax_number']) && is_string($data['tax_number'])) {
                                        $taxNumber = $data['tax_number'];
                                    }
                                    
                                    // IF number
                                    if (isset($data['if_number']) && is_string($data['if_number'])) {
                                        $ifNumber = $data['if_number'];
                                    }
                                    
                                    // RC number
                                    if (isset($data['rc_number']) && is_string($data['rc_number'])) {
                                        $rcNumber = $data['rc_number'];
                                    }
                                    
                                    // CNSS number
                                    if (isset($data['cnss_number']) && is_string($data['cnss_number'])) {
                                        $cnssNumber = $data['cnss_number'];
                                    }
                                    
                                    // TP number
                                    if (isset($data['tp_number']) && is_string($data['tp_number'])) {
                                        $tpNumber = $data['tp_number'];
                                    }
                                    
                                    // NIS number
                                    if (isset($data['nis_number']) && is_string($data['nis_number'])) {
                                        $nisNumber = $data['nis_number'];
                                    }
                                    
                                    // NIF number
                                    if (isset($data['nif_number']) && is_string($data['nif_number'])) {
                                        $nifNumber = $data['nif_number'];
                                    }
                                    
                                    // AI number
                                    if (isset($data['ai_number']) && is_string($data['ai_number'])) {
                                        $aiNumber = $data['ai_number'];
                                    }
                                }
                                // Try to get from relationship
                                else if (isset($invoice->invoiceable) && 
                                        isset($invoice->invoiceable->client)) {
                                    
                                    $client = $invoice->invoiceable->client;
                                    
                                    // Client name
                                    if (isset($client->name) && is_string($client->name)) {
                                        $clientName = $client->name;
                                    }
                                    
                                    // Client email
                                    if (isset($client->email) && is_string($client->email)) {
                                        $clientEmail = $client->email;
                                    }
                                    
                                    // Client phone
                                    if (isset($client->phone) && is_string($client->phone)) {
                                        $clientPhone = $client->phone;
                                    }
                                    
                                    // Client address
                                    if (isset($client->address) && is_string($client->address)) {
                                        $clientAddress = $client->address;
                                    }
                                    
                                    // Tax number
                                    if (isset($client->tax_number) && is_string($client->tax_number)) {
                                        $taxNumber = $client->tax_number;
                                    }
                                    
                                    // IF number
                                    if (isset($client->if_number) && is_string($client->if_number)) {
                                        $ifNumber = $client->if_number;
                                    }
                                    
                                    // RC number
                                    if (isset($client->rc_number) && is_string($client->rc_number)) {
                                        $rcNumber = $client->rc_number;
                                    }
                                    
                                    // CNSS number
                                    if (isset($client->cnss_number) && is_string($client->cnss_number)) {
                                        $cnssNumber = $client->cnss_number;
                                    }
                                    
                                    // TP number
                                    if (isset($client->tp_number) && is_string($client->tp_number)) {
                                        $tpNumber = $client->tp_number;
                                    }
                                    
                                    // NIS number
                                    if (isset($client->nis_number) && is_string($client->nis_number)) {
                                        $nisNumber = $client->nis_number;
                                    }
                                    
                                    // NIF number
                                    if (isset($client->nif_number) && is_string($client->nif_number)) {
                                        $nifNumber = $client->nif_number;
                                    }
                                    
                                    // AI number
                                    if (isset($client->ai_number) && is_string($client->ai_number)) {
                                        $aiNumber = $client->ai_number;
                                    }
                                }
                            @endphp
                            
                            @if($clientName)
                                <p><strong>{{ $clientName }}</strong></p>
                                
                                @if($clientAddress)
                                    <p>{{ $clientAddress }}</p>
                                @endif
                                
                                @if($clientEmail)
                                    <p>{{ $clientEmail }}</p>
                                @endif
                                
                                @if($clientPhone)
                                    <p>{{ $clientPhone }}</p>
                                @endif
                                
                                <!-- Tax fields -->
                                @if($taxNumber)
                                <div class="tax-info">
                                    <span class="tax-label">Tax Number:</span>{{ $taxNumber }}
                                </div>
                                @endif
                                
                                @if($ifNumber)
                                <div class="tax-info">
                                    <span class="tax-label">IF Number:</span>{{ $ifNumber }}
                                </div>
                                @endif
                                
                                @if($rcNumber)
                                <div class="tax-info">
                                    <span class="tax-label">RC Number:</span>{{ $rcNumber }}
                                </div>
                                @endif
                                
                                @if($cnssNumber)
                                <div class="tax-info">
                                    <span class="tax-label">CNSS Number:</span>{{ $cnssNumber }}
                                </div>
                                @endif
                                
                                @if($tpNumber)
                                <div class="tax-info">
                                    <span class="tax-label">TP Number:</span>{{ $tpNumber }}
                                </div>
                                @endif
                                
                                @if($nisNumber)
                                <div class="tax-info">
                                    <span class="tax-label">NIS Number:</span>{{ $nisNumber }}
                                </div>
                                @endif
                                
                                @if($nifNumber)
                                <div class="tax-info">
                                    <span class="tax-label">NIF Number:</span>{{ $nifNumber }}
                                </div>
                                @endif
                                
                                @if($aiNumber)
                                <div class="tax-info">
                                    <span class="tax-label">AI Number:</span>{{ $aiNumber }}
                                </div>
                                @endif
                            @endif
                            
                            <!-- Only dates -->
                            <table class="date-table">
                                <tr>
                                    <td class="date-label">Issue Date:</td>
                                    <td class="date-value">
                                        {{ date('d/m/Y', strtotime($invoice->issue_date)) }}
                                    </td>
                                </tr>
                                <tr>
                                    <td class="date-label">Status:</td>
                                    <td class="date-value">
                                        <span class="status-badge status-{{ $invoice->status }}">
                                            {{ $invoice->status }}
                                        </span>
                                    </td>
                                </tr>
                            </table>
                        </div>
                    </td>
                </tr>
            </table>
        </div>
        
        <!-- ITEMS TABLE - MINIMAL -->
        <table class="items-table">
            <thead>
                <tr>
                    <th width="70%">Description</th>
                    <th width="30%" class="text-right">Total</th>
                </tr>
            </thead>
            <tbody>
                @foreach($invoice->items as $item)
                <tr>
                    <td>{{ $item->description }}</td>
                    <td class="text-right">{{ number_format($item->total_price, 2) }} DH</td>
                </tr>
                @endforeach
            </tbody>
        </table>
        
        <!-- TOTALS - MINIMAL -->
        <div class="totals-section">
            <table class="totals-table">
                <tr>
                    <td class="grand-total">Total:</td>
                    <td class="grand-total text-right">{{ number_format($invoice->total_amount, 2) }} DH</td>
                </tr>
            </table>
        </div>
        
        <!-- SIMPLE FOOTER -->
        <div class="footer">
            <p>{{ $invoice->team->name }}</p>
        </div>
    </div>
</body>
</html>
