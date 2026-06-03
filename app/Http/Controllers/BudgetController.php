<?php

namespace App\Http\Controllers;

use App\Http\Requests\BudgetRequest;
use App\Models\Budget;
use App\Models\Category;
use App\Models\Transaction;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Http\RedirectResponse;
use Inertia\Inertia;
use Inertia\Response;

class BudgetController extends Controller
{
    public function index(Request $request): Response
    {
        $user = $request->user();
        $now = Carbon::now();

        $budgets = Budget::query()
            ->where('user_id', $user->id)
            ->with('category:id,name,icon,color,type')
            ->orderByDesc('is_active')
            ->get()
            ->map(function (Budget $b) use ($user, $now) {
                $start = $b->period === 'weekly' ? $now->copy()->startOfWeek() : $now->copy()->startOfMonth();
                $end = $b->period === 'weekly' ? $now->copy()->endOfWeek() : $now->copy()->endOfMonth();

                $spent = Transaction::where('user_id', $user->id)
                    ->where('category_id', $b->category_id)
                    ->where('type', 'expense')
                    ->whereBetween('occurred_at', [$start, $end])
                    ->sum('amount');

                $b->spent = (float) $spent;
                $b->remaining = max(0, (float) $b->limit_amount - (float) $spent);
                $b->percent = $b->limit_amount > 0 ? min(100, round(($spent / $b->limit_amount) * 100, 1)) : 0;
                $b->is_alerting = $b->percent >= $b->alert_threshold;

                return $b;
            });

        return Inertia::render('Budgets/Index', [
            'budgets' => $budgets,
            'categories' => Category::forUser($user->id)->where('type', 'expense')->orderBy('name')->get(['id', 'name', 'icon', 'color']),
        ]);
    }

    public function create(Request $request): Response
    {
        return Inertia::render('Budgets/Form', [
            'budget' => null,
            'categories' => Category::forUser($request->user()->id)->where('type', 'expense')->orderBy('name')->get(['id', 'name', 'icon', 'color']),
        ]);
    }

    public function store(BudgetRequest $request): RedirectResponse
    {
        $request->user()->budgets()->create($request->validated());

        return redirect()->route('budgets.index')->with('success', 'Budget créé.');
    }

    public function edit(Request $request, Budget $budget): Response
    {
        abort_if($budget->user_id !== $request->user()->id, 403);

        return Inertia::render('Budgets/Form', [
            'budget' => $budget,
            'categories' => Category::forUser($request->user()->id)->where('type', 'expense')->orderBy('name')->get(['id', 'name', 'icon', 'color']),
        ]);
    }

    public function update(BudgetRequest $request, Budget $budget): RedirectResponse
    {
        abort_if($budget->user_id !== $request->user()->id, 403);
        $budget->update($request->validated());

        return redirect()->route('budgets.index')->with('success', 'Budget mis à jour.');
    }

    public function destroy(Request $request, Budget $budget): RedirectResponse
    {
        abort_if($budget->user_id !== $request->user()->id, 403);
        $budget->delete();

        return redirect()->route('budgets.index')->with('success', 'Budget supprimé.');
    }
}
