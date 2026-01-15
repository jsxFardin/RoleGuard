<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Service;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Inertia\Inertia;
use Inertia\Response;

class ServiceController extends Controller
{
    public function index(Request $request): Response
    {
        $query = Service::with('parent')
            ->orderBy('parent_id')
            ->orderBy('sort_order')
            ->orderBy('title');

        if ($request->filled('search')) {
            $search = $request->string('search');
            $query->where(function ($q) use ($search) {
                $q->where('title', 'like', "%{$search}%")
                    ->orWhere('description', 'like', "%{$search}%");
            });
        }

        if ($request->filled('status')) {
            $query->where('is_active', $request->integer('status'));
        }

        if ($request->filled('parent')) {
            if ($request->string('parent')->toString() === 'none') {
                $query->whereNull('parent_id');
            } else {
                $query->where('parent_id', $request->integer('parent'));
            }
        }

        $services = $query->paginate(15)->withQueryString();

        $parentServices = Service::parents()
            ->orderBy('sort_order')
            ->orderBy('title')
            ->get(['id', 'title']);

        return Inertia::render('admin/Services/Index', [
            'services' => $services,
            'parentServices' => $parentServices,
            'filters' => $request->only(['search', 'status', 'parent']),
        ]);
    }

    public function create(): Response
    {
        $parentServices = Service::parents()
            ->active()
            ->orderBy('sort_order')
            ->get();

        return Inertia::render('admin/Services/Create', [
            'parentServices' => $parentServices,
        ]);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'parent_id' => 'nullable|exists:services,id',
            'title' => 'required|string|max:255',
            'description' => 'nullable|string',
            'content' => 'nullable|string',
            'icon' => 'nullable|string|max:255',
            'image' => 'nullable|string|max:255',
            'meta_title' => 'nullable|string|max:255',
            'meta_description' => 'nullable|string',
            'is_active' => 'boolean',
            'sort_order' => 'nullable|integer',
        ]);

        if (empty($validated['slug'])) {
            $validated['slug'] = Str::slug($validated['title']);
        }

        Service::create($validated);

        return redirect()->route('admin.services.index')
            ->with('success', 'Service created successfully.');
    }

    public function edit(Service $service): Response
    {
        $parentServices = Service::parents()
            ->where('id', '!=', $service->id)
            ->active()
            ->orderBy('sort_order')
            ->get();

        return Inertia::render('admin/Services/Edit', [
            'service' => $service,
            'parentServices' => $parentServices,
        ]);
    }

    public function update(Request $request, Service $service)
    {
        $validated = $request->validate([
            'parent_id' => 'nullable|exists:services,id',
            'slug' => 'required|string|max:255|unique:services,slug,' . $service->id,
            'title' => 'required|string|max:255',
            'description' => 'nullable|string',
            'content' => 'nullable|string',
            'icon' => 'nullable|string|max:255',
            'image' => 'nullable|string|max:255',
            'meta_title' => 'nullable|string|max:255',
            'meta_description' => 'nullable|string',
            'is_active' => 'boolean',
            'sort_order' => 'nullable|integer',
        ]);

        $service->update($validated);

        return redirect()->route('admin.services.index')
            ->with('success', 'Service updated successfully.');
    }

    public function destroy($id)
    {
        $service = Service::findOrFail($id);
        
        // Prevent deletion if service has children
        if ($service->children()->count() > 0) {
            return redirect()->route('admin.services.index')
                ->with('error', 'Cannot delete service with sub-services. Please delete sub-services first.');
        }

        $service->delete();

        return redirect()->route('admin.services.index')
            ->with('success', 'Service deleted successfully.');
    }
}
