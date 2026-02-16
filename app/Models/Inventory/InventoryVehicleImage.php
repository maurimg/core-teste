<?php

namespace App\Models\Inventory;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class InventoryVehicleImage extends Model
{
    use HasFactory;

    protected $table = 'inventory_vehicle_images';

    protected $fillable = [
        'vehicle_id',
        'url_original',
        'url_local',
        'ordem'
    ];

    public function vehicle()
    {
        return $this->belongsTo(InventoryVehicle::class, 'vehicle_id');
    }
}
