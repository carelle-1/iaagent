@extends('layouts.admin')

@section('title', 'Dashboard')

@section('content')
<!-- OVERVIEW -->
<section class="page active" id="page-overview" data-page="overview">
  <div class="page-head"><div><h1>Vue d'ensemble de la plateforme</h1><p>Santé globale d'IA Agent Studio, tous clients confondus.</p></div></div>

  <div class="kpis">
    <div class="kpi c1"><div class="top"><div class="ic"><svg class="ico"><use href="#i-clients"/></svg></div><span class="delta">+{{ $newUsersMonth ?? 0 }} ce mois</span></div><div class="num">{{ $totalUsers ?? 0 }}</div><div class="lbl">Clients (utilisateurs)</div></div>
    <div class="kpi c2"><div class="top"><div class="ic"><svg class="ico"><use href="#i-check"/></svg></div><span class="delta">{{ $metaTotal ?? 0 }} au total</span></div><div class="num">{{ $metaConnected ?? 0 }}</div><div class="lbl">Comptes Meta connectés</div></div>
    <div class="kpi c3"><div class="top"><div class="ic"><svg class="ico"><use href="#i-dashboard"/></svg></div><span class="delta">{{ $metaConnected ?? 0 }} connectés</span></div><div class="num">{{ $metaTotal ?? 0 }}</div><div class="lbl">Comptes Meta (total)</div></div>
    <div class="kpi c4"><div class="top"><div class="ic"><svg class="ico"><use href="#i-alert"/></svg></div><span class="delta {{ ($metaPending ?? 0) > 0 ? 'down' : '' }}">{{ $metaDisconnected ?? 0 }} déconnectés</span></div><div class="num">{{ $metaPending ?? 0 }}</div><div class="lbl">Comptes Meta en attente</div></div>
  </div>

  <div class="two-col">
    <div class="panel">
      <div class="panel-head"><div><h2>Croissance des clients</h2><p>Nouveaux comptes créés par mois</p></div><span class="link">Exporter <svg class="ico" style="width:14px;height:14px;vertical-align:-2px"><use href="#i-arrow-tr"/></svg></span></div>
      <div class="bars">
        @php
          $growthData = $growthData ?? [];
          $growthLabels = $growthLabels ?? [];
          $maxGrowth = $maxGrowth > 0 ? $maxGrowth : 1;
        @endphp
        @forelse($growthData as $index => $count)
          @php
            $height = $maxGrowth > 0 ? round(($count / $maxGrowth) * 100) : 0;
            if ($height < 15) { $height = 15; }
          @endphp
          <div class="bar-col"><i style="height:{{ $height }}%"></i><span>{{ $growthLabels[$index] ?? '' }}</span></div>
        @empty
          <div class="bar-col"><i style="height:15%"></i><span>—</span></div>
        @endforelse
      </div>
    </div>
    <div class="panel">
      <div class="panel-head"><div><h2>Activité récente</h2><p>Événements plateforme</p></div></div>
      <div class="activity">
        <div class="activity-item"><span class="dot g"></span><div><div class="t">Ghostroar Digital a validé 4 publications</div><div class="time">il y a 12 min</div></div></div>
        <div class="activity-item"><span class="dot c"></span><div><div class="t">Nouveau client : Atelier Numa (Pack Starter)</div><div class="time">il y a 1 h</div></div></div>
        <div class="activity-item"><span class="dot o"></span><div><div class="t">Jeton Meta expiré pour « Lumio Studio »</div><div class="time">il y a 3 h</div></div></div>
        <div class="activity-item"><span class="dot r"></span><div><div class="t">Échec de paiement — Agence Verso</div><div class="time">il y a 5 h</div></div></div>
      </div>
    </div>
  </div>

  <div class="panel">
    <div class="panel-head"><div><h2>Clients nécessitant une attention</h2><p>Clients ayant un compte/page Meta en statut déconnecté ou en attente (données réelles).</p></div><span class="link">Données réelles</span></div>
    <div class="table-wrap">
      <table class="data">
        <tr><th>Client</th><th>Compte Meta</th><th>Problème</th><th>Statut</th><th></th></tr>
        @forelse(($attentionClients ?? []) as $acc)
          @php
            $u = $acc->user ?? null;
            $fullName = trim((($u->first_name ?? '') . ' ' . ($u->last_name ?? '')));
            $display = $fullName !== '' ? $fullName : ($u->email ?? 'Client');
            $initials = strtoupper((mb_substr($u->first_name ?? ($u->email ?? 'C'), 0, 1) ?: 'C') . (mb_substr($u->last_name ?? '', 0, 1) ?: ''));
            $isDisc = ($acc->status ?? '') === 'disconnected';
          @endphp
          <tr>
            <td><div class="client-cell"><div class="av">{{ $initials }}</div><div><div class="nm">{{ $display }}</div><div class="em">{{ $u->email ?? '' }}</div></div></div></td>
            <td>{{ $acc->name }} <span style="color:var(--muted);font-size:12px;">({{ strtoupper($acc->platform ?? 'facebook') }})</span></td>
            <td>{{ $isDisc ? 'Compte Meta déconnecté' : 'Compte Meta en attente' }}</td>
            <td><span class="pill {{ $isDisc ? 'r' : 'o' }}">{{ $isDisc ? 'Déconnecté' : 'En attente' }}</span></td>
            <td class="row-actions"><div class="icbtn" title="ID: {{ $acc->meta_id ?? '-' }}"><svg class="ico"><use href="#i-arrow-tr"/></svg></div></td>
          </tr>
        @empty
          <tr><td colspan="5" style="color:var(--muted);padding:18px;">Aucun client ne nécessite d'attention pour le moment.</td></tr>
        @endforelse
      </table>
    </div>
  </div>
