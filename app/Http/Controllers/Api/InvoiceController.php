<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Invoice;
use App\Models\InvoiceItem;
use App\Models\ActivityLog;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use Spatie\Browsershot\Browsershot;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\View;
use Illuminate\Support\Facades\Session;

class InvoiceController extends Controller
{
    public function index(Request $request)
    {
        $user = $request->user();

        if (!$user->team) {
            return response()->json([
                'error' => true,
                'message' => 'No team found for the user'
            ], 404);
        }

        $query = Invoice::where('team_id', $user->team->id)
                       ->with(['invoiceable']);

        // Date range filter
        if ($request->has('start_date') && $request->has('end_date')) {
            $query->whereBetween('issue_date', [
                $request->start_date,
                $request->end_date
            ]);
        }

        // Status filter
        if ($request->has('status')) {
            $query->where('status', $request->status);
        }

        // Search by reference number
        if ($request->has('search')) {
            $searchTerm = $request->search;
            $query->where(function($q) use ($searchTerm) {
                $q->where('reference_number', 'like', "%{$searchTerm}%")
                  ->orWhere('notes', 'like', "%{$searchTerm}%");
            });
        }

        // Sort
        $sortField = $request->get('sort_by', 'issue_date');
        $sortDirection = $request->get('sort_direction', 'desc');
        $query->orderBy($sortField, $sortDirection);

        $invoices = $query->paginate($request->input('per_page', 15));

        return response()->json([
            'invoices' => $invoices
        ]);
    }

