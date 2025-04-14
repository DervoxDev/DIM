<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Barryvdh\DomPDF\Facade\Pdf as PDF;
use DNS1D;
use DNS2D;
use App\Models\Product;
use App\Models\Team;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\App;

class BarcodeController extends Controller
{
    public function generate(Request $request)
    {
        try {
            // Validate request
            $request->validate([
                'barcode_type' => 'required|string',
                'paper_width' => 'required|numeric',
                'paper_height' => 'required|numeric',
                'show_team_name' => 'required|boolean',
                'show_price' => 'required|boolean',
                'show_product_name' => 'required|boolean',
                'show_content' => 'required|boolean',
            ]);
    
            // Get paper dimensions
            $paperWidth = $request->input('paper_width');
            $paperHeight = $request->input('paper_height');
    
            // Variables to store product and team data
            $product = null;
            $team = null;
            $content = $request->input('content', '');
            $customName = $request->input('custom_name', '');
            $customPrice = $request->input('custom_price', '');
            $teamName = '';
            $locale = config('app.locale'); // Default locale
    
            if ($request->has('product_id')) {
                $product = Product::findOrFail($request->input('product_id'));
                $content = $product->barcode ?? (string)$product->id;
                $customName = $product->name;
                
                // Load team if available
                if ($product->team_id) {
                    $team = Team::find($product->team_id);
                    
                    // Get locale from team if available
                    if ($team) {
                        $teamName = $team->name;
                        $locale = $team->locale ?? config('app.locale');
                    }
                }
                
                // Set application locale
                App::setLocale($locale);
                
                // Debug log for locale
                Log::info("Using locale from team: {$locale}");
                
                // Translations for price label based on locale
                $priceLabel = ($locale === 'fr') ? 'Prix: ' : 'Price: ';
                $currency = 'DH';
                
                // Ensure price is properly set with label and currency
                if (!empty($product->price_formatted)) {
                    $customPrice = $priceLabel . $product->price_formatted . ' ' . $currency;
                } elseif (is_numeric($product->price)) {
                    $customPrice = $priceLabel . number_format($product->price, 2) . ' ' . $currency;
                }
            } else if (!empty($customPrice)) {
                // For custom price without product, we still need to format it
                $priceLabel = ($locale === 'fr') ? 'Prix: ' : 'Price: ';
                $currency = 'DH';
                $customPrice = $priceLabel . $customPrice . ' ' . $currency;
            }
    
            // Make sure content is not empty
            if (empty($content)) {
                $content = '0';
            }
            
            // Log values for debugging
            Log::info("Barcode generation - Locale: {$locale}, Product Name: {$customName}, Price: {$customPrice}, Team Name: {$teamName}");
    
            // Normalize barcode type
            $barcodeType = $this->normalizeBarcodeType($request->input('barcode_type'));
            
            // Generate the barcode image
            $barcodeImage = $this->generateBarcode($content, $barcodeType, $paperHeight);
            
            // Create PDF with barcode
            $data = [
                'barcode_image' => $barcodeImage,
                'barcode_content' => $content,
                'barcode_type' => $barcodeType,
                'product_name' => $customName,
                'price' => $customPrice,
                'team_name' => $teamName,
                'show_team_name' => (bool)$request->input('show_team_name'),
                'show_price' => (bool)$request->input('show_price'),
                'show_product_name' => (bool)$request->input('show_product_name'),
                'show_content' => (bool)$request->input('show_content'),
                'paper_width' => $paperWidth,
                'paper_height' => $paperHeight,
                'locale' => $locale,
            ];
            
            // Set paper size
            $customPaper = array(0, 0, $paperWidth * 2.83, $paperHeight * 2.83); // Convert mm to points
            
            $pdf = PDF::loadView('pdfs.barcode', $data)
                ->setPaper($customPaper, 'portrait');
            
            // Set PDF options to prevent blank pages
            $pdf->setOptions([
                'dpi' => 96,
                'defaultFont' => 'sans-serif',
                'isHtml5ParserEnabled' => true,
                'isRemoteEnabled' => true,
            ]);
            
            // Return PDF with headers
            $headers = [
                'Content-Type' => 'application/pdf',
                'Content-Disposition' => 'inline; filename="barcode.pdf"',
            ];
            
            return $pdf->stream('barcode.pdf', $headers);
        } catch (\Exception $e) {
            Log::error('Barcode generation failed: ' . $e->getMessage());
            Log::error('Stack trace: ' . $e->getTraceAsString());
            return response()->json([
                'success' => false,
                'message' => 'Failed to generate barcode',
                'error' => $e->getMessage()
            ], 500);
        }
    }
    /**
     * Generate a barcode using the milon/barcode library
     */
    private function generateBarcode($content, $type, $paperHeight)
    {
        // Format content based on barcode type if necessary
        $content = $this->formatBarcodeContent($content, $type);
        
        // Calculate space allocation for elements
        // Reserved space for text elements in mm (approximate)
        $textElementsSpace = 12; // Space for team name, product name, barcode text and price (if all visible)
        
        // Calculate available height for barcode in mm
        $availableBarcodeHeightMm = max(10, $paperHeight - $textElementsSpace);
        
        // Convert mm to pixels at 96 DPI (1 inch = 25.4mm, 1 inch = 96px at screen resolution)
        // Barcode should take up most of the available space but have a margin
        $barcodeHeightPx = ($availableBarcodeHeightMm / 25.4) * 96 * 0.8; // 80% of available space
        
        // Ensure reasonable limits
        $barcodeHeightPx = max(30, min(400, $barcodeHeightPx));
        
        try {
            // Check if 2D or 1D barcode
            if (in_array($type, ['QRCODE', 'PDF417', 'DATAMATRIX'])) {
                // For 2D barcodes - calculate appropriate size factor
                // For QR codes, the size parameter is a multiplier, not direct pixels
                // Higher size value = larger QR code
                $size = max(2, min(10, ceil($paperHeight / 15))); // Scale based on paper height
                
                $barcodeImage = DNS2D::getBarcodePNG($content, $type, $size, $size);
                
                // Return as is for 2D codes (they're already scaled)
                return $barcodeImage;
            } else {
                // For 1D barcodes - calculate width to make bars readable
                // Width of bars should scale with height for readability
                $width = max(1, min(4, ceil($paperHeight / 10)));
                $height = $barcodeHeightPx;
                
                // Generate 1D barcode with calculated dimensions
                return DNS1D::getBarcodePNG($content, $type, $width, $height);
            }
        } catch (\Exception $e) {
            Log::error("Original barcode error: " . $e->getMessage());
            
            // Fallback to CODE128
            try {
                $width = max(1, min(4, ceil($paperHeight / 25)));
                $height = $barcodeHeightPx;
                return DNS1D::getBarcodePNG($content, 'C128', $width, $height);
            } catch (\Exception $e2) {
                Log::error("Fallback barcode error: " . $e2->getMessage());
                
                // If everything fails, create a simple text placeholder
                $img = imagecreate(300, $barcodeHeightPx);
                $bgColor = imagecolorallocate($img, 255, 255, 255);
                $textColor = imagecolorallocate($img, 0, 0, 0);
                $fontSize = min(5, max(1, ceil($paperHeight / 10)));
                imagestring($img, $fontSize, 10, $barcodeHeightPx/2 - 10, "Barcode: $content", $textColor);
                
                ob_start();
                imagepng($img);
                $imageData = ob_get_contents();
                ob_end_clean();
                imagedestroy($img);
                
                return base64_encode($imageData);
            }
        }
    }
    
