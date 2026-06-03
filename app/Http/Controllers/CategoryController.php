<?php

namespace App\Http\Controllers;

use App\Models\Category;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class CategoryController extends Controller
{
    public function index(Request $request): Response
    {
        $userId = $request->user()->id;

        $categories = Category::query()
            ->where(fn ($q) => $q->whereNull('user_id')->orWhere('user_id', $userId))
            ->orderBy('type')
            ->orderBy('name')
            ->get(['id', 'name', 'icon', 'color', 'type', 'is_default', 'user_id']);

        return Inertia::render('Categories/Index', [
            'categories' => $categories,
        ]);
    }

    public function create(): Response
    {
        return Inertia::render('Categories/Form', ['category' => null]);
    }

    public function store(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'name'  => ['required', 'string', 'max:100'],
            'icon'  => ['required', 'string', 'max:64'],
            'color' => ['required', 'string', 'regex:/^#[0-9a-fA-F]{6}$/'],
            'type'  => ['required', 'in:expense,income'],
        ]);

        $request->user()->categories()->create($data);

        return redirect()->route('categories.index')->with('success', 'Catégorie créée.');
    }

    public function edit(Request $request, Category $category): Response
    {
        // Les catégories globales (user_id null) ne sont pas modifiables
        abort_if($category->user_id !== $request->user()->id, 403);

        return Inertia::render('Categories/Form', ['category' => $category]);
    }

    public function update(Request $request, Category $category): RedirectResponse
    {
        abort_if($category->user_id !== $request->user()->id, 403);

        $data = $request->validate([
            'name'  => ['required', 'string', 'max:100'],
            'icon'  => ['required', 'string', 'max:64'],
            'color' => ['required', 'string', 'regex:/^#[0-9a-fA-F]{6}$/'],
            'type'  => ['required', 'in:expense,income'],
        ]);

        $category->update($data);

        return redirect()->route('categories.index')->with('success', 'Catégorie mise à jour.');
    }

    public function destroy(Request $request, Category $category): RedirectResponse
    {
        abort_if($category->user_id !== $request->user()->id, 403);

        // Détacher les transactions liées avant suppression
        $category->transactions()->update(['category_id' => null]);
        $category->budgets()->delete();
        $category->delete();

        return redirect()->route('categories.index')->with('success', 'Catégorie supprimée.');
    }
}
