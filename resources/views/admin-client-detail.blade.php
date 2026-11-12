@extends('layouts.admin')

@section('title', 'Client ' . $display)

@section('content')
<div class="page-head">
  <div>
    <h1>Détail client</h1>
    <p>Informations et comptes Meta de {{ $display }}.</p>
  </div>
  <a href="{{ route('admin.dashboard') }}" class="btn"><svg class="ico"><use href="#i-arrow-left"/></svg> Retour</a>
</div>

<div class="kpis">
  <div class="kpi c1"><div class="top"><div class="ic"><svg class="ico"><use href="#i-user"/></svg></div></div><div class="num">{{ $initials }}</div><div class="lbl">Client</div></div>
  <div class="kpi c2"><div class="top"><div class="ic"><svg class="ico"><use href="#i-link"/></svg></div></div><div class="num">{{ $totalMeta }}</div><div class="lbl">Comptes Meta</div></div>
  <div class="kpi c3"><div class="top"><div class="ic"><svg class="ico"><use href="#i-check"/></svg></div><span class="delta">{{ $connectedCount }} connecté{{ $connectedCount > 1 ? 's' : '' }}</span></div><div class="num">{{ $client->email }}</div><div class="lbl">E-mail</div></div>
  <div class="kpi c4"><div class="top"><div class="ic"><svg class="ico"><use href="#i-dashboard"/></svg></div></div><div class="num">{{ $client->created_at ? \Illuminate\Support\Carbon::parse($client->created_at)->format('d/m/Y') : '—' }}</div><div class="lbl">Inscrit le</div></div>
</div>

<div class="panel">
  <div class="panel-head"><div><h2>Comptes Meta associés</h2><p>Comptes Meta rattachés à ce client.</p></div></div>
  <div class="table-wrap">
    <table class="data">
      <tr><th>Compte</th><th>Plateforme</th><th>ID Meta</th><th>Statut</th></tr>
      @forelse($client->metaAccounts as $acc)
        @php
          $isDisc = $acc->status === 'disconnected';
        @endphp
        <tr>
          <td>{{ $acc->name }}</td>
          <td>{{ strtoupper($acc->platform ?? 'facebook') }}</td>
          <td>{{ $acc->meta_id ?? '—' }}</td>
          <td><span class="pill {{ $isDisc ? 'r' : 'g' }}">{{ $isDisc ? 'Déconnecté' : 'Connecté' }}</span></td>
        </tr>
      @empty
        <tr><td colspan="4" style="color:var(--muted);padding:18px;">Aucun compte Meta associé à ce client.</td></tr>
      @endforelse
    </table>
  </div>
</div>

<div class="panel">
  <div class="panel-head"><div><h2>Informations client</h2><p>Détails du compte utilisateur.</p></div></div>
  <div class="table-wrap">
    <table class="data">
      <tr><th>Nom</th><th>Entreprise</th><th>Rôle</th><th>ID</th></tr>
      <tr>
        <td>{{ $display }}</td>
        <td>{{ $client->company ?? '—' }}</td>
        <td>{{ $client->role }}</td>
        <td>#{{ $client->id }}</td>
      </tr>
    </table>
  </div>
</div>
@endsection