</section>

<!-- CLIENTS -->
<section class="page" id="page-clients" data-page="clients">
  <div class="page-head"><div><h1>Clients & Comptes</h1><p>{{ $totalUsers }} compte{{ $totalUsers > 1 ? 's' : '' }} actif{{ $totalUsers > 1 ? 's' : '' }} sur la plateforme.</p></div><button class="pillf active" style="cursor:pointer;">+ Nouveau client</button></div>
  <div class="toolbar">
    <div class="pillf active">Tous (142)</div>
    <div class="pillf">Actifs (128)</div>
    <div class="pillf">À reconnecter (9)</div>
    <div class="pillf">Suspendus (5)</div>
  </div>
  <div class="panel">
    <div class="table-wrap">
      <table class="data">
        <tr><th>Client</th><th>Plan</th><th>Pages connectées</th><th>Posts / mois</th><th>Statut</th><th></th></tr>
        @forelse(($clients ?? []) as $client)
          @php
            $metaAccounts = $client->metaAccounts ?? collect();
            $connectedCount = $metaAccounts->where('status', 'connected')->count();
            $totalMeta = $metaAccounts->count();
            $fullName = trim(($client->first_name ?? '') . ' ' . ($client->last_name ?? ''));
            $display = $fullName !== '' ? $fullName : ($client->email ?? 'Client');
            $initials = strtoupper((mb_substr($client->first_name ?? ($client->email ?? 'C'), 0, 1) ?: 'C') . (mb_substr($client->last_name ?? '', 0, 1) ?: ''));
            if ($totalMeta === 0) {
              $statusLabel = 'Aucun compte';
              $statusClass = 'v';
            } elseif ($connectedCount === $totalMeta) {
              $statusLabel = 'Actif';
              $statusClass = 'g';
            } elseif ($connectedCount > 0) {
              $statusLabel = 'Partiel';
              $statusClass = 'o';
            } else {
              $statusLabel = 'Déconnecté';
              $statusClass = 'r';
            }
            $plan = $client->company ? $client->company : 'Starter';
          @endphp
          <tr>
            <td><div class="client-cell"><div class="av">{{ $initials }}</div><div><div class="nm">{{ $display }}</div><div class="em">{{ $client->email ?? '' }}</div></div></div></td>
            <td>{{ $plan }}</td>
            <td>{{ $totalMeta }}</td>
            <td>-</td>
            <td><span class="pill {{ $statusClass }}">{{ $statusLabel }}</span></td>
            <td class="row-actions"><a href="{{ route('admin.clients.show', $client->id) }}" class="icbtn" title="Détails"><svg class="ico"><use href="#i-settings"/></svg></a><a href="{{ route('admin.clients.show', $client->id) }}" class="icbtn" target="_blank" title="Ouvrir"><svg class="ico"><use href="#i-arrow-tr"/></svg></a></td>
          </tr>
        @empty
          <tr><td colspan="6" style="color:var(--muted);padding:18px;">Aucun client trouvé dans la base de données.</td></tr>
        @endforelse
      </table>
    </div>
  </div>