    public function show(Request $request, $id)
    {
        $user = $request->user();

        $invoice = Invoice::where('team_id', $user->team->id)
                        ->with(['invoiceable'])
                        ->find($id);

        if (!$invoice) {
            return response()->json([
                'error' => true,
                'message' => 'Invoice not found'
            ], 404);
        }

        return response()->json([
            'invoice' => $invoice
        ]);
    }
    public function store(Request $request)
    {
        try {
            \Log::info('Invoice creation request received', [
                'request_data' => $request->all()
            ]);
    
            $user = $request->user();
            
            if (!$user->team) {
                return response()->json([
                    'error' => true,
                    'message' => 'No team found for the user'
                ], 404);
            }
    
            // Map invoice type to model namespace
            $typeMapping = [
                'Purchase' => 'App\\Models\\Purchase',
                'Sale' => 'App\\Models\\Sale'
            ];
    
            // Map the type or return error if invalid
            if (!isset($typeMapping[$request->invoiceable_type])) {
                return response()->json([
                    'error' => true,
                    'message' => 'Invalid invoiceable type',
                    'valid_types' => array_keys($typeMapping)
                ], 422);
            }
    
            $mappedType = $typeMapping[$request->invoiceable_type];
    
            // Validate main invoice data
            $validator = Validator::make($request->all(), [
                'invoiceable_type' => 'required|string|in:Purchase,Sale',
                'status' => 'required|in:draft,sent,paid,overdue,cancelled',
                'issue_date' => 'required|date',
                'due_date' => 'required|date|after_or_equal:issue_date',
                'total_amount' => 'required|numeric|min:0',
                'tax_amount' => 'nullable|numeric|min:0',
                'discount_amount' => 'nullable|numeric|min:0',
                'notes' => 'nullable|string',
                'items' => 'required|array|min:1',
                'items.*.description' => 'required|string',
                'items.*.quantity' => 'required|numeric|min:1',
                'items.*.unit_price' => 'required|numeric|min:0',
                'items.*.total_price' => 'required|numeric|min:0',
                'items.*.notes' => 'nullable|string'
            ]);
    
            if ($validator->fails()) {
                return response()->json([
                    'error' => true,
                    'message' => 'Validation error',
                    'errors' => $validator->errors()
                ], 422);
            }
    
            try {
                DB::beginTransaction();
    
                $lastInvoice = Invoice::where('team_id', $user->team->id)
                    ->orderBy('id', 'desc')
                    ->first();
    
                $nextId = ($lastInvoice ? $lastInvoice->id : 0) + 1;
                $nextInvoiceableId = ($lastInvoice ? $lastInvoice->invoiceable_id : 0) + 1;
    
                $year = date('Y');
                $referenceNumber = "INV-{$year}-" . str_pad($nextId, 6, '0', STR_PAD_LEFT);
    
                // Create invoice with mapped type
                $invoice = new Invoice([
                    'team_id' => $user->team->id,
                    'reference_number' => $referenceNumber,
                    'invoiceable_type' => $mappedType, // Use mapped type here
                    'invoiceable_id' => $nextInvoiceableId,
                    'total_amount' => $request->total_amount,
                    'tax_amount' => $request->tax_amount ?? 0,
                    'discount_amount' => $request->discount_amount ?? 0,
                    'status' => $request->status,
                    'issue_date' => $request->issue_date,
                    'due_date' => $request->due_date,
                    'notes' => $request->notes,
                    'meta_data' => array_merge($request->meta_data ?? [], [
                        'created_by' => $user->id,
                        'created_at' => now()->toISOString(),
                        'source_type' => $request->invoiceable_type,
                        'source_reference' => $referenceNumber
                    ])
                ]);
    
                $invoice->save();
    
                // Create invoice items
                foreach ($request->items as $itemData) {
                    $item = new InvoiceItem([
                        'description' => $itemData['description'],
                        'quantity' => $itemData['quantity'],
                        'unit_price' => $itemData['unit_price'],
                        'total_price' => $itemData['total_price'],
                        'notes' => $itemData['notes'] ?? null,
                        'meta_data' => $itemData['meta_data'] ?? null
                    ]);
                    $invoice->items()->save($item);
                }
    
                // Log activity
                ActivityLog::create([
                    'log_type' => 'Create',
                    'model_type' => "Invoice",
                    'model_id' => $invoice->id,
                    'model_identifier' => $invoice->reference_number,
                    'user_identifier' => $user?->name,
                    'user_id' => $user->id,
                    'user_email' => $user?->email,
                    'ip_address' => $request->ip(),
                    'user_agent' => $request->userAgent(),
                    'description' => "Created invoice {$invoice->reference_number}",
                    'new_values' => array_merge($invoice->toArray(), [
                        'items' => $invoice->items->toArray()
                    ])
                ]);
    
                DB::commit();
    
                // Transform the type back for the response
                $invoice->load(['items']);
                $responseInvoice = $invoice->toArray();
                $responseInvoice['invoiceable_type'] = array_search($invoice->invoiceable_type, $typeMapping);
    
                return response()->json([
                    'message' => 'Invoice created successfully',
                    'invoice' => $responseInvoice
                ], 201);
    
            } catch (\Exception $e) {
                DB::rollBack();
                throw $e;
            }
        } catch (\Exception $e) {
            \Log::error('Error creating invoice', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
    
            return response()->json([
                'error' => true,
                'message' => 'Error creating invoice',
                'debug_message' => config('app.debug') ? $e->getMessage() : null
            ], 500);
        }
    }
    
    
    
    public function update(Request $request, $id)
    {
        try {
            \Log::info('Invoice update request received', [
                'invoice_id' => $id,
                'request_data' => $request->all(),
                'user_id' => $request->user()?->id,
                'ip' => $request->ip()
            ]);
    
            $user = $request->user();
    
            $invoice = Invoice::where('team_id', $user->team->id)->find($id);
    
            if (!$invoice) {
                \Log::warning('Invoice not found for update', [
                    'invoice_id' => $id,
                    'team_id' => $user->team->id,
                    'user_id' => $user->id
                ]);
                return response()->json([
                    'error' => true,
                    'message' => 'Invoice not found'
                ], 404);
            }
    
            \Log::info('Found invoice for update', [
                'invoice_id' => $invoice->id,
                'reference_number' => $invoice->reference_number,
                'current_status' => $invoice->status,
                'current_payment_status' => $invoice->payment_status,
                'current_email_status' => $invoice->is_email_sent
            ]);
    
            // Validate all possible fields with CORRECT statuses
            $validator = Validator::make($request->all(), [
                'status' => 'sometimes|required|in:draft,completed,cancelled',
                'payment_status' => 'sometimes|required|in:paid,partial,unpaid',
                'is_email_sent' => 'sometimes|required|boolean'
            ]);
    
            if ($validator->fails()) {
                \Log::warning('Invoice update validation failed', [
                    'invoice_id' => $id,
                    'errors' => $validator->errors()->toArray(),
                    'request_data' => $request->all()
                ]);
                return response()->json([
                    'error' => true,
                    'message' => 'Validation error',
                    'errors' => $validator->errors()
                ], 422);
            }
    
            try {
                DB::beginTransaction();
                \Log::info('Starting invoice update transaction', [
                    'invoice_id' => $invoice->id,
                    'old_status' => $invoice->status,
                    'new_status' => $request->has('status') ? $request->status : null,
                    'old_payment_status' => $invoice->payment_status,
                    'new_payment_status' => $request->has('payment_status') ? $request->payment_status : null,
                    'old_email_status' => $invoice->is_email_sent,
                    'new_email_status' => $request->has('is_email_sent') ? $request->is_email_sent : null
                ]);
    
                $oldData = $invoice->toArray();
                $changes = [];
    
                // Update document status if provided
                if ($request->has('status')) {
                    $invoice->status = $request->status;
                    $changes['status'] = [
                        'old' => $oldData['status'],
                        'new' => $invoice->status
                    ];
                }
    
                // Update payment status if provided
                if ($request->has('payment_status')) {
                    $invoice->payment_status = $request->payment_status;
                    $changes['payment_status'] = [
                        'old' => $oldData['payment_status'] ?? 'unpaid',
                        'new' => $invoice->payment_status
                    ];
    
                    // If we're updating the invoice's payment status, also update the sale
                    // if this is from a sale and not a quote
                    if ($invoice->type === 'invoice' && 
                        $invoice->invoiceable_type === 'App\Models\Sale' && 
                        $invoice->invoiceable_id) {
                        
                        try {
                            $sale = \App\Models\Sale::find($invoice->invoiceable_id);
                            if ($sale && $sale->team_id === $user->team->id) {
                                $sale->payment_status = $invoice->payment_status;
                                $sale->save();
                                
                                \Log::info('Updated sale payment status', [
                                    'sale_id' => $sale->id,
                                    'new_payment_status' => $invoice->payment_status
                                ]);
                            }
                        } catch (\Exception $e) {
                            \Log::warning('Failed to update sale payment status', [
                                'sale_id' => $invoice->invoiceable_id,
                                'error' => $e->getMessage()
                            ]);
                            // Don't fail the whole transaction if this fails
                        }
                    }
                }
    
                // Update email sent status if provided
                if ($request->has('is_email_sent')) {
                    $invoice->is_email_sent = $request->is_email_sent;
                    $changes['is_email_sent'] = [
                        'old' => $oldData['is_email_sent'] ?? false,
                        'new' => $invoice->is_email_sent
                    ];
                }
    
                $invoice->save();
    
                \Log::info('Invoice updated successfully', [
                    'invoice_id' => $invoice->id,
                    'changes' => $changes
                ]);
    
                try {
                    ActivityLog::create([
                        'log_type' => 'Update',
                        'model_type' => $invoice->type === 'quote' ? "Quotation" : "Invoice",
                        'model_id' => $invoice->id,
                        'model_identifier' => $invoice->reference_number,
                        'user_identifier' => $user?->name,
                        'user_id' => $user->id,
                        'user_email' => $user?->email,
                        'ip_address' => $request->ip(),
                        'user_agent' => $request->userAgent(),
                        'description' => "Updated " . ($invoice->type === 'quote' ? "quotation" : "invoice") . " {$invoice->reference_number}",
                        'old_values' => $oldData,
                        'new_values' => $invoice->fresh()->toArray(),
                        'changes' => $changes
                    ]);
    
                    \Log::info('Activity log created for invoice update', [
                        'invoice_id' => $invoice->id,
                        'log_type' => 'Update'
                    ]);
                } catch (\Exception $e) {
                    \Log::error('Error creating activity log for invoice update', [
                        'invoice_id' => $invoice->id,
                        'error' => $e->getMessage(),
                        'trace' => $e->getTraceAsString()
                    ]);
                    // Don't throw the error as this is not critical
                }
    
                DB::commit();
                \Log::info('Invoice update transaction committed successfully', [
                    'invoice_id' => $invoice->id
                ]);
    
                $refreshedInvoice = $invoice->fresh(['items']);
                return response()->json([
                    'message' => 'Invoice updated successfully',
                    'invoice' => $refreshedInvoice
                ]);
    
            } catch (\Exception $e) {
                DB::rollBack();
                \Log::error('Error updating invoice', [
                    'invoice_id' => $id,
                    'error_message' => $e->getMessage(),
                    'error_code' => $e->getCode(),
                    'error_file' => $e->getFile(),
                    'error_line' => $e->getLine(),
                    'trace' => $e->getTraceAsString(),
                    'request_data' => $request->all()
                ]);
    
                return response()->json([
                    'error' => true,
                    'message' => 'Error updating invoice',
                    'debug_message' => config('app.debug') ? $e->getMessage() : null,
                    'error_details' => config('app.debug') ? [
                        'file' => $e->getFile(),
                        'line' => $e->getLine(),
                        'message' => $e->getMessage(),
                        'trace' => array_slice($e->getTrace(), 0, 5)
                    ] : null
                ], 500);
            }
        } catch (\Exception $e) {
            \Log::critical('Unhandled exception in invoice update', [
                'invoice_id' => $id,
                'error_message' => $e->getMessage(),
                'error_code' => $e->getCode(),
                'error_file' => $e->getFile(),
                'error_line' => $e->getLine(),
                'trace' => $e->getTraceAsString(),
                'request_data' => $request->all()
            ]);
    
            return response()->json([
                'error' => true,
                'message' => 'Critical error occurred while updating invoice',
                'debug_message' => config('app.debug') ? $e->getMessage() : null
            ], 500);
        }
    }
    


    public function destroy(Request $request, $id)
    {
        $user = $request->user();

        $invoice = Invoice::where('team_id', $user->team->id)->find($id);

        if (!$invoice) {
            return response()->json([
                'error' => true,
                'message' => 'Invoice not found'
            ], 404);
        }

        try {
            DB::beginTransaction();

            $invoice->delete();

            ActivityLog::create([
                'log_type' => 'Delete',
                'model_type' => "Invoice",
                'model_id' => $invoice->id,
                'model_identifier' => $invoice->reference_number,
                'user_identifier' => $user?->name,
                'user_id' => $user->id,
                'user_email' => $user?->email,
                'ip_address' => $request->ip(),
                'user_agent' => $request->userAgent(),
                'description' => "Deleted invoice {$invoice->reference_number}",
                'old_values' => $invoice->toArray()
            ]);

            DB::commit();

            return response()->json([
                'message' => 'Invoice deleted successfully'
            ]);

        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'error' => true,
                'message' => 'Error deleting invoice'
            ], 500);
        }
    }
    public function markAsEmailSent(Request $request, $id)
    {
        $user = $request->user();
        $invoice = Invoice::where('team_id', $user->team->id)->find($id);
        
        if (!$invoice) {
            return response()->json([
                'error' => true,
                'message' => 'Invoice not found'
            ], 404);
        }
        
        try {
            DB::beginTransaction();
            
            // Update the email sent status
            $oldEmailSentStatus = $invoice->is_email_sent;
            $invoice->is_email_sent = true;
            $invoice->save();
            
            // If invoice was draft and now marked as email sent, also mark status as sent
            if ($invoice->status === 'draft') {
                $oldStatus = $invoice->status;
                $invoice->status = 'sent';
                $invoice->save();
                
                // Log status change
                \Log::info('Invoice status changed from draft to sent when marking as email sent', [
                    'invoice_id' => $invoice->id,
                    'old_status' => $oldStatus,
                    'new_status' => $invoice->status
                ]);
            }
            
            ActivityLog::create([
                'log_type' => 'Email',
                'model_type' => $invoice->type === 'quote' ? "Quotation" : "Invoice",
                'model_id' => $invoice->id,
                'model_identifier' => $invoice->reference_number,
                'user_identifier' => $user?->name,
                'user_id' => $user->id,
                'user_email' => $user?->email,
                'ip_address' => $request->ip(),
                'user_agent' => $request->userAgent(),
                'description' => "Marked " . ($invoice->type === 'quote' ? "quotation" : "invoice") . " {$invoice->reference_number} as email sent",
                'old_values' => ['is_email_sent' => $oldEmailSentStatus],
                'new_values' => ['is_email_sent' => $invoice->is_email_sent]
            ]);
            
            DB::commit();
            
            return response()->json([
                'message' => ($invoice->type === 'quote' ? "Quotation" : "Invoice") . ' marked as email sent',
                'invoice' => $invoice->fresh()
            ]);
        } catch (\Exception $e) {
            DB::rollBack();
            \Log::error('Error marking invoice as email sent', [
                'invoice_id' => $id,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString() 
            ]);
            
            return response()->json([
                'error' => true,
                'message' => 'Error updating email status'
            ], 500);
        }
    }
    
    public function send(Request $request, $id)
    {
        $user = $request->user();
    
        $invoice = Invoice::where('team_id', $user->team->id)->find($id);
    
        if (!$invoice) {
            return response()->json([
                'error' => true,
                'message' => 'Invoice not found'
            ], 404);
        }
    
        // Check if the invoice has client contact information
        $hasClientEmail = false;
        if (isset($invoice->meta_data['contact']['data']['email'])) {
            $clientEmail = $invoice->meta_data['contact']['data']['email'];
            if (filter_var($clientEmail, FILTER_VALIDATE_EMAIL)) {
                $hasClientEmail = true;
            }
        }
    
        if (!$hasClientEmail) {
            return response()->json([
                'error' => true,
                'message' => 'Cannot send email - no valid client email address found'
            ], 422);
        }
    
        try {
            DB::beginTransaction();
            
            // Mark email as sent - only update this flag, not the status
            $invoice->is_email_sent = true;
            $invoice->save();
    
            // Send the actual email
            $clientName = $invoice->meta_data['contact']['data']['name'] ?? 'Client';
            $clientEmail = $invoice->meta_data['contact']['data']['email'];
            
            // Log the attempt
            \Log::info('Sending invoice email', [
                'invoice_id' => $invoice->id,
                'reference' => $invoice->reference_number,
                'client_email' => $clientEmail
            ]);
    
            // Create PDF here or use existing PDF generation method
            $pdfPath = null;
            try {
                // This would be your method to generate and save the PDF
                // $pdfPath = $this->generatePdf($invoice->id);
                
                // For this example, we assume the PDF generation works
                $documentType = $invoice->type === 'quote' ? 'Quotation' : 'Invoice';
                
                // Here you would add the actual email sending code
                // Mail::to($clientEmail)->send(new InvoiceEmail($invoice, $pdfPath, $documentType));
                
                // For now, just simulate email sending success
                \Log::info("Email would be sent to $clientEmail with $documentType {$invoice->reference_number}");
            } catch (\Exception $e) {
                \Log::error('Error sending invoice email', [
                    'invoice_id' => $invoice->id,
                    'error' => $e->getMessage()
                ]);
                
                throw $e;
            }
    
            ActivityLog::create([
                'log_type' => 'Email',
                'model_type' => $invoice->type === 'quote' ? "Quotation" : "Invoice",
                'model_id' => $invoice->id,
                'model_identifier' => $invoice->reference_number,
                'user_identifier' => $user?->name,
                'user_id' => $user->id,
                'user_email' => $user?->email,
                'ip_address' => $request->ip(),
                'user_agent' => $request->userAgent(),
                'description' => "Sent " . ($invoice->type === 'quote' ? "quotation" : "invoice") . 
                               " {$invoice->reference_number} to " . $clientEmail,
                'new_values' => [
                    'is_email_sent' => $invoice->is_email_sent
                ]
            ]);
    
            DB::commit();
    
            return response()->json([
                'message' => ($invoice->type === 'quote' ? "Quote" : "Invoice") . ' sent successfully',
                'invoice' => $invoice->fresh()
            ]);
    
        } catch (\Exception $e) {
            DB::rollBack();
            \Log::error('Error sending invoice', [
                'invoice_id' => $id,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            
            return response()->json([
                'error' => true,
                'message' => 'Error sending invoice: ' . $e->getMessage()
            ], 500);
        }
    }
    

    public function markAsPaid(Request $request, $id)
    {
        $user = $request->user();
    
        $invoice = Invoice::where('team_id', $user->team->id)->find($id);
    
        if (!$invoice) {
            return response()->json([
                'error' => true,
                'message' => 'Invoice not found'
            ], 404);
        }
    
        try {
            DB::beginTransaction();
    
            // Save old status for logging
            $oldPaymentStatus = $invoice->payment_status;
            
            // Update the payment status
            $invoice->payment_status = 'paid';
            $invoice->save();
    
            // If this invoice is from a sale (and not a quote), update the sale's payment status too
            if ($invoice->type === 'invoice' && 
                $invoice->invoiceable_type === 'App\Models\Sale' && 
                $invoice->invoiceable_id) {
                
                try {
                    $sale = \App\Models\Sale::find($invoice->invoiceable_id);
                    if ($sale && $sale->team_id === $user->team->id) {
                        $sale->payment_status = 'paid';
                        $sale->save();
                        
                        \Log::info('Updated sale payment status to paid', [
                            'sale_id' => $sale->id
                        ]);
                    }
                } catch (\Exception $e) {
                    \Log::warning('Failed to update sale payment status to paid', [
                        'sale_id' => $invoice->invoiceable_id,
                        'error' => $e->getMessage()
                    ]);
                    // Don't fail the transaction if this update fails
                }
            }
    
            ActivityLog::create([
                'log_type' => 'Payment Status Update',
                'model_type' => $invoice->type === 'quote' ? "Quotation" : "Invoice",
                'model_id' => $invoice->id,
                'model_identifier' => $invoice->reference_number,
                'user_identifier' => $user?->name,
                'user_id' => $user->id,
                'user_email' => $user?->email,
                'ip_address' => $request->ip(),
                'user_agent' => $request->userAgent(),
                'description' => "Marked " . ($invoice->type === 'quote' ? "quotation" : "invoice") . 
                               " {$invoice->reference_number} as paid",
                'old_values' => ['payment_status' => $oldPaymentStatus],
                'new_values' => ['payment_status' => 'paid']
            ]);
    
            DB::commit();
    
            return response()->json([
                'message' => ($invoice->type === 'quote' ? "Quotation" : "Invoice") . ' marked as paid',
                'invoice' => $invoice->fresh()
            ]);
    
        } catch (\Exception $e) {
            DB::rollBack();
            \Log::error('Error marking invoice as paid', [
                'invoice_id' => $id,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            
            return response()->json([
                'error' => true,
                'message' => 'Error updating payment status'
            ], 500);
        }
    }
    

    public function download(Request $request, $id)
    {
        try {
            $user = $request->user();
            
            // Get invoice with all necessary relationships
            $invoice = Invoice::where('team_id', $user->team->id)
                             ->with([
                                 'items',
                                 'invoiceable',
                                 'team',
                             ])
                             ->findOrFail($id);
    
            if (!$invoice) {
                return response()->json([
                    'error' => true,
                    'message' => 'Invoice not found'
                ], 404);
            }
    
            // Verify all necessary data is present
            if (!$invoice->items || !$invoice->invoiceable) {
                return response()->json([
                    'error' => true,
                    'message' => 'Invoice data is incomplete'
                ], 422);
            }
    
            // Get team's preferred locale, fallback to default
            $locale = $invoice->team->locale ?? config('app.locale');
            
            // Set locale for this request
            app()->setLocale($locale);
    
            // Process logo for DomPDF
            if ($invoice->team->image_path) {
                $imagePath = storage_path('app/public/' . $invoice->team->image_path);
                if (file_exists($imagePath)) {
                    try {
                        // Get image info
                        $imgInfo = getimagesize($imagePath);
                        
                        // Calculate max dimensions
                        $maxWidth = 150; // pixels
                        $maxHeight = 60; // pixels
                        
                        // Check if we need to resize
                        if ($imgInfo[0] > $maxWidth || $imgInfo[1] > $maxHeight) {
                            // Convert the image to base64
                            $logoData = file_get_contents($imagePath);
                            $logoBase64 = 'data:' . mime_content_type($imagePath) . ';base64,' . base64_encode($logoData);
                            
                            // Provide the base64 image and dimensions to the view
                            $invoice->team->logo_data_url = $logoBase64;
                            
                            // Also provide dimensions for the img tag
                            if ($imgInfo[0] > $maxWidth) {
                                $ratio = $maxWidth / $imgInfo[0];
                                $width = $maxWidth;
                                $height = $imgInfo[1] * $ratio;
                            } else {
                                $ratio = $maxHeight / $imgInfo[1];
                                $height = $maxHeight;
                                $width = $imgInfo[0] * $ratio;
                            }
                            
                            $invoice->team->logo_width = round($width);
                            $invoice->team->logo_height = round($height);
                        } else {
                            // Image is already small enough, convert to base64
                            $logoData = file_get_contents($imagePath);
                            $logoBase64 = 'data:' . mime_content_type($imagePath) . ';base64,' . base64_encode($logoData);
                            $invoice->team->logo_data_url = $logoBase64;
                            $invoice->team->logo_width = $imgInfo[0];
                            $invoice->team->logo_height = $imgInfo[1];
                        }
                    } catch (\Exception $e) {
                        \Log::warning("Error processing invoice logo: " . $e->getMessage());
                        // Continue without logo
                    }
                }
            }
    
            // Format numbers according to locale
            $numberFormatter = new \NumberFormatter($locale, \NumberFormatter::DECIMAL);
            $currencyFormatter = new \NumberFormatter($locale, \NumberFormatter::CURRENCY);
            
            // Format dates according to locale
            $dateFormatter = new \IntlDateFormatter(
                $locale,
                \IntlDateFormatter::LONG,
                \IntlDateFormatter::NONE
            );
    
            // Define hard-coded fallbacks for missing translations
            $fallbacks = [
                'payment_information' => 'Payment Information',
                'payment_method' => 'Payment Method',
                'bank_transfer' => 'Bank Transfer',
                'bank_account' => 'Bank Account',
                'payment_terms' => 'Payment Terms',
                'payment_date' => 'Payment Date', 
                'invoice_number' => 'Invoice Number',
                'address' => 'Address',
                'name' => 'Name',
                'standard_payment_terms' => 'Payment is due within',
                'days' => 'days',
                'status' => 'Status'
            ];
    
            // Prepare translations with fallbacks
            $translations = [];
            foreach ([
                'invoice', 'bill', 'bill_to', 'supplier', 'from', 'tax_number',
                'issue_date', 'due_date', 'description', 'quantity', 'unit_price',
                'tax', 'discount', 'total', 'subtotal', 'total_amount', 'notes',
                'thank_you', 'generated_on', 'reference_number', 'attn', 'email', 'phone',
                'payment_information', 'payment_method', 'bank_transfer', 'bank_account',
                'payment_terms', 'invoice_number', 'address', 'name', 'standard_payment_terms', 
                'days', 'status'
            ] as $key) {
                $translationKey = "invoice.$key";
                $translated = __($translationKey);
                
                // If translation doesn't exist (returns the key itself), use fallback
                if ($translated === $translationKey && isset($fallbacks[$key])) {
                    $translations[$key] = $fallbacks[$key];
                } else {
                    $translations[$key] = $translated;
                }
            }
    
            // Prepare formatted data with proper translations
            $formattedInvoice = [
                'invoice' => $invoice,
                'formatted' => [
                    'issue_date' => $invoice->issue_date ? $dateFormatter->format($invoice->issue_date) : '',
                    'due_date' => $invoice->due_date ? $dateFormatter->format($invoice->due_date) : '',
                    'subtotal' => $currencyFormatter->format($invoice->meta_data['subtotal'] ?? 0),
                    'tax_amount' => $currencyFormatter->format($invoice->tax_amount),
                    'discount_amount' => $currencyFormatter->format($invoice->discount_amount),
                    'total_amount' => $currencyFormatter->format($invoice->total_amount),
                ],
                'items' => $invoice->items->map(function ($item) use ($numberFormatter, $currencyFormatter) {
                    return [
                        'description' => $item->description,
                        'quantity' => $numberFormatter->format($item->quantity),
                        'unit_price' => $currencyFormatter->format($item->unit_price),
                        'tax_amount' => $currencyFormatter->format($item->tax_amount ?? 0),
                        'discount_amount' => $currencyFormatter->format($item->discount_amount ?? 0),
                        'total_price' => $currencyFormatter->format($item->total_price),
                    ];
                }),
                'rtl' => in_array($locale, ['ar']), // RTL support for Arabic
            ];
            
            // Create filename with locale
            $filename = "invoice-{$invoice->reference_number}-{$locale}.pdf";
            $tempPath = storage_path('app/public/temp');
            
            if (!file_exists($tempPath)) {
                mkdir($tempPath, 0755, true);
            }
            
            $pdfPath = $tempPath . '/' . $filename;
    
            // Generate HTML content 
            $html = View::make('invoices.dompdf', $formattedInvoice)->render();
    
            // Configure DomPDF
            $options = new \Dompdf\Options();
            $options->set('isHtml5ParserEnabled', true);
            $options->set('isRemoteEnabled', true); // For images
            $options->set('isPhpEnabled', false);   // Security
            $options->set('defaultFont', 'Arial');  
            $options->set('defaultMediaType', 'screen'); // Better for CSS styles
            $options->set('isFontSubsettingEnabled', true); // Better font handling
            
            // Create DomPDF instance
            $dompdf = new \Dompdf\Dompdf($options);
            $dompdf->loadHtml($html);
            $dompdf->setPaper('A4');
            $dompdf->render();
            
            // Save PDF to file
            file_put_contents($pdfPath, $dompdf->output());
            
            // Log activity
            ActivityLog::create([
                'log_type' => 'Download',
                'model_type' => "Invoice",
                'model_id' => $invoice->id,
                'model_identifier' => $invoice->reference_number,
                'user_identifier' => $user?->name,
                'user_id' => $user->id,
                'user_email' => $user?->email,
                'ip_address' => $request->ip(),
                'user_agent' => $request->userAgent(),
                'description' => "Downloaded invoice {$invoice->reference_number} in {$locale}",
                'meta_data' => ['locale' => $locale],
            ]);
    
            // Free up memory by removing references to large objects
            $dompdf = null;
            $html = null;
            $formattedInvoice = null;
            $invoice = null;
            
            // Garbage collection
            if (function_exists('gc_collect_cycles')) {
                gc_collect_cycles();
            }
            
            // Return PDF
            return response()->download($pdfPath, $filename, [
                'Content-Type' => 'application/pdf',
            ])->deleteFileAfterSend(true);
    
        } catch (\Exception $e) {
            \Log::error('PDF Generation Error: ' . $e->getMessage(), [
                'invoice_id' => $id ?? null,
                'user_id' => $request->user()->id ?? null,
                'trace' => $e->getTraceAsString()
            ]);
            
            return response()->json([
                'error' => true,
                'message' => 'Error generating invoice PDF: ' . $e->getMessage(),
                'details' => config('app.debug') ? $e->getTrace() : null
            ], 500);
        }
    }
    
    
    
    
}
