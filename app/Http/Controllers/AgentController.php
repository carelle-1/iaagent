<?php

namespace App\Http\Controllers;

use App\Models\Agent;
use App\Models\AgentCategory;
use App\Models\Subscription;
use Illuminate\Http\Request;

class AgentController extends Controller
{
    public function index()
    {
        $agents = Agent::with(['subscriptions', 'category'])->orderBy('name')->get();
        $categories = AgentCategory::ordered()->get();
        $subscriptions = Subscription::where('is_active', true)->orderBy('sort_order')->get();
        return view('admin-agents', compact('agents', 'categories', 'subscriptions'));
    }

    public function create()
    {
        $subscriptions = Subscription::where('is_active', true)->orderBy('sort_order')->get();
        $categories = AgentCategory::active()->ordered()->get();
        return view('admin-agent-form', compact('subscriptions', 'categories'))->with('agent', null);
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
            'category_id' => 'nullable|exists:agent_categories,id',
            'is_active' => 'boolean',
            'subscription_ids' => 'nullable|array',
            'subscription_ids.*' => 'exists:subscriptions,id',
        ]);

        $data['is_active'] = $request->boolean('is_active');

        $agent = Agent::create($data);

        if ($request->filled('subscription_ids')) {
            $agent->subscriptions()->sync($request->subscription_ids);
        }

        return redirect()->route('admin.agents.index')
            ->with('success', 'Agent créé avec succès.');
    }

    public function show(Agent $agent)
    {
        $agent->load(['subscriptions', 'category']);
        return view('admin-agent-show', compact('agent'));
    }

    public function edit(Agent $agent)
    {
        $subscriptions = Subscription::where('is_active', true)->orderBy('sort_order')->get();
        $categories = AgentCategory::active()->ordered()->get();
        $agent->load('subscriptions');
        return view('admin-agent-form', compact('agent', 'subscriptions', 'categories'));
    }

    public function update(Request $request, Agent $agent)
    {
        $data = $request->validate([
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
            'category_id' => 'nullable|exists:agent_categories,id',
            'is_active' => 'boolean',
            'subscription_ids' => 'nullable|array',
            'subscription_ids.*' => 'exists:subscriptions,id',
        ]);

        $data['is_active'] = $request->boolean('is_active');

        $agent->update($data);

        $agent->subscriptions()->sync($request->subscription_ids ?? []);

        return redirect()->route('admin.agents.index')
            ->with('success', 'Agent mis à jour.');
    }

    public function destroy(Agent $agent)
    {
        $agent->delete();
        return redirect()->route('admin.agents.index')
            ->with('success', 'Agent supprimé.');
    }
}