<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\File;
use App\Models\User;
use Carbon\Carbon;

class OrganizasePlusUserSeeder extends Seeder
{
    public function run()
    {
        $json = File::get(base_path('organizaseplus.users.json'));
        $data = json_decode($json, true);

        // Achata a estrutura para garantir a leitura de todos os nós de usuários
        $usersList = [];
        $this->extractUsers($data, $usersList);

        foreach ($usersList as $userData) {
            if (empty($userData['email'])) {
                continue;
            }

            // 1. Cria ou obtém o Usuário
            $user = User::create([
                'name'     => $userData['name'] ?? 'Sem Nome',
                'email'    => $userData['email'],
                'password' => $userData['password'] ?? bcrypt('123456'),
            ]);

            // 2. Extrai receitas e despesas de qualquer nível
            $expenses = $userData['expenses'] 
                ?? $userData['expensesRevenues']['expenses'] 
                ?? [];

            $revenues = $userData['revenues'] 
                ?? $userData['expensesRevenues']['revenues'] 
                ?? [];

            // 3. Inserção das Receitas
            foreach ($revenues as $revenue) {
                $user->revenues()->create([
                    'name_revenue'  => $revenue['nameRevenue'] ?? null,
                    'value_revenue' => $revenue['valueRevenue'] ?? 0,
                    'color'         => $revenue['color'] ?? null,
                    'icon'          => $revenue['icon'] ?? null,
                    'anotation'     => $revenue['anotation'] ?? null,
                    'date_revenue'  => isset($revenue['dateRevenue']['$date']) 
                        ? Carbon::parse($revenue['dateRevenue']['$date'])->format('Y-m-d H:i:s') 
                        : null,
                ]);
            }

            // 4. Inserção das Despesas
            foreach ($expenses as $expense) {
                $user->expenses()->create([
                    'name_expense'  => $expense['nameExpense'] ?? null,
                    'value_expense' => $expense['valueExpense'] ?? 0,
                    'color'         => $expense['color'] ?? null,
                    'icon'          => $expense['icon'] ?? null,
                    'anotation'     => $expense['anotation'] ?? null,
                    'is_paid'       => $expense['isPaid'] ?? false,
                    'date_expense'  => isset($expense['dateExpense']['$date']) 
                        ? Carbon::parse($expense['dateExpense']['$date'])->format('Y-m-d H:i:s') 
                        : null,
                ]);
            }
        }
    }

    /**
     * Função auxiliar para varrer o JSON e mapear qualquer objeto que seja um Usuário
     */
    private function extractUsers($item, &$usersList)
    {
        if (!is_array($item)) {
            return;
        }

        // Se o item contém a chave 'email', ele é um nó de usuário válido
        if (isset($item['email'])) {
            $usersList[] = $item;
        } else {
            foreach ($item as $subItem) {
                $this->extractUsers($subItem, $usersList);
            }
        }
    }
}