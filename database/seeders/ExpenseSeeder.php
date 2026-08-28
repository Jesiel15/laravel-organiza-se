<?php

namespace Database\Seeders;

use App\Models\Expense;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

/**
 * Seeder gerado a partir do arquivo expenses.json do projeto original.
 *
 * O JSON original tinha "userId": "" (vazio), então aqui localizamos (ou
 * criamos, se não existir) o usuário pelo e-mail informado no próprio
 * arquivo, e associamos as despesas a ele.
 *
 * Rode com: php artisan db:seed --class=ExpenseSeeder
 */
class ExpenseSeeder extends Seeder
{
    public function run(): void
    {
        $email = 'fariajesiel@gmail.com';

        $user = User::firstOrCreate(
            ['email' => $email],
            [
                'name' => 'Jesiel Faria',
                // Senha aleatória só para o usuário existir — troque depois via /user/password.
                'password' => Hash::make(Str::random(24)),
            ]
        );

        $expenses = [
            [
                'icon' => 'pi pi-chart-bar',
                'color' => '#9DA7D0',
                'nameExpense' => 'Fatura 1',
                'valueExpense' => 150.00,
                'dateExpense' => '2024-06-26',
                'anotation' => 'qualquer texto que eu coloque',
            ],
            [
                'icon' => 'pi pi-chart-bar',
                'color' => '#9DA7D0',
                'nameExpense' => 'Fatura 2',
                'valueExpense' => 200.00,
                'dateExpense' => '2024-06-26',
                'anotation' => 'qualquer texto que eu coloque',
            ],
        ];

        foreach ($expenses as $data) {
            Expense::firstOrCreate(
                [
                    'user_id' => $user->id,
                    'name_expense' => $data['nameExpense'],
                    'date_expense' => $data['dateExpense'],
                    'value_expense' => $data['valueExpense'],
                ],
                [
                    'icon' => $data['icon'],
                    'color' => $data['color'],
                    'anotation' => $data['anotation'],
                ]
            );
        }

        $this->command?->info('ExpenseSeeder: '.count($expenses)." despesas verificadas/criadas para {$email}.");
    }
}
