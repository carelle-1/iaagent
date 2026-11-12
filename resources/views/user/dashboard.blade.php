<!DOCTYPE html>
<html lang="fr">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1, viewport-fit=cover">
<title>IA Agent Studio — Tableau de bord utilisateur</title>
<meta name="csrf-token" content="{{ csrf_token() }}">
<link rel="preconnect" href="https://fonts.googleapis.com">
<link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
<link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&display=swap" rel="stylesheet">
<style>
:root{
  --bg:#07070b; --panel:#101016; --panel-2:#0c0c12; --border:#1f1f29;
  --text:#f2f2f6; --muted:#9a9aab; --muted-2:#6f6f80;
  --violet:#8b7bf0; --cyan:#35d4e8; --cyan-solid:#22c7e8;
  --pill-bg:rgba(139,123,240,.12); --pill-border:rgba(139,123,240,.35);
  --green:#34c27a; --red:#e85a5a; --orange:#f0a93c;
}
*{box-sizing:border-box; margin:0; padding:0;}
body{
  margin:0; background:var(--bg); color:var(--text);
  font-family:'Inter',system-ui,-apple-system,sans-serif;
  -webkit-font-smoothing:antialiased;
  min-height:100vh;
}
.wrap{max-width:1200px; margin:0 auto; padding:0 24px;}
a{color:var(--cyan); text-decoration:none;}
.btn{display:inline-flex; align-items:center; justify-content:center; font-weight:600; font-size:14px; border-radius:10px; cursor:pointer; border:1px solid transparent; text-decoration:none; white-space:nowrap;}
.btn-primary{background:var(--cyan-solid); color:#031015; padding:10px 18px;}
.btn-primary:hover{filter:brightness(1.08);}
.btn-outline{background:transparent; color:var(--text); border-color:var(--border); padding:10px 18px;}
.btn-outline:hover{border-color:#3a3a4a;}
.btn-danger{background:var(--red); color:#fff; padding:10px 18px;}
.btn-danger:hover{filter:brightness(1.08);}

/* Header */
header{border-bottom:1px solid var(--border); background:rgba(7,7,11,.85); backdrop-filter:blur(10px); position:sticky; top:0; z-index:20;}
.navbar{display:flex; align-items:center; justify-content:space-between; padding:16px 24px; max-width:1200px; margin:0 auto;}
.brand{display:flex; align-items:center; gap:10px; font-weight:800; font-size:17px;}
.brand img{height:36px; width:auto;}
nav.links{display:flex; align-items:center; gap:24px;}
nav.links a{font-size:14px; color:#cfcfe0;}

/* Main */
main{padding:40px 0 60px;}
.page-header{display:flex; align-items:center; justify-content:space-between; margin-bottom:32px; flex-wrap:wrap; gap:16px;}
.page-header h1{font-size:28px; font-weight:800; letter-spacing:-.01em;}
.user-badge{display:inline-flex; align-items:center; gap:8px; font-size:12px; font-weight:600; color:#7fe0b4; background:rgba(60,200,140,.12); border:1px solid rgba(60,200,140,.35); padding:6px 12px; border-radius:20px;}

/* Stats Grid */
.stats-grid{display:grid; grid-template-columns:repeat(auto-fit, minmax(200px, 1fr)); gap:18px; margin-bottom:32px;}
.stat-card{background:var(--panel); border:1px solid var(--border); border-radius:14px; padding:24px;}
.stat-card .label{font-size:12.5px; color:var(--muted); text-transform:uppercase; letter-spacing:.06em; margin-bottom:8px;}
.stat-card .value{font-size:32px; font-weight:800; color:var(--text);}
.stat-card .value.cyan{color:var(--cyan);}
.stat-card .value.violet{color:var(--violet);}

/* Sections */
.section{background:var(--panel); border:1px solid var(--border); border-radius:16px; padding:28px; margin-bottom:24px;}
.section-header{display:flex; align-items:center; justify-content:space-between; margin-bottom:20px; flex-wrap:wrap; gap:12px;}
.section-title{font-size:18px; font-weight:700;}
.section-empty{color:var(--muted); text-align:center; padding:40px 20px;}

/* Tables */
.table-wrap{overflow-x:auto;}
table{width:100%; border-collapse:collapse; font-size:14px;}
th,td{padding:14px 16px; text-align:left; border-bottom:1px solid var(--border);}
th{font-weight:600; color:var(--muted); font-size:12px; text-transform:uppercase; letter-spacing:.06em; background:var(--panel-2);}
tr:last-child td{border-bottom:none;}
tr:hover td{background:var(--panel-2);}
.status{display:inline-flex; align-items:center; gap:6px; font-size:12.5px; font-weight:600; padding:4px 10px; border-radius:20px;}
.status.pending{color:#f0a93c; background:rgba(240,169,60,.12); border:1px solid rgba(240,169,60,.35);}
.status.validated{color:#7fe0b4; background:rgba(60,200,140,.12); border:1px solid rgba(60,200,140,.35);}
.status.published{color:#8b7bf0; background:rgba(139,123,240,.12); border:1px solid rgba(139,123,240,.35);}
.status.rejected{color:#e85a5a; background:rgba(232,90,90,.12); border:1px solid rgba(232,90,90,.35);}
.status.draft{color:var(--muted); background:var(--panel-2); border:1px solid var(--border);}

/* Actions */
.actions{display:flex; gap:8px;}
.btn-sm{padding:6px 12px; font-size:12.5px;}

/* Modal */
.modal-overlay{position:fixed; inset:0; background:rgba(0,0,0,.7); backdrop-filter:blur(4px); z-index:100; display:none; align-items:center; justify-content:center; padding:24px;}
.modal-overlay.show{display:flex;}
.modal{background:var(--panel); border:1px solid var(--border); border-radius:16px; width:100%; max-width:500px; max-height:90vh; overflow:auto;}
.modal-header{display:flex; align-items:center; justify-content:space-between; padding:20px 24px; border-bottom:1px solid var(--border);}
.modal-header h2{font-size:18px; font-weight:700;}
.modal-close{background:none; border:none; color:var(--muted); font-size:20px; cursor:pointer; line-height:1;}
.modal-body{padding:24px;}
.modal-footer{display:flex; justify-content:flex-end; gap:12px; padding:16px 24px; border-top:1px solid var(--border);}

/* Form */
.field{margin-bottom:16px;}
.field label{display:block; font-size:13px; color:var(--muted); margin-bottom:6px;}
.field input, .field textarea, .field select{width:100%; background:var(--panel-2); border:1px solid var(--border); color:var(--text); border-radius:10px; padding:11px 13px; font-size:14px; outline:none; font-family:inherit;}
.field input:focus, .field textarea:focus, .field select:focus{border-color:var(--cyan);}
.field .err{font-size:11.5px; color:var(--red); margin-top:5px; display:none;}
.field.invalid input, .field.invalid textarea, .field.invalid select{border-color:var(--red);}
.field.invalid .err{display:block;}

/* Toast */
.toast{position:fixed; bottom:24px; left:50%; transform:translateX(-50%) translateY(20px); background:var(--panel); border:1px solid var(--border); border-left:3px solid var(--green); padding:13px 20px; border-radius:10px; font-size:13.5px; opacity:0; pointer-events:none; transition:all .25s; z-index:99;}
.toast.show{opacity:1; transform:translateX(-50%) translateY(0);}

@media (max-width:768px){
  .navbar{padding:16px;}
  nav.links{display:none;}
  .page-header{flex-direction:column; align-items:flex-start;}
  .stats-grid{grid-template-columns:1fr;}
}
</style>
</head>
<body>

<header>
  <div class="navbar">
    <div class="brand">
      <img src="/assets/1.png" alt="IA Agent Studio">
      <span class="brand-tag">SaaS · Agents IA</span>
    </div>
    <nav class="links">
      <a href="/user/dashboard">Tableau de bord</a>
      <a href="/user/publications">Mes publications</a>
      <a href="/user/calendar">Calendrier</a>
      <form action="/logout" method="POST" style="display:inline;">
        <input type="hidden" name="_token" value="{{ csrf_token() }}">
        <button type="submit" class="btn btn-outline" style="background:none; border:none; color:#cfcfe0; font:inherit; cursor:pointer; padding:0;">Déconnexion</button>
      </form>
    </nav>
  </div>
</header>

<main class="wrap">
  <div class="page-header">
    <h1>Tableau de bord</h1>
    <span class="user-badge">Utilisateur</span>
  </div>

  <div class="stats-grid">
    <div class="stat-card">
      <div class="label">Publications validées</div>
      <div class="value cyan">{{ $validatedCount ?? 0 }}</div>
    </div>
    <div class="stat-card">
      <div class="label">En attente</div>
      <div class="value violet">{{ $pendingCount ?? 0 }}</div>
    </div>
    <div class="stat-card">
      <div class="label">Publiées</div>
      <div class="value">{{ $publishedCount ?? 0 }}</div>
    </div>
    <div class="stat-card">
      <div class="label">Cette semaine</div>
      <div class="value">{{ $thisWeekCount ?? 0 }}</div>
    </div>
  </div>

  <section class="section">
    <div class="section-header">
      <h2 class="section-title">Prochaines publications</h2>
    </div>
    <div class="table-wrap">
      <table>
        <thead>
          <tr>
            <th>Date</th>
            <th>Page</th>
            <th>Contenu</th>
            <th>Statut</th>
            <th>Actions</th>
          </tr>
        </thead>
        <tbody>
          @forelse($upcomingPublications ?? [] as $pub)
          <tr>
            <td>{{ $pub->scheduled_at->format('d/m/Y H:i') }}</td>
            <td>{{ $pub->page->name ?? 'N/A' }}</td>
            <td style="max-width:300px; overflow:hidden; text-overflow:ellipsis; white-space:nowrap;">{{ $pub->content_preview ?? Str::limit($pub->content, 80) }}</td>
            <td><span class="status {{ $pub->status }}">{{ ucfirst($pub->status) }}</span></td>
            <td>
              <div class="actions">
                <a href="/user/publications/{{ $pub->id }}" class="btn btn-outline btn-sm">Voir</a>
                @if($pub->status === 'pending')
                <a href="/user/publications/{{ $pub->id }}/edit" class="btn btn-primary btn-sm">Modifier</a>
                @endif
              </div>
            </td>
          </tr>
          @empty
          <tr>
            <td colspan="5" class="section-empty">Aucune publication planifiée pour le moment.</td>
          </tr>
          @endforelse
        </tbody>
      </table>
    </div>
  </section>

  <section class="section">
    <div class="section-header">
      <h2 class="section-title">Historique récent</h2>
    </div>
    <div class="table-wrap">
      <table>
        <thead>
          <tr>
            <th>Date</th>
            <th>Page</th>
            <th>Contenu</th>
            <th>Statut</th>
          </tr>
        </thead>
        <tbody>
          @forelse($recentPublications ?? [] as $pub)
          <tr>
            <td>{{ $pub->published_at?->format('d/m/Y H:i') ?? $pub->scheduled_at->format('d/m/Y H:i') }}</td>
            <td>{{ $pub->page->name ?? 'N/A' }}</td>
            <td style="max-width:300px; overflow:hidden; text-overflow:ellipsis; white-space:nowrap;">{{ $pub->content_preview ?? Str::limit($pub->content, 80) }}</td>
            <td><span class="status {{ $pub->status }}">{{ ucfirst($pub->status) }}</span></td>
          </tr>
          @empty
          <tr>
            <td colspan="4" class="section-empty">Aucun historique pour le moment.</td>
          </tr>
          @endforelse
        </tbody>
      </table>
    </div>
  </section>
</main>

<div class="toast" id="toast"></div>

<script>
// Toast
function showToast(msg, type='success'){
  const t = document.getElementById('toast');
  t.textContent = msg;
  t.style.borderLeftColor = type === 'error' ? 'var(--red)' : 'var(--green)';
  t.classList.add('show');
  setTimeout(() => t.classList.remove('show'), 3200);
}

// Flash messages
@if(session('success'))
showToast('{{ session('success') }}');
@endif
@if(session('error'))
showToast('{{ session('error') }}', 'error');
@endif
</script>
</body>
</html>