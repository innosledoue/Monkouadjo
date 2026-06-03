<?php

namespace App\Http\Controllers;

use App\Models\Budget;
use App\Models\Debt;
use App\Models\Tontine;
use App\Models\Transaction;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class DashboardController extends Controller
{
    public function __invoke(Request $request): Response
    {
        $user = $request->user();
        $now = Carbon::now();
        $monthStart = $now->copy()->startOfMonth();
        $monthEnd = $now->copy()->endOfMonth();

        $monthTx = Transaction::where('user_id', $user->id)
            ->whereBetween('occurred_at', [$monthStart, $monthEnd])
            ->get();

        $income = (float) $monthTx->where('type', 'income')->sum('amount');
        $expense = (float) $monthTx->where('type', 'expense')->sum('amount');
        $balance = $income - $expense;

        $byCategory = $monthTx->where('type', 'expense')
            ->groupBy('category_id')
            ->map(fn ($g) => [
                'category_id' => $g->first()->category_id,
                'total' => (float) $g->sum('amount'),
            ])
            ->values();

        $categories = \App\Models\Category::forUser($user->id)->get(['id', 'name', 'icon', 'color']);
        $catMap = $categories->keyBy('id');

        $byCategory = $byCategory->map(function ($row) use ($catMap) {
            $cat = $catMap->get($row['category_id']);
            return [
                'category_id' => $row['category_id'],
                'name' => $cat?->name ?? 'Sans catégorie',
                'icon' => $cat?->icon ?? 'circle-dot',
                'color' => $cat?->color ?? '#94a3b8',
                'total' => $row['total'],
            ];
        })->sortByDesc('total')->values();

        // Solde vital (Mode Urgence simplifié) : solde du mois moins budgets fixes restants
        $activeBudgets = Budget::where('user_id', $user->id)->where('is_active', true)->get();
        $fixedRemaining = 0.0;
        foreach ($activeBudgets as $b) {
            $spent = (float) $monthTx->where('category_id', $b->category_id)->where('type', 'expense')->sum('amount');
            $fixedRemaining += max(0, (float) $b->limit_amount - $spent);
        }
        $vitalBalance = $balance - $fixedRemaining;

        $recent = Transaction::where('user_id', $user->id)
            ->with('category:id,name,icon,color')
            ->orderByDesc('occurred_at')
            ->limit(8)
            ->get();

        $pendingDebts = Debt::where('user_id', $user->id)
            ->whereIn('status', ['pending', 'partial'])
            ->orderBy('due_date')
            ->limit(5)
            ->get(['id', 'contact_name', 'type', 'amount', 'due_date', 'status']);

        $debtsSummary = [
            'lent'     => (float) Debt::where('user_id', $user->id)->where('type', 'lent')->whereIn('status', ['pending', 'partial'])->sum('amount'),
            'borrowed' => (float) Debt::where('user_id', $user->id)->where('type', 'borrowed')->whereIn('status', ['pending', 'partial'])->sum('amount'),
        ];

        $activeTontines = Tontine::where('user_id', $user->id)
            ->where('status', 'active')
            ->orderBy('name')
            ->limit(5)
            ->get(['id', 'name', 'contribution', 'members_count', 'current_turn', 'frequency']);

        return Inertia::render('Dashboard', [
            'stats' => [
                'income' => $income,
                'expense' => $expense,
                'balance' => $balance,
                'vital_balance' => $vitalBalance,
                'period_label' => $monthStart->locale('fr')->isoFormat('MMMM YYYY'),
            ],
            'by_category' => $byCategory,
            'recent_transactions' => $recent,
            'pending_debts' => $pendingDebts,
            'debts_summary' => $debtsSummary,
            'active_tontines' => $activeTontines,
        ]);
    }
}
