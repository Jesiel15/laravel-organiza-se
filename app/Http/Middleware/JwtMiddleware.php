<?php

namespace App\Http\Middleware;

use App\Services\JwtService;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * Equivalente ao middleware "authenticateToken" do index.js original.
 * Lê o header Authorization: Bearer <token>, valida e injeta os dados
 * do usuário autenticado em $request->authUser (objeto com id, name, email).
 */
class JwtMiddleware
{
    public function __construct(protected JwtService $jwt)
    {
    }

    public function handle(Request $request, Closure $next): Response
    {
        $authHeader = $request->header('Authorization');
        $token = null;

        if ($authHeader && str_starts_with($authHeader, 'Bearer ')) {
            $token = substr($authHeader, 7);
        }

        if (!$token) {
            return response()->json(['msg' => 'Token não encontrado'], 401);
        }

        $payload = $this->jwt->decode($token);

        if (!$payload) {
            return response()->json(['msg' => 'Token inválido ou expirado'], 401); // era 403
        }

        $request->attributes->set('authUser', $payload);

        return $next($request);
    }
}
