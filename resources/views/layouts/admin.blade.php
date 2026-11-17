<!DOCTYPE html>
<html lang="fr">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1, viewport-fit=cover">
<title>IA Agent Studio — @yield('title', 'Administration')</title>
<meta http-equiv="Cache-Control" content="no-cache, no-store, must-revalidate">
<meta http-equiv="Pragma" content="no-cache">
<meta http-equiv="Expires" content="0">
<link rel="preconnect" href="https://fonts.googleapis.com">
<link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
<link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&display=swap" rel="stylesheet">
<link rel="stylesheet" href="{{ asset('vendor/sweetalert2/sweetalert2.min.css') }}">
<script src="{{ asset('vendor/sweetalert2/sweetalert2.min.js') }}"></script>
<style>
:root{
  --bg:#07070a; --panel:#111116; --panel-2:#0c0c11; --border:#1e1e27; --border-2:#26262f;
  --text:#f2f2f6; --muted:#9a9aab; --muted-2:#6c6c7c;
  --cyan:#2bc7e8; --violet:#8b7bf0; --pink:#ec5fa0;
  --green:#34c27a; --orange:#f0a93c; --red:#e85a5a;
  --sidebar-w:258px;
}
html{box-sizing:border-box; scroll-padding-top:env(safe-area-inset-top,0px);}
*, *::before, *::after{box-sizing:inherit;}
body{margin:0; background:var(--bg); color:var(--text); font-family:'Inter',system-ui,sans-serif;
  padding-top:env(safe-area-inset-top,0px); padding-bottom:env(safe-area-inset-bottom,0px); -webkit-font-smoothing:antialiased;}
button,input,select,textarea{font-family:inherit;}
a{color:inherit; text-decoration:none;}
.app{display:flex; min-height:100vh;}
.sidebar{width:var(--sidebar-w); flex-shrink:0; background:var(--panel-2); border-right:1px solid var(--border);
  display:flex; flex-direction:column; padding:18px 14px; position:fixed; top:0; bottom:0; left:0; overflow-y:auto;
  transition:transform .25s ease; z-index:50;}
