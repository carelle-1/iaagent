<!DOCTYPE html>
<html lang="fr">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1, viewport-fit=cover">
  <title>IA Agent Studio — Connexion</title>
  <meta name="csrf-token" content="{{ csrf_token() }}">
  <link rel="preconnect" href="https://fonts.googleapis.com">
  <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
  <link href="https://fonts.googleapis.com/css2?family=Century+Gothic:wght@400;500;600;700;800&display=swap" rel="stylesheet">
  <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/sweetalert2@11/dist/sweetalert2.min.css">
<style>
:root{
  --bg:#07070a; --panel:#111116; --panel-2:#0c0c11; --border:#1e1e27; --border-2:#2a2a36;
  --text:#f2f2f6; --muted:#9a9aab; --muted-2:#6c6c7c;
  --cyan:#2bc7e8; --violet:#8b7bf0; --pink:#ec5fa0; --green:#34c27a; --red:#e85a5a;
}
html{box-sizing:border-box; scroll-padding-top:env(safe-area-inset-top,0px);}
*,*::before,*::after{box-sizing:inherit;}
body{margin:0; min-height:100vh; background:var(--bg); color:var(--text); font-family:'Century Gothic',system-ui,sans-serif;
  padding-top:env(safe-area-inset-top,0px); padding-bottom:env(safe-area-inset-bottom,0px); -webkit-font-smoothing:antialiased; overflow:hidden;}
button,input{font-family:inherit;}
a{color:var(--cyan); text-decoration:none;}

.form-side{min-height:100vh; display:flex; align-items:center; justify-content:center; padding:40px 24px;}
.form-card{width:100%; max-width:400px;}

.form-card h1{font-size:22px; margin:0 0 6px; text-align:center;}
.form-card .sub{font-size:13.5px; color:var(--muted); margin:0 0 26px;}

.field{margin-bottom:10px;}
.field label{display:block; font-size:13px; color:var(--muted); margin-bottom:6px;}
.field input{width:100%; background:var(--panel-2); border:1px solid var(--border-2); color:var(--text); border-radius:10px; padding:11px 13px; font-size:14px; outline:none;}
.field input:focus{border-color:var(--cyan);}
.field .hint{font-size:11.5px; color:var(--muted-2); margin-top:5px;}
.field .err{font-size:11.5px; color:var(--red); margin-top:5px; display:none;}
.field.invalid input{border-color:var(--red);}
.field.invalid .err{display:block;}

.pw-wrap{position:relative;}
.pw-toggle{position:absolute; right:10px; top:50%; transform:translateY(-50%); background:none; border:none; color:var(--muted); cursor:pointer; font-size:13px;}

.row-between{display:flex; justify-content:space-between; align-items:center; margin-bottom:22px; font-size:13px;}
.checkline{display:flex; align-items:center; gap:8px; color:var(--muted);}
.checkline input{accent-color:var(--cyan); width:15px; height:15px;}

