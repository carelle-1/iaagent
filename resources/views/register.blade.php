<!DOCTYPE html>
<html lang="fr">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1, viewport-fit=cover">
  <title>IA Agent Studio — Inscription</title>
  <meta name="csrf-token" content="{{ csrf_token() }}">
  <link rel="preconnect" href="https://fonts.googleapis.com">
  <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
  <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&display=swap" rel="stylesheet">
  <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/sweetalert2@11/dist/sweetalert2.min.css">
<style>
:root{
  --bg:#07070a; --panel:#111116; --panel-2:#0c0c11; --border:#1e1e27; --border-2:#2a2a36;
  --text:#f2f2f6; --muted:#9a9aab; --muted-2:#6c6c7c;
  --cyan:#2bc7e8; --violet:#8b7bf0; --pink:#ec5fa0; --green:#34c27a; --red:#e85a5a;
}
html{box-sizing:border-box; scroll-padding-top:env(safe-area-inset-top,0px);}
*,*::before,*::after{box-sizing:inherit;}
body{margin:0; min-height:100vh; background:var(--bg); color:var(--text); font-family:'Inter',system-ui,sans-serif;
  padding-top:env(safe-area-inset-top,0px); padding-bottom:env(safe-area-inset-bottom,0px); -webkit-font-smoothing:antialiased; overflow:hidden;}
button,input{font-family:inherit;}
a{color:var(--cyan); text-decoration:none;}

.form-side{min-height:100vh; display:flex; align-items:center; justify-content:center; padding:40px 24px;}
.form-card{width:100%; max-width:400px;}

.form-card h1{font-size:22px; margin:0 0 6px; text-align:center;}
.field-row{display:flex; gap:16px;}
.field-row .field{flex:1; margin-bottom:10px;}
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

.strength{height:4px; border-radius:3px; background:#222230; margin-top:8px; overflow:hidden;}
.strength i{display:block; height:100%; width:0%; background:var(--red); transition:width .2s, background .2s;}

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
  .field-row{flex-direction:column; gap:0;}
}
</style>
</head>
<body>

  <div class="form-side">
    <div class="form-card">
      <!-- SIGNUP -->
      <form id="signupForm" novalidate>
        <h1>Créer votre compte</h1>

        <div class="field-row">
          <div class="field" id="suFirstNameField">
            <label>Prénom</label>
            <input type="text" id="suFirstName" name="first_name" placeholder="Carelle" autocomplete="given-name">
            <div class="err">Entrez votre prénom.</div>
          </div>
          <div class="field" id="suLastNameField">
            <label>Nom</label>
            <input type="text" id="suLastName" name="last_name" placeholder="Carelle" autocomplete="family-name">
            <div class="err">Entrez votre nom.</div>
          </div>
        </div>

        <div class="field" id="suCompanyField">
          <label>Entreprise <span style="color:var(--muted-2);font-weight:400">(optionnel)</span></label>
          <input type="text" id="suCompany" name="company" placeholder="Ghostroar Digital" autocomplete="organization">
          <div class="err">Entrez le nom de votre entreprise.</div>
        </div>

        <div class="field" id="suEmailField">
          <label>Adresse e-mail</label>
          <input type="email" id="suEmail" name="email" placeholder="vous@entreprise.com" autocomplete="email">
          <div class="err">Entrez une adresse e-mail valide.</div>
        </div>

        <div class="field" id="suPwField">
          <label>Mot de passe</label>
          <div class="pw-wrap">
            <input type="password" id="suPw" name="password" placeholder="8 caractères minimum" autocomplete="new-password">
            <button type="button" class="pw-toggle" data-target="suPw">Afficher</button>
          </div>
          <div class="strength"><i id="strengthBar"></i></div>
          <div class="hint" id="strengthLabel">Utilisez au moins 8 caractères, un chiffre et une majuscule.</div>
          <div class="err">Le mot de passe doit contenir au moins 8 caractères.</div>
        </div>

        <div class="field" id="suPwConfirmField">
          <label>Confirmer le mot de passe</label>
          <input type="password" id="suPwConfirm" name="password_confirmation" placeholder="••••••••" autocomplete="new-password">
          <div class="err">Les mots de passe ne correspondent pas.</div>
        </div>

        <div class="field" id="suTermsField" style="margin-bottom:22px;">
          <label class="checkline"><input type="checkbox" id="suTerms" name="terms"> J'accepte les conditions d'utilisation</label>
          <div class="err">Vous devez accepter les conditions d'utilisation.</div>
        </div>

        <button class="btn-main" type="submit" id="signupBtn">Créer mon compte</button>
        <div class="switch-line">Déjà un compte ? <a href="/login">Se connecter</a></div>
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

