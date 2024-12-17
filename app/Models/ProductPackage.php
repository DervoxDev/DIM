<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ProductPackage extends Model
{
    protected $fillable = [
        'name',
        'pieces_per_package',
        'purchase_price',
        'selling_price',
        'barcode',
        'team_id' ,
        'total_pieces',
        'package_id',
        'is_package',
    ];

    public function product()
    {
        return $this->belongsTo(Product::class);
    }
    public function team()
    {
        return $this->belongsTo(Team::class);
    }
}
