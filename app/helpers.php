<?php

use App\Models\Tenant;

if (! function_exists('tenant')) {
    function tenant(): ?Tenant
    {
        return app()->has('currentTenant')
            ? app('currentTenant')
            : null;
    }
}

