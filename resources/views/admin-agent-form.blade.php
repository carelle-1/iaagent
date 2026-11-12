@extends('layouts.admin')

@section('title', $agent ? 'Modifier l\'agent' : 'Nouvel agent')

@section('content')
<div class="page-head">
  <div>
    <h1>{{ $agent ? 'Modifier l\'agent' : 'Nouvel agent' }}</h1>
    <p>{{ $agent ? 'Modifiez les détails de l\'agent.' : 'Créez un nouvel agent IA.' }}</p>
  </div>
  <a href="{{ route('admin.agents.index') }}" class="btn secondary"><svg class="ico"><use href="#i-arrow-left"/></svg> Retour</a>
</div>

@if($errors->any())
  <div class="errors">
    <strong>Erreurs :</strong>
    <ul>
      @foreach($errors->all() as $error)
        <li>{{ $error }}</li>
      @endforeach
    </ul>
  </div>
@endif

<div class="panel">
  <form method="POST" action="{{ $agent ? route('admin.agents.update', $agent->id) : route('admin.agents.store') }}">
    @csrf
    @if($agent) @method('PUT') @endif

    <div class="form-group col-full">
      <label>Nom de l'agent <small>ex: Mira, Support Bot</small></label>
      <input type="text" name="name" value="{{ old('name', $agent ? $agent->name : '') }}" required placeholder="Mira">
    </div>

    <div class="form-group col-full">
      <label>Description</label>
      <textarea name="description" rows="4" placeholder="Description de l'agent...">{{ old('description', $agent ? $agent->description : '') }}</textarea>
      <div class="help">Description affichée dans la liste des agents.</div>
    </div>

    <div class="form-group col-full">
      <label>Abonnements autorisés <small>Sélectionnez les forfaits qui peuvent utiliser cet agent</small></label>
      @if($subscriptions->count())
        <div style="display:flex; flex-wrap:wrap; gap:10px;">
          @foreach($subscriptions as $sub)
            <label style="display:flex; align-items:center; gap:8px; background:var(--panel); border:1px solid var(--border); border-radius:8px; padding:10px 14px; cursor:pointer; font-size:13.5px;">
              <input type="checkbox" name="subscription_ids[]" value="{{ $sub->id }}" {{ (old('subscription_ids', $agent ? $agent->subscriptions->pluck('id')->toArray() : []) && in_array($sub->id, old('subscription_ids', $agent ? $agent->subscriptions->pluck('id')->toArray() : []))) ? 'checked' : '' }}>
              <span style="font-weight:600;">{{ $sub->name }}</span>
              <span style="color:var(--muted); font-size:12px; font-weight:400;">{{ number_format($sub->price, 0, ',', ' ') }} {{ $sub->currency }}/{{ $sub->interval === 'month' ? 'mois' : 'an' }}</span>
            </label>
          @endforeach
        </div>
      @else
        <p style="color:var(--muted);">Aucun abonnement actif disponible. Créez d'abord des abonnements dans <a href="{{ route('admin.subscriptions.index') }}" style="color:var(--cyan);">Facturation</a>.</p>
      @endif
    </div>

    <div class="form-group col-full checkbox-row">
      <input type="checkbox" name="is_active" value="1" {{ old('is_active', $agent ? $agent->is_active : true) ? 'checked' : '' }} id="is_active">
      <label for="is_active" style="margin:0; font-weight:600;">Agent actif</label>
    </div>

    <div style="display:flex; gap:10px; margin-top:24px;">
      <button type="submit" class="btn primary">{{ $agent ? 'Enregistrer les modifications' : 'Créer l\'agent' }}</button>
      <a href="{{ route('admin.agents.index') }}" class="btn secondary">Annuler</a>
    </div>
  </form>
</div>
@endsection