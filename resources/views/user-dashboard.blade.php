<!DOCTYPE html>
<html lang="fr">
<head>
<script>(function(){try{var t=localStorage.getItem('theme')||'dark';document.documentElement.setAttribute('data-theme',t);}catch(e){}})();</script>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1, viewport-fit=cover">
<title>IA Agent Studio — Tableau de bord</title>
<meta name="csrf-token" content="{{ csrf_token() }}">
<link rel="preconnect" href="https://fonts.googleapis.com">
<link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
<link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&display=swap" rel="stylesheet">
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/sweetalert2@11/dist/sweetalert2.min.css">
<style>
:root{
  --bg:#07070a; --panel:#111116; --panel-2:#0c0c11; --border:#1e1e27; --border-2:#26262f;
  --text:#f2f2f6; --muted:#9a9aab; --muted-2:#6c6c7c;
  --cyan:#2bc7e8; --violet:#8b7bf0; --pink:#ec5fa0;
  --green:#34c27a; --orange:#f0a93c; --red:#e85a5a;
  --sidebar-w:260px;
  --nav-text:#cfcfe0; --nav-hover:#17171f; --subnav-text:#b8b8c8; --bar-bg:#222230;
  --topbar-bg:rgba(7,7,10,.9); --alert-bg:#1d1306; --alert-border:#4a330d; --alert-text:#c9ab82;
  --chip-req-bg:#3a1f06; --stat-hi-bg:#17110a; --stat-hi-border:#4a2a10; --av-bg:#3a3a46; --dropdown-hover:#1a1a23;
}
html[data-theme="light"]{
  --bg:#f4f6f9; --panel:#ffffff; --panel-2:#f0f2f6; --border:#e2e6ee; --border-2:#d6dbe6;
  --text:#121826; --muted:#5b6475; --muted-2:#7a8496;
  --nav-text:#334155; --nav-hover:#e8edf5; --subnav-text:#475569; --bar-bg:#dde3ee;
  --topbar-bg:rgba(244,246,249,.9); --alert-bg:#fff7ed; --alert-border:#fed7aa; --alert-text:#9a3412;
  --chip-req-bg:#ffedd5; --stat-hi-bg:#fff7ed; --stat-hi-border:#fed7aa; --av-bg:#cbd5e1; --dropdown-hover:#e8edf5;
}
html{box-sizing:border-box; scroll-padding-top:env(safe-area-inset-top,0px);}
*,*::before,*::after{box-sizing:inherit;}
body{margin:0; background:var(--bg); color:var(--text); font-family:'Inter',system-ui,sans-serif;
  padding-top:env(safe-area-inset-top,0px); padding-bottom:env(safe-area-inset-bottom,0px); -webkit-font-smoothing:antialiased; transition:background .2s,color .2s;}
button{font-family:inherit;}
a{color:inherit; text-decoration:none;}

