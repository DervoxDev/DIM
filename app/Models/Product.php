<?php

    namespace App\Models;

    use Illuminate\Database\Eloquent\Model;
    use Illuminate\Database\Eloquent\Factories\HasFactory;
    use Illuminate\Database\Eloquent\SoftDeletes;
    use Illuminate\Database\Eloquent\Relations\BelongsTo;
    use Illuminate\Database\Eloquent\Relations\HasMany;
    use Illuminate\Support\Facades\DB;

    class Product extends Model
    {
        use HasFactory, SoftDeletes;

        protected $fillable = [
            'team_id',
            'reference',
            'name',
            'description',
            'price',
            'purchase_price',
            'expired_date',
            'quantity',
            'product_unit_id',
            'sku',
            'barcode',
            'status',
            'min_stock_level',
            'max_stock_level',
            'reorder_point',
            'location',
        ];

        protected $casts = [
            'expired_date' => 'date',
            'price' => 'integer',
            'purchase_price' => 'integer',
            'quantity' => 'integer',
            'min_stock_level' => 'integer',
            'max_stock_level' => 'integer',
            'reorder_point' => 'integer',
        ];

        // Relationships
        public function team(): BelongsTo
        {
            return $this->belongsTo(Team::class);
        }

        public function unit(): BelongsTo
        {
            return $this->belongsTo(ProductUnit::class, 'product_unit_id');
        }
        public function stockMovements(): HasMany
          {
              return $this->hasMany(StockMovement::class);
      }   
      public function packages()
      {
          return $this->hasMany(ProductPackage::class);
      }
        // Scopes
        public function scopeActive($query)
        {
            return $query->where('status', 'active');
        }

        public function scopeLowStock($query)
        {
            return $query->whereRaw('quantity <= min_stock_level');
        }

        public function scopeExpiringSoon($query, $days = 30)
        {
            return $query->whereNotNull('expired_date')
                         ->whereDate('expired_date', '<=', now()->addDays($days));
        }

        // Accessors & Mutators
        public function getIsLowStockAttribute(): bool
        {
            return $this->quantity <= $this->min_stock_level;
        }

        public function getIsExpiredAttribute(): bool
        {
            return $this->expired_date && $this->expired_date->isPast();
        }

        // // Helper methods
        // public function updateStock(int $quantity, string $operation = 'add'): void
        // {
        //     if ($operation === 'add') {
        //         $this->increment('quantity', $quantity);
        //     } else {
        //         $this->decrement('quantity', $quantity);
        //     }
        // }

public function updateStock($quantity, $operation = 'add')
{
    try {
        DB::beginTransaction();
        
        if ($operation === 'subtract') {
            // Check if we have enough stock
            if ($this->quantity < $quantity) {
                throw new \Exception("Insufficient stock. Available: {$this->quantity}, Requested: {$quantity}");
            }
            $this->quantity -= $quantity;
        } else {
            $this->quantity += $quantity;
        }

        $success = $this->save();
        
        if (!$success) {
            throw new \Exception("Failed to update product stock");
        }

        // Log stock movement
        $this->stockMovements()->create([
            'team_id' => $this->team_id,
            'quantity' => $operation === 'subtract' ? -$quantity : $quantity,
            'type' => $operation === 'subtract' ? 'sale' : 'purchase',
            'reference' => 'Stock ' . ($operation === 'subtract' ? 'decrease' : 'increase'),
            'movement_date' => now(),
        ]);

        DB::commit();
        return true;

    } catch (\Exception $e) {
        DB::rollBack();
        \Log::error('Stock update failed', [
            'product_id' => $this->id,
            'product_name' => $this->name,
            'current_stock' => $this->quantity,
            'requested_quantity' => $quantity,
            'operation' => $operation,
            'error' => $e->getMessage()
        ]);
        throw $e;
    }
}
// app/Models/Product.php

public function updatePrices($purchasePrice, $sellingPrice)
{
    try {
        DB::beginTransaction();
        
        $oldPrices = [
            'purchase_price' => $this->purchase_price,
            'price' => $this->price
        ];

        // Update prices if provided
        if ($purchasePrice > 0) {
            $this->purchase_price = $purchasePrice;
        }
        if ($sellingPrice > 0) {
            $this->price = $sellingPrice;
        }

        $success = $this->save();
        
        if (!$success) {
            throw new \Exception("Failed to update product prices");
        }

        // Log price changes
        ActivityLog::create([
            'log_type' => 'Price Update',
            'model_type' => 'Product',
            'model_id' => $this->id,
            'model_identifier' => $this->name,
            'description' => "Updated product prices",
            'old_values' => $oldPrices,
            'new_values' => [
                'purchase_price' => $this->purchase_price,
                'price' => $this->price
            ]
        ]);

        DB::commit();
        return true;

    } catch (\Exception $e) {
        DB::rollBack();
        \Log::error('Price update failed', [
            'product_id' => $this->id,
            'product_name' => $this->name,
            'purchase_price' => $purchasePrice,
            'selling_price' => $sellingPrice,
            'error' => $e->getMessage()
        ]);
        throw $e;
    }
}
public function updatePackagePrices($packageId, $purchasePrice, $sellingPrice = null)
{
    $package = $this->packages()->find($packageId);
    if ($package) {
        $package->update([
            'purchase_price' => $purchasePrice,
            'selling_price' => $sellingPrice ?? $package->selling_price
        ]);
    }
}
  // Add method to check stock availability
  public function hasEnoughStock(int $quantity): bool
  {
      return $this->quantity >= $quantity;
  }

  // Add method to get stock status
  public function getStockStatus(): string
  {
      if ($this->quantity <= $this->min_stock_level) {
          return 'low';
      } elseif ($this->quantity >= $this->max_stock_level) {
          return 'high';
      }
      return 'normal';
  }

        public function needsReorder(): bool
        {
            return $this->quantity <= $this->reorder_point;
        }

        public function barcodes(): HasMany
        {
            return $this->hasMany(ProductBarcode::class);
        }

        public function addBarcode(string $barcode): ProductBarcode
        {
            // Check if barcode already exists across all products
            $existingBarcode = ProductBarcode::where('barcode', $barcode)->first();

            if ($existingBarcode) {
                throw new \Exception('Barcode already exists for another product');
            }

            return $this->barcodes()->create(['barcode' => $barcode]);
        }

        public function removeBarcodeById(int $barcodeId): bool
        {
            return $this->barcodes()->where('id', $barcodeId)->delete() > 0;
        }

        public function removeBarcodeByValue(string $barcode): bool
        {
            return $this->barcodes()->where('barcode', $barcode)->delete() > 0;
        }
    }
