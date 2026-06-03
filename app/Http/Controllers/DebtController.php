<?php

namespace App\Http\Controllers;

use App\Models\Debt;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class DebtController extends Controller
{
    public function index(Request $request): Response
    {
        $user = $request->user();

        $debts = Debt::query()
            ->where('user_id', $user->id)
            ->when($request->input('type'), fn ($q, $type) => $q->where('type', $type))
            ->when($request->input('status'), fn ($q, $status) => $q->where('status', $status))
            ->orderByRaw("CASE status WHEN 'pending' THEN 1 WHEN 'partial' THEN 2 WHEN 'settled' THEN 3 ELSE 4 END")
            ->orderBy('due_date')
            ->get();

        $summary = [
            'lent_pending'     => (float) $debts->where('type', 'lent')->whereIn('status', ['pending', 'partial'])->sum('amount'),
            'borrowed_pending' => (float) $debts->where('type', 'borrowed')->whereIn('status', ['pending', 'partial'])->sum('amount'),
        ];

        return Inertia::render('Debts/Index', [
            'debts'   => $debts,
            'summary' => $summary,
            'filters' => $request->only(['type', 'status']),
        ]);
    }

    public function create(): Response
    {
        return Inertia::render('Debts/Form', ['debt' => null]);
    }

    public function store(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'contact_name'  => ['required', 'string', 'max:255'],
            'contact_phone' => ['nullable', 'string', 'max:30'],
            'type'          => ['required', 'in:lent,borrowed'],
            'amount'        => ['required', 'numeric', 'min:1'],
            'due_date'      => ['nullable', 'date'],
            'status'        => ['required', 'in:pending,partial,settled'],
            'note'          => ['nullable', 'string', 'max:500'],
        ]);

        $request->user()->debts()->create($data);

        return redirect()->route('debts.index')->with('success', 'Dette enregistrée.');
    }

    public function edit(Request $request, Debt $debt): Response
    {
        abort_if($debt->user_id !== $request->user()->id, 403);

        return Inertia::render('Debts/Form', ['debt' => $debt]);
    }

    public function update(Request $request, Debt $debt): RedirectResponse
    {
        abort_if($debt->user_id !== $request->user()->id, 403);

        $data = $request->validate([
            'contact_name'  => ['required', 'string', 'max:255'],
            'contact_phone' => ['nullable', 'string', 'max:30'],
            'type'          => ['required', 'in:lent,borrowed'],
            'amount'        => ['required', 'numeric', 'min:1'],
            'due_date'      => ['nullable', 'date'],
            'status'        => ['required', 'in:pending,partial,settled'],
            'note'          => ['nullable', 'string', 'max:500'],
        ]);

        $debt->update($data);

        return redirect()->route('debts.index')->with('success', 'Dette mise à jour.');
    }

    public function destroy(Request $request, Debt $debt): RedirectResponse
    {
        abort_if($debt->user_id !== $request->user()->id, 403);
        $debt->delete();

        return redirect()->route('debts.index')->with('success', 'Dette supprimée.');
    }
}
