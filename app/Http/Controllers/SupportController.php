<?php

namespace App\Http\Controllers;

use App\Mail\SupportMail;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Mail;

class SupportController extends Controller
{
    /**
     * POST /support/email
     * Igual ao original, mas o rate-limit de 2h usa o Cache do Laravel
     * (sobrevive a reinícios do servidor, diferente do Map em memória do Node).
     */
    public function send(Request $request)
    {
        $authUser = $request->attributes->get('authUser');

        $name = $request->input('name');
        $email = $request->input('email');
        $message = $request->input('message');

        if (!$name || !$email || !$message) {
            return response()->json(['msg' => 'Nome, e-mail e mensagem são obrigatórios.'], 400);
        }

        if ($authUser->email !== $email) {
            return response()->json(['msg' => 'E-mail não corresponde ao usuário autenticado.'], 403);
        }

        $cacheKey = "support_email_last_sent:{$authUser->id}";
        $lastSent = Cache::get($cacheKey);

        if ($lastSent) {
            $unlockAt = $lastSent->copy()->addHours(2);
            $remaining = now()->diffInSeconds($unlockAt, false);

            if ($remaining > 0) {
                $remainingHours = intdiv($remaining, 3600);
                $remainingMinutes = (int) ceil(($remaining % 3600) / 60);

                $timeMessage = '';
                if ($remainingHours > 0) {
                    $timeMessage .= "{$remainingHours} hora" . ($remainingHours > 1 ? 's' : '');
                    if ($remainingMinutes > 0) $timeMessage .= ' e ';
                }
                if ($remainingMinutes > 0) {
                    $timeMessage .= "{$remainingMinutes} minuto" . ($remainingMinutes > 1 ? 's' : '');
                }

                return response()->json([
                    'msg' => "Você já enviou uma mensagem recentemente. Tente novamente em {$timeMessage}.",
                ], 429);
            }
        }

        Mail::to(config('organizase.mail_support_to'))->send(
            new SupportMail($name, $email, $authUser->name, $authUser->email, $message)
        );

        Cache::put($cacheKey, now(), now()->addHours(2));

        return response()->json(['msg' => 'Mensagem enviada com sucesso!']);
    }
}
