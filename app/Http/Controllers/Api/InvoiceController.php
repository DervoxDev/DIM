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

    public function update(Request $request, $id)
    {
        $user = $request->user();

        $invoice = Invoice::where('team_id', $user->team->id)->find($id);

        if (!$invoice) {
            return response()->json([
                'error' => true,
                'message' => 'Invoice not found'
            ], 404);
        }

        $validator = Validator::make($request->all(), [
            'status' => 'sometimes|required|in:draft,sent,paid,cancelled',
            'due_date' => 'nullable|date',
            'notes' => 'nullable|string',
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

            $oldData = $invoice->toArray();
            $invoice->update($request->all());

            ActivityLog::create([
                'log_type' => 'Update',
                'model_type' => "Invoice",
                'model_id' => $invoice->id,
                'model_identifier' => $invoice->reference_number,
                'user_identifier' => $user?->name,
                'user_id' => $user->id,
                'user_email' => $user?->email,
                'ip_address' => $request->ip(),
                'user_agent' => $request->userAgent(),
                'description' => "Updated invoice {$invoice->reference_number}",
                'old_values' => $oldData,
                'new_values' => $invoice->fresh()->toArray()
            ]);

            DB::commit();

            return response()->json([
                'message' => 'Invoice updated successfully',
                'invoice' => $invoice->fresh()
            ]);

        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'error' => true,
                'message' => 'Error updating invoice'
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

        try {
            DB::beginTransaction();

            $invoice->markAsSent();

            ActivityLog::create([
                'log_type' => 'Send',
                'model_type' => "Invoice",
                'model_id' => $invoice->id,
                'model_identifier' => $invoice->reference_number,
                'user_identifier' => $user?->name,
                'user_id' => $user->id,
                'user_email' => $user?->email,
                'ip_address' => $request->ip(),
                'user_agent' => $request->userAgent(),
                'description' => "Sent invoice {$invoice->reference_number}",
                'new_values' => $invoice->fresh()->toArray()
            ]);

            DB::commit();

            return response()->json([
                'message' => 'Invoice sent successfully',
                'invoice' => $invoice->fresh()
            ]);

        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'error' => true,
                'message' => 'Error sending invoice'
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

            $invoice->markAsPaid();

            ActivityLog::create([
                'log_type' => 'Status Update',
                'model_type' => "Invoice",
                'model_id' => $invoice->id,
                'model_identifier' => $invoice->reference_number,
                'user_identifier' => $user?->name,
                'user_id' => $user->id,
                'user_email' => $user?->email,
                'ip_address' => $request->ip(),
                'user_agent' => $request->userAgent(),
                'description' => "Marked invoice {$invoice->reference_number} as paid",
                'new_values' => $invoice->fresh()->toArray()
            ]);

            DB::commit();

            return response()->json([
                'message' => 'Invoice marked as paid',
                'invoice' => $invoice->fresh()
            ]);

        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'error' => true,
                'message' => 'Error updating invoice status'
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
                                 // Add any other necessary relationships
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
    
            // Generate HTML
            $html = View::make('invoices.pdf', [
                'invoice' => $invoice,
                // Add any other data needed by the template
            ])->render();
    
            // Create filename and path
            $filename = "invoice-{$invoice->reference_number}.pdf";
            $tempPath = storage_path('app/public/temp');
            
            if (!file_exists($tempPath)) {
                mkdir($tempPath, 0755, true);
            }
            
            $pdfPath = $tempPath . '/' . $filename;
    
            // Generate PDF using Browsershot
            Browsershot::html($html)
                ->format('A4')
                ->margins(16, 16, 16, 16)
                ->showBackground()
                ->savePdf($pdfPath);
    
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
                'description' => "Downloaded invoice {$invoice->reference_number}",
            ]);
    
            // Return file download response
            return response()->download($pdfPath, $filename, [
                'Content-Type' => 'application/pdf',
            ])->deleteFileAfterSend(true);
    
        } catch (\Exception $e) {
            \Log::error('PDF Generation Error: ' . $e->getMessage());
            return response()->json([
                'error' => true,
                'message' => 'Error generating invoice PDF: ' . $e->getMessage(),
                'details' => $e->getTrace()
            ], 500);
        }
    }
    
}
