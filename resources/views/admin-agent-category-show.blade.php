@extends('layouts.admin')

@section('title', 'Détails de la catégorie : ' . $category->name)

@section('content')
<div class="page-head">
  <div>
    <h1>{{ $category->name }}</h1>
    <p>Détails de la catégorie et agents associés.</p>
  </div>
  <div style="display:flex; gap:8px;">
    <a href="{{ route('admin.agent-categories.edit', $category->id) }}" class="btn secondary"><x-lucide-edit class="ico" /> Modifier</a>
    <a href="{{ route('admin.agent-categories.index') }}" class="btn primary"><x-lucide-arrow-left class="ico" /> Retour</a>
  </div>
</div>

@if(session('success'))
  <div class="alert success">{{ session('success') }}</div>
@endif

<div class="panel">
  <div class="panel-head"><div><h2>Informations</h2></div></div>
  <div style="padding:20px; display:grid; grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)); gap:16px;">
    <div>
      <label style="font-size:12px; color:var(--muted); text-transform:uppercase; letter-spacing:0.5px;">Nom</label>
      <div style="font-weight:600; margin-top:4px;">{{ $category->name }}</div>
    </div>
    <div>
      <label style="font-size:12px; color:var(--muted); text-transform:uppercase; letter-spacing:0.5px;">Description</label>
      <div style="margin-top:4px; color:var(--muted);">{{ $category->description ?? '—' }}</div>
    </div>
    <div>
      <label style="font-size:12px; color:var(--muted); text-transform:uppercase; letter-spacing:0.5px;">Ordre</label>
      <div style="font-weight:600; margin-top:4px;">{{ $category->sort_order }}</div>
    </div>
    <div>
      <label style="font-size:12px; color:var(--muted); text-transform:uppercase; letter-spacing:0.5px;">Statut</label>
      <div style="margin-top:4px;"><span class="pill {{ $category->is_active ? 'g' : 'r' }}">{{ $category->is_active ? 'Actif' : 'Inactif' }}</span></div>
    </div>
    <div>
      <label style="font-size:12px; color:var(--muted); text-transform:uppercase; letter-spacing:0.5px;">Créé le</label>
      <div style="margin-top:4px; color:var(--muted);">{{ $category->created_at->format('d/m/Y H:i') }}</div>
    </div>
    <div>
      <label style="font-size:12px; color:var(--muted); text-transform:uppercase; letter-spacing:0.5px;">Modifié le</label>
      <div style="margin-top:4px; color:var(--muted);">{{ $category->updated_at->format('d/m/Y H:i') }}</div>
    </div>
  </div>
</div>

<div class="panel" style="margin-top:16px;">
  <div class="panel-head"><div><h2>Agents dans cette catégorie ({{ $category->agents->count() }})</h2></div><a href="{{ route('admin.agents.create') }}" class="btn primary" style="padding:6px 12px; font-size:12.5px;"><x-lucide-plus class="ico" /> Ajouter un agent</a></div>
  <div class="table-wrap">
    @if($category->agents->count())
      <table class="data">
        <tr><th>Agent</th><th>Description</th><th>Abonnements autorisés</th><th>Statut</th><th></th></tr>
        @foreach($category->agents as $agent)
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
            </td>
          </tr>
        @endforeach
      </table>
    @else
      <div style="padding:40px; text-align:center; color:var(--muted);">
        <x-lucide-bot style="width:48px;height:48px;margin:0 auto 16px;opacity:0.4;" />
        <p style="margin:0 0 8px; font-size:16px;">Aucun agent dans cette catégorie.</p>
        <a href="{{ route('admin.agents.create') }}" class="btn primary"><x-lucide-plus class="ico" /> Créer un agent</a>
      </div>
    @endif
  </div>
</div>

<div class="panel" style="margin-top:16px;">
  <div class="panel-head"><div><h2>Assigner des agents à cette catégorie</h2></div></div>
  <form method="POST" action="{{ route('admin.agent-categories.assign-agents', $category->id) }}">
    @csrf
    @php
      $assignedAgentIds = $category->agents->pluck('id')->toArray();
      $availableAgents = \App\Models\Agent::whereNotIn('id', $assignedAgentIds)->where('is_active', true)->orderBy('name')->get();
    @endphp
    @if($availableAgents->count())
      <div style="padding:20px;">
        <p style="margin:0 0 16px; color:var(--muted);">Sélectionnez les agents à ajouter à cette catégorie :</p>
        <div style="display:flex; flex-wrap:wrap; gap:10px; max-height:300px; overflow-y:auto;">
          @foreach($availableAgents as $agent)
            <label style="display:flex; align-items:center; gap:8px; background:var(--panel); border:1px solid var(--border); border-radius:8px; padding:10px 14px; cursor:pointer; font-size:13.5px; min-width:200px;">
              <input type="checkbox" name="agent_ids[]" value="{{ $agent->id }}" style="width:18px; height:18px;">
              <span style="font-weight:600;">{{ $agent->name }}</span>
              @if($agent->category)
                <span style="color:var(--muted); font-size:11px; background:var(--bg); padding:2px 8px; border-radius:4px;">{{ $agent->category->name }}</span>
              @else
                <span style="color:var(--muted); font-size:11px; background:var(--bg); padding:2px 8px; border-radius:4px;">Sans catégorie</span>
              @endif
            </label>
          @endforeach
        </div>
        <div style="margin-top:16px; display:flex; gap:10px;">
          <button type="submit" class="btn primary"><x-lucide-user-plus class="ico" /> Assigner les sélectionnés</button>
        </div>
      </div>
    @else
      <div style="padding:40px; text-align:center; color:var(--muted);">
        <x-lucide-check-circle style="width:48px;height:48px;margin:0 auto 16px;opacity:0.4;" />
        <p style="margin:0 0 8px; font-size:16px;">Tous les agents actifs sont déjà dans cette catégorie.</p>
      </div>
    @endif
  </form>
</div>
@endsection