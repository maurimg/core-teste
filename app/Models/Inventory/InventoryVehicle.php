<?php

namespace App\Models\Inventory;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class InventoryVehicle extends Model
{
    use HasFactory;

    protected $table = 'inventory_vehicles';

    protected $fillable = [
        'tenant_id',
        'original_id',
        'url_original',
        'marca',
        'modelo',
        'versao',
        'ano',
        'preco',
        'km',
        'combustivel',
        'cambio',
        'descricao_raw',
        'hash_integridade',
        'raw_payload',
        'last_sync_at'
    ];

    protected $casts = [
        'preco' => 'decimal:2',
        'km' => 'integer',
        'raw_payload' => 'array',
        'last_sync_at' => 'datetime'
    ];

    public function images()
    {
        return $this->hasMany(InventoryVehicleImage::class, 'vehicle_id');
    }

    public function optionals()
    {
        return $this->hasMany(InventoryVehicleOptional::class, 'vehicle_id');
    }

    public function integrityHistory()
    {
        return $this->hasMany(InventoryIntegrityHistory::class, 'vehicle_id');
    }

    public function warnings()
    {
        return $this->hasMany(InventoryInternalWarning::class, 'vehicle_id');
    }

    public function tenant()
    {
        return $this->belongsTo(\App\Models\Tenant::class, 'tenant_id');
    }
}
