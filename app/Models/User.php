<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;

class User extends Authenticatable
{
    use HasFactory, Notifiable;

    protected $fillable = [
        'name',
        'email',
        'password',
    ];

    protected $hidden = [
        'password',
        'remember_token',
    ];

    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
        ];
    }

    public function tenants(): BelongsToMany
    {
        return $this->belongsToMany(
            Tenant::class,
            'tenant_users'
        )->withPivot('role')->withTimestamps();
    }

    public function tenantLinks(): HasMany
    {
        return $this->hasMany(TenantUser::class);
    }

    /**
     * Tenant ativo — fonte única: container
     */
    public function currentTenant(): ?Tenant
    {
        return app()->has('currentTenant')
            ? app('currentTenant')
            : null;
    }
}

