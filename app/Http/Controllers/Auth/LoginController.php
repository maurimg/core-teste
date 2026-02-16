<?php
namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Models\TenantUser;

class LoginController extends Controller
{
    public function login(Request $request)
    {
        $credentials = $request->validate([
            'email' => ['required', 'email'],
            'password' => ['required'],
        ]);

        if (! Auth::attempt($credentials)) {
            return response()->json([
                'message' => 'Credenciais inválidas'
            ], 401);
        }

        $request->session()->regenerate();

        $user = Auth::user();

        // Regra atual: primeiro vínculo do usuário
        $tenantLink = TenantUser::where('user_id', $user->id)->first();

        if (! $tenantLink) {
            Auth::logout();
            return response()->json([
                'message' => 'Usuário sem tenant vinculado'
            ], 403);
        }

        $request->session()->put('tenant_id', $tenantLink->tenant_id);

        return response()->json([
            'message' => 'Login realizado com sucesso',
            'tenant_id' => $tenantLink->tenant_id
        ]);
    }
}

