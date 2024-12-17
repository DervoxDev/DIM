<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class SaleItem extends Model
{
    use HasFactory;

    protected $fillable = [
        'sale_id',
        'product_id',
        'package_id',
        'is_package',
        'quantity',
        'total_pieces',
        'unit_price',
        'total_price',
        'tax_rate',
        'tax_amount',
        'discount_amount',
        'notes',
    ];

    protected $casts = [
        'is_package' => 'boolean',
        'quantity' => 'integer',
        'total_pieces' => 'integer',
        'unit_price' => 'decimal:2',
        'total_price' => 'decimal:2',
        'tax_rate' => 'decimal:2',
        'tax_amount' => 'decimal:2',
        'discount_amount' => 'decimal:2',
    ];

    public function package()
    {
        return $this->belongsTo(ProductPackage::class);
    }

    public function updateProductStock()
    {
        if ($this->is_package) {
            $totalPieces = $this->quantity * $this->package->pieces_per_package;
            $this->product->updateStock($totalPieces, 'subtract');
        } else {
            $this->product->updateStock($this->quantity, 'subtract');
        }
    }

    // Relationships
    public function sale()
    {
        return $this->belongsTo(Sale::class);
    }

    public function product()
    {
        return $this->belongsTo(Product::class);
    }

    // Helper methods
    public function calculateTotals()
    {
        // Calculate total pieces
        $this->total_pieces = $this->is_package 
            ? $this->quantity * $this->package->pieces_per_package 
            : $this->quantity;

        // Calculate price totals
        $subtotal = $this->quantity * $this->unit_price;
        $this->tax_amount = ($subtotal * $this->tax_rate) / 100;
        $this->total_price = $subtotal + $this->tax_amount - $this->discount_amount;
    }

}
