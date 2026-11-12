@extends('layouts.admin')

@section('title', 'Agents IA')

@section('content')
<div class="page-head">
  <div>
    <h1>Agents IA</h1>
    <p>Gérez les agents IA de la plateforme et leurs abonnements autorisés.</p>
  </div>
  <a href="{{ route('admin.agents.create') }}" class="btn primary"><svg class="ico"><use href="#i-plus"/></svg> Nouvel agent</a>
</div>

@if(session('success'))
  <div class="alert success">{{ session('success') }}</div>
@endif
@if(session('error'))
  <div class="alert error">{{ session('error') }}</div>
@endif

<div class="panel">
  <div class="panel-head"><div><h2>Liste des agents</h2><p>Agents configurés sur la plateforme.</p></div></div>
  <div class="table-wrap">
    <table class="data">
      <tr><th>Agent</th><th>Description</th><th>Abonnements autorisés</th><th>Statut</th><th></th></tr>
      @forelse($agents as $agent)
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
            <a href="{{ route('admin.agents.show', $agent->id) }}" class="icbtn" title="Voir"><svg class="ico"><use href="#i-eye"/></svg></a>
            <a href="{{ route('admin.agents.edit', $agent->id) }}" class="icbtn" title="Modifier"><svg class="ico"><use href="#i-edit"/></svg></a>
            <form action="{{ route('admin.agents.destroy', $agent->id) }}" method="POST" style="display:inline;" onsubmit="return confirm('Supprimer cet agent ? Cette action est irréversible.');">
              @csrf @method('DELETE')
              <button type="submit" class="icbtn btn-danger" title="Supprimer"><svg class="ico"><use href="#i-trash"/></svg></button>
            </form>
          </td>
        </tr>
      @empty
        <tr><td colspan="5" style="color:var(--muted);padding:18px;">Aucun agent configuré. Cliquez sur "Nouvel agent" pour en ajouter un.</td></tr>
      @endforelse
    </table>
  </div>
</div>
@endsection