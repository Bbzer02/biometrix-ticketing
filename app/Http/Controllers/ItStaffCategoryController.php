<?php

namespace App\Http\Controllers;

use App\Models\TicketCategory;
use App\Models\TicketPriority;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Str;

class ItStaffCategoryController extends Controller
{
    private function authorise(): void
    {
        $user = Auth::user();
        if (! $user || (! $user->isItStaff() && ! $user->isAdmin())) {
            abort(403);
        }
    }

    public function index(): JsonResponse
    {
        $this->authorise();
        return response()->json(
            TicketCategory::orderBy('name')->get(['id', 'name', 'slug', 'description'])
        );
    }

    private function bustCache(): void
    {
        Cache::forget('ticket_categories_list');
        Cache::forget('ticket_priorities_list');
    }

    public function store(Request $request): JsonResponse
    {
        $this->authorise();
        $data = $request->validate([
            'name'        => ['required', 'string', 'max:255'],
            'description' => ['nullable', 'string', 'max:2000'],
        ]);

        $name = trim((string) $data['name']);
        $exists = TicketCategory::query()
            ->whereRaw('LOWER(name) = ?', [Str::lower($name)])
            ->exists();
        if ($exists) {
            return response()->json(['message' => 'Category already exists. You already put it like that.'], 422);
        }

        $slug = Str::slug($name);
        $cat = TicketCategory::create(['name' => $data['name'], 'slug' => $slug, 'description' => $data['description'] ?? null]);
        $this->bustCache();
        return response()->json($cat, 201);
    }

    public function update(Request $request, TicketCategory $category): JsonResponse
    {
        $this->authorise();
        $data = $request->validate([
            'name'        => ['required', 'string', 'max:255'],
            'description' => ['nullable', 'string', 'max:2000'],
        ]);

        $name = trim((string) $data['name']);
        $exists = TicketCategory::query()
            ->where('id', '!=', $category->id)
            ->whereRaw('LOWER(name) = ?', [Str::lower($name)])
            ->exists();
        if ($exists) {
            return response()->json(['message' => 'Category already exists. You already put it like that.'], 422);
        }

        $slug = Str::slug($name);
        $category->update(['name' => $name, 'slug' => $slug, 'description' => $data['description'] ?? null]);
        $this->bustCache();
        return response()->json($category->fresh());
    }

    public function destroy(TicketCategory $category): JsonResponse
    {
        $this->authorise();
        $category->delete();
        $this->bustCache();
        return response()->json(['ok' => true]);
    }

    // ── Priorities ────────────────────────────────────────────────────────────

    public function priorityIndex(): JsonResponse
    {
        $this->authorise();
        return response()->json(
            TicketPriority::orderBy('sort_order')->orderBy('id')
                ->get(['id', 'key', 'label', 'sort_order', 'active'])
        );
    }

    public function priorityStore(Request $request): JsonResponse
    {
        $this->authorise();
        $data = $request->validate([
            'label'      => ['required', 'string', 'max:60'],
            'sort_order' => ['nullable', 'integer', 'min:0'],
            'active'     => ['nullable', 'boolean'],
        ]);
        $key = Str::slug($data['label'], '_'); $base = $key; $i = 1;
        while (TicketPriority::where('key', $key)->exists()) { $key = $base . '_' . $i++; }
        $p = TicketPriority::create(['key' => $key, 'label' => $data['label'], 'sort_order' => (int)($data['sort_order'] ?? 0), 'active' => (bool)($data['active'] ?? true)]);
        $this->bustCache();
        return response()->json($p, 201);
    }

    public function priorityUpdate(Request $request, TicketPriority $priority): JsonResponse
    {
        $this->authorise();
        $data = $request->validate([
            'label'      => ['required', 'string', 'max:60'],
            'sort_order' => ['nullable', 'integer', 'min:0'],
            'active'     => ['nullable', 'boolean'],
        ]);
        $priority->update(['label' => $data['label'], 'sort_order' => (int)($data['sort_order'] ?? 0), 'active' => isset($data['active']) ? (bool)$data['active'] : $priority->active]);
        $this->bustCache();
        return response()->json($priority->fresh());
    }

    public function priorityDestroy(TicketPriority $priority): JsonResponse
    {
        $this->authorise();
        $priority->delete();
        $this->bustCache();
        return response()->json(['ok' => true]);
    }
}
