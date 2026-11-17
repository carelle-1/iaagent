@extends('layouts.admin')

@section('title', $subscription->name)

@section('content')
<div class="page-head">
  <div>
    <h1>{{ $subscription->name }}</h1>
    <p>{{ $subscription->description ?? 'Aucune description.' }}</p>
  </div>
  <div class="actions">
    <a href="{{ route('admin.subscriptions.edit', $subscription->id) }}" class="btn"><x-lucide-edit class="ico" /> Modifier</a>
    <form action="{{ route('admin.subscriptions.destroy', $subscription->id) }}" method="POST" style="display:inline;" onsubmit="return confirm('Supprimer cet abonnement ? Cette action est irréversible.');">
      @csrf @method('DELETE')
      <button type="submit" class="btn danger"><x-lucide-trash-2 class="ico" /> Supprimer</button>
    </form>
    <a href="{{ route('admin.subscriptions.index') }}" class="btn primary"><x-lucide-arrow-left class="ico" /> Retour</a>
  </div>
</div>

<div class="kpis">
  <div class="kpi c1"><div class="top"><div class="ic"><x-lucide-link class="ico" /></div></div><div class="num">{{ $subscription->slug }}</div><div class="lbl">Slug</div></div>
  <div class="kpi c2"><div class="top"><div class="ic"><x-lucide-users class="ico" /></div></div><div class="num">{{ number_format($subscription->price, 0, ',', ' ') }} {{ $subscription->currency }}</div><div class="lbl">Prix / {{ $subscription->interval === 'month' ? 'mois' : 'an' }}</div></div>
  <div class="kpi c3"><div class="top"><div class="ic"><x-lucide-check class="ico" /></div><span class="delta {{ $subscription->is_active ? '' : 'down' }}">{{ $subscription->is_active ? 'Actif' : 'Inactif' }}</span></div><div class="num">{{ $subscription->interval === 'month' ? 'Mensuel' : 'Annuel' }}</div><div class="lbl">Répétition</div></div>
  <div class="kpi c4"><div class="top"><div class="ic"><x-lucide-layout-dashboard class="ico" /></div></div><div class="num">{{ \Illuminate\Support\Carbon::parse($subscription->created_at)->format('d/m/Y') }}</div><div class="lbl">Créé le</div></div>
</div>

<div class="panel">
  <div class="panel-head"><div><h2>Avantages du forfait</h2><p>Liste des bénéfices inclus dans cet abonnement.</p></div></div>
  @if(count($subscription->features))
    <ul class="features-list">
      @foreach($subscription->features as $feature)
        <li>{{ $feature }}</li>
      @endforeach
    </ul>
  @else
    <p style="color:var(--muted);">Aucun avantage défini pour ce forfait.</p>
  @endif
</div>

<div class="panel">
  <div class="panel-head"><div><h2>Informations techniques</h2><p>Détails du forfait dans la base de données.</p></div></div>
  <div class="table-wrap">
    <table class="data">
      <tr><th>Champ</th><th>Valeur</th></tr>
      <tr><td>ID</td><td>#{{ $subscription->id }}</td></tr>
      <tr><td>Nom</td><td>{{ $subscription->name }}</td></tr>
      <tr><td>Slug</td><td>{{ $subscription->slug }}</td></tr>
      <tr><td>Prix</td><td>{{ number_format($subscription->price, 0, ',', ' ') }} {{ $subscription->currency }}</td></tr>
      <tr><td>Intervalle</td><td>{{ $subscription->interval === 'month' ? 'Mensuel' : 'Annuel' }}</td></tr>
      <tr><td>Statut</td><td><span class="pill {{ $subscription->is_active ? 'g' : 'r' }}">{{ $subscription->is_active ? 'Actif' : 'Inactif' }}</span></td></tr>
      <tr><td>Ordre d'affichage</td><td>{{ $subscription->sort_order }}</td></tr>
      <tr><td>Créé le</td><td>{{ \Illuminate\Support\Carbon::parse($subscription->created_at)->format('d/m/Y H:i') }}</td></tr>
      <tr><td>Modifié le</td><td>{{ \Illuminate\Support\Carbon::parse($subscription->updated_at)->format('d/m/Y H:i') }}</td></tr>
    </table>
  </div>
</div>
@endsection