.brand{display:flex; align-items:center; gap:9px; padding:8px 8px 6px; font-weight:800; font-size:16px;}
.brand .logo{width:26px; height:26px; border-radius:7px; background:linear-gradient(135deg,var(--cyan),var(--violet)); flex-shrink:0;}
.brand-tag{margin:0 8px 18px; font-size:11px; color:var(--red); background:rgba(232,90,90,.12); border:1px solid rgba(232,90,90,.3); padding:4px 10px; border-radius:20px; display:inline-block; font-weight:700; letter-spacing:.03em;}
.nav-item{display:flex; align-items:center; gap:11px; padding:10px 12px; border-radius:9px; font-size:14px; color:#cfcfe0; cursor:pointer; white-space:nowrap;}
.nav-item:hover{background:#17171f;}
.nav-item.active{background: #17B9FF; color: #fff; font-weight:600;}
.nav-item .ic{width:22px; text-align:center; flex-shrink:0;}
.nav-group-item{padding:0; gap:0;}
.nav-group-link{display:flex; align-items:center; gap:11px; flex:1; min-width:0; padding:10px 0 10px 12px;}
.nav-group-toggle{display:flex; align-items:center; justify-content:center; padding:10px 12px; border:0; border-radius:9px; background:none; color:inherit; cursor:pointer;}
.sidebar-foot{margin-top:auto; background:var(--panel); border:1px solid var(--border); border-radius:12px; padding:14px; font-size:12.5px; color:var(--muted);}
.main{margin-left:var(--sidebar-w); flex:1; min-width:0;}
.topbar{display:flex; align-items:center; gap:16px; padding:14px 28px; border-bottom:1px solid var(--border); position:sticky; top:0; background:rgba(7,7,10,.9); backdrop-filter:blur(8px); z-index:60; flex-wrap:nowrap; min-height:64px; overflow:visible;}
.menu-btn{display:none; background:var(--panel); border:1px solid var(--border); color:var(--text); width:36px; height:36px; border-radius:9px; align-items:center; justify-content:center; cursor:pointer; flex-shrink:0;}
.search{flex:1 1 260px; max-width:380px; min-width:0; display:flex; align-items:center; gap:8px; background:var(--panel); border:1px solid var(--border); border-radius:10px; padding:8px 14px;}
.search input{background:none; border:none; outline:none; color:var(--text); font-size:13.5px; width:100%; min-width:0;}
.topbar-right{display:flex; align-items:center; gap:14px; margin-left:auto; flex-shrink:0; overflow:visible;}
.icon-btn{width:36px; height:36px; border-radius:50%; background:var(--panel); border:1px solid var(--border); display:flex; align-items:center; justify-content:center; cursor:pointer; position:relative; color:var(--text);}
.dot-badge{position:absolute; top:-3px; right:-3px; background:var(--red); color:#fff; font-size:10px; border-radius:50%; width:16px; height:16px; display:flex; align-items:center; justify-content:center;}
.user-chip{display:flex; align-items:center; gap:9px; cursor:pointer; background:none; border:none; padding:0; color:inherit; font:inherit;}
.user-chip:focus{outline:none;}
.user-menu{position:relative; overflow:visible;}
.user-dropdown{position:absolute; right:0; top:calc(100% + 10px); background:var(--panel); border:1px solid var(--border-2); border-radius:12px; min-width:180px; padding:6px; display:none; box-shadow:0 12px 30px rgba(0,0,0,.45); z-index:60;}
.user-dropdown.open{display:block;}
.user-dropdown a,.user-dropdown button{display:flex; align-items:center; gap:8px; width:100%; padding:10px 12px; border:none; background:none; color:var(--text); font-size:13.5px; border-radius:8px; cursor:pointer; text-align:left; text-decoration:none;}
.user-dropdown a:hover,.user-dropdown button:hover{background:#1a1a23;}
.user-dropdown .danger{color:var(--red);}
.avatar{width:34px; height:34px; border-radius:50%; background:linear-gradient(135deg,var(--red),var(--violet)); display:flex; align-items:center; justify-content:center; font-weight:700; font-size:12px;}
.user-chip .name{font-size:13.5px; font-weight:600; line-height:1.2;}
.user-chip .plan{font-size:11.5px; color:var(--muted);}
.content{padding:26px 28px 60px;}
.page{display:none;}
.page.active{display:block;}
.page-head{display:flex; justify-content:space-between; align-items:flex-end; gap:16px; flex-wrap:wrap; margin-bottom:22px;}
.page-head h1{font-size:22px; margin:0 0 6px;}
.page-head p{margin:0; color:var(--muted); font-size:14px;}
.page-head .actions{display:flex; gap:8px;}
.kpis{display:grid; grid-template-columns:repeat(4,1fr); gap:16px; margin-bottom:20px;}
.kpi{background:var(--panel); border:1px solid var(--border); border-radius:14px; padding:18px;}
.kpi .top{display:flex; justify-content:space-between; align-items:center; margin-bottom:10px;}
.kpi .ic{width:30px; height:30px; border-radius:8px; display:flex; align-items:center; justify-content:center; font-size:14px;}
.kpi.c1 .ic{background:#132a3a; color:var(--cyan);}
.kpi.c2 .ic{background:#20142f; color:var(--violet);}
.kpi.c3 .ic{background:#132b20; color:var(--green);}
.kpi.c4 .ic{background:#2f1c0d; color:var(--orange);}
.kpi .delta{font-size:11.5px; font-weight:700; color:var(--green);}
.kpi .delta.down{color:var(--red);}
.kpi .num{font-size:25px; font-weight:800;}
.kpi .lbl{font-size:12.5px; color:var(--muted); margin-top:3px;}
.opacity-50{opacity:0.55;}
.two-col{display:grid; grid-template-columns:1.5fr 1fr; gap:18px; margin-bottom:20px;}
.panel{background:var(--panel); border:1px solid var(--border); border-radius:16px; padding:22px;}
.panel-head{display:flex; justify-content:space-between; align-items:flex-start; margin-bottom:16px; gap:10px; flex-wrap:wrap;}
.panel-head h2{margin:0 0 4px; font-size:16px; font-weight:700;}
.panel-head p{margin:0; font-size:13px; color:var(--muted);}
.panel-head .link{font-size:13px; color:var(--cyan); white-space:nowrap; cursor:pointer;}
.bars{display:flex; align-items:flex-end; gap:10px; height:140px; padding-top:10px;}
.bars .bar-col{flex:1; display:flex; flex-direction:column; align-items:center; gap:8px; justify-content:flex-end; height:100%;}
.bars .bar-col i{display:block; width:100%; max-width:30px; border-radius:6px 6px 0 0; background:linear-gradient(180deg,var(--cyan),var(--violet));}
.bars .bar-col span{font-size:11px; color:var(--muted);}
.activity{display:flex; flex-direction:column; gap:14px;}
.activity-item{display:flex; gap:12px; font-size:13px;}
.activity-item .dot{width:8px; height:8px; border-radius:50%; margin-top:6px; flex-shrink:0;}
.activity-item .dot.g{background:var(--green);} .activity-item .dot.o{background:var(--orange);} .activity-item .dot.r{background:var(--red);} .activity-item .dot.c{background:var(--cyan);}
.activity-item .t{color:var(--text);}
.activity-item .time{color:var(--muted-2); font-size:12px; margin-top:2px;}
.toolbar{display:flex; gap:10px; margin-bottom:16px; flex-wrap:wrap; align-items:center;}
.pillf{font-size:12.5px; padding:7px 14px; border-radius:20px; border:1px solid var(--border); color:var(--muted); cursor:pointer;}
.pillf.active{background:var(--cyan); color:#031015; border-color:var(--cyan); font-weight:600;}
.table-wrap{overflow-x:auto;}
table.data{width:100%; border-collapse:collapse; font-size:13.5px; min-width:640px;}
table.data th{text-align:left; color:var(--muted); font-weight:600; font-size:12px; padding:10px 14px; border-bottom:1px solid var(--border); white-space:nowrap;}
table.data td{padding:12px 14px; border-bottom:1px solid var(--border); vertical-align:middle;}
.client-cell{display:flex; align-items:center; gap:10px;}
.client-cell .av{width:30px; height:30px; border-radius:50%; background:#3a3a46; display:flex; align-items:center; justify-content:center; font-weight:700; font-size:11.5px; flex-shrink:0;}
.client-cell .nm{font-weight:600;}
.client-cell .em{font-size:12px; color:var(--muted);}
.pill{padding:4px 10px; border-radius:20px; font-size:11.5px; font-weight:600; white-space:nowrap;}
.pill.g{background:rgba(52,194,122,.12); color:var(--green);}
.pill.o{background:rgba(240,169,60,.12); color:var(--orange);}
.pill.v{background:rgba(139,123,240,.15); color:var(--violet);}
.pill.r{background:rgba(232,90,90,.12); color:var(--red);}
.row-actions{display:flex; gap:8px;}
.icbtn{width:28px; height:28px; border-radius:7px; background:var(--panel-2); border:1px solid var(--border); display:flex; align-items:center; justify-content:center; cursor:pointer; font-size:12px; text-decoration:none;}
.plan-grid{display:grid; grid-template-columns:repeat(3,1fr); gap:16px; margin-bottom:20px;}
.plan-card{background:var(--panel); border:1px solid var(--border); border-radius:14px; padding:20px;}
.plan-card h3{margin:0 0 6px; font-size:15px;}
.plan-card .amt{font-size:24px; font-weight:800; margin-bottom:6px;}
.plan-card .sub{font-size:12.5px; color:var(--muted);}
.ticket{display:flex; gap:12px; border:1px solid var(--border); border-radius:12px; padding:14px; background:var(--panel-2); margin-bottom:10px; align-items:flex-start; flex-wrap:wrap;}
.ticket .av{width:34px; height:34px; border-radius:50%; background:#3a3a46; flex-shrink:0; display:flex; align-items:center; justify-content:center; font-weight:700; font-size:12px;}
.ticket .body{flex:1; min-width:180px;}
.ticket .t{font-weight:600; font-size:13.5px;}
.ticket .s{font-size:12.5px; color:var(--muted); margin-top:2px;}
.toggle-row{display:flex; justify-content:space-between; align-items:center; padding:13px 0; border-bottom:1px solid var(--border); max-width:500px;}
.toggle-row .lbl{font-size:14px;}
.toggle-row .sub{font-size:12.5px; color:var(--muted); margin-top:2px;}
.switch{width:40px; height:22px; background:#2a2a36; border-radius:20px; position:relative; cursor:pointer; flex-shrink:0;}
.switch.on{background:var(--cyan);}
.switch i{position:absolute; top:2px; left:2px; width:18px; height:18px; border-radius:50%; background:#fff; transition:left .18s;}
.switch.on i{left:20px;}
.form-group{margin-bottom:18px;}
.form-group label{display:block; font-size:13px; color:var(--text); margin-bottom:6px; font-weight:600;}
.form-group label small{font-weight:400; color:var(--muted);}
input,select,textarea{width:100%; background:var(--panel); border:1px solid var(--border); border-radius:9px; padding:10px 14px; color:var(--text); font-size:13.5px; box-sizing:border-box;}
input:focus,select:focus,textarea:focus{outline:none; border-color:var(--cyan);}
textarea{min-height:120px; resize:vertical;}
.help{font-size:12px; color:var(--muted-2); margin-top:4px;}
.alert{padding:12px 16px; border-radius:10px; margin-bottom:16px; font-size:13.5px;}
.alert.error{background:rgba(232,90,90,.12); color:var(--red); border:1px solid rgba(232,90,90,.3);}
.alert.success{background:rgba(52,194,122,.12); color:var(--green); border:1px solid rgba(52,194,122,.3);}
.btn{display:inline-flex; align-items:center; gap:8px; padding:10px 18px; border-radius:10px; border:1px solid var(--border); background:var(--panel); color:var(--text); font-size:13.5px; cursor:pointer; text-decoration:none;}
.btn:hover{background:#17171f;}
.btn.primary{background:var(--cyan); color:#031015; border-color:var(--cyan); font-weight:600;}
.btn.secondary{background:transparent; color:var(--muted);}
.btn.secondary:hover{background:#17171f; color:var(--text);}
.btn.danger{color:var(--red); border-color:rgba(232,90,90,.3);}
.btn.danger:hover{background:rgba(232,90,90,.1);}
.row{display:flex; gap:16px; flex-wrap:wrap;}
.col-2{flex:1 1 200px;}
.col-3{flex:1 1 200px;}
.col-full{flex:1 1 100%;}
.checkbox-row{display:flex; align-items:center; gap:10px; margin-top:6px;}
.checkbox-row input{width:auto;}
.errors{background:rgba(232,90,90,.12); color:var(--red); border:1px solid rgba(232,90,90,.3); border-radius:10px; padding:12px 16px; margin-bottom:16px; font-size:13px;}
.errors ul{margin:6px 0 0; padding-left:20px;}
.errors li{margin-bottom:4px.}
.features-list{list-style:none; margin:0; padding:0;}
.features-list li{padding:8px 0; border-bottom:1px solid var(--border); font-size:13.5px;}
.features-list li:before{content:"• "; color:var(--cyan); font-weight:800;}
@media (max-width:980px){
  .two-col{grid-template-columns:1fr;}
  .kpis{grid-template-columns:1fr 1fr;}
  .plan-grid{grid-template-columns:1fr;}
}
@media (max-width:760px){
  .sidebar{transform:translateX(-100%);}
  .sidebar.open{transform:translateX(0); box-shadow:0 0 0 2000px rgba(0,0,0,.5);}
  .main{margin-left:0;}
  .menu-btn{display:flex;}
  .kpis{grid-template-columns:1fr 1fr;}
  .search{display:none;}
}
.ico{width:1.15em;height:1.15em;display:inline-block;vertical-align:-0.125em;fill:none;stroke:currentColor;stroke-width:1.8;stroke-linecap:round;stroke-linejoin:round;flex-shrink:0;}
.ic .ico,.icon-btn .ico,.menu-btn .ico{width:22px;height:22px;}
.kpi .ic .ico{width:18px;height:18px;}
.nav-item .ic{display:inline-flex;align-items:center;justify-content:center;}
.nav-group-toggle .ico,.agent-trigger > .ico{width:20px;height:20px;}
.brand-tag .ico{width:16px;height:16px;vertical-align:-2px;}
</style>
@stack('styles')
</head>
<body>
<div class="app">

<aside class="sidebar" id="sidebar">
    <div class="brand"><img src="/assets/1.png" alt="IA Agent Studio" style="height:48px;width:auto;display:block;"></div>
    <span class="brand-tag"><x-lucide-alert-triangle class="ico" /> ADMINISTRATION</span>

    <a class="nav-item @if(request()->routeIs('admin.dashboard')) active @endif" href="{{ route('admin.dashboard') }}" data-page="overview"><span class="ic"><x-lucide-layout-dashboard class="ico" /></span> Vue d'ensemble</a>
    <a class="nav-item @if(request()->routeIs('admin.clients.*')) active @endif" href="{{ route('admin.clients.index') }}" data-page="clients"><span class="ic"><x-lucide-users class="ico" /></span> Clients & Comptes</a>
    
    <div class="nav-group">
        <div class="nav-item nav-group-item @if(request()->routeIs('admin.agents.*') || request()->routeIs('admin.agent-categories.*')) active @endif">
            <a class="nav-group-link" href="{{ route('admin.agents.index') }}"><span class="ic"><x-lucide-bot class="ico" /></span> Agents IA</a>
            <button class="nav-group-toggle" type="button" data-toggle="agents-level1" aria-label="Afficher les agents" aria-expanded="false" aria-controls="agents-level1-menu">
                <x-lucide-chevron-right class="ico" style="transition:transform .2s;" id="agents-level1-chevron" />
            </button>
        </div>
        
        <!-- Level 1: List of admin-managed agents -->
        <div class="nav-submenu level-1" id="agents-level1-menu" style="display:none; margin-left:8px; margin-top:4px; padding-left:4px; border-left:1px solid var(--border);">
            <a class="nav-item @if(request()->routeIs('admin.agent-categories.*')) active @endif" href="{{ route('admin.agent-categories.index') }}" style="padding:8px 12px; font-size:12.5px;"><span class="ic"><x-lucide-folder class="ico" style="width:15px;height:15px;" /></span> Catégories d'agents</a>
            @if($adminAgents->isNotEmpty())
                @foreach($adminAgents as $agent)
                    <button class="nav-item agent-trigger" type="button" data-agent-id="{{ $agent->id }}" data-toggle="agent-level2" aria-expanded="false" aria-haspopup="true" style="padding:8px 12px; font-size:13px; width:100%; text-align:left; background:none; border:none; color:inherit; cursor:pointer;">
                        <span class="ic"><x-lucide-user-round class="ico" style="width:18px;height:18px;" /></span> {{ $agent->name }}
                        <x-lucide-chevron-right class="ico" style="margin-left:auto;transition:transform .2s;" />
                    </button>
                    
                    <!-- Level 2: Options for this specific agent (hidden by default) -->
                    <div class="nav-submenu level-2" id="agent-{{ $agent->id }}-menu" style="display:none; margin-left:16px; margin-top:4px; padding-left:4px; border-left:1px solid var(--border-2);">
                        <a class="nav-item @if(request()->routeIs('admin.agents.'.$agent->id.'.calendrier')) active @endif" href="{{ route('admin.agents.show', ['agent' => $agent->id, 'section' => 'calendrier']) }}" style="padding:8px 12px; font-size:12.5px;"><span class="ic"><x-lucide-calendar class="ico" style="width:15px;height:15px;" /></span> Calendrier Éditorial</a>
                        <a class="nav-item @if(request()->routeIs('admin.agents.'.$agent->id.'.studio')) active @endif" href="{{ route('admin.agents.show', ['agent' => $agent->id, 'section' => 'studio']) }}" style="padding:8px 12px; font-size:12.5px;"><span class="ic"><x-lucide-edit class="ico" style="width:15px;height:15px;" /></span> Studio des Publications</a>
                        <a class="nav-item @if(request()->routeIs('admin.agents.'.$agent->id.'.identite')) active @endif" href="{{ route('admin.agents.show', ['agent' => $agent->id, 'section' => 'identite']) }}" style="padding:8px 12px; font-size:12.5px;"><span class="ic"><x-lucide-user-round class="ico" style="width:15px;height:15px;" /></span> Identité & Inspiration</a>
                        <a class="nav-item @if(request()->routeIs('admin.agents.'.$agent->id.'.veille')) active @endif" href="{{ route('admin.agents.show', ['agent' => $agent->id, 'section' => 'veille']) }}" style="padding:8px 12px; font-size:12.5px;"><span class="ic"><x-lucide-bell class="ico" style="width:15px;height:15px;" /></span> Veille & Tendances</a>
                        <a class="nav-item @if(request()->routeIs('admin.agents.'.$agent->id.'.abonnements')) active @endif" href="{{ route('admin.agents.show', ['agent' => $agent->id, 'section' => 'abonnements']) }}" style="padding:8px 12px; font-size:12.5px;"><span class="ic"><x-lucide-credit-card class="ico" style="width:15px;height:15px;" /></span> Abonnements & Quotas</a>
                    </div>
                @endforeach
            @else
                <div style="padding:12px; font-size:13px; color:var(--muted);">Aucun agent créé</div>
                <a class="nav-item" href="{{ route('admin.agents.create') }}" style="padding:8px 12px; font-size:13px; margin-left:4px;"><span class="ic"><x-lucide-plus class="ico" style="width:18px;height:18px;" /></span> Créer un agent</a>
            @endif
        </div>
    </div>
    
    <a class="nav-item @if(request()->routeIs('admin.subscriptions.*')) active @endif" href="{{ route('admin.subscriptions.index') }}" data-page="facturation"><span class="ic"><x-lucide-credit-card class="ico" /></span> Abonnements & Facturation</a>
    <a class="nav-item @if(request()->routeIs('admin.support.*')) active @endif" href="{{ route('admin.support.index') }}" data-page="support"><span class="ic"><x-lucide-mail class="ico" /></span> Support</a>
    <a class="nav-item @if(request()->routeIs('admin.securite.*')) active @endif" href="{{ route('admin.securite.index') }}" data-page="securite"><span class="ic"><x-lucide-shield-check class="ico" /></span> Sécurité & Logs</a>
    <a class="nav-item @if(request()->routeIs('admin.systeme.*')) active @endif" href="{{ route('admin.systeme.index') }}" data-page="systeme"><span class="ic"><x-lucide-settings class="ico" /></span> Paramètres système</a>

    <div class="sidebar-foot">Connecté en tant que <b style="color:var(--text)">Super Admin</b><br>Accès complet à toutes les instances clients.</div>
</aside>

<div class="main">
    <div class="topbar">
        <button class="menu-btn" id="menuBtn"><x-lucide-menu class="ico" /></button>
        <div class="search"><input placeholder="Rechercher un client, un ticket, un ID…"></div>
        <div class="topbar-right">
            <button class="icon-btn"><x-lucide-bell class="ico" /><span class="dot-badge">5</span></button>
<div class="user-menu" id="userMenu">
    <button class="user-chip" id="userChip" type="button" aria-expanded="false" aria-haspopup="true" onclick="event.stopPropagation(); var dd=document.getElementById('userDropdown'); dd.classList.toggle('open'); this.setAttribute('aria-expanded', dd.classList.contains('open'));">
        <div class="avatar">SA</div>
        <div><div class="name">Super Admin</div><div class="plan">Accès total</div></div>
        <span style="color:var(--muted)">▾</span>
    </button>
    <div class="user-dropdown" id="userDropdown" role="menu">
        <a href="/logout" class="danger" role="menuitem"><x-lucide-log-out class="ico" style="width:18px;height:18px;vertical-align:-2px" /> Déconnexion</a>
    </div>
</div>
        </div>
    </div>

    <div class="content">
        @yield('content')
    </div>
</div>
</div>

<script>
var sidebar = document.getElementById('sidebar');
var menuBtn = document.getElementById('menuBtn');
if (sidebar && menuBtn) {
  menuBtn.addEventListener('click', function(){ sidebar.classList.toggle('open'); });
}

var userChip = document.getElementById('userChip');
var userDropdown = document.getElementById('userDropdown');
var userMenu = document.getElementById('userMenu');
if(userChip && userDropdown && userMenu){
  userChip.addEventListener('click', function(e){
    e.stopPropagation();
    var isOpen = userDropdown.classList.toggle('open');
    userChip.setAttribute('aria-expanded', isOpen);
    console.log('Dropdown toggled:', isOpen);
  });
}

document.addEventListener('click', function(e){
  if(sidebar && window.innerWidth<=760 && sidebar.classList.contains('open') && !sidebar.contains(e.target) && e.target.id!=='menuBtn'){
    sidebar.classList.remove('open');
  }
  if(userDropdown && userDropdown.classList.contains('open') && userMenu){
    if(!userMenu.contains(e.target)){ userDropdown.classList.remove('open'); }
  }
});

// Level 1: Agents IA main dropdown
var agentsLevel1Btn = document.querySelector('[data-toggle="agents-level1"]');
var agentsLevel1Menu = document.getElementById('agents-level1-menu');
var agentsLevel1Chevron = document.getElementById('agents-level1-chevron');
if(agentsLevel1Btn && agentsLevel1Menu && agentsLevel1Chevron){
  agentsLevel1Btn.addEventListener('click', function(e){
    e.stopPropagation();
    var isOpen = agentsLevel1Menu.style.display !== 'none';
    agentsLevel1Menu.style.display = isOpen ? 'none' : 'block';
    agentsLevel1Chevron.style.transform = isOpen ? 'rotate(0deg)' : 'rotate(90deg)';
    agentsLevel1Btn.setAttribute('aria-expanded', !isOpen);
  });
  // Auto-open if on any agent route
  @if(request()->routeIs('admin.agents.*'))
  agentsLevel1Menu.style.display = 'block';
  agentsLevel1Chevron.style.transform = 'rotate(90deg)';
  agentsLevel1Btn.setAttribute('aria-expanded', 'true');
  @endif
}

// Level 2: Individual agent dropdowns
document.querySelectorAll('[data-toggle="agent-level2"]').forEach(function(btn){
  var agentId = btn.getAttribute('data-agent-id');
  var submenu = document.getElementById('agent-' + agentId + '-menu');
  var chevron = btn.querySelector('x-lucide-chevron-right:last-child, svg:last-child');
  if(!submenu || !chevron) return;
  btn.addEventListener('click', function(e){
    e.stopPropagation();
    var isOpen = submenu.style.display !== 'none';
    // Close other agent submenus
    document.querySelectorAll('.nav-submenu.level-2').forEach(function(m){
      if(m !== submenu) m.style.display = 'none';
    });
    document.querySelectorAll('[data-toggle="agent-level2"] x-lucide-chevron-right:last-child, [data-toggle="agent-level2"] svg:last-child').forEach(function(c){
      if(c !== chevron) c.style.transform = 'rotate(0deg)';
    });
    submenu.style.display = isOpen ? 'none' : 'block';
    chevron.style.transform = isOpen ? 'rotate(0deg)' : 'rotate(90deg)';
    btn.setAttribute('aria-expanded', !isOpen);
  });
  // Auto-open if on this agent's child route
  @php
    $currentAgentId = request()->route('agent');
    $currentSection = request()->route('section');
  @endphp
  @if($currentAgentId && $currentSection)
  if('{{ $currentAgentId }}' === '{{ $agent->id }}'){
    submenu.style.display = 'block';
    chevron.style.transform = 'rotate(90deg)';
    btn.setAttribute('aria-expanded', 'true');
    // Also ensure level 1 is open
    if(agentsLevel1Menu) agentsLevel1Menu.style.display = 'block';
    if(agentsLevel1Chevron) agentsLevel1Chevron.style.transform = 'rotate(90deg)';
    if(agentsLevel1Btn) agentsLevel1Btn.setAttribute('aria-expanded', 'true');
  }
  @endif
});

// Close dropdowns when clicking outside
document.addEventListener('click', function(e){
  if(agentsLevel1Menu && agentsLevel1Menu.style.display !== 'none'){
    if(!agentsLevel1Btn.contains(e.target) && !agentsLevel1Menu.contains(e.target)){
      agentsLevel1Menu.style.display = 'none';
      agentsLevel1Chevron.style.transform = 'rotate(0deg)';
      agentsLevel1Btn.setAttribute('aria-expanded', 'false');
    }
  }
  document.querySelectorAll('[data-toggle="agent-level2"]').forEach(function(btn){
    var agentId = btn.getAttribute('data-agent-id');
    var submenu = document.getElementById('agent-' + agentId + '-menu');
    var chevron = btn.querySelector('x-lucide-chevron-right:last-child, svg:last-child');
    if(submenu && submenu.style.display !== 'none'){
      if(!btn.contains(e.target) && !submenu.contains(e.target)){
        submenu.style.display = 'none';
        if(chevron) chevron.style.transform = 'rotate(0deg)';
        btn.setAttribute('aria-expanded', 'false');
      }
    }
  });
});

document.querySelectorAll('[data-switch]').forEach(function(sw){
    sw.addEventListener('click', function(){ sw.classList.toggle('on'); });
});

document.querySelectorAll('.toolbar .pillf').forEach(function(p){
  p.addEventListener('click', function(){
    p.parentElement.querySelectorAll('.pillf').forEach(function(x){x.classList.remove('active');});
    p.classList.add('active');
  });
});

document.addEventListener("pageshow", function(e){ if(e.persisted){ window.location.reload(); } });

@if(session('success') || session('error'))
<script>
document.addEventListener('DOMContentLoaded', function(){
  @if(session('success'))
  Swal.fire({
    toast: true,
    position: 'bottom-end',
    icon: 'success',
    title: '{{ addslashes(session('success')) }}',
    showConfirmButton: false,
    timer: 2500,
    timerProgressBar: true,
    background: '#111116',
    color: '#f2f2f6',
    customClass: { popup: 'swal2-toast-admin' }
  });
  @endif
  @if(session('error'))
  Swal.fire({
    toast: true,
    position: 'bottom-end',
    icon: 'error',
    title: '{{ addslashes(session('error')) }}',
    showConfirmButton: false,
    timer: 3500,
    timerProgressBar: true,
    background: '#111116',
    color: '#f2f2f6',
    customClass: { popup: 'swal2-toast-admin' }
  });
  @endif
});
</script>
@endif
<script>document.documentElement.classList.add('js-loaded');</script>
@stack('scripts')
</body>
</html>