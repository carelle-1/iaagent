@extends('layouts.admin')

@section('title', $category->exists ? 'Modifier la catégorie' : 'Nouvelle catégorie')

@section('content')
<div class="page-head">
  <div>
    <h1>{{ $category->exists ? 'Modifier la catégorie' : 'Nouvelle catégorie' }}</h1>
    <p>{{ $category->exists ? 'Modifiez les détails de la catégorie.' : 'Créez une nouvelle catégorie pour organiser vos agents.' }}</p>
  </div>
  <a href="{{ route('admin.agent-categories.index') }}" class="btn secondary"><x-lucide-arrow-left class="ico" /> Retour</a>
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
  <form method="POST" action="{{ $category->exists ? route('admin.agent-categories.update', $category->id) : route('admin.agent-categories.store') }}">
    @csrf
    @if($category->exists) @method('PUT') @endif

    <div class="form-group col-full">
      <label>Nom de la catégorie <small>ex: Support, Marketing, Développement</small></label>
      <input type="text" name="name" value="{{ old('name', $category->name) }}" required placeholder="Support">
    </div>

    <div class="form-group col-full">
      <label>Description</label>
      <textarea name="description" rows="4" placeholder="Description de la catégorie...">{{ old('description', $category->description) }}</textarea>
      <div class="help">Description affichée dans la liste des catégories.</div>
    </div>

    <div class="form-group">
      <label>Ordre d'affichage</label>
      <input type="number" name="sort_order" value="{{ old('sort_order', $category->sort_order ?? 0) }}" min="0" step="1" style="width:100%;">
      <div class="help">Les catégories avec un ordre plus bas apparaissent en premier.</div>
    </div>

    <div class="form-group col-full checkbox-row">
      <input type="checkbox" name="is_active" value="1" {{ old('is_active', $category->is_active ?? true) ? 'checked' : '' }} id="is_active">
      <label for="is_active" style="margin:0; font-weight:600;">Catégorie active</label>
    </div>

    <div style="display:flex; gap:10px; margin-top:24px;">
      <button type="submit" class="btn primary">{{ $category->exists ? 'Enregistrer les modifications' : 'Créer la catégorie' }}</button>
      <a href="{{ route('admin.agent-categories.index') }}" class="btn secondary">Annuler</a>
    </div>
  </form>
</div>
@endsection