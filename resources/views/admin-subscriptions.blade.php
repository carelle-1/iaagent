@extends('layouts.admin')

@section('title', 'Abonnements')

@section('content')
<div class="page-head">
  <div>
    <h1>Abonnements & Facturation</h1>
    <p>Créez, modifiez et gérez les forfaits d'abonnement de la plateforme.</p>
  </div>
  <a href="{{ route('admin.subscriptions.create') }}" class="btn primary"><x-lucide-plus class="ico" /> Nouvel abonnement</a>
</div>

<div class="panel">
  <div class="panel-head"><div><h2>Forfaits actifs</h2><p>Liste des abonnements disponibles pour les clients.</p></div></div>
  <div class="table-wrap">
    <table class="data">
      <tr><th>Forfait</th><th>Prix</th><th>Durée</th><th>Avantages</th><th>Statut</th><th></th></tr>
      @forelse($subscriptions as $sub)
        @php
          $features = $sub->features;
          $price = number_format($sub->price, 0, ',', ' ') . ' ' . $sub->currency . ' / ' . ($sub->interval === 'month' ? 'mois' : 'an');
        @endphp
        <tr>
          <td>
            <div style="font-weight:600;">{{ $sub->name }}</div>
            <div style="font-size:12px; color:var(--muted);">{{ $sub->slug }}</div>
            @if($sub->description)
              <div style="font-size:12px; color:var(--muted-2); margin-top:4px;">{{ $sub->description }}</div>
            @endif
          </td>
          <td>{{ $price }}</td>
          <td>{{ $sub->interval === 'month' ? 'Mensuel' : 'Annuel' }}</td>
          <td>
            <ul style="list-style:none; margin:0; padding:0; font-size:12px; color:var(--muted);">
              @forelse($features as $feature)
                <li style="margin-bottom:2px;">• {{ $feature }}</li>
              @empty
                <li>Aucun avantage défini</li>
              @endforelse
            </ul>
          </td>
          <td><span class="pill {{ $sub->is_active ? 'g' : 'r' }}">{{ $sub->is_active ? 'Actif' : 'Inactif' }}</span></td>
          <td class="row-actions">
            <a href="{{ route('admin.subscriptions.show', $sub->id) }}" class="icbtn" title="Voir"><x-lucide-eye class="ico" /></a>
            <a href="{{ route('admin.subscriptions.edit', $sub->id) }}" class="icbtn" title="Modifier"><x-lucide-edit class="ico" /></a>
            <form action="{{ route('admin.subscriptions.destroy', $sub->id) }}" method="POST" style="display:inline;" onsubmit="return confirm('Supprimer cet abonnement ? Cette action est irréversible.');">
              @csrf @method('DELETE')
              <button type="submit" class="icbtn btn-danger" title="Supprimer"><x-lucide-trash-2 class="ico" /></button>
            </form>
          </td>
        </tr>
      @empty
        <tr><td colspan="6" style="color:var(--muted);padding:18px;">Aucun abonnement configuré. Cliquez sur "Nouvel abonnement" pour en ajouter un.</td></tr>
      @endforelse
    </table>
  </div>
</div>

<div class="panel">
  <div class="panel-head"><div><h2>Factures récentes</h2><p>Derniers paiements traités</p></div><span class="link">Tout voir â†’</span></div>
  <div class="table-wrap">
    <table class="data">
      <tr><th>Client</th><th>Montant</th><th>Date</th><th>Statut</th></tr>
      <tr><td>Ghostroar Digital</td><td>79,00 €</td><td>01/11/2026</td><td><span class="pill g">Payée</span></td></tr>
      <tr><td>Atelier Numa</td><td>9,00 €</td><td>03/11/2026</td><td><span class="pill g">Payée</span></td></tr>
      <tr><td>Agence Verso</td><td>79,00 €</td><td>05/11/2026</td><td><span class="pill r">Échouée</span></td></tr>
    </table>
  </div>
</div>
@endsection