.btn-main{width:100%; padding:13px 0; border:none; border-radius:10px; background:var(--cyan); color:#031015; font-weight:700; font-size:14.5px; cursor:pointer; margin-bottom:16px;}
.btn-main:hover{filter:brightness(1.06);}
.btn-main:disabled{opacity:0.6; cursor:not-allowed;}
.divider{display:flex; align-items:center; gap:12px; color:var(--muted-2); font-size:12px; margin:18px 0;}
.divider::before,.divider::after{content:""; flex:1; height:1px; background:var(--border);}
.btn-google{width:100%; display:flex; align-items:center; justify-content:center; gap:10px; padding:11px 0; border-radius:10px; border:1px solid var(--border-2); background:var(--panel-2); color:var(--text); font-size:13.5px; font-weight:600; cursor:pointer; margin-bottom:22px;}
.switch-line{text-align:center; font-size:13.5px; color:var(--muted);}

.loader-overlay{position:fixed; inset:0; background:rgba(7,7,10,.95); backdrop-filter:blur(8px); z-index:1000; display:none; align-items:center; justify-content:center;}
.swal2-popup{background:#111116 !important; color:#f2f2f6 !important; border:1px solid #2a2a36;}
.swal2-title,.swal2-html-container{color:#f2f2f6 !important;}
.swal2-timer-progress-bar{background:#2bc7e8 !important;}
.loader-overlay.show{display:flex;}
.loader-video{width:180px; height:180px; border-radius:16px; object-fit:cover; box-shadow:0 0 40px rgba(53,212,232,.3);}

@media (max-width:860px){
  .form-side{min-height:100vh;}
}
</style>
</head>
<body>

  <div class="form-side">
    <div class="form-card">
      <!-- LOGIN -->
      <form id="loginForm" novalidate>
        <h1>Connexion</h1>

        <div class="field" id="loginEmailField">
          <label>Adresse e-mail</label>
          <input type="email" id="loginEmail" name="email" placeholder="vous@entreprise.com" autocomplete="email">
          <div class="err">Entrez une adresse e-mail valide.</div>
        </div>

        <div class="field" id="loginPwField">
          <label>Mot de passe</label>
          <div class="pw-wrap">
            <input type="password" id="loginPw" name="password" placeholder="••••••••" autocomplete="current-password">
            <button type="button" class="pw-toggle" data-target="loginPw">Afficher</button>
          </div>
          <div class="err">Le mot de passe est requis.</div>
        </div>

        <div class="row-between">
          <label class="checkline"><input type="checkbox" name="remember" checked> Se souvenir de moi</label>
          <a href="#">Mot de passe oublié ?</a>
        </div>

        <button class="btn-main" type="submit" id="loginBtn">Se connecter</button>
        <div class="divider">ou</div>
        <button class="btn-google" type="button"><x-lucide-globe class="ico" style="width:16px;height:16px;" /> Continuer avec Google</button>
        <div class="switch-line">Pas encore de compte ? <a href="/register">Créer un compte</a></div>
      </form>
    </div>
  </div>

<!-- Loader Overlay -->
<div class="loader-overlay" id="loaderOverlay">
  <video src="/assets/mascotte5.mp4" autoplay loop muted playsinline class="loader-video"></video>
</div>

<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<script>
// Password show/hide
document.querySelectorAll('.pw-toggle').forEach(function(btn){
  btn.addEventListener('click', function(){
    var input = document.getElementById(btn.dataset.target);
    var hidden = input.type === 'password';
    input.type = hidden ? 'text' : 'password';
    btn.textContent = hidden ? 'Masquer' : 'Afficher';
  });
});

function notify(msg, type){
  var icon = type === 'error' ? 'error' : 'success';
  return Swal.fire({
    toast: true,
    position: 'bottom',
    icon: icon,
    title: msg,
    showConfirmButton: false,
    timer: type === 'error' ? 3500 : 1800,
    timerProgressBar: true,
    background: '#111116',
    color: '#f2f2f6'
  });
}

function validEmail(v){ return /^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(v); }
function markField(id, valid){
  var f = document.getElementById(id);
  f.classList.toggle('invalid', !valid);
  return valid;
}

function getCookie(name){
  var value = '; ' + document.cookie;
  var parts = value.split('; ' + name + '=');
  if(parts.length === 2) return parts.pop().split(';').shift();
  return null;
}

window.addEventListener('pageshow', function(e){ if(e.persisted) window.location.reload(); });

document.getElementById('loginForm').addEventListener('submit', async function(e){
  e.preventDefault();

  var okEmail = markField('loginEmailField', validEmail(document.getElementById('loginEmail').value));
  var okPw = markField('loginPwField', document.getElementById('loginPw').value.length > 0);

  if(!okEmail || !okPw) return;

  var btn = document.getElementById('loginBtn');
  var loader = document.getElementById('loaderOverlay');
  btn.disabled = true;
  btn.textContent = 'Connexion...';
  loader.classList.add('show');

  try {
    const csrfToken = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content')
        || decodeURIComponent(getCookie('XSRF-TOKEN') || '');

    if(!csrfToken){
      console.error('CSRF token not found');
      notify('Erreur de sécurité, veuillez rafraîchir la page', 'error');
      return;
    }

    const formData = new FormData(this);
    const response = await fetch('/login', {
      method: 'POST',
      headers: {
        'X-CSRF-TOKEN': csrfToken,
        'X-Requested-With': 'XMLHttpRequest',
        'Accept': 'application/json',
      },
      body: formData
    });

    let data = {};
    try { data = await response.json(); } catch(_) {}

    if(response.status === 419){
      notify('Session expirée, veuillez rafraîchir la page.', 'error');
      return;
    }

    if(response.ok && data.success){
      notify(data.message, 'success');
      setTimeout(() => window.location.href = data.redirect, 1000);
      return;
    } else {
      if(data.errors){
        Object.keys(data.errors).forEach(field => {
          const fieldId = field === 'email' ? 'loginEmailField' : 'loginPwField';
          const el = document.getElementById(fieldId);
          if(el){
            el.classList.add('invalid');
            var errEl = el.querySelector('.err');
            if(errEl) errEl.textContent = data.errors[field][0];
          }
        });
      }
      notify(data.message || 'Erreur de connexion', 'error');
    }
  } catch(err) {
    notify('Erreur de connexion au serveur', 'error');
  } finally {
    btn.disabled = false;
    btn.textContent = 'Se connecter';
    loader.classList.remove('show');
  }
});
</script>
</body>
</html>