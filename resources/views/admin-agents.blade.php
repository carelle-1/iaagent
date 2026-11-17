@extends('layouts.admin')

@section('title', 'Agents IA')

@section('content')
<div class="page-head">
  <div>
    <h1>Agents IA</h1>
    <p>Gérez les agents IA de la plateforme et leurs abonnements autorisés.</p>
  </div>
  <div style="display:flex; gap:8px; flex-wrap:wrap;">
    <a href="{{ route('admin.agents.create') }}" class="btn primary"><x-lucide-plus class="ico" /> Nouvel agent</a>
    <a href="{{ route('admin.agent-categories.index') }}" class="btn secondary"><x-lucide-folder-plus class="ico" /> Gérer les catégories</a>
  </div>
</div>

@if(session('success'))
  <div class="alert success">{{ session('success') }}</div>
@endif
@if(session('error'))
  <div class="alert error">{{ session('error') }}</div>
@endif

<div class="panel">
  <div class="panel-head"><div><h2>Liste des agents</h2><p>Agents configurés sur la plateforme, groupés par catégorie.</p></div></div>
  <div class="table-wrap">
    @php
      $agentsByCategory = $agents->groupBy(function($agent) {
          return $agent->category ? $agent->category->id : 0;
      });
      $categoriesWithAgents = $categories->mapWithKeys(function($cat) use ($agentsByCategory) {
          return [$cat->id => $agentsByCategory->get($cat->id, collect())];
      });
      $uncategorizedAgents = $agentsByCategory->get(0, collect());
    @endphp

    @foreach($categoriesWithAgents as $categoryId => $categoryAgents)
      @php $category = $categories->firstWhere('id', $categoryId) @endphp
      <div style="margin-bottom:24px;">
        <h3 style="margin:0 0 12px; padding:10px 14px; background:var(--panel); border:1px solid var(--border); border-radius:8px; display:flex; align-items:center; gap:10px;">
          <span style="font-weight:600;">{{ $category->name }}</span>
          @if($category->description)
            <span style="color:var(--muted); font-size:12.5px; font-weight:400;">{{ $category->description }}</span>
          @endif
          <span class="pill v" style="font-size:11px;">{{ $categoryAgents->count() }} agent(s)</span>
        </h3>
        <table class="data" style="width:100%;">
          <tr><th>Agent</th><th>Description</th><th>Abonnements autorisés</th><th>Statut</th><th></th></tr>
          @forelse($categoryAgents as $agent)
            <tr>
              <td>
                <div style="font-weight:600;">{{ $agent->name }}</div>
              </td>
              <td style="color:var(--muted); max-width:300px;">{{ $agent->description ?? '—' }}</td>
              <td>
                @if($agent->subscriptions->count())
                  @foreach($agent->subscriptions as $sub)
                    <span class="pill v" style="margin-right:6px; margin-bottom:4px; display:inline-block;">{{ $sub->name }}</span>
                  @endforeach
                @else
                  <span style="color:var(--muted);">— Aucun —</span>
                @endif
              </td>
              <td><span class="pill {{ $agent->is_active ? 'g' : 'r' }}">{{ $agent->is_active ? 'Actif' : 'Inactif' }}</span></td>
              <td class="row-actions">
                <a href="{{ route('admin.agents.show', $agent->id) }}" class="icbtn" title="Voir"><x-lucide-eye class="ico" /></a>
                <a href="{{ route('admin.agents.edit', $agent->id) }}" class="icbtn" title="Modifier"><x-lucide-edit class="ico" /></a>
                <form action="{{ route('admin.agents.destroy', $agent->id) }}" method="POST" style="display:inline;" onsubmit="return confirm('Supprimer cet agent ? Cette action est irréversible.');">
                  @csrf @method('DELETE')
                  <button type="submit" class="icbtn btn-danger" title="Supprimer"><x-lucide-trash-2 class="ico" /></button>
                </form>
              </td>
            </tr>
          @empty
            <tr><td colspan="5" style="color:var(--muted);padding:18px;">Aucun agent dans cette catégorie.</td></tr>
          @endforelse
        </table>
      </div>
    @endforeach

    @if($uncategorizedAgents->count())
      <div style="margin-bottom:24px;">
        <h3 style="margin:0 0 12px; padding:10px 14px; background:var(--panel); border:1px solid var(--border); border-radius:8px; display:flex; align-items:center; gap:10px;">
          <span style="font-weight:600; color:var(--muted);">Sans catégorie</span>
          <span class="pill" style="font-size:11px;">{{ $uncategorizedAgents->count() }} agent(s)</span>
        </h3>
        <table class="data" style="width:100%;">
          <tr><th>Agent</th><th>Description</th><th>Abonnements autorisés</th><th>Statut</th><th></th></tr>
          @forelse($uncategorizedAgents as $agent)
            <tr>
              <td>
                <div style="font-weight:600;">{{ $agent->name }}</div>
              </td>
              <td style="color:var(--muted); max-width:300px;">{{ $agent->description ?? '—' }}</td>
              <td>
                @if($agent->subscriptions->count())
                  @foreach($agent->subscriptions as $sub)
                    <span class="pill v" style="margin-right:6px; margin-bottom:4px; display:inline-block;">{{ $sub->name }}</span>
                  @endforeach
                @else
                  <span style="color:var(--muted);">— Aucun —</span>
                @endif
              </td>
              <td><span class="pill {{ $agent->is_active ? 'g' : 'r' }}">{{ $agent->is_active ? 'Actif' : 'Inactif' }}</span></td>
              <td class="row-actions">
                <a href="{{ route('admin.agents.show', $agent->id) }}" class="icbtn" title="Voir"><x-lucide-eye class="ico" /></a>
                <a href="{{ route('admin.agents.edit', $agent->id) }}" class="icbtn" title="Modifier"><x-lucide-edit class="ico" /></a>
                <form action="{{ route('admin.agents.destroy', $agent->id) }}" method="POST" style="display:inline;" onsubmit="return confirm('Supprimer cet agent ? Cette action est irréversible.');">
                  @csrf @method('DELETE')
                  <button type="submit" class="icbtn btn-danger" title="Supprimer"><x-lucide-trash-2 class="ico" /></button>
                </form>
              </td>
            </tr>
          @empty
            <tr><td colspan="5" style="color:var(--muted);padding:18px;">Aucun agent sans catégorie.</td></tr>
          @endforelse
        </table>
      </div>
    @endif

    @if($agents->isEmpty())
      <div style="padding:40px; text-align:center; color:var(--muted);">
        <x-lucide-bot style="width:48px;height:48px;margin:0 auto 16px;opacity:0.4;" />
        <p style="margin:0 0 8px; font-size:16px;">Aucun agent configuré.</p>
        <p style="margin:0 0 16px;">Cliquez sur "Nouvel agent" pour en ajouter un.</p>
        <a href="{{ route('admin.agents.create') }}" class="btn primary"><x-lucide-plus class="ico" /> Nouvel agent</a>
      </div>
    @endif
  </div>
</div>
@endsection