// Password strength
var suPw = document.getElementById('suPw');
suPw.addEventListener('input', function(){
  var v = suPw.value; var score = 0;
  if(v.length >= 8) score++;
  if(/[A-Z]/.test(v)) score++;
  if(/[0-9]/.test(v)) score++;
  if(/[^A-Za-z0-9]/.test(v)) score++;
  var pct = (score/4)*100;
  var bar = document.getElementById('strengthBar');
  var label = document.getElementById('strengthLabel');
  bar.style.width = pct + '%';
  var colors = ['#e85a5a','#e85a5a','#f0a93c','#34c27a','#34c27a'];
  bar.style.background = colors[score];
  var texts = ['Trop faible','Faible','Moyen','Bon','Excellent'];
  label.textContent = v.length ? 'Force du mot de passe : ' + texts[score] : 'Utilisez au moins 8 caractères, un chiffre et une majuscule.';
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

document.getElementById('signupForm').addEventListener('submit', async function(e){
  e.preventDefault();

  var okFirstName = markField('suFirstNameField', document.getElementById('suFirstName').value.trim().length > 0);
  var okLastName = markField('suLastNameField', document.getElementById('suLastName').value.trim().length > 0);
  var companyVal = document.getElementById('suCompany').value.trim();
  var okCompany = markField('suCompanyField', true);
  var okEmail = markField('suEmailField', validEmail(document.getElementById('suEmail').value));
  var pwVal = document.getElementById('suPw').value;
  var okPw = markField('suPwField', pwVal.length >= 8);
  var okConfirm = markField('suPwConfirmField', pwVal === document.getElementById('suPwConfirm').value && pwVal.length > 0);
  var termsChecked = document.getElementById('suTerms').checked;
  markField('suTermsField', termsChecked);

  if(!okFirstName || !okLastName || !okCompany || !okEmail || !okPw || !okConfirm || !termsChecked){
    notify('Veuillez corriger les champs en rouge.', 'error');
    return;
  }

  var btn = document.getElementById('signupBtn');
  var loader = document.getElementById('loaderOverlay');
  btn.disabled = true;
  btn.textContent = 'Création...';
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
    if(!formData.get('company')) formData.set('company', '');
    const response = await fetch('/register', {
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

    if(response.ok && data.success){
      notify(data.message, 'success');
      setTimeout(() => window.location.href = data.redirect, 1000);
      return;
    } else {
      if(data.errors){
        Object.keys(data.errors).forEach(field => {
          const fieldMap = {
            'first_name': 'suFirstNameField',
            'last_name': 'suLastNameField',
            'company': 'suCompanyField',
            'email': 'suEmailField',
            'password': 'suPwField',
            'terms': 'suTermsField'
          };
          const fieldId = fieldMap[field];
          if(fieldId){
            const el = document.getElementById(fieldId);
            if(el){
              el.classList.add('invalid');
              const errEl = el.querySelector('.err');
              if(errEl) errEl.textContent = data.errors[field][0];
            }
          }
        });
      }
      notify(data.message || 'Erreur lors de la création du compte', 'error');
    }
  } catch(err) {
    notify('Erreur de connexion au serveur', 'error');
  } finally {
    btn.disabled = false;
    btn.textContent = 'Créer mon compte';
    loader.classList.remove('show');
  }
});
</script>
</body>
</html>