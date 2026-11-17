<?php

namespace App\Http\Controllers;

use App\Models\AgentCategory;
use App\Models\Agent;
use Illuminate\Http\Request;

class AgentCategoryController extends Controller
{
    public function index()
    {
        $categories = AgentCategory::ordered()->get();
        return view('admin-agent-categories', compact('categories'));
    }

    public function create()
    {
        $category = new AgentCategory();
        return view('admin-agent-category-form', compact('category'));
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
            'sort_order' => 'nullable|integer|min:0',
            'is_active' => 'boolean',
        ]);

        $data['is_active'] = $request->boolean('is_active');
        $data['sort_order'] = $request->integer('sort_order', 0);

        AgentCategory::create($data);

        return redirect()->route('admin.agent-categories.index')
            ->with('success', 'Catégorie créée avec succès.');
    }

    public function show(AgentCategory $category)
    {
        $category->load('agents');
        return view('admin-agent-category-show', compact('category'));
    }

    public function edit(AgentCategory $category)
    {
        return view('admin-agent-category-form', compact('category'));
    }

    public function update(Request $request, AgentCategory $category)
    {
        $data = $request->validate([
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
            'sort_order' => 'nullable|integer|min:0',
            'is_active' => 'boolean',
        ]);

        $data['is_active'] = $request->boolean('is_active');
        $data['sort_order'] = $request->integer('sort_order', 0);

        $category->update($data);

        return redirect()->route('admin.agent-categories.index')
            ->with('success', 'Catégorie mise à jour.');
    }

    public function destroy(AgentCategory $category)
    {
        $category->delete();
        return redirect()->route('admin.agent-categories.index')
            ->with('success', 'Catégorie supprimée.');
    }

    public function assignAgents(Request $request, AgentCategory $category)
    {
        $data = $request->validate([
            'agent_ids' => 'nullable|array',
            'agent_ids.*' => 'exists:agents,id',
        ]);

        $agentIds = $data['agent_ids'] ?? [];

        if (!empty($agentIds)) {
            Agent::whereIn('id', $agentIds)->update(['category_id' => $category->id]);
        }

        return redirect()->route('admin.agent-categories.show', $category->id)
            ->with('success', count($agentIds) . ' agent(s) assigné(s) à la catégorie.');
    }
}