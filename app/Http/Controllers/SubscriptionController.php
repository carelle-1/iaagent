<?php

namespace App\Http\Controllers;

use App\Models\Subscription;
use Illuminate\Http\Request;

class SubscriptionController extends Controller
{
    public function index()
    {
        $subscriptions = Subscription::orderBy('sort_order')->latest()->get();
        return view('admin-subscriptions', compact('subscriptions'));
    }

    public function create()
    {
        return view('admin-subscription-form')->with('subscription', null);
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'name' => 'required|string|max:255',
            'slug' => 'required|string|max:255|unique:subscriptions',
            'description' => 'nullable|string',
            'price' => 'required|numeric|min:0',
            'currency' => 'required|string|max:10',
            'interval' => 'required|in:month,year',
            'features' => 'nullable|string',
            'is_active' => 'boolean',
            'sort_order' => 'required|integer|min:0',
        ]);

        $data['features'] = $request->input('features') ? array_filter(array_map('trim', explode("\n", $request->input('features')))) : [];
        $data['is_active'] = $request->boolean('is_active');
        $data['price'] = (float) $data['price'];

        Subscription::create($data);

        return redirect()->route('admin.subscriptions.index')
            ->with('success', 'Abonnement créé avec succès.');
    }

    public function show(Subscription $subscription)
    {
        return view('admin-subscription-show', compact('subscription'));
    }

    public function edit(Subscription $subscription)
    {
        return view('admin-subscription-form', compact('subscription'));
    }

    public function update(Request $request, Subscription $subscription)
    {
        $data = $request->validate([
            'name' => 'required|string|max:255',
            'slug' => 'required|string|max:255|unique:subscriptions,slug,' . $subscription->id,
            'description' => 'nullable|string',
            'price' => 'required|numeric|min:0',
            'currency' => 'required|string|max:10',
            'interval' => 'required|in:month,year',
            'features' => 'nullable|string',
            'is_active' => 'boolean',
            'sort_order' => 'required|integer|min:0',
        ]);

        $data['features'] = $request->input('features') ? array_filter(array_map('trim', explode("\n", $request->input('features')))) : [];
        $data['is_active'] = $request->boolean('is_active');
        $data['price'] = (float) $data['price'];

        $subscription->update($data);

        return redirect()->route('admin.subscriptions.index')
            ->with('success', 'Abonnement mis à jour.');
    }

    public function destroy(Subscription $subscription)
    {
        $subscription->delete();
        return redirect()->route('admin.subscriptions.index')
            ->with('success', 'Abonnement supprimé.');
    }
}