    /**
     * Format barcode content based on the barcode type
     */
    private function formatBarcodeContent($content, $type)
    {
        // Ensure content is string
        $content = (string)$content;
        
        // Format based on barcode type
        switch ($type) {
            case 'EAN13':
                // EAN13 must be 13 digits
                $content = preg_replace('/[^0-9]/', '', $content);
                if (strlen($content) > 13) {
                    $content = substr($content, 0, 13);
                } elseif (strlen($content) < 13) {
                    $content = str_pad($content, 13, '0', STR_PAD_LEFT);
                }
                break;
                
            case 'EAN8':
                // EAN8 must be 8 digits
                $content = preg_replace('/[^0-9]/', '', $content);
                if (strlen($content) > 8) {
                    $content = substr($content, 0, 8);
                } elseif (strlen($content) < 8) {
                    $content = str_pad($content, 8, '0', STR_PAD_LEFT);
                }
                break;
                
            case 'UPCA':
                // UPCA must be 12 digits
                $content = preg_replace('/[^0-9]/', '', $content);
                if (strlen($content) > 12) {
                    $content = substr($content, 0, 12);
                } elseif (strlen($content) < 12) {
                    $content = str_pad($content, 12, '0', STR_PAD_LEFT);
                }
                break;
                
            case 'UPCE':
                // UPCE must be 8 digits
                $content = preg_replace('/[^0-9]/', '', $content);
                if (strlen($content) > 8) {
                    $content = substr($content, 0, 8);
                } elseif (strlen($content) < 8) {
                    $content = str_pad($content, 8, '0', STR_PAD_LEFT);
                }
                break;
        }
        
        return $content;
    }

    /**
     * Normalize barcode type to a supported type
     */
    private function normalizeBarcodeType($type)
    {
        // Convert to uppercase
        $type = strtoupper($type);
        
        // Map of supported barcode types in milon/barcode
        $supportedTypes = [
            // 1D barcodes
            'C39', 'C39+', 'C39E', 'C39E+', 'C93', 
            'S25', 'S25+', 'I25', 'I25+', 
            'C128', 'C128A', 'C128B', 'C128C',
            'GS1-128', 'EAN2', 'EAN5', 'EAN8', 'EAN13',
            'UPCA', 'UPCE', 'MSI', 'MSI+', 'POSTNET',
            'PLANET', 'RMS4CC', 'KIX', 'IMB', 'CODABAR',
            'CODE11', 'PHARMA', 'PHARMA2T',
            
            // 2D barcodes
            'QRCODE', 'PDF417', 'DATAMATRIX'
        ];
        
        // Map of common alternative names
        $typeMap = [
            'CODE39' => 'C39',
            'CODE39+' => 'C39+',
            'CODE39E' => 'C39E',
            'CODE39E+' => 'C39E+',
            'CODE93' => 'C93',
            'CODE128' => 'C128',
            'CODE128A' => 'C128A',
            'CODE128B' => 'C128B',
            'CODE128C' => 'C128C',
            'QR' => 'QRCODE'
        ];
        
        // If the type is in the map, convert it
        if (isset($typeMap[$type])) {
            $type = $typeMap[$type];
        }
        
        // Check if the type is supported
        if (!in_array($type, $supportedTypes)) {
            // Default to C128 if not supported
            return 'C128';
        }
        
        return $type;
    }
}
