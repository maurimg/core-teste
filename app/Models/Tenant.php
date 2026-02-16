<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Tenant extends Model
{
    protected $fillable = [
        'uuid',
        'name',
        'slug',
        'status',
    ];

    /**
     * Usuários pertencentes ao tenant
     */
    public function users(): BelongsToMany
    {
        return $this->belongsToMany(
            User::class,
            'tenant_users'
        )->withPivot('role')->withTimestamps();
    }

    /**
     * Vínculos diretos (pivot)
     */
    public function tenantUsers(): HasMany
    {
        return $this->hasMany(TenantUser::class);
    }
}

