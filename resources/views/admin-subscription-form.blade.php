@extends('layouts.admin')

@section('title', $subscription ? 'Modifier l\'abonnement' : 'Nouvel abonnement')

@section('content')
<div class="page-head">
  <div>
    <h1>{{ $subscription ? 'Modifier l\'abonnement' : 'Nouvel abonnement' }}</h1>
    <p>{{ $subscription ? 'Modifiez les détails du forfait existant.' : 'Créez un nouveau forfait d\'abonnement pour la plateforme.' }}</p>
  </div>
  <a href="{{ route('admin.subscriptions.index') }}" class="btn secondary"><x-lucide-arrow-left class="ico" /> Retour</a>
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
  <form method="POST" action="{{ $subscription ? route('admin.subscriptions.update', $subscription->id) : route('admin.subscriptions.store') }}">
    @csrf
    @if($subscription) @method('PUT') @endif

    <div class="form-group col-full">
      <label>Nom du forfait <small>ex: Starter, Pro, Premium</small></label>
      <input type="text" name="name" value="{{ old('name', $subscription ? $subscription->name : '') }}" required placeholder="Starter">
    </div>

    <div class="form-group col-full">
      <label>Slug (identifiant unique) <small>ex: starter, pro, premium</small></label>
      <input type="text" name="slug" value="{{ old('slug', $subscription ? $subscription->slug : '') }}" required placeholder="starter">
    </div>

    <div class="form-group col-full">
      <label>Description</label>
      <textarea name="description" placeholder="Brève description du forfait...">{{ old('description', $subscription ? $subscription->description : '') }}</textarea>
      <div class="help">Description affichée aux clients sur la page de tarification.</div>
    </div>

    <div class="row">
      <div class="form-group col-2">
        <label>Prix</label>
        <input type="number" name="price" step="0.01" min="0" value="{{ old('price', $subscription ? $subscription->price : 0) }}" required placeholder="0.00">
      </div>
      <div class="form-group col-2">
        <label>Devise</label>
        <select name="currency">
          <option value="XOF" {{ old('currency', $subscription ? $subscription->currency : 'XOF') === 'XOF' ? 'selected' : '' }}>XOF (FCFA)</option>
          <option value="EUR" {{ old('currency', $subscription ? $subscription->currency : 'XOF') === 'EUR' ? 'selected' : '' }}>EUR (€)</option>
          <option value="USD" {{ old('currency', $subscription ? $subscription->currency : 'XOF') === 'USD' ? 'selected' : '' }}>USD ($)</option>
        </select>
      </div>
      <div class="form-group col-2">
        <label>Intervalle</label>
        <select name="interval">
          <option value="month" {{ old('interval', $subscription ? $subscription->interval : 'month') === 'month' ? 'selected' : '' }}>Mensuel</option>
          <option value="year" {{ old('interval', $subscription ? $subscription->interval : 'month') === 'year' ? 'selected' : '' }}>Annuel</option>
        </select>
      </div>
      <div class="form-group col-2">
        <label>Ordre d'affichage</label>
        <input type="number" name="sort_order" min="0" value="{{ old('sort_order', $subscription ? $subscription->sort_order : 0) }}" placeholder="0">
      </div>
    </div>

    <div class="form-group col-full">
      <label>Avantages (un par ligne) <small>Chaque ligne = un avantage affiché à la clientèle</small></label>
      <textarea name="features" rows="6" placeholder="Accès illimité aux générations IA
Support prioritaire
Export de données
Plusieurs comptes Meta">{{ old('features', $subscription && $subscription->features ? implode("\n", $subscription->features) : '') }}</textarea>
      <div class="help">Saisissez un avantage par ligne. Ils seront listés sous le forfait.</div>
    </div>

    <div class="form-group col-full checkbox-row">
      <input type="checkbox" name="is_active" value="1" {{ old('is_active', $subscription ? $subscription->is_active : true) ? 'checked' : '' }} id="is_active">
      <label for="is_active" style="margin:0; font-weight:600;">Forfait actif (visible par les clients)</label>
    </div>

    <div style="display:flex; gap:10px; margin-top:24px;">
      <button type="submit" class="btn primary">{{ $subscription ? 'Enregistrer les modifications' : 'Créer l\'abonnement' }}</button>
      <a href="{{ route('admin.subscriptions.index') }}" class="btn secondary">Annuler</a>
    </div>
  </form>
</div>
@endsection


