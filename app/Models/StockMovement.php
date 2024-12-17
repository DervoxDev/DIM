<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class StockMovement extends Model
{
    protected $fillable = [
        'team_id',
        'product_id',
        'quantity',
        'type',
        'reference',
        'movement_date',
        'notes'
    ];

    protected $casts = [
        'movement_date' => 'datetime',
        'quantity' => 'decimal:2'
    ];

    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class);
    }

    public function team(): BelongsTo
    {
        return $this->belongsTo(Team::class);
    }
}
