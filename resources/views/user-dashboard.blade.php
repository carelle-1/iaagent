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
<link href="https://fonts.googleapis.com/css2?family=Century+Gothic:wght@400;500;600;700;800&display=swap" rel="stylesheet">
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/sweetalert2@11/dist/sweetalert2.min.css">
<link rel="icon" href="{{ asset('assets/1.png') }}" type="image/png">
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
body{margin:0; background:var(--bg); color:var(--text); font-family:'Century Gothic',system-ui,sans-serif;
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
.nav-item .ic{width:22px; text-align:center; flex-shrink:0;}
.nav-item .chev{margin-left:auto; font-size:11px; opacity:.6;}
.sub{padding-left:10px; max-height:0; overflow:hidden; transition:max-height .22s ease;}
.sub.open{max-height:1000px;}
.sub#servicesSub.open,.sub#userAgentsSub.open,.sub.agent-tools.open{max-height:none;overflow:visible;}
.sub .nav-item{font-size:13.5px; padding-left:16px; color:var(--subnav-text); white-space:normal;}
.sub .sub{padding-left:8px;}
.nav-item.nav-toggle{width:100%;border:0;background:transparent;text-align:left;font-family:inherit;}
.agent-tools .nav-item{padding-left:16px;}
.category-toggle .cat-count{margin-left:4px;font-size:11px;padding:1px 6px;border-radius:20px;background:var(--panel-2);color:var(--muted);font-weight:600;}
.category-toggle.active{background:#17B9FF;color:#FFF;font-weight:600;}
.sub.category-agent-list.open{max-height:none;overflow:visible;}
.cat-section-label{font-size:11px;text-transform:uppercase;letter-spacing:.04em;color:var(--muted);padding:8px 16px 4px;font-weight:600;}
.category-agent-list .agent-tools .nav-item{padding-left:16px;}
.category-agent-list .empty-box{margin:4px 12px;}
.agent-card-modules{border-top:1px dashed var(--border);padding-top:8px;}
.agent-card-modules-head{font-size:12.5px;color:var(--muted);cursor:pointer;user-select:none;display:flex;justify-content:space-between;align-items:center;}
.agent-card-modules-head.open .chev{transform:rotate(180deg);}
.agent-card-module-grid{grid-template-columns:1fr;gap:6px;margin-top:8px;}
.agent-card-module-grid .btn-inline{justify-content:flex-start;width:100%;}
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
.lang{display:flex; align-items:center; gap:6px; background:var(--panel); border:1px solid var(--border); border-radius:20px; padding:7px 14px; font-size:13px; cursor:pointer; position:relative;}
.lang-dropdown{position:absolute; right:0; top:calc(100% + 8px); background:var(--panel); border:1px solid var(--border-2); border-radius:12px; min-width:160px; padding:6px; display:none; box-shadow:0 12px 30px rgba(0,0,0,.45); z-index:60;}
.lang-option{display:flex; align-items:center; gap:8px; width:100%; padding:8px 12px; border:none; background:none; color:var(--text); font-size:13px; border-radius:8px; cursor:pointer; text-align:left;}
.lang-option:hover{background:var(--dropdown-hover);}
.lang-option.active{background:var(--cyan); color:#031015; font-weight:600;}
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
.page{display:none; position:relative;}
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
.stat{background:var(--panel); border:1px solid var(--border); border-radius:14px; padding:18px; cursor:pointer; transition:all .15s;}
.stat:hover{background:#17171f; border-color:var(--border-2);}
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
.pill{padding:4px 10px; border-radius:20px; font-size:11.5px; font-weight:600; white-space:nowrap;}
.pill.g{background:rgba(52,194,122,.12); color:var(--green);}
.pill.o{background:rgba(240,169,60,.12); color:var(--orange);}
.pill.v{background:rgba(139,123,240,.15); color:var(--violet);}
table.data .col-statut{width:150px; min-width:150px; white-space:nowrap;}
.cal-menu-btn{min-width:42px; justify-content:center; font-size:16px; letter-spacing:1px; line-height:1;}
.cal-action-menu{position:fixed; z-index:300; display:none; min-width:160px; padding:6px; background:var(--panel); border:1px solid var(--border-2); border-radius:10px; box-shadow:0 12px 30px rgba(0,0,0,.45);}
.cal-action-menu.open{display:block;}
.cal-action-menu button{display:flex; align-items:center; gap:8px; width:100%; padding:9px 10px; border:0; background:none; color:var(--text); font:inherit; font-size:13px; text-align:left; border-radius:7px; cursor:pointer;}
.cal-action-menu button:hover{background:var(--dropdown-hover);}
.cal-action-menu button.danger{color:var(--red);}
.cal-action-menu button.primary{color:var(--cyan);}
.cal-action-menu button .ico{width:18px; height:18px;}
.identity-images-grid{display:grid; grid-template-columns:repeat(auto-fill, minmax(120px, 1fr)); gap:10px;}
.identity-image-card{border:1px solid var(--border); border-radius:12px; overflow:hidden; background:var(--panel-2);}
.identity-image-card img{display:block; width:100%; height:110px; object-fit:cover;}
.identity-image-caption{padding:8px 8px 0; font-size:12px; color:var(--muted); word-break:break-word;}
.identity-image-actions{padding:8px; display:flex; justify-content:flex-end;}
.studio-layout{display:grid; grid-template-columns:minmax(260px, 340px) 1fr; gap:16px; align-items:start;}
.studio-list{display:flex; flex-direction:column; gap:8px; max-height:72vh; overflow-y:auto; padding-right:4px;}
.studio-list .empty-box{width:100%;}
.studio-list-item{display:flex; gap:10px; align-items:flex-start; width:100%; text-align:left; border:1px solid var(--border); border-radius:12px; padding:10px; background:var(--panel-2); cursor:pointer; font:inherit; color:inherit; transition:border-color .15s, background .15s;}
.studio-list-item:hover{border-color:var(--border-2);}
.studio-list-item.active{border-color:var(--cyan); box-shadow:0 0 0 1px var(--cyan) inset;}
.studio-list-thumb{width:54px; height:54px; flex:0 0 auto; border-radius:9px; object-fit:cover; background:var(--panel); border:1px solid var(--border); display:block;}
.studio-list-body{min-width:0; flex:1;}
.studio-list-body h4{margin:0 0 4px; font-size:13.5px; line-height:1.3; overflow:hidden; text-overflow:ellipsis; white-space:nowrap;}
.studio-list-meta{font-size:11.5px; color:var(--muted); overflow:hidden; text-overflow:ellipsis; white-space:nowrap;}
.studio-detail{border:1px solid var(--border); border-radius:14px; background:var(--panel-2); padding:18px; min-height:320px;}
.studio-detail-empty{display:flex; align-items:center; justify-content:center; min-height:280px; color:var(--muted); font-size:13.5px; text-align:center; padding:20px;}
.studio-detail-head{margin-bottom:14px;}
.studio-section{margin-bottom:18px; padding-bottom:14px; border-bottom:1px solid var(--border);}
.studio-section:last-of-type{border-bottom:none;}
.studio-section h3{font-size:13.5px; font-weight:600; color:var(--muted); margin:0 0 10px; text-transform:uppercase; letter-spacing:.04em;}
.studio-detail-title{margin:10px 0 0; font-size:18px; line-height:1.3;}
.studio-generated{margin-top:6px; padding:12px; border:1px dashed var(--border-2); border-radius:10px; background:var(--panel);}
.studio-meta{display:flex; flex-wrap:wrap; gap:8px; align-items:center; margin-bottom:8px; color:var(--muted); font-size:12.5px;}
.studio-actions{display:flex; justify-content:flex-end; margin-top:auto; padding-top:10px;}
.studio-card-thumb{display:block; width:100%; height:120px; object-fit:cover; border-radius:10px; border:1px solid var(--border); background:var(--panel); margin-bottom:10px;}
.studio-visual-box{margin-top:12px; border-top:1px solid var(--border); padding-top:12px;}
.studio-visual-preview{display:none; width:100%; max-height:320px; object-fit:cover; border-radius:12px; border:1px solid var(--border); background:var(--panel-2); margin-top:10px;}
.studio-visual-meta{font-size:12.5px; color:var(--muted); margin-top:6px; white-space:pre-wrap;}
.meta-accounts-list{display:flex;flex-direction:column;gap:10px;}
.meta-account-item{display:flex;align-items:center;gap:12px;padding:14px 16px;border:1px solid var(--border);border-radius:12px;background:var(--panel-2);}
.meta-account-item .av{width:38px;height:38px;border-radius:50%;background:var(--av-bg);flex-shrink:0;display:flex;align-items:center;justify-content:center;font-weight:700;font-size:13px;overflow:hidden;}
.meta-account-item .av img{width:100%;height:100%;object-fit:cover;display:block;}
.meta-account-item .info{flex:1;min-width:0;}
.meta-account-item .nm{font-weight:600;font-size:14px;display:flex;align-items:center;gap:8px;flex-wrap:wrap;}
.meta-account-item .sub{font-size:12.5px;color:var(--muted);margin-top:2px;}
.meta-account-item .status{font-size:12.5px;font-weight:700;white-space:nowrap;}
.meta-account-item .status.connected{color:var(--green);}
.meta-account-item .status.disconnected{color:var(--red);}
.meta-account-item .status.pending{color:var(--orange);}
.meta-account-empty{padding:20px;text-align:center;color:var(--muted);font-size:12.5px;border:1px dashed var(--border-2);border-radius:12px;background:var(--panel-2);}
.studio-engagement{display:flex; gap:18px; margin-top:12px; padding-top:10px; border-top:1px solid var(--border); flex-wrap:wrap;}
.eng-stat{display:flex; align-items:center; gap:6px; font-size:12.5px; color:var(--muted);}
.eng-stat .eng-num{font-weight:700; color:var(--text);}
.eng-stat .eng-lbl{font-size:11.5px;}
.studio-comments{margin-top:16px; border:1px solid var(--border); border-radius:12px; background:var(--panel-2);}
.studio-comments .panel-head{padding:10px 14px; border-bottom:1px solid var(--border); font-weight:700; font-size:13px; color:var(--text);}
.studio-comments-list{padding:10px 14px; max-height:260px; overflow-y:auto;}
.comment-item{display:flex; gap:10px; padding:10px 0; border-bottom:1px solid var(--border); font-size:13px;}
.comment-item:last-child{border-bottom:none;}
.comment-item .av{width:30px; height:30px; border-radius:50%; background:var(--av-bg); flex-shrink:0; display:flex; align-items:center; justify-content:center; font-weight:700; font-size:12px;}
.comment-item .body{flex:1; min-width:0;}
.comment-item .head{display:flex; justify-content:space-between; gap:8px; margin-bottom:4px;}
.comment-item .author{font-weight:700; font-size:12.5px; color:var(--text);}
.comment-item .time{font-size:11px; color:var(--muted-2);}
.comment-item .text{color:var(--text); margin-bottom:6px; white-space:pre-wrap;}
.comment-reply{margin-top:6px; padding-left:40px; border-left:1px solid var(--border-2);}
.comment-reply .field{margin-bottom:8px;}
.comment-reply textarea{width:100%; min-height:50px;}
.comment-reply .actions{display:flex; gap:8px; align-items:center;}
.btn-comment-reply{font-size:12px; padding:6px 12px;}
.empty-comments{text-align:center; color:var(--muted); padding:20px; font-size:12.5px;}
@media (max-width: 860px){ .studio-layout{grid-template-columns:1fr;} .studio-list{max-height:none;} }
.form-row{display:flex; flex-direction:column; gap:6px; margin-bottom:16px; max-width:420px}
.form-row label{font-size:13px; color:var(--muted);}
.form-row input,.form-row select,.form-row textarea{background:var(--panel-2); border:1px solid var(--border-2); color:var(--text); border-radius:9px; padding:10px 12px; font-size:13.5px; font-family:inherit;}
.toggle-row{display:flex; justify-content:space-between; align-items:center; padding:13px 0; border-bottom:1px solid var(--border); max-width:460px}
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
.meta-card .info{flex:1;min-width:220px}
.meta-card .nm{font-weight:600;font-size:14px;display:flex;align-items:center;gap:8px;flex-wrap:wrap}
.meta-card .sub{font-size:12.5px;color:var(--muted);margin-top:2px}
.status-pill{display:inline-flex;align-items:center;gap:6px;border:1px solid var(--border-2);padding:6px 12px;border-radius:20px;font-size:12.5px;font-weight:600;white-space:nowrap}
.status-pill.ok{background:rgba(52,194,122,.12);color:var(--green);border-color:rgba(52,194,122,.3)}
.status-pill.off{background:rgba(232,90,90,.12);color:var(--red);border-color:rgba(232,90,90,.3)}
.status-pill.pending{background:rgba(240,169,60,.12);color:var(--orange);border-color:rgba(240,169,60,.3)}
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
.empty-box{border:1px dashed var(--border-2);border-radius:12px;padding:24px;text-align:center;color:var(--muted);background:var(--panel-2)}
.btn-inline.is-loading{opacity:.85;cursor:progress;pointer-events:none;}
.kd-spinner{display:inline-block;width:16px;height:16px;border:2px solid currentColor;border-top-color:transparent;border-radius:50%;animation:kd-spin .7s linear infinite;flex-shrink:0;}
.kd-spinner.lg{width:20px;height:20px;border-width:2.5px;}
@keyframes kd-spin{to{transform:rotate(360deg)}}
.calendar-loading{display:flex;align-items:center;gap:14px;border:1px solid rgba(34,211,238,.35);background:rgba(34,211,238,.08);border-radius:12px;padding:14px 16px;margin-bottom:12px;color:var(--text);}
.calendar-loading .kd-spinner{color:var(--cyan);}
.kd-loading-title{font-weight:600;font-size:13.5px;}
.kd-loading-sub{font-size:12.5px;color:var(--muted);margin-top:2px;}
.kd-dots{animation:kd-dots 1.2s steps(4,end) infinite;}
@keyframes kd-dots{0%{opacity:.2}50%{opacity:1}100%{opacity:.2}}
@media (prefers-reduced-motion: reduce){.kd-spinner{animation-duration:1.6s;}.kd-dots{animation:none;}}
.page-skeleton{display:none; position:absolute; inset:0; z-index:40; background:var(--bg); padding:0; gap:0; flex-direction:column;}
.page-skeleton.show{display:flex; height:100%; align-items:center; justify-content:center;}
.skeleton-loader{display:flex; gap:10px; align-items:center; justify-content:center; padding:40px 20px; flex-wrap:wrap;}
.skeleton-stick{width:6px; border-radius:6px; background:#17B9FF; flex-shrink:0; animation:skeleton-pulse 1.4s ease-in-out infinite;}
.skeleton-stick.h80{height:80px;} .skeleton-stick.h110{height:110px;} .skeleton-stick.h140{height:140px;} .skeleton-stick.h170{height:170px;} .skeleton-stick.h200{height:200px;} .skeleton-stick.h230{height:230px;}
.skeleton-stick.d1{animation-delay:0s;} .skeleton-stick.d2{animation-delay:0.2s;} .skeleton-stick.d3{animation-delay:0.4s;} .skeleton-stick.d4{animation-delay:0.6s;}
.skeleton-stick.d5{animation-delay:0.8s;} .skeleton-stick.d6{animation-delay:1s;}
@keyframes skeleton-pulse{0%,100%{opacity:.4; transform:scaleY(1);}50%{opacity:1; transform:scaleY(1.3);}}
.skeleton-row,.skeleton-bar,.skeleton-text,.skeleton-avatar,.skeleton-col,.skeleton-header{display:none !important;}
.swap2-popup{background:#111116 !important;color:#f2f2f6 !important;border:1px solid #2a2a36}
.swap2-title,.swal2-html-container{color:#f2f2f6 !important}
html[data-theme="light"] .swal2-popup{background:#ffffff !important;color:#121826 !important;border-color:#e2e6ee}
html[data-theme="light"] .swal2-title,html[data-theme="light"] .swal2-html-container{color:#121826 !important}
.msg-thread{display:flex; gap:12px; border:1px solid var(--border); border-radius:12px; padding:14px; background:var(--panel-2); margin-bottom:10px}
.msg-thread .av{width:34px; height:34px; border-radius:50%; background:#3a3a46; flex-shrink:0; display:flex; align-items:center; justify-content:center; font-weight:700; font-size:12px}
.msg-thread .t{font-weight:600; font-size:13.5px}
.msg-thread .s{font-size:12.5px; color:var(--muted); margin-top:2px}

@media (max-width:980px){
  .two-col{grid-template-columns:1fr}
  .stats{grid-template-columns:repeat(2,1fr)}
  .card-grid{grid-template-columns:1fr 1fr}
}
@media (max-width:760px){
  .sidebar{transform:translateX(-100%)}
  .sidebar.open{transform:translateX(0); box-shadow:0 0 0 2000px rgba(0,0,0,.5)}
  .main{margin-left:0}
  .menu-btn{display:flex}
  .stats{grid-template-columns:1fr 1fr}
  .hero-banner{flex-direction:column}
  .card-grid{grid-template-columns:1fr}
}
.ico{width:1.15em;height:1.15em;display:inline-block;vertical-align:-0.125em;fill:none;stroke:currentColor;stroke-width:1.8;stroke-linecap:round;stroke-linejoin:round;flex-shrink:0;}
.ico-fill{fill:currentColor;stroke:none}
.ic .ico,.icon-btn .ico,.logo-ic{width:22px;height:22px;}
.hbtn .ico,.act .ico,.status-ok .ico,.sidebar-foot .ico{width:18px;height:18px;}
.nav-item .ic{display:inline-flex;align-items:center;justify-content:center;}
</style>
</head>
<body>
<script>window.addEventListener('pageshow', function(e){ if(e.persisted){ window.location.reload(); } });</script>
<div id="js-debug" style="display:none;position:fixed;top:10px;left:10px;right:10px;background:#1a1a23;border:1px solid #e85a5a;padding:12px;z-index:1000;color:#e85a5a;font-size:12px;overflow-y:auto;max-height:200px;"></div>
<div class="app">

  <aside class="sidebar" id="sidebar">
    <div class="brand">
      <img class="brand-logo brand-logo-dark" src="/assets/1.png" alt="IA Agent Studio" style="height:48px;width:auto;display:block;">
      <img class="brand-logo brand-logo-light" src="/assets/1.png" alt="IA Agent Studio" style="height:48px;width:auto;">
    </div>

    <div class="nav-item active" data-page="dashboard" data-i18n="nav.dashboard"><span class="ic"><x-lucide-layout-dashboard class="ico" /></span> Tableau de bord</div>

    <div class="nav-item" id="servicesToggle"><span class="ic"><x-lucide-settings class="ico" /></span> Services <span class="chev">▾</span></div>
    <div class="sub open" id="servicesSub">
      <button class="nav-item nav-toggle" id="userAgentsToggle" type="button" aria-expanded="false" aria-controls="userAgentsSub">
        <span class="ic"><x-lucide-bot class="ico" /></span> <span data-i18n="nav.agents">Agents IA</span> <span class="chev">▾</span>
      </button>
      <div class="sub" id="userAgentsSub">
        <div id="userAgentCategoryList">
          <div class="empty-box" style="padding:10px;font-size:12px;">Ouvrez le menu pour charger les catégories.</div>
        </div>
        <div id="userAgentNavList"></div>
      </div>
    </div>

    <div class="nav-item" data-page="pages-meta" data-i18n="nav.pages-meta"><span class="ic"><x-lucide-link class="ico" /></span> Pages & Comptes Meta</div>
    <div class="nav-item" data-page="messagerie" data-i18n="nav.messagerie"><span class="ic"><x-lucide-mail class="ico" /></span> Messagerie Support</div>
    <div class="nav-item" data-page="parametres" data-i18n="nav.parametres"><span class="ic"><x-lucide-settings class="ico" /></span> Paramètres</div>

    <div class="sidebar-foot">
      <div class="row"><span><x-lucide-zap class="ico" /> Pack Premium</span><span>4/9999 posts</span></div>
      <div class="bar"><i></i></div>
      <div class="meta"><span>Validation requise</span><span><x-lucide-check class="ico" /> 100% sécurisé</span></div>
    </div>
  </aside>

  <div class="main">
    <div class="topbar">
      <button class="menu-btn" id="menuBtn"><x-lucide-menu class="ico" /></button>
      <button class="icon-btn" id="themeToggle" title="Changer le thème"><x-lucide-sun class="ico" id="themeToggleIcon" /></button>
      <div class="lang" id="langSelector"><x-lucide-globe class="ico" /><span id="langLabel">FR</span></div>
      <div id="langDropdown" class="lang-dropdown">
        <div class="lang-option" data-lang="fr">Français</div>
        <div class="lang-option" data-lang="en">English</div>
      </div>
      <button class="icon-btn"><x-lucide-bell class="ico" /><span class="dot-badge">7</span></button>
      <div class="user-menu" id="userMenu">
        <div class="user-chip" id="userChip">
          <div class="avatar">{{ strtoupper(mb_substr(auth()->user()->first_name ?? 'U', 0, 1) . mb_substr(auth()->user()->last_name ?? '', 0, 1)) }}</div>
          <div><div class="name">{{ trim((auth()->user()->first_name ?? '') . ' ' . (auth()->user()->last_name ?? '')) ?: (auth()->user()->email ?? 'Utilisateur') }}</div><div class="plan">Pack Premium</div></div>
          <span style="color:var(--muted)">▾</span>
        </div>
        <div class="user-dropdown" id="userDropdown">
          <a href="/logout" class="danger"><x-lucide-log-out class="ico" /> Déconnexion</a>
        </div>
      </div>
    </div>

    <div class="content">

      <section class="page active" id="page-dashboard">
        <div class="page-skeleton" id="skel-dashboard">
          <div class="skeleton-loader">
            <div class="skeleton-stick h110 d1"></div><div class="skeleton-stick h170 d2"></div><div class="skeleton-stick h80 d3"></div><div class="skeleton-stick h230 d4"></div><div class="skeleton-stick h140 d5"></div><div class="skeleton-stick h100 d6"></div>
          </div>
        </div>
        <div class="hero-banner">
          <div>
            <div class="eyebrow" data-i18n="hero.eyebrow">IA Agent Studio</div>
            <h1><span data-i18n="hero.greeting">Bonjour, </span>{{ auth()->user()->first_name ?? 'vous' }} <x-lucide-waves class="ico" style="width:26px;height:26px;vertical-align:-3px" /></h1>
            <p data-i18n="hero.subtitle">Vos agents IA gèrent le contenu de vos pages sociales avec une rigueur absolue. Chaque publication reste soumise à votre validation avant mise en ligne.</p>
          </div>
          <div class="hero-actions">
            <button class="hbtn light" data-page-link="my-agents" data-i18n="hero.btn.publications">Examiner les publications <x-lucide-arrow-right class="ico" /></button>
            <button class="hbtn ghost" data-page-link="calendrier" data-i18n="hero.btn.calendrier"><x-lucide-calendar class="ico" /> Calendrier</button>
          </div>
        </div>

        <div class="alert">
          <div class="ic"><x-lucide-alert-triangle class="ico" style="width:22px;height:22px" /></div>
          <div class="body">
            <div class="t1"><span id="dashboardPendingText">{{ (int) ($dashboardStats['pending_validation'] ?? 0) }} publication(s) en attente de votre validation explicite</span> <span class="chip-req">Règle Obligatoire</span></div>
            <div class="t2">Conformément aux règles de sécurité Meta Colab, aucun contenu ne sera programmé ou publié sans votre approbation expresse.</div>
          </div>
          <div class="act">Valider maintenant <x-lucide-arrow-right class="ico" /></div>
        </div>

        <div class="stats">
          <div class="stat" data-page-link="my-agents" data-stat="agents_operational" style="cursor:pointer;"><div class="num">{{ (int) ($dashboardStats['agents_operational'] ?? 0) }}</div><div class="lbl" data-i18n="stat.agents_operational">100% opérationnels</div></div>
          <div class="stat c-green" data-page-link="pages-meta" data-stat="meta_connected" style="cursor:pointer;"><div class="num">{{ (int) ($dashboardStats['meta_connected'] ?? 0) }}</div><div class="lbl" data-i18n="stat.meta_connected">Facebook & Instagram</div></div>
          <div class="stat highlight c-orange" data-page-link="calendrier" data-stat="pending_validation" style="cursor:pointer;"><div class="num">{{ (int) ($dashboardStats['pending_validation'] ?? 0) }}</div><div class="lbl" data-i18n="stat.pending_validation">Validation requise</div></div>
          <div class="stat c-violet" data-page-link="studio" data-stat="auto_ready" style="cursor:pointer;"><div class="num">{{ (int) ($dashboardStats['auto_ready'] ?? 0) }}</div><div class="lbl" data-i18n="stat.auto_ready">Publication auto prête</div></div>
          <div class="stat c-green2" data-page-link="calendrier" data-stat="online_meta" style="cursor:pointer;"><div class="num">{{ (int) ($dashboardStats['online_meta'] ?? 0) }}</div><div class="lbl" data-i18n="stat.online_meta">En ligne sur Meta</div></div>
        </div>

        <div class="two-col">
          <div class="panel">
              <div class="panel-head">
                <div><h2 data-i18n="meta.title">Comptes Meta Connectés & Statut</h2><p data-i18n="meta.sub">Surveillance continue des jetons OAuth et des autorisations de publication</p></div>
                <span class="link" data-page-link="pages-meta" data-i18n="meta.gérer" style="cursor:pointer">Gérer <x-lucide-arrow-up-right class="ico" /></span>
              </div>
              <div id="dashboardMetaAccountsList" class="meta-accounts-list">
                @forelse($metaAccounts as $account)
                  @php
                    $statusLabels = ['connected' => 'Connecté', 'disconnected' => 'Déconnecté', 'pending' => 'En attente'];
                    $status = $statusLabels[$account->status] ?? $account->status;
                    $platform = $account->platform === 'instagram' ? 'Instagram' : 'Facebook';
                  @endphp
                  <div class="meta-account-item">
                    <div class="av">{{ mb_strtoupper(mb_substr($account->name ?? 'Compte', 0, 1)) }}</div>
                    <div class="info">
                      <div class="nm">{{ $account->name ?? 'Compte Meta' }} <span class="tag-fb" style="font-size:10px;padding:2px 6px;border-radius:10px;background:rgba(23,185,255,.15);color:#17B9FF;">{{ $platform }}</span></div>
                      <div class="sub">ID : {{ $account->meta_id ?: '—' }} · {{ $status }}</div>
                    </div>
                    <span class="status {{ $account->status === 'connected' ? 'connected' : ($account->status === 'disconnected' ? 'disconnected' : 'pending') }}">{{ $status }}</span>
                  </div>
                @empty
                  <div class="meta-account-empty">Aucun compte Meta connecté. Ajoutez vos comptes depuis « Pages & Comptes Meta ».</div>
                @endforelse
              </div>
            </div>

            <div class="panel">
              <div class="panel-head"><div><h2 data-i18n="quick.title">Accès Rapides</h2><p data-i18n="quick.sub">Opérations rapides pour piloter vos réseaux</p></div></div>
              <div class="quick">
                <div class="qbtn b1" data-page-link="my-agents" style="cursor:pointer;"><span class="ic"><x-lucide-plus class="ico" /></span><span class="lbl" data-i18n="quick.agent">Créer un nouvel agent IA</span><span class="arr"><x-lucide-arrow-right class="ico" /></span></div>
                <div class="qbtn b2" data-page-link="calendrier" style="cursor:pointer;"><span class="ic"><x-lucide-settings class="ico" /></span><span class="lbl" data-i18n="quick.calendar">Générer calendrier hebdo</span><span class="arr"><x-lucide-arrow-right class="ico" /></span></div>
                <div class="qbtn b3" data-page-link="veille" style="cursor:pointer;"><span class="ic"><x-lucide-chart-bar class="ico" /></span><span class="lbl" data-i18n="quick.watch">Veille concurrentielle IA</span><span class="arr"><x-lucide-arrow-right class="ico" /></span></div>
              </div>
              <div class="panel-foot"><span data-i18n="quick.plan">Forfait : </span><b style="color:var(--text)">Pack Premium</b><a href="#" data-page-link="abonnements" data-i18n="quick.details" style="cursor:pointer; color:var(--cyan); font-weight:600;">Voir détails</a></div>
          </div>
        </div>
      </section>

      <section class="page" id="page-my-agents">
        <div class="page-skeleton" id="skel-my-agents">
          <div class="skeleton-loader">
            <div class="skeleton-stick h110 d1"></div><div class="skeleton-stick h170 d2"></div><div class="skeleton-stick h80 d3"></div><div class="skeleton-stick h230 d4"></div><div class="skeleton-stick h140 d5"></div><div class="skeleton-stick h100 d6"></div>
          </div>
        </div>
        <div class="page-head" style="display:flex;justify-content:space-between;align-items:flex-end;gap:16px;flex-wrap:wrap;">
          <div><h1 data-i18n="my-agents.title">Mes Agents IA</h1><p id="myAgentsSubtitle" data-i18n="my-agents.sub">Vos agents configurés et leur périmètre d'action.</p></div>
          <div style="display:flex;gap:10px;align-items:flex-end;flex-wrap:wrap;">
            <div class="field" style="margin:0;min-width:240px;">
              <label>Catégorie</label>
              <select id="agentCategoryFilter"><option value="">Toutes les catégories</option></select>
            </div>
            <button class="btn-inline primary" id="agentAddBtn"><x-lucide-plus class="ico" /> Créer un agent</button>
          </div>
        </div>
        <div id="userAgentsList" class="card-grid">
          <div class="empty-box">Chargement de vos agents…</div>
        </div>
      </section>

      <section class="page" id="page-calendrier">
        <div class="page-skeleton" id="skel-calendrier">
          <div class="skeleton-loader">
            <div class="skeleton-stick h110 d1"></div><div class="skeleton-stick h170 d2"></div><div class="skeleton-stick h80 d3"></div><div class="skeleton-stick h230 d4"></div><div class="skeleton-stick h140 d5"></div><div class="skeleton-stick h100 d6"></div>
          </div>
        </div>
        <div class="page-head" style="display:flex;justify-content:space-between;align-items:flex-end;gap:16px;flex-wrap:wrap;">
          <div><h1 data-i18n="calendar.title">Calendrier Éditorial</h1><p data-i18n="calendar.sub">Générez un calendrier à partir de l'objectif, de la charte et des comptes inspirants de votre agent.</p></div>
          <div style="display:flex;gap:10px;align-items:flex-end;flex-wrap:wrap;">
            <div class="field" style="margin:0;min-width:220px;">
              <label>Agent</label>
              <select id="calendarAgentSelect"><option value="">Sélectionnez un agent</option></select>
            </div>
            <div class="field" style="margin:0;min-width:220px;">
              <label>Modèle de génération</label>
              <select id="calendarModelSelect"><option value="">Chargement…</option></select>
            </div>
          </div>
        </div>

        <div class="panel" style="margin-bottom:16px;">
          <div class="panel-head"><div><h2>Génération IA</h2><p>Le calendrier est construit à partir de l'identité de l'agent et du modèle de génération.</p></div></div>
          <div style="display:flex;gap:12px;align-items:flex-end;flex-wrap:wrap;">
            <div class="field" style="margin:0;min-width:150px;">
              <label>Date de début</label>
              <input type="date" id="calendarStartDate">
            </div>
            <div class="field" style="margin:0;min-width:110px;">
              <label>Semaines</label>
              <input type="number" id="calendarWeeks" min="1" max="4" value="1">
            </div>
            <div class="field" style="margin:0;min-width:130px;">
              <label>Posts / semaine</label>
              <input type="number" id="calendarPostsPerWeek" min="1" max="10" value="3">
            </div>
            <button class="btn-inline primary" id="calendarGenerateBtn"><x-lucide-zap class="ico" /> Générer le calendrier</button>
            <button class="btn-inline danger" id="calendarClearBtn"><x-lucide-trash-2 class="ico" /> Vider</button>
          </div>
          <div id="calendarStatus" style="margin-top:10px;font-size:12.5px;color:var(--muted);">Vérification du moteur de génération…</div>
        </div>

        <div class="panel">
          <div class="panel-head"><div><h2>Calendrier généré</h2><p>Publications proposées par l'agent, à valider avant publication.</p></div><span id="calendarCount" class="pill v">0 publication(s)</span></div>
          <div id="calendarLoading" class="calendar-loading" style="display:none;" role="status" aria-live="polite">
            <span class="kd-spinner" aria-hidden="true"></span>
            <div>
              <div class="kd-loading-title">Génération du calendrier<span class="kd-dots">…</span></div>
              <div class="kd-loading-sub">Votre agent élabore les publications. Cela peut prendre 1 à 5 minutes. <span id="calendarElapsed">0 s</span></div>
            </div>
          </div>
          <div id="calendarList"><div class="empty-box">Aucun calendrier généré. Cliquez sur « Générer le calendrier ».</div></div>
        </div>
      </section>

      <div class="modal-backdrop" id="calendarEditModal">
        <div class="modal">
          <div class="modal-head"><h3>Modifier la publication</h3><button class="btn-inline" id="calendarEditClose" type="button">Fermer</button></div>
          <form id="calendarEditForm">
            <input type="hidden" id="calendarEditId">
            <div class="modal-body">
              <div class="field"><label>Date et heure</label><input type="datetime-local" id="calendarEditPublishedAt" required></div>
              <div class="field">
                <label>Plateforme</label>
                <select id="calendarEditPlatform">
                  <option value="facebook">Facebook</option>
                </select>
                <div style="margin-top:4px;font-size:12px;color:var(--muted);">Seul Facebook est pris en charge pour le moment.</div>
              </div>
              <div class="field"><label>Pilier</label><input id="calendarEditPillar" maxlength="100" placeholder="Ex : Pédagogie"></div>
              <div class="field"><label>Titre</label><input id="calendarEditTitle" maxlength="255" placeholder="Titre de la publication"></div>
              <div class="field"><label>Légende</label><textarea id="calendarEditCaption" rows="4" maxlength="1000" placeholder="Légende à publier"></textarea></div>
              <div class="field"><label>Prompt visuel</label><textarea id="calendarEditVisual" rows="2" maxlength="500" placeholder="Description du visuel"></textarea></div>
              <div class="field"><label>Hashtags</label><input id="calendarEditHashtags" maxlength="300" placeholder="#hashtag1 #hashtag2"></div>
              <div class="field" style="margin-bottom:0;">
                <label>Statut</label>
                <select id="calendarEditStatus">
                  <option value="pending">En attente</option>
                  <option value="validated">Validée</option>
                  <option value="published">Publiée</option>
                  <option value="draft">Brouillon</option>
                </select>
              </div>
            </div>
            <div class="modal-foot"><button type="button" class="btn-inline" id="calendarEditCancel">Annuler</button><button type="submit" class="btn-inline primary">Enregistrer</button></div>
          </form>
        </div>
      </div>

      <div class="cal-action-menu" id="calendarActionMenu">
        <button type="button" data-menu-action="edit"><x-lucide-edit class="ico" /> Modifier</button>
        <button type="button" data-menu-action="delete" class="danger"><x-lucide-trash-2 class="ico" /> Supprimer</button>
        <button type="button" data-menu-action="validate" class="primary"><x-lucide-check class="ico" /> Valider</button>
        <button type="button" data-menu-action="publish" class="primary" style="display:none;"><x-lucide-check class="ico" /> Publier</button>
      </div>

      <section class="page" id="page-studio">
        <div class="page-skeleton" id="skel-studio">
          <div class="skeleton-loader">
            <div class="skeleton-stick h110 d1"></div><div class="skeleton-stick h170 d2"></div><div class="skeleton-stick h80 d3"></div><div class="skeleton-stick h230 d4"></div><div class="skeleton-stick h140 d5"></div><div class="skeleton-stick h100 d6"></div>
          </div>
        </div>
        <div class="page-head">
          <div><h1 data-i18n="studio.title">Studio des Publications</h1><p data-i18n="studio.sub">Publications validées dans le calendrier éditorial, prêtes à générer.</p></div>
          <button type="button" class="btn-inline" id="studioRefreshBtn"><x-lucide-calendar class="ico" /> Actualiser</button>
        </div>
        <div class="panel">
          <div class="panel-head">
            <div><h2>Publications validées</h2><p>Sélectionnez une publication pour afficher ses informations jusqu'au visuel.</p></div>
            <span id="studioCount" class="pill v">0 publication(s)</span>
          </div>
          <div class="studio-layout">
            <div id="studioPublicationsList" class="studio-list"><div class="empty-box">Chargement…</div></div>
            <div id="studioDetail" class="studio-detail">
              <div class="studio-detail-empty">Sélectionnez une publication dans la liste pour afficher toutes ses informations jusqu'au visuel.</div>
            </div>
          </div>
        </div>
      </section>

      <section class="page" id="page-identite">
        <div class="page-skeleton" id="skel-identite">
          <div class="skeleton-loader">
            <div class="skeleton-stick h110 d1"></div><div class="skeleton-stick h170 d2"></div><div class="skeleton-stick h80 d3"></div><div class="skeleton-stick h230 d4"></div><div class="skeleton-stick h140 d5"></div><div class="skeleton-stick h100 d6"></div>
          </div>
        </div>
        <div class="page-head" style="display:flex;justify-content:space-between;align-items:flex-end;gap:16px;flex-wrap:wrap;">
          <div><h1 data-i18n="identite.title">Identité & Inspiration</h1><p data-i18n="identite.sub">Objectif, palette & charte, logo et comptes inspirants qui guident l'agent.</p></div>
          <div style="display:flex;gap:10px;align-items:flex-end;flex-wrap:wrap;">
            <div class="field" style="margin:0;min-width:220px;">
              <label>Agent</label>
              <select id="identityAgentSelect"><option value="">Sélectionnez un agent</option></select>
            </div>
            <button class="btn-inline primary" id="identitySaveBtn"><x-lucide-check class="ico" /> Enregistrer l'identité</button>
          </div>
        </div>

        <div id="identityEmpty" class="empty-box" style="display:none;">Aucun agent configuré. Créez d'abord un agent via « Agents IA → catégorie → Créer un agent ».</div>

        <div id="identityContent" style="display:none;">
          <div class="two-col" style="align-items:start;">
            <div>
<div class="panel" style="margin-bottom:16px;">
                <div class="panel-head"><div><h2>Objectif</h2><p>La mission principale assignée à l'agent.</p></div></div>
                <div class="field" style="margin:0;">
                  <label>Objectif de l'agent</label>
                  <textarea id="identityObjective" rows="3" maxlength="5000" placeholder="Ex : Générer des leads qualifiés pour l'offre ERP et nourrir la relation client."></textarea>
                </div>
              </div>

              <div class="panel" style="margin-bottom:16px;">
                <div class="panel-head"><div><h2>Informations d'identité</h2><p>Secteur d'activité, localisation et cible de l'agent.</p></div></div>
                <div class="field">
                  <label>Secteur d'activité</label>
                  <input id="identitySecteurActivite" type="text" maxlength="255" placeholder="Ex : Technologies de l'information, Santé, Finance...">
                </div>
                <div class="field">
                  <label>Localisation</label>
                  <input id="identityLocalisation" type="text" maxlength="255" placeholder="Ex : Paris, France / International / Région Auvergne-Rhône-Alpes...">
                </div>
                <div class="field" style="margin:0;">
                  <label>Cible</label>
                  <textarea id="identityCible" rows="3" maxlength="5000" placeholder="Ex : Dirigeants de PME, décideurs IT, responsables RH..."></textarea>
                </div>
              </div>

              <div class="panel">
                <div class="panel-head"><div><h2>Palette & charte</h2><p>Couleurs et règles visuelles de la marque.</p></div></div>
                <div class="field">
                  <label>Couleurs</label>
                  <div id="paletteList" style="display:flex;flex-direction:column;gap:8px;margin-bottom:12px;"></div>
                  <div style="display:flex;gap:8px;align-items:flex-end;flex-wrap:wrap;">
                    <div style="flex:1;min-width:160px;">
                      <label style="font-size:12px;color:var(--muted);display:block;margin-bottom:4px;">Nom</label>
                      <input id="paletteName" maxlength="60" placeholder="Ex : Primaire">
                    </div>
                    <div>
                      <label style="font-size:12px;color:var(--muted);display:block;margin-bottom:4px;">Couleur</label>
                      <input id="paletteColor" type="color" value="#2bc7e8" style="width:54px;height:40px;padding:2px;">
                    </div>
                    <button type="button" class="btn-inline" id="paletteAddBtn"><x-lucide-plus class="ico" /> Ajouter</button>
                  </div>
                </div>
                <div class="field" style="margin:0;">
                  <label>Charte éditoriale & visuelle</label>
                  <textarea id="identityCharter" rows="3" maxlength="5000" placeholder="Typographie, ton, règles de composition, éléments interdits..."></textarea>
                </div>
              </div>
            </div>

            <div>
              <div class="panel" style="margin-bottom:16px;">
                <div class="panel-head"><div><h2>Logo</h2><p>Logo utilisé par l'agent.</p></div></div>
                <div style="display:flex;align-items:center;gap:16px;flex-wrap:wrap;">
                  <div id="identityLogoPreview" style="width:84px;height:84px;border-radius:12px;background:var(--panel-2);border:1px solid var(--border);display:flex;align-items:center;justify-content:center;overflow:hidden;flex-shrink:0;">
                    <x-lucide-image class="ico" style="width:32px;height:32px;color:var(--muted);" />
                  </div>
                  <div>
                    <input type="file" id="identityLogoInput" accept="image/*" style="display:none;">
                    <div style="display:flex;gap:8px;flex-wrap:wrap;">
                      <button type="button" class="btn-inline" id="identityLogoSelectBtn"><x-lucide-plus class="ico" /> Choisir un logo</button>
                      <button type="button" class="btn-inline danger" id="identityLogoRemoveBtn" style="display:none;"><x-lucide-trash-2 class="ico" /> Retirer</button>
                    </div>
                    <div class="sub" style="margin-top:6px;color:var(--muted);font-size:12.5px;">JPG, PNG, WebP. Max 2 Mo. Carré recommandé.</div>
                  </div>
                </div>
              </div>

              <div class="panel" style="margin-bottom:16px;">
                <div class="panel-head">
                  <div><h2>Images</h2><p>Visuels de référence uploadés pour l'agent.</p></div>
                  <button type="button" class="btn-inline" id="identityImagesAddBtn"><x-lucide-plus class="ico" /> Ajouter des images</button>
                </div>
                <input type="file" id="identityImagesInput" accept="image/*" multiple style="display:none;">
                <div id="identityImagesList" class="identity-images-grid"><div class="empty-box">Aucune image.</div></div>
                <div class="sub" style="margin-top:8px;color:var(--muted);font-size:12.5px;">JPG, PNG, WebP. Max 5 Mo par image. 10 images maximum par envoi.</div>
              </div>

              <div class="panel">
                <div class="panel-head">
                  <div><h2>Comptes inspirants</h2><p>Comptes de référence pour le style éditorial.</p></div>
                  <button type="button" class="btn-inline" id="accountAddBtn"><x-lucide-plus class="ico" /> Ajouter</button>
                </div>
                <div id="inspiringAccountsList"><div class="empty-box">Aucun compte inspirant.</div></div>
              </div>

              <div class="panel" style="margin-bottom:16px;">
                <div class="panel-head">
                  <div><h2>Sites web</h2><p>Liens à mettre en avant dans les publications.</p></div>
                  <button type="button" class="btn-inline" id="websiteAddBtn"><x-lucide-plus class="ico" /> Ajouter</button>
                </div>
                <div id="websitesList"><div class="empty-box">Aucun site web.</div></div>
              </div>

              <div class="panel">
                <div class="panel-head">
                  <div><h2>Contacts & numéros de téléphone</h2><p>Personnes à contacter et coordonnées à publier.</p></div>
                  <button type="button" class="btn-inline" id="contactAddBtn"><x-lucide-plus class="ico" /> Ajouter</button>
                </div>
                <div id="contactsList"><div class="empty-box">Aucun contact.</div></div>
              </div>
            </div>
          </div>
        </div>
      </section>

      <div class="modal-backdrop" id="websiteModal">
        <div class="modal">
          <div class="modal-head"><h3 id="websiteModalTitle">Site web</h3><button class="btn-inline" id="websiteModalClose" type="button">Fermer</button></div>
          <form id="websiteForm">
            <input type="hidden" id="websiteId">
            <div class="modal-body">
              <div class="field">
                <label>Nom du site <span style="color:var(--red)">*</span></label>
                <input id="websiteLabel" required maxlength="255" placeholder="Ex : Site vitrine">
              </div>
              <div class="field">
                <label>Adresse URL <span style="color:var(--red)">*</span></label>
                <input id="websiteUrl" type="url" required maxlength="1000" placeholder="https://exemple.com">
              </div>
              <div class="field" style="margin:0;">
                <label>Notes</label>
                <textarea id="websiteNotes" maxlength="2000" placeholder="Page à privilégier, usage du lien..."></textarea>
              </div>
            </div>
            <div class="modal-foot"><button type="button" class="btn-inline" id="websiteCancelBtn">Annuler</button><button type="submit" class="btn-inline primary" id="websiteSaveBtn">Enregistrer</button></div>
          </form>
        </div>
      </div>

      <div class="modal-backdrop" id="contactModal">
        <div class="modal">
          <div class="modal-head"><h3 id="contactModalTitle">Contact</h3><button class="btn-inline" id="contactModalClose" type="button">Fermer</button></div>
          <form id="contactForm">
            <input type="hidden" id="contactId">
            <div class="modal-body">
              <div class="field">
                <label>Nom du contact <span style="color:var(--red)">*</span></label>
                <input id="contactName" required maxlength="255" placeholder="Ex : Service client">
              </div>
              <div class="field">
                <label>Numéro de téléphone <span style="color:var(--red)">*</span></label>
                <input id="contactPhone" type="tel" required maxlength="50" placeholder="Ex : +33 6 12 34 56 78">
              </div>
              <div class="field">
                <label>Adresse e-mail</label>
                <input id="contactEmail" type="email" maxlength="255" placeholder="Ex : contact@exemple.com">
              </div>
              <div class="field">
                <label style="display:flex;align-items:center;gap:8px;">
                  <input id="contactIsPublic" type="checkbox" style="width:18px;height:18px;accent-color:var(--cyan);">
                  <span>Destiné à la communication publique</span>
                </label>
              </div>
              <div class="field" style="margin:0;">
                <label>Notes</label>
                <textarea id="contactNotes" maxlength="2000" placeholder="Horaires, personne à demander, usage..."></textarea>
              </div>
            </div>
            <div class="modal-foot"><button type="button" class="btn-inline" id="contactCancelBtn">Annuler</button><button type="submit" class="btn-inline primary" id="contactSaveBtn">Enregistrer</button></div>
          </form>
        </div>
      </div>

<div class="modal-backdrop" id="accountModal">
        <div class="modal">
          <div class="modal-head"><h3 id="accountModalTitle">Compte inspirant</h3><button class="btn-inline" id="accountModalClose" type="button">Fermer</button></div>
          <form id="accountForm">
            <input type="hidden" id="accountId">
            <div class="modal-body">
              <div class="field">
                <label>Plateforme</label>
                <select id="accountPlatform">
                  <option value="instagram">Instagram</option>
                  <option value="facebook">Facebook</option>
                  <option value="tiktok">TikTok</option>
                  <option value="linkedin">LinkedIn</option>
                  <option value="youtube">YouTube</option>
                  <option value="x">X (Twitter)</option>
                  <option value="other">Autre</option>
                </select>
              </div>
              <div class="field">
                <label>Pseudo / Nom <span style="color:var(--red)">*</span></label>
                <input id="accountHandle" required maxlength="255" placeholder="Ex : @marque-inspirante">
              </div>
              <div class="field">
                <label>URL du profil</label>
                <input id="accountUrl" type="url" maxlength="500" placeholder="https://instagram.com/...">
              </div>
              <div class="field">
                <label>Style recherché</label>
                <textarea id="accountStyle" maxlength="2000" placeholder="Ex : Ton décalé, visuels minimalistes, storytelling par carrousel..."></textarea>
              </div>
              <div class="field" style="margin:0;">
                <label>Notes</label>
                <textarea id="accountNotes" maxlength="2000" placeholder="Pourquoi ce compte inspire-t-il ?"></textarea>
              </div>
            </div>
            <div class="modal-foot"><button type="button" class="btn-inline" id="accountCancelBtn">Annuler</button><button type="submit" class="btn-inline primary" id="accountSaveBtn">Enregistrer</button></div>
          </form>
        </div>
      </div>

      <section class="page" id="page-veille">
        <div class="page-skeleton" id="skel-veille">
          <div class="skeleton-loader">
            <div class="skeleton-stick h110 d1"></div><div class="skeleton-stick h170 d2"></div><div class="skeleton-stick h80 d3"></div><div class="skeleton-stick h230 d4"></div><div class="skeleton-stick h140 d5"></div><div class="skeleton-stick h100 d6"></div>
          </div>
        </div>
        <div class="page-head"><h1 data-i18n="veille.title">Veille & Tendances</h1><p data-i18n="veille.sub">Signaux marché et contenus concurrents détectés par l'agent.</p></div>
        <div class="card-grid" id="veilleGrid">
          <div class="simple-card"><h3 data-i18n="veille.sujet">Sujet émergent</h3><p data-i18n="veille.sujet_text">Hausse de 32% des publications sur « automatisation RH » cette semaine.</p></div>
          <div class="simple-card"><h3 data-i18n="veille.concurrent">Concurrent actif</h3><p data-i18n="veille.concurrent_text">Un concurrent a publié 5 posts sponsorisés sur l'offre ERP.</p></div>
          <div class="simple-card"><h3 data-i18n="veille.format">Format gagnant</h3><p data-i18n="veille.format_text">Les carrousels génèrent 2,1x plus d'engagement ce mois-ci.</p></div>
        </div>
      </section>

      <section class="page" id="page-abonnements">
        <div class="page-skeleton" id="skel-abonnements">
          <div class="skeleton-loader">
            <div class="skeleton-stick h110 d1"></div><div class="skeleton-stick h170 d2"></div><div class="skeleton-stick h80 d3"></div><div class="skeleton-stick h230 d4"></div><div class="skeleton-stick h140 d5"></div><div class="skeleton-stick h100 d6"></div>
          </div>
        </div>
        <div class="page-head"><h1 data-i18n="abonnements.title">Abonnements & Quotas</h1><p data-i18n="abonnements.sub">Suivi de votre forfait et de votre consommation.</p></div>
        <div class="panel" style="max-width:700px" id="subscriptionPanel">
          <div style="display:flex; justify-content:space-between; font-weight:600; margin-bottom:8px;">
            <span id="subPlanName">Pack Premium</span>
            <span id="subPlanPrice">0 EUR / mois</span>
          </div>
          <div class="bar" style="margin-bottom:16px;"><i></i></div>
          <div style="display:flex; justify-content:space-between; font-size:12.5px; color:var(--muted); margin-bottom:6px;">
            <span>Utilisé</span><span id="subQuotaUsed">0</span>
          </div>
          <div class="bar" style="height:8px; margin-bottom:16px; background:var(--border-2); border-radius:4px; overflow:hidden;">
            <div id="subQuotaBar" style="height:100%; width:0%; background:var(--cyan);"></div>
          </div>
          <div style="display:flex; justify-content:space-between; font-size:12.5px; color:var(--muted); margin-bottom:12px;">
            <span>Renouvellement</span><span id="subRenewalDate">—</span>
          </div>
          <div id="subFeatures" style="font-size:13px; color:var(--muted);"></div>
          <p id="subStats" style="color:var(--muted); font-size:13px; margin-top:12px;"></p>
        </div>
      </section>

      <section class="page" id="page-pages-meta">
        <div class="page-skeleton" id="skel-pages-meta">
          <div class="skeleton-loader">
            <div class="skeleton-stick h110 d1"></div><div class="skeleton-stick h170 d2"></div><div class="skeleton-stick h80 d3"></div><div class="skeleton-stick h230 d4"></div><div class="skeleton-stick h140 d5"></div><div class="skeleton-stick h100 d6"></div>
          </div>
        </div>
        <div class="page-head" style="display:flex;justify-content:space-between;align-items:flex-end;gap:16px;flex-wrap:wrap;">
          <div><h1>Pages & Comptes Meta</h1><p>Gérez vos connexions Meta : afficher, modifier, déconnecter ou supprimer vos comptes/pages sur la plateforme.</p></div>
          <button class="btn-inline primary" id="metaAddBtn"><x-lucide-plus class="ico" /> Ajouter un compte</button>
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
                    <x-lucide-image class="ico" style="width:32px; height:32px; color:var(--muted);" />
                  </div>
                  <div style="flex:1;">
                    <input type="file" id="agentProfilePhoto" accept="image/*" style="display:none;">
                    <button type="button" class="btn-inline" id="agentPhotoSelectBtn"><x-lucide-plus class="ico" /> Choisir une photo</button>
                    <button type="button" class="btn-inline" id="agentPhotoRemoveBtn" style="display:none;"><x-lucide-trash-2 class="ico" /> Retirer</button>
                    <input type="hidden" id="agentRemovePhoto" value="0">
                    <div class="sub" style="margin-top:6px;color:var(--muted);font-size:12.5px;">Format: JPG, PNG, WebP. Max 2Mo. Carré recommandé.</div>
                  </div>
                </div>
              </div>
              <div class="field">
                <label>Catégorie d'agent <span style="color:var(--red)">*</span></label>
                <select id="agentCategory" required>
                  <option value="">Sélectionnez une catégorie</option>
                </select>
                <div class="sub" style="margin-top:6px;color:var(--muted);font-size:12.5px;">Sélectionnez la catégorie créée par l'administrateur à laquelle cet agent doit appartenir.</div>
              </div>
              <div class="field">
                <label>Modèle d'agent <span style="color:var(--red)">*</span></label>
                <select id="agentAgentId" required disabled>
                  <option value="">Sélectionnez d'abord une catégorie</option>
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

      <div class="modal-backdrop" id="publishModal">
        <div class="modal" style="max-width:520px;">
          <div class="modal-head"><h3 id="publishModalTitle">Publier la publication</h3><button class="btn-inline" id="publishModalClose" type="button">Fermer</button></div>
          <div class="modal-body">
            <div class="field">
              <label>Sélectionner le(s) compte(s) Meta</label>
              <div id="publishAccountList" style="display:flex;flex-direction:column;gap:8px;"></div>
              <div class="sub" style="margin-top:8px;color:var(--muted);font-size:12.5px;">Seuls les comptes connectés sur la plateforme de la publication sont affichés.</div>
            </div>
            <div id="publishError" style="color:var(--red); font-size:12.5px; margin-top:10px; display:none;"></div>
          </div>
          <div class="modal-foot">
            <button type="button" class="btn-inline" id="publishCancelBtn">Annuler</button>
            <button type="button" class="btn-inline primary" id="publishConfirmBtn">Publier</button>
          </div>
        </div>
      </div>

      <section class="page" id="page-messagerie">
        <div class="page-skeleton" id="skel-messagerie">
          <div class="skeleton-loader">
            <div class="skeleton-stick h110 d1"></div><div class="skeleton-stick h170 d2"></div><div class="skeleton-stick h80 d3"></div><div class="skeleton-stick h230 d4"></div><div class="skeleton-stick h140 d5"></div><div class="skeleton-stick h100 d6"></div>
          </div>
        </div>
        <div class="page-head"><h1>Messagerie Support</h1><p>Échanges avec l'équipe IA Agent Studio.</p></div>
        <div class="msg-thread"><div class="av">S</div><div><div class="t">Support IA Agent Studio</div><div class="s">Votre page Ghostroar a été vérifiée avec succès. — hier</div></div></div>
        <div class="msg-thread"><div class="av">{{ strtoupper(mb_substr(auth()->user()->first_name ?? 'U', 0, 1) . mb_substr(auth()->user()->last_name ?? '', 0, 1)) }}</div><div><div class="t">Vous</div><div class="s">Merci, pouvez-vous augmenter mon quota mensuel ? — hier</div></div></div>
      </section>

      <section class="page" id="page-parametres">
        <div class="page-skeleton" id="skel-parametres">
          <div class="skeleton-loader">
            <div class="skeleton-stick h110 d1"></div><div class="skeleton-stick h170 d2"></div><div class="skeleton-stick h80 d3"></div><div class="skeleton-stick h230 d4"></div><div class="skeleton-stick h140 d5"></div><div class="skeleton-stick h100 d6"></div>
          </div>
        </div>
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
window.appBasePath = @json(rtrim(request()->getBaseUrl(), '/'));
var sidebar = document.getElementById('sidebar');
sidebar.addEventListener('click', function(e){
  var el = e.target.closest('.nav-item[data-page]');
  if(!el || !sidebar.contains(el)) return;
  document.querySelectorAll('.nav-item[data-page]').forEach(function(n){n.classList.remove('active');});
  el.classList.add('active');
  var target = el.getAttribute('data-page');
  document.querySelectorAll('.page').forEach(function(p){p.classList.remove('active');});
  var page = document.getElementById('page-'+target);
  if(page){
    page.classList.add('active');
    var skel = document.getElementById('skel-'+target);
    if(skel){
      skel.classList.add('show');
      setTimeout(function(){ skel.classList.remove('show'); }, 800);
    }
  }
  var selectedAgentId = el.getAttribute('data-agent-id');
  if(selectedAgentId){
    window.currentAgentId = selectedAgentId;
    document.dispatchEvent(new CustomEvent('useragent:change', {detail:{id:selectedAgentId}}));
  }
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
    document.querySelectorAll('.page').forEach(function(sec){
      sec.classList.remove('active');
    });
    var targetEl = document.getElementById('page-'+target);
    if(targetEl){
      targetEl.classList.add('active');
      var skel = document.getElementById('skel-'+target);
      if(skel){
        skel.classList.add('show');
        setTimeout(function(){ skel.classList.remove('show'); }, 800);
      }
    }
    document.querySelectorAll('.nav-item[data-page]').forEach(function(n){
      n.classList.toggle('active', n.getAttribute('data-page') === target);
    });
  });
});
</script>
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<script>
var ICO = {
  check: '<svg class="ico" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M20 6 9 17l-5-5"/></svg>',
  edit: '<svg class="ico" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M11 4H4a2 2 0 0 0-2 2v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2v-7"/><path d="M18.5 2.5a2.121 2.121 0 0 1 3 3L12 15l-4 1 1-4 9.5-9.5z"/></svg>',
  'trash-2': '<svg class="ico" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M10 11v6"/><path d="M14 11v6"/><path d="M19 6v14a2 2 0 0 1-2 2H7a2 2 0 0 1-2-2V6"/><path d="M3 6h18"/><path d="M8 6V4a2 2 0 0 1 2-2h4a2 2 0 0 1 2 2v2"/></svg>',
  calendar: '<svg class="ico" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M8 2v3"/><path d="M16 2v3"/><rect x="3" y="3" width="18" height="18" rx="2"/><path d="M3 9h18"/></svg>',
  'user-round': '<svg class="ico" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="8" r="5"/><path d="M20 21a8 8 0 0 0-16 0"/></svg>',
  'chart-bar': '<svg class="ico" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M3 3v16a2 2 0 0 0 2 2h16"/><path d="M7 16h8"/><path d="M7 11h12"/><path d="M7 6h3"/></svg>',
  'credit-card': '<svg class="ico" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><rect width="20" height="14" x="2" y="5" rx="2"/><line x1="2" x2="22" y1="10" y2="10"/><path d="M6 14h2"/></svg>',
  bot: '<svg class="ico" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M12 8V4H8"/><rect width="16" height="12" x="4" y="8" rx="2"/><path d="M2 14h2"/><path d="M20 14h2"/><path d="M15 13v2"/><path d="M9 13v2"/></svg>',
  plus: '<svg class="ico" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M5 12h14"/><path d="M12 5v14"/></svg>',
  zap: '<svg class="ico" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M4 14a1 1 0 0 1-.78-1.63l9.9-10.2a.5.5 0 0 1 .86.46l-1.92 6.02A1 1 0 0 0 13 10h7a1 1 0 0 1 .78 1.63l-9.9 10.2a.5.5 0 0 1-.86-.46l1.92-6.02A1 1 0 0 0 11 14z"/></svg>',
  image: '<svg class="ico" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><rect width="18" height="18" x="3" y="3" rx="2" ry="2"/><circle cx="9" cy="9" r="2"/><path d="m21 15-3.086-3.086a2 2 0 0 0-2.828 0L6 21"/></svg>'
};
function iconSvg(name, css){
  var s = ICO[name] || '';
  return css ? s.replace('class="ico"', 'class="ico" style="' + css + '"') : s;
}
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
  function esc(s){ return String(s==null?'':s).replace(/[&<>"']/g,function(c){return {'&':'&amp;','<':'&lt;','>':'&gt;','"':'&quot;',"'":'&#039;'}[c]; }); }
  function statusPill(st){
    var s = st || 'connected';
    var cls = s==='connected' ? 'ok' : (s==='disconnected' ? 'off' : 'pending');
    var label = s==='connected' ? 'Connecté' : (s==='disconnected' ? 'Déconnecté' : 'En attente');
    return '<span class="status-pill '+cls+'">'+ICO.check+' '+label+'</span>';
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
      if(window.applyLang) window.applyLang(window.currentLang || 'fr');
    }

  async function api(url, opts){
    opts = opts || {};
    opts.headers = Object.assign({'Accept':'application/json','X-CSRF-TOKEN':csrf,'X-Requested-With':'XMLHttpRequest','Content-Type':'application/json'}, opts.headers||{});
    opts.credentials = 'same-origin';
    var res = await fetch(window.appBasePath + url, opts);
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
  console.log('Agent handling IIFE started');
  var csrf = document.querySelector('meta[name="csrf-token"]') ? document.querySelector('meta[name="csrf-token"]').getAttribute('content') : '';
  var listEl = document.getElementById('userAgentsList');
  var modal = document.getElementById('agentModal');
  var form = document.getElementById('agentForm');
  var agentsMenuEl = document.getElementById('userAgentNavList');
  var agentsToggle = document.getElementById('userAgentsToggle');
  var categoriesMenuEl = document.getElementById('userAgentCategoryList');
  var categoryFilterEl = document.getElementById('agentCategoryFilter');
  var subtitleEl = document.getElementById('myAgentsSubtitle');
  console.log('Elements found:', {listEl, modal, form, agentsMenuEl, agentsToggle});
  if(!listEl || !modal || !form || !agentsMenuEl || !agentsToggle) { console.log('Missing elements, returning'); return; }

  var modules = [
    {page:'calendrier', icon:ICO.calendar, label:'Calendrier Éditorial', labelKey:'module.calendrier'},
    {page:'studio', icon:ICO.edit, label:'Studio des Publications', labelKey:'module.studio'},
    {page:'identite', icon:ICO['user-round'], label:'Identité & Inspiration', labelKey:'module.identite'},
    {page:'veille', icon:ICO['chart-bar'], label:'Veille & Tendances', labelKey:'module.veille'},
    {page:'abonnements', icon:ICO['credit-card'], label:'Abonnements & Quotas', labelKey:'module.abonnements'}
  ];

  function notify(msg, type){
    if(window.Swal){
      return Swal.fire({toast:true,position:'bottom',icon:type||'success',title:msg,showConfirmButton:false,timer:type==='error'?3500:1800,timerProgressBar:true});
    }
  }
function esc(s){ return String(s==null?'':s).replace(/[&<>"']/g,function(c){return {'&':'&amp;','<':'&lt;','>':'&gt;','"':'&quot;',"'":'&#039;'}[c]; }); }
  function agentPill(st){
      return '<span class="status-pill ok">'+ICO.check+' Actif</span>';
    }
    function getPhotoUrl(photo) {
      if (!photo) return null;
      // Extract filename from path like "user-agents/filename.jpg"
      var filename = photo.split('/').pop();
      return window.appBasePath + '/user/agents/photo/' + filename;
    }
    function categoryNameOf(ua){
      var agent = ua.agent || {};
      if(agent.category && agent.category.name) return agent.category.name;
      return '';
    }
    function categoryIdOf(ua){
      var agent = ua.agent || {};
      return agent.category_id != null ? String(agent.category_id) : '';
    }
    function render(userAgents){
      var all = userAgents || [];
      var visible = categoryFilterId
        ? all.filter(function(ua){ return categoryIdOf(ua) === String(categoryFilterId); })
        : all;

      if(subtitleEl){
        if(categoryFilterId){
          var cat = categoriesCache.find(function(c){ return String(c.id) === String(categoryFilterId); });
          subtitleEl.textContent = cat
            ? 'Agents de la catégorie « ' + cat.name + ' » et leurs périmètres d\'action.'
            : 'Vos agents configurés et leur périmètre d\'action.';
        } else {
          subtitleEl.textContent = 'Vos agents configurés et leur périmètre d\'action.';
        }
      }

      if(!all.length){
        listEl.innerHTML = '<div class="empty-box">Aucun agent n’est encore associé à votre compte. Cliquez sur « Créer un agent » pour choisir un modèle.</div>';
        return;
      }
      if(!visible.length){
        listEl.innerHTML = '<div class="empty-box">Aucun agent dans cette catégorie. Cliquez sur « Créer un agent » pour en ajouter un.</div>';
        return;
      }
      listEl.innerHTML = visible.map(function(ua){
        var agent = ua.agent || {};
        var photoUrl = getPhotoUrl(ua.profile_photo);
        var avText = esc((agent.name||'A').charAt(0).toUpperCase());
        var name = ua.custom_name || agent.name || 'Agent';
        var domain = ua.domain ? ua.domain.replace('-', ' ').replace(/\b\w/g, function(l){ return l.toUpperCase(); }) : 'Non défini';
        var tone = ua.tone ? ua.tone.charAt(0).toUpperCase() + ua.tone.slice(1) : 'Non défini';
        var catName = categoryNameOf(ua);
        var actions = '<button class="btn-inline primary" data-action="open" data-id="'+ua.id+'">'+ICO.bot+' Gérer</button>' +
                      '<button class="btn-inline" data-action="edit" data-id="'+ua.id+'">'+ICO.edit+' Modifier</button>' +
                      '<button class="btn-inline danger" data-action="delete" data-id="'+ua.id+'">'+ICO['trash-2']+' Supprimer</button>';
        var moduleLinks = modules.map(function(module){
          return '<button class="btn-inline" type="button" data-open-agent="'+ua.id+'" data-target-page="'+module.page+'">'+module.icon+' <span data-i18n="'+module.labelKey+'">'+esc(module.label)+'</span></button>';
        }).join('');
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
          +   '<span><strong>Catégorie:</strong> '+esc(catName||'Non classé')+'</span>'
          +   '<span><strong>Domaine:</strong> '+esc(domain)+'</span>'
          +   '<span><strong>Tonalité:</strong> '+esc(tone)+'</span>'
          + '</div>'
          + '<div class="agent-card-modules">'
          +   '<div class="agent-card-modules-head" data-toggle-modules="'+ua.id+'">Options de l\'agent <span class="chev">▾</span></div>'
          +   '<div class="agent-card-module-grid" data-modules-for="'+ua.id+'" style="display:none;">'+moduleLinks+'</div>'
          + '</div>'
          + '<div class="meta-actions" style="margin-top:auto;">'+actions+'</div>'
          + '</div>';
      }).join('');
    }

  async function api(url, opts){
    opts = opts || {};
    opts.headers = Object.assign({'Accept':'application/json','X-CSRF-TOKEN':csrf,'X-Requested-With':'XMLHttpRequest','Content-Type':'application/json'}, opts.headers||{});
    opts.credentials = 'same-origin';
    var res = await fetch(window.appBasePath + url, opts);
    var data = {};
    try{ data = await res.json(); }catch(e){}
    if(!res.ok) throw Object.assign(new Error(data.message||'Erreur'), {data:data,status:res.status});
    return data;
  }


  var userAgentsCache = [];
  var adminAgentsCache = [];
  var categoriesCache = [];
  var categoryFilterId = '';
  var expandedCategoryId = '';
  var modalCategoryScope = '';
  var activeUserAgentId = null;

  function renderUserAgentEntry(userAgent){
    var id = esc(userAgent.id);
    var isSelected = String(userAgent.id)===String(activeUserAgentId);
    var name = esc(userAgent.custom_name || (userAgent.agent && userAgent.agent.name) || 'Agent IA');
    var options = modules.map(function(module){
      return '<div class="nav-item" data-page="'+module.page+'" data-agent-id="'+id+'">'
        + '<span class="ic">'+module.icon+'</span> <span data-i18n="'+module.labelKey+'">'+esc(module.label)+'</span>'
        + '</div>';
    }).join('');

    return '<div class="agent-nav-entry">'
      + '<button class="nav-item nav-toggle user-agent-toggle'+(isSelected?' selected':'')+'" type="button" data-user-agent-toggle data-user-agent-id="'+id+'" aria-expanded="'+(isSelected?'true':'false')+'">'
      + '<span class="ic">'+ICO.bot+'</span> '+name+' <span class="chev">▾</span>'
      + '</button>'
      + '<div class="sub agent-tools'+(isSelected?' open':'')+'" data-user-agent-tools="'+id+'">'+options+'</div>'
      + '</div>';
  }

  function renderAgentNavigation(userAgents){
    var orphans = (userAgents || []).filter(function(ua){
      var cid = categoryIdOf(ua);
      return !cid || !categoriesCache.some(function(c){ return String(c.id) === String(cid); });
    });

    if(!(userAgents || []).length){
      activeUserAgentId = null;
    }

    if(!orphans.length){
      agentsMenuEl.innerHTML = '';
      return;
    }

    agentsMenuEl.innerHTML = '<div class="cat-section-label">Agents sans catégorie</div>' + orphans.map(renderUserAgentEntry).join('');
      if(window.applyLang) window.applyLang(window.currentLang || 'fr');
    }

  async function loadCategories(){
    try{
      var data = await api('/user/agents/categories');
      categoriesCache = data.categories || [];
      renderCategoryNavigation();
      populateCategoryFilter();
      if(categoryFilterId && !categoriesCache.some(function(c){ return String(c.id)===String(categoryFilterId); })){
        categoryFilterId = '';
        if(categoryFilterEl) categoryFilterEl.value = '';
        render(userAgentsCache);
      }
      populateAgentSelect();
    }catch(e){
      console.error('Failed to load categories', e);
      categoriesMenuEl.innerHTML = '<div class="empty-box" style="padding:10px;font-size:12px;">Impossible de charger les catégories. Ouvrez à nouveau le menu pour réessayer.</div>';
      agentsLoaded = false;
    }
  }

  function renderCategoryNavigation(){
    if(!categoriesMenuEl) return;
    if(!categoriesCache.length){
      categoriesMenuEl.innerHTML = '<div class="empty-box" style="padding:10px;font-size:12px;">Aucune catégorie publiée par l\'administrateur.</div>';
      return;
    }
    categoriesMenuEl.innerHTML = categoriesCache.map(function(cat){
      var id = esc(cat.id);
      var myAgents = userAgentsCache.filter(function(ua){ return categoryIdOf(ua) === String(cat.id); });
      var isSelected = String(cat.id) === String(categoryFilterId);
      var isOpen = String(cat.id) === String(expandedCategoryId);

      var myAgentsHtml = myAgents.length
        ? myAgents.map(renderUserAgentEntry).join('')
        : '<div class="empty-box" style="padding:8px;font-size:12px;">Aucun agent créé dans cette catégorie.</div>';

      return '<button class="nav-item nav-toggle category-toggle'+(isSelected?' active':'')+'" type="button" data-category-id="'+id+'" aria-expanded="'+(isOpen?'true':'false')+'">'
        + '<span class="ic">'+ICO.bot+'</span> '+esc(cat.name)
        + ' <span class="cat-count">'+myAgents.length+'</span>'
        + ' <span class="chev">▾</span>'
        + '</button>'
        + '<div class="sub category-agent-list'+(isOpen?' open':'')+'" data-category-agents="'+id+'">'
        +   '<div class="cat-section-label">Agents créés</div>'
        +   myAgentsHtml
        + '</div>';
    }).join('');
  }

  function populateCategoryFilter(){
    if(!categoryFilterEl) return;
    var current = categoryFilterId;
    categoryFilterEl.innerHTML = '<option value="">Toutes les catégories</option>' + categoriesCache.map(function(cat){
      var count = userAgentsCache.filter(function(ua){ return categoryIdOf(ua) === String(cat.id); }).length;
      return '<option value="'+cat.id+'" '+(String(cat.id)===String(current)?'selected':'')+'>'+esc(cat.name)+' ('+count+')</option>';
    }).join('');
    categoryFilterEl.value = current;
  }

  async function loadUserAgents(){
    try{
      var data = await api('/user/agents');
      userAgentsCache = data.userAgents || [];
      render(userAgentsCache);
      renderAgentNavigation(userAgentsCache);
      renderCategoryNavigation();
      populateCategoryFilter();
    }catch(e){
      listEl.innerHTML = '<div class="empty-box">Impossible de charger vos agents.</div>';
      agentsMenuEl.innerHTML = '<div class="empty-box" style="padding:10px;font-size:12px;">Impossible de charger vos agents. Réessayez en rouvant le menu.</div>';
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

  function adminAgentGroups(){
    var groups = categoriesCache.map(function(cat){
      return {label: cat.name, agents: (cat.agents || []).slice()};
    });
    var known = {};
    groups.forEach(function(g){
      g.agents.forEach(function(a){ known[String(a.id)] = true; });
    });
    var orphans = adminAgentsCache.filter(function(a){ return !known[String(a.id)]; });
    if(orphans.length){
      groups.push({label: 'Autres modèles', agents: orphans});
    }
    if(!groups.length){
      groups.push({label: 'Modèles disponibles', agents: adminAgentsCache.slice()});
    }
    return groups;
  }

  function populateAgentSelect(preselectedId, categoryId){
    var select = document.getElementById('agentAgentId');
    if(!select) return;
    if(preselectedId === undefined) preselectedId = null;
    if(categoryId === undefined) categoryId = modalCategoryScope;
    var current = preselectedId != null && preselectedId !== '' ? String(preselectedId) : select.value;

    if(!categoryId){
      select.innerHTML = '<option value="">Sélectionnez d\'abord une catégorie</option>';
      select.disabled = true;
      select.value = '';
      return;
    }
    select.disabled = false;

    var html = '<option value="">Sélectionnez un modèle d\'agent</option>';
    adminAgentGroups().forEach(function(group){
      var agents = group.agents.filter(function(a){ return String(a.category_id) === String(categoryId); });
      if(!agents.length) return;
      html += '<optgroup label="'+esc(group.label)+'">';
      html += agents.map(function(a){
        var desc = a.description ? ' - ' + a.description : '';
        return '<option value="'+a.id+'" '+(String(current)===String(a.id)?'selected':'')+'>'+esc(a.name)+esc(desc)+'</option>';
      }).join('');
      html += '</optgroup>';
    });
    select.innerHTML = html;
    if(current) select.value = current;
  }

  function populateAgentCategorySelect(selectedId){
    var select = document.getElementById('agentCategory');
    if(!select) return;
    if(selectedId === undefined) selectedId = '';
    var available = categoriesCache.filter(function(cat){
      return (cat.agents || []).length > 0;
    });
    var html = '<option value="">Sélectionnez une catégorie</option>';
    available.forEach(function(cat){
      var n = userAgentsCache.filter(function(ua){ return categoryIdOf(ua) === String(cat.id); }).length;
      html += '<option value="'+cat.id+'" '+(String(selectedId)===String(cat.id)?'selected':'')+'>'
        + esc(cat.name) + ' (' + (cat.agents || []).length + ' modèle' + ((cat.agents || []).length > 1 ? 's' : '') + (n ? ', ' + n + ' agent' + (n > 1 ? 's' : '') : '') + ')'
        + '</option>';
    });
    if(!available.length){
      html += '<option value="" disabled>Aucune catégorie disponible (ajoutée par l\'administrateur)</option>';
    }
    select.innerHTML = html;
    if(selectedId) select.value = String(selectedId);
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

function openModal(userAgent, categoryId){
    var scope = userAgent
      ? String(categoryIdOf(userAgent) || '')
      : (categoryId != null && categoryId !== '' ? String(categoryId) : (categoryFilterId || ''));
    modalCategoryScope = userAgent ? '' : scope;
    var cat = scope ? categoriesCache.find(function(c){ return String(c.id) === scope; }) : null;
    if(!userAgent && scope && cat && !(cat.agents || []).length){
      notify('Aucun modèle d\'agent disponible dans cette catégorie.', 'error');
      return;
    }
    document.getElementById('agentUserAgentId').value = userAgent ? userAgent.id : '';
    document.getElementById('agentCustomName').value = userAgent ? (userAgent.custom_name||'') : '';
    document.getElementById('agentTone').value = userAgent ? (userAgent.tone||'') : '';
    document.getElementById('agentModalTitle').textContent = userAgent
      ? 'Modifier l\'agent'
      : (cat ? 'Créer un agent · ' + cat.name : 'Créer un agent');
    document.getElementById('agentSaveBtn').textContent = userAgent ? 'Enregistrer' : 'Créer l\'agent';
    populateDomainSelect();
    if (userAgent && userAgent.domain) {
      document.getElementById('agentDomain').value = userAgent.domain;
      toggleCustomDomainField();
    }
    populateAgentCategorySelect(scope);
    var agentSelect = document.getElementById('agentAgentId');
    if (scope) {
      agentSelect.value = userAgent ? String(userAgent.agent_id || '') : '';
      populateAgentSelect(userAgent ? String(userAgent.agent_id || '') : '', scope);
    } else {
      agentSelect.innerHTML = '<option value="">Sélectionnez d\'abord une catégorie</option>';
      agentSelect.disabled = true;
      agentSelect.value = '';
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
      previewEl.innerHTML = iconSvg('image','width:28px;height:28px;color:var(--muted);');
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

  var agentCategoryEl = document.getElementById('agentCategory');
  if(agentCategoryEl){
    agentCategoryEl.addEventListener('change', function(){
      var categoryId = agentCategoryEl.value || '';
      modalCategoryScope = categoryId;
      populateAgentSelect('', categoryId);
      var agentSelect = document.getElementById('agentAgentId');
      if(agentSelect) agentSelect.focus();
    });
  }
  function closeModal(){ modal.classList.remove('open'); modalCategoryScope = ''; form.reset(); document.getElementById('agentDomainCustom').style.display = 'none'; document.getElementById('agentDomainCustom').required = false; document.getElementById('agentPhotoPreview').innerHTML = iconSvg('image','width:28px;height:28px;color:var(--muted);'); document.getElementById('agentPhotoRemoveBtn').style.display = 'none'; document.getElementById('agentRemovePhoto').value = '0'; }

  function navigateToPage(page, agentId){
    if(agentId){
      window.currentAgentId = String(agentId);
      activeUserAgentId = String(agentId);
      renderAgentNavigation(userAgentsCache);
    }
    var navItem = agentId
      ? document.querySelector('.nav-item[data-page="'+page+'"][data-agent-id="'+agentId+'"]')
      : null;
    if(!navItem){
      navItem = document.querySelector('.nav-item[data-page="'+page+'"]');
    }
    if(navItem && sidebar && sidebar.contains(navItem)){
      navItem.click();
    } else {
      navItem = document.querySelector('#userAgentNavList .nav-item[data-page="'+page+'"]');
      if(navItem){
        navItem.click();
      } else {
        document.querySelectorAll('.page').forEach(function(p){ p.classList.remove('active'); });
        var pg = document.getElementById('page-'+page);
        if(pg){ pg.classList.add('active'); }
        document.querySelectorAll('.nav-item[data-page]').forEach(function(n){
          n.classList.toggle('active', n.getAttribute('data-page') === page);
        });
        document.dispatchEvent(new CustomEvent('useragent:change', {detail:{id: agentId ? String(agentId) : null}}));
        if(window.innerWidth <= 760){ var sb = document.getElementById('sidebar'); if(sb) sb.classList.remove('open'); }
        window.scrollTo(0,0);
      }
    }
  }

  function selectCategory(categoryId){
    categoryFilterId = categoryId ? String(categoryId) : '';
    if(categoryFilterEl) categoryFilterEl.value = categoryFilterId;
    render(userAgentsCache);
    renderCategoryNavigation();
    navigateToPage('my-agents', null);
    var addBtn = document.getElementById('agentAddBtn');
    if(addBtn) addBtn.focus();
  }

  var agentAddBtn = document.getElementById('agentAddBtn');
  if (agentAddBtn) {
    agentAddBtn.addEventListener('click', function(){ openModal(null, categoryFilterId); });
  }
  document.getElementById('agentModalClose').addEventListener('click', closeModal);
  document.getElementById('agentCancelBtn').addEventListener('click', closeModal);
  modal.addEventListener('click', function(e){ if(e.target===modal) closeModal(); });

  if(categoryFilterEl){
    categoryFilterEl.addEventListener('change', function(){
      categoryFilterId = categoryFilterEl.value || '';
      render(userAgentsCache);
      renderCategoryNavigation();
    });
  }

  var agentsSubEl = document.getElementById('userAgentsSub');

  function toggleUserAgentTools(selectedId, toggle){
    var tools = agentsSubEl.querySelector('[data-user-agent-tools="'+selectedId+'"]');
    if(!tools) return;
    var shouldOpen = !tools.classList.contains('open');
    agentsSubEl.querySelectorAll('[data-user-agent-tools]').forEach(function(menu){ menu.classList.remove('open'); });
    agentsSubEl.querySelectorAll('[data-user-agent-toggle]').forEach(function(button){
      button.classList.remove('selected');
      button.setAttribute('aria-expanded','false');
    });
    activeUserAgentId = shouldOpen ? selectedId : null;
    if(shouldOpen){
      tools.classList.add('open');
      toggle.classList.add('selected');
      toggle.setAttribute('aria-expanded','true');
    }
  }

  if(agentsSubEl){
    agentsSubEl.addEventListener('click', function(e){
      var toggle = e.target.closest('[data-category-id]');
      if(toggle){
        var catId = toggle.getAttribute('data-category-id');
        expandedCategoryId = (expandedCategoryId === String(catId)) ? '' : String(catId);
        selectCategory(catId);
        return;
      }

      var uaToggle = e.target.closest('[data-user-agent-toggle]');
      if(uaToggle){
        toggleUserAgentTools(uaToggle.getAttribute('data-user-agent-id'), uaToggle);
      }
    });
  }

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
    document.getElementById('agentPhotoPreview').innerHTML = iconSvg('image','width:28px;height:28px;color:var(--muted);');
    document.getElementById('agentPhotoRemoveBtn').style.display = 'none';
    document.getElementById('agentRemovePhoto').value = '1';
  });

  form.addEventListener('submit', async function(e){
    e.preventDefault();
    var id = document.getElementById('agentUserAgentId').value;
    var agentId = document.getElementById('agentAgentId').value;
    var categoryId = document.getElementById('agentCategory').value;
    if(!categoryId){ notify("Veuillez sélectionner une catégorie d'agent.", 'error'); return; }
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
    formData.append('category_id', categoryId);
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
      var res = await fetch(window.appBasePath + url, {
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
    var toggleModules = e.target.closest('[data-toggle-modules]');
    if(toggleModules){
      var modulesId = toggleModules.getAttribute('data-toggle-modules');
      var grid = listEl.querySelector('[data-modules-for="'+modulesId+'"]');
      if(grid){
        var isOpen = grid.style.display !== 'none';
        grid.style.display = isOpen ? 'none' : 'grid';
        toggleModules.classList.toggle('open', !isOpen);
      }
      return;
    }

    var moduleBtn = e.target.closest('[data-open-agent]');
    if(moduleBtn){
      var agentId = moduleBtn.getAttribute('data-open-agent');
      var targetPage = moduleBtn.getAttribute('data-target-page');
      navigateToPage(targetPage, agentId);
      return;
    }

    var btn = e.target.closest('[data-action]');
    if(!btn) return;
    var id = btn.getAttribute('data-id');
    var action = btn.getAttribute('data-action');
    if(action==='open'){
      navigateToPage('calendrier', id);
      return;
    }
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
  function ensureAgentsLoad(){
    if(agentsLoaded){ renderCategoryNavigation(); return; }
    agentsLoaded = true;
    loadCategories();
    loadAdminAgents();
    loadUserAgents();
  }
  var manageAgentsItem = document.querySelector('.nav-item[data-page="my-agents"]');
  if(manageAgentsItem){ manageAgentsItem.addEventListener('click', ensureAgentsLoad); }
  agentsToggle.addEventListener('click', function(){
    setTimeout(ensureAgentsLoad, 0);
  });
  if(document.getElementById('page-my-agents') && document.getElementById('page-my-agents').classList.contains('active')) ensureAgentsLoad();
})();

// Identité & Inspiration handling
(function(){
  var pageEl = document.getElementById('page-identite');
  var contentEl = document.getElementById('identityContent');
  var emptyEl = document.getElementById('identityEmpty');
  var agentSelect = document.getElementById('identityAgentSelect');
  var saveBtn = document.getElementById('identitySaveBtn');
  var paletteListEl = document.getElementById('paletteList');
  var accountsListEl = document.getElementById('inspiringAccountsList');
  var imagesListEl = document.getElementById('identityImagesList');
  var imagesInput = document.getElementById('identityImagesInput');
  var accountModal = document.getElementById('accountModal');
  var accountForm = document.getElementById('accountForm');
  var websitesListEl = document.getElementById('websitesList');
  var contactsListEl = document.getElementById('contactsList');
  var websiteModal = document.getElementById('websiteModal');
  var websiteForm = document.getElementById('websiteForm');
  var contactModal = document.getElementById('contactModal');
  var contactForm = document.getElementById('contactForm');
  if(!pageEl || !contentEl || !emptyEl || !agentSelect || !saveBtn || !paletteListEl || !accountsListEl || !imagesListEl || !imagesInput || !accountModal || !accountForm) return;
  if(!websitesListEl || !contactsListEl || !websiteModal || !websiteForm || !contactModal || !contactForm) return;

  var csrf = document.querySelector('meta[name="csrf-token"]') ? document.querySelector('meta[name="csrf-token"]').getAttribute('content') : '';
    var placeholderLogo = iconSvg('image','width:32px;height:32px;color:var(--muted);');
  var agents = [];
  var currentAgentId = window.currentAgentId ? String(window.currentAgentId) : '';
  var identity = null;
  var palette = [];
  var accounts = [];
  var images = [];
  var logoFile = null;
  var removeLogo = false;
  var editingAccountId = null;
  var websites = [];
  var contacts = [];
  var editingWebsiteId = null;
  var editingContactId = null;
  var loaded = false;

  function notify(msg, type){
    if(window.Swal){
      return Swal.fire({toast:true,position:'bottom',icon:type||'success',title:msg,showConfirmButton:false,timer:type==='error'?3500:1800,timerProgressBar:true});
    }
  }
  function esc(s){ return String(s==null?'':s).replace(/[&<>"']/g,function(c){return {'&':'&amp;','<':'&lt;','>':'&gt;','"':'&quot;',"'":'&#039;'}[c]; }); }

  async function api(url, opts){
    opts = opts || {};
    opts.headers = Object.assign({'Accept':'application/json','X-CSRF-TOKEN':csrf,'X-Requested-With':'XMLHttpRequest'}, opts.headers||{});
    opts.credentials = 'same-origin';
    if(opts.body && !(opts.body instanceof FormData) && !opts.headers['Content-Type']){
      opts.headers['Content-Type'] = 'application/json';
    }
    var res = await fetch(url, opts);
    var data = {};
    try{ data = await res.json(); }catch(e){}
    if(!res.ok) throw Object.assign(new Error(data.message||'Erreur'), {data:data,status:res.status});
    return data;
  }

  function showContent(){
    contentEl.style.display = 'block';
    emptyEl.style.display = 'none';
  }
  function showEmpty(){
    contentEl.style.display = 'none';
    emptyEl.style.display = 'block';
  }

function setFields(){
    document.getElementById('identityObjective').value = identity && identity.objective ? identity.objective : '';
    document.getElementById('identityCharter').value = identity && identity.charter ? identity.charter : '';
    document.getElementById('identitySecteurActivite').value = identity && identity.secteur_activite ? identity.secteur_activite : '';
    document.getElementById('identityLocalisation').value = identity && identity.localisation ? identity.localisation : '';
    document.getElementById('identityCible').value = identity && identity.cible ? identity.cible : '';
  }

  function renderLogo(){
    var preview = document.getElementById('identityLogoPreview');
    var removeBtn = document.getElementById('identityLogoRemoveBtn');
    if(logoFile){
      var reader = new FileReader();
      reader.onload = function(ev){
        preview.innerHTML = '<img src="'+ev.target.result+'" alt="" style="width:100%;height:100%;object-fit:cover;">';
      };
      reader.readAsDataURL(logoFile);
      removeBtn.style.display = 'inline-flex';
      return;
    }
    if(!removeLogo && identity && identity.logo_url){
      preview.innerHTML = '<img src="'+esc(identity.logo_url)+'" alt="" style="width:100%;height:100%;object-fit:cover;">';
      removeBtn.style.display = 'inline-flex';
      return;
    }
    preview.innerHTML = placeholderLogo;
    removeBtn.style.display = 'none';
  }

  function renderPalette(){
    if(!palette.length){
      paletteListEl.innerHTML = '<div style="font-size:12.5px;color:var(--muted);">Aucune couleur enregistrée.</div>';
      return;
    }
    paletteListEl.innerHTML = palette.map(function(c, i){
      return '<div style="display:flex;align-items:center;gap:10px;border:1px solid var(--border);border-radius:10px;padding:8px 10px;background:var(--panel-2);">'
        + '<span style="width:26px;height:26px;border-radius:7px;border:1px solid var(--border-2);background:'+esc(c.hex)+';flex-shrink:0;"></span>'
        + '<span style="flex:1;font-size:13px;">'+esc(c.name||c.hex)+' <span style="color:var(--muted);">'+esc(c.hex)+'</span></span>'
        + '<button type="button" class="btn-inline danger" data-palette-remove="'+i+'">'+ICO['trash-2']+' Retirer</button>'
        + '</div>';
    }).join('');
  }

function renderAccounts(){
    if(!accounts.length){
      accountsListEl.innerHTML = '<div class="empty-box">Aucun compte inspirant.</div>';
      return;
    }
    accountsListEl.innerHTML = accounts.map(function(a){
      var platform = esc((a.platform||'other').toUpperCase());
      var link = a.url
        ? '<div class="sub"><a href="'+esc(a.url)+'" target="_blank" rel="noopener" style="color:var(--cyan);word-break:break-all;">'+esc(a.url)+'</a></div>'
        : '';
      var style = a.style ? '<div class="sub">Style: '+esc(a.style)+'</div>' : '';
      var notes = a.notes ? '<div class="sub">'+esc(a.notes)+'</div>' : '';
      return '<div class="meta-card">'
        + '<div class="info"><div class="nm">'+esc(a.handle)+' <span class="tag-fb">'+platform+'</span></div>'+link+style+notes+'</div>'
        + '<div class="meta-actions">'
        + '<button class="btn-inline" data-account-edit="'+a.id+'">'+ICO.edit+' Modifier</button>'
        + '<button class="btn-inline danger" data-account-delete="'+a.id+'">'+ICO['trash-2']+' Supprimer</button>'
        + '</div></div>';
    }).join('');
  }

  function renderWebsites(){
    if(!websites.length){
      websitesListEl.innerHTML = '<div class="empty-box">Aucun site web.</div>';
      return;
    }
    websitesListEl.innerHTML = websites.map(function(w){
      var link = w.url
        ? '<div class="sub"><a href="'+esc(w.url)+'" target="_blank" rel="noopener" style="color:var(--cyan);word-break:break-all;">'+esc(w.url)+'</a></div>'
        : '';
      var notes = w.notes ? '<div class="sub">'+esc(w.notes)+'</div>' : '';
      return '<div class="meta-card">'
        + '<div class="info"><div class="nm">'+esc(w.label||'Site web')+'</div>'+link+notes+'</div>'
        + '<div class="meta-actions">'
        + '<button type="button" class="btn-inline" data-website-edit="'+w.id+'">'+ICO.edit+' Modifier</button>'
        + '<button type="button" class="btn-inline danger" data-website-delete="'+w.id+'">'+ICO['trash-2']+' Supprimer</button>'
        + '</div></div>';
    }).join('');
  }

  function renderContacts(){
    if(!contacts.length){
      contactsListEl.innerHTML = '<div class="empty-box">Aucun contact.</div>';
      return;
    }
    contactsListEl.innerHTML = contacts.map(function(c){
      var phone = c.phone ? '<div class="sub">'+esc(c.phone)+'</div>' : '';
      var email = c.email ? '<div class="sub">'+esc(c.email)+'</div>' : '';
      var isPublic = c.is_public ? '<div class="sub"><span class="status-pill ok">'+ICO.check+' Public</span></div>' : '';
      var notes = c.notes ? '<div class="sub">'+esc(c.notes)+'</div>' : '';
      return '<div class="meta-card">'
        + '<div class="info"><div class="nm">'+esc(c.name||'Contact')+'</div>'+phone+email+isPublic+notes+'</div>'
        + '<div class="meta-actions">'
        + '<button type="button" class="btn-inline" data-contact-edit="'+c.id+'">'+ICO.edit+' Modifier</button>'
        + '<button type="button" class="btn-inline danger" data-contact-delete="'+c.id+'">'+ICO['trash-2']+' Supprimer</button>'
        + '</div></div>';
    }).join('');
  }

  function renderImages(){
    if(!images.length){
      imagesListEl.innerHTML = '<div class="empty-box">Aucune image.</div>';
      return;
    }
    imagesListEl.innerHTML = images.map(function(img){
      var caption = img.caption ? '<div class="identity-image-caption">'+esc(img.caption)+'</div>' : '';
      return '<div class="identity-image-card">'
        + '<img src="'+esc(img.url||'')+'" alt="'+esc(img.caption||'')+'">'
        + caption
        + '<div class="identity-image-actions"><button type="button" class="btn-inline danger" data-identity-image-delete="'+img.id+'">'+ICO['trash-2']+' Supprimer</button></div>'
        + '</div>';
    }).join('');
  }

function openAccountModal(account){
    editingAccountId = account ? account.id : null;
    document.getElementById('accountModalTitle').textContent = account ? 'Modifier le compte inspirant' : 'Ajouter un compte inspirant';
    document.getElementById('accountPlatform').value = account ? (account.platform||'instagram') : 'instagram';
    document.getElementById('accountHandle').value = account ? (account.handle||'') : '';
    document.getElementById('accountUrl').value = account ? (account.url||'') : '';
    document.getElementById('accountStyle').value = account ? (account.style||'') : '';
    document.getElementById('accountNotes').value = account ? (account.notes||'') : '';
    accountModal.classList.add('open');
  }
  function closeAccountModal(){
    accountModal.classList.remove('open');
    accountForm.reset();
    editingAccountId = null;
  }

  function openWebsiteModal(website){
    editingWebsiteId = website ? website.id : null;
    document.getElementById('websiteModalTitle').textContent = website ? 'Modifier le site web' : 'Ajouter un site web';
    document.getElementById('websiteLabel').value = website ? (website.label||'') : '';
    document.getElementById('websiteUrl').value = website ? (website.url||'') : '';
    document.getElementById('websiteNotes').value = website ? (website.notes||'') : '';
    websiteModal.classList.add('open');
  }
  function closeWebsiteModal(){
    websiteModal.classList.remove('open');
    websiteForm.reset();
    editingWebsiteId = null;
  }

  function openContactModal(contact){
    editingContactId = contact ? contact.id : null;
    document.getElementById('contactModalTitle').textContent = contact ? 'Modifier le contact' : 'Ajouter un contact';
    document.getElementById('contactName').value = contact ? (contact.name||'') : '';
    document.getElementById('contactPhone').value = contact ? (contact.phone||'') : '';
    document.getElementById('contactEmail').value = contact ? (contact.email||'') : '';
    document.getElementById('contactIsPublic').checked = contact ? (contact.is_public === true || contact.is_public === 'true') : false;
    document.getElementById('contactNotes').value = contact ? (contact.notes||'') : '';
    contactModal.classList.add('open');
  }
  function closeContactModal(){
    contactModal.classList.remove('open');
    contactForm.reset();
    editingContactId = null;
  }

  async function loadAgents(){
    try{
      var data = await api('/user/agents');
      agents = data.userAgents || [];
      agentSelect.innerHTML = '<option value="">Sélectionnez un agent</option>' + agents.map(function(a){
        var name = a.custom_name || (a.agent && a.agent.name) || ('Agent #' + a.id);
        return '<option value="'+a.id+'">'+esc(name)+'</option>';
      }).join('');
      if(!agents.length){
        currentAgentId = '';
        agentSelect.value = '';
        showEmpty();
        return;
      }
      if(!currentAgentId || !agents.some(function(a){ return String(a.id)===String(currentAgentId); })){
        currentAgentId = String(agents[0].id);
      }
      window.currentAgentId = currentAgentId;
      agentSelect.value = currentAgentId;
      showContent();
      await loadIdentity(true);
    }catch(e){
      showEmpty();
      notify("Impossible de charger vos agents.", 'error');
    }
  }

  async function loadIdentity(force){
    if(!currentAgentId){ showEmpty(); return; }
    if(loaded && !force){ return; }
    loaded = true;
    try{
      var data = await api('/user/agents/'+currentAgentId+'/identity');
      identity = data.identity || null;
      accounts = data.accounts || [];
      images = data.images || [];
      websites = data.websites || [];
      contacts = data.contacts || [];
      palette = (identity && Array.isArray(identity.palette)) ? identity.palette : [];
      logoFile = null;
      removeLogo = false;
      var logoInput = document.getElementById('identityLogoInput');
      if(logoInput) logoInput.value = '';
      setFields();
      renderPalette();
      renderAccounts();
      renderWebsites();
      renderContacts();
      renderImages();
      renderLogo();
    }catch(e){
      loaded = false;
      notify("Impossible de charger l'identité.", 'error');
    }
  }

saveBtn.addEventListener('click', async function(){
    if(!currentAgentId){ notify('Sélectionnez un agent.', 'error'); return; }
    var fd = new FormData();
    fd.append('_token', csrf);
    fd.append('objective', document.getElementById('identityObjective').value.trim());
    fd.append('charter', document.getElementById('identityCharter').value.trim());
    fd.append('secteur_activite', document.getElementById('identitySecteurActivite').value.trim());
    fd.append('localisation', document.getElementById('identityLocalisation').value.trim());
    fd.append('cible', document.getElementById('identityCible').value.trim());
    fd.append('palette', JSON.stringify(palette));
    if(logoFile) fd.append('logo', logoFile);
    if(removeLogo) fd.append('remove_logo', '1');
    try{
      var data = await api('/user/agents/'+currentAgentId+'/identity', {method:'POST', body:fd});
      identity = data.identity || identity;
      palette = (identity && Array.isArray(identity.palette)) ? identity.palette : [];
      logoFile = null;
      removeLogo = false;
      var logoInput = document.getElementById('identityLogoInput');
      if(logoInput) logoInput.value = '';
      renderPalette();
      renderLogo();
      notify(data.message || 'Identité enregistrée', 'success');
    }catch(err){
      if(err.data && err.data.errors){
        notify(Object.values(err.data.errors).flat().join('\n'), 'error');
      } else {
        notify(err.message || "Erreur lors de l'enregistrement", 'error');
      }
    }
  });

  document.getElementById('paletteAddBtn').addEventListener('click', function(){
    var nameInput = document.getElementById('paletteName');
    var colorInput = document.getElementById('paletteColor');
    var name = nameInput.value.trim();
    var hex = colorInput.value.toUpperCase();
    if(!/^#[0-9A-F]{6}$/.test(hex)){ notify('Couleur invalide.', 'error'); return; }
    if(!name) name = hex;
    if(palette.some(function(c){ return String(c.hex).toUpperCase()===hex; })){ notify('Cette couleur existe déjà.', 'error'); return; }
    palette.push({name:name, hex:hex});
    nameInput.value = '';
    renderPalette();
  });

  paletteListEl.addEventListener('click', function(e){
    var btn = e.target.closest('[data-palette-remove]');
    if(!btn) return;
    var idx = parseInt(btn.getAttribute('data-palette-remove'), 10);
    if(!isNaN(idx)) palette.splice(idx, 1);
    renderPalette();
  });

  document.getElementById('identityLogoSelectBtn').addEventListener('click', function(){
    document.getElementById('identityLogoInput').click();
  });
  document.getElementById('identityLogoInput').addEventListener('change', function(e){
    var file = e.target.files && e.target.files[0];
    if(!file) return;
    logoFile = file;
    removeLogo = false;
    renderLogo();
  });
  document.getElementById('identityLogoRemoveBtn').addEventListener('click', function(){
    logoFile = null;
    removeLogo = true;
    document.getElementById('identityLogoInput').value = '';
    renderLogo();
  });

  document.getElementById('identityImagesAddBtn').addEventListener('click', function(){
    if(!currentAgentId){ notify('Sélectionnez un agent.', 'error'); return; }
    imagesInput.click();
  });

  imagesInput.addEventListener('change', async function(e){
    var files = Array.from(e.target.files || []);
    if(!files.length) return;
    if(!currentAgentId){ notify('Sélectionnez un agent.', 'error'); return; }

    var addBtn = document.getElementById('identityImagesAddBtn');
    addBtn.disabled = true;
    addBtn.style.opacity = '0.6';

    var fd = new FormData();
    fd.append('_token', csrf);
    files.forEach(function(file){ fd.append('images[]', file); });

    try{
      var data = await api('/user/agents/'+currentAgentId+'/identity/images', {method:'POST', body:fd});
      images = data.images || [];
      renderImages();
      notify(data.message || 'Images ajoutées', 'success');
    }catch(err){
      if(err.data && err.data.errors){
        notify(Object.values(err.data.errors).flat().join('\n'), 'error');
      } else {
        notify(err.message || "Erreur lors de l'upload", 'error');
      }
    }finally{
      addBtn.disabled = false;
      addBtn.style.opacity = '1';
      imagesInput.value = '';
    }
  });

  imagesListEl.addEventListener('click', async function(e){
    var btn = e.target.closest('[data-identity-image-delete]');
    if(!btn) return;

    var id = btn.getAttribute('data-identity-image-delete');

    if(window.Swal){
      var r = await Swal.fire({title:'Supprimer cette image ?', text:'Cette action est irréversible.', icon:'warning', showCancelButton:true, confirmButtonText:'Supprimer', cancelButtonText:'Annuler', confirmButtonColor:'#e85a5a'});
      if(!r.isConfirmed) return;
    }

    try{
      var data = await api('/user/agents/'+currentAgentId+'/identity/images/'+id, {method:'DELETE'});
      images = data.images || [];
      renderImages();
      notify(data.message || 'Image supprimée', 'success');
    }catch(err){
      notify(err.message || 'Erreur', 'error');
    }
  });

  agentSelect.addEventListener('change', async function(){
    currentAgentId = agentSelect.value || '';
    window.currentAgentId = currentAgentId;
    if(currentAgentId){
      showContent();
      await loadIdentity(true);
    } else {
      showEmpty();
    }
  });

  document.getElementById('accountAddBtn').addEventListener('click', function(){
    if(!currentAgentId){ notify('Sélectionnez un agent.', 'error'); return; }
    openAccountModal(null);
  });
  document.getElementById('accountModalClose').addEventListener('click', closeAccountModal);
  document.getElementById('accountCancelBtn').addEventListener('click', closeAccountModal);
  accountModal.addEventListener('click', function(e){ if(e.target===accountModal) closeAccountModal(); });

accountForm.addEventListener('submit', async function(e){
    e.preventDefault();
    if(!currentAgentId){ notify('Sélectionnez un agent.', 'error'); return; }
    var handle = document.getElementById('accountHandle').value.trim();
    if(!handle){ notify('Renseignez le pseudo / nom du compte.', 'error'); return; }
    var payload = {
      platform: document.getElementById('accountPlatform').value,
      handle: handle,
      url: document.getElementById('accountUrl').value.trim() || null,
      style: document.getElementById('accountStyle').value.trim() || null,
      notes: document.getElementById('accountNotes').value.trim() || null
    };
    try{
      var url = editingAccountId
        ? '/user/agents/'+currentAgentId+'/identity/accounts/'+editingAccountId
        : '/user/agents/'+currentAgentId+'/identity/accounts';
      var data = editingAccountId
        ? await api(url, {method:'PUT', body:JSON.stringify(payload)})
        : await api(url, {method:'POST', body:JSON.stringify(payload)});
      notify(data.message || 'Compte enregistré', 'success');
      closeAccountModal();
      await loadIdentity(true);
    }catch(err){
      if(err.data && err.data.errors){
        notify(Object.values(err.data.errors).flat().join('\n'), 'error');
      } else {
        notify(err.message || 'Erreur lors de l\'enregistrement', 'error');
      }
    }
  });

  accountsListEl.addEventListener('click', async function(e){
    var editBtn = e.target.closest('[data-account-edit]');
    if(editBtn){
      var acc = accounts.find(function(a){ return String(a.id)===String(editBtn.getAttribute('data-account-edit')); });
      if(acc) openAccountModal(acc);
      return;
    }
    var delBtn = e.target.closest('[data-account-delete]');
    if(delBtn){
      var id = delBtn.getAttribute('data-account-delete');
      if(window.Swal){
        var r = await Swal.fire({title:'Supprimer ce compte inspirant ?', text:'Cette action est irréversible.', icon:'warning', showCancelButton:true, confirmButtonText:'Supprimer', cancelButtonText:'Annuler', confirmButtonColor:'#e85a5a'});
        if(!r.isConfirmed) return;
      }
      try{
        var d = await api('/user/agents/'+currentAgentId+'/identity/accounts/'+id, {method:'DELETE'});
        notify(d.message || 'Compte supprimé', 'success');
        await loadIdentity(true);
      }catch(err){ notify(err.message || 'Erreur', 'error'); }
    }
  });

  document.getElementById('websiteAddBtn').addEventListener('click', function(){
    if(!currentAgentId){ notify('Sélectionnez un agent.', 'error'); return; }
    openWebsiteModal(null);
  });
  document.getElementById('websiteModalClose').addEventListener('click', closeWebsiteModal);
  document.getElementById('websiteCancelBtn').addEventListener('click', closeWebsiteModal);
  websiteModal.addEventListener('click', function(e){ if(e.target===websiteModal) closeWebsiteModal(); });

  websiteForm.addEventListener('submit', async function(e){
    e.preventDefault();
    if(!currentAgentId){ notify('Sélectionnez un agent.', 'error'); return; }
    var label = document.getElementById('websiteLabel').value.trim();
    var urlValue = document.getElementById('websiteUrl').value.trim();
    if(!label){ notify('Renseignez le nom du site.', 'error'); return; }
    if(!urlValue){ notify('Renseignez l\'adresse URL du site.', 'error'); return; }
    var payload = {
      label: label,
      url: urlValue,
      notes: document.getElementById('websiteNotes').value.trim() || null
    };
    try{
      var websiteEndpoint = editingWebsiteId
        ? '/user/agents/'+currentAgentId+'/websites/'+editingWebsiteId
        : '/user/agents/'+currentAgentId+'/websites';
      var websiteData = editingWebsiteId
        ? await api(websiteEndpoint, {method:'PUT', body:JSON.stringify(payload)})
        : await api(websiteEndpoint, {method:'POST', body:JSON.stringify(payload)});
      notify(websiteData.message || 'Site web enregistré', 'success');
      closeWebsiteModal();
      await loadIdentity(true);
    }catch(err){
      if(err.data && err.data.errors){
        notify(Object.values(err.data.errors).flat().join('\n'), 'error');
      } else {
        notify(err.message || 'Erreur lors de l\'enregistrement', 'error');
      }
    }
  });

  websitesListEl.addEventListener('click', async function(e){
    var editBtn = e.target.closest('[data-website-edit]');
    if(editBtn){
      var website = websites.find(function(w){ return String(w.id)===String(editBtn.getAttribute('data-website-edit')); });
      if(website) openWebsiteModal(website);
      return;
    }
    var delBtn = e.target.closest('[data-website-delete]');
    if(delBtn){
      var websiteId = delBtn.getAttribute('data-website-delete');
      if(window.Swal){
        var wr = await Swal.fire({title:'Supprimer ce site web ?', text:'Cette action est irréversible.', icon:'warning', showCancelButton:true, confirmButtonText:'Supprimer', cancelButtonText:'Annuler', confirmButtonColor:'#e85a5a'});
        if(!wr.isConfirmed) return;
      }
      try{
        var wd = await api('/user/agents/'+currentAgentId+'/websites/'+websiteId, {method:'DELETE'});
        notify(wd.message || 'Site web supprimé', 'success');
        await loadIdentity(true);
      }catch(err){ notify(err.message || 'Erreur', 'error'); }
    }
  });

  document.getElementById('contactAddBtn').addEventListener('click', function(){
    if(!currentAgentId){ notify('Sélectionnez un agent.', 'error'); return; }
    openContactModal(null);
  });
  document.getElementById('contactModalClose').addEventListener('click', closeContactModal);
  document.getElementById('contactCancelBtn').addEventListener('click', closeContactModal);
  contactModal.addEventListener('click', function(e){ if(e.target===contactModal) closeContactModal(); });

  contactForm.addEventListener('submit', async function(e){
    e.preventDefault();
    if(!currentAgentId){ notify('Sélectionnez un agent.', 'error'); return; }
    var name = document.getElementById('contactName').value.trim();
    var phone = document.getElementById('contactPhone').value.trim();
    if(!name){ notify('Renseignez le nom du contact.', 'error'); return; }
    if(!phone){ notify('Renseignez le numéro de téléphone.', 'error'); return; }
    var payload = {
      name: name,
      phone: phone,
      email: document.getElementById('contactEmail').value.trim() || null,
      is_public: document.getElementById('contactIsPublic').checked,
      notes: document.getElementById('contactNotes').value.trim() || null
    };
    try{
      var contactEndpoint = editingContactId
        ? '/user/agents/'+currentAgentId+'/contacts/'+editingContactId
        : '/user/agents/'+currentAgentId+'/contacts';
      var contactData = editingContactId
        ? await api(contactEndpoint, {method:'PUT', body:JSON.stringify(payload)})
        : await api(contactEndpoint, {method:'POST', body:JSON.stringify(payload)});
      notify(contactData.message || 'Contact enregistré', 'success');
      closeContactModal();
      await loadIdentity(true);
    }catch(err){
      if(err.data && err.data.errors){
        notify(Object.values(err.data.errors).flat().join('\n'), 'error');
      } else {
        notify(err.message || 'Erreur lors de l\'enregistrement', 'error');
      }
    }
  });

  contactsListEl.addEventListener('click', async function(e){
    var editBtn = e.target.closest('[data-contact-edit]');
    if(editBtn){
      var contact = contacts.find(function(c){ return String(c.id)===String(editBtn.getAttribute('data-contact-edit')); });
      if(contact) openContactModal(contact);
      return;
    }
    var delBtn = e.target.closest('[data-contact-delete]');
    if(delBtn){
      var contactId = delBtn.getAttribute('data-contact-delete');
      if(window.Swal){
        var cr = await Swal.fire({title:'Supprimer ce contact ?', text:'Cette action est irréversible.', icon:'warning', showCancelButton:true, confirmButtonText:'Supprimer', cancelButtonText:'Annuler', confirmButtonColor:'#e85a5a'});
        if(!cr.isConfirmed) return;
      }
      try{
        var cd = await api('/user/agents/'+currentAgentId+'/contacts/'+contactId, {method:'DELETE'});
        notify(cd.message || 'Contact supprimé', 'success');
        await loadIdentity(true);
      }catch(err){ notify(err.message || 'Erreur', 'error'); }
    }
  });

  document.addEventListener('click', function(e){
    var nav = e.target.closest('.nav-item[data-page="identite"]');
    if(!nav) return;
    var id = nav.getAttribute('data-agent-id');
    if(id){
      currentAgentId = String(id);
      window.currentAgentId = currentAgentId;
      agentSelect.value = currentAgentId;
    }
    if(!agents.length){
      loadAgents();
    } else {
      showContent();
      loadIdentity(true);
    }
  });

  if(pageEl.classList.contains('active')) loadAgents();
})();

// Studio des Publications handling
(function(){
  var pageEl = document.getElementById('page-studio');
  var listEl = document.getElementById('studioPublicationsList');
  var detailEl = document.getElementById('studioDetail');
  var countEl = document.getElementById('studioCount');
  var refreshBtn = document.getElementById('studioRefreshBtn');
  if(!pageEl || !listEl || !detailEl || !countEl || !refreshBtn) return;

  var csrf = document.querySelector('meta[name="csrf-token"]') ? document.querySelector('meta[name="csrf-token"]').getAttribute('content') : '';
  var publications = [];
  var selectedId = null;
  var generatedText = '';

  var EMPTY_DETAIL = '<div class="studio-detail-empty">Sélectionnez une publication dans la liste pour afficher toutes ses informations jusqu\'au visuel.</div>';

  function notify(msg, type){
    if(window.Swal){
      return Swal.fire({toast:true, position:'bottom', icon:type||'success', title:msg, showConfirmButton:false, timer:type==='error'?3500:1800, timerProgressBar:true});
    }
  }
  function esc(s){ return String(s==null?'':s).replace(/[&<>"']/g, function(c){ return {'&':'&amp;','<':'&lt;','>':'&gt;','"':'&quot;',"'":'&#039;'}[c]; }); }

  async function api(url, opts){
    opts = opts || {};
    var tokenMeta = document.querySelector('meta[name="csrf-token"]');
    var token = tokenMeta ? tokenMeta.getAttribute('content') : csrf;
    opts.headers = Object.assign({'Accept':'application/json','X-CSRF-TOKEN':token,'X-Requested-With':'XMLHttpRequest'}, opts.headers||{});
    opts.credentials = opts.credentials || 'same-origin';
    var method = String(opts.method || 'GET').toUpperCase();
    if(['POST', 'PUT', 'PATCH', 'DELETE'].indexOf(method) !== -1 && !opts.body){
      opts.body = JSON.stringify({_token:token});
      opts.headers['Content-Type'] = 'application/json';
    }
    if(opts.body && !(opts.body instanceof FormData) && !opts.headers['Content-Type']){
      opts.headers['Content-Type'] = 'application/json';
    }
    var res = await fetch(url, opts);
    if(res.status === 419){
      var message = 'Votre session a expiré ou son jeton de sécurité a changé. La page va être rechargée ; reconnectez-vous si nécessaire, puis relancez le visuel.';
      notify(message, 'warning');
      window.setTimeout(function(){ window.location.reload(); }, 1200);
      throw Object.assign(new Error(message), {status:419});
    }
    var data = {};
    try{ data = await res.json(); }catch(e){}
    if(!res.ok) throw Object.assign(new Error(data.message||'Erreur'), {data:data,status:res.status});
    return data;
  }

  function formatDate(iso){
    var d = new Date(iso);
    if(isNaN(d.getTime())) return iso;
    return d.toLocaleDateString('fr-FR', {weekday:'short', day:'2-digit', month:'2-digit'}) + ' — ' + d.toLocaleTimeString('fr-FR', {hour:'2-digit', minute:'2-digit'});
  }

  function platformLabel(platform){
    var labels = {facebook:'Facebook', instagram:'Instagram', linkedin:'LinkedIn', tiktok:'TikTok', youtube:'YouTube', x:'X (Twitter)', other:'Autre'};
    return labels[platform] || platform || 'Autre';
  }

  function getSelected(){
    for(var i=0;i<publications.length;i++){ if(String(publications[i].id)===String(selectedId)) return publications[i]; }
    return null;
  }

  function renderList(){
    if(!publications.length){
      listEl.innerHTML = '<div class="empty-box">Aucune publication validée. Validez une entrée du calendrier éditorial.</div>';
      countEl.textContent = '0 publication(s)';
      return;
    }
    countEl.textContent = publications.length + ' publication(s)';
    listEl.innerHTML = publications.map(function(p){
      var thumb = p.visual_url
        ? '<img class="studio-list-thumb" src="'+esc(p.visual_url)+'" alt="">'
        : '<div class="studio-list-thumb"></div>';
      return '<button type="button" class="studio-list-item" data-studio-select="'+p.id+'">'
        + thumb
        + '<div class="studio-list-body">'
        + '<h4>'+esc(p.title || 'Publication')+'</h4>'
        + '<div class="studio-list-meta">'+esc(p.agent_name)+' · '+esc(platformLabel(p.platform))+'</div>'
        + '<div class="studio-list-meta">'+esc(formatDate(p.published_at))+'</div>'
        + '</div>'
        + '</button>';
    }).join('');
    highlight();
  }

  function highlight(){
    Array.prototype.forEach.call(listEl.querySelectorAll('[data-studio-select]'), function(el){
      el.classList.toggle('active', String(el.getAttribute('data-studio-select'))===String(selectedId));
    });
  }

function renderDetail(p){
    generatedText = '';
    var hasGeneratedText = !!(p.generated_content && String(p.generated_content).trim() !== '');
    var studioGenerated = !!p.studio_generated;
    var alreadyGenerated = hasGeneratedText || !!p.visual_url || !!p.visual_id;
    var generateLabel = alreadyGenerated ? 'Régénérer la publication' : 'Générer la publication';
    var visualBusy = ['pending', 'running', 'downloading'].indexOf(p.visual_status) !== -1;
    var visualFailed = p.visual_status === 'failed';
    var visualStatusLabels = {
      pending: 'Image en attente dans ComfyUI.',
      running: 'Image en cours de génération par ComfyUI.',
      downloading: 'Image terminée ; récupération et enregistrement en cours.',
      generated: 'Image enregistrée dans Storage.',
      failed: 'Échec de la génération du visuel.'
    };
    var visualStatusText = p.visual_error || visualStatusLabels[p.visual_status] || 'Aucune image générée.';
    var visualButtonLabel = visualFailed ? 'Réessayer la génération du visuel' : 'Générer le visuel';
    if(p.visual_status === 'generated') visualButtonLabel = 'Régénérer uniquement le visuel';
    var visualDirection = p.visual_direction || {};
    var visualSubject = p.visual_subject || visualDirection.subject || '';
    var visualScene = p.visual_scene || visualDirection.scene || '';
    var visualPromptUsed = p.visual_prompt_used || visualDirection.visual_prompt || '';
    var visualNegativePrompt = p.visual_negative_prompt || visualDirection.negative_prompt || '';
    var visualDirectionSummary = (visualSubject || visualScene)
      ? '<div class="studio-visual-meta"><strong>Sujet identifié :</strong> '+esc(visualSubject || '—')
        + (visualScene ? '<br><strong>Scène :</strong> '+esc(visualScene) : '')
        + (visualDirection.main_objects && visualDirection.main_objects.length ? '<br><strong>Objets essentiels :</strong> '+esc(visualDirection.main_objects.join(', ')) : '')
        + '</div>'
      : '';
    var visualPromptFields = '<div class="field"><label for="studioComfyVisualPrompt">Prompt positif envoyé à ComfyUI (modifiable)</label>'
      + '<textarea id="studioComfyVisualPrompt" rows="5" maxlength="2000">'+esc(visualPromptUsed)+'</textarea></div>'
      + '<div class="field"><label for="studioComfyNegativePrompt">Prompt négatif (modifiable)</label>'
      + '<textarea id="studioComfyNegativePrompt" rows="3" maxlength="2000">'+esc(visualNegativePrompt)+'</textarea></div>';
    var visualImage = p.visual_url
      ? '<img class="studio-visual-preview" id="studioVisualImage" src="'+esc(p.visual_url)+'" alt="Visuel généré" style="display:block;">'
      : '<img class="studio-visual-preview" id="studioVisualImage" alt="Visuel généré" style="display:none;">';
    var visualMeta = p.visual_theme ? 'Thème : '+esc(p.visual_theme) : '';
    var visualDownload = p.visual_download_url || p.visual_url;
    var textStatus = studioGenerated
      ? 'Texte généré avec Ollama.'
      : (hasGeneratedText ? 'Texte de secours conservé ; Ollama n’a pas généré le texte.' : 'Texte à générer.');

    // Calendar planning data (read-only reference)
    var calendarCaption = p.calendar_caption ? '<div class="field"><label>Légende (calendrier)</label><textarea rows="3" readonly>'+esc(p.calendar_caption)+'</textarea></div>' : '';
    var calendarHashtags = p.calendar_hashtags ? '<div class="field"><label>Hashtags (calendrier)</label><input value="'+esc(p.calendar_hashtags)+'" readonly></div>' : '';
    var calendarVisualPrompt = p.calendar_visual_prompt ? '<div class="field"><label>Prompt visuel (calendrier)</label><textarea rows="2" readonly>'+esc(p.calendar_visual_prompt)+'</textarea></div>' : '';

    // Studio production data (AI-generated)
    var studioCaption = p.caption ? '<div class="field"><label>Légende générée</label><textarea rows="4" readonly id="studioCaption">'+esc(p.caption)+'</textarea></div>' : '';
    var studioHashtags = p.hashtags ? '<div class="field"><label>Hashtags générés</label><input value="'+esc(p.hashtags)+'" readonly id="studioHashtags"></div>' : '';
    var studioVisualPrompt = p.visual_prompt ? '<div class="field"><label>Prompt visuel généré</label><textarea rows="3" readonly id="studioVisualPrompt">'+esc(p.visual_prompt)+'</textarea></div>' : '';
    var studioCta = p.cta ? '<div class="field"><label>Appel à l\'action</label><input value="'+esc(p.cta)+'" readonly id="studioCta"></div>' : '';

    detailEl.innerHTML = '<div class="studio-detail-head">'
      + '<div class="studio-meta" style="margin-bottom:0;">'
      + '<span class="pill v">'+esc(p.agent_name)+'</span>'
      + '<span class="pill o">'+esc(platformLabel(p.platform))+'</span>'
      + '<span class="pill g">'+esc(formatDate(p.published_at))+'</span>'
      + (p.pillar ? '<span class="pill">'+esc(p.pillar)+'</span>' : '')
      + '</div>'
      + '<h2 class="studio-detail-title">'+esc(p.title || 'Publication')+'</h2>'
      + '</div>'
      + '<div class="studio-section"><h3>Planification (Calendrier)</h3>'
      + calendarCaption + calendarHashtags + calendarVisualPrompt
      + '</div>'
      + '<div class="studio-section"><h3>Production (Studio IA)</h3>'
      + '<div id="studioTextStatus" class="sub" style="margin-bottom:10px;">'+esc(textStatus)+'</div>'
      + ((studioGenerated || hasGeneratedText)
          ? studioCaption + studioHashtags + studioVisualPrompt + studioCta
          : '<div class="empty-box">Aucune génération effectuée. Cliquez sur « Générer la publication » pour produire le contenu avec Ollama et l\'image avec ComfyUI.</div>')
      + '</div>'
      + '<div class="studio-generated" id="studioGeneratedBox" style="display:none;">'
      + '<div class="field"><label>Texte complet de la publication</label><textarea rows="8" readonly id="studioGeneratedText"></textarea></div>'
      + '</div>'
      + '<div class="studio-actions" style="justify-content:flex-start;gap:8px;flex-wrap:wrap;">'
      + '<button type="button" class="btn-inline primary" id="studioGenerateBtn" data-entry="'+p.id+'">'+generateLabel+'</button>'
      + '<button type="button" class="btn-inline" id="studioGenerateVisualBtn" data-entry="'+p.id+'"'
      + (visualBusy ? ' disabled' : '')
      + '>'+esc(visualBusy ? 'Génération en cours…' : visualButtonLabel)+'</button>'
      + '<button type="button" class="btn-inline" id="studioCopyBtn" style="display:none;">Copier la publication</button>'
      + '</div>'
      + '<div class="studio-visual-box">'
      + '<div style="display:flex;gap:8px;align-items:center;flex-wrap:wrap;">'
      + '<span id="studioVisualPromptStatus" class="sub" style="color:var(--muted);font-size:12.5px;">'
      + esc(p.visual_prompt ? 'Prompt visuel préparé.' : 'Prompt visuel en attente.')+'</span>'
      + '<span id="studioVisualStatus" class="sub" style="color:var(--muted);font-size:12.5px;">'+esc(visualStatusText)+'</span>'
      + '</div>'
      + visualDirectionSummary
      + visualPromptFields
      + visualImage
      + '<a id="studioVisualDownload" class="btn-inline" href="'+esc(visualDownload || '#')+'" download="'+esc(p.visual_name || 'visuel-genere.png')+'"'
      + (visualDownload ? '' : ' style="display:none;"')+'>Télécharger le visuel</a>'
      + '<button type="button" class="btn-inline primary" id="studioPublishBtn" data-entry="'+p.id+'"'
      + (p.is_published ? ' disabled' : '')+' style="'+(visualDownload ? '' : 'display:none;')+'">Publier</button>'
      + '<div id="studioVisualMeta" class="studio-visual-meta">'+visualMeta+'</div>'
      + '<div class="studio-engagement" id="studioEngagementBox">'
      + '<span class="eng-stat"><span class="eng-num" id="studioEngLikes">—</span> <span class="eng-lbl">J\'aime</span></span>'
      + '<span class="eng-stat"><span class="eng-num" id="studioEngComments">—</span> <span class="eng-lbl">Commentaires</span></span>'
      + '<span class="eng-stat"><span class="eng-num" id="studioEngShares">—</span> <span class="eng-lbl">Partages</span></span>'
      + '</div>'
      + '<div class="studio-comments">'
      + '<div class="panel-head"><span id="studioCommentsCount">0</span> commentaire(s)</div>'
      + '<div class="studio-comments-list" id="studioCommentsList"><div class="empty-comments">Chargement des commentaires…</div></div>'
      + '<div id="studioReplyForm" style="display:none;padding:10px 14px; border-top:1px solid var(--border);">'
      + '<div class="field"><textarea id="studioReplyMessage" rows="2" placeholder="Écrire une réponse…"></textarea></div>'
      + '<input type="hidden" id="studioReplyCommentId">'
      + '<div class="actions"><button type="button" class="btn-inline btn-comment-reply" id="studioReplyCancel">Annuler</button><button type="button" class="btn-inline primary btn-comment-reply" id="studioReplySend">Envoyer</button></div>'
      + '</div>'
      + '</div>'
      + '</div>';

    if(hasGeneratedText){
      generatedText = String(p.generated_content);
      var textEl = document.getElementById('studioGeneratedText');
      var genBox = document.getElementById('studioGeneratedBox');
      var copyBtn = document.getElementById('studioCopyBtn');
      if(textEl) textEl.value = generatedText;
      if(genBox) genBox.style.display = 'block';
      if(copyBtn) copyBtn.style.display = 'inline-flex';
    }
    if(p && p.is_published && p.platform_post_id){
      loadEngagement(p.id);
      loadComments(p.id);
    }
  }

  function resumeVisualIfPending(p){
    if(!p || !p.visual_id) return;
    if(['pending', 'running', 'downloading'].indexOf(String(p.visual_status || '')) === -1) return;
    setVisualStatus('Reprise du suivi du visuel…', true);
    pollVisual(p.visual_id, null, function(){});
  }

  function selectPublication(id){
    stopVisualPoll();
    selectedId = id;
    var p = getSelected();
    highlight();
    if(p){ renderDetail(p); resumeVisualIfPending(p); }
  }

  async function load(){
    stopVisualPoll();
    listEl.innerHTML = '<div class="empty-box">Chargement…</div>';
    try{
      var d = await api('/user/publications/validated');
      publications = d.entries || [];
      if(selectedId && !getSelected()) selectedId = null;
      renderList();
      var p = getSelected();
      if(p){ renderDetail(p); resumeVisualIfPending(p); } else { detailEl.innerHTML = EMPTY_DETAIL; }
    }catch(e){
      listEl.innerHTML = '<div class="empty-box">Impossible de charger les publications.</div>';
      detailEl.innerHTML = EMPTY_DETAIL;
      notify('Erreur de chargement', 'error');
    }
  }

  var visualPollTimer = null;
  var visualPollResolve = null;

  function stopVisualPoll(){
    if(visualPollTimer){
      clearTimeout(visualPollTimer);
      visualPollTimer = null;
    }
    if(visualPollResolve){
      var pendingResolve = visualPollResolve;
      visualPollResolve = null;
      pendingResolve();
    }
  }

  function finishVisualPoll(){
    visualPollTimer = null;
    if(visualPollResolve){
      var done = visualPollResolve;
      visualPollResolve = null;
      done();
    }
  }

  var VISUAL_POLL_INTERVAL = 15000;
  var VISUAL_POLL_TIMEOUT = 14400000;

  function setVisualStatus(text, loading){
    var el = document.getElementById('studioVisualStatus');
    if(!el) return;
    var spinner = loading
      ? '<span class="kd-spinner" style="width:12px;height:12px;border-width:1.5px;margin-right:6px;vertical-align:-1px;"></span>'
      : '';
    el.innerHTML = spinner + esc(text);
  }

  function setTextStatus(text, loading){
    var el = document.getElementById('studioTextStatus');
    if(!el) return;
    var spinner = loading
      ? '<span class="kd-spinner" style="width:12px;height:12px;border-width:1.5px;margin-right:6px;vertical-align:-1px;"></span>'
      : '';
    el.innerHTML = spinner + esc(text);
  }

  function setStudioBtnLoading(btn, label){
    if(!btn) return;
    btn.disabled = true;
    btn.innerHTML = '<span class="kd-spinner" aria-hidden="true"></span><span>'+esc(label)+'</span>';
  }

  function applyVisualState(publication, visual, status){
    if(!publication || !visual) return;
    publication.visual_id = visual.id || publication.visual_id;
    publication.visual_status = status || visual.status || publication.visual_status;
    publication.visual_prompt_used = visual.prompt || publication.visual_prompt_used || '';
    publication.visual_negative_prompt = visual.negative_prompt || publication.visual_negative_prompt || '';
    publication.visual_direction = visual.visual_direction || publication.visual_direction || {};
    publication.visual_scene = visual.visual_scene || publication.visual_direction.scene || '';
    publication.visual_subject = visual.visual_subject || publication.visual_subject || '';
    publication.visual_style = visual.visual_style || publication.visual_style || '';
    publication.visual_error = visual.error || null;
    if(visual.url){
      publication.visual_url = visual.url;
      publication.visual_download_url = visual.download_url || visual.url;
      publication.visual_name = visual.filename || publication.visual_name;
      publication.visual_theme = visual.theme || publication.visual_theme;
    }
  }

  function handleVisualTimeout(){
    finishVisualPoll();
    setVisualStatus('Génération toujours en cours côté moteur. Le visuel apparaîtra automatiquement au prochain chargement de la page.', false);
    notify('Le moteur de génération est encore en cours.', 'info');
  }

async function pollVisual(visualId, btn, done, deadline){
    done = done || function(){};
    deadline = deadline || (Date.now() + VISUAL_POLL_TIMEOUT);
    visualPollResolve = done;
    var p = getSelected();
    var imageEl = document.getElementById('studioVisualImage');
    var statusEl = document.getElementById('studioVisualStatus');
    var metaEl = document.getElementById('studioVisualMeta');

    if(!statusEl || !imageEl){ finishVisualPoll(); return; }

    try{
      var d = await api('/user/publications/visuals/'+visualId);
      var v = d.visual || {};
      var st = d.status || v.status || 'pending';
      applyVisualState(p, v, st);

      if(st === 'generated'){
        finishVisualPoll();
        if(p){
          p.visual_url = v.url;
          p.visual_download_url = v.download_url || v.url;
          p.visual_name = v.filename;
          p.visual_theme = v.theme;
          p.visual_id = v.id;
          p.visual_status = 'generated';
          p.visual_error = null;
        }
        if(v.url){ imageEl.src = v.url; imageEl.style.display = 'block'; }
        var downloadEl = document.getElementById('studioVisualDownload');
        if(downloadEl && v.url){
          downloadEl.href = v.download_url || v.url;
          downloadEl.download = v.filename || 'visuel-genere.png';
          downloadEl.style.display = 'inline-flex';
        }
        var publishEl = document.getElementById('studioPublishBtn');
        if(publishEl && v.url){
          publishEl.style.display = 'inline-flex';
          publishEl.disabled = p && p.is_published ? true : false;
        }
        if(metaEl){ metaEl.textContent = 'Thème : '+(v.theme||'')+' · Modèle : '+(v.model||'ComfyUI'); }
        setVisualStatus('Visuel généré.', false);
        if(btn) btn.disabled = false;
        if(p){
          var item = listEl.querySelector('[data-studio-select="'+p.id+'"]');
          var oldThumb = item ? item.querySelector('.studio-list-thumb') : null;
          if(oldThumb && v.url){
            if(oldThumb.tagName === 'IMG'){ oldThumb.src = v.url; }
            else {
              var img = document.createElement('img');
              img.className = 'studio-list-thumb';
              img.alt = '';
              img.src = v.url;
              oldThumb.replaceWith(img);
            }
          }
        }
        renderList();
        renderDetail(p);
        notify('Visuel généré', 'success');
        return;
      }

      if(st === 'failed'){
        finishVisualPoll();
        if(p){
          p.visual_status = 'failed';
          p.visual_error = d.message || v.error || 'Génération échouée.';
        }
        setVisualStatus(d.message || v.error || 'Génération échouée.', false);
        if(btn) btn.disabled = false;
        if(p) renderDetail(p);
        notify(d.message || 'Génération échouée', 'error');
        return;
      }

      var progressLabels = {
        pending: 'Image en attente dans ComfyUI…',
        running: 'Image en cours de génération par ComfyUI…',
        downloading: 'Image terminée ; récupération du PNG et enregistrement…'
      };
      setVisualStatus(progressLabels[st] || 'Traitement du visuel en cours…', true);

      if(Date.now() >= deadline){
        handleVisualTimeout();
        if(btn) btn.disabled = false;
        return;
      }

      visualPollTimer = setTimeout(function(){ pollVisual(visualId, btn, done, deadline); }, VISUAL_POLL_INTERVAL);
    }catch(err){
      if(Date.now() < deadline){
        setVisualStatus('Génération du visuel en cours… nouvel essai…', true);
        visualPollTimer = setTimeout(function(){ pollVisual(visualId, btn, done, deadline); }, VISUAL_POLL_INTERVAL);
        return;
      }
      handleVisualTimeout();
      if(btn) btn.disabled = false;
    }
  }

  async function loadEngagement(entryId){
    if(!entryId) return;
    var likesEl = document.getElementById('studioEngLikes');
    var commentsEl = document.getElementById('studioEngComments');
    var sharesEl = document.getElementById('studioEngShares');
    if(!likesEl || !commentsEl || !sharesEl) return;
    try{
      var d = await api('/user/publications/'+entryId+'/insights');
      var stats = d && d.insights ? d.insights : {};
      var likes = stats.likes ?? stats.like_count ?? stats.like ?? 0;
      var comments = stats.comments ?? stats.comment_count ?? stats.comment ?? 0;
      var shares = stats.shares ?? stats.share_count ?? stats.share ?? 0;
      likesEl.textContent = likes;
      commentsEl.textContent = comments;
      sharesEl.textContent = shares;
    }catch(e){
      likesEl.textContent = '—';
      commentsEl.textContent = '—';
      sharesEl.textContent = '—';
    }
  }

  async function loadComments(entryId){
    if(!entryId) return;
    var listEl = document.getElementById('studioCommentsList');
    var countEl = document.getElementById('studioCommentsCount');
    if(!listEl || !countEl) return;
    try{
      var d = await api('/user/publications/'+entryId+'/comments');
      var items = Array.isArray(d && d.comments) ? d.comments : [];
      countEl.textContent = items.length;
      if(!items.length){
        listEl.innerHTML = '<div class="empty-comments">Aucun commentaire pour le moment.</div>';
        return;
      }
      listEl.innerHTML = items.map(function(c){
        var author = c.author || c.username || c.name || 'Utilisateur';
        var initial = String(author).charAt(0).toUpperCase();
        var text = c.message || c.text || c.body || '';
        var time = c.created_time || c.created_at || '';
        var cid = c.id || c.comment_id || '';
        return '<div class="comment-item" id="comment-'+esc(cid)+'">'
          + '<div class="av">'+esc(initial)+'</div>'
          + '<div class="body">'
          + '<div class="head"><span class="author">'+esc(author)+'</span><span class="time">'+esc(time)+'</span></div>'
          + '<div class="text">'+esc(text)+'</div>'
          + (cid ? '<button type="button" class="btn-comment-reply" onclick="openReplyForm(\''+esc(cid)+'\', this)">Répondre</button>' : '')
          + '</div>'
          + '</div>';
      }).join('');
    }catch(e){
      listEl.innerHTML = '<div class="empty-comments">Impossible de charger les commentaires.</div>';
    }
  }

  function openReplyForm(commentId, btn){
    var form = document.getElementById('studioReplyForm');
    var idEl = document.getElementById('studioReplyCommentId');
    var txt = document.getElementById('studioReplyMessage');
    if(!form || !idEl || !txt) return;
    idEl.value = commentId;
    txt.value = '';
    form.style.display = 'block';
    txt.focus();
    // move form right after the clicked comment
    var target = document.getElementById('comment-'+esc(commentId));
    if(target && target.parentElement){
      target.parentElement.insertAdjacentElement('afterend', form);
    }
  }

  document.getElementById('studioReplyCancel') && document.getElementById('studioReplyCancel').addEventListener('click', function(){
    var form = document.getElementById('studioReplyForm');
    if(form) form.style.display = 'none';
  });

  document.getElementById('studioReplySend') && document.getElementById('studioReplySend').addEventListener('click', async function(){
    var form = document.getElementById('studioReplyForm');
    var idEl = document.getElementById('studioReplyCommentId');
    var txt = document.getElementById('studioReplyMessage');
    var entry = getSelected();
    if(!idEl || !txt || !entry) return;
    var commentId = idEl.value;
    var message = txt.value.trim();
    if(!commentId || !message){ notify('Veuillez écrire un message.', 'warning'); return; }
    var btn = document.getElementById('studioReplySend');
    var original = btn.textContent;
    btn.disabled = true;
    btn.textContent = 'Envoi…';
    try{
      await api('/user/publications/'+entry.id+'/comments/reply', {
        method:'POST',
        body:JSON.stringify({comment_id:commentId, message:message})
      });
      notify('Réponse envoyée.', 'success');
      form.style.display = 'none';
      loadComments(entry.id);
      if(entry.is_published && entry.platform_post_id) loadEngagement(entry.id);
    }catch(err){
      notify(err.message || 'Échec de l\'envoi de la réponse.', 'error');
    }finally{
      btn.disabled = false;
      btn.textContent = original;
    }
  });

  async function loadMetaAccounts(){
    var listEl = document.getElementById('dashboardMetaAccountsList');
    if(!listEl) return;
    try{
      var d = await api('/user/meta-accounts');
      var accounts = Array.isArray(d && d.accounts) ? d.accounts : [];
      if(!accounts.length){
        listEl.innerHTML = '<div class="meta-account-empty">Aucun compte Meta connecté. Ajoutez vos comptes depuis « Pages & Comptes Meta ».</div>';
        return;
      }
      var platformLabel = function(p){ var l = {facebook:'Facebook', instagram:'Instagram'}; return l[p] || p || 'Meta'; };
      var statusLabel = function(s){ var l = {connected:'Connecté', disconnected:'Déconnecté', pending:'En attente'}; return l[s] || s; };
      var statusClass = function(s){ return s === 'connected' ? 'connected' : (s === 'disconnected' ? 'disconnected' : 'pending'); };
      listEl.innerHTML = accounts.map(function(a){
        var initial = String(a.name || 'Compte').charAt(0).toUpperCase();
        var avatar = a.picture_url ? '<img src="'+esc(a.picture_url)+'" alt="">' : esc(initial);
        return '<div class="meta-account-item">'
          + '<div class="av">'+avatar+'</div>'
          + '<div class="info">'
          + '<div class="nm">'+esc(a.name||'Compte Meta')+' <span class="tag-fb" style="font-size:10px;padding:2px 6px;border-radius:10px;background:rgba(23,185,255,.15);color:#17B9FF;">'+esc(platformLabel(a.platform))+'</span></div>'
          + '<div class="sub">ID : '+esc(a.meta_id||'—')+' · '+esc(statusLabel(a.status))+'</div>'
          + '</div>'
          + '<span class="status '+esc(statusClass(a.status))+'">'+esc(statusLabel(a.status))+'</span>'
          + '</div>';
      }).join('');
    }catch(e){
      listEl.innerHTML = '<div class="meta-account-empty">Impossible de charger les comptes Meta.</div>';
      document.getElementById('js-debug') && (document.getElementById('js-debug').innerHTML += '<div>meta-accounts error: '+e.message+'</div>', document.getElementById('js-debug').style.display='block');
    }
  }

  loadMetaAccounts();

  async function loadDashboardStats(){
    try{
      var d = await api('/user/meta-accounts/dashboard-stats');
      var stats = d && d.stats ? d.stats : {};
      var map = {
        agents_operational: '100% opérationnels',
        meta_connected: 'Facebook & Instagram',
        pending_validation: 'Validation requise',
        auto_ready: 'Publication auto prête',
        online_meta: 'En ligne sur Meta',
      };
      Object.keys(map).forEach(function(key){
        var el = document.querySelector('.stat[data-stat="'+key+'"] .num');
        if(el) el.textContent = stats[key] !== undefined ? stats[key] : '—';
      });
      var pendingText = document.getElementById('dashboardPendingText');
      if(pendingText){
        var pending = stats.pending_validation !== undefined ? stats.pending_validation : 0;
        pendingText.textContent = pending + ' publication(s) en attente de votre validation explicite';
      }
    }catch(e){
      document.getElementById('js-debug') && (document.getElementById('js-debug').innerHTML += '<div>dashboard-stats error: '+e.message+'</div>', document.getElementById('js-debug').style.display='block');
    }
  }

  loadDashboardStats();

  async function loadVeille(){
    var grid = document.getElementById('veilleGrid');
    if(!grid) return;
    try{
      var d = await api('/user/veille');
      var signals = Array.isArray(d && d.signals) ? d.signals : [];
      if(!signals.length){
        grid.innerHTML = '<div class="empty-box">Aucune donnée de veille disponible pour le moment.</div>';
        return;
      }
      grid.innerHTML = signals.map(function(s){
        return '<div class="simple-card"><h3>'+esc(s.title)+'</h3><p>'+esc(s.text)+'</p></div>';
      }).join('');
      applyLang(currentLang);
    }catch(e){
      grid.innerHTML = '<div class="empty-box">Impossible de charger les données de veille.</div>';
      document.getElementById('js-debug') && (document.getElementById('js-debug').innerHTML += '<div>veille error: '+e.message+'</div>', document.getElementById('js-debug').style.display='block');
    }
  }

  loadVeille();

  async function loadSubscription(){
    var panel = document.getElementById('subscriptionPanel');
    if(!panel) return;
    try{
      var d = await api('/user/subscription');
      var sub = d && d.subscription ? d.subscription : {};
      var quota = d && d.quota ? d.quota : {};
      var stats = d && d.stats ? d.stats : {};

      var planNameEl = document.getElementById('subPlanName');
      var planPriceEl = document.getElementById('subPlanPrice');
      var quotaUsedEl = document.getElementById('subQuotaUsed');
      var quotaBarEl = document.getElementById('subQuotaBar');
      var renewalEl = document.getElementById('subRenewalDate');
      var featuresEl = document.getElementById('subFeatures');
      var statsEl = document.getElementById('subStats');

      if(planNameEl) planNameEl.textContent = sub.name || 'Gratuit';
      if(planPriceEl) planPriceEl.textContent = (sub.price !== undefined ? sub.price : 0) + ' ' + (sub.currency || 'EUR') + ' / ' + (sub.interval || 'mois');
      if(quotaUsedEl) quotaUsedEl.textContent = (quota.used || 0) + ' / ' + (quota.limit || 0) + ' posts';
      if(quotaBarEl) quotaBarEl.style.width = (quota.percent || 0) + '%';
      if(renewalEl) renewalEl.textContent = sub.renewal_at || '—';

      if(featuresEl && Array.isArray(sub.features) && sub.features.length){
        featuresEl.innerHTML = '<strong>Inclus :</strong> ' + sub.features.map(function(f){ return esc(f); }).join(', ');
      } else {
        featuresEl.innerHTML = '<strong>Inclus :</strong> Aucune fonctionnalité listée.';
      }

      if(statsEl){
        statsEl.textContent = 'Publications : ' + (stats.total_entries || 0) + ' au total · ' + (stats.published || 0) + ' publiées · ' + (stats.validated || 0) + ' en attente de validation.';
      }
    }catch(e){
      panel.innerHTML = '<div class="empty-box">Impossible de charger les données d\'abonnement.</div>';
      document.getElementById('js-debug') && (document.getElementById('js-debug').innerHTML += '<div>subscription error: '+e.message+'</div>', document.getElementById('js-debug').style.display='block');
    }
  }

  loadSubscription();

  var currentLang = localStorage.getItem('lang') || 'fr';

  /* ===== i18n ===== */
  var I18N = {
    fr: {
      'nav.dashboard': 'Tableau de bord',
      'nav.agents': 'Agents IA',
      'nav.pages-meta': 'Pages & Comptes Meta',
      'nav.messagerie': 'Messagerie Support',
      'nav.parametres': 'Paramètres',
      'hero.eyebrow': 'IA Agent Studio',
      'hero.greeting': 'Bonjour, ',
      'hero.subtitle': 'Vos agents IA gèrent le contenu de vos pages sociales avec une rigueur absolue. Chaque publication reste soumise à votre validation avant mise en ligne.',
      'hero.btn.publications': 'Examiner les publications',
      'hero.btn.calendrier': 'Calendrier',
      'alert.t1': 'publication(s) en attente de votre validation explicite',
      'alert.t2': 'Conformément aux règles de sécurité Meta Colab, aucun contenu ne sera programmé ou publié sans votre approbation expresse.',
      'alert.btn': 'Valider maintenant',
      'stat.agents_operational': '100% opérationnels',
      'stat.meta_connected': 'Facebook & Instagram',
      'stat.pending_validation': 'Validation requise',
      'stat.auto_ready': 'Publication auto prête',
      'stat.online_meta': 'En ligne sur Meta',
      'quick.title': 'Accès Rapides',
      'quick.sub': 'Opérations rapides pour piloter vos réseaux',
      'quick.agent': 'Créer un nouvel agent IA',
      'quick.calendar': 'Générer calendrier hebdo',
      'quick.watch': 'Veille concurrentielle IA',
      'quick.plan': 'Forfait : Pack Premium',
      'quick.details': 'Voir détails',
      'meta.title': 'Comptes Meta Connectés & Statut',
      'meta.sub': 'Surveillance continue des jetons OAuth et des autorisations de publication',
      'meta.gérer': 'Gérer',
      'studio.title': 'Studio des Publications',
      'studio.sub': 'Publications validées dans le calendrier éditorial, prêtes à générer.',
      'studio.btn.refresh': 'Actualiser',
      'studio.head.validated': 'Publications validées',
      'studio.head.sub': 'Sélectionnez une publication pour afficher ses informations jusqu\'au visuel.',
      'studio.btn.generate': 'Générer la publication',
      'studio.btn.generate.visual': 'Générer le visuel',
      'calendar.title': 'Calendrier Éditorial',
      'calendar.sub': 'Générez un calendrier à partir de l\'objectif, de la charte et des comptes inspirants de votre agent.',
      'identite.title': 'Identité & Inspiration',
      'identite.sub': 'Objectif, palette & charte, logo et comptes inspirants qui guident l\'agent.',
      'veille.title': 'Veille & Tendances',
      'abonnements.title': 'Abonnements & Quotas',
      'messagerie.title': 'Messagerie Support',
      'my-agents.title': 'Mes Agents IA',
      'my-agents.sub': 'Vos agents configurés et leur périmètre d\'action.',
      'veille.sub': 'Signaux marché et contenus concurrents détectés par l\'agent.',
      'veille.sujet': 'Sujet émergent',
      'veille.sujet_text': 'Hausse de 32% des publications sur « automatisation RH » cette semaine.',
      'veille.concurrent': 'Concurrent actif',
      'veille.concurrent_text': 'Un concurrent a publié 5 posts sponsorisés sur l\'offre ERP.',
      'veille.format': 'Format gagnant',
      'veille.format_text': 'Les carrousels génèrent 2,1x plus d\'engagement ce mois-ci.',
      'abonnements.sub': 'Suivi de votre forfait et de votre consommation.',
      'module.calendrier': 'Calendrier Éditorial',
      'module.studio': 'Studio des Publications',
      'module.identite': 'Identité & Inspiration',
      'module.veille': 'Veille & Tendances',
      'module.abonnements': 'Abonnements & Quotas',
      'lang.fr': 'Français',
      'lang.en': 'English'
    },
    en: {
      'nav.dashboard': 'Dashboard',
      'nav.pages-meta': 'Meta Pages & Accounts',
      'nav.messagerie': 'Support Messaging',
      'nav.parametres': 'Settings',
      'hero.eyebrow': 'IA Agent Studio',
      'hero.greeting': 'Hello, ',
      'hero.subtitle': 'Your AI agents manage the content of your social pages with absolute rigor. Each publication remains subject to your validation before going live.',
      'hero.btn.publications': 'Review Publications',
      'hero.btn.calendrier': 'Calendar',
      'alert.t1': 'publication(s) pending your explicit validation',
      'alert.t2': 'In accordance with Meta Colab security rules, no content will be scheduled or published without your explicit approval.',
      'alert.btn': 'Validate Now',
      'stat.agents_operational': '100% operational',
      'stat.meta_connected': 'Facebook & Instagram',
      'stat.pending_validation': 'Validation required',
      'stat.auto_ready': 'Auto publication ready',
      'stat.online_meta': 'Online on Meta',
      'quick.title': 'Quick Access',
      'quick.sub': 'Quick operations to manage your networks',
      'quick.agent': 'Create a new AI agent',
      'quick.calendar': 'Generate weekly calendar',
      'quick.watch': 'AI competitive intelligence',
      'quick.plan': 'Plan: Premium Pack',
      'quick.details': 'See details',
      'meta.title': 'Connected Meta Accounts & Status',
      'meta.sub': 'Continuous monitoring of OAuth tokens and publishing permissions',
      'meta.gérer': 'Manage',
      'studio.title': 'Publications Studio',
      'studio.sub': 'Validated publications from the editorial calendar, ready to generate.',
      'studio.btn.refresh': 'Refresh',
      'studio.head.validated': 'Validated Publications',
      'studio.head.sub': 'Select a publication to view its information up to the visual.',
      'studio.btn.generate': 'Generate Publication',
      'studio.btn.generate.visual': 'Generate Visual',
      'calendar.title': 'Editorial Calendar',
      'calendar.sub': 'Generate a calendar based on the agent objective, charter, and inspiring accounts.',
      'identite.title': 'Identity & Inspiration',
      'identite.sub': 'Objective, palette & charter, logo and inspiring accounts that guide the agent.',
      'veille.title': 'Trends & Monitoring',
      'abonnements.title': 'Subscriptions & Quotas',
      'messagerie.title': 'Support Messaging',
      'my-agents.title': 'My AI Agents',
      'my-agents.sub': 'Your configured agents and their scope of action.',
      'veille.sub': 'Market signals and competitor content detected by the agent.',
      'veille.sujet': 'Emerging topic',
      'veille.sujet_text': '32% increase in publications on « HR automation » this week.',
      'veille.concurrent': 'Active competitor',
      'veille.concurrent_text': 'A competitor posted 5 sponsored posts on the ERP offer.',
      'veille.format': 'Winning format',
      'veille.format_text': 'Carousels generate 2.1x more engagement this month.',
      'abonnements.sub': 'Track your plan and consumption.',
      'module.calendrier': 'Editorial Calendar',
      'module.studio': 'Publications Studio',
      'module.identite': 'Identity & Inspiration',
      'module.veille': 'Trends & Monitoring',
      'module.abonnements': 'Subscriptions & Quotas',
      'lang.fr': 'French',
      'lang.en': 'English'
    }
  };

  function t(key, lang){
    var dict = I18N[lang] || I18N.fr;
    var val = dict[key];
    return val !== undefined ? val : key;
  }

  function applyLang(lang){
    currentLang = lang;
    window.currentLang = lang;
    document.documentElement.lang = lang;
    document.querySelectorAll('[data-i18n]').forEach(function(el){
      var key = el.getAttribute('data-i18n');
      var val = t(key, lang);
      if(val === key) return;
      if(el.tagName === 'INPUT' || el.tagName === 'TEXTAREA'){
        if(!el.value || el.value === el.getAttribute('data-i18n-default')) el.placeholder = val;
      } else {
        el.textContent = val;
      }
    });
    var labelEl = document.getElementById('langLabel');
    if(labelEl) labelEl.textContent = lang.toUpperCase();
    localStorage.setItem('lang', lang);
  }
  window.applyLang = applyLang;
  window.currentLang = currentLang;

  function initLang(){
    applyLang(currentLang);
    var selector = document.getElementById('langSelector');
    var dropdown = document.getElementById('langDropdown');
    if(selector && dropdown){
      selector.addEventListener('click', function(e){
        e.stopPropagation();
        dropdown.style.display = dropdown.style.display === 'block' ? 'none' : 'block';
      });
      dropdown.querySelectorAll('.lang-option').forEach(function(opt){
        opt.addEventListener('click', function(){
          var l = opt.getAttribute('data-lang');
          dropdown.querySelectorAll('.lang-option').forEach(function(o){o.classList.remove('active');});
          opt.classList.add('active');
          dropdown.style.display = 'none';
          applyLang(l);
        });
      });
    }
    document.addEventListener('click', function(e){
      if(dropdown && dropdown.style.display === 'block' && !selector.contains(e.target) && !dropdown.contains(e.target)){
        dropdown.style.display = 'none';
      }
    });
  }

  initLang();

  async function generateVisual(entryId, btn, existingVisualId){
    var statusEl = document.getElementById('studioVisualStatus');

    stopVisualPoll();

    if(btn) setStudioBtnLoading(btn, 'Génération du visuel…');
    setVisualStatus('Envoi à ComfyUI…', true);

    try{
      var visualId = existingVisualId;
      var v = {};
      var st = 'pending';

      if(!visualId){
        // Start new visual generation
        var selected = getSelected();
        var overrides = {};
        var promptEditor = document.getElementById('studioComfyVisualPrompt');
        var negativeEditor = document.getElementById('studioComfyNegativePrompt');
        var currentPrompt = selected
          ? String(selected.visual_prompt_used || (selected.visual_direction && selected.visual_direction.visual_prompt) || '')
          : '';
        var currentNegative = selected
          ? String(selected.visual_negative_prompt || (selected.visual_direction && selected.visual_direction.negative_prompt) || '')
          : '';
        if(promptEditor && promptEditor.value.trim() && promptEditor.value.trim() !== currentPrompt){
          overrides.visual_prompt = promptEditor.value.trim();
        }
        if(negativeEditor && negativeEditor.value !== currentNegative){
          overrides.negative_prompt = negativeEditor.value;
        }
        var d = await api('/user/publications/'+entryId+'/visual', {
          method:'POST',
          body:JSON.stringify(overrides)
        });
        v = d.visual || {};
        st = d.status || 'pending';
        visualId = v.id;
        applyVisualState(selected, v, st);
      } else {
        // Poll existing visual generation
        var d = await api('/user/publications/visuals/'+visualId);
        v = d.visual || {};
        st = d.status || v.status || 'pending';
      }

      if(st === 'generated' && v.url){
        stopVisualPoll();
        var p = getSelected();
        if(p){
          p.visual_url = v.url;
          p.visual_download_url = v.download_url || v.url;
          p.visual_name = v.filename;
          p.visual_theme = v.theme;
          p.visual_id = v.id;
          p.visual_status = 'generated';
          p.visual_error = null;
        }
        var imageEl = document.getElementById('studioVisualImage');
        var metaEl = document.getElementById('studioVisualMeta');
        if(imageEl){ imageEl.src = v.url; imageEl.style.display = 'block'; }
        if(metaEl){ metaEl.textContent = 'Thème : '+(v.theme||'')+' · Modèle : '+(v.model||'ComfyUI'); }
        setVisualStatus('Visuel généré.', false);
        if(btn) btn.disabled = false;
        renderList();
        renderDetail(p);
        notify('Visuel généré', 'success');
        return;
      }

      setVisualStatus('Génération du visuel par ComfyUI en cours…', true);
      await new Promise(function(resolve){ pollVisual(visualId, btn, resolve); });
    }catch(err){
      stopVisualPoll();
      if(err.status === 419){
        if(btn) btn.disabled = false;
        return;
      }
      var selected = getSelected();
      var failedVisual = err.data && err.data.visual;
      if(selected){
        selected.visual_status = 'failed';
        selected.visual_error = err.message || 'Erreur de génération du visuel.';
        if(failedVisual){
          selected.visual_id = failedVisual.id;
          selected.visual_error = failedVisual.error || selected.visual_error;
        }
        renderDetail(selected);
      }
      setVisualStatus(err.message || 'Erreur', false);
      if(btn) btn.disabled = false;
      notify(err.message || 'Erreur', 'error');
    }
  }

  async function generatePublication(entryId, btn){
    var original = btn.textContent;
    setStudioBtnLoading(btn, 'Génération du descriptif avec Ollama…');
    setTextStatus('Génération du descriptif avec Ollama…', true);
    try{
      var d = await api('/user/publications/'+entryId+'/generate', {method:'POST'});
      var pub = d.publication || {};
      generatedText = pub.text || '';
      var box = document.getElementById('studioGeneratedBox');
      var textEl = document.getElementById('studioGeneratedText');
      var copy = document.getElementById('studioCopyBtn');
      if(textEl) textEl.value = generatedText;
      if(box) box.style.display = 'block';
      if(copy) copy.style.display = 'inline-flex';

      original = 'Régénérer la publication';
      var selected = getSelected();
      if(selected){
        selected.generated_content = generatedText;
        selected.visual_prompt = pub.visual_prompt || selected.visual_prompt;
        selected.caption = pub.caption || selected.caption;
        selected.hashtags = pub.hashtags || selected.hashtags;
        selected.cta = pub.cta || selected.cta;
        selected.studio_generated = !!pub.studio_generated;
        selected.studio_generated_at = pub.studio_generated_at || selected.studio_generated_at;
        selected.visual_status = pub.visual_status || 'none';
        // Visual info from generate response
        if(pub.visual){
          applyVisualState(selected, pub.visual, pub.visual_status);
        }
      }
      renderDetail(selected);
      renderList();
      setTextStatus(pub.studio_generated ? 'Texte généré avec Ollama.' : 'Texte de secours conservé ; Ollama n’a pas généré le texte.', false);
      notify(d.message || 'Texte de la publication généré', 'success');

      // If visual generation was triggered, poll for completion
      var visualInfo = pub.visual;
      var visualStatus = pub.visual_status;
      if(visualInfo && visualInfo.id && ['pending', 'running', 'downloading'].indexOf(visualStatus) !== -1){
        setVisualStatus('Génération du visuel avec ComfyUI en cours…', true);
        await generateVisual(entryId, document.getElementById('studioGenerateVisualBtn'), visualInfo.id);
      } else if(visualInfo && visualStatus === 'generated'){
        // Visual already generated, update UI
        var p = getSelected();
        if(p && visualInfo.url){
          p.visual_url = visualInfo.url;
          p.visual_theme = visualInfo.theme;
          p.visual_id = visualInfo.id;
          p.visual_name = visualInfo.filename;
          p.visual_status = 'generated';
          p.visual_download_url = visualInfo.download_url || visualInfo.url;
          var imageEl = document.getElementById('studioVisualImage');
          var metaEl = document.getElementById('studioVisualMeta');
          var downloadEl = document.getElementById('studioVisualDownload');
          if(imageEl){ imageEl.src = visualInfo.url; imageEl.style.display = 'block'; }
          if(downloadEl){
            downloadEl.href = visualInfo.download_url || visualInfo.url;
            downloadEl.download = visualInfo.filename || 'visuel-genere.png';
            downloadEl.style.display = 'inline-flex';
          }
          if(metaEl){ metaEl.textContent = 'Thème : '+(visualInfo.theme||'')+' · Modèle : '+(visualInfo.model||'ComfyUI'); }
          setVisualStatus('Visuel déjà généré.', false);
          renderDetail(p);
        }
      } else if(visualStatus === 'failed'){
        setVisualStatus((visualInfo && visualInfo.error) || 'La génération du visuel a échoué.', false);
        notify((visualInfo && visualInfo.error) || 'La génération du visuel a échoué.', 'error');
      }
    }catch(err){
      setTextStatus(err.message || 'Erreur lors de la génération du texte.', false);
      notify(err.message || 'Erreur', 'error');
    }finally{
      if(document.body.contains(btn)){
        btn.disabled = false;
        btn.textContent = original;
      }
    }
  }

  listEl.addEventListener('click', function(e){
    var item = e.target.closest('[data-studio-select]');
    if(!item) return;
    selectPublication(item.getAttribute('data-studio-select'));
  });

  detailEl.addEventListener('click', function(e){
    var genBtn = e.target.closest('#studioGenerateBtn');
    if(genBtn){ generatePublication(genBtn.getAttribute('data-entry'), genBtn); return; }

    var visualBtn = e.target.closest('#studioGenerateVisualBtn');
    if(visualBtn){ generateVisual(visualBtn.getAttribute('data-entry'), visualBtn, null); return; }

    var copy = e.target.closest('#studioCopyBtn');
    if(copy){
      if(!generatedText){ notify('Générez d\'abord la publication', 'error'); return; }
      if(navigator.clipboard){
        navigator.clipboard.writeText(generatedText).then(function(){ notify('Publication copiée', 'success'); }, function(){ notify('Copie impossible', 'error'); });
      } else {
        notify('Copie impossible', 'error');
      }
    }

    var publishBtn = e.target.closest('#studioPublishBtn');
    if(publishBtn){
      var entryId = publishBtn.getAttribute('data-entry');
      openPublishModal(entryId);
      return;
    }
  });

  refreshBtn.addEventListener('click', load);

  if(pageEl.classList.contains('active')) load();
  document.addEventListener('click', function(e){
    var nav = e.target.closest('.nav-item[data-page="studio"]');
    if(nav) load();
  });

  /* ===== Publish modal ===== */
  var publishModal = document.getElementById('publishModal');
  var publishAccountList = document.getElementById('publishAccountList');
  var publishCancelBtn = document.getElementById('publishCancelBtn');
  var publishConfirmBtn = document.getElementById('publishConfirmBtn');
  var publishModalClose = document.getElementById('publishModalClose');
  var publishError = document.getElementById('publishError');
  var currentPublishEntryId = null;

  function openPublishModal(entryId){
    currentPublishEntryId = entryId;
    publishError.style.display = 'none';
    publishError.textContent = '';
    publishAccountList.innerHTML = '<div class="empty-box">Chargement des comptes…</div>';
    publishModal.classList.add('open');
    loadPublishAccounts(entryId);
  }

  async function loadPublishAccounts(entryId){
    try{
      var p = publications.find(function(item){ return String(item.id) === String(entryId); });
      var platform = p ? String(p.platform || '').toLowerCase() : '';
      var d = await api('/user/meta-accounts');
      var accounts = Array.isArray(d && d.accounts) ? d.accounts : [];
      var filtered = accounts.filter(function(a){
        return a.status === 'connected' && a.platform && String(a.platform).toLowerCase() === platform;
      });
      if(!filtered.length){
        publishAccountList.innerHTML = '<div class="empty-box">Aucun compte connecté sur '+ (platform === 'instagram' ? 'Instagram' : 'Facebook') +'. Ajoutez-en un dans « Pages & Comptes Meta ».</div>';
        publishConfirmBtn.disabled = true;
        return;
      }
      publishConfirmBtn.disabled = false;
      publishAccountList.innerHTML = filtered.map(function(a){
        return '<label style="display:flex;align-items:center;gap:10px;padding:10px;border:1px solid var(--border);border-radius:8px;background:var(--panel-2);cursor:pointer;">'
          + '<input type="checkbox" name="publish_account" value="'+a.id+'" style="accent-color:var(--cyan);">'
          + '<div style="flex:1;"><div style="font-weight:600;">'+esc(a.name)+'</div><div style="font-size:12px;color:var(--muted);">'+esc(a.platform)+' · ID '+esc(a.meta_id||'—')+'</div></div>'
          + '</label>';
      }).join('');
    }catch(e){
      publishAccountList.innerHTML = '<div class="empty-box">Erreur lors du chargement des comptes.</div>';
      publishConfirmBtn.disabled = true;
    }
  }

  function closePublishModal(){
    publishModal.classList.remove('open');
    currentPublishEntryId = null;
  }

  publishCancelBtn.addEventListener('click', closePublishModal);
  publishModalClose.addEventListener('click', closePublishModal);
  publishModal.addEventListener('click', function(e){
    if(e.target === publishModal) closePublishModal();
  });

  publishConfirmBtn.addEventListener('click', async function(){
    if(!currentPublishEntryId) return;
    var checkboxes = publishAccountList.querySelectorAll('input[name="publish_account"]:checked');
    var ids = Array.from(checkboxes).map(function(cb){ return parseInt(cb.value); });
    if(ids.length === 0){
      publishError.textContent = 'Sélectionnez au moins un compte.';
      publishError.style.display = 'block';
      return;
    }
    publishConfirmBtn.disabled = true;
    publishConfirmBtn.textContent = 'Publication…';
    try{
      var d = await api('/user/publications/'+currentPublishEntryId+'/publish', {
        method:'POST',
        body:JSON.stringify({meta_account_ids: ids})
      });
      if(d.success){
        notify(d.message || 'Publié avec succès.', 'success');
        closePublishModal();
        load();
        renderDetail(publications.find(function(item){ return String(item.id) === String(currentPublishEntryId); }));
      } else {
        publishError.textContent = d.message || 'Échec de la publication.';
        publishError.style.display = 'block';
      }
    }catch(err){
      publishError.textContent = err.message || 'Échec de la publication.';
      publishError.style.display = 'block';
    }finally{
      publishConfirmBtn.disabled = false;
      publishConfirmBtn.textContent = 'Publier';
    }
  });

})();

// Calendrier Éditorial handling
(function(){
  var pageEl = document.getElementById('page-calendrier');
  var agentSelect = document.getElementById('calendarAgentSelect');
  var modelSelect = document.getElementById('calendarModelSelect');
  var startDateInput = document.getElementById('calendarStartDate');
  var weeksInput = document.getElementById('calendarWeeks');
  var postsPerWeekInput = document.getElementById('calendarPostsPerWeek');
  var generateBtn = document.getElementById('calendarGenerateBtn');
  var clearBtn = document.getElementById('calendarClearBtn');
  var statusEl = document.getElementById('calendarStatus');
  var listEl = document.getElementById('calendarList');
  var countEl = document.getElementById('calendarCount');
  var menuEl = document.getElementById('calendarActionMenu');
  var editModal = document.getElementById('calendarEditModal');
  if(!pageEl || !agentSelect || !modelSelect || !startDateInput || !weeksInput || !postsPerWeekInput || !generateBtn || !clearBtn || !statusEl || !listEl || !countEl || !menuEl || !editModal) return;

  var csrf = document.querySelector('meta[name="csrf-token"]') ? document.querySelector('meta[name="csrf-token"]').getAttribute('content') : '';
  var agents = [];
  var models = [];
  var entries = [];
  var currentAgentId = window.currentAgentId ? String(window.currentAgentId) : '';

  function notify(msg, type){
    if(window.Swal){
      return Swal.fire({toast:true,position:'bottom',icon:type||'success',title:msg,showConfirmButton:false,timer:type==='error'?4000:2000,timerProgressBar:true});
    }
  }
  function esc(s){ return String(s==null?'':s).replace(/[&<>"']/g,function(c){return {'&':'&amp;','<':'&lt;','>':'&gt;','"':'&quot;',"'":'&#039;'}[c]; }); }

  async function api(url, opts){
    opts = opts || {};
    opts.headers = Object.assign({'Accept':'application/json','X-CSRF-TOKEN':csrf,'X-Requested-With':'XMLHttpRequest'}, opts.headers||{});
    opts.credentials = 'same-origin';
    if(opts.body && !(opts.body instanceof FormData) && !opts.headers['Content-Type']){
      opts.headers['Content-Type'] = 'application/json';
    }
    var res = await fetch(url, opts);
    var data = {};
    try{ data = await res.json(); }catch(e){}
    if(!res.ok) throw Object.assign(new Error(data.message||'Erreur'), {data:data,status:res.status});
    return data;
  }

  function nextMonday(){
    var d = new Date();
    var iso = d.getDay() === 0 ? 7 : d.getDay();
    var add = (8 - iso) % 7;
    if(add === 0) add = 7;
    d.setDate(d.getDate() + add);
    return d.getFullYear() + '-' + String(d.getMonth()+1).padStart(2,'0') + '-' + String(d.getDate()).padStart(2,'0');
  }
  startDateInput.value = nextMonday();

  function statusPill(status){
    if(status === 'validated') return '<span class="pill g">Validée</span>';
    if(status === 'published') return '<span class="pill v">Publiée</span>';
    if(status === 'draft') return '<span class="pill">Brouillon</span>';
    return '<span class="pill o">En attente</span>';
  }

  function formatDate(iso){
    var d = new Date(iso);
    if(isNaN(d.getTime())) return iso;
    return d.toLocaleDateString('fr-FR', {weekday:'short', day:'2-digit', month:'2-digit'}) + ' — ' + d.toLocaleTimeString('fr-FR', {hour:'2-digit', minute:'2-digit'});
  }

  var menuEntryId = '';

  function closeMenu(){
    menuEl.classList.remove('open');
    menuEntryId = '';
  }

  function openMenu(btn){
    var id = btn.getAttribute('data-cal-menu');
    var entry = entries.find(function(x){ return String(x.id)===String(id); });
    if(!entry) return;

    if(menuEntryId === String(id) && menuEl.classList.contains('open')){
      closeMenu();
      return;
    }

    menuEntryId = String(id);
    var validateItem = menuEl.querySelector('[data-menu-action="validate"]');
    var publishItem = menuEl.querySelector('[data-menu-action="publish"]');
    if(validateItem) validateItem.style.display = (entry.status === 'validated' || entry.status === 'published') ? 'none' : '';
    if(publishItem) publishItem.style.display = entry.status === 'validated' ? '' : 'none';

    menuEl.classList.add('open');
    menuEl.style.visibility = 'hidden';

    var rect = btn.getBoundingClientRect();
    var mw = menuEl.offsetWidth;
    var mh = menuEl.offsetHeight;
    var left = Math.min(rect.right - mw, window.innerWidth - mw - 8);
    var top = rect.bottom + 4;

    if(top + mh > window.innerHeight - 8){
      top = rect.top - mh - 4;
    }

    menuEl.style.left = Math.max(8, left) + 'px';
    menuEl.style.top = Math.max(8, top) + 'px';
    menuEl.style.visibility = '';
  }

  function openEditModal(entry){
    document.getElementById('calendarEditId').value = entry.id;
    document.getElementById('calendarEditPublishedAt').value = (entry.published_at || '').slice(0, 16);
    document.getElementById('calendarEditPlatform').value = entry.platform || 'facebook';
    document.getElementById('calendarEditPillar').value = entry.pillar || '';
    document.getElementById('calendarEditTitle').value = entry.title || '';
    document.getElementById('calendarEditCaption').value = entry.caption || '';
    document.getElementById('calendarEditVisual').value = entry.visual_prompt || '';
    document.getElementById('calendarEditHashtags').value = entry.hashtags || '';
    document.getElementById('calendarEditStatus').value = entry.status || 'pending';
    editModal.classList.add('open');
  }

  function closeEditModal(){
    editModal.classList.remove('open');
  }

  function render(){
    if(!entries.length){
      listEl.innerHTML = '<div class="empty-box">Aucun calendrier généré. Cliquez sur « Générer le calendrier ».</div>';
      countEl.textContent = '0 publication(s)';
      return;
    }
    countEl.textContent = entries.length + ' publication(s)';
    listEl.innerHTML = '<div style="overflow-x:auto;"><table class="data"><thead><tr>'
      + '<th>Date</th><th>Plateforme</th><th>Pilier</th><th>Publication</th><th class="col-statut">Statut</th><th>Actions</th>'
      + '</tr></thead><tbody>'
      + entries.map(function(e){
          var title = e.title ? '<div style="font-weight:600;">'+esc(e.title)+'</div>' : '';
          var caption = e.caption ? '<div style="margin-top:4px;white-space:pre-wrap;">'+esc(e.caption)+'</div>' : '';
          var visual = e.visual_prompt ? '<div style="margin-top:4px;color:var(--muted);font-size:12.5px;"><b>Prompt visuel :</b> '+esc(e.visual_prompt)+'</div>' : '';
          var hashtags = e.hashtags ? '<div style="margin-top:2px;color:var(--cyan);font-size:12.5px;">'+esc(e.hashtags)+'</div>' : '';
          var actions = '<button type="button" class="btn-inline cal-menu-btn" data-cal-menu="'+e.id+'" aria-label="Actions">•••</button>';
          return '<tr>'
            + '<td style="white-space:nowrap;">'+esc(formatDate(e.published_at))+'</td>'
            + '<td>'+esc((e.platform||'facebook').toUpperCase())+'</td>'
            + '<td>'+esc(e.pillar||'—')+'</td>'
            + '<td style="min-width:280px;max-width:440px;">'+title+caption+visual+hashtags+'</td>'
            + '<td class="col-statut">'+statusPill(e.status)+'</td>'
            + '<td>'+actions+'</td>'
            + '</tr>';
        }).join('')
      + '</tbody></table></div>';
  }

  function populateModels(){
    if(!models.length){
      modelSelect.innerHTML = '<option value="">Aucun modèle disponible</option>';
      statusEl.textContent = 'Aucun modèle de génération disponible pour le moment.';
      return;
    }
    modelSelect.innerHTML = models.map(function(m){
      return '<option value="'+esc(m)+'">'+esc(m)+'</option>';
    }).join('');
    statusEl.textContent = models.length + ' modèle(s) de génération disponible(s).';
  }

  async function loadModels(){
    try{
      var data = await api('/user/agents/calendar/models');
      models = data.models || [];
      populateModels();
      if(data.default_model && models.indexOf(data.default_model) !== -1){
        modelSelect.value = data.default_model;
      }
    }catch(e){
      models = [];
      populateModels();
      statusEl.textContent = 'Moteur de génération injoignable. Réessayez dans quelques instants.';
    }
  }

  async function loadAgents(){
    try{
      var data = await api('/user/agents');
      agents = data.userAgents || [];
      agentSelect.innerHTML = '<option value="">Sélectionnez un agent</option>' + agents.map(function(a){
        var name = a.custom_name || (a.agent && a.agent.name) || ('Agent #' + a.id);
        return '<option value="'+a.id+'">'+esc(name)+'</option>';
      }).join('');
      if(!agents.length){
        currentAgentId = '';
        agentSelect.value = '';
        listEl.innerHTML = '<div class="empty-box">Créez d\'abord un agent via « Agents IA → catégorie → Créer un agent ».</div>';
        return;
      }
      if(!currentAgentId || !agents.some(function(a){ return String(a.id)===String(currentAgentId); })){
        currentAgentId = String(agents[0].id);
      }
      window.currentAgentId = currentAgentId;
      agentSelect.value = currentAgentId;
      await loadCalendar();
    }catch(e){
      listEl.innerHTML = '<div class="empty-box">Impossible de charger vos agents.</div>';
      notify('Impossible de charger vos agents.', 'error');
    }
  }

  async function loadCalendar(){
    if(!currentAgentId) return;
    try{
      var data = await api('/user/agents/'+currentAgentId+'/calendar');
      entries = data.entries || [];
      if(data.models){
        models = data.models;
        populateModels();
        if(data.default_model && models.indexOf(data.default_model) !== -1){
          modelSelect.value = data.default_model;
        }
      }
      render();
      statusEl.textContent = entries.length
        ? (entries.length + ' publication(s) chargée(s).')
        : 'Prêt. Aucun calendrier généré pour le moment.';
    }catch(e){
      notify('Impossible de charger le calendrier.', 'error');
      if(!models.length) loadModels();
    }
  }

  var generateBtnHome = generateBtn.innerHTML;
  var calendarLoadingEl = document.getElementById('calendarLoading');
  var calendarElapsedEl = document.getElementById('calendarElapsed');
  var calendarSpinTimer = null;
  var calendarSpinStart = 0;

  function setCalendarLoading(on){
    if(on){
      calendarSpinStart = Date.now();
      if(calendarLoadingEl) calendarLoadingEl.style.display = 'flex';
      if(calendarElapsedEl) calendarElapsedEl.textContent = '0 s';
      generateBtn.classList.add('is-loading');
      generateBtn.disabled = true;
      generateBtn.innerHTML = '<span class="kd-spinner" aria-hidden="true"></span><span>Génération en cours…</span>';
      [modelSelect, agentSelect, startDateInput, weeksInput, postsPerWeekInput, clearBtn].forEach(function(el){
        if(el) el.disabled = true;
      });
      if(calendarSpinTimer) clearInterval(calendarSpinTimer);
      calendarSpinTimer = setInterval(function(){
        if(!calendarElapsedEl) return;
        var s = Math.round((Date.now() - calendarSpinStart) / 1000);
        calendarElapsedEl.textContent = s < 60 ? (s + ' s') : (Math.floor(s / 60) + ' min ' + (s % 60) + ' s');
      }, 1000);
      return;
    }
    if(calendarSpinTimer){ clearInterval(calendarSpinTimer); calendarSpinTimer = null; }
    if(calendarLoadingEl) calendarLoadingEl.style.display = 'none';
    generateBtn.classList.remove('is-loading');
    generateBtn.disabled = false;
    generateBtn.innerHTML = generateBtnHome;
    [modelSelect, agentSelect, startDateInput, weeksInput, postsPerWeekInput, clearBtn].forEach(function(el){
      if(el) el.disabled = false;
    });
  }

  generateBtn.addEventListener('click', async function(){
    if(!currentAgentId){ notify('Sélectionnez un agent.', 'error'); return; }
    var model = modelSelect.value;
    if(!model){ notify('Aucun modèle de génération disponible.', 'error'); return; }

    var payload = {
      model: model,
      start_date: startDateInput.value || null,
      weeks: weeksInput.value,
      posts_per_week: postsPerWeekInput.value
    };

    setCalendarLoading(true);
    statusEl.textContent = 'Génération du calendrier en cours… (cela peut prendre 1 à 5 minutes).';

    try{
      var data = await api('/user/agents/'+currentAgentId+'/calendar/generate', {method:'POST', body:JSON.stringify(payload)});
      entries = data.entries || [];
      render();
      statusEl.textContent = data.message || 'Calendrier généré.';
      notify(data.message || 'Calendrier généré', 'success');
    }catch(err){
      if(err.data && err.data.errors){
        statusEl.textContent = Object.values(err.data.errors).flat().join(' ');
      } else {
        statusEl.textContent = err.message || 'Erreur lors de la génération.';
      }
      notify(err.message || 'Erreur lors de la génération', 'error');
    }finally{
      setCalendarLoading(false);
    }
  });

  clearBtn.addEventListener('click', async function(){
    if(!currentAgentId) return;
    if(window.Swal){
      var r = await Swal.fire({title:'Vider le calendrier ?', text:'Toutes les publications générées seront supprimées.', icon:'warning', showCancelButton:true, confirmButtonText:'Vider', cancelButtonText:'Annuler', confirmButtonColor:'#e85a5a'});
      if(!r.isConfirmed) return;
    }
    try{
      var d = await api('/user/agents/'+currentAgentId+'/calendar', {method:'DELETE'});
      entries = [];
      render();
      statusEl.textContent = d.message || 'Calendrier vidé.';
      notify(d.message || 'Calendrier vidé', 'success');
    }catch(err){ notify(err.message || 'Erreur', 'error'); }
  });

  menuEl.addEventListener('click', async function(e){
    var btn = e.target.closest('[data-menu-action]');
    if(!btn) return;

    var action = btn.getAttribute('data-menu-action');
    var id = menuEntryId;
    closeMenu();
    if(!id) return;

    var entry = entries.find(function(x){ return String(x.id)===String(id); });

    if(action === 'edit'){
      if(entry) openEditModal(entry);
      return;
    }

    if(action === 'validate' || action === 'publish'){
      var status = action === 'publish' ? 'published' : 'validated';
      try{
        var d = await api('/user/agents/'+currentAgentId+'/calendar/'+id+'/status', {method:'PATCH', body:JSON.stringify({status:status})});
        if(entry && d.entry) entry.status = d.entry.status;
        render();
        notify(d.message || 'Statut mis à jour', 'success');
      }catch(err){ notify(err.message || 'Erreur', 'error'); }
      return;
    }

    if(action === 'delete'){
      if(window.Swal){
        var r = await Swal.fire({title:'Supprimer cette publication ?', text:'Cette action est irréversible.', icon:'warning', showCancelButton:true, confirmButtonText:'Supprimer', cancelButtonText:'Annuler', confirmButtonColor:'#e85a5a'});
        if(!r.isConfirmed) return;
      }
      try{
        var d2 = await api('/user/agents/'+currentAgentId+'/calendar/'+id, {method:'DELETE'});
        entries = entries.filter(function(x){ return String(x.id)!==String(id); });
        render();
        notify(d2.message || 'Publication supprimée', 'success');
      }catch(err){ notify(err.message || 'Erreur', 'error'); }
    }
  });

  document.addEventListener('click', function(e){
    var trigger = e.target.closest('[data-cal-menu]');
    if(trigger){
      e.stopPropagation();
      openMenu(trigger);
      return;
    }
    if(!e.target.closest('#calendarActionMenu')) closeMenu();
  });

  document.addEventListener('keydown', function(e){
    if(e.key === 'Escape'){
      closeMenu();
      closeEditModal();
    }
  });

  window.addEventListener('resize', closeMenu);
  window.addEventListener('scroll', closeMenu, true);

  ['calendarEditClose', 'calendarEditCancel'].forEach(function(id){
    var el = document.getElementById(id);
    if(el) el.addEventListener('click', closeEditModal);
  });

  editModal.addEventListener('click', function(e){
    if(e.target === editModal) closeEditModal();
  });

  document.getElementById('calendarEditForm').addEventListener('submit', async function(e){
    e.preventDefault();

    var id = document.getElementById('calendarEditId').value;
    if(!id) return;

    var payload = {
      published_at: document.getElementById('calendarEditPublishedAt').value,
      platform: document.getElementById('calendarEditPlatform').value,
      pillar: document.getElementById('calendarEditPillar').value.trim(),
      title: document.getElementById('calendarEditTitle').value.trim(),
      caption: document.getElementById('calendarEditCaption').value.trim(),
      visual_prompt: document.getElementById('calendarEditVisual').value.trim(),
      hashtags: document.getElementById('calendarEditHashtags').value.trim(),
      status: document.getElementById('calendarEditStatus').value
    };

    try{
      var d = await api('/user/agents/'+currentAgentId+'/calendar/'+id, {method:'PATCH', body:JSON.stringify(payload)});
      var entry = entries.find(function(x){ return String(x.id)===String(id); });
      if(entry && d.entry){
        Object.keys(d.entry).forEach(function(key){ entry[key] = d.entry[key]; });
      }
      render();
      closeEditModal();
      notify(d.message || 'Publication mise à jour', 'success');
    }catch(err){
      if(err.data && err.data.errors){
        notify(Object.values(err.data.errors).flat().join(' '), 'error');
      } else {
        notify(err.message || 'Erreur', 'error');
      }
    }
  });

  agentSelect.addEventListener('change', async function(){
    currentAgentId = agentSelect.value || '';
    window.currentAgentId = currentAgentId;
    if(currentAgentId){
      await loadCalendar();
    } else {
      entries = [];
      render();
    }
  });

  document.addEventListener('click', function(e){
    var nav = e.target.closest('.nav-item[data-page="calendrier"]');
    if(!nav) return;
    var id = nav.getAttribute('data-agent-id');
    if(id){
      currentAgentId = String(id);
      window.currentAgentId = currentAgentId;
      agentSelect.value = currentAgentId;
    }
    if(!agents.length){
      loadAgents();
    } else {
      loadCalendar();
    }
  });

  if(pageEl.classList.contains('active')){
    loadAgents();
  }
})();

window.onerror = function(msg, url, line){
  var dbg = document.getElementById('js-debug');
  if(dbg){
    dbg.innerHTML += '<div style="color:#e85a5a;font-size:12px;margin:4px 0;">JS Error: '+msg+' at line '+line+'</div>';
    dbg.style.display = 'block';
  }
  return false;
};
</script>
</body>
</html>






