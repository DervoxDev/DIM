<?php

    namespace App\Models;

    use Illuminate\Database\Eloquent\Model;
    use Illuminate\Database\Eloquent\Factories\HasFactory;
    use Illuminate\Database\Eloquent\SoftDeletes;
    use Illuminate\Database\Eloquent\Relations\BelongsTo;
    use Illuminate\Database\Eloquent\Relations\HasMany;
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

        // Helper methods
        public function updateStock(int $quantity, string $operation = 'add'): void
        {
            if ($operation === 'add') {
                $this->increment('quantity', $quantity);
            } else {
                $this->decrement('quantity', $quantity);
            }
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
