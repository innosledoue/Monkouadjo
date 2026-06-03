<?php

namespace App\Http\Controllers;

use App\Models\Tontine;
use Carbon\Carbon;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class TontineController extends Controller
{
    public function index(Request $request): Response
    {
        $user = $request->user();

        $tontines = Tontine::query()
            ->where('user_id', $user->id)
            ->when($request->input('status'), fn ($q, $s) => $q->where('status', $s))
            ->orderByRaw("CASE status WHEN 'active' THEN 1 WHEN 'paused' THEN 2 WHEN 'completed' THEN 3 ELSE 4 END")
            ->orderBy('name')
            ->get()
            ->map(function (Tontine $t) {
                // Montant total du cycle = contribution × nombre de membres
                $t->cycle_total = (float) $t->contribution * $t->members_count;
                // Progression : tour actuel / total de membres
                $t->progress_pct = $t->members_count > 0
                    ? min(100, round(($t->current_turn / $t->members_count) * 100))
                    : 0;
                return $t;
            });

        return Inertia::render('Tontines/Index', [
            'tontines' => $tontines,
            'filters'  => $request->only(['status']),
        ]);
    }

    public function create(): Response
    {
        return Inertia::render('Tontines/Form', ['tontine' => null]);
    }

    public function store(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'name'          => ['required', 'string', 'max:255'],
            'members_count' => ['required', 'integer', 'min:2', 'max:200'],
            'contribution'  => ['required', 'numeric', 'min:1'],
            'frequency'     => ['required', 'in:weekly,monthly'],
            'current_turn'  => ['required', 'integer', 'min:1'],
            'start_date'    => ['nullable', 'date'],
            'status'        => ['required', 'in:active,paused,completed'],
            'note'          => ['nullable', 'string', 'max:500'],
        ]);

        $request->user()->tontines()->create($data);

        return redirect()->route('tontines.index')->with('success', 'Tontine créée.');
    }

    public function edit(Request $request, Tontine $tontine): Response
    {
        abort_if($tontine->user_id !== $request->user()->id, 403);

        return Inertia::render('Tontines/Form', ['tontine' => $tontine]);
    }

    public function update(Request $request, Tontine $tontine): RedirectResponse
    {
        abort_if($tontine->user_id !== $request->user()->id, 403);

        $data = $request->validate([
            'name'          => ['required', 'string', 'max:255'],
            'members_count' => ['required', 'integer', 'min:2', 'max:200'],
            'contribution'  => ['required', 'numeric', 'min:1'],
            'frequency'     => ['required', 'in:weekly,monthly'],
            'current_turn'  => ['required', 'integer', 'min:1'],
            'start_date'    => ['nullable', 'date'],
            'status'        => ['required', 'in:active,paused,completed'],
            'note'          => ['nullable', 'string', 'max:500'],
        ]);

        $tontine->update($data);

        return redirect()->route('tontines.index')->with('success', 'Tontine mise à jour.');
    }

    public function destroy(Request $request, Tontine $tontine): RedirectResponse
    {
        abort_if($tontine->user_id !== $request->user()->id, 403);
        $tontine->delete();

        return redirect()->route('tontines.index')->with('success', 'Tontine supprimée.');
    }
}
