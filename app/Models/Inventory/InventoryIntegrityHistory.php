<?php

namespace App\Models\Inventory;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class InventoryIntegrityHistory extends Model
{
    use HasFactory;

    protected $table = 'inventory_integrity_history';

    protected $fillable = [
        'vehicle_id',
        'hash_anterior',
        'hash_novo',
        'mudancas_detectadas'
    ];

    protected $casts = [
        'mudancas_detectadas' => 'array'
    ];

    public function vehicle()
    {
        return $this->belongsTo(InventoryVehicle::class, 'vehicle_id');
    }
}
