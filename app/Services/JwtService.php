<?php

namespace App\Services;

use App\Models\User;
use Firebase\JWT\JWT;
use Firebase\JWT\Key;
use Firebase\JWT\ExpiredException;

/**
 * Serviço simples de JWT, equivalente ao uso de jsonwebtoken no projeto Node original.
 * Requer o pacote "firebase/php-jwt" (composer require firebase/php-jwt).
 */
class JwtService
{
    protected string $secret;
    protected string $algo = 'HS256';

    public function __construct()
    {
        $this->secret = config('organizase.jwt_secret');
    }

    /**
     * Gera um token com payload { id, name, email }, igual ao original.
     * $expiresIn em segundos (padrão 1 dia, igual ao "1d" do Node).
     */
    // TODO: Setar esse valor para 1 MÊS, 86400 = 1 Dia
    public function generate(User $user, int $expiresIn = 86400): string
    {
        $now = time();

        $payload = [
            'id' => $user->id,
            'name' => $user->name,
            'email' => $user->email,
            'iat' => $now,
            'exp' => $now + $expiresIn,
        ];

        return JWT::encode($payload, $this->secret, $this->algo);
    }

    /**
     * Decodifica e valida o token. Retorna o payload (objeto) ou null se inválido/expirado.
     */
    public function decode(string $token): ?object
    {
        try {
            return JWT::decode($token, new Key($this->secret, $this->algo));
        } catch (ExpiredException $e) {
            return null;
        } catch (\Throwable $e) {
            return null;
        }
    }
}
