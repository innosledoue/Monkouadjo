<?php

namespace App\Http\Controllers;

use App\Models\Category;
use App\Models\Transaction;
use App\Services\Ai\IvorianSmsParser;
use Carbon\Carbon;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class SmsImportController extends Controller
{
    public function index(): Response
    {
        return Inertia::render('Sms/Import');
    }

    public function preview(Request $request, IvorianSmsParser $parser): JsonResponse
    {
        $data = $request->validate([
            'sms' => ['required', 'string', 'max:20000'],
        ]);

        // Sépare en messages individuels — double saut de ligne OU "---"
        $messages = preg_split('/\n\s*\n|\n?---\n?/u', trim($data['sms']));
        $messages = array_values(array_filter(array_map('trim', $messages)));

        $parsed = array_map(fn ($sms) => $parser->parse($sms), $messages);

        return response()->json([
            'count' => count($parsed),
            'items' => $parsed,
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $request->validate([
            'items' => ['required', 'array', 'min:1'],
            'items.*.amount' => ['required', 'numeric', 'min:0.01'],
            'items.*.type' => ['required', 'in:expense,income'],
            'items.*.payment_method' => ['required', 'in:cash,om,momo,wave,card,bank,other'],
            'items.*.occurred_at' => ['nullable', 'date'],
            'items.*.beneficiary' => ['nullable', 'string', 'max:255'],
            'items.*.note' => ['nullable', 'string', 'max:500'],
            'items.*.category_id' => ['nullable', 'exists:categories,id'],
        ]);

        $user = $request->user();
        $created = 0;

        foreach ($request->input('items', []) as $item) {
            $user->transactions()->create([
                'type' => $item['type'],
                'amount' => $item['amount'],
                'category_id' => $item['category_id'] ?? null,
                'occurred_at' => $item['occurred_at'] ?? Carbon::now(),
                'payment_method' => $item['payment_method'],
                'beneficiary' => $item['beneficiary'] ?? null,
                'note' => $item['note'] ?? null,
                'source' => 'sms',
            ]);
            $created++;
        }

        return redirect()
            ->route('transactions.index')
            ->with('success', "$created transaction(s) importée(s) depuis SMS.");
    }
}
