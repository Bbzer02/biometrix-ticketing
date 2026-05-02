<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\TicketPriority;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\View\View;

class TicketPriorityController extends Controller
{
    public function index(): View
    {
        $priorities = TicketPriority::query()->orderBy('sort_order')->orderBy('id')->get();
        return view('admin.priorities.index', compact('priorities'));
    }

    public function create(): View
    {
        return view('admin.priorities.create');
    }

    public function store(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'key' => ['required', 'string', 'max:30', 'unique:ticket_priorities,key'],
            'label' => ['required', 'string', 'max:60'],
            'description' => ['nullable', 'string', 'max:2000'],
            'sort_order' => ['nullable', 'integer', 'min:0'],
            'active' => ['nullable', 'boolean'],
        ]);

        TicketPriority::create(['key' => $data['key'], 'label' => $data['label'], 'description' => $data['description'] ?? null, 'sort_order' => (int)($data['sort_order'] ?? 0), 'active' => (bool)($data['active'] ?? true)]);
        Cache::forget('ticket_priorities_list');
        return redirect()->route('admin.priorities.index')->with('success', 'Priority created.');
    }

    public function edit(TicketPriority $priority): View
    {
        return view('admin.priorities.edit', compact('priority'));
    }

    public function update(Request $request, TicketPriority $priority): RedirectResponse
    {
        $data = $request->validate([
            'key' => ['required', 'string', 'max:30', 'unique:ticket_priorities,key,' . $priority->id],
            'label' => ['required', 'string', 'max:60'],
            'description' => ['nullable', 'string', 'max:2000'],
            'sort_order' => ['nullable', 'integer', 'min:0'],
            'active' => ['nullable', 'boolean'],
        ]);

        $priority->update(['key' => $data['key'], 'label' => $data['label'], 'description' => $data['description'] ?? null, 'sort_order' => (int)($data['sort_order'] ?? 0), 'active' => (bool)($data['active'] ?? false)]);
        Cache::forget('ticket_priorities_list');
        return redirect()->route('admin.priorities.index')->with('success', 'Priority updated.');
    }

    public function destroy(TicketPriority $priority): RedirectResponse
    {
        $priority->delete();
        Cache::forget('ticket_priorities_list');
        return redirect()->route('admin.priorities.index')->with('success', 'Priority deleted.');
    }
}

