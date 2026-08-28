<?php

namespace App\Http\Controllers;

use App\Http\Resources\RevenueResource;
use App\Models\Revenue;
use Illuminate\Http\Request;

class RevenueController extends Controller
{
    /**
     * GET /revenues
     */
    public function index(Request $request)
    {
        $authUser = $request->attributes->get('authUser');

        $revenues = Revenue::where('user_id', $authUser->id)
            ->orderByDesc('date_revenue')
            ->get();

        return RevenueResource::collection($revenues);
    }

    /**
     * GET /revenues/{monthYear}
     */
    public function indexByMonth(Request $request, string $monthYear)
    {
        $authUser = $request->attributes->get('authUser');

        $revenues = Revenue::where('user_id', $authUser->id)
            ->forMonthYear($monthYear)
            ->orderByDesc('date_revenue')
            ->get();

        return RevenueResource::collection($revenues);
    }

    /**
     * GET /revenues/{monthYear}/{revenueId}
     */
    public function show(Request $request, string $monthYear, int $revenueId)
    {
        $authUser = $request->attributes->get('authUser');

        $revenue = Revenue::where('user_id', $authUser->id)
            ->where('id', $revenueId)
            ->first();

        if (!$revenue) {
            return response()->json(['msg' => 'Receita não encontrada.'], 404);
        }

        return new RevenueResource($revenue);
    }

    /**
     * POST /revenues
     */
    public function store(Request $request)
    {
        $authUser = $request->attributes->get('authUser');

        $nameRevenue = $request->input('nameRevenue');
        $valueRevenue = $request->input('valueRevenue');
        $dateRevenue = $request->input('dateRevenue');

        if (!$nameRevenue || $valueRevenue === null || !$dateRevenue) {
            return response()->json([
                'msg' => 'nameRevenue, valueRevenue e dateRevenue são obrigatórios',
            ], 400);
        }

        $date = date_create($dateRevenue);
        if (!$date) {
            return response()->json(['msg' => 'dateRevenue inválida'], 400);
        }

        $revenue = Revenue::create([
            'user_id' => $authUser->id,
            'icon' => $request->input('icon', 'pi pi-money-bill'),
            'color' => $request->input('color', '#2881e4'),
            'name_revenue' => $nameRevenue,
            'value_revenue' => $valueRevenue,
            'date_revenue' => $date,
            'anotation' => $request->input('anotation'),
        ]);

        return response()->json([
            'msg' => 'Receita criada com sucesso',
            'revenue' => new RevenueResource($revenue),
        ], 201);
    }

    /**
     * PUT /revenues/{monthYear}/{revenueId}
     */
    public function update(Request $request, string $monthYear, int $revenueId)
    {
        $authUser = $request->attributes->get('authUser');

        $revenue = Revenue::where('user_id', $authUser->id)
            ->where('id', $revenueId)
            ->first();

        if (!$revenue) {
            return response()->json(['msg' => 'Receita não encontrada.'], 404);
        }

        $updates = $request->only([
            'icon', 'color', 'nameRevenue', 'valueRevenue', 'dateRevenue', 'anotation',
        ]);

        if (isset($updates['dateRevenue'])) {
            $date = date_create($updates['dateRevenue']);
            if (!$date) {
                return response()->json(['msg' => 'dateRevenue inválida'], 400);
            }
            $revenue->date_revenue = $date;
        }

        if (array_key_exists('icon', $updates)) $revenue->icon = $updates['icon'];
        if (array_key_exists('color', $updates)) $revenue->color = $updates['color'];
        if (array_key_exists('nameRevenue', $updates)) $revenue->name_revenue = $updates['nameRevenue'];
        if (array_key_exists('valueRevenue', $updates)) $revenue->value_revenue = $updates['valueRevenue'];
        if (array_key_exists('anotation', $updates)) $revenue->anotation = $updates['anotation'];

        $revenue->save();

        return response()->json([
            'msg' => 'Receita atualizada com sucesso',
            'revenue' => new RevenueResource($revenue),
        ]);
    }

    /**
     * DELETE /revenues/{monthYear}/{revenueId}
     */
    public function destroy(Request $request, string $monthYear, int $revenueId)
    {
        $authUser = $request->attributes->get('authUser');

        $revenue = Revenue::where('user_id', $authUser->id)
            ->where('id', $revenueId)
            ->first();

        if (!$revenue) {
            return response()->json(['msg' => 'Receita não encontrada.'], 404);
        }

        $revenue->delete();

        return response()->json(['msg' => 'Receita excluída com sucesso.']);
    }

    /**
     * POST /revenues/{monthYear}/{revenueId}/replicate
     */
    public function replicate(Request $request, string $monthYear, int $revenueId)
    {
        $authUser = $request->attributes->get('authUser');

        $original = Revenue::where('user_id', $authUser->id)
            ->where('id', $revenueId)
            ->first();

        if (!$original) {
            return response()->json(['msg' => 'Receita original não encontrada.'], 404);
        }

        $nextDate = (clone $original->date_revenue)->addMonthNoOverflow();

        $copy = Revenue::create([
            'user_id' => $authUser->id,
            'icon' => $original->icon,
            'color' => $original->color,
            'name_revenue' => $original->name_revenue,
            'value_revenue' => $original->value_revenue,
            'date_revenue' => $nextDate,
            'anotation' => $original->anotation,
        ]);

        $nextMonthKey = $nextDate->format('mY');

        return response()->json([
            'msg' => 'Receita replicada com sucesso',
            'monthKey' => $nextMonthKey,
            'revenue' => new RevenueResource($copy),
        ], 201);
    }
}
