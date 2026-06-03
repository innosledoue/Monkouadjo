<?php

namespace App\Http\Controllers;

use App\Http\Requests\TransactionRequest;
use App\Models\Category;
use App\Models\Transaction;
use Illuminate\Http\Request;
use Illuminate\Http\RedirectResponse;
use Inertia\Inertia;
use Inertia\Response;

class TransactionController extends Controller
{
    public function index(Request $request): Response
    {
        $user = $request->user();

        $transactions = Transaction::query()
            ->where('user_id', $user->id)
            ->with('category:id,name,icon,color,type')
            ->when($request->input('type'), fn ($q, $type) => $q->where('type', $type))
            ->when($request->input('category_id'), fn ($q, $cid) => $q->where('category_id', $cid))
            ->when($request->input('from'), fn ($q, $d) => $q->whereDate('occurred_at', '>=', $d))
            ->when($request->input('to'), fn ($q, $d) => $q->whereDate('occurred_at', '<=', $d))
            ->orderByDesc('occurred_at')
            ->orderByDesc('id')
            ->paginate(20)
            ->withQueryString();

        return Inertia::render('Transactions/Index', [
            'transactions' => $transactions,
            'categories' => Category::forUser($user->id)->orderBy('name')->get(['id', 'name', 'icon', 'color', 'type']),
            'filters' => $request->only(['type', 'category_id', 'from', 'to']),
        ]);
    }

    public function create(Request $request): Response
    {
        return Inertia::render('Transactions/Form', [
            'transaction' => null,
            'categories' => Category::forUser($request->user()->id)->orderBy('name')->get(['id', 'name', 'icon', 'color', 'type']),
        ]);
    }

    public function store(TransactionRequest $request): RedirectResponse
    {
        $data = $request->validated();

        // Déduplication offline : si un UUID est fourni et existe déjà, on ne crée pas de doublon
        if (!empty($data['uuid'])) {
            $existing = Transaction::where('user_id', $request->user()->id)
                ->where('uuid', $data['uuid'])
                ->first();
            if ($existing) {
                return redirect()->route('transactions.index')->with('success', 'Transaction déjà synchronisée.');
            }
        }

        $request->user()->transactions()->create($data);

        return redirect()->route('transactions.index')->with('success', 'Transaction enregistrée.');
    }

    public function edit(Request $request, Transaction $transaction): Response
    {
        abort_if($transaction->user_id !== $request->user()->id, 403);

        return Inertia::render('Transactions/Form', [
            'transaction' => $transaction,
            'categories' => Category::forUser($request->user()->id)->orderBy('name')->get(['id', 'name', 'icon', 'color', 'type']),
        ]);
    }

    public function update(TransactionRequest $request, Transaction $transaction): RedirectResponse
    {
        abort_if($transaction->user_id !== $request->user()->id, 403);
        $transaction->update($request->validated());

        return redirect()->route('transactions.index')->with('success', 'Transaction mise à jour.');
    }

    public function destroy(Request $request, Transaction $transaction): RedirectResponse
    {
        abort_if($transaction->user_id !== $request->user()->id, 403);
        $transaction->delete();

        return redirect()->route('transactions.index')->with('success', 'Transaction supprimée.');
    }
}
