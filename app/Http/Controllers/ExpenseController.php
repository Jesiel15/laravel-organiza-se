<?php

namespace App\Http\Controllers;

use App\Http\Resources\ExpenseResource;
use App\Models\Expense;
use Illuminate\Http\Request;

class ExpenseController extends Controller
{
    /**
     * GET /expenses
     * Todas as despesas do usuário, ordenadas por data desc.
     */
    public function index(Request $request)
    {
        $authUser = $request->attributes->get('authUser');

        $expenses = Expense::where('user_id', $authUser->id)
            ->orderByDesc('date_expense')
            ->get();

        return ExpenseResource::collection($expenses);
    }

    /**
     * GET /expenses/{monthYear}
     * monthYear no formato MMYYYY (ex: 082025), igual ao original.
     */
    public function indexByMonth(Request $request, string $monthYear)
    {
        $authUser = $request->attributes->get('authUser');

        $expenses = Expense::where('user_id', $authUser->id)
            ->forMonthYear($monthYear)
            ->orderByDesc('date_expense')
            ->get();

        return ExpenseResource::collection($expenses);
    }

    /**
     * GET /expenses/{monthYear}/{expenseId}
     */
    public function show(Request $request, string $monthYear, int $expenseId)
    {
        $authUser = $request->attributes->get('authUser');

        $expense = Expense::where('user_id', $authUser->id)
            ->where('id', $expenseId)
            ->first();

        if (!$expense) {
            return response()->json(['msg' => 'Despesa não encontrada.'], 404);
        }

        return new ExpenseResource($expense);
    }

    /**
     * POST /expenses
     */
    public function store(Request $request)
    {
        $authUser = $request->attributes->get('authUser');

        $nameExpense = $request->input('nameExpense');
        $valueExpense = $request->input('valueExpense');
        $dateExpense = $request->input('dateExpense');

        if (!$nameExpense || $valueExpense === null || !$dateExpense) {
            return response()->json([
                'msg' => 'nameExpense, valueExpense e dateExpense são obrigatórios',
            ], 400);
        }

        $date = date_create($dateExpense);
        if (!$date) {
            return response()->json(['msg' => 'dateExpense inválida'], 400);
        }

        $expense = Expense::create([
            'user_id' => $authUser->id,
            'icon' => $request->input('icon', 'pi pi-receipt'),
            'color' => $request->input('color', '#2881e4'),
            'name_expense' => $nameExpense,
            'value_expense' => $valueExpense,
            'date_expense' => $date,
            'anotation' => $request->input('anotation'),
        ]);

        return response()->json([
            'msg' => 'Despesa criada com sucesso',
            'expense' => new ExpenseResource($expense),
        ], 201);
    }

    /**
     * PUT /expenses/{monthYear}/{expenseId}
     * Se a data mudar, o próprio filtro por mês passa a refletir isso automaticamente
     * (não precisamos "mover" o registro como no Mongoose, pois não há mais chave de mês).
     */
    public function update(Request $request, string $monthYear, int $expenseId)
    {
        $authUser = $request->attributes->get('authUser');

        $expense = Expense::where('user_id', $authUser->id)
            ->where('id', $expenseId)
            ->first();

        if (!$expense) {
            return response()->json(['msg' => 'Despesa não encontrada.'], 404);
        }

        $updates = $request->only([
            'icon', 'color', 'nameExpense', 'valueExpense', 'dateExpense', 'anotation', 'isPaid',
        ]);

        if (isset($updates['dateExpense'])) {
            $date = date_create($updates['dateExpense']);
            if (!$date) {
                return response()->json(['msg' => 'dateExpense inválida'], 400);
            }
            $expense->date_expense = $date;
        }

        if (array_key_exists('icon', $updates)) $expense->icon = $updates['icon'];
        if (array_key_exists('color', $updates)) $expense->color = $updates['color'];
        if (array_key_exists('nameExpense', $updates)) $expense->name_expense = $updates['nameExpense'];
        if (array_key_exists('valueExpense', $updates)) $expense->value_expense = $updates['valueExpense'];
        if (array_key_exists('anotation', $updates)) $expense->anotation = $updates['anotation'];
        if (array_key_exists('isPaid', $updates)) $expense->is_paid = $updates['isPaid'];

        $expense->save();

        return response()->json([
            'msg' => 'Despesa atualizada com sucesso',
            'expense' => new ExpenseResource($expense),
        ]);
    }

    /**
     * PATCH /expenses/{monthYear}/{expenseId}
     * Usado principalmente para alternar isPaid, sem mexer nos outros campos.
     */
    public function patch(Request $request, string $monthYear, int $expenseId)
    {
        $authUser = $request->attributes->get('authUser');

        $expense = Expense::where('user_id', $authUser->id)
            ->where('id', $expenseId)
            ->first();

        if (!$expense) {
            return response()->json(['msg' => 'Despesa não encontrada.'], 404);
        }

        if ($request->has('isPaid')) {
            $expense->is_paid = $request->boolean('isPaid');
        }
        if ($request->has('anotation')) {
            $expense->anotation = $request->input('anotation');
        }

        $expense->save();

        return response()->json([
            'msg' => 'Status da despesa atualizado com sucesso',
            'expense' => new ExpenseResource($expense),
        ]);
    }

    /**
     * DELETE /expenses/{monthYear}/{expenseId}
     */
    public function destroy(Request $request, string $monthYear, int $expenseId)
    {
        $authUser = $request->attributes->get('authUser');

        $expense = Expense::where('user_id', $authUser->id)
            ->where('id', $expenseId)
            ->first();

        if (!$expense) {
            return response()->json(['msg' => 'Despesa não encontrada.'], 404);
        }

        $expense->delete();

        return response()->json(['msg' => 'Despesa excluída com sucesso.']);
    }

    /**
     * POST /expenses/{monthYear}/{expenseId}/replicate
     * Clona a despesa para o mês seguinte, resetando isPaid.
     */
    public function replicate(Request $request, string $monthYear, int $expenseId)
    {
        $authUser = $request->attributes->get('authUser');

        $original = Expense::where('user_id', $authUser->id)
            ->where('id', $expenseId)
            ->first();

        if (!$original) {
            return response()->json(['msg' => 'Despesa original não encontrada.'], 404);
        }

        $nextDate = (clone $original->date_expense)->addMonthNoOverflow();

        $copy = Expense::create([
            'user_id' => $authUser->id,
            'icon' => $original->icon,
            'color' => $original->color,
            'name_expense' => $original->name_expense,
            'value_expense' => $original->value_expense,
            'date_expense' => $nextDate,
            'anotation' => $original->anotation,
            'is_paid' => false,
        ]);

        $nextMonthKey = $nextDate->format('mY');

        return response()->json([
            'msg' => 'Despesa replicada com sucesso',
            'monthKey' => $nextMonthKey,
            'expense' => new ExpenseResource($copy),
        ], 201);
    }
}