</section>

<!-- AGENTS IA -->
<section class="page" id="page-agents" data-page="agents">
  <div class="page-head"><div><h1>Agents IA</h1><p>Gestion des agents IA de la plateforme.</p></div><a href="{{ route('admin.agents.index') }}" class="btn" style="padding:6px 12px; font-size:12.5px;">Gérer les agents <svg class="ico" style="width:12px;height:12px;vertical-align:-1px"><use href="#i-arrow-tr"/></svg></a></div>
  <div class="plan-grid">
    @forelse(($agents ?? []) as $agent)
      <div class="plan-card {{ $agent->is_active ? '' : 'opacity-50' }}">
        <h3>{{ $agent->name }} <span class="pill {{ $agent->is_active ? 'g' : 'r' }}">{{ $agent->is_active ? 'Actif' : 'Inactif' }}</span></h3>
        @if($agent->description)
          <div style="margin-top:8px; font-size:12px; color:var(--muted);">{{ $agent->description }}</div>
        @endif
        <div style="margin-top:8px;">
          @if($agent->subscriptions->count())
            @foreach($agent->subscriptions as $sub)
              <span class="pill v" style="margin-right:4px; margin-bottom:4px; display:inline-block; font-size:11px;">{{ $sub->name }}</span>
            @endforeach
          @else
            <span style="font-size:11px; color:var(--muted);">Aucun abonnement lié</span>
          @endif
        </div>
      </div>
    @empty
      <div class="plan-card" style="grid-column:1/-1; text-align:center; color:var(--muted);">
        <h3>Aucun agent configuré</h3>
        <div class="amt">—</div>
        <div class="sub">Créez un agent depuis la gestion.</div>
      </div>
    @endforelse
  </div>
</section>

<!-- FACTURATION -->
<section class="page" id="page-facturation" data-page="facturation">
  <div class="page-head"><div><h1>Abonnements & Facturation</h1><p>Répartition des forfaits et revenus récurrents.</p></div><a href="{{ route('admin.subscriptions.index') }}" class="btn" style="padding:6px 12px; font-size:12.5px;">Gérer les abonnements <svg class="ico" style="width:12px;height:12px;vertical-align:-1px"><use href="#i-arrow-tr"/></svg></a></div>
  <div class="plan-grid">
    @forelse(($subscriptions ?? []) as $sub)
      @php
        $features = $sub->features ?? [];
        $price = number_format($sub->price ?? 0, 0, ',', ' ') . ' ' . ($sub->currency ?? '€') . ' / ' . (($sub->interval ?? 'month') === 'month' ? 'mois' : 'an');
      @endphp
      <div class="plan-card {{ $sub->is_active ? '' : 'opacity-50' }}">
        <h3>{{ $sub->name }} <span class="pill {{ $sub->is_active ? 'g' : 'r' }}">{{ $sub->is_active ? 'Actif' : 'Inactif' }}</span></h3>
        <div class="amt">{{ number_format($sub->price, 0, ',', ' ') }} {{ $sub->currency }}</div>
        <div class="sub">{{ $sub->interval === 'month' ? '/ mois' : '/ an' }}</div>
        @if(count($features))
          <ul style="margin-top:12px; padding-left:16px; font-size:12px; color:var(--muted); list-style:disc;">
            @foreach($features as $feature)
              <li style="margin-bottom:4px;">{{ $feature }}</li>
            @endforeach
          </ul>
        @endif
        @if(!$sub->is_active)
          <div style="margin-top:12px; font-size:11px; color:var(--red);">Ce forfait est inactif.</div>
        @endif
      </div>
    @empty
      <div class="plan-card" style="grid-column:1/-1; text-align:center; color:var(--muted);">
        <h3>Aucun forfait configuré</h3>
        <div class="amt">—</div>
        <div class="sub">Créez un abonnement depuis la gestion.</div>
      </div>
    @endforelse
  </div>
  <div class="panel">
    <div class="panel-head"><div><h2>Factures récentes</h2><p>Derniers paiements traités</p></div><span class="link">Tout voir →</span></div>
    <div class="table-wrap">
      <table class="data">
        <tr><th>Client</th><th>Montant</th><th>Date</th><th>Statut</th></tr>
        <tr><td>Ghostroar Digital</td><td>79,00 €</td><td>01/11/2026</td><td><span class="pill g">Payée</span></td></tr>
        <tr><td>Atelier Numa</td><td>9,00 €</td><td>03/11/2026</td><td><span class="pill g">Payée</span></td></tr>
        <tr><td>Agence Verso</td><td>79,00 €</td><td>05/11/2026</td><td><span class="pill r">Échouée</span></td></tr>
      </table>
    </div>
  </div>
