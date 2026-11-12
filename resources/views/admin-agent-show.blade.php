@extends('layouts.admin')

@section('title', $agent->name)

@section('content')
<div class="page-head">
  <div>
    <h1>{{ $agent->name }}</h1>
    <p>{{ $agent->description ?? 'Aucune description.' }}</p>
  </div>
  <div class="actions">
    <a href="{{ route('admin.agents.edit', $agent->id) }}" class="btn"><svg class="ico"><use href="#i-edit"/></svg> Modifier</a>
    <form action="{{ route('admin.agents.destroy', $agent->id) }}" method="POST" style="display:inline;" onsubmit="return confirm('Supprimer cet agent ? Cette action est irréversible.');">
      @csrf @method('DELETE')
      <button type="submit" class="btn danger"><svg class="ico"><use href="#i-trash"/></svg> Supprimer</button>
    </form>
    <a href="{{ route('admin.agents.index') }}" class="btn primary"><svg class="ico"><use href="#i-arrow-left"/></svg> Retour</a>
  </div>
</div>

<div class="kpis">
  <div class="kpi c1"><div class="top"><div class="ic"><svg class="ico"><use href="#i-check"/></svg></div></div><div class="num">{{ $agent->is_active ? 'Actif' : 'Inactif' }}</div><div class="lbl">Statut</div></div>
  <div class="kpi c2"><div class="top"><div class="ic"><svg class="ico"><use href="#i-link"/></svg></div></div><div class="num">{{ $agent->subscriptions->count() }}</div><div class="lbl">Abonnements liés</div></div>
  <div class="kpi c3"><div class="top"><div class="ic"><svg class="ico"><use href="#i-dashboard"/></svg></div></div><div class="num">{{ \Illuminate\Support\Carbon::parse($agent->created_at)->format('d/m/Y') }}</div><div class="lbl">Créé le</div></div>
</div>

<div class="panel">
  <div class="panel-head"><div><h2>Abonnements autorisés</h2><p>Forfaits qui peuvent utiliser cet agent.</p></div></div>
  @if($agent->subscriptions->count())
    <div style="display:flex; flex-wrap:wrap; gap:10px;">
      @foreach($agent->subscriptions as $sub)
        <div style="background:var(--panel-2); border:1px solid var(--border); border-radius:10px; padding:14px 18px; min-width:200px;">
          <div style="font-weight:600;">{{ $sub->name }}</div>
          <div style="color:var(--muted); font-size:12px; margin-top:4px;">{{ number_format($sub->price, 0, ',', ' ') }} {{ $sub->currency }} / {{ $sub->interval === 'month' ? 'mois' : 'an' }}</div>
          @if($sub->features->count())
            <ul style="margin:8px 0 0; padding-left:16px; font-size:11px; color:var(--muted);">
              @foreach($sub->features as $feature)
                <li>{{ $feature }}</li>
              @endforeach
            </ul>
          @endif
        </div>
      @endforeach
    </div>
  @else
    <p style="color:var(--muted);">Aucun abonnement lié à cet agent.</p>
  @endif
</div>

<div class="panel">
  <div class="panel-head"><div><h2>Informations techniques</h2></div></div>
  <div class="table-wrap">
    <table class="data">
      <tr><th>Champ</th><th>Valeur</th></tr>
      <tr><td>ID</td><td>#{{ $agent->id }}</td></tr>
      <tr><td>Nom</td><td>{{ $agent->name }}</td></tr>
      <tr><td>Statut</td><td><span class="pill {{ $agent->is_active ? 'g' : 'r' }}">{{ $agent->is_active ? 'Actif' : 'Inactif' }}</span></td></tr>
      <tr><td>Créé le</td><td>{{ \Illuminate\Support\Carbon::parse($agent->created_at)->format('d/m/Y H:i') }}</td></tr>
      <tr><td>Modifié le</td><td>{{ \Illuminate\Support\Carbon::parse($agent->updated_at)->format('d/m/Y H:i') }}</td></tr>
    </table>
  </div>
</div>
@endsection