.app{display:flex; min-height:100vh;}
.sidebar{
  width:var(--sidebar-w); flex-shrink:0; background:var(--panel-2); border-right:1px solid var(--border);
  display:flex; flex-direction:column; padding:18px 14px; position:fixed; top:0; bottom:0; left:0;
  overflow-x:hidden; overflow-y:auto; scrollbar-width:none; transition:transform .25s ease; z-index:50;
}
.sidebar::-webkit-scrollbar{display:none;}
.brand{display:flex; align-items:center; gap:9px; padding:8px 8px 20px; font-weight:800; font-size:16px;}
.brand .logo{width:26px; height:26px; border-radius:7px; background:linear-gradient(135deg,var(--cyan),var(--violet)); flex-shrink:0;}
.brand-logo-light{display:none !important;}
html[data-theme="light"] .brand-logo-dark{display:none !important;}
html[data-theme="light"] .brand-logo-light{display:block !important;}
.nav-item{display:flex; align-items:center; gap:11px; padding:10px 12px; border-radius:9px; font-size:14px; color:var(--nav-text); cursor:pointer; white-space:nowrap;}
.nav-item:hover{background:var(--nav-hover);}
.nav-item.active{background: #17B9FF; color:#FFF; font-weight:600;}
.sub .nav-item.active{background: #17B9FF; color:#FFF; font-weight:600;}
.nav-item .ic{width:17px; text-align:center; flex-shrink:0;}
.nav-item .chev{margin-left:auto; font-size:11px; opacity:.6;}
.sub{padding-left:10px; max-height:0; overflow:hidden; transition:max-height .22s ease;}
.sub.open{max-height:1000px;}
.sub#servicesSub.open,.sub#userAgentsSub.open,.sub.agent-tools.open{max-height:none;overflow:visible;}
.sub .nav-item{font-size:13.5px; padding-left:16px; color:var(--subnav-text); white-space:normal;}
.sub .sub{padding-left:8px;}
.nav-item.nav-toggle{width:100%;border:0;background:transparent;text-align:left;font-family:inherit;}
.agent-tools .nav-item{padding-left:16px;}
.sidebar-foot{margin-top:auto; background:var(--panel); border:1px solid var(--border); border-radius:12px; padding:14px; font-size:12.5px;}
.sidebar-foot .row{display:flex; justify-content:space-between; align-items:center; margin-bottom:8px; font-weight:600;}
.bar{height:5px; background:var(--bar-bg); border-radius:4px; overflow:hidden; margin-bottom:8px;}
.bar i{display:block; height:100%; width:4%; background:var(--orange);}
.sidebar-foot .meta{display:flex; justify-content:space-between; color:var(--muted); font-size:12px;}

.main{margin-left:var(--sidebar-w); flex:1; min-width:0;}
.topbar{display:flex; align-items:center; justify-content:flex-end; gap:16px; padding:14px 28px; border-bottom:1px solid var(--border); position:sticky; top:0; background:var(--topbar-bg); backdrop-filter:blur(8px); z-index:30;}
.menu-btn{display:none; background:var(--panel); border:1px solid var(--border); color:var(--text); width:36px; height:36px; border-radius:9px; align-items:center; justify-content:center; cursor:pointer; margin-right:auto;}
.icon-btn{width:36px; height:36px; border-radius:50%; background:var(--panel); border:1px solid var(--border); display:flex; align-items:center; justify-content:center; cursor:pointer; position:relative; color:var(--text);}
.dot-badge{position:absolute; top:-3px; right:-3px; background:var(--red); color:#fff; font-size:10px; border-radius:50%; width:16px; height:16px; display:flex; align-items:center; justify-content:center;}
.lang{display:flex; align-items:center; gap:6px; background:var(--panel); border:1px solid var(--border); border-radius:20px; padding:7px 14px; font-size:13px; cursor:pointer;}
.user-chip{display:flex; align-items:center; gap:9px; cursor:pointer;}
.user-menu{position:relative;}
.user-dropdown{position:absolute; right:0; top:calc(100% + 10px); background:var(--panel); border:1px solid var(--border-2); border-radius:12px; min-width:180px; padding:6px; display:none; box-shadow:0 12px 30px rgba(0,0,0,.45); z-index:60;}
.user-dropdown.open{display:block;}
.user-dropdown a,.user-dropdown button{display:flex; align-items:center; gap:8px; width:100%; padding:10px 12px; border:none; background:none; color:var(--text); font-size:13.5px; border-radius:8px; cursor:pointer; text-align:left;}
.user-dropdown a:hover,.user-dropdown button:hover{background:var(--dropdown-hover);}
.user-dropdown .danger{color:var(--red);}
.avatar{width:34px; height:34px; border-radius:50%; background:linear-gradient(135deg,var(--cyan),var(--violet)); display:flex; align-items:center; justify-content:center; font-weight:700; font-size:12px; color:#031015;}
.user-chip .name{font-size:13.5px; font-weight:600; line-height:1.2;}
.user-chip .plan{font-size:11.5px; color:var(--muted);}

.content{padding:26px 28px 60px;}
.page{display:none;}
.page.active{display:block;}

.hero-banner{border-radius:16px; padding:26px 28px; background:linear-gradient(110deg,#26b6e8 0%, #8f7bf0 55%, #ec5fa0 100%); display:flex; justify-content:space-between; gap:20px; flex-wrap:wrap; margin-bottom:18px;}
.hero-banner .eyebrow{display:flex; align-items:center; gap:10px; font-size:12px; font-weight:700; letter-spacing:.04em; color:rgba(255,255,255,.9); margin-bottom:14px;}
.hero-banner .eyebrow .tag{background:rgba(255,255,255,.2); padding:3px 10px; border-radius:20px;}
.hero-banner h1{margin:0 0 10px; font-size:26px; font-weight:800; color:#fff;}
.hero-banner p{margin:0; font-size:14px; color:rgba(255,255,255,.92); max-width:48ch; line-height:1.55;}
.hero-actions{display:flex; flex-direction:column; gap:10px; justify-content:center;}
.hbtn{display:inline-flex; align-items:center; gap:8px; justify-content:center; font-size:13.5px; font-weight:600; padding:10px 16px; border-radius:9px; white-space:nowrap; cursor:pointer; border:none;}
.hbtn.light{background:#fff; color:#111;}
.hbtn.ghost{background:rgba(255,255,255,.16); color:#fff; border:1px solid rgba(255,255,255,.3);}

.alert{display:flex; align-items:center; gap:14px; background:var(--alert-bg); border:1px solid var(--alert-border); border-radius:14px; padding:14px 18px; margin-bottom:22px; flex-wrap:wrap;}
.alert .ic{width:34px; height:34px; border-radius:50%; background:var(--orange); display:flex; align-items:center; justify-content:center; font-size:16px; flex-shrink:0;}
.alert .body{flex:1; min-width:220px;}
.alert .t1{font-weight:700; font-size:14px; display:flex; align-items:center; gap:10px; flex-wrap:wrap;}
.chip-req{background:var(--chip-req-bg); color:var(--orange); font-size:11px; padding:3px 9px; border-radius:20px; font-weight:600;}
.alert .t2{font-size:13px; color:var(--alert-text); margin-top:4px;}
.alert .act{background:var(--orange); color:#1d1306; font-weight:700; padding:10px 16px; border-radius:9px; font-size:13.5px; display:flex; align-items:center; gap:8px; white-space:nowrap; cursor:pointer;}

.stats{display:grid; grid-template-columns:repeat(5,1fr); gap:16px; margin-bottom:22px;}
.stat{background:var(--panel); border:1px solid var(--border); border-radius:14px; padding:18px;}
.stat.highlight{border-color:var(--stat-hi-border); background:var(--stat-hi-bg);}
.stat .num{font-size:26px; font-weight:800;}
.stat .lbl{font-size:12.5px; color:var(--muted); margin-top:4px;}
.stat.c-green .lbl{color:var(--green);}
.stat.c-orange .num,.stat.c-orange .lbl{color:var(--orange);}
.stat.c-violet .lbl{color:var(--violet);}
.stat.c-green2 .lbl{color:var(--green);}

.two-col{display:grid; grid-template-columns:1.6fr 1fr; gap:18px;}
.panel{background:var(--panel); border:1px solid var(--border); border-radius:16px; padding:22px;}
.panel-head{display:flex; justify-content:space-between; align-items:flex-start; margin-bottom:16px;}
.panel-head h2{margin:0 0 4px; font-size:16px; font-weight:700;}
.panel-head p{margin:0; font-size:13px; color:var(--muted);}
.panel-head .link{font-size:13px; color:var(--cyan); white-space:nowrap; cursor:pointer;}
.meta-row{display:flex; align-items:center; gap:14px; border:1px solid var(--border); border-radius:12px; padding:14px 16px; background:var(--panel-2);}
.meta-row .av{width:38px; height:38px; border-radius:50%; background:var(--av-bg); display:flex; align-items:center; justify-content:center; font-weight:700; font-size:13px;}
.meta-row .info{flex:1; min-width:0;}
.meta-row .nm{font-weight:600; font-size:14px; display:flex; align-items:center; gap:8px;}
.tag-fb{background:#2a3a66; color:#9db6ff; font-size:10.5px; font-weight:700; padding:2px 8px; border-radius:5px;}
.meta-row .sub{font-size:12.5px; color:var(--muted); margin-top:2px;}
.status-ok{display:flex; align-items:center; gap:6px; background:rgba(52,194,122,.12); color:var(--green); border:1px solid rgba(52,194,122,.3); padding:6px 12px; border-radius:20px; font-size:12.5px; font-weight:600; white-space:nowrap;}

.quick{display:flex; flex-direction:column; gap:12px; margin-bottom:16px;}
.qbtn{display:flex; align-items:center; gap:12px; background:var(--panel-2); border:1px solid var(--border); border-radius:12px; padding:13px 15px; cursor:pointer;}
.qbtn .ic{width:34px; height:34px; border-radius:9px; display:flex; align-items:center; justify-content:center; font-size:15px; flex-shrink:0;}
.qbtn.b1 .ic{background:#1b2440; color:#7ea6ff;}
.qbtn.b2 .ic{background:#2a1b40; color:#c79bff;}
.qbtn.b3 .ic{background:#133324; color:#4fd896;}
.qbtn span.lbl{font-size:13.5px; font-weight:600; flex:1;}
.qbtn .arr{color:var(--muted);}
.panel-foot{display:flex; justify-content:space-between; align-items:center; font-size:12.5px; color:var(--muted); border-top:1px solid var(--border); padding-top:14px;}
.panel-foot a{color:var(--cyan); font-weight:600;}

.page-head{margin-bottom:20px;}
.page-head h1{font-size:22px; margin:0 0 6px;}
.page-head p{margin:0; color:var(--muted); font-size:14px;}
.card-grid{display:grid; grid-template-columns:repeat(3,1fr); gap:16px;}
.simple-card{background:var(--panel); border:1px solid var(--border); border-radius:14px; padding:18px;}
.simple-card h3{margin:0 0 8px; font-size:14.5px;}
.simple-card p{margin:0; font-size:13px; color:var(--muted); line-height:1.5;}
table.data{width:100%; border-collapse:collapse; font-size:13.5px;}
table.data th{text-align:left; color:var(--muted); font-weight:600; font-size:12px; padding:10px 14px; border-bottom:1px solid var(--border);}
table.data td{padding:12px 14px; border-bottom:1px solid var(--border);}
.pill{padding:4px 10px; border-radius:20px; font-size:11.5px; font-weight:600;}
.pill.g{background:rgba(52,194,122,.12); color:var(--green);}
.pill.o{background:rgba(240,169,60,.12); color:var(--orange);}
.pill.v{background:rgba(139,123,240,.15); color:var(--violet);}
.form-row{display:flex; flex-direction:column; gap:6px; margin-bottom:16px; max-width:420px.}
.form-row label{font-size:13px; color:var(--muted);}
.form-row input,.form-row select,.form-row textarea{background:var(--panel-2); border:1px solid var(--border-2); color:var(--text); border-radius:9px; padding:10px 12px; font-size:13.5px; font-family:inherit;}
.toggle-row{display:flex; justify-content:space-between; align-items:center; padding:13px 0; border-bottom:1px solid var(--border); max-width:460px.}
.switch{width:40px; height:22px; background:#2a2a36; border-radius:20px; position:relative; cursor:pointer; flex-shrink:0;}
.switch.on{background:var(--cyan);}
.switch i{position:absolute; top:2px; left:2px; width:18px; height:18px; border-radius:50%; background:#fff; transition:left .18s;}
.switch.on i{left:20px;}
.btn-inline{display:inline-flex;align-items:center;gap:6px;padding:8px 12px;border-radius:8px;border:1px solid var(--border-2);background:var(--panel-2);color:var(--text);font-size:12.5px;cursor:pointer;font-weight:600;}
.btn-inline:hover{border-color:var(--cyan);}
.btn-inline.primary{background:var(--cyan);color:#031015;border-color:var(--cyan);}
.btn-inline.danger{color:var(--red);border-color:rgba(232,90,90,.35);}
.btn-inline.warn{color:var(--orange);border-color:rgba(240,169,60,.35);}
.meta-actions{display:flex;gap:8px;flex-wrap:wrap;justify-content:flex-end;}
.meta-card{display:flex;align-items:center;gap:14px;border:1px solid var(--border);border-radius:12px;padding:14px 16px;background:var(--panel-2);margin-bottom:10px;flex-wrap:wrap;}
.meta-card .info{flex:1;min-width:220px.}
.meta-card .nm{font-weight:600;font-size:14px;display:flex;align-items:center;gap:8px;flex-wrap:wrap.}
.meta-card .sub{font-size:12.5px;color:var(--muted);margin-top:2px.}
.status-pill{display:inline-flex;align-items:center;gap:6px;border:1px solid var(--border-2);padding:6px 12px;border-radius:20px;font-size:12.5px;font-weight:600;white-space:nowrap.}
.status-pill.ok{background:rgba(52,194,122,.12);color:var(--green);border-color:rgba(52,194,122,.3).}
.status-pill.off{background:rgba(232,90,90,.12);color:var(--red);border-color:rgba(232,90,90,.3).}
.status-pill.pending{background:rgba(240,169,60,.12);color:var(--orange);border-color:rgba(240,169,60,.3).}
.modal-backdrop{position:fixed;inset:0;background:rgba(0,0,0,.6);backdrop-filter:blur(4px);display:none;align-items:flex-start;justify-content:center;z-index:200;padding:24px 16px;overflow-y:auto;}
.modal-backdrop.open{display:flex;}
.modal{width:100%;max-width:520px;background:var(--panel);border:1px solid var(--border-2);border-radius:14px;overflow:hidden;display:flex;flex-direction:column;max-height:calc(100vh - 48px);margin:auto 0;}
.modal-head{display:flex;justify-content:space-between;align-items:center;padding:16px 18px;border-bottom:1px solid var(--border);position:sticky;top:0;background:var(--panel);z-index:1;flex-shrink:0;}
.modal-head h3{margin:0;font-size:16px;}
.modal form{display:flex;flex-direction:column;min-height:0;flex:1;overflow:hidden;}
.modal-body{padding:18px;overflow-y:auto;flex:1;min-height:0;-webkit-overflow-scrolling:touch;}
.modal-foot{display:flex;justify-content:flex-end;gap:10px;padding:14px 18px;border-top:1px solid var(--border);position:sticky;bottom:0;background:var(--panel);z-index:1;flex-shrink:0;}
@media (max-width:600px){
  .modal-backdrop{padding:12px 10px;align-items:stretch;}
  .modal{max-height:calc(100vh - 24px);margin:0;border-radius:12px;}
  .modal-body{padding:14px;}
}
.field{margin-bottom:14px;}
.field label{display:block;font-size:13px;color:var(--muted);margin-bottom:6px;}
.field input,.field select,.field textarea{width:100%;background:var(--panel-2);border:1px solid var(--border-2);color:var(--text);border-radius:9px;padding:10px 12px;font-size:13.5px;font-family:inherit;}
.field textarea{min-height:80px;resize:vertical;}
.empty-box{border:1px dashed var(--border-2);border-radius:12px;padding:24px;text-align:center;color:var(--muted);background:var(--panel-2).}
.swap2-popup{background:#111116 !important;color:#f2f2f6 !important;border:1px solid #2a2a36.}
.swap2-title,.swal2-html-container{color:#f2f2f6 !important.}
html[data-theme="light"] .swal2-popup{background:#ffffff !important;color:#121826 !important;border-color:#e2e6ee.}
html[data-theme="light"] .swal2-title,html[data-theme="light"] .swal2-html-container{color:#121826 !important.}
.msg-thread{display:flex; gap:12px; border:1px solid var(--border); border-radius:12px; padding:14px; background:var(--panel-2); margin-bottom:10px.}
.msg-thread .av{width:34px; height:34px; border-radius:50%; background:#3a3a46; flex-shrink:0; display:flex; align-items:center; justify-content:center; font-weight:700; font-size:12px.}
.msg-thread .t{font-weight:600; font-size:13.5px.}
.msg-thread .s{font-size:12.5px; color:var(--muted); margin-top:2px.}

@media (max-width:980px){
  .two-col{grid-template-columns:1fr.}
  .stats{grid-template-columns:repeat(2,1fr).}
  .card-grid{grid-template-columns:1fr 1fr.}
}
@media (max-width:760px){
  .sidebar{transform:translateX(-100%).}
  .sidebar.open{transform:translateX(0); box-shadow:0 0 0 2000px rgba(0,0,0,.5).}
  .main{margin-left:0.}
  .menu-btn{display:flex.}
  .stats{grid-template-columns:1fr 1fr.}
  .hero-banner{flex-direction:column.}
  .card-grid{grid-template-columns:1fr.}
}
.ico{width:1em;height:1em;display:inline-block;vertical-align:-0.125em;fill:none;stroke:currentColor;stroke-width:1.8;stroke-linecap:round;stroke-linejoin:round;flex-shrink:0.}
.ico-fill{fill:currentColor;stroke:none.}
.ic .ico,.icon-btn .ico,.logo-ic{width:18px;height:18px.}
.hbtn .ico,.act .ico,.status-ok .ico,.sidebar-foot .ico{width:16px;height:16px.}
.nav-item .ic{display:inline-flex;align-items:center;justify-content:center.}
</style>
</head>
<body>
<script>window.addEventListener('pageshow', function(e){ if(e.persisted){ window.location.reload(); } });</script>
<div class="app">

  <svg xmlns="http://www.w3.org/2000/svg" style="display:none">
    <symbol id="i-dashboard" viewBox="0 0 24 24"><rect x="3" y="3" width="8" height="8" rx="2"/><rect x="13" y="3" width="8" height="5" rx="2"/><rect x="13" y="10" width="8" height="11" rx="2"/><rect x="3" y="13" width="8" height="8" rx="2"/></symbol>
    <symbol id="i-agents" viewBox="0 0 24 24"><rect x="4" y="4" width="16" height="16" rx="3"/><path d="M9 9h6M9 13h4"/></symbol>
    <symbol id="i-services" viewBox="0 0 24 24"><path d="M12 3v4M12 17v4M4.9 4.9l2.8 2.8M16.3 16.3l2.8 2.8M3 12h4M17 12h4M4.9 19.1l2.8-2.8M16.3 7.7l2.8-2.8"/></symbol>
    <symbol id="i-community" viewBox="0 0 24 24"><circle cx="12" cy="8" r="4"/><path d="M4 20c0-4 3.6-6 8-6s8 2 8 6"/></symbol>
    <symbol id="i-calendar" viewBox="0 0 24 24"><rect x="3" y="5" width="18" height="16" rx="2"/><path d="M16 3v4M8 3v4M3 10h18"/></symbol>
    <symbol id="i-studio" viewBox="0 0 24 24"><rect x="3" y="4" width="18" height="14" rx="2"/><circle cx="8.5" cy="9.5" r="1.5"/><path d="M21 14l-5-5-4 4-2-2-5 5"/></symbol>
    <symbol id="i-identity" viewBox="0 0 24 24"><circle cx="12" cy="12" r="9"/><circle cx="12" cy="12" r="3"/></symbol>
    <symbol id="i-trend" viewBox="0 0 24 24"><path d="M12 3l1.8 5.2H19l-4.2 3 1.6 5.3L12 13.4 7.6 16.5l1.6-5.3L5 8.2h5.2z"/></symbol>
    <symbol id="i-billing" viewBox="0 0 24 24"><rect x="3" y="5" width="18" height="14" rx="2"/><path d="M3 10h18"/></symbol>
    <symbol id="i-link" viewBox="0 0 24 24"><path d="M10 13a5 5 0 0 0 7.1 0l2.1-2.1a5 5 0 0 0-7.1-7.1L11 5"/><path d="M14 11a5 5 0 0 0-7.1 0L4.8 13.1a5 5 0 0 0 7.1 7.1L13 19"/></symbol>
    <symbol id="i-mail" viewBox="0 0 24 24"><rect x="3" y="5" width="18" height="14" rx="2"/><path d="M4 7l8 6 8-6"/></symbol>
    <symbol id="i-settings" viewBox="0 0 24 24"><circle cx="12" cy="12" r="3"/><path d="M19.4 15a1 1 0 0 0 .2 1.1l.1.1a2 2 0 1 1-2.8 2.8l-.1-.1a1 1 0 0 0-1.1-.2 1 1 0 0 0-.6.9V20a2 2 0 1 1-4 0v-.1a1 1 0 0 0-.7-.9 1 1 0 0 0-1.1.2l-.1.1a2 2 0 1 1-2.8-2.8l.1-.1a1 1 0 0 0 .2-1.1 1 1 0 0 0-.9-.6H4a2 2 0 1 1 0-4h.1a1 1 0 0 0 .9-.7 1 1 0 0 0-.2-1.1l-.1-.1a2 2 0 1 1 2.8-2.8l.1.1a1 1 0 0 0 1.1.2h.1a1 1 0 0 0 .6-.9V4a2 2 0 1 1 4 0v.1a1 1 0 0 0 .6.9 1 1 0 0 0 1.1-.2l.1-.1a2 2 0 1 1 2.8 2.8l-.1.1a1 1 0 0 0-.2 1.1v.1a1 1 0 0 0 .9.6H20a2 2 0 1 1 0 4h-.1a1 1 0 0 0-.9.6z"/></symbol>
    <symbol id="i-bolt" viewBox="0 0 24 24"><path d="M13 2L4 14h6l-1 8 9-12h-6z"/></symbol>
    <symbol id="i-check" viewBox="0 0 24 24"><path d="M20 6L9 17l-5-5"/></symbol>
    <symbol id="i-sun" viewBox="0 0 24 24"><circle cx="12" cy="12" r="4"/><path d="M12 2v2M12 20v2M4.9 4.9l1.4 1.4M17.7 17.7l1.4 1.4M2 12h2M20 12h2M4.9 19.1l1.4-1.4M17.7 6.3l1.4-1.4"/></symbol>
    <symbol id="i-moon" viewBox="0 0 24 24"><path d="M21 12.8A9 9 0 1 1 11.2 3a7 7 0 0 0 9.8 9.8z"/></symbol>
    <symbol id="i-globe" viewBox="0 0 24 24"><circle cx="12" cy="12" r="9"/><path d="M3 12h18M12 3a15 15 0 0 1 0 18M12 3a15 15 0 0 0 0 18"/></symbol>
    <symbol id="i-bell" viewBox="0 0 24 24"><path d="M18 8a6 6 0 0 0-12 0c0 7-3 7-3 7h18s-3 0-3-7"/><path d="M13.7 21a2 2 0 0 1-3.4 0"/></symbol>
    <symbol id="i-wave" viewBox="0 0 24 24"><path d="M7 11V8a2 2 0 1 1 4 0v2"/><path d="M11 10V7a2 2 0 1 1 4 0v4"/><path d="M15 11V9a2 2 0 1 1 4 0v5a7 7 0 0 1-7 7h-1a7 7 0 0 1-6-3l-2-3a2 2 0 0 1 3-2l2 2v-4"/></symbol>
    <symbol id="i-arrow-r" viewBox="0 0 24 24"><path d="M5 12h14"/><path d="M13 6l6 6-6 6"/></symbol>
    <symbol id="i-arrow-tr" viewBox="0 0 24 24"><path d="M7 17L17 7"/><path d="M9 7h8v8"/></symbol>
    <symbol id="i-alert" viewBox="0 0 24 24"><path d="M12 3l9 16H3z"/><path d="M12 9v4"/><path d="M12 17h.01"/></symbol>
    <symbol id="i-plus" viewBox="0 0 24 24"><path d="M12 5v14M5 12h14"/></symbol>
    <symbol id="i-chart" viewBox="0 0 24 24"><path d="M4 19h16"/><path d="M7 16V9"/><path d="M12 16V5"/><path d="M17 16v-4"/></symbol>
    <symbol id="i-menu" viewBox="0 0 24 24"><path d="M4 6h16M4 12h16M4 18h16"/></symbol>
    <symbol id="i-logout" viewBox="0 0 24 24"><path d="M9 21H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h4"/><path d="M16 17l5-5-5-5"/><path d="M21 12H9"/></symbol>
  </svg>

  <aside class="sidebar" id="sidebar">
    <div class="brand">
      <img class="brand-logo brand-logo-dark" src="/assets/1.png" alt="IA Agent Studio" style="height:48px;width:auto;display:block;">
      <img class="brand-logo brand-logo-light" src="/assets/4.png" alt="IA Agent Studio" style="height:48px;width:auto;">
    </div>

    <div class="nav-item active" data-page="dashboard"><span class="ic"><svg class="ico"><use href="#i-dashboard"/></svg></span> Tableau de bord</div>

    <div class="nav-item" id="servicesToggle"><span class="ic"><svg class="ico"><use href="#i-services"/></svg></span> Services <span class="chev">▾</span></div>
    <div class="sub open" id="servicesSub">
      <button class="nav-item nav-toggle" id="userAgentsToggle" type="button" aria-expanded="false" aria-controls="userAgentsSub">
        <span class="ic"><svg class="ico"><use href="#i-agents"/></svg></span> Agents IA <span class="chev">▾</span>
      </button>
      <div class="sub" id="userAgentsSub">
        <div class="nav-item" data-page="my-agents"><span class="ic"><svg class="ico"><use href="#i-agents"/></svg></span> Gérer mes agents</div>
        <div id="userAgentNavList">
          <div class="empty-box" style="padding:10px;font-size:12px;">Ouvrez le menu pour charger vos agents.</div>
        </div>
      </div>
    </div>

    <div class="nav-item" data-page="pages-meta"><span class="ic"><svg class="ico"><use href="#i-link"/></svg></span> Pages & Comptes Meta</div>
    <div class="nav-item" data-page="messagerie"><span class="ic"><svg class="ico"><use href="#i-mail"/></svg></span> Messagerie Support</div>
    <div class="nav-item" data-page="parametres"><span class="ic"><svg class="ico"><use href="#i-settings"/></svg></span> Paramètres</div>

    <div class="sidebar-foot">
      <div class="row"><span><svg class="ico"><use href="#i-bolt"/></svg> Pack Premium</span><span>4/9999 posts</span></div>
      <div class="bar"><i></i></div>
      <div class="meta"><span>Validation requise</span><span><svg class="ico"><use href="#i-check"/></svg> 100% sécurisé</span></div>
    </div>
  </aside>

  <div class="main">
    <div class="topbar">
      <button class="menu-btn" id="menuBtn"><svg class="ico"><use href="#i-menu"/></svg></button>
      <button class="icon-btn" id="themeToggle" title="Changer le thème"><svg class="ico"><use id="themeToggleIcon" href="#i-sun"/></svg></button>
      <div class="lang"><svg class="ico"><use href="#i-globe"/></svg> FR</div>
      <button class="icon-btn"><svg class="ico"><use href="#i-bell"/></svg><span class="dot-badge">7</span></button>
      <div class="user-menu" id="userMenu">
        <div class="user-chip" id="userChip">
          <div class="avatar">{{ strtoupper(mb_substr(auth()->user()->first_name ?? 'U', 0, 1) . mb_substr(auth()->user()->last_name ?? '', 0, 1)) }}</div>
          <div><div class="name">{{ trim((auth()->user()->first_name ?? '') . ' ' . (auth()->user()->last_name ?? '')) ?: (auth()->user()->email ?? 'Utilisateur') }}</div><div class="plan">Pack Premium</div></div>
          <span style="color:var(--muted)">▾</span>
        </div>
        <div class="user-dropdown" id="userDropdown">
          <a href="/logout" class="danger"><svg class="ico"><use href="#i-logout"/></svg> Déconnexion</a>
        </div>
      </div>
    </div>

    <div class="content">

      <section class="page active" id="page-dashboard">
        <div class="hero-banner">
          <div>
            <div class="eyebrow"><span class="tag">PLATEFORME SAAS</span> IA Agent Studio</div>
            <h1>Bonjour, {{ auth()->user()->first_name ?? 'vous' }} <svg class="ico" style="width:22px;height:22px;vertical-align:-3px"><use href="#i-wave"/></svg></h1>
            <p>Vos agents IA gèrent le contenu de vos pages sociales avec une rigueur absolue. Chaque publication reste soumise à votre validation avant mise en ligne.</p>
          </div>
          <div class="hero-actions">
            <button class="hbtn light">Examiner les publications <svg class="ico"><use href="#i-arrow-r"/></svg></button>
            <button class="hbtn ghost"><svg class="ico"><use href="#i-calendar"/></svg> Calendrier</button>
          </div>
        </div>

        <div class="alert">
          <div class="ic"><svg class="ico" style="width:18px;height:18px"><use href="#i-alert"/></svg></div>
          <div class="body">
            <div class="t1">4 publication(s) en attente de votre validation explicite <span class="chip-req">Règle Obligatoire</span></div>
            <div class="t2">Conformément aux règles de sécurité Meta Colab, aucun contenu ne sera programmé ou publié sans votre approbation expresse.</div>
          </div>
          <div class="act">Valider maintenant <svg class="ico"><use href="#i-arrow-r"/></svg></div>
        </div>

        <div class="stats">
          <div class="stat"><div class="num">1</div><div class="lbl">100% opérationnels</div></div>
          <div class="stat c-green"><div class="num">1</div><div class="lbl">Facebook & Instagram</div></div>
          <div class="stat highlight c-orange"><div class="num">4</div><div class="lbl">Validation requise</div></div>
          <div class="stat c-violet"><div class="num">0</div><div class="lbl">Publication auto prête</div></div>
          <div class="stat c-green2"><div class="num">8</div><div class="lbl">En ligne sur Meta</div></div>
        </div>

        <div class="two-col">
          <div class="panel">
              <div class="panel-head">
                <div><h2>Comptes Meta Connectés & Statut</h2><p>Surveillance continue des jetons OAuth et des autorisations de publication</p></div>
                <span class="link" data-page-link="pages-meta" style="cursor:pointer">Gérer <svg class="ico"><use href="#i-arrow-tr"/></svg></span>
              </div>
              <div class="meta-row">
                <div class="av">M</div>
                <div class="info">
                  <div class="nm">Mira <span class="tag-fb">FACEBOOK</span></div>
                  <div class="sub">0 abonnés · Agent : Community manager</div>
                </div>
                <span class="status-ok"><svg class="ico"><use href="#i-check"/></svg> Connecté</span>
              </div>
            </div>

            <div class="panel">
              <div class="panel-head"><div><h2>Accès Rapides</h2><p>Opérations rapides pour piloter vos réseaux</p></div></div>
              <div class="quick">
                <div class="qbtn b1"><span class="ic"><svg class="ico"><use href="#i-plus"/></svg></span><span class="lbl">Créer un nouvel agent IA</span><span class="arr"><svg class="ico"><use href="#i-arrow-r"/></svg></span></div>
                <div class="qbtn b2"><span class="ic"><svg class="ico"><use href="#i-services"/></svg></span><span class="lbl">Générer calendrier hebdo</span><span class="arr"><svg class="ico"><use href="#i-arrow-r"/></svg></span></div>
                <div class="qbtn b3"><span class="ic"><svg class="ico"><use href="#i-chart"/></svg></span><span class="lbl">Veille concurrentielle IA</span><span class="arr"><svg class="ico"><use href="#i-arrow-r"/></svg></span></div>
              </div>
            <div class="panel-foot"><span>Forfait : <b style="color:var(--text)">Pack Premium</b></span><a href="#">Voir détails</a></div>
          </div>
        </div>
      </section>

      <section class="page" id="page-my-agents">
        <div class="page-head" style="display:flex;justify-content:space-between;align-items:flex-end;gap:16px;flex-wrap:wrap;">
          <div><h1>Mes Agents IA</h1><p>Vos agents configurés et leur périmètre d'action.</p></div>
          <button class="btn-inline primary" id="agentAddBtn"><svg class="ico"><use href="#i-plus"/></svg> Créer un agent</button>
        </div>
        <div id="userAgentsList" class="card-grid">
          <div class="empty-box">Chargement de vos agents…</div>
        </div>
      </section>

      <section class="page" id="page-calendrier">
        <div class="page-head"><h1>Calendrier Éditorial</h1><p>Vue hebdomadaire des publications proposées par l'agent.</p></div>
        <table class="data">
          <tr><th>Date</th><th>Page</th><th>Pilier</th><th>Statut</th></tr>
          <tr><td>Lun. 10/11 — 09:00</td><td>Ghostroar FB</td><td>Pédagogie</td><td><span class="pill o">En attente</span></td></tr>
          <tr><td>Mar. 11/11 — 12:30</td><td>Ghostroar FB</td><td>Preuve</td><td><span class="pill o">En attente</span></td></tr>
          <tr><td>Mer. 12/11 — 18:00</td><td>Ghostroar IG</td><td>Offre</td><td><span class="pill g">Validée</span></td></tr>
          <tr><td>Jeu. 13/11 — 09:00</td><td>Ghostroar FB</td><td>Coulisses</td><td><span class="pill v">Planifiée</span></td></tr>
        </table>
      </section>

      <section class="page" id="page-studio">
        <div class="page-head"><h1>Studio des Publications</h1><p>Éditez le texte et le visuel avant validation.</p></div>
        <div class="panel" style="max-width:640px">
          <div class="form-row"><label>Texte de la publication</label><textarea rows="5">Un ERP ne remplace pas vos équipes : il relie les fonctions…</textarea></div>
          <div class="form-row"><label>Prompt du visuel</label><input value="Illustration moderne, bleu/violet, style flat design"></div>
          <button class="hbtn" style="background:var(--cyan); color:#031015;">Régénérer le visuel</button>
        </div>
      </section>

      <section class="page" id="page-identite">
        <div class="page-head"><h1>Identité & Inspiration</h1><p>Ton de marque, valeurs et références qui guident l'agent.</p></div>
        <div class="card-grid">
          <div class="simple-card"><h3>Ton de voix</h3><p>Professionnel, chaleureux, orienté résultats concrets.</p></div>
          <div class="simple-card"><h3>Palette & charte</h3><p>Bleu cyan, violet, fond sombre — cohérent avec la plateforme.</p></div>
          <div class="simple-card"><h3>Comptes inspirants</h3><p>3 comptes de référence suivis pour nourrir le style éditorial.</p></div>
        </div>
      </section>

      <section class="page" id="page-veille">
        <div class="page-head"><h1>Veille & Tendances</h1><p>Signaux marché et contenus concurrents détectés par l'agent.</p></div>
        <div class="card-grid">
          <div class="simple-card"><h3>Sujet émergent</h3><p>Hausse de 32% des publications sur « automatisation RH » cette semaine.</p></div>
          <div class="simple-card"><h3>Concurrent actif</h3><p>Un concurrent a publié 5 posts sponsorisés sur l'offre ERP.</p></div>
          <div class="simple-card"><h3>Format gagnant</h3><p>Les carrousels génèrent 2,1x plus d'engagement ce mois-ci.</p></div>
        </div>
      </section>

      <section class="page" id="page-abonnements">
        <div class="page-head"><h1>Abonnements & Quotas</h1><p>Suivi de votre forfait et de votre consommation.</p></div>
        <div class="panel" style="max-width:460px">
          <div style="display:flex; justify-content:space-between; font-weight:600; margin-bottom:8px;">Pack Premium <span>4 / 9999 posts</span></div>
          <div class="bar" style="margin-bottom:16px;"><i></i></div>
          <p style="color:var(--muted); font-size:13.5px;">Renouvellement le 1er décembre 2026 · Validation humaine obligatoire incluse.</p>
        </div>
      </section>

      <section class="page" id="page-pages-meta">
        <div class="page-head" style="display:flex;justify-content:space-between;align-items:flex-end;gap:16px;flex-wrap:wrap;">
          <div><h1>Pages & Comptes Meta</h1><p>Gérez vos connexions Meta : afficher, modifier, déconnecter ou supprimer vos comptes/pages sur la plateforme.</p></div>
          <button class="btn-inline primary" id="metaAddBtn"><svg class="ico"><use href="#i-plus"/></svg> Ajouter un compte</button>
        </div>
        <div class="panel" style="margin-bottom:16px;">
          <div style="font-size:13px;color:var(--muted);">Configuration Meta App (serveur) : App ID <b style="color:var(--text)">{{ config('services.meta.app_id') ? 'configuré' : 'non configuré' }}</b> · Version Graph <b style="color:var(--text)">{{ config('services.meta.graph_version') }}</b>. Les jetons d'accès sont stockés côté serveur et ne sont pas affichés.</div>
        </div>
        <div class="panel">
          <div class="panel-head"><div><h2>Vos comptes Meta</h2><p>Liste des comptes/pages associés à votre espace.</p></div></div>
          <div id="metaAccountsList"><div class="empty-box">Chargement des comptes…</div></div>
        </div>
      </section>

      <div class="modal-backdrop" id="metaModal">
        <div class="modal">
          <div class="modal-head"><h3 id="metaModalTitle">Compte Meta</h3><button class="btn-inline" id="metaModalClose" type="button">Fermer</button></div>
          <form id="metaForm">
            <input type="hidden" id="metaAccountId">
            <div class="modal-body">
              <div id="metaProfilePreview" style="display:none;align-items:center;gap:12px;margin-bottom:14px;padding:12px;border:1px solid var(--border);border-radius:10px;background:var(--panel-2);"></div>
              <div class="field"><label>ID Meta (Page ID / compte)</label><input id="metaId" required maxlength="255" placeholder="Ex: 123456789012345"><div class="sub" style="margin-top:6px;color:var(--muted);font-size:12.5px;">Renseignez l'identifiant de la page/compte : le nom et la photo de profil seront récupérés automatiquement via l'app Meta configurée.</div></div>
              <div class="field"><label>Jeton d'accès (optionnel)</label><input id="metaAccessToken" type="password" maxlength="2000" placeholder="Jeton d'accès de page/utilisateur si requis"><div class="sub" style="margin-top:6px;color:var(--muted);font-size:12.5px;">Si la récupération échoue avec le jeton d'application, fournissez un jeton d'accès Meta (Page Access Token) ayant accès à cette page/compte.</div></div>
              <div class="field"><label>Plateforme</label><select id="metaPlatform"><option value="facebook">Facebook</option><option value="instagram">Instagram</option></select></div>
              <div class="field"><label>Statut</label><select id="metaStatus"><option value="connected">Connecté</option><option value="pending">En attente</option><option value="disconnected">Déconnecté</option></select></div>
              <div class="field"><label>Catégorie</label><input id="metaCategory" maxlength="255" placeholder="Optionnel"></div>
              <div class="field"><label>Notes</label><textarea id="metaNotes" maxlength="2000" placeholder="Optionnel"></textarea></div>
            </div>
            <div class="modal-foot"><button type="button" class="btn-inline" id="metaCancelBtn">Annuler</button><button type="submit" class="btn-inline primary" id="metaSaveBtn">Enregistrer</button></div>
          </form>
        </div>
      </div>

      <div class="modal-backdrop" id="agentModal">
        <div class="modal">
          <div class="modal-head"><h3 id="agentModalTitle">Créer un agent</h3><button class="btn-inline" id="agentModalClose" type="button">Fermer</button></div>
          <form id="agentForm" enctype="multipart/form-data">
            <input type="hidden" id="agentUserAgentId">
            <div class="modal-body">
              <div class="field">
                <label>Photo de profil</label>
                <div style="display:flex; align-items:center; gap:16px;">
                  <div id="agentPhotoPreview" style="width:60px; height:60px; border-radius:50%; background:var(--panel-2); border:1px solid var(--border); display:flex; align-items:center; justify-content:center; overflow:hidden; flex-shrink:0;">
                    <svg class="ico" style="width:24px; height:24px; color:var(--muted);"><use href="#i-identity"/></svg>
                  </div>
                  <div style="flex:1;">
                    <input type="file" id="agentProfilePhoto" accept="image/*" style="display:none;">
                    <button type="button" class="btn-inline" id="agentPhotoSelectBtn"><svg class="ico"><use href="#i-plus"/></svg> Choisir une photo</button>
                    <button type="button" class="btn-inline" id="agentPhotoRemoveBtn" style="display:none;"><svg class="ico"><use href="#i-trash"/></svg> Retirer</button>
                    <input type="hidden" id="agentRemovePhoto" value="0">
                    <div class="sub" style="margin-top:6px;color:var(--muted);font-size:12.5px;">Format: JPG, PNG, WebP. Max 2Mo. Carré recommandé.</div>
                  </div>
                </div>
              </div>
              <div class="field">
                <label>Modèle d'agent <span style="color:var(--red)">*</span></label>
                <select id="agentAgentId" required>
                  <option value="">Sélectionnez un modèle d'agent</option>
                </select>
                <div class="sub" style="margin-top:6px;color:var(--muted);font-size:12.5px;">Choisissez le modèle d'agent défini par l'administrateur.</div>
              </div>
              <div class="field">
                <label>Nom personnalisé</label>
                <input id="agentCustomName" maxlength="255" placeholder="Ex: Mon Community Manager">
                <div class="sub" style="margin-top:6px;color:var(--muted);font-size:12.5px;">Optionnel : donnez un nom spécifique à votre instance (ex: « Support Client Ghostroar »).</div>
              </div>
              <div class="field">
                <label>Domaine d'activité <span style="color:var(--red)">*</span></label>
                <select id="agentDomain" required>
                  <option value="">Sélectionnez un domaine</option>
                  <option value="community-management">Community Management</option>
                  <option value="support-client">Support Client</option>
                  <option value="recrutement">Recrutement / RH</option>
                  <option value="veille-sectorielle">Veille Sectorielle</option>
                  <option value="marketing-contenu">Marketing de Contenu</option>
                  <option value="vente-prospection">Vente / Prospection</option>
                  <option value="autre">Autre</option>
                </select>
                <input type="text" id="agentDomainCustom" maxlength="255" placeholder="Précisez le domaine" style="display:none; margin-top:8px;">
                <div class="sub" style="margin-top:6px;color:var(--muted);font-size:12.5px;">Sélectionnez "Autre" pour saisir un domaine personnalisé.</div>
              </div>
              <div class="field">
                <label>Tonalité <span style="color:var(--red)">*</span></label>
                <select id="agentTone" required>
                  <option value="">Sélectionnez une tonalité</option>
                  <option value="professionnel">Professionnel</option>
                  <option value="chaleureux">Chaleureux / Empathique</option>
                  <option value="expert">Expert / Technique</option>
                  <option value="creatif">Créatif / Inspiré</option>
                  <option value="direct">Direct / Concis</option>
                  <option value="pedagogique">Pédagogique</option>
                  <option value="humoristique">Humoristique / Décontracté</option>
                  <option value="institutionnel">Institutionnel / Formel</option>
                </select>
              </div>
            </div>
            <div class="modal-foot"><button type="button" class="btn-inline" id="agentCancelBtn">Annuler</button><button type="submit" class="btn-inline primary" id="agentSaveBtn">Créer l'agent</button></div>
          </form>
        </div>
      </div>

      <section class="page" id="page-messagerie">
        <div class="page-head"><h1>Messagerie Support</h1><p>Échanges avec l'équipe IA Agent Studio.</p></div>
        <div class="msg-thread"><div class="av">S</div><div><div class="t">Support IA Agent Studio</div><div class="s">Votre page Ghostroar a été vérifiée avec succès. — hier</div></div></div>
        <div class="msg-thread"><div class="av">{{ strtoupper(mb_substr(auth()->user()->first_name ?? 'U', 0, 1) . mb_substr(auth()->user()->last_name ?? '', 0, 1)) }}</div><div><div class="t">Vous</div><div class="s">Merci, pouvez-vous augmenter mon quota mensuel ? — hier</div></div></div>
      </section>

      <section class="page" id="page-parametres">
        <div class="page-head"><h1>Paramètres</h1><p>Préférences du compte et de la plateforme.</p></div>
        <div class="toggle-row"><span>Mode sombre</span><div class="switch" id="themeSwitch" data-switch="theme"><i></i></div></div>
        <div class="toggle-row"><span>Notifications par e-mail</span><div class="switch on" data-switch><i></i></div></div>
        <div class="toggle-row"><span>Validation obligatoire avant publication</span><div class="switch on" data-switch><i></i></div></div>
        <div class="toggle-row"><span>Mode sandbox Meta</span><div class="switch" data-switch><i></i></div></div>
      </section>

    </div>
  </div>
</div>

<script>
var sidebar = document.getElementById('sidebar');
sidebar.addEventListener('click', function(e){
  var el = e.target.closest('.nav-item[data-page]');
  if(!el || !sidebar.contains(el)) return;
  document.querySelectorAll('.nav-item[data-page]').forEach(function(n){n.classList.remove('active');});
  el.classList.add('active');
  var target = el.getAttribute('data-page');
  document.querySelectorAll('.page').forEach(function(p){p.classList.remove('active');});
  var page = document.getElementById('page-'+target);
  if(page){ page.classList.add('active'); }
  if(window.innerWidth <= 760){ sidebar.classList.remove('open'); }
  window.scrollTo(0,0);
});

function wireToggle(toggleId, subId){
  var t = document.getElementById(toggleId), s = document.getElementById(subId);
  t.addEventListener('click', function(){ s.classList.toggle('open'); });
}
wireToggle('servicesToggle','servicesSub');
document.getElementById('userAgentsToggle').addEventListener('click', function(){
  var sub = document.getElementById('userAgentsSub');
  var isOpen = sub.classList.toggle('open');
  this.setAttribute('aria-expanded', isOpen);
});

document.getElementById('menuBtn').addEventListener('click', function(){ sidebar.classList.toggle('open'); });

var userChip = document.getElementById('userChip');
var userDropdown = document.getElementById('userDropdown');
if(userChip && userDropdown){
  userChip.addEventListener('click', function(e){
    e.stopPropagation();
    userDropdown.classList.toggle('open');
  });
}

document.addEventListener('click', function(e){
  if(window.innerWidth<=760 && sidebar.classList.contains('open') && !sidebar.contains(e.target) && e.target.id!=='menuBtn'){
    sidebar.classList.remove('open');
  }
  if(userDropdown && userDropdown.classList.contains('open')){
    var menu = document.getElementById('userMenu');
    if(menu && !menu.contains(e.target)){ userDropdown.classList.remove('open'); }
  }
});

document.querySelectorAll('[data-switch]').forEach(function(sw){
  sw.addEventListener('click', function(){
    if(sw.dataset.switch === 'theme'){ toggleTheme(); return; }
    sw.classList.toggle('on');
  });
});

function getStoredTheme(){ try{ return localStorage.getItem('theme') || 'dark'; }catch(e){ return 'dark'; } }
function applyTheme(theme){
  document.documentElement.setAttribute('data-theme', theme);
  try{ localStorage.setItem('theme', theme); }catch(e){}
  var icon = document.getElementById('themeToggleIcon');
  if(icon){ icon.setAttribute('href', theme === 'dark' ? '#i-sun' : '#i-moon'); }
  var sw = document.getElementById('themeSwitch');
  if(sw){ sw.classList.toggle('on', theme === 'dark'); }
}
function toggleTheme(){ applyTheme(getStoredTheme() === 'dark' ? 'light' : 'dark'); }

var themeBtn = document.getElementById('themeToggle');
if(themeBtn){ themeBtn.addEventListener('click', toggleTheme); }
applyTheme(getStoredTheme());

document.querySelectorAll('[data-page-link]').forEach(function(el){
  el.addEventListener('click', function(){
    var target = el.getAttribute('data-page-link');
    var nav = document.querySelector('.nav-item[data-page="'+target+'"]');
    if(nav) nav.click();
  });
});
</script>
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<script>
(function(){
  var csrf = document.querySelector('meta[name="csrf-token"]') ? document.querySelector('meta[name="csrf-token"]').getAttribute('content') : '';
  var listEl = document.getElementById('metaAccountsList');
  var modal = document.getElementById('metaModal');
  var form = document.getElementById('metaForm');
  if(!listEl || !modal || !form) return;

  function notify(msg, type){
    if(window.Swal){
      return Swal.fire({toast:true,position:'bottom',icon:type||'success',title:msg,showConfirmButton:false,timer:type==='error'?3500:1800,timerProgressBar:true});
    }
  }
  function esc(s){ return String(s==null?'':s).replace(/[&<>"']/g,function(c){return {'&':'&','<':'<','>':'>','"':'"',"'":'\''}[c]; }); }
  function statusPill(st){
    var s = st || 'connected';
    var cls = s==='connected' ? 'ok' : (s==='disconnected' ? 'off' : 'pending');
    var label = s==='connected' ? 'Connecté' : (s==='disconnected' ? 'Déconnecté' : 'En attente');
    return '<span class="status-pill '+cls+'"><svg class="ico"><use href="#i-check"/></svg> '+label+'</span>';
  }
  function render(accounts){
    if(!accounts || !accounts.length){
      listEl.innerHTML = '<div class="empty-box">Aucun compte Meta associé. Cliquez sur « Ajouter un compte » pour commencer.</div>';
      return;
    }
    listEl.innerHTML = accounts.map(function(a){
      var avText = esc((a.name||'M').charAt(0).toUpperCase());
      var av = a.picture_url
        ? '<div class="av" style="overflow:hidden;padding:0;"><img src="'+esc(a.picture_url)+'" alt="" style="width:100%;height:100%;object-fit:cover;display:block;" onerror="this.parentNode.textContent=\''+avText+'\'"></div>'
        : '<div class="av">'+avText+'</div>';
      var plat = esc((a.platform||'facebook').toUpperCase());
      var sub = [];
      if(a.meta_id) sub.push('ID: '+esc(a.meta_id));
      if(a.category) sub.push(esc(a.category));
      sub.push(plat);
      var actions = '<button class="btn-inline" data-action="edit" data-id="'+a.id+'">Modifier</button>';
      if(a.status !== 'disconnected'){
        actions += '<button class="btn-inline warn" data-action="disconnect" data-id="'+a.id+'">Déconnecter</button>';
      } else {
        actions += '<button class="btn-inline primary" data-action="reconnect" data-id="'+a.id+'">Reconnecter</button>';
      }
      actions += '<button class="btn-inline danger" data-action="delete" data-id="'+a.id+'">Supprimer</button>';
      return '<div class="meta-card">'
        + '<div class="meta-row" style="border:none;background:none;padding:0;flex:1;min-width:260px;">'+av+'<div class="info"><div class="nm">'+esc(a.name||'Compte Meta')+' <span class="tag-fb">'+plat+'</span></div><div class="sub">'+sub.join(' · ')+'</div>'+(a.notes?'<div class="sub">'+esc(a.notes)+'</div>':'')+'</div></div>'
        + '<div style="display:flex;align-items:center;gap:10px;flex-wrap:wrap;justify-content:flex-end;">'+statusPill(a.status)+'<div class="meta-actions">'+actions+'</div></div>'
        + '</div>';
    }).join('');
  }

  async function api(url, opts){
    opts = opts || {};
    opts.headers = Object.assign({'Accept':'application/json','X-CSRF-TOKEN':csrf,'X-Requested-With':'XMLHttpRequest','Content-Type':'application/json'}, opts.headers||{});
    var res = await fetch(url, opts);
    var data = {};
    try{ data = await res.json(); }catch(e){}
    if(!res.ok) throw Object.assign(new Error(data.message||'Erreur'), {data:data,status:res.status});
    return data;
  }

  var accountsCache = [];
  async function loadAccounts(){
    try{
      var data = await api('/user/meta-accounts');
      accountsCache = data.accounts || [];
      render(accountsCache);
    }catch(e){ listEl.innerHTML = '<div class="empty-box">Impossible de charger les comptes Meta.</div>'; }
  }

  function openModal(account){
    document.getElementById('metaAccountId').value = account ? account.id : '';
    document.getElementById('metaPlatform').value = account ? (account.platform||'facebook') : 'facebook';
    document.getElementById('metaId').value = account ? (account.meta_id||'') : '';
    document.getElementById('metaStatus').value = account ? (account.status||'connected') : 'connected';
    document.getElementById('metaCategory').value = account ? (account.category||'') : '';
    document.getElementById('metaNotes').value = account ? (account.notes||'') : '';
    var tokenField = document.getElementById('metaAccessToken');
    if(tokenField) tokenField.value = '';
    var preview = document.getElementById('metaProfilePreview');
    if(account && (account.name || account.picture_url)){
      var av = account.picture_url
        ? '<div class="av" style="overflow:hidden;padding:0;"><img src="'+esc(account.picture_url)+'" alt="" style="width:100%;height:100%;object-fit:cover;display:block;"></div>'
        : '<div class="av">'+esc((account.name||'M').charAt(0).toUpperCase())+'</div>';
      preview.innerHTML = av + '<div><div style="font-weight:600;">'+esc(account.name||'Compte Meta')+'</div><div style="font-size:12.5px;color:var(--muted);">Nom et photo récupérés depuis Meta</div></div>';
      preview.style.display = 'flex';
    } else {
      preview.innerHTML = '';
      preview.style.display = 'none';
    }
    document.getElementById('metaModalTitle').textContent = account ? 'Modifier le compte Meta' : 'Ajouter un compte Meta';
    modal.classList.add('open');
  }
  function closeModal(){ modal.classList.remove('open'); }

  document.getElementById('metaAddBtn').addEventListener('click', function(){ openModal(null); });
  document.getElementById('metaModalClose').addEventListener('click', closeModal);
  document.getElementById('metaCancelBtn').addEventListener('click', closeModal);
  modal.addEventListener('click', function(e){ if(e.target===modal) closeModal(); });

  form.addEventListener('submit', async function(e){
    e.preventDefault();
    var id = document.getElementById('metaAccountId').value;
    var metaIdVal = document.getElementById('metaId').value.trim();
    if(!metaIdVal){ notify("Veuillez renseigner l'ID Meta (Page ID / compte).", 'error'); return; }
    var tokenVal = (document.getElementById('metaAccessToken') && document.getElementById('metaAccessToken').value.trim()) || null;
    var payload = {
      platform: document.getElementById('metaPlatform').value,
      meta_id: metaIdVal,
      status: document.getElementById('metaStatus').value,
      category: document.getElementById('metaCategory').value.trim() || null,
      notes: document.getElementById('metaNotes').value.trim() || null
    };
    if(tokenVal) payload.access_token = tokenVal;
    try{
      var data = id ? await api('/user/meta-accounts/'+id, {method:'PUT', body:JSON.stringify(payload)})
                  : await api('/user/meta-accounts', {method:'POST', body:JSON.stringify(payload)});
      notify(data.message || 'Enregistré', 'success');
      closeModal();
      loadAccounts();
    }catch(err){ notify(err.message || 'Erreur lors de l\'enregistrement', 'error'); }
  });

  listEl.addEventListener('click', async function(e){
    var btn = e.target.closest('[data-action]');
    if(!btn) return;
    var id = btn.getAttribute('data-id');
    var action = btn.getAttribute('data-action');
    var acc = accountsCache.find(function(a){ return String(a.id)===String(id); });
    if(action==='edit'){ openModal(acc||null); return; }
    if(action==='disconnect'){
      if(window.Swal){
        var r = await Swal.fire({title:'Déconnecter ce compte ?', text:'Le compte sera marqué comme déconnecté sur la plateforme.', icon:'warning', showCancelButton:true, confirmButtonText:'Déconnecter', cancelButtonText:'Annuler', confirmButtonColor:'#f0a93c'});
        if(!r.isConfirmed) return;
      }
      try{ var d = await api('/user/meta-accounts/'+id+'/disconnect', {method:'POST', body:'{}'}); notify(d.message||'Compte déconnecté','success'); loadAccounts(); }
      catch(err){ notify(err.message||'Erreur','error'); }
      return;
    }
    if(action==='reconnect'){
      if(window.Swal){
        var r3 = await Swal.fire({title:'Reconnecter ce compte ?', text:"Le compte sera marqué comme connecté. Pour rafraîchir nom/photo, modifiez le compte et fournissez un jeton d'accès de page si nécessaire.", icon:'question', showCancelButton:true, confirmButtonText:'Reconnecter', cancelButtonText:'Annuler', confirmButtonColor:'#2bc7e8'});
        if(!r3.isConfirmed) return;
      }
      try{ var d3 = await api('/user/meta-accounts/'+id+'/reconnect', {method:'POST', body:'{}'}); notify(d3.message||'Compte reconnecté','success'); loadAccounts(); }
      catch(err){ notify(err.message||'Erreur','error'); }
      return;
    }
    if(action==='delete'){
      if(window.Swal){
        var r2 = await Swal.fire({title:'Supprimer ce compte ?', text:'Cette action est irréversible.', icon:'warning', showCancelButton:true, confirmButtonText:'Supprimer', cancelButtonText:'Annuler', confirmButtonColor:'#e85a5a'});
        if(!r2.isConfirmed) return;
      }
      try{ var d2 = await api('/user/meta-accounts/'+id, {method:'DELETE'}); notify(d2.message||'Compte supprimé','success'); loadAccounts(); }
      catch(err){ notify(err.message||'Erreur','error'); }
    }
  });

  var loaded = false;
  function ensureLoad(){ if(!loaded){ loaded = true; loadAccounts(); } }
  document.querySelectorAll('.nav-item[data-page="pages-meta"]').forEach(function(el){
    el.addEventListener('click', function(){ setTimeout(ensureLoad, 0); });
  });
  if(document.getElementById('page-pages-meta') && document.getElementById('page-pages-meta').classList.contains('active')) ensureLoad();
})();

// Agent handling
(function(){
  var csrf = document.querySelector('meta[name="csrf-token"]') ? document.querySelector('meta[name="csrf-token"]').getAttribute('content') : '';
  var listEl = document.getElementById('userAgentsList');
  var modal = document.getElementById('agentModal');
  var form = document.getElementById('agentForm');
  var agentsMenuEl = document.getElementById('userAgentNavList');
  var agentsToggle = document.getElementById('userAgentsToggle');
  if(!listEl || !modal || !form || !agentsMenuEl || !agentsToggle) return;

  function notify(msg, type){
    if(window.Swal){
      return Swal.fire({toast:true,position:'bottom',icon:type||'success',title:msg,showConfirmButton:false,timer:type==='error'?3500:1800,timerProgressBar:true});
    }
  }
  function esc(s){ return String(s==null?'':s).replace(/[&<>"']/g,function(c){return {'&':'&','<':'<','>':'>','"':'"',"'":'\''}[c]; }); }
function agentPill(st){
      return '<span class="status-pill ok"><svg class="ico"><use href="#i-check"/></svg> Actif</span>';
    }
    function getPhotoUrl(photo) {
      if (!photo) return null;
      // Extract filename from path like "user-agents/filename.jpg"
      var filename = photo.split('/').pop();
      return '/user/agents/photo/' + filename;
    }
    function render(userAgents){
      if(!userAgents || !userAgents.length){
        listEl.innerHTML = '<div class="empty-box">Aucun agent configuré. Cliquez sur « Créer un agent » pour commencer.</div>';
        return;
      }
      listEl.innerHTML = userAgents.map(function(ua){
        var agent = ua.agent || {};
        var photoUrl = getPhotoUrl(ua.profile_photo);
        var avText = esc((agent.name||'A').charAt(0).toUpperCase());
        var name = ua.custom_name || agent.name || 'Agent';
        var domain = ua.domain ? ua.domain.replace('-', ' ').replace(/\b\w/g, function(l){ return l.toUpperCase(); }) : 'Non défini';
        var tone = ua.tone ? ua.tone.charAt(0).toUpperCase() + ua.tone.slice(1) : 'Non défini';
        var sub = [domain, tone];
        var actions = '<button class="btn-inline" data-action="edit" data-id="'+ua.id+'"><svg class="ico"><use href="#i-edit"/></svg> Modifier</button>' +
                      '<button class="btn-inline danger" data-action="delete" data-id="'+ua.id+'"><svg class="ico"><use href="#i-trash"/></svg> Supprimer</button>';
        var avatarHtml = photoUrl
          ? '<div class="av" style="width:48px;height:48px;overflow:hidden;border-radius:50%;background:var(--panel-2);flex-shrink:0;"><img src="'+esc(photoUrl)+'" alt="" style="width:100%;height:100%;object-fit:cover;"></div>'
          : '<div class="av" style="width:48px;height:48px;border-radius:50%;background:var(--panel-2);display:flex;align-items:center;justify-content:center;font-weight:700;font-size:16px;flex-shrink:0;">'+avText+'</div>';
        return '<div class="simple-card" style="display:flex;flex-direction:column;gap:8px;">'
          + '<div style="display:flex;justify-content:space-between;align-items:flex-start;">'
          +   '<div style="display:flex;align-items:center;gap:12px;">'
          +     avatarHtml
          +     '<div><h3 style="margin:0 0 4px;">'+esc(name)+'</h3><p style="margin:0;font-size:13px;color:var(--muted);">Modèle: '+esc(agent.name||'—')+'</p></div>'
          +   '</div>'
          +   agentPill(ua.is_active)
          + '</div>'
          + '<div style="font-size:12.5px;color:var(--muted);display:flex;gap:16px;flex-wrap:wrap;">'
          +   '<span><strong>Domaine:</strong> '+esc(domain)+'</span>'
          +   '<span><strong>Tonalité:</strong> '+esc(tone)+'</span>'
          + '</div>'
          + '<div class="meta-actions" style="margin-top:auto;">'+actions+'</div>'
          + '</div>';
      }).join('');
    }

  async function api(url, opts){
    opts = opts || {};
    opts.headers = Object.assign({'Accept':'application/json','X-CSRF-TOKEN':csrf,'X-Requested-With':'XMLHttpRequest','Content-Type':'application/json'}, opts.headers||{});
    var res = await fetch(url, opts);
    var data = {};
    try{ data = await res.json(); }catch(e){}
    if(!res.ok) throw Object.assign(new Error(data.message||'Erreur'), {data:data,status:res.status});
    return data;
  }

  var userAgentsCache = [];
  var adminAgentsCache = [];
  var activeUserAgentId = null;

  function renderAgentNavigation(userAgents){
    if(!userAgents.length){
      activeUserAgentId = null;
      agentsMenuEl.innerHTML = '<div class="empty-box" style="padding:10px;font-size:12px;">Aucun agent créé. Utilisez « Gérer mes agents » pour en ajouter un.</div>';
      return;
    }

    if(!userAgents.some(function(agent){ return String(agent.id)===String(activeUserAgentId); })){
      activeUserAgentId = null;
    }

    var modules = [
      {page:'calendrier', icon:'i-calendar', label:'Calendrier Éditorial'},
      {page:'studio', icon:'i-studio', label:'Studio des Publications'},
      {page:'identite', icon:'i-identity', label:'Identité & Inspiration'},
      {page:'veille', icon:'i-trend', label:'Veille & Tendances'},
      {page:'abonnements', icon:'i-billing', label:'Abonnements & Quotas'}
    ];

    agentsMenuEl.innerHTML = userAgents.map(function(userAgent){
      var id = esc(userAgent.id);
      var isSelected = String(userAgent.id)===String(activeUserAgentId);
      var name = esc(userAgent.custom_name || (userAgent.agent && userAgent.agent.name) || 'Agent IA');
      var options = modules.map(function(module){
        return '<div class="nav-item" data-page="'+module.page+'" data-agent-id="'+id+'">'
          + '<span class="ic"><svg class="ico"><use href="#'+module.icon+'"/></svg></span> '+module.label
          + '</div>';
      }).join('');

      return '<div class="agent-nav-entry">'
        + '<button class="nav-item nav-toggle user-agent-toggle'+(isSelected?' selected':'')+'" type="button" data-user-agent-toggle data-user-agent-id="'+id+'" aria-expanded="'+(isSelected?'true':'false')+'">'
        + '<span class="ic"><svg class="ico"><use href="#i-agents"/></svg></span> '+name+' <span class="chev">▾</span>'
        + '</button>'
        + '<div class="sub agent-tools'+(isSelected?' open':'')+'" data-user-agent-tools="'+id+'">'+options+'</div>'
        + '</div>';
    }).join('');
  }

  async function loadUserAgents(){
    try{
      var data = await api('/user/agents');
      userAgentsCache = data.userAgents || [];
      render(userAgentsCache);
      renderAgentNavigation(userAgentsCache);
    }catch(e){
      listEl.innerHTML = '<div class="empty-box">Impossible de charger vos agents.</div>';
      agentsMenuEl.innerHTML = '<div class="empty-box" style="padding:10px;font-size:12px;">Impossible de charger vos agents. Réessayez en rouvrant le menu.</div>';
      agentsLoaded = false;
    }
  }

  async function loadAdminAgents(){
    try{
      var data = await api('/user/agents/admin-agents');
      adminAgentsCache = data.agents || [];
      populateAgentSelect();
    }catch(e){ console.error('Failed to load admin agents', e); }
  }

  function populateAgentSelect(){
    var select = document.getElementById('agentAgentId');
    if(!select) return;
    var current = select.value;
    select.innerHTML = '<option value="">Sélectionnez un modèle d\'agent</option>' + adminAgentsCache.map(function(a){
      return '<option value="'+a.id+'" '+(current==a.id?'selected':'')+'>'+esc(a.name)+' - '+esc(a.description||'Sans description')+'</option>';
    }).join('');
  }
  function loadCustomDomains() {
    try {
      var stored = localStorage.getItem('customAgentDomains');
      return stored ? JSON.parse(stored) : [];
    } catch (e) { return []; }
  }
  function saveCustomDomain(domain) {
    var domains = loadCustomDomains();
    if (!domains.includes(domain)) {
      domains.push(domain);
      try { localStorage.setItem('customAgentDomains', JSON.stringify(domains)); } catch (e) {}
    }
    return domains;
  }
  function populateDomainSelect() {
    var select = document.getElementById('agentDomain');
    var customInput = document.getElementById('agentDomainCustom');
    if (!select) return;
    var currentValue = select.value;
    var customDomains = loadCustomDomains();
    var baseOptions = [
      {value: '', text: 'Sélectionnez un domaine'},
      {value: 'community-management', text: 'Community Management'},
      {value: 'support-client', text: 'Support Client'},
      {value: 'recrutement', text: 'Recrutement / RH'},
      {value: 'veille-sectorielle', text: 'Veille Sectorielle'},
      {value: 'marketing-contenu', text: 'Marketing de Contenu'},
      {value: 'vente-prospection', text: 'Vente / Prospection'}
    ];
    var options = baseOptions.concat(customDomains.map(function(d) { return {value: d, text: d}; })).concat([{value: 'autre', text: 'Autre'}]);
    select.innerHTML = options.map(function(o) {
      return '<option value="'+esc(o.value)+'" '+(currentValue===o.value?'selected':'')+'>'+esc(o.text)+'</option>';
    }).join('');
    toggleCustomDomainField();
  }

  function openModal(userAgent){
    document.getElementById('agentUserAgentId').value = userAgent ? userAgent.id : '';
    document.getElementById('agentAgentId').value = userAgent ? (userAgent.agent_id||'') : '';
    document.getElementById('agentCustomName').value = userAgent ? (userAgent.custom_name||'') : '';
    document.getElementById('agentTone').value = userAgent ? (userAgent.tone||'') : '';
    document.getElementById('agentModalTitle').textContent = userAgent ? 'Modifier l\'agent' : 'Créer un agent';
    document.getElementById('agentSaveBtn').textContent = userAgent ? 'Enregistrer' : 'Créer l\'agent';
    populateDomainSelect();
    if (userAgent && userAgent.domain) {
      document.getElementById('agentDomain').value = userAgent.domain;
      toggleCustomDomainField();
    }
    // Handle profile photo preview
    var previewEl = document.getElementById('agentPhotoPreview');
    var removeBtn = document.getElementById('agentPhotoRemoveBtn');
    var removeInput = document.getElementById('agentRemovePhoto');
    if (userAgent && userAgent.profile_photo) {
      previewEl.innerHTML = '<img src="'+getPhotoUrl(userAgent.profile_photo)+'" alt="" style="width:100%;height:100%;object-fit:cover;">';
      removeBtn.style.display = 'inline-flex';
      removeInput.value = '0';
    } else {
      previewEl.innerHTML = '<svg class="ico" style="width:24px; height:24px; color:var(--muted);"><use href="#i-identity"/></svg>';
      removeBtn.style.display = 'none';
      removeInput.value = '0';
    }
    modal.classList.add('open');
    if (adminAgentsCache.length === 0) {
      loadAdminAgents();
    }
  }
  function toggleCustomDomainField() {
    var domainSelect = document.getElementById('agentDomain');
    var customInput = document.getElementById('agentDomainCustom');
    if (domainSelect.value === 'autre') {
      customInput.style.display = 'block';
      customInput.required = true;
    } else {
      customInput.style.display = 'none';
      customInput.required = false;
      customInput.value = '';
    }
  }
  document.getElementById('agentDomain').addEventListener('change', toggleCustomDomainField);
  function closeModal(){ modal.classList.remove('open'); form.reset(); document.getElementById('agentDomainCustom').style.display = 'none'; document.getElementById('agentDomainCustom').required = false; document.getElementById('agentPhotoPreview').innerHTML = '<svg class="ico" style="width:24px; height:24px; color:var(--muted);"><use href="#i-identity"/></svg>'; document.getElementById('agentPhotoRemoveBtn').style.display = 'none'; document.getElementById('agentRemovePhoto').value = '0'; }

  document.getElementById('agentAddBtn').addEventListener('click', function(){ openModal(null); });
  document.getElementById('agentModalClose').addEventListener('click', closeModal);
  document.getElementById('agentCancelBtn').addEventListener('click', closeModal);
  modal.addEventListener('click', function(e){ if(e.target===modal) closeModal(); });

  // Photo selection
  document.getElementById('agentPhotoSelectBtn').addEventListener('click', function(){
    document.getElementById('agentProfilePhoto').click();
  });
  document.getElementById('agentProfilePhoto').addEventListener('change', function(e){
    var file = e.target.files[0];
    if (file) {
      var reader = new FileReader();
      reader.onload = function(event) {
        document.getElementById('agentPhotoPreview').innerHTML = '<img src="'+event.target.result+'" alt="" style="width:100%;height:100%;object-fit:cover;">';
        document.getElementById('agentPhotoRemoveBtn').style.display = 'inline-flex';
        document.getElementById('agentRemovePhoto').value = '0';
      };
      reader.readAsDataURL(file);
    }
  });
  document.getElementById('agentPhotoRemoveBtn').addEventListener('click', function(){
    document.getElementById('agentProfilePhoto').value = '';
    document.getElementById('agentPhotoPreview').innerHTML = '<svg class="ico" style="width:24px; height:24px; color:var(--muted);"><use href="#i-identity"/></svg>';
    document.getElementById('agentPhotoRemoveBtn').style.display = 'none';
    document.getElementById('agentRemovePhoto').value = '1';
  });

  form.addEventListener('submit', async function(e){
    e.preventDefault();
    var id = document.getElementById('agentUserAgentId').value;
    var agentId = document.getElementById('agentAgentId').value;
    if(!agentId){ notify("Veuillez sélectionner un modèle d'agent.", 'error'); return; }
    var domainSelect = document.getElementById('agentDomain');
    var domain = domainSelect.value;
    if (domain === 'autre') {
      domain = document.getElementById('agentDomainCustom').value.trim();
      if (!domain) { notify("Veuillez saisir un domaine d'activité.", 'error'); return; }
      saveCustomDomain(domain);
      populateDomainSelect();
    }
    if(!domain){ notify("Veuillez sélectionner un domaine d'activité.", 'error'); return; }
    var tone = document.getElementById('agentTone').value;
    if(!tone){ notify("Veuillez sélectionner une tonalité.", 'error'); return; }

    var formData = new FormData();
    formData.append('agent_id', agentId);
    formData.append('custom_name', document.getElementById('agentCustomName').value.trim() || '');
    formData.append('domain', domain);
    formData.append('tone', tone);
    formData.append('_token', csrf);
    if (method === 'PUT') {
      formData.append('_method', 'PUT');
    }
    var photoFile = document.getElementById('agentProfilePhoto').files[0];
    if (photoFile) {
      formData.append('profile_photo', photoFile);
    }
    var removePhoto = document.getElementById('agentRemovePhoto').value === '1';
    if (removePhoto) {
      formData.append('remove_photo', '1');
    }

    var url = id ? '/user/agents/' + id : '/user/agents';
    var method = id ? 'POST' : 'POST';

    try{
      var res = await fetch(url, {
        method: method,
        headers: {
          'Accept': 'application/json',
          'X-Requested-With': 'XMLHttpRequest'
        },
        body: formData
      });
      var data = {};
      try { data = await res.json(); } catch(e) {}
      if (!res.ok) throw Object.assign(new Error(data.message||'Erreur'), {data:data, status:res.status});
      notify(data.message || (id ? 'Agent mis à jour' : 'Agent créé'), 'success');
      closeModal();
      loadUserAgents();
    }catch(err){ 
      console.error('Error', err);
      if (err.data && err.data.errors) {
        var messages = Object.values(err.data.errors).flat().join('\n');
        notify(messages, 'error');
      } else {
        notify(err.message || 'Erreur lors de l\'enregistrement', 'error'); 
      }
    }
  });

  listEl.addEventListener('click', async function(e){
    var btn = e.target.closest('[data-action]');
    if(!btn) return;
    var id = btn.getAttribute('data-id');
    var action = btn.getAttribute('data-action');
    if(action==='edit'){
      var ua = userAgentsCache.find(function(a){ return String(a.id)===String(id); });
      if (ua) openModal(ua);
      return;
    }
    if(action==='delete'){
      if(window.Swal){
        var r = await Swal.fire({title:'Supprimer cet agent ?', text:'Cette action est irréversible.', icon:'warning', showCancelButton:true, confirmButtonText:'Supprimer', cancelButtonText:'Annuler', confirmButtonColor:'#e85a5a'});
        if(!r.isConfirmed) return;
      }
      try{ var d = await api('/user/agents/'+id, {method:'DELETE'}); notify(d.message||'Agent supprimé','success'); loadUserAgents(); }
      catch(err){ notify(err.message||'Erreur','error'); }
    }
  });

  var agentsLoaded = false;
  function ensureAgentsLoad(){ if(!agentsLoaded){ agentsLoaded = true; loadUserAgents(); loadAdminAgents(); } }
  agentsToggle.addEventListener('click', function(){
    setTimeout(ensureAgentsLoad, 0);
  });
  agentsMenuEl.addEventListener('click', function(e){
    var toggle = e.target.closest('[data-user-agent-toggle]');
    if(!toggle) return;

    var selectedId = toggle.getAttribute('data-user-agent-id');
    var tools = agentsMenuEl.querySelector('[data-user-agent-tools="'+selectedId+'"]');
    var shouldOpen = !tools.classList.contains('open');
    agentsMenuEl.querySelectorAll('[data-user-agent-tools]').forEach(function(menu){
      menu.classList.remove('open');
    });
    agentsMenuEl.querySelectorAll('[data-user-agent-toggle]').forEach(function(button){
      button.classList.remove('selected');
      button.setAttribute('aria-expanded', 'false');
    });
    activeUserAgentId = shouldOpen ? selectedId : null;
    if(shouldOpen){
      tools.classList.add('open');
      toggle.classList.add('selected');
      toggle.setAttribute('aria-expanded', 'true');
    }
  });
  if(document.getElementById('page-my-agents') && document.getElementById('page-my-agents').classList.contains('active')) ensureAgentsLoad();
})();
</script>
</body>
</html>