</section>

<!-- SUPPORT -->
<section class="page" id="page-support" data-page="support">
  <div class="page-head"><div><h1>Support</h1><p>Tickets ouverts par les clients de la plateforme.</p></div></div>
  <div class="ticket"><div class="av">LS</div><div class="body"><div class="t">Lumio Studio — Reconnexion Meta impossible</div><div class="s">« Le bouton reconnecter renvoie une erreur 403… » — il y a 2 h</div></div><span class="pill o">Ouvert</span></div>
  <div class="ticket"><div class="av">AN</div><div class="body"><div class="t">Atelier Numa — Question sur les quotas</div><div class="s">« Puis-je passer au Pack Pro en cours de mois ? » — il y a 5 h</div></div><span class="pill v">En cours</span></div>
  <div class="ticket"><div class="av">GD</div><div class="body"><div class="t">Ghostroar Digital — Visuel non généré</div><div class="s">« Le studio des publications reste bloqué sur "génération…" » — hier</div></div><span class="pill g">Résolu</span></div>
</section>

<!-- SECURITE -->
<section class="page" id="page-securite" data-page="securite">
  <div class="page-head"><div><h1>Sécurité & Logs</h1><p>Historique des accès et événements sensibles.</p></div></div>
  <div class="panel">
    <div class="table-wrap">
      <table class="data">
        <tr><th>Événement</th><th>Compte</th><th>IP</th><th>Date</th></tr>
        <tr><td>Connexion admin réussie</td><td>Super Admin</td><td>154.72.10.4</td><td>11/11/2026 09:12</td></tr>
        <tr><td>Jeton Meta renouvelé</td><td>Ghostroar Digital</td><td>—</td><td>11/11/2026 08:40</td></tr>
        <tr><td>3 échecs de connexion</td><td>agence.verso@mail.fr</td><td>88.14.201.9</td><td>10/11/2026 22:05</td></tr>
        <tr><td>Export de données RGPD</td><td>Super Admin</td><td>154.72.10.4</td><td>09/11/2026 16:30</td></tr>
      </table>
    </div>
  </div>
</section>

<!-- SYSTEME -->
<section class="page" id="page-systeme" data-page="systeme">
  <div class="page-head"><div><h1>Paramètres système</h1><p>Réglages globaux de la plateforme IA Agent Studio.</p></div></div>
  <div class="toggle-row"><div><div class="lbl">Mode maintenance</div><div class="sub">Bloque l'accès client, laisse l'admin connecté</div></div><div class="switch" data-switch><i></i></div></div>
  <div class="toggle-row"><div><div class="lbl">Nouvelles inscriptions</div><div class="sub">Autoriser la création de nouveaux comptes</div></div><div class="switch on" data-switch><i></i></div></div>
  <div class="toggle-row"><div><div class="lbl">Mode sandbox Meta par défaut</div><div class="sub">Pour tous les nouveaux comptes créés</div></div><div class="switch on" data-switch><i></i></div></div>
  <div class="toggle-row"><div><div class="lbl">Alertes de sécurité par e-mail</div><div class="sub">Envoyées à l'équipe admin</div></div><div class="switch on" data-switch><i></i></div></div>
</section>
@endsection

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function(){
  var active = '{{ $activeSection ?? "overview" }}';
  document.querySelectorAll('.page').forEach(function(p){ p.classList.remove('active'); });
  var page = document.getElementById('page-' + active);
  if(page){ page.classList.add('active'); }
  document.querySelectorAll('.nav-item').forEach(function(n){ n.classList.remove('active'); });
  var nav = document.querySelector('.nav-item[data-page="' + active + '"]');
  if(nav){ nav.classList.add('active'); }
});
</script>
@endpush