<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Services\JwtService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;

class AuthController extends Controller
{
    public function __construct(protected JwtService $jwt)
    {
    }

    /**
     * POST /register
     */
    public function register(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'name' => 'required|string',
            'email' => 'required|email',
            'password' => [
                'required',
                'string',
                function ($attribute, $value, $fail) {
                    // Conta a quantidade de letras e números
                    preg_match_all('/[a-zA-Z]/', $value, $letters);
                    preg_match_all('/[0-9]/', $value, $numbers);

                    $lettersCount = count($letters[0]);
                    $numbersCount = count($numbers[0]);

                    if ($lettersCount < 4 || $numbersCount < 2) {
                        $fail('A senha deve conter no mínimo 4 letras e 2 números.');
                    }
                },
            ],
        ]);

        if ($validator->fails()) {
            return response()->json([
                'msg' => $validator->errors()->first('password') ?? 'Dados inválidos.'
            ], 400);
        }

        $data = $validator->validated();

        if (User::where('email', $data['email'])->exists()) {
            return response()->json(['msg' => 'Email já registrado'], 400);
        }

        $user = User::create([
            'name' => $data['name'],
            'email' => $data['email'],
            'password' => Hash::make($data['password']),
        ]);

        $token = $this->jwt->generate($user);

        return response()->json([
            'msg' => 'Usuário criado com sucesso',
            'token' => $token,
            'user' => [
                'id' => $user->id,
                'name' => $user->name,
                'email' => $user->email,
                'is_admin' => (bool) $user->is_admin,
            ],
        ], 201);
    }

    /**
     * POST /login
     */
    public function login(Request $request)
    {
        $email = $request->input('email');
        $password = $request->input('password');

        if (!$email || !$password) {
            return response()->json(['msg' => 'email e password são obrigatórios'], 400);
        }

        $user = User::where('email', $email)->first();

        if (!$user) {
            return response()->json(['msg' => 'Usuário não encontrado'], 400);
        }

        if (!Hash::check($password, $user->password)) {
            return response()->json(['msg' => 'Senha incorreta'], 400);
        }

        $token = $this->jwt->generate($user);

        return response()->json([
            'token' => $token,
            'user' => [
                'id' => $user->id,
                'name' => $user->name,
                'email' => $user->email,
                'is_admin' => (bool) $user->is_admin,
            ],
        ]);
    }

    /**
     * PUT /user/password
     */
    public function updatePassword(Request $request)
    {
        $authUser = $request->attributes->get('authUser');
        $user = User::find($authUser->id);

        if (!$user) {
            return response()->json(['msg' => 'Usuário não encontrado.'], 404);
        }

        $password = $request->input('password');
        $newPassword = $request->input('newPassword');

        if (!$password) {
            return response()->json(['msg' => 'A senha atual é obrigatória para qualquer alteração.'], 400);
        }

        if (!$newPassword) {
            return response()->json(['msg' => 'A nova senha é obrigatória.'], 400);
        }

        if (!Hash::check($password, $user->password)) {
            return response()->json(['msg' => 'Senha atual incorreta.'], 401);
        }

        if (Hash::check($newPassword, $user->password)) {
            return response()->json(['msg' => 'A nova senha deve ser diferente da senha atual.'], 400);
        }

        $user->password = Hash::make($newPassword);
        $user->save();

        return response()->json([
            'msg' => 'Senha atualizada com sucesso',
            'user' => [
                'id' => $user->id,
                'name' => $user->name,
                'email' => $user->email,
                'is_admin' => (bool) $user->is_admin,
            ],
        ]);
    }

    /**
     * PATCH /user/emailname
     */
    public function updateProfile(Request $request)
    {
        $authUser = $request->attributes->get('authUser');
        $user = User::find($authUser->id);

        if (!$user) {
            return response()->json(['msg' => 'Usuário não encontrado.'], 404);
        }

        $name = $request->input('name');
        $email = $request->input('email');
        $updated = false;

        if ($name && $name !== $user->name) {
            $user->name = $name;
            $updated = true;
        }

        if ($email && $email !== $user->email) {
            if (User::where('email', $email)->where('id', '!=', $user->id)->exists()) {
                return response()->json(['msg' => 'Email já em uso.'], 400);
            }
            $user->email = $email;
            $updated = true;
        }

        if (!$updated) {
            return response()->json(['msg' => 'Nada para atualizar.'], 400);
        }

        $user->save();

        $newToken = $this->jwt->generate($user, 8 * 60 * 60); // 8h, igual ao original

        return response()->json([
            'msg' => 'Perfil atualizado com sucesso',
            'user' => [
                'id' => $user->id,
                'name' => $user->name,
                'email' => $user->email,
                'is_admin' => (bool) $user->is_admin,
            ],
            'token' => $newToken,
        ]);
    }
}
