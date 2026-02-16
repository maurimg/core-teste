<?php

namespace App\Models\Inventory;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class InventoryInternalWarning extends Model
{
    use HasFactory;

    protected $table = 'inventory_internal_warnings';

    protected $fillable = [
        'vehicle_id',
        'tipo',
        'mensagem',
        'status'
    ];

    public function vehicle()
    {
        return $this->belongsTo(InventoryVehicle::class, 'vehicle_id');
    }
}
