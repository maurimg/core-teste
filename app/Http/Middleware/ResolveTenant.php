<?php
namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;
use App\Models\Tenant;

class ResolveTenant
{
    public function handle(Request $request, Closure $next): Response
    {
        $tenantId = $request->session()->get('tenant_id');

        if (! $tenantId) {
            return response()->json([
                'message' => 'Tenant não definido na sessão'
            ], 401);
        }

        $tenant = Tenant::where('id', $tenantId)
            ->where('status', 'active')
            ->first();

        if (! $tenant) {
            return response()->json([
                'message' => 'Tenant inválido ou inativo'
            ], 403);
        }

        app()->instance('currentTenant', $tenant);

        return $next($request);
    }
}

