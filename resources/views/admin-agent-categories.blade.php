@extends('layouts.admin')

@section('title', 'Catégories d\'agents')

@section('content')
<div class="page-head">
  <div>
    <h1>Catégories d'agents</h1>
    <p>Organisez vos agents IA par catégories pour une meilleure gestion.</p>
  </div>
  <a href="{{ route('admin.agent-categories.create') }}" class="btn primary"><x-lucide-plus class="ico" /> Nouvelle catégorie</a>
</div>

@if(session('success'))
  <div class="alert success">{{ session('success') }}</div>
@endif
@if(session('error'))
  <div class="alert error">{{ session('error') }}</div>
@endif

<div class="panel">
  <div class="panel-head"><div><h2>Liste des catégories</h2><p>Catégories configurées pour organiser les agents.</p></div></div>
  <div class="table-wrap">
    <table class="data">
      <tr><th>Catégorie</th><th>Description</th><th>Ordre</th><th>Statut</th><th>Agents</th><th></th></tr>
      @forelse($categories as $category)
        <tr>
          <td>
            <div style="font-weight:600;">{{ $category->name }}</div>
          </td>
          <td style="color:var(--muted); max-width:300px;">{{ $category->description ?? '—' }}</td>
          <td>{{ $category->sort_order }}</td>
          <td><span class="pill {{ $category->is_active ? 'g' : 'r' }}">{{ $category->is_active ? 'Actif' : 'Inactif' }}</span></td>
          <td>
            <span class="pill v">{{ $category->agents_count ?? $category->agents->count() }}</span>
          </td>
          <td class="row-actions">
            <a href="{{ route('admin.agent-categories.show', $category->id) }}" class="icbtn" title="Voir"><x-lucide-eye class="ico" /></a>
            <a href="{{ route('admin.agent-categories.edit', $category->id) }}" class="icbtn" title="Modifier"><x-lucide-edit class="ico" /></a>
            <form action="{{ route('admin.agent-categories.destroy', $category->id) }}" method="POST" style="display:inline;" onsubmit="return confirm('Supprimer cette catégorie ? Les agents associés seront dissociés.');">
              @csrf @method('DELETE')
              <button type="submit" class="icbtn btn-danger" title="Supprimer"><x-lucide-trash-2 class="ico" /></button>
            </form>
          </td>
        </tr>
      @empty
        <tr><td colspan="6" style="color:var(--muted);padding:18px;">Aucune catégorie configurée. Cliquez sur "Nouvelle catégorie" pour en ajouter une.</td></tr>
      @endforelse
    </table>
  </div>
</div>
@endsection