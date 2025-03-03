<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class PurchaseItem extends Model
{
    use HasFactory;

        protected $fillable = [
            'purchase_id',
            'product_id',
            'package_id',
            'is_package',
            'quantity',
            'unit_price',
            'total_price',
            'tax_rate',
            'tax_amount',
            'discount_amount',
            'total_pieces',
            'notes'
        ];
    
        protected $casts = [
            'is_package' => 'boolean',
            'quantity' => 'integer',
            'unit_price' => 'decimal:2',
            'total_price' => 'decimal:2',
            'tax_rate' => 'decimal:2',
            'tax_amount' => 'decimal:2',
            'discount_amount' => 'decimal:2',
            'total_pieces' => 'integer'
        ];
    
        public function purchase()
        {
            return $this->belongsTo(Purchase::class);
        }
    
        public function product()
        {
            return $this->belongsTo(Product::class);
        }
    
        public function package()
        {
            return $this->belongsTo(ProductPackage::class);
        }
    
        public function calculateTotals()
        {
            $subtotal = $this->quantity * $this->unit_price;
            $this->tax_amount = ($subtotal * $this->tax_rate) / 100;
            $this->total_price = $subtotal + $this->tax_amount - $this->discount_amount;
            
            // Calculate total pieces based on package or individual pieces
            $this->total_pieces = $this->is_package && $this->package 
                ? $this->quantity * $this->package->pieces_per_package 
                : $this->quantity;
        }
    }
    