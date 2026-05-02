<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\TicketCategory;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class TicketCategoryController extends Controller
{
    public function index(): View
    {
        $categories = TicketCategory::query()->orderBy('name')->get();
        return view('admin.categories.index', compact('categories'));
    }

    public function create(): View
    {
        return view('admin.categories.create');
    }

    public function store(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'name' => ['required', 'string', 'max:255', Rule::unique('ticket_categories', 'name')],
            'slug' => ['nullable', 'string', 'max:255', 'unique:ticket_categories,slug'],
            'description' => ['nullable', 'string', 'max:2000'],
        ], [
            'name.unique' => 'Category already exists. You already put it like that.',
        ]);

        $name = trim((string) $data['name']);
        $slug = $data['slug'] ?: Str::slug($name);
        TicketCategory::create(['name' => $name, 'slug' => $slug, 'description' => $data['description'] ?? null]);
        Cache::forget('ticket_categories_list');
        return redirect()->route('admin.categories.index')->with('success', 'Category created.');
    }

    public function edit(TicketCategory $category): View
    {
        return view('admin.categories.edit', compact('category'));
    }

    public function update(Request $request, TicketCategory $category): RedirectResponse
    {
        $data = $request->validate([
            'name' => ['required', 'string', 'max:255', Rule::unique('ticket_categories', 'name')->ignore($category->id)],
            'slug' => ['required', 'string', 'max:255', 'unique:ticket_categories,slug,' . $category->id],
            'description' => ['nullable', 'string', 'max:2000'],
        ], [
            'name.unique' => 'Category already exists. You already put it like that.',
        ]);

        $category->update(['name' => trim((string) $data['name']), 'slug' => $data['slug'], 'description' => $data['description'] ?? null]);
        Cache::forget('ticket_categories_list');
        return redirect()->route('admin.categories.index')->with('success', 'Category updated.');
    }

    public function destroy(TicketCategory $category): RedirectResponse
    {
        $category->delete();
        Cache::forget('ticket_categories_list');
        return redirect()->route('admin.categories.index')->with('success', 'Category deleted.');
    }
}

