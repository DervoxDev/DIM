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
        
        /* Use the configured primary color instead of hardcoded one */
        :root {
            --primary-color: {{ $invoice->meta_data['config']['primaryColor'] ?? '#2563eb' }};
        }
        
        .invoice-container {
            width: 100%;
        }
        
        .primary-color { color: var(--primary-color); }
        .secondary-color { color: #475569; }
        .bg-light { background-color: #f8fafc; }
        
        h1 {
            font-size: 18pt;
            color: var(--primary-color);
            margin: 0 0 5pt 0;
        }
        
        h2 {
            font-size: 14pt;
            color: var(--primary-color);
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
            position: relative;
        }
        
        .header-table,
        .info-table,
        .items-table,
        .totals-table {
            width: 100%;
            border-collapse: collapse;
        }
        
        .logo {
            max-width: 50pt;
            max-height: 50pt;
        }
        
        .invoice-title {
            text-align: center;
            width: 100%;
            margin-bottom: 10pt;
        }
        
        .invoice-info {
            background-color: #f8fafc;
            border-radius: 4pt;
            padding: 8pt;
            border: 1px solid #e2e8f0;
            margin-bottom: 15pt;
        }
        
        .client-card {
            background-color: #f8fafc;
            border-radius: 4pt;
            padding: 8pt;
            border: 1px solid #e2e8f0;
            min-height: 100pt;
            width: 100%;
            margin-top: 5pt;
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
         
        .items-table th {
            background-color: var(--primary-color);
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
            width: 50%;
            margin-left: 50%;
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
            color: var(--primary-color);
            font-size: 12pt;
            border-top: 2px solid var(--primary-color) !important;
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
        
        .payment-methods-section {
            margin-top: 15pt;
            margin-bottom: 15pt;
            width: 100%;
        }
        
        .payment-methods-table {
            width: 40%;
            margin-left: 60%;
            border-collapse: collapse;
        }
        
        .payment-methods-table th {
            background-color: #e2e8f0;
            color: #475569;
            padding: 5pt;
            text-align: left;
            font-size: 8pt;
            font-weight: bold;
            border-bottom: 1px solid #cbd5e1;
        }
        
        .payment-methods-table td {
            padding: 4pt 6pt;
            border-bottom: 1px solid #e2e8f0;
        }
        
        .payment-methods-title {
            font-size: 9pt;
            font-weight: bold;
            color: #475569;
            margin-bottom: 5pt;
            text-align: right;
        }
        
        .payment-method-name {
            text-transform: capitalize;
        }
        
        .tax-info-table {
            width: 100%;
            margin-top: 5pt;
        }
        
        .tax-info-table td {
            padding: 2pt 0;
        }
        
        .tax-info-label {
            font-weight: bold;
            color: #475569;
        }
        
        .tax-info-value {
            color: #334155;
        }
        
        .client-card-title {
            font-size: 10pt;
            font-weight: bold;
            color: var(--primary-color);
            margin-bottom: 5pt;
            border-bottom: 1px solid #e2e8f0;
            padding-bottom: 3pt;
        }

        .reference-number {
            text-align: right;
            font-size: 14pt;
            color: var(--primary-color);
            font-weight: bold;
            margin-bottom: 5pt;
        }
    </style>
</head>
<body>
    <div class="invoice-container">
        <!-- CENTRALIZED INVOICE TITLE -->
        <div class="invoice-title">
            @if(isset($invoice->meta_data['document_type']) && $invoice->meta_data['document_type'] === 'quote')
                <h1>{{ __('invoice.quote') }}</h1>
                <p>{{ __('invoice.valid_until') }}: {{ Carbon\Carbon::parse($invoice->issue_date)->addDays(30)->format('d/m/Y') }}</p>
            @else
                <h1>{{ __('invoice.invoice') }}</h1>
            @endif
        </div>
        
        <!-- HEADER SECTION WITH LOGO, COMPANY INFO AND CLIENT CARD -->
        <div class="header">
            <!-- Company logo and info -->
            <table class="header-table">
                <tr>
                    <td width="50%" style="vertical-align: top;">
                        @if(($invoice->meta_data['config']['logoEnabled'] ?? true) && isset($invoice->team->logo_data_url))
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
                    
                    <!-- Reference number and Client info card -->
                    <td width="50%" style="vertical-align: top;">
                        <div class="reference-number">#{{ $invoice->reference_number }}</div>
                        
                        @php
                            $hasClient = false;
                            $hasSupplier = false;
                            $entityData = null;
                            
                            if ($invoice->invoiceable_type === 'App\Models\Sale') {
                                // First check if we have meta_data.contact
                                if (isset($invoice->meta_data['contact']) && isset($invoice->meta_data['contact']['data']) && !empty($invoice->meta_data['contact']['data']['name'])) {
                                    $entityData = (object) $invoice->meta_data['contact']['data'];
                                    $hasClient = true;
                                } 
                                // Then check for the sale.client relationship
                                elseif (isset($invoice->invoiceable->client)) {
                                    $entityData = $invoice->invoiceable->client;
                                    $hasClient = true;
                                }
                            } else {
                                // Similar logic for suppliers
                                if (isset($invoice->meta_data['contact']) && isset($invoice->meta_data['contact']['data']) && !empty($invoice->meta_data['contact']['data']['name'])) {
                                    $entityData = (object) $invoice->meta_data['contact']['data'];
                                    $hasSupplier = true;
                                } 
                                elseif (isset($invoice->invoiceable->supplier)) {
                                    $entityData = $invoice->invoiceable->supplier;
                                    $hasSupplier = true;
                                }
                            }
                        @endphp
                        
                        @if(($invoice->meta_data['config']['showClientInfo'] ?? true) && ($hasClient || $hasSupplier))
                        <div class="client-card">
                            <div class="client-card-title">{{ $invoice->invoiceable_type === 'App\Models\Sale' ? __('invoice.bill_to') : __('invoice.supplier') }}</div>
                            <table class="tax-info-table">
                                <!-- Name, Email, Phone with labels -->
                                <tr>
                                    <td class="tax-info-label">{{ __('invoice.name') }}:</td>
                                    <td class="tax-info-value"><strong>{{ $entityData->name }}</strong></td>
                                </tr>
                                
                                @if(isset($entityData->email) && $entityData->email)
                                <tr>
                                    <td class="tax-info-label">{{ __('invoice.email') }}:</td>
                                    <td class="tax-info-value">{{ $entityData->email }}</td>
                                </tr>
                                @endif
                                
                                @if(isset($entityData->phone) && $entityData->phone)
                                <tr>
                                    <td class="tax-info-label">{{ __('invoice.phone') }}:</td>
                                    <td class="tax-info-value">{{ $entityData->phone }}</td>
                                </tr>
                                @endif
                                
                                @if(isset($entityData->address) && $entityData->address)
                                <tr>
                                    <td class="tax-info-label">{{ __('invoice.address') }}:</td>
                                    <td class="tax-info-value">{{ $entityData->address }}</td>
                                </tr>
                                @endif
                                
                                <!-- Tax Information in Inline Layout -->
                                @if(($invoice->meta_data['config']['showTaxNumbers'] ?? true) && $hasClient && $entityData)
                                    @if(isset($entityData->tax_number) && !empty($entityData->tax_number))
                                    <tr>
                                        <td class="tax-info-label">{{ __('invoice.tax_number') }}:</td>
                                        <td class="tax-info-value">{{ $entityData->tax_number }}</td>
                                    </tr>
                                    @endif
                                    
                                    @if(isset($entityData->if_number) && !empty($entityData->if_number))
                                    <tr>
                                        <td class="tax-info-label">{{ __('invoice.if_number') }}:</td>
                                        <td class="tax-info-value">{{ $entityData->if_number }}</td>
                                    </tr>
                                    @endif
                                    
                                    @if(isset($entityData->rc_number) && !empty($entityData->rc_number))
                                    <tr>
                                        <td class="tax-info-label">{{ __('invoice.rc_number') }}:</td>
                                        <td class="tax-info-value">{{ $entityData->rc_number }}</td>
                                    </tr>
                                    @endif
                                    
                                    @if(isset($entityData->cnss_number) && !empty($entityData->cnss_number))
                                    <tr>
                                        <td class="tax-info-label">{{ __('invoice.cnss_number') }}:</td>
                                        <td class="tax-info-value">{{ $entityData->cnss_number }}</td>
                                    </tr>
                                    @endif
                                    
                                    @if(isset($entityData->tp_number) && !empty($entityData->tp_number))
                                    <tr>
                                        <td class="tax-info-label">{{ __('invoice.tp_number') }}:</td>
                                        <td class="tax-info-value">{{ $entityData->tp_number }}</td>
                                    </tr>
                                    @endif
                                    
                                    @if(isset($entityData->nis_number) && !empty($entityData->nis_number))
                                    <tr>
                                        <td class="tax-info-label">{{ __('invoice.nis_number') }}:</td>
                                        <td class="tax-info-value">{{ $entityData->nis_number }}</td>
                                    </tr>
                                    @endif
                                    
                                    @if(isset($entityData->nif_number) && !empty($entityData->nif_number))
                                    <tr>
                                        <td class="tax-info-label">{{ __('invoice.nif_number') }}:</td>
                                        <td class="tax-info-value">{{ $entityData->nif_number }}</td>
                                    </tr>
                                    @endif
                                    
                                    @if(isset($entityData->ai_number) && !empty($entityData->ai_number))
                                    <tr>
                                        <td class="tax-info-label">{{ __('invoice.ai_number') }}:</td>
                                        <td class="tax-info-value">{{ $entityData->ai_number }}</td>
                                    </tr>
                                    @endif
                                @endif
                            </table>
                        </div>
                        @endif
                    </td>
                </tr>
            </table>
        </div>
        
        <!-- INVOICE INFO SECTION -->
        <div class="invoice-info">
            <table class="info-table">
                <tr class="date-row">
                    <td class="date-label text-bold">{{ __('invoice.issue_date') }}:</td>
                    <td class="date-value">
                        @if($invoice->issue_date)
                            {{ $invoice->issue_date instanceof \DateTime ? $invoice->issue_date->format('d/m/Y') : date('d/m/Y', strtotime($invoice->issue_date)) }}
                        @else
                            -
                        @endif
                    </td>
                    <td class="date-label text-bold">{{ __('invoice.due_date') }}:</td>
                    <td class="date-value">
                        @if($invoice->due_date)
                            {{ $invoice->due_date instanceof \DateTime ? $invoice->due_date->format('d/m/Y') : date('d/m/Y', strtotime($invoice->due_date)) }}
                        @else
                            -
                        @endif
                    </td>
                </tr>
            </table>
        </div>
        
        <!-- ITEMS TABLE -->
        <table class="items-table">
            <thead>
                <tr>
                    <th width="45%">{{ __('invoice.description') }}</th>
                    <th width="15%">{{ __('invoice.quantity') }}</th>
                    <th width="15%">{{ __('invoice.unit_price') }}</th>
                    <th width="10%">{{ __('invoice.tax') }}</th>
                    <th width="15%">{{ __('invoice.discount') }}</th>
                    <th width="15%" class="text-right">{{ __('invoice.total') }}</th>
                </tr>
            </thead>
            <tbody>
                @php
                    // Calculate total item discounts
                    $totalItemDiscounts = 0;
                @endphp
                @foreach($invoice->items as $index => $item)
                    @php
                        // Get the corresponding item data from metadata
                        $itemData = $invoice->meta_data['items_data'][$index] ?? null;
                        $taxRate = $itemData['tax_rate'] ?? 0;
                        $unitPrice = $itemData['unit_price'] ?? $item->unit_price;
                        $discountAmount = $itemData['discount_amount'] ?? 0;
                        $totalPrice = $itemData['total_price'] ?? $item->total_price;
                        $quantity = $itemData['quantity'] ?? $item->quantity;
                        
                        // Add to total item discounts
                        $totalItemDiscounts += $discountAmount;
                    @endphp
                    <tr>
                        <td>
                            <strong>{{ $item->description }}</strong>
                            @if($item->notes)
                                <br><span class="text-sm secondary-color">{{ $item->notes }}</span>
                            @endif
                        </td>
                        <td>{{ $quantity }}</td>
                        <td>{{ number_format($unitPrice, 2) }} DH</td>
                        <td>{{ number_format($taxRate, 2) }}%</td>
                        <td>{{ number_format($discountAmount, 2) }} DH</td>
                        <td class="text-right text-bold">{{ number_format($totalPrice, 2) }} DH</td>
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
                @php
                    // Calculate total discount (invoice discount + all item discounts)
                    $totalDiscount = $invoice->discount_amount + $totalItemDiscounts;
                @endphp
                @if($totalDiscount > 0)
                    <tr>
                        <td class="secondary-color">{{ __('invoice.discount') }}:</td>
                        <td class="text-right">-{{ number_format($totalDiscount, 2) }} DH</td>
                    </tr>
                @endif
                <tr>
                    <td class="grand-total">{{ __('invoice.total_amount') }}:</td>
                    <td class="grand-total text-right">{{ number_format($invoice->total_amount, 2) }} DH</td>
                </tr>
            </table>
        </div>
        
        <!-- PAYMENT METHODS (conditionally shown) -->
        @if(($invoice->meta_data['config']['showPaymentMethods'] ?? true) && 
            isset($invoice->meta_data['payment_methods']) && 
            count($invoice->meta_data['payment_methods']) > 0)
        <div style="margin-top: 20pt; width: 40%; float: left;">
            <p class="text-bold">{{ __('invoice.payment_method') }} :</p>
            @foreach($invoice->meta_data['payment_methods'] as $payment)
                @php
                    $methodKey = $payment['method'] ?? 'cash';
                    
                    $methodTranslations = [
                        'en' => [
                            'cash' => 'Cash',
                            'credit_card' => 'Credit Card',
                            'bank_transfer' => 'Bank Transfer',
                            'check' => 'Check',
                            'online_payment' => 'Online Payment',
                            'debit_card' => 'Debit Card',
                            'other' => 'Other'
                        ],
                        'fr' => [
                            'cash' => 'Espèces',
                            'credit_card' => 'Carte de Crédit',
                            'bank_transfer' => 'Virement Bancaire',
                            'check' => 'Chèque',
                            'online_payment' => 'Paiement en Ligne',
                            'debit_card' => 'Carte de Débit',
                            'other' => 'Autre'
                        ]
                    ];
                    
                    $lang = app()->getLocale();
                    if (!isset($methodTranslations[$lang])) {
                        $lang = 'fr';
                    }
                    
                    $methodName = $methodTranslations[$lang][$methodKey] ?? ucfirst(str_replace('_', ' ', $methodKey));
                @endphp
                <p>- {{ $methodName }}</p>
            @endforeach
        </div>
        <div style="clear: both;"></div>
        @endif
        
        @if(isset($invoice->meta_data['config']['showAmountInWords']) && $invoice->meta_data['config']['showAmountInWords'])
<div style="margin-top: 20pt; margin-bottom: 10pt; width: 100%;">
    <p class="text-bold">{{ __('invoice.amount_in_words') }} :</p>
    <p>
        <?php 
            $amount = $invoice->total_amount;
            $lang = app()->getLocale();
            
            try {
                // Convert float amount to integer cents/centimes (multiply by 100)
                $amountInCents = (int)round($amount * 100);
                
                // Choose the appropriate language code
                $langCode = $lang === 'fr' ? 'fr' : 'en';
                
                // Initialize NumberToWords
                $numberToWords = new \NumberToWords\NumberToWords();
                
                // Get the appropriate currency transformer
                $currencyTransformer = $numberToWords->getCurrencyTransformer($langCode);
                
                // Transform the amount to words
                $amountInWords = $currencyTransformer->toWords($amountInCents, 'MAD');
                
                // Output with first letter uppercase
                echo ucfirst($amountInWords);
            } catch (\Exception $e) {
                // Fallback to numeric display in case of errors
                echo number_format($amount, 2) . ' dirhams';
            }
        ?>
    </p>
</div>
@endif
 
   <!-- NOTES SECTION (conditionally shown) -->
@if(($invoice->meta_data['config']['showNotes'] ?? true) && 
    isset($invoice->meta_data['document_type']) && $invoice->meta_data['document_type'] === 'invoice')
<div class="notes-section">
    <div class="notes-title">{{ __('invoice.notes') }}</div>
    <p>{{ $invoice->notes ?: ($invoice->meta_data['config']['defaultNotes'] ?? 'Thank you for your business.') }}</p>
</div>
@endif

        <!-- TERMS & CONDITIONS (conditionally shown for quotes) -->
        @if(($invoice->meta_data['config']['showTermsConditions'] ?? true) && 
            isset($invoice->meta_data['document_type']) && $invoice->meta_data['document_type'] === 'quote')
        <div class="notes-section">
            <div class="notes-title">{{ __('invoice.terms_and_conditions') }}</div>
            @if(!empty($invoice->meta_data['config']['defaultTerms']))
                <p>{{ $invoice->meta_data['config']['defaultTerms'] }}</p>
            @else
                <ol>
                    <li>{{ __('invoice.quote_valid_for') }}</li>
                    <li>{{ __('invoice.prices_subject_to_change') }}</li>
                    <li>{{ __('invoice.product_availability') }}</li>
                    <li>{{ __('invoice.payment_terms_quote') }}</li>
                </ol>
            @endif
        </div>
        @endif
        
        <!-- FOOTER -->
        <div class="footer">
    @if(($invoice->meta_data['config']['showThanksMessage'] ?? true))
    <p class="text-bold primary-color">
        @if(isset($invoice->meta_data['document_type']) && $invoice->meta_data['document_type'] === 'quote')
            {{ $invoice->meta_data['config']['thanksMessage'] ?? __('invoice.thank_you_for_considering_our_offer') }}
        @else
            {{ $invoice->meta_data['config']['thanksMessage'] ?? __('invoice.thank_you') }}
        @endif
    </p>
    @endif

            
            @php
                $footerText = $invoice->meta_data['config']['footerText'] ?? '';
                
                if (!empty($footerText)) {
                    // Replace variables in footer text
                    $footerText = str_replace('%teamName%', $invoice->team->name, $footerText);
                    $footerText = str_replace('%teamEmail%', $invoice->team->email ?? '', $footerText);
                    $footerText = str_replace('%teamPhone%', $invoice->team->phone ?? '', $footerText);
                    $footerText = str_replace('%teamAddress%', $invoice->team->address ?? '', $footerText);
                    
                    echo "<p>{$footerText}</p>";
                } else {
                    echo "<p>{$invoice->team->name} &copy; " . date('Y') . "</p>";
                }
            @endphp
            
            <p>
                @if(isset($invoice->meta_data['document_type']) && $invoice->meta_data['document_type'] === 'quote')
                    {{ __('invoice.quote_generated_on') }}
                @else
                    {{ __('invoice.generated_on') }}
                @endif
                : {{ now()->format('d/m/Y H:i:s') }}
            </p>
        </div>
    </div>
</body>
</html>
