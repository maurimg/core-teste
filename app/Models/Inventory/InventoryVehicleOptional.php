<?php

namespace App\Models\Inventory;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class InventoryVehicleOptional extends Model
{
    use HasFactory;

    protected $table = 'inventory_vehicle_optionals';

    protected $fillable = [
        'vehicle_id',
        'nome'
    ];

    public function vehicle()
    {
        return $this->belongsTo(InventoryVehicle::class, 'vehicle_id');
    }
}
