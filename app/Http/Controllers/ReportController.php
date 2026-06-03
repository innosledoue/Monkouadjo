<?php

namespace App\Http\Controllers;

use App\Models\Category;
use App\Models\Transaction;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Http\Response as HttpResponse;
use Inertia\Inertia;
use Inertia\Response;

class ReportController extends Controller
{
    public function index(Request $request): Response
    {
        [$from, $to, $periodLabel] = $this->resolvePeriod($request);

        $user = $request->user();

        $txAll = Transaction::where('user_id', $user->id)
            ->whereBetween('occurred_at', [$from, $to])
            ->get(['type', 'amount', 'category_id', 'occurred_at']);

        $income  = (float) $txAll->where('type', 'income')->sum('amount');
        $expense = (float) $txAll->where('type', 'expense')->sum('amount');
        $net     = $income - $expense;
        $savingsRate = $income > 0 ? round(($net / $income) * 100, 1) : 0;

        // Évolution mensuelle sur les 12 derniers mois
        $monthly = $this->buildMonthly($user->id);

        // Répartition par catégorie (dépenses)
        $categories = Category::forUser($user->id)->get(['id', 'name', 'color'])->keyBy('id');
        $byCategory = $txAll->where('type', 'expense')
            ->groupBy('category_id')
            ->map(fn ($g, $cid) => [
                'category_id' => $cid,
                'name'  => $categories->get($cid)?->name ?? 'Sans catégorie',
                'color' => $categories->get($cid)?->color ?? '#94a3b8',
                'total' => (float) $g->sum('amount'),
            ])
            ->sortByDesc('total')
            ->values();

        $byCategory = $byCategory->map(function ($row) use ($expense) {
            $row['pct'] = $expense > 0 ? round(($row['total'] / $expense) * 100, 1) : 0;
            return $row;
        });

        return Inertia::render('Reports/Index', [
            'summary' => [
                'income'       => $income,
                'expense'      => $expense,
                'net'          => $net,
                'savings_rate' => $savingsRate,
                'period_label' => $periodLabel,
            ],
            'monthly'      => $monthly,
            'by_category'  => $byCategory,
            'filters'      => $request->only(['period', 'from', 'to']),
        ]);
    }

    public function exportCsv(Request $request): HttpResponse
    {
        [$from, $to] = $this->resolvePeriod($request);

        $user = $request->user();

        $transactions = Transaction::where('user_id', $user->id)
            ->whereBetween('occurred_at', [$from, $to])
            ->with('category:id,name')
            ->orderByDesc('occurred_at')
            ->get();

        $lines = ["Date,Type,Catégorie,Mode de paiement,Bénéficiaire,Note,Montant (FCFA)"];
        foreach ($transactions as $tx) {
            $typeLabel = $tx->type === 'income' ? 'Revenu' : 'Dépense';
            $lines[] = implode(',', [
                '"' . Carbon::parse($tx->occurred_at)->format('d/m/Y H:i') . '"',
                '"' . $typeLabel . '"',
                '"' . ($tx->category?->name ?? '') . '"',
                '"' . $tx->payment_method . '"',
                '"' . str_replace('"', '""', $tx->beneficiary ?? '') . '"',
                '"' . str_replace('"', '""', $tx->note ?? '') . '"',
                ($tx->type === 'expense' ? '-' : '') . number_format((float)$tx->amount, 0, '.', ''),
            ]);
        }

        $filename = 'transactions_' . $from->format('Ymd') . '_' . $to->format('Ymd') . '.csv';

        return response(implode("\n", $lines), 200, [
            'Content-Type'        => 'text/csv; charset=UTF-8',
            'Content-Disposition' => 'attachment; filename="' . $filename . '"',
        ]);
    }

    private function resolvePeriod(Request $request): array
    {
        $period = $request->input('period', 'current_month');
        $now    = Carbon::now();

        switch ($period) {
            case 'last_3_months':
                $from  = $now->copy()->subMonths(2)->startOfMonth();
                $to    = $now->copy()->endOfMonth();
                $label = 'Derniers 3 mois';
                break;
            case 'last_6_months':
                $from  = $now->copy()->subMonths(5)->startOfMonth();
                $to    = $now->copy()->endOfMonth();
                $label = 'Derniers 6 mois';
                break;
            case 'current_year':
                $from  = $now->copy()->startOfYear();
                $to    = $now->copy()->endOfYear();
                $label = 'Année ' . $now->year;
                break;
            case 'custom':
                $from  = $request->input('from') ? Carbon::parse($request->input('from'))->startOfDay() : $now->copy()->startOfMonth();
                $to    = $request->input('to')   ? Carbon::parse($request->input('to'))->endOfDay()     : $now->copy()->endOfMonth();
                $label = $from->format('d/m/Y') . ' → ' . $to->format('d/m/Y');
                break;
            default: // current_month
                $from  = $now->copy()->startOfMonth();
                $to    = $now->copy()->endOfMonth();
                $label = $now->locale('fr')->isoFormat('MMMM YYYY');
        }

        return [$from, $to, $label];
    }

    private function buildMonthly(int $userId): array
    {
        $months = [];
        $now    = Carbon::now();

        for ($i = 11; $i >= 0; $i--) {
            $m     = $now->copy()->subMonths($i);
            $start = $m->copy()->startOfMonth();
            $end   = $m->copy()->endOfMonth();

            $tx = Transaction::where('user_id', $userId)
                ->whereBetween('occurred_at', [$start, $end])
                ->get(['type', 'amount']);

            $months[] = [
                'label'   => $m->locale('fr')->isoFormat('MMM YY'),
                'income'  => (float) $tx->where('type', 'income')->sum('amount'),
                'expense' => (float) $tx->where('type', 'expense')->sum('amount'),
            ];
        }

        return $months;
    }
}
