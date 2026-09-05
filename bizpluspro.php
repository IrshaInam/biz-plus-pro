<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>BizPulse – Smart Business Dashboard</title>
<script src="https://cdnjs.cloudflare.com/ajax/libs/Chart.js/4.4.1/chart.umd.js"></script>
<style>
  @import url('https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800&family=Space+Grotesk:wght@400;500;600;700&display=swap');

  :root {
    --bg-primary: #0a0f1e;
    --bg-secondary: #0f172a;
    --bg-card: #141b2d;
    --bg-card-hover: #1a2340;
    --accent-blue: #3b82f6;
    --accent-purple: #8b5cf6;
    --accent-cyan: #06b6d4;
    --accent-green: #10b981;
    --accent-amber: #f59e0b;
    --accent-red: #ef4444;
    --text-primary: #f1f5f9;
    --text-secondary: #94a3b8;
    --text-muted: #475569;
    --border: rgba(255,255,255,0.07);
    --border-hover: rgba(255,255,255,0.15);
    --glow-blue: rgba(59,130,246,0.2);
    --glow-purple: rgba(139,92,246,0.2);
    --sidebar-w: 260px;
  }
  .light {
    --bg-primary: #f0f4ff;
    --bg-secondary: #ffffff;
    --bg-card: #ffffff;
    --bg-card-hover: #f8faff;
    --text-primary: #0f172a;
    --text-secondary: #475569;
    --text-muted: #94a3b8;
    --border: rgba(0,0,0,0.08);
    --border-hover: rgba(0,0,0,0.15);
    --glow-blue: rgba(59,130,246,0.08);
    --glow-purple: rgba(139,92,246,0.08);
  }

  * { margin:0; padding:0; box-sizing:border-box; }
  body { font-family:'Inter',sans-serif; background:var(--bg-primary); color:var(--text-primary); min-height:100vh; transition:background 0.3s,color 0.3s; }

  /* ── AUTH SCREENS ── */
  .auth-screen {
    display:none; position:fixed; inset:0; z-index:1000;
    background:var(--bg-primary);
    align-items:center; justify-content:center;
    overflow:hidden;
  }
  .auth-screen.active { display:flex; }

  .auth-bg {
    position:absolute; inset:0; overflow:hidden;
  }
  .auth-orb {
    position:absolute; border-radius:50%;
    filter:blur(80px); opacity:0.4;
    animation:orbFloat 8s ease-in-out infinite;
  }
  .auth-orb:nth-child(1) { width:400px;height:400px;background:var(--accent-blue);top:-100px;right:-100px;animation-delay:0s; }
  .auth-orb:nth-child(2) { width:300px;height:300px;background:var(--accent-purple);bottom:-80px;left:-80px;animation-delay:3s; }
  .auth-orb:nth-child(3) { width:200px;height:200px;background:var(--accent-cyan);top:50%;left:50%;animation-delay:6s; }

  @keyframes orbFloat {
    0%,100% { transform:translate(0,0) scale(1); }
    50% { transform:translate(20px,-20px) scale(1.05); }
  }

  .auth-grid-bg {
    position:absolute; inset:0;
    background-image:
      linear-gradient(rgba(59,130,246,0.05) 1px, transparent 1px),
      linear-gradient(90deg, rgba(59,130,246,0.05) 1px, transparent 1px);
    background-size:40px 40px;
    animation:gridMove 20s linear infinite;
  }
  @keyframes gridMove {
    0% { background-position:0 0; }
    100% { background-position:40px 40px; }
  }

  .auth-card {
    position:relative; z-index:10;
    background:rgba(20,27,45,0.85);
    backdrop-filter:blur(20px);
    border:1px solid var(--border-hover);
    border-radius:24px;
    padding:48px 40px;
    width:100%; max-width:460px;
    box-shadow:0 0 60px rgba(59,130,246,0.1);
    animation:cardIn 0.6s cubic-bezier(0.16,1,0.3,1);
  }
  .light .auth-card { background:rgba(255,255,255,0.9); }
  @keyframes cardIn {
    from { opacity:0; transform:translateY(30px) scale(0.95); }
    to { opacity:1; transform:translateY(0) scale(1); }
  }

  .auth-logo {
    display:flex; align-items:center; gap:12px; margin-bottom:32px;
  }
  .auth-logo-icon {
    width:44px; height:44px; border-radius:12px;
    background:linear-gradient(135deg,var(--accent-blue),var(--accent-purple));
    display:flex; align-items:center; justify-content:center;
    font-size:20px; color:#fff;
    box-shadow:0 0 20px var(--glow-blue);
  }
  .auth-logo-text { font-family:'Space Grotesk',sans-serif; font-size:22px; font-weight:700; letter-spacing:-0.5px; }
  .auth-logo-text span { color:var(--accent-blue); }

  .auth-title { font-size:26px; font-weight:700; margin-bottom:6px; letter-spacing:-0.5px; }
  .auth-subtitle { color:var(--text-secondary); font-size:14px; margin-bottom:28px; }

  .form-group { margin-bottom:16px; }
  .form-label { font-size:13px; font-weight:500; color:var(--text-secondary); margin-bottom:6px; display:block; }
  .form-input {
    width:100%; padding:12px 16px; border-radius:12px;
    background:rgba(255,255,255,0.05); border:1px solid var(--border);
    color:var(--text-primary); font-size:14px; font-family:'Inter',sans-serif;
    transition:all 0.2s; outline:none;
  }
  .light .form-input { background:rgba(0,0,0,0.03); }
  .form-input:focus { border-color:var(--accent-blue); box-shadow:0 0 0 3px rgba(59,130,246,0.15); }
  .form-input::placeholder { color:var(--text-muted); }

  .form-row { display:grid; grid-template-columns:1fr 1fr; gap:12px; }

  .role-select { appearance:none; cursor:pointer; }

  .btn-primary {
    width:100%; padding:14px; border-radius:12px;
    background:linear-gradient(135deg,var(--accent-blue),var(--accent-purple));
    color:#fff; font-size:15px; font-weight:600; border:none; cursor:pointer;
    transition:all 0.2s; margin-top:8px;
    box-shadow:0 4px 20px rgba(59,130,246,0.3);
    position:relative; overflow:hidden;
  }
  .btn-primary::after {
    content:''; position:absolute; inset:0;
    background:linear-gradient(135deg,transparent,rgba(255,255,255,0.1),transparent);
    transform:translateX(-100%);
    transition:transform 0.5s;
  }
  .btn-primary:hover::after { transform:translateX(100%); }
  .btn-primary:hover { transform:translateY(-1px); box-shadow:0 6px 30px rgba(59,130,246,0.4); }
  .btn-primary:active { transform:translateY(0); }

  .auth-divider { text-align:center; margin:20px 0; color:var(--text-muted); font-size:13px; position:relative; }
  .auth-divider::before,.auth-divider::after { content:''; position:absolute; top:50%; width:42%; height:1px; background:var(--border); }
  .auth-divider::before { left:0; } .auth-divider::after { right:0; }

  .auth-link { text-align:center; font-size:14px; color:var(--text-secondary); margin-top:16px; }
  .auth-link a { color:var(--accent-blue); text-decoration:none; font-weight:500; }

  .demo-accounts {
    margin-top:20px; padding:16px; border-radius:12px;
    background:rgba(59,130,246,0.08); border:1px solid rgba(59,130,246,0.15);
  }
  .demo-accounts p { font-size:12px; color:var(--text-secondary); margin-bottom:8px; font-weight:500; }
  .demo-pill {
    display:inline-block; padding:4px 10px; border-radius:8px; font-size:11px; margin:2px;
    background:rgba(59,130,246,0.15); color:var(--accent-blue); cursor:pointer;
    transition:all 0.15s;
  }
  .demo-pill:hover { background:rgba(59,130,246,0.3); }

  .toast {
    position:fixed; bottom:24px; right:24px; z-index:9999;
    padding:12px 20px; border-radius:12px;
    background:var(--accent-green); color:#fff;
    font-size:14px; font-weight:500;
    transform:translateX(200%); transition:transform 0.3s cubic-bezier(0.16,1,0.3,1);
    box-shadow:0 4px 20px rgba(16,185,129,0.3);
  }
  .toast.show { transform:translateX(0); }
  .toast.error { background:var(--accent-red); }

  /* ── MAIN DASHBOARD LAYOUT ── */
  #dashboard { display:none; }
  #dashboard.active { display:flex; min-height:100vh; }

  .sidebar {
    width:var(--sidebar-w); background:var(--bg-card);
    border-right:1px solid var(--border);
    position:fixed; top:0; left:0; bottom:0;
    overflow-y:auto; z-index:100;
    transition:transform 0.3s cubic-bezier(0.16,1,0.3,1);
    display:flex; flex-direction:column;
  }
  .sidebar::-webkit-scrollbar { width:4px; }
  .sidebar::-webkit-scrollbar-thumb { background:var(--border-hover); border-radius:4px; }

  .sidebar-logo {
    padding:24px 20px 20px;
    display:flex; align-items:center; gap:10px;
    border-bottom:1px solid var(--border);
  }
  .sidebar-logo-icon {
    width:36px; height:36px; border-radius:10px;
    background:linear-gradient(135deg,var(--accent-blue),var(--accent-purple));
    display:flex; align-items:center; justify-content:center; font-size:16px; color:#fff;
  }
  .sidebar-logo-text { font-family:'Space Grotesk',sans-serif; font-weight:700; font-size:18px; }
  .sidebar-logo-text span { color:var(--accent-blue); }

  .nav-section { padding:16px 12px 8px; }
  .nav-label { font-size:10px; font-weight:600; letter-spacing:1.5px; color:var(--text-muted); text-transform:uppercase; padding:0 8px 8px; }

  .nav-item {
    display:flex; align-items:center; gap:10px;
    padding:10px 12px; border-radius:10px; cursor:pointer;
    color:var(--text-secondary); font-size:14px; font-weight:500;
    transition:all 0.15s; margin-bottom:2px; position:relative;
  }
  .nav-item:hover { background:rgba(59,130,246,0.08); color:var(--text-primary); }
  .nav-item.active { background:linear-gradient(135deg,rgba(59,130,246,0.15),rgba(139,92,246,0.15)); color:var(--accent-blue); }
  .nav-item.active::before {
    content:''; position:absolute; left:0; top:20%; bottom:20%;
    width:3px; border-radius:0 3px 3px 0;
    background:linear-gradient(var(--accent-blue),var(--accent-purple));
  }
  .nav-icon { font-size:18px; width:20px; text-align:center; }

  .nav-badge {
    margin-left:auto; padding:2px 8px; border-radius:20px;
    font-size:11px; font-weight:600;
    background:rgba(239,68,68,0.15); color:var(--accent-red);
  }

  .sidebar-user {
    margin-top:auto; padding:16px 12px;
    border-top:1px solid var(--border);
  }
  .user-card {
    display:flex; align-items:center; gap:10px;
    padding:10px 12px; border-radius:12px;
    background:rgba(255,255,255,0.04); cursor:pointer;
    transition:background 0.15s;
  }
  .user-card:hover { background:rgba(255,255,255,0.08); }
  .user-avatar {
    width:34px; height:34px; border-radius:50%;
    background:linear-gradient(135deg,var(--accent-blue),var(--accent-purple));
    display:flex; align-items:center; justify-content:center;
    font-size:13px; font-weight:700; color:#fff; flex-shrink:0;
  }
  .user-info .user-name { font-size:13px; font-weight:600; }
  .user-info .user-role { font-size:11px; color:var(--text-secondary); text-transform:capitalize; }

  /* ── MAIN CONTENT ── */
  .main-content {
    margin-left:var(--sidebar-w); flex:1;
    min-height:100vh; overflow:hidden;
  }

  .topbar {
    background:var(--bg-card); border-bottom:1px solid var(--border);
    padding:0 28px; height:64px;
    display:flex; align-items:center; gap:16px;
    position:sticky; top:0; z-index:50;
  }
  .topbar-title { font-size:18px; font-weight:700; flex:1; letter-spacing:-0.3px; }

  .search-wrap { position:relative; }
  .search-input {
    padding:8px 12px 8px 36px; border-radius:10px;
    background:rgba(255,255,255,0.05); border:1px solid var(--border);
    color:var(--text-primary); font-size:13px; width:200px;
    transition:all 0.2s; outline:none;
  }
  .light .search-input { background:rgba(0,0,0,0.04); }
  .search-input:focus { width:260px; border-color:var(--accent-blue); }
  .search-icon { position:absolute; left:10px; top:50%; transform:translateY(-50%); color:var(--text-muted); font-size:15px; }

  .topbar-btn {
    width:38px; height:38px; border-radius:10px;
    background:rgba(255,255,255,0.05); border:1px solid var(--border);
    display:flex; align-items:center; justify-content:center;
    cursor:pointer; color:var(--text-secondary); font-size:17px;
    transition:all 0.15s; position:relative;
  }
  .topbar-btn:hover { background:rgba(255,255,255,0.1); color:var(--text-primary); }

  .notif-dot {
    width:8px; height:8px; border-radius:50%;
    background:var(--accent-red); position:absolute;
    top:6px; right:6px; border:2px solid var(--bg-card);
  }

  /* ── PAGES ── */
  .page { display:none; padding:28px; animation:pageIn 0.3s ease; }
  .page.active { display:block; }
  @keyframes pageIn { from { opacity:0; transform:translateY(8px); } to { opacity:1; transform:translateY(0); } }

  /* ── KPI CARDS ── */
  .kpi-grid { display:grid; grid-template-columns:repeat(auto-fit,minmax(200px,1fr)); gap:16px; margin-bottom:24px; }

  .kpi-card {
    background:var(--bg-card); border:1px solid var(--border);
    border-radius:16px; padding:20px 22px;
    cursor:pointer; transition:all 0.25s;
    position:relative; overflow:hidden;
  }
  .kpi-card::before {
    content:''; position:absolute; top:-40px; right:-40px;
    width:100px; height:100px; border-radius:50%;
    background:var(--kpi-color,var(--accent-blue)); opacity:0.08;
    transition:all 0.3s;
  }
  .kpi-card:hover { transform:translateY(-3px); border-color:var(--border-hover); box-shadow:0 8px 30px rgba(0,0,0,0.15); }
  .kpi-card:hover::before { opacity:0.15; transform:scale(1.5); }

  .kpi-header { display:flex; align-items:center; justify-content:space-between; margin-bottom:12px; }
  .kpi-icon {
    width:40px; height:40px; border-radius:10px;
    background:rgba(var(--kpi-rgb,59,130,246),0.12);
    display:flex; align-items:center; justify-content:center;
    font-size:18px; color:var(--kpi-color,var(--accent-blue));
  }
  .kpi-badge {
    padding:3px 8px; border-radius:6px; font-size:11px; font-weight:600;
  }
  .kpi-badge.up { background:rgba(16,185,129,0.12); color:var(--accent-green); }
  .kpi-badge.down { background:rgba(239,68,68,0.12); color:var(--accent-red); }

  .kpi-value { font-size:26px; font-weight:700; letter-spacing:-1px; line-height:1; margin-bottom:4px; }
  .kpi-label { font-size:13px; color:var(--text-secondary); }

  /* ── CHART CARDS ── */
  .chart-grid { display:grid; grid-template-columns:2fr 1fr; gap:20px; margin-bottom:24px; }
  @media(max-width:900px) { .chart-grid { grid-template-columns:1fr; } }

  .card {
    background:var(--bg-card); border:1px solid var(--border);
    border-radius:16px; padding:20px 22px;
    transition:border-color 0.2s;
  }
  .card:hover { border-color:var(--border-hover); }

  .card-header { display:flex; align-items:center; justify-content:space-between; margin-bottom:20px; }
  .card-title { font-size:15px; font-weight:600; }
  .card-subtitle { font-size:12px; color:var(--text-secondary); margin-top:2px; }

  .card-action {
    font-size:12px; color:var(--accent-blue); cursor:pointer;
    padding:5px 10px; border-radius:7px;
    background:rgba(59,130,246,0.1); transition:background 0.15s;
  }
  .card-action:hover { background:rgba(59,130,246,0.2); }

  /* ── TABLES ── */
  .table-wrap { overflow-x:auto; }
  table { width:100%; border-collapse:collapse; }
  th { font-size:11px; font-weight:600; color:var(--text-muted); text-transform:uppercase; letter-spacing:0.8px; padding:0 12px 12px; text-align:left; border-bottom:1px solid var(--border); }
  td { padding:12px; border-bottom:1px solid var(--border); font-size:13px; }
  tr:last-child td { border-bottom:none; }
  tr:hover td { background:rgba(255,255,255,0.02); }

  .status-badge {
    display:inline-block; padding:3px 10px; border-radius:20px; font-size:11px; font-weight:600;
  }
  .status-paid { background:rgba(16,185,129,0.12); color:var(--accent-green); }
  .status-pending { background:rgba(245,158,11,0.12); color:var(--accent-amber); }
  .status-cancelled { background:rgba(239,68,68,0.12); color:var(--accent-red); }

  .product-cell { display:flex; align-items:center; gap:10px; }
  .product-dot { width:8px; height:8px; border-radius:50%; flex-shrink:0; }

  /* ── PROGRESS BARS ── */
  .progress-bar-wrap { margin-bottom:14px; }
  .progress-header { display:flex; justify-content:space-between; margin-bottom:6px; }
  .progress-name { font-size:13px; font-weight:500; }
  .progress-val { font-size:13px; color:var(--text-secondary); }
  .progress-track { height:6px; border-radius:4px; background:rgba(255,255,255,0.06); overflow:hidden; }
  .light .progress-track { background:rgba(0,0,0,0.06); }
  .progress-fill { height:100%; border-radius:4px; transition:width 1.2s cubic-bezier(0.16,1,0.3,1); }

  /* ── ACTIVITY FEED ── */
  .activity-item { display:flex; gap:12px; padding:10px 0; border-bottom:1px solid var(--border); }
  .activity-item:last-child { border-bottom:none; }
  .activity-dot { width:34px; height:34px; border-radius:50%; flex-shrink:0; display:flex; align-items:center; justify-content:center; font-size:15px; }
  .activity-body { flex:1; }
  .activity-text { font-size:13px; font-weight:500; }
  .activity-time { font-size:11px; color:var(--text-muted); margin-top:2px; }

  /* ── INVENTORY ── */
  .inv-grid { display:grid; grid-template-columns:repeat(auto-fill,minmax(220px,1fr)); gap:14px; }
  .inv-card {
    background:var(--bg-card); border:1px solid var(--border);
    border-radius:14px; padding:18px 18px;
    transition:all 0.2s;
  }
  .inv-card:hover { transform:translateY(-2px); border-color:var(--border-hover); }
  .inv-tag { font-size:10px; font-weight:600; letter-spacing:0.8px; color:var(--text-muted); text-transform:uppercase; margin-bottom:8px; }
  .inv-name { font-size:15px; font-weight:600; margin-bottom:4px; }
  .inv-stock { font-size:22px; font-weight:700; }
  .inv-footer { display:flex; justify-content:space-between; align-items:center; margin-top:10px; }
  .inv-alert { font-size:11px; color:var(--accent-amber); display:flex; align-items:center; gap:4px; }
  .inv-ok { font-size:11px; color:var(--accent-green); }

  /* ── EMPLOYEES ── */
  .emp-grid { display:grid; grid-template-columns:repeat(auto-fill,minmax(260px,1fr)); gap:16px; }
  .emp-card {
    background:var(--bg-card); border:1px solid var(--border);
    border-radius:16px; padding:22px;
    transition:all 0.2s; cursor:pointer;
  }
  .emp-card:hover { transform:translateY(-3px); border-color:var(--border-hover); }
  .emp-avatar {
    width:50px; height:50px; border-radius:50%; margin-bottom:14px;
    display:flex; align-items:center; justify-content:center;
    font-size:18px; font-weight:700; color:#fff;
  }
  .emp-name { font-size:16px; font-weight:600; }
  .emp-role { font-size:12px; color:var(--text-secondary); margin-bottom:14px; }
  .emp-stats { display:grid; grid-template-columns:1fr 1fr; gap:8px; }
  .emp-stat { background:rgba(255,255,255,0.04); border-radius:8px; padding:8px 10px; }
  .light .emp-stat { background:rgba(0,0,0,0.04); }
  .emp-stat-label { font-size:10px; color:var(--text-muted); text-transform:uppercase; letter-spacing:0.5px; }
  .emp-stat-val { font-size:15px; font-weight:700; margin-top:2px; }
  .emp-perf { margin-top:12px; }
  .perf-label { font-size:11px; color:var(--text-secondary); margin-bottom:5px; }
  .perf-track { height:4px; border-radius:4px; background:rgba(255,255,255,0.06); overflow:hidden; }
  .light .perf-track { background:rgba(0,0,0,0.06); }
  .perf-fill { height:100%; border-radius:4px; background:linear-gradient(90deg,var(--accent-blue),var(--accent-purple)); }

  /* ── NOTIFICATIONS ── */
  .notif-list { display:flex; flex-direction:column; gap:10px; }
  .notif-item {
    background:var(--bg-card); border:1px solid var(--border);
    border-radius:14px; padding:16px 18px;
    display:flex; gap:14px; align-items:flex-start;
    transition:all 0.2s; cursor:pointer;
  }
  .notif-item:hover { border-color:var(--border-hover); }
  .notif-item.unread { border-left:3px solid var(--accent-blue); }
  .notif-icon { width:38px; height:38px; border-radius:10px; display:flex; align-items:center; justify-content:center; font-size:17px; flex-shrink:0; }
  .notif-body { flex:1; }
  .notif-title { font-size:14px; font-weight:600; }
  .notif-desc { font-size:12px; color:var(--text-secondary); margin-top:3px; }
  .notif-time { font-size:11px; color:var(--text-muted); margin-top:5px; }

  /* ── SETTINGS ── */
  .settings-grid { display:grid; grid-template-columns:1fr 1fr; gap:20px; }
  @media(max-width:700px) { .settings-grid { grid-template-columns:1fr; } }
  .setting-group { background:var(--bg-card); border:1px solid var(--border); border-radius:16px; padding:22px; }
  .setting-title { font-size:15px; font-weight:700; margin-bottom:16px; display:flex; align-items:center; gap:8px; }
  .setting-row { display:flex; justify-content:space-between; align-items:center; padding:10px 0; border-bottom:1px solid var(--border); }
  .setting-row:last-child { border-bottom:none; }
  .setting-name { font-size:13px; font-weight:500; }
  .setting-desc { font-size:11px; color:var(--text-muted); margin-top:2px; }
  .toggle {
    width:42px; height:23px; border-radius:20px; background:var(--text-muted);
    position:relative; cursor:pointer; transition:background 0.2s; flex-shrink:0;
  }
  .toggle.on { background:var(--accent-blue); }
  .toggle::after { content:''; position:absolute; width:17px; height:17px; border-radius:50%; background:#fff; top:3px; left:3px; transition:transform 0.2s; }
  .toggle.on::after { transform:translateX(19px); }

  /* ── 3D CUBE ANIMATION ── */
  .hero-3d {
    perspective:800px; width:120px; height:120px;
    margin:0 auto 0; display:flex; align-items:center; justify-content:center;
  }
  .cube {
    width:60px; height:60px; transform-style:preserve-3d;
    animation:cubeRotate 8s linear infinite;
  }
  @keyframes cubeRotate {
    0% { transform:rotateX(20deg) rotateY(0deg); }
    100% { transform:rotateX(20deg) rotateY(360deg); }
  }
  .cube-face {
    position:absolute; width:60px; height:60px;
    border:1px solid rgba(59,130,246,0.4);
    display:flex; align-items:center; justify-content:center; font-size:20px;
  }
  .cube-face.front  { background:rgba(59,130,246,0.1); transform:translateZ(30px); }
  .cube-face.back   { background:rgba(139,92,246,0.1); transform:rotateY(180deg) translateZ(30px); }
  .cube-face.left   { background:rgba(6,182,212,0.1); transform:rotateY(-90deg) translateZ(30px); }
  .cube-face.right  { background:rgba(16,185,129,0.1); transform:rotateY(90deg) translateZ(30px); }
  .cube-face.top    { background:rgba(245,158,11,0.1); transform:rotateX(90deg) translateZ(30px); }
  .cube-face.bottom { background:rgba(239,68,68,0.1); transform:rotateX(-90deg) translateZ(30px); }

  /* ── FLOATING PARTICLES ── */
  .particle {
    position:absolute; border-radius:50%;
    animation:particleFloat linear infinite;
    pointer-events:none;
  }
  @keyframes particleFloat {
    0% { transform:translateY(100vh) scale(0); opacity:0; }
    10% { opacity:1; }
    90% { opacity:1; }
    100% { transform:translateY(-10vh) scale(1); opacity:0; }
  }

  /* ── CUSTOMERS ── */
  .customer-grid { display:grid; grid-template-columns:repeat(auto-fill,minmax(280px,1fr)); gap:16px; }
  .customer-card {
    background:var(--bg-card); border:1px solid var(--border);
    border-radius:16px; padding:20px; transition:all 0.2s;
  }
  .customer-card:hover { border-color:var(--border-hover); transform:translateY(-2px); }
  .cust-header { display:flex; align-items:center; gap:12px; margin-bottom:14px; }
  .cust-avatar { width:44px; height:44px; border-radius:50%; display:flex; align-items:center; justify-content:center; font-size:15px; font-weight:700; color:#fff; }
  .cust-name { font-size:15px; font-weight:600; }
  .cust-email { font-size:12px; color:var(--text-secondary); }
  .cust-stats { display:flex; gap:16px; }
  .cust-stat { text-align:center; }
  .cust-stat-val { font-size:16px; font-weight:700; }
  .cust-stat-lbl { font-size:10px; color:var(--text-muted); text-transform:uppercase; letter-spacing:0.5px; }
  .loyalty-badge { margin-top:12px; padding:4px 10px; border-radius:20px; font-size:11px; font-weight:600; display:inline-block; }
  .loyalty-gold { background:rgba(245,158,11,0.15); color:var(--accent-amber); }
  .loyalty-silver { background:rgba(148,163,184,0.15); color:#94a3b8; }
  .loyalty-platinum { background:rgba(139,92,246,0.15); color:var(--accent-purple); }

  /* ── REPORTS ── */
  .report-cards { display:grid; grid-template-columns:repeat(auto-fit,minmax(200px,1fr)); gap:14px; margin-bottom:24px; }
  .report-card {
    background:var(--bg-card); border:1px solid var(--border);
    border-radius:14px; padding:20px; cursor:pointer; transition:all 0.2s;
    display:flex; flex-direction:column; gap:10px;
  }
  .report-card:hover { border-color:var(--accent-blue); transform:translateY(-2px); }
  .report-icon { width:44px; height:44px; border-radius:12px; display:flex; align-items:center; justify-content:center; font-size:20px; }
  .report-name { font-size:14px; font-weight:600; }
  .report-desc { font-size:12px; color:var(--text-secondary); }
  .report-btn {
    margin-top:auto; padding:7px 14px; border-radius:8px;
    background:rgba(59,130,246,0.1); color:var(--accent-blue);
    border:none; cursor:pointer; font-size:12px; font-weight:500;
    transition:background 0.15s; text-align:center;
  }
  .report-btn:hover { background:rgba(59,130,246,0.2); }

  /* ── LIGHT MODE OVERRIDES ── */
  .light .cube-face.front  { border-color:rgba(59,130,246,0.3); }
  .light .auth-card { color:#0f172a; }
  .light .form-input { color:#0f172a; }

  /* ── ANIMATIONS ── */
  @keyframes shimmer {
    0% { background-position:-200% 0; }
    100% { background-position:200% 0; }
  }
  .shimmer {
    background:linear-gradient(90deg,transparent 0%,rgba(255,255,255,0.05) 50%,transparent 100%);
    background-size:200% 100%;
    animation:shimmer 2s infinite;
  }

  .pulse-ring {
    animation:pulseRing 2s cubic-bezier(0.215, 0.61, 0.355, 1) infinite;
  }
  @keyframes pulseRing {
    0% { transform:scale(0.8); opacity:1; }
    50% { transform:scale(1.1); opacity:0.5; }
    100% { transform:scale(0.8); opacity:1; }
  }

  /* ── MINI CHARTS (sparklines) ── */
  .sparkline { display:flex; align-items:flex-end; gap:3px; height:32px; }
  .spark-bar { flex:1; border-radius:3px 3px 0 0; min-height:4px; transition:height 0.5s; }

  /* ── ADD FORM MODAL ── */
  .modal-overlay {
    display:none; position:fixed; inset:0; z-index:500;
    background:rgba(0,0,0,0.6); backdrop-filter:blur(4px);
    align-items:center; justify-content:center;
  }
  .modal-overlay.open { display:flex; }
  .modal {
    background:var(--bg-card); border:1px solid var(--border);
    border-radius:20px; padding:32px; width:100%; max-width:480px;
    animation:cardIn 0.3s cubic-bezier(0.16,1,0.3,1);
  }
  .modal-title { font-size:18px; font-weight:700; margin-bottom:20px; }
  .modal-actions { display:flex; gap:10px; margin-top:20px; }
  .btn-cancel {
    flex:1; padding:11px; border-radius:10px; border:1px solid var(--border);
    background:transparent; color:var(--text-secondary); cursor:pointer; font-size:14px;
    transition:all 0.15s;
  }
  .btn-cancel:hover { background:rgba(255,255,255,0.05); color:var(--text-primary); }
  .btn-submit {
    flex:1; padding:11px; border-radius:10px;
    background:linear-gradient(135deg,var(--accent-blue),var(--accent-purple));
    color:#fff; border:none; cursor:pointer; font-size:14px; font-weight:600;
    transition:all 0.2s;
  }
  .btn-submit:hover { opacity:0.9; }

  /* responsive */
  @media(max-width:768px) {
    .sidebar { transform:translateX(-100%); }
    .sidebar.open { transform:translateX(0); }
    .main-content { margin-left:0; }
    .kpi-grid { grid-template-columns:1fr 1fr; }
  }

  /* page header */
  .page-header { display:flex; align-items:center; justify-content:space-between; margin-bottom:24px; }
  .page-header h1 { font-size:22px; font-weight:700; letter-spacing:-0.5px; }
  .btn-add {
    padding:9px 18px; border-radius:10px;
    background:linear-gradient(135deg,var(--accent-blue),var(--accent-purple));
    color:#fff; border:none; cursor:pointer; font-size:13px; font-weight:600;
    display:flex; align-items:center; gap:6px; transition:all 0.2s;
    box-shadow:0 2px 12px rgba(59,130,246,0.3);
  }
  .btn-add:hover { transform:translateY(-1px); box-shadow:0 4px 20px rgba(59,130,246,0.4); }

  /* floating action particles on hero */
  .float-stat {
    position:absolute; padding:8px 14px; border-radius:12px;
    background:var(--bg-card); border:1px solid var(--border);
    font-size:12px; font-weight:600; white-space:nowrap;
    animation:floatStat 4s ease-in-out infinite;
    backdrop-filter:blur(10px);
  }
  @keyframes floatStat {
    0%,100% { transform:translateY(0); }
    50% { transform:translateY(-6px); }
  }

  /* ── HYPERLINKS ── */
  a.link, .footer-link, .quick-link, .table-link {
    color:var(--accent-blue); text-decoration:none; cursor:pointer;
    transition:color 0.15s, opacity 0.15s;
  }
  a.link:hover, .footer-link:hover, .quick-link:hover, .table-link:hover { text-decoration:underline; opacity:0.85; }

  .breadcrumbs { display:flex; align-items:center; gap:6px; font-size:12px; color:var(--text-secondary); margin-bottom:2px; }
  .breadcrumbs a { color:var(--text-secondary); text-decoration:none; }
  .breadcrumbs a:hover { color:var(--accent-blue); text-decoration:underline; }
  .breadcrumbs .sep { color:var(--text-muted); }
  .breadcrumbs .current { color:var(--text-primary); font-weight:600; }

  .site-footer {
    margin-top:32px; padding:20px 0 4px; border-top:1px solid var(--border);
    display:flex; flex-wrap:wrap; align-items:center; justify-content:space-between; gap:12px;
    font-size:12px; color:var(--text-muted);
  }
  .site-footer .footer-links { display:flex; gap:16px; flex-wrap:wrap; }

  .quicklinks-row { display:flex; gap:8px; flex-wrap:wrap; margin-top:14px; }
  .quicklinks-row .quick-link {
    padding:6px 12px; border-radius:8px; font-size:12px; font-weight:500;
    background:rgba(59,130,246,0.08); border:1px solid rgba(59,130,246,0.15);
  }

  /* ── DROPDOWN MENUS ── */
  .dropdown-wrap { position:relative; }
  .dropdown-panel {
    position:absolute; top:calc(100% + 10px); right:0; z-index:200;
    background:var(--bg-card); border:1px solid var(--border-hover);
    border-radius:14px; box-shadow:0 12px 40px rgba(0,0,0,0.3);
    width:320px; max-height:420px; overflow-y:auto;
    opacity:0; visibility:hidden; transform:translateY(-8px);
    transition:all 0.18s cubic-bezier(0.16,1,0.3,1);
  }
  .dropdown-panel.open { opacity:1; visibility:visible; transform:translateY(0); }
  .dropdown-header { padding:14px 16px; border-bottom:1px solid var(--border); display:flex; justify-content:space-between; align-items:center; }
  .dropdown-header .dh-title { font-size:13px; font-weight:700; }
  .dropdown-header .dh-action { font-size:11px; color:var(--accent-blue); cursor:pointer; }
  .dropdown-list { padding:6px; }
  .dropdown-item {
    display:flex; gap:10px; align-items:flex-start; padding:10px; border-radius:10px;
    cursor:pointer; transition:background 0.12s;
  }
  .dropdown-item:hover { background:rgba(255,255,255,0.05); }
  .dropdown-item .di-icon { width:32px; height:32px; border-radius:9px; display:flex; align-items:center; justify-content:center; font-size:15px; flex-shrink:0; }
  .dropdown-item .di-title { font-size:12.5px; font-weight:600; }
  .dropdown-item .di-desc { font-size:11px; color:var(--text-secondary); margin-top:1px; }
  .dropdown-item .di-time { font-size:10px; color:var(--text-muted); margin-top:3px; }
  .dropdown-empty { padding:30px 16px; text-align:center; font-size:12px; color:var(--text-muted); }

  .profile-menu { width:230px; }
  .profile-menu .pm-head { padding:16px; display:flex; gap:10px; align-items:center; border-bottom:1px solid var(--border); }
  .profile-menu .pm-name { font-size:13px; font-weight:700; }
  .profile-menu .pm-email { font-size:11px; color:var(--text-secondary); }
  .profile-menu .pm-item {
    padding:11px 16px; font-size:13px; font-weight:500; cursor:pointer;
    display:flex; align-items:center; gap:10px; transition:background 0.12s;
  }
  .profile-menu .pm-item:hover { background:rgba(255,255,255,0.05); }
  .profile-menu .pm-divider { height:1px; background:var(--border); margin:4px 0; }

  /* ── LIVE CLOCK / STATUS PILL ── */
  .status-pill {
    display:flex; align-items:center; gap:6px; font-size:12px; color:var(--text-secondary);
    padding:7px 12px; border-radius:10px; background:rgba(255,255,255,0.04); border:1px solid var(--border);
    white-space:nowrap;
  }
  .status-pill .live-dot { width:7px; height:7px; border-radius:50%; background:var(--accent-green); animation:pulseRing 2s infinite; flex-shrink:0; }

  /* ── SIDEBAR COLLAPSE ── */
  .sidebar.collapsed { width:76px; }
  .sidebar.collapsed .sidebar-logo-text,
  .sidebar.collapsed .nav-label,
  .sidebar.collapsed .nav-item span:not(.nav-icon),
  .sidebar.collapsed .nav-badge,
  .sidebar.collapsed .user-info { display:none; }
  .sidebar.collapsed .nav-item { justify-content:center; }
  .sidebar.collapsed ~ .main-content { margin-left:76px; }
  .collapse-btn {
    width:28px; height:28px; border-radius:8px; border:1px solid var(--border);
    background:rgba(255,255,255,0.05); color:var(--text-secondary); cursor:pointer;
    display:flex; align-items:center; justify-content:center; font-size:13px;
    transition:all 0.15s; margin-left:auto;
  }
  .collapse-btn:hover { background:rgba(255,255,255,0.1); color:var(--text-primary); }

  /* ── ROW CHECKBOX TOGGLES ── */
  .row-check {
    width:16px; height:16px; border-radius:5px; border:1.5px solid var(--border-hover);
    cursor:pointer; display:inline-flex; align-items:center; justify-content:center;
    transition:all 0.15s; background:transparent; flex-shrink:0;
  }
  .row-check.checked { background:var(--accent-blue); border-color:var(--accent-blue); }
  .row-check.checked::after { content:'✓'; color:#fff; font-size:10px; font-weight:700; }

  /* ── PUSH PERMISSION BANNER ── */
  .push-banner {
    display:flex; align-items:center; gap:14px; padding:14px 18px; border-radius:14px;
    background:linear-gradient(135deg,rgba(59,130,246,0.1),rgba(139,92,246,0.1));
    border:1px solid rgba(59,130,246,0.2); margin-bottom:20px;
  }
  .push-banner .pb-icon { font-size:22px; }
  .push-banner .pb-text { flex:1; }
  .push-banner .pb-title { font-size:13px; font-weight:700; }
  .push-banner .pb-desc { font-size:11.5px; color:var(--text-secondary); margin-top:2px; }
  .push-banner .pb-btn {
    padding:8px 16px; border-radius:9px; border:none; cursor:pointer;
    background:linear-gradient(135deg,var(--accent-blue),var(--accent-purple));
    color:#fff; font-size:12px; font-weight:600; white-space:nowrap;
  }
  .push-banner .pb-dismiss {
    padding:8px 12px; border-radius:9px; border:1px solid var(--border); background:transparent;
    color:var(--text-secondary); cursor:pointer; font-size:12px;
  }

  /* ── CHATBOT WIDGET ── */
  .chat-launcher {
    position:fixed; bottom:24px; right:24px; z-index:800;
    width:58px; height:58px; border-radius:50%;
    background:linear-gradient(135deg,var(--accent-blue),var(--accent-purple));
    display:flex; align-items:center; justify-content:center;
    font-size:24px; color:#fff; cursor:pointer; border:none;
    box-shadow:0 8px 24px rgba(59,130,246,0.4);
    transition:transform 0.2s;
  }
  .chat-launcher:hover { transform:scale(1.07); }
  .chat-launcher .cl-badge {
    position:absolute; top:-4px; right:-4px; width:18px; height:18px; border-radius:50%;
    background:var(--accent-red); color:#fff; font-size:10px; font-weight:700;
    display:flex; align-items:center; justify-content:center; border:2px solid var(--bg-primary);
  }

  .chat-window {
    position:fixed; bottom:96px; right:24px; z-index:801;
    width:360px; height:480px; max-height:70vh;
    background:var(--bg-card); border:1px solid var(--border-hover);
    border-radius:18px; box-shadow:0 16px 50px rgba(0,0,0,0.35);
    display:flex; flex-direction:column; overflow:hidden;
    opacity:0; visibility:hidden; transform:translateY(16px) scale(0.97);
    transition:all 0.2s cubic-bezier(0.16,1,0.3,1);
  }
  .chat-window.open { opacity:1; visibility:visible; transform:translateY(0) scale(1); }

  .chat-head {
    padding:16px 18px; display:flex; align-items:center; gap:10px;
    background:linear-gradient(135deg,rgba(59,130,246,0.12),rgba(139,92,246,0.1));
    border-bottom:1px solid var(--border);
  }
  .chat-head .ch-avatar {
    width:36px; height:36px; border-radius:50%;
    background:linear-gradient(135deg,var(--accent-blue),var(--accent-purple));
    display:flex; align-items:center; justify-content:center; font-size:16px; color:#fff; flex-shrink:0;
  }
  .chat-head .ch-name { font-size:13.5px; font-weight:700; }
  .chat-head .ch-status { font-size:11px; color:var(--accent-green); display:flex; align-items:center; gap:4px; }
  .chat-head .ch-status .live-dot { width:6px; height:6px; border-radius:50%; background:var(--accent-green); }
  .chat-head .ch-close {
    margin-left:auto; width:28px; height:28px; border-radius:8px; border:none; background:transparent;
    color:var(--text-secondary); cursor:pointer; font-size:15px; display:flex; align-items:center; justify-content:center;
  }
  .chat-head .ch-close:hover { background:rgba(255,255,255,0.08); color:var(--text-primary); }

  .chat-body { flex:1; overflow-y:auto; padding:16px; display:flex; flex-direction:column; gap:10px; }
  .chat-body::-webkit-scrollbar { width:4px; }
  .chat-body::-webkit-scrollbar-thumb { background:var(--border-hover); border-radius:4px; }

  .chat-msg { max-width:84%; padding:10px 13px; border-radius:14px; font-size:13px; line-height:1.45; }
  .chat-msg.bot { align-self:flex-start; background:rgba(255,255,255,0.06); color:var(--text-primary); border-bottom-left-radius:4px; }
  .light .chat-msg.bot { background:rgba(0,0,0,0.05); }
  .chat-msg.user { align-self:flex-end; background:linear-gradient(135deg,var(--accent-blue),var(--accent-purple)); color:#fff; border-bottom-right-radius:4px; }
  .chat-msg.typing { display:flex; gap:4px; padding:13px 16px; }
  .chat-msg.typing span { width:6px; height:6px; border-radius:50%; background:var(--text-muted); animation:typingDot 1.2s infinite; }
  .chat-msg.typing span:nth-child(2) { animation-delay:0.2s; }
  .chat-msg.typing span:nth-child(3) { animation-delay:0.4s; }
  @keyframes typingDot { 0%,60%,100% { opacity:0.3; transform:translateY(0); } 30% { opacity:1; transform:translateY(-3px); } }

  .chat-quick { display:flex; flex-wrap:wrap; gap:6px; padding:0 16px 12px; }
  .chat-quick button {
    padding:6px 11px; border-radius:20px; border:1px solid var(--border); background:transparent;
    color:var(--accent-blue); font-size:11.5px; cursor:pointer; transition:background 0.15s;
  }
  .chat-quick button:hover { background:rgba(59,130,246,0.1); }

  .chat-input-row { display:flex; gap:8px; padding:12px 14px; border-top:1px solid var(--border); }
  .chat-input-row input {
    flex:1; padding:10px 14px; border-radius:20px; border:1px solid var(--border);
    background:rgba(255,255,255,0.04); color:var(--text-primary); font-size:13px; outline:none;
  }
  .light .chat-input-row input { background:rgba(0,0,0,0.03); }
  .chat-input-row input:focus { border-color:var(--accent-blue); }
  .chat-input-row button {
    width:38px; height:38px; border-radius:50%; border:none; cursor:pointer; flex-shrink:0;
    background:linear-gradient(135deg,var(--accent-blue),var(--accent-purple)); color:#fff; font-size:15px;
    display:flex; align-items:center; justify-content:center;
  }

  @media(max-width:480px) {
    .chat-window { width:92vw; right:4vw; bottom:88px; }
  }
</style>
</head>
<body>

<!-- ─── SIGN IN ──────────────────────────────── -->
<div class="auth-screen active" id="screen-signin">
  <div class="auth-bg">
    <div class="auth-grid-bg"></div>
    <div class="auth-orb"></div>
    <div class="auth-orb"></div>
    <div class="auth-orb"></div>
  </div>
  <div class="auth-card">
    <div class="auth-logo">
      <div class="auth-logo-icon">📊</div>
      <div class="auth-logo-text">Biz<span>Pulse</span></div>
    </div>
    <div class="auth-title">Welcome back</div>
    <div class="auth-subtitle">Sign in to your business dashboard</div>

    <div class="form-group">
      <label class="form-label">Email Address</label>
      <input type="email" id="si-email" class="form-input" placeholder="admin@bizpulse.com">
    </div>
    <div class="form-group">
      <label class="form-label">Password</label>
      <input type="password" id="si-pass" class="form-input" placeholder="••••••••">
    </div>
    <button class="btn-primary" onclick="doSignIn()">Sign In →</button>

    <div class="auth-divider">Quick Demo Access</div>
    <div class="demo-accounts">
      <p>🎯 Click to auto-fill:</p>
      <span class="demo-pill" onclick="fillDemo('admin@bizpulse.com','admin123','admin')">👑 Admin</span>
      <span class="demo-pill" onclick="fillDemo('manager@bizpulse.com','mgr123','manager')">📋 Manager</span>
      <span class="demo-pill" onclick="fillDemo('emp@bizpulse.com','emp123','employee')">👤 Employee</span>
    </div>
    <div class="auth-link">Don't have an account? <a href="#" onclick="showScreen('screen-signup')">Create Account</a></div>
  </div>
</div>

<!-- ─── SIGN UP ──────────────────────────────── -->
<div class="auth-screen" id="screen-signup">
  <div class="auth-bg">
    <div class="auth-grid-bg"></div>
    <div class="auth-orb"></div>
    <div class="auth-orb"></div>
    <div class="auth-orb"></div>
  </div>
  <div class="auth-card" style="max-width:520px">
    <div class="auth-logo">
      <div class="auth-logo-icon">📊</div>
      <div class="auth-logo-text">Biz<span>Pulse</span></div>
    </div>
    <div class="auth-title">Create Account</div>
    <div class="auth-subtitle">Join thousands of businesses growing smarter</div>

    <div class="form-row">
      <div class="form-group"><label class="form-label">First Name</label><input id="su-fname" class="form-input" placeholder="Ali"></div>
      <div class="form-group"><label class="form-label">Last Name</label><input id="su-lname" class="form-input" placeholder="Hassan"></div>
    </div>
    <div class="form-group">
      <label class="form-label">Email Address</label>
      <input id="su-email" class="form-input" placeholder="you@company.com">
    </div>
    <div class="form-group">
      <label class="form-label">Business Name</label>
      <input id="su-biz" class="form-input" placeholder="Your Company Ltd.">
    </div>
    <div class="form-row">
      <div class="form-group">
        <label class="form-label">Role</label>
        <select id="su-role" class="form-input role-select">
          <option value="admin">Admin</option>
          <option value="manager">Manager</option>
          <option value="employee">Employee</option>
        </select>
      </div>
      <div class="form-group">
        <label class="form-label">Password</label>
        <input id="su-pass" type="password" class="form-input" placeholder="••••••••">
      </div>
    </div>
    <div class="form-group">
      <label class="form-label">Confirm Password</label>
      <input id="su-pass2" type="password" class="form-input" placeholder="••••••••">
    </div>
    <button class="btn-primary" onclick="doSignUp()">Create My Account →</button>
    <div class="auth-link">Already have an account? <a href="#" onclick="showScreen('screen-signin')">Sign In</a></div>
  </div>
</div>

<!-- ─── MAIN DASHBOARD ──────────────────────── -->
<div id="dashboard">

  <!-- SIDEBAR -->
  <aside class="sidebar" id="sidebar">
    <div class="sidebar-logo">
      <div class="sidebar-logo-icon">📊</div>
      <div class="sidebar-logo-text">Biz<span>Pulse</span></div>
      <button class="collapse-btn" id="collapse-btn" onclick="toggleCollapse()" title="Collapse sidebar">«</button>
    </div>

    <div class="nav-section">
      <div class="nav-label">Main</div>
      <div class="nav-item active" onclick="navTo('page-dashboard',this)">
        <span class="nav-icon">🏠</span> Dashboard
      </div>
      <div class="nav-item" onclick="navTo('page-sales',this)">
        <span class="nav-icon">💰</span> Sales
      </div>
      <div class="nav-item" onclick="navTo('page-inventory',this)">
        <span class="nav-icon">📦</span> Inventory
        <span class="nav-badge">3</span>
      </div>
    </div>

    <div class="nav-section">
      <div class="nav-label">People</div>
      <div class="nav-item" onclick="navTo('page-customers',this)">
        <span class="nav-icon">👥</span> Customers
      </div>
      <div class="nav-item" onclick="navTo('page-employees',this)">
        <span class="nav-icon">👤</span> Employees
      </div>
    </div>

    <div class="nav-section">
      <div class="nav-label">Insights</div>
      <div class="nav-item" onclick="navTo('page-reports',this)">
        <span class="nav-icon">📑</span> Reports
      </div>
      <div class="nav-item" onclick="navTo('page-notifications',this)">
        <span class="nav-icon">🔔</span> Notifications
        <span class="nav-badge">5</span>
      </div>
    </div>

    <div class="nav-section">
      <div class="nav-label">System</div>
      <div class="nav-item" onclick="navTo('page-settings',this)">
        <span class="nav-icon">⚙️</span> Settings
      </div>
      <div class="nav-item" onclick="doLogout()">
        <span class="nav-icon">🚪</span> Logout
      </div>
    </div>

    <div class="sidebar-user">
      <div class="user-card">
        <div class="user-avatar" id="sidebar-avatar">A</div>
        <div class="user-info">
          <div class="user-name" id="sidebar-name">Admin User</div>
          <div class="user-role" id="sidebar-role">Administrator</div>
        </div>
      </div>
    </div>
  </aside>

  <!-- MAIN CONTENT -->
  <div class="main-content">
    <!-- TOPBAR -->
    <div class="topbar">
      <div class="topbar-title" id="page-title">Dashboard Overview</div>
      <div class="status-pill" id="live-clock"><span class="live-dot"></span><span id="clock-text">--:--:--</span></div>
      <div class="search-wrap">
        <span class="search-icon">🔍</span>
        <input class="search-input" placeholder="Search anything...">
      </div>
      <div class="topbar-btn" onclick="toggleTheme()" title="Toggle theme">🌙</div>

      <div class="dropdown-wrap">
        <div class="topbar-btn" onclick="toggleDropdown('notif-dropdown')" title="Notifications">
          🔔<div class="notif-dot" id="bell-dot"></div>
        </div>
        <div class="dropdown-panel" id="notif-dropdown">
          <div class="dropdown-header">
            <div class="dh-title">Notifications</div>
            <div class="dh-action" onclick="markAllRead()">Mark all read</div>
          </div>
          <div class="dropdown-list" id="notif-dropdown-list"></div>
        </div>
      </div>

      <div class="dropdown-wrap">
        <div class="topbar-btn" onclick="toggleDropdown('profile-dropdown')" title="Account">👤</div>
        <div class="dropdown-panel profile-menu" id="profile-dropdown">
          <div class="pm-head">
            <div class="user-avatar" id="menu-avatar">A</div>
            <div>
              <div class="pm-name" id="menu-name">Admin User</div>
              <div class="pm-email" id="menu-email">admin@bizpulse.com</div>
            </div>
          </div>
          <div class="pm-item" onclick="navTo('page-settings',null);closeAllDropdowns();">⚙️ Account Settings</div>
          <div class="pm-item" onclick="navTo('page-notifications',null);closeAllDropdowns();">🔔 Notifications</div>
          <a class="pm-item" href="#" onclick="showToast('Opening help center...');closeAllDropdowns();return false;">❓ Help &amp; Support</a>
          <div class="pm-divider"></div>
          <div class="pm-item" style="color:var(--accent-red);" onclick="doLogout()">🚪 Logout</div>
        </div>
      </div>
    </div>

    <!-- ── PAGE: DASHBOARD ── -->
    <div class="page active" id="page-dashboard">
      <div class="breadcrumbs">
        <a href="#" onclick="navTo('page-dashboard',null);return false;">Home</a>
        <span class="sep">/</span>
        <span class="current">Dashboard</span>
      </div>

      <div class="push-banner" id="push-banner">
        <div class="pb-icon">🔔</div>
        <div class="pb-text">
          <div class="pb-title">Turn on push notifications</div>
          <div class="pb-desc">Get instant alerts for new orders, low stock, and payments — even when this tab isn't focused.</div>
        </div>
        <button class="pb-btn" onclick="requestPushPermission()">Enable</button>
        <button class="pb-dismiss" onclick="dismissPushBanner()">Not now</button>
      </div>

      <!-- 3D Hero Area -->
      <div style="background:var(--bg-card);border:1px solid var(--border);border-radius:20px;padding:28px;margin-bottom:24px;position:relative;overflow:hidden;">
        <div style="position:absolute;top:0;right:0;width:300px;height:300px;background:radial-gradient(circle,rgba(59,130,246,0.12),transparent);pointer-events:none;"></div>

        <div style="display:flex;align-items:center;gap:32px;flex-wrap:wrap;">
          <div style="flex:1;min-width:200px;">
            <div style="font-size:13px;color:var(--accent-blue);font-weight:600;margin-bottom:8px;text-transform:uppercase;letter-spacing:1px;">Welcome back 👋</div>
            <div style="font-size:28px;font-weight:800;letter-spacing:-1px;margin-bottom:8px;" id="hero-greeting">Good Morning, Admin!</div>
            <div style="font-size:14px;color:var(--text-secondary);">Here's what's happening with your business today.</div>
            <div style="display:flex;gap:10px;margin-top:16px;">
              <button class="btn-add" onclick="openModal('modal-add-sale')">+ New Sale</button>
              <button style="padding:9px 18px;border-radius:10px;border:1px solid var(--border);background:transparent;color:var(--text-primary);cursor:pointer;font-size:13px;" onclick="navTo('page-reports',null)">📑 Reports</button>
            </div>
            <div class="quicklinks-row">
              <a class="quick-link" href="#" onclick="navTo('page-inventory',null);return false;">📦 Low stock items</a>
              <a class="quick-link" href="#" onclick="navTo('page-customers',null);return false;">👥 Top customers</a>
              <a class="quick-link" href="https://docs.claude.com" target="_blank" rel="noopener">📘 Help docs ↗</a>
            </div>
          </div>
          <div style="position:relative;">
            <div class="hero-3d">
              <div class="cube">
                <div class="cube-face front">📊</div>
                <div class="cube-face back">💰</div>
                <div class="cube-face left">📦</div>
                <div class="cube-face right">👥</div>
                <div class="cube-face top">📈</div>
                <div class="cube-face bottom">⚡</div>
              </div>
            </div>
          </div>
        </div>
      </div>

      <!-- KPIs -->
      <div class="kpi-grid">
        <div class="kpi-card" style="--kpi-color:#3b82f6;--kpi-rgb:59,130,246">
          <div class="kpi-header">
            <div class="kpi-icon">💰</div>
            <div class="kpi-badge up">▲ 12.5%</div>
          </div>
          <div class="kpi-value">₨ 4.2M</div>
          <div class="kpi-label">Total Revenue</div>
          <div class="sparkline" style="margin-top:10px;">
            <div class="spark-bar" style="height:40%;background:var(--accent-blue);opacity:0.3;"></div>
            <div class="spark-bar" style="height:60%;background:var(--accent-blue);opacity:0.4;"></div>
            <div class="spark-bar" style="height:45%;background:var(--accent-blue);opacity:0.3;"></div>
            <div class="spark-bar" style="height:80%;background:var(--accent-blue);opacity:0.6;"></div>
            <div class="spark-bar" style="height:65%;background:var(--accent-blue);opacity:0.5;"></div>
            <div class="spark-bar" style="height:90%;background:var(--accent-blue);"></div>
          </div>
        </div>
        <div class="kpi-card" style="--kpi-color:#10b981;--kpi-rgb:16,185,129">
          <div class="kpi-header">
            <div class="kpi-icon" style="background:rgba(16,185,129,0.12);color:#10b981;">📦</div>
            <div class="kpi-badge up">▲ 8.3%</div>
          </div>
          <div class="kpi-value" style="color:#10b981;">1,284</div>
          <div class="kpi-label">Orders This Month</div>
          <div class="sparkline" style="margin-top:10px;">
            <div class="spark-bar" style="height:55%;background:#10b981;opacity:0.3;"></div>
            <div class="spark-bar" style="height:70%;background:#10b981;opacity:0.5;"></div>
            <div class="spark-bar" style="height:50%;background:#10b981;opacity:0.3;"></div>
            <div class="spark-bar" style="height:85%;background:#10b981;opacity:0.6;"></div>
            <div class="spark-bar" style="height:75%;background:#10b981;opacity:0.7;"></div>
            <div class="spark-bar" style="height:95%;background:#10b981;"></div>
          </div>
        </div>
        <div class="kpi-card" style="--kpi-color:#8b5cf6;--kpi-rgb:139,92,246">
          <div class="kpi-header">
            <div class="kpi-icon" style="background:rgba(139,92,246,0.12);color:#8b5cf6;">👥</div>
            <div class="kpi-badge up">▲ 24%</div>
          </div>
          <div class="kpi-value" style="color:#8b5cf6;">3,472</div>
          <div class="kpi-label">Active Customers</div>
          <div class="sparkline" style="margin-top:10px;">
            <div class="spark-bar" style="height:30%;background:#8b5cf6;opacity:0.3;"></div>
            <div class="spark-bar" style="height:45%;background:#8b5cf6;opacity:0.4;"></div>
            <div class="spark-bar" style="height:60%;background:#8b5cf6;opacity:0.5;"></div>
            <div class="spark-bar" style="height:75%;background:#8b5cf6;opacity:0.7;"></div>
            <div class="spark-bar" style="height:85%;background:#8b5cf6;opacity:0.8;"></div>
            <div class="spark-bar" style="height:95%;background:#8b5cf6;"></div>
          </div>
        </div>
        <div class="kpi-card" style="--kpi-color:#f59e0b;--kpi-rgb:245,158,11">
          <div class="kpi-header">
            <div class="kpi-icon" style="background:rgba(245,158,11,0.12);color:#f59e0b;">📈</div>
            <div class="kpi-badge down">▼ 2.1%</div>
          </div>
          <div class="kpi-value" style="color:#f59e0b;">68.4%</div>
          <div class="kpi-label">Profit Margin</div>
          <div class="sparkline" style="margin-top:10px;">
            <div class="spark-bar" style="height:75%;background:#f59e0b;opacity:0.6;"></div>
            <div class="spark-bar" style="height:80%;background:#f59e0b;opacity:0.7;"></div>
            <div class="spark-bar" style="height:70%;background:#f59e0b;opacity:0.5;"></div>
            <div class="spark-bar" style="height:85%;background:#f59e0b;opacity:0.7;"></div>
            <div class="spark-bar" style="height:72%;background:#f59e0b;opacity:0.6;"></div>
            <div class="spark-bar" style="height:68%;background:#f59e0b;"></div>
          </div>
        </div>
      </div>

      <!-- Charts Row -->
      <div class="chart-grid">
        <div class="card">
          <div class="card-header">
            <div><div class="card-title">Revenue Overview</div><div class="card-subtitle">Monthly breakdown 2024</div></div>
            <div class="card-action" onclick="showToast('Exporting revenue data as CSV...')">Export</div>
          </div>
          <div style="position:relative;height:220px;">
            <canvas id="revenueChart" role="img" aria-label="Monthly revenue bar chart 2024"></canvas>
          </div>
        </div>
        <div class="card">
          <div class="card-header">
            <div><div class="card-title">Sales by Category</div><div class="card-subtitle">This quarter</div></div>
          </div>
          <div style="position:relative;height:180px;">
            <canvas id="categoryChart" role="img" aria-label="Sales by category donut chart"></canvas>
          </div>
          <div style="display:flex;flex-wrap:wrap;gap:8px;margin-top:12px;">
            <span style="display:flex;align-items:center;gap:4px;font-size:11px;color:var(--text-secondary);"><span style="width:8px;height:8px;border-radius:2px;background:#3b82f6;display:inline-block;"></span>Electronics 38%</span>
            <span style="display:flex;align-items:center;gap:4px;font-size:11px;color:var(--text-secondary);"><span style="width:8px;height:8px;border-radius:2px;background:#8b5cf6;display:inline-block;"></span>Clothing 24%</span>
            <span style="display:flex;align-items:center;gap:4px;font-size:11px;color:var(--text-secondary);"><span style="width:8px;height:8px;border-radius:2px;background:#10b981;display:inline-block;"></span>Food 22%</span>
            <span style="display:flex;align-items:center;gap:4px;font-size:11px;color:var(--text-secondary);"><span style="width:8px;height:8px;border-radius:2px;background:#f59e0b;display:inline-block;"></span>Other 16%</span>
          </div>
        </div>
      </div>

      <!-- Bottom Row -->
      <div style="display:grid;grid-template-columns:1fr 1fr 1fr;gap:20px;flex-wrap:wrap;" id="bottom-grid">
        <!-- Recent Orders -->
        <div class="card" style="grid-column:span 2;">
          <div class="card-header">
            <div><div class="card-title">Recent Orders</div><div class="card-subtitle">Last 5 transactions</div></div>
            <div class="card-action" onclick="navTo('page-sales',null)">View All</div>
          </div>
          <div class="table-wrap">
            <table>
              <thead><tr><th>Order</th><th>Customer</th><th>Product</th><th>Amount</th><th>Status</th></tr></thead>
              <tbody id="recent-orders-body"></tbody>
            </table>
          </div>
        </div>
        <!-- Top Products -->
        <div class="card">
          <div class="card-header"><div class="card-title">Top Products</div></div>
          <div id="top-products-list"></div>
        </div>
      </div>
    </div>

    <!-- ── PAGE: SALES ── -->
    <div class="page" id="page-sales">
      <div class="breadcrumbs"><a href="#" onclick="navTo('page-dashboard',null);return false;">Home</a><span class="sep">/</span><span class="current">Sales</span></div>
      <div class="page-header">
        <div>
          <h1>Sales Management</h1>
          <div style="font-size:13px;color:var(--text-secondary);margin-top:4px;">Track and manage all your sales</div>
        </div>
        <button class="btn-add" onclick="openModal('modal-add-sale')">+ New Sale</button>
      </div>

      <div class="kpi-grid" style="grid-template-columns:repeat(4,1fr);">
        <div class="kpi-card" style="--kpi-color:#3b82f6;--kpi-rgb:59,130,246">
          <div class="kpi-icon" style="margin-bottom:10px;">📅</div>
          <div class="kpi-value">₨ 148K</div>
          <div class="kpi-label">Today's Sales</div>
        </div>
        <div class="kpi-card" style="--kpi-color:#10b981;--kpi-rgb:16,185,129">
          <div class="kpi-icon" style="background:rgba(16,185,129,0.12);color:#10b981;margin-bottom:10px;">📊</div>
          <div class="kpi-value" style="color:#10b981;">₨ 896K</div>
          <div class="kpi-label">This Week</div>
        </div>
        <div class="kpi-card" style="--kpi-color:#8b5cf6;--kpi-rgb:139,92,246">
          <div class="kpi-icon" style="background:rgba(139,92,246,0.12);color:#8b5cf6;margin-bottom:10px;">📆</div>
          <div class="kpi-value" style="color:#8b5cf6;">₨ 4.2M</div>
          <div class="kpi-label">This Month</div>
        </div>
        <div class="kpi-card" style="--kpi-color:#f59e0b;--kpi-rgb:245,158,11">
          <div class="kpi-icon" style="background:rgba(245,158,11,0.12);color:#f59e0b;margin-bottom:10px;">🏆</div>
          <div class="kpi-value" style="color:#f59e0b;">₨ 38.6M</div>
          <div class="kpi-label">Annual Total</div>
        </div>
      </div>

      <div class="card" style="margin-bottom:20px;">
        <div class="card-header">
          <div class="card-title">Sales Trend</div>
          <div style="display:flex;gap:8px;">
            <button style="padding:5px 12px;border-radius:8px;border:1px solid var(--border);background:rgba(59,130,246,0.1);color:var(--accent-blue);font-size:12px;cursor:pointer;">Weekly</button>
            <button style="padding:5px 12px;border-radius:8px;border:1px solid var(--border);background:transparent;color:var(--text-secondary);font-size:12px;cursor:pointer;">Monthly</button>
          </div>
        </div>
        <div style="position:relative;height:200px;">
          <canvas id="salesTrendChart" role="img" aria-label="Weekly sales trend line chart"></canvas>
        </div>
      </div>

      <div class="card">
        <div class="card-header">
          <div class="card-title">All Transactions</div>
          <input style="padding:7px 12px;border-radius:8px;border:1px solid var(--border);background:transparent;color:var(--text-primary);font-size:12px;outline:none;" placeholder="Search orders...">
        </div>
        <div class="table-wrap">
          <table>
            <thead><tr><th style="width:20px;"></th><th>#</th><th>Date</th><th>Customer</th><th>Product</th><th>Qty</th><th>Amount</th><th>Status</th></tr></thead>
            <tbody id="sales-table-body"></tbody>
          </table>
        </div>
      </div>
    </div>

    <!-- ── PAGE: INVENTORY ── -->
    <div class="page" id="page-inventory">
      <div class="breadcrumbs"><a href="#" onclick="navTo('page-dashboard',null);return false;">Home</a><span class="sep">/</span><span class="current">Inventory</span></div>
      <div class="page-header">
        <div>
          <h1>Inventory Management</h1>
          <div style="font-size:13px;color:var(--text-secondary);margin-top:4px;">Monitor stock levels & alerts</div>
        </div>
        <button class="btn-add" onclick="openModal('modal-add-product')">+ Add Product</button>
      </div>

      <div class="kpi-grid" style="margin-bottom:20px;">
        <div class="kpi-card" style="--kpi-color:#3b82f6;--kpi-rgb:59,130,246"><div class="kpi-value">248</div><div class="kpi-label">Total Products</div></div>
        <div class="kpi-card" style="--kpi-color:#f59e0b;--kpi-rgb:245,158,11"><div class="kpi-value" style="color:#f59e0b;">12</div><div class="kpi-label">Low Stock Items</div></div>
        <div class="kpi-card" style="--kpi-color:#ef4444;--kpi-rgb:239,68,68"><div class="kpi-value" style="color:#ef4444;">3</div><div class="kpi-label">Out of Stock</div></div>
        <div class="kpi-card" style="--kpi-color:#10b981;--kpi-rgb:16,185,129"><div class="kpi-value" style="color:#10b981;">₨ 12.4M</div><div class="kpi-label">Stock Value</div></div>
      </div>

      <div class="card" style="margin-bottom:20px;">
        <div class="card-header"><div class="card-title">Stock by Category</div></div>
        <div id="stock-bars"></div>
      </div>

      <div class="inv-grid" id="inventory-grid"></div>
    </div>

    <!-- ── PAGE: CUSTOMERS ── -->
    <div class="page" id="page-customers">
      <div class="breadcrumbs"><a href="#" onclick="navTo('page-dashboard',null);return false;">Home</a><span class="sep">/</span><span class="current">Customers</span></div>
      <div class="page-header">
        <div>
          <h1>Customer Management</h1>
          <div style="font-size:13px;color:var(--text-secondary);margin-top:4px;">Manage profiles & loyalty</div>
        </div>
        <button class="btn-add" onclick="openModal('modal-add-customer')">+ Add Customer</button>
      </div>
      <div class="customer-grid" id="customers-grid"></div>
    </div>

    <!-- ── PAGE: EMPLOYEES ── -->
    <div class="page" id="page-employees">
      <div class="breadcrumbs"><a href="#" onclick="navTo('page-dashboard',null);return false;">Home</a><span class="sep">/</span><span class="current">Employees</span></div>
      <div class="page-header">
        <div>
          <h1>Employee Management</h1>
          <div style="font-size:13px;color:var(--text-secondary);margin-top:4px;">Team performance & attendance</div>
        </div>
        <button class="btn-add" onclick="openModal('modal-add-employee')">+ Add Employee</button>
      </div>
      <div class="emp-grid" id="employees-grid"></div>
    </div>

    <!-- ── PAGE: REPORTS ── -->
    <div class="page" id="page-reports">
      <div class="breadcrumbs"><a href="#" onclick="navTo('page-dashboard',null);return false;">Home</a><span class="sep">/</span><span class="current">Reports</span></div>
      <div class="page-header"><h1>Reports & Analytics</h1></div>
      <div class="report-cards">
        <div class="report-card">
          <div class="report-icon" style="background:rgba(59,130,246,0.12);">📊</div>
          <div class="report-name">Sales Report</div>
          <div class="report-desc">Complete sales breakdown by product, region & time</div>
          <button class="report-btn" onclick="showToast('Generating Sales Report PDF...')">📄 Download PDF</button>
        </div>
        <div class="report-card">
          <div class="report-icon" style="background:rgba(16,185,129,0.12);">📦</div>
          <div class="report-name">Inventory Report</div>
          <div class="report-desc">Stock levels, movements & valuation</div>
          <button class="report-btn" onclick="showToast('Generating Inventory Report...')">📄 Download PDF</button>
        </div>
        <div class="report-card">
          <div class="report-icon" style="background:rgba(139,92,246,0.12);">👥</div>
          <div class="report-name">Customer Report</div>
          <div class="report-desc">Customer acquisition, retention & spending</div>
          <button class="report-btn" onclick="showToast('Generating Customer Report...')">📄 Download PDF</button>
        </div>
        <div class="report-card">
          <div class="report-icon" style="background:rgba(245,158,11,0.12);">💸</div>
          <div class="report-name">Expense Report</div>
          <div class="report-desc">Business expenses and profit & loss</div>
          <button class="report-btn" onclick="showToast('Generating Expense Report...')">📄 Download PDF</button>
        </div>
        <div class="report-card">
          <div class="report-icon" style="background:rgba(6,182,212,0.12);">👤</div>
          <div class="report-name">Employee Report</div>
          <div class="report-desc">Attendance, performance and payroll</div>
          <button class="report-btn" onclick="showToast('Generating Employee Report...')">📄 Download PDF</button>
        </div>
      </div>

      <div class="card">
        <div class="card-header"><div class="card-title">Annual Performance</div><div class="card-subtitle">2024 full year overview</div></div>
        <div style="position:relative;height:240px;">
          <canvas id="annualChart" role="img" aria-label="Annual performance comparison chart"></canvas>
        </div>
      </div>
    </div>

    <!-- ── PAGE: NOTIFICATIONS ── -->
    <div class="page" id="page-notifications">
      <div class="breadcrumbs"><a href="#" onclick="navTo('page-dashboard',null);return false;">Home</a><span class="sep">/</span><span class="current">Notifications</span></div>
      <div class="page-header">
        <h1>Notifications</h1>
        <button style="padding:8px 16px;border-radius:10px;border:1px solid var(--border);background:transparent;color:var(--text-secondary);cursor:pointer;font-size:13px;" onclick="markAllRead()">Mark all read</button>
      </div>
      <div class="notif-list" id="notif-list"></div>
    </div>

    <!-- ── PAGE: SETTINGS ── -->
    <div class="page" id="page-settings">
      <div class="breadcrumbs"><a href="#" onclick="navTo('page-dashboard',null);return false;">Home</a><span class="sep">/</span><span class="current">Settings</span></div>
      <div class="page-header"><h1>Settings</h1></div>
      <div class="settings-grid">
        <div class="setting-group">
          <div class="setting-title">👤 Profile Settings</div>
          <div style="margin-bottom:14px;">
            <div style="display:flex;align-items:center;gap:14px;margin-bottom:16px;">
              <div class="user-avatar" style="width:56px;height:56px;font-size:20px;" id="settings-avatar">A</div>
              <div>
                <div style="font-weight:600;" id="settings-name">Admin User</div>
                <div style="font-size:12px;color:var(--text-secondary);" id="settings-email">admin@bizpulse.com</div>
              </div>
            </div>
            <div class="form-group"><label class="form-label">Display Name</label><input class="form-input" id="profile-name" placeholder="Your Name"></div>
            <div class="form-group"><label class="form-label">Email</label><input class="form-input" id="profile-email" placeholder="email@company.com"></div>
            <button class="btn-primary" onclick="showToast('Profile updated!')">Save Changes</button>
          </div>
        </div>
        <div class="setting-group">
          <div class="setting-title">🎨 Appearance</div>
          <div class="setting-row">
            <div><div class="setting-name">Dark Mode</div><div class="setting-desc">Switch between dark and light theme</div></div>
            <div class="toggle on" id="theme-toggle" onclick="toggleTheme()"></div>
          </div>
          <div class="setting-row">
            <div><div class="setting-name">Compact View</div><div class="setting-desc">Reduce spacing in tables</div></div>
            <div class="toggle" onclick="this.classList.toggle('on')"></div>
          </div>
          <div class="setting-row">
            <div><div class="setting-name">Animations</div><div class="setting-desc">Enable UI animations</div></div>
            <div class="toggle on" onclick="this.classList.toggle('on')"></div>
          </div>
        </div>
        <div class="setting-group">
          <div class="setting-title">🔔 Notifications</div>
          <div class="setting-row">
            <div><div class="setting-name">Browser Push Notifications</div><div class="setting-desc">Get desktop alerts even when this tab is in the background</div></div>
            <div class="toggle" id="push-toggle" onclick="togglePushSetting(this)"></div>
          </div>
          <div class="setting-row">
            <div><div class="setting-name">New Orders</div><div class="setting-desc">Get notified on new orders</div></div>
            <div class="toggle on" onclick="this.classList.toggle('on')"></div>
          </div>
          <div class="setting-row">
            <div><div class="setting-name">Low Stock Alerts</div><div class="setting-desc">Warn when inventory is low</div></div>
            <div class="toggle on" onclick="this.classList.toggle('on')"></div>
          </div>
          <div class="setting-row">
            <div><div class="setting-name">Payment Reminders</div><div class="setting-desc">Alert on pending payments</div></div>
            <div class="toggle" onclick="this.classList.toggle('on')"></div>
          </div>
          <div class="setting-row">
            <div><div class="setting-name">Weekly Summary</div><div class="setting-desc">Email summary every Monday</div></div>
            <div class="toggle on" onclick="this.classList.toggle('on')"></div>
          </div>
        </div>
        <div class="setting-group">
          <div class="setting-title">🔐 Security</div>
          <div class="form-group"><label class="form-label">Current Password</label><input type="password" class="form-input" placeholder="••••••••"></div>
          <div class="form-group"><label class="form-label">New Password</label><input type="password" class="form-input" placeholder="••••••••"></div>
          <div class="form-group"><label class="form-label">Confirm Password</label><input type="password" class="form-input" placeholder="••••••••"></div>
          <button class="btn-primary" onclick="showToast('Password changed!')">Update Password</button>
        </div>
      </div>
    </div>

    <!-- FOOTER -->
    <div class="site-footer" style="padding:0 28px 28px;">
      <div>© 2026 BizPulse Inc. · <a class="footer-link" href="#" onclick="navTo('page-settings',null);return false;">Settings</a></div>
      <div class="footer-links">
        <a class="footer-link" href="#" onclick="showToast('Opening privacy policy...');return false;">Privacy Policy</a>
        <a class="footer-link" href="#" onclick="showToast('Opening terms of service...');return false;">Terms of Service</a>
        <a class="footer-link" href="https://docs.claude.com" target="_blank" rel="noopener">Documentation ↗</a>
        <a class="footer-link" href="#" onclick="showToast('Opening support center...');return false;">Contact Support</a>
      </div>
    </div>

  </div><!-- /main-content -->
</div><!-- /dashboard -->

<!-- ─── CHATBOT WIDGET ──────────────────────── -->
<button class="chat-launcher" id="chat-launcher" onclick="toggleChat()" title="Chat with BizPulse Assistant">
  💬
  <span class="cl-badge" id="chat-badge">1</span>
</button>

<div class="chat-window" id="chat-window">
  <div class="chat-head">
    <div class="ch-avatar">🤖</div>
    <div>
      <div class="ch-name">BizPulse Assistant</div>
      <div class="ch-status"><span class="live-dot"></span> Online</div>
    </div>
    <button class="ch-close" onclick="toggleChat()">✕</button>
  </div>
  <div class="chat-body" id="chat-body"></div>
  <div class="chat-quick" id="chat-quick">
    <button onclick="askBot('How are sales today?')">Today's sales</button>
    <button onclick="askBot('Show low stock items')">Low stock</button>
    <button onclick="askBot('Top customer')">Top customer</button>
    <button onclick="askBot('Help')">Help</button>
  </div>
  <div class="chat-input-row">
    <input type="text" id="chat-input" placeholder="Ask about sales, stock, customers..." onkeydown="if(event.key==='Enter')sendChat()">
    <button onclick="sendChat()">➤</button>
  </div>
</div>

<!-- Toast -->
<div class="toast" id="toast"></div>

<!-- ─── MODALS ──────────────────────────── -->
<div class="modal-overlay" id="modal-add-sale" onclick="closeModal(event,'modal-add-sale')">
  <div class="modal">
    <div class="modal-title">➕ Record New Sale</div>
    <div class="form-group"><label class="form-label">Customer Name</label><input class="form-input" placeholder="Customer name"></div>
    <div class="form-group"><label class="form-label">Product</label><input class="form-input" placeholder="Product name"></div>
    <div class="form-row">
      <div class="form-group"><label class="form-label">Quantity</label><input type="number" class="form-input" placeholder="1"></div>
      <div class="form-group"><label class="form-label">Amount (₨)</label><input type="number" class="form-input" placeholder="5000"></div>
    </div>
    <div class="modal-actions">
      <button class="btn-cancel" onclick="document.getElementById('modal-add-sale').classList.remove('open')">Cancel</button>
      <button class="btn-submit" onclick="addSaleRecord()">Save Sale</button>
    </div>
  </div>
</div>

<div class="modal-overlay" id="modal-add-product" onclick="closeModal(event,'modal-add-product')">
  <div class="modal">
    <div class="modal-title">📦 Add Product</div>
    <div class="form-group"><label class="form-label">Product Name</label><input class="form-input" id="new-prod-name" placeholder="Product name"></div>
    <div class="form-row">
      <div class="form-group"><label class="form-label">Category</label><select class="form-input"><option>Electronics</option><option>Clothing</option><option>Food</option><option>Other</option></select></div>
      <div class="form-group"><label class="form-label">Stock Qty</label><input type="number" class="form-input" id="new-prod-qty" placeholder="100"></div>
    </div>
    <div class="modal-actions">
      <button class="btn-cancel" onclick="document.getElementById('modal-add-product').classList.remove('open')">Cancel</button>
      <button class="btn-submit" onclick="addProduct()">Add Product</button>
    </div>
  </div>
</div>

<div class="modal-overlay" id="modal-add-customer" onclick="closeModal(event,'modal-add-customer')">
  <div class="modal">
    <div class="modal-title">👥 Add Customer</div>
    <div class="form-row">
      <div class="form-group"><label class="form-label">Name</label><input class="form-input" id="new-cust-name" placeholder="Customer name"></div>
      <div class="form-group"><label class="form-label">Email</label><input class="form-input" id="new-cust-email" placeholder="email@co.com"></div>
    </div>
    <div class="form-group"><label class="form-label">Phone</label><input class="form-input" placeholder="+92 300 0000000"></div>
    <div class="modal-actions">
      <button class="btn-cancel" onclick="document.getElementById('modal-add-customer').classList.remove('open')">Cancel</button>
      <button class="btn-submit" onclick="addCustomer()">Add Customer</button>
    </div>
  </div>
</div>

<div class="modal-overlay" id="modal-add-employee" onclick="closeModal(event,'modal-add-employee')">
  <div class="modal">
    <div class="modal-title">👤 Add Employee</div>
    <div class="form-row">
      <div class="form-group"><label class="form-label">Name</label><input class="form-input" id="new-emp-name" placeholder="Employee name"></div>
      <div class="form-group"><label class="form-label">Role</label><input class="form-input" id="new-emp-role" placeholder="Job title"></div>
    </div>
    <div class="form-row">
      <div class="form-group"><label class="form-label">Department</label><select class="form-input"><option>Sales</option><option>IT</option><option>HR</option><option>Finance</option></select></div>
      <div class="form-group"><label class="form-label">Salary (₨)</label><input type="number" class="form-input" placeholder="50000"></div>
    </div>
    <div class="modal-actions">
      <button class="btn-cancel" onclick="document.getElementById('modal-add-employee').classList.remove('open')">Cancel</button>
      <button class="btn-submit" onclick="addEmployee()">Add Employee</button>
    </div>
  </div>
</div>

<script>
// ── STATE ──
const ACCOUNTS = [
  {email:'admin@bizpulse.com', pass:'admin123', role:'admin', name:'Admin User'},
  {email:'manager@bizpulse.com', pass:'mgr123', role:'manager', name:'Sara Manager'},
  {email:'emp@bizpulse.com', pass:'emp123', role:'employee', name:'Ali Employee'},
];
let signedUpAccounts = [];
let currentUser = null;
let isLight = false;

// ── AUTH ──
function showScreen(id) {
  document.querySelectorAll('.auth-screen').forEach(s=>s.classList.remove('active'));
  const el = document.getElementById(id);
  if(el) el.classList.add('active');
}
function fillDemo(email,pass,role) {
  document.getElementById('si-email').value = email;
  document.getElementById('si-pass').value = pass;
}
function doSignIn() {
  const email = document.getElementById('si-email').value.trim();
  const pass = document.getElementById('si-pass').value;
  const all = [...ACCOUNTS, ...signedUpAccounts];
  const user = all.find(u => u.email===email && u.pass===pass);
  if(!user) { showToast('Invalid email or password','error'); return; }
  currentUser = user;
  launchDashboard();
}
function doSignUp() {
  const fname = document.getElementById('su-fname').value.trim();
  const lname = document.getElementById('su-lname').value.trim();
  const email = document.getElementById('su-email').value.trim();
  const biz   = document.getElementById('su-biz').value.trim();
  const role  = document.getElementById('su-role').value;
  const pass  = document.getElementById('su-pass').value;
  const pass2 = document.getElementById('su-pass2').value;
  if(!fname||!lname||!email||!pass) { showToast('Please fill all fields','error'); return; }
  if(pass!==pass2) { showToast('Passwords do not match','error'); return; }
  if(pass.length<6) { showToast('Password must be 6+ characters','error'); return; }
  const exists = [...ACCOUNTS,...signedUpAccounts].find(u=>u.email===email);
  if(exists) { showToast('Account already exists','error'); return; }
  const newUser = {email, pass, role, name:`${fname} ${lname}`, biz};
  signedUpAccounts.push(newUser);
  currentUser = newUser;
  showToast('Account created! Welcome 🎉');
  setTimeout(launchDashboard, 800);
}
function doLogout() {
  currentUser = null;
  document.getElementById('dashboard').classList.remove('active');
  document.getElementById('dashboard').style.display = 'none';
  showScreen('screen-signin');
  document.getElementById('si-email').value = '';
  document.getElementById('si-pass').value = '';
}
function launchDashboard() {
  document.querySelectorAll('.auth-screen').forEach(s=>s.classList.remove('active'));
  const db = document.getElementById('dashboard');
  db.style.display = 'flex';
  db.classList.add('active');
  // Set user info
  const initials = currentUser.name.split(' ').map(n=>n[0]).join('').toUpperCase().slice(0,2);
  document.getElementById('sidebar-avatar').textContent = initials;
  document.getElementById('sidebar-name').textContent = currentUser.name;
  document.getElementById('sidebar-role').textContent = currentUser.role;
  document.getElementById('settings-avatar').textContent = initials;
  document.getElementById('settings-name').textContent = currentUser.name;
  document.getElementById('settings-email').textContent = currentUser.email;
  document.getElementById('profile-name').value = currentUser.name;
  document.getElementById('profile-email').value = currentUser.email;
  document.getElementById('menu-avatar').textContent = initials;
  document.getElementById('menu-name').textContent = currentUser.name;
  document.getElementById('menu-email').textContent = currentUser.email;
  // Greeting
  const h = new Date().getHours();
  const g = h<12?'Morning':h<17?'Afternoon':'Evening';
  document.getElementById('hero-greeting').textContent = `Good ${g}, ${currentUser.name.split(' ')[0]}!`;
  initCharts();
  renderAll();
  startClock();
  updateBellBadge();
  initChat();
  if('Notification' in window && Notification.permission === 'granted') {
    dismissPushBanner();
    startPushSimulation();
  } else if('Notification' in window && Notification.permission === 'denied') {
    dismissPushBanner();
  }
}

// ── NAVIGATION ──
function navTo(pageId, navEl) {
  document.querySelectorAll('.page').forEach(p=>p.classList.remove('active'));
  document.querySelectorAll('.nav-item').forEach(n=>n.classList.remove('active'));
  const page = document.getElementById(pageId);
  if(page) page.classList.add('active');
  if(navEl) navEl.classList.add('active');
  const titles = {
    'page-dashboard':'Dashboard Overview','page-sales':'Sales Management',
    'page-inventory':'Inventory','page-customers':'Customers',
    'page-employees':'Employees','page-reports':'Reports & Analytics',
    'page-notifications':'Notifications','page-settings':'Settings'
  };
  document.getElementById('page-title').textContent = titles[pageId]||'Dashboard';
}

// ── THEME ──
function toggleTheme() {
  isLight = !isLight;
  document.body.classList.toggle('light', isLight);
  const btn = document.querySelector('.topbar-btn:first-of-type');
  document.querySelectorAll('.topbar-btn')[0].textContent = isLight ? '🌙' : '☀️';
  const tog = document.getElementById('theme-toggle');
  tog.classList.toggle('on', !isLight);
}

// ── DROPDOWNS (notifications bell + profile menu) ──
function toggleDropdown(id) {
  const panel = document.getElementById(id);
  const wasOpen = panel.classList.contains('open');
  closeAllDropdowns();
  if(!wasOpen) {
    panel.classList.add('open');
    if(id === 'notif-dropdown') renderNotifDropdown();
  }
}
function closeAllDropdowns() {
  document.querySelectorAll('.dropdown-panel').forEach(p=>p.classList.remove('open'));
}
document.addEventListener('click', e => {
  if(!e.target.closest('.dropdown-wrap')) closeAllDropdowns();
});
function renderNotifDropdown() {
  const list = document.getElementById('notif-dropdown-list');
  const unread = notifData.filter(n=>n.unread);
  if(notifData.length === 0) {
    list.innerHTML = '<div class="dropdown-empty">No notifications yet</div>';
    return;
  }
  list.innerHTML = notifData.slice(0,6).map(n=>`
    <div class="dropdown-item" onclick="closeAllDropdowns();navTo('page-notifications',null);">
      <div class="di-icon" style="background:${n.bg};">${n.icon}</div>
      <div>
        <div class="di-title">${n.title}</div>
        <div class="di-desc">${n.desc}</div>
        <div class="di-time">${n.time}</div>
      </div>
    </div>`).join('');
}
function markAllRead() {
  notifData.forEach(n=>n.unread=false);
  renderNotifications();
  renderNotifDropdown();
  updateBellBadge();
  showToast('All notifications marked as read');
}
function updateBellBadge() {
  const hasUnread = notifData.some(n=>n.unread);
  const dot = document.getElementById('bell-dot');
  if(dot) dot.style.display = hasUnread ? 'block' : 'none';
}

// ── SIDEBAR COLLAPSE ──
function toggleCollapse() {
  const sb = document.getElementById('sidebar');
  sb.classList.toggle('collapsed');
  document.getElementById('collapse-btn').textContent = sb.classList.contains('collapsed') ? '»' : '«';
}

// ── LIVE CLOCK ──
function startClock() {
  function tick() {
    const el = document.getElementById('clock-text');
    if(el) el.textContent = new Date().toLocaleTimeString([], {hour:'2-digit',minute:'2-digit',second:'2-digit'});
  }
  tick();
  setInterval(tick, 1000);
}

// ── PUSH NOTIFICATIONS ──
function requestPushPermission() {
  if(!('Notification' in window)) {
    showToast('Push notifications are not supported in this browser','error');
    return;
  }
  Notification.requestPermission().then(perm => {
    if(perm === 'granted') {
      showToast('Push notifications enabled! 🎉');
      dismissPushBanner();
      const tog = document.getElementById('push-toggle');
      if(tog) tog.classList.add('on');
      sendPushNotification('BizPulse Notifications Enabled', 'You will now receive alerts for new orders, low stock, and payments.');
      startPushSimulation();
    } else if(perm === 'denied') {
      showToast('Push notifications blocked — enable them in your browser settings','error');
      dismissPushBanner();
    }
  });
}
function dismissPushBanner() {
  const b = document.getElementById('push-banner');
  if(b) b.style.display = 'none';
}
function togglePushSetting(el) {
  if(el.classList.contains('on')) {
    el.classList.remove('on');
    showToast('Browser push notifications turned off');
  } else {
    requestPushPermission();
  }
}
function sendPushNotification(title, body) {
  if('Notification' in window && Notification.permission === 'granted') {
    try {
      new Notification(title, { body, icon: '' });
    } catch(e) { /* some sandboxed environments block this; toast still informs the user */ }
  }
  showToast(title);
}
let pushSimInterval = null;
function startPushSimulation() {
  if(pushSimInterval) return;
  const events = [
    {title:'New order received', body:'Order #ORD-0894 just came in from a customer.'},
    {title:'Low stock warning', body:'Office Chair inventory has dropped below 5 units.'},
    {title:'Payment received', body:'A payment of ₨ 18,000 has been confirmed.'},
  ];
  let i = 0;
  pushSimInterval = setInterval(()=>{
    const ev = events[i % events.length];
    i++;
    sendPushNotification(ev.title, ev.body);
  }, 45000);
}

// ── TOAST ──
function showToast(msg, type='success') {
  const t = document.getElementById('toast');
  t.textContent = (type==='error'?'❌ ':type==='success'?'✅ ':'')+msg;
  t.className = 'toast show'+(type==='error'?' error':'');
  clearTimeout(t._timer);
  t._timer = setTimeout(()=>t.classList.remove('show'), 3000);
}

// ── MODAL ──
function openModal(id) { document.getElementById(id).classList.add('open'); }
function closeModal(e, id) { if(e.target===e.currentTarget) document.getElementById(id).classList.remove('open'); }

// ── DATA ──
const salesData = [
  {id:'#ORD-0892',date:'Jun 25',cust:'Ayesha K.',product:'Samsung TV',qty:1,amt:'₨ 95,000',status:'paid'},
  {id:'#ORD-0891',date:'Jun 25',cust:'Bilal R.',product:'Nike Shoes',qty:2,amt:'₨ 12,000',status:'pending'},
  {id:'#ORD-0890',date:'Jun 24',cust:'Sana M.',product:'Office Chair',qty:3,amt:'₨ 36,000',status:'paid'},
  {id:'#ORD-0889',date:'Jun 24',cust:'Hamza A.',product:'Laptop Bag',qty:1,amt:'₨ 4,500',status:'cancelled'},
  {id:'#ORD-0888',date:'Jun 23',cust:'Nadia F.',product:'Headphones',qty:2,amt:'₨ 18,000',status:'paid'},
  {id:'#ORD-0887',date:'Jun 23',cust:'Usman T.',product:'Keyboard',qty:1,amt:'₨ 6,000',status:'paid'},
  {id:'#ORD-0886',date:'Jun 22',cust:'Zara H.',product:'Mouse',qty:4,amt:'₨ 8,000',status:'pending'},
];
const inventoryData = [
  {name:'Samsung TV 55"',cat:'Electronics',qty:24,low:false,color:'#3b82f6'},
  {name:'Nike Air Max',cat:'Clothing',qty:87,low:false,color:'#8b5cf6'},
  {name:'Iphone 15 Pro',cat:'Electronics',qty:8,low:true,color:'#3b82f6'},
  {name:'Office Chair',cat:'Furniture',qty:5,low:true,color:'#f59e0b'},
  {name:'Basmati Rice 5kg',cat:'Food',qty:312,low:false,color:'#10b981'},
  {name:'Laptop Bag',cat:'Accessories',qty:4,low:true,color:'#ef4444'},
  {name:'HP Laptop',cat:'Electronics',qty:19,low:false,color:'#3b82f6'},
  {name:'Levi Jeans',cat:'Clothing',qty:56,low:false,color:'#8b5cf6'},
];
const customersData = [
  {name:'Ayesha Khan',email:'ayesha@mail.com',orders:24,spent:'₨ 180K',loyalty:'platinum',color:'#8b5cf6'},
  {name:'Bilal Raza',email:'bilal@mail.com',orders:12,spent:'₨ 64K',loyalty:'gold',color:'#3b82f6'},
  {name:'Sana Malik',email:'sana@mail.com',orders:8,spent:'₨ 42K',loyalty:'silver',color:'#10b981'},
  {name:'Hamza Ahmed',email:'hamza@mail.com',orders:31,spent:'₨ 230K',loyalty:'platinum',color:'#f59e0b'},
  {name:'Nadia Fatima',email:'nadia@mail.com',orders:6,spent:'₨ 28K',loyalty:'silver',color:'#ef4444'},
  {name:'Usman Tariq',email:'usman@mail.com',orders:18,spent:'₨ 95K',loyalty:'gold',color:'#06b6d4'},
];
const employeesData = [
  {name:'Ahsan Ali',role:'Sales Manager',dept:'Sales',attendance:'96%',tasks:12,salary:'₨ 85K',perf:88,color:'#3b82f6'},
  {name:'Fatima Noor',role:'HR Officer',dept:'HR',attendance:'98%',tasks:8,salary:'₨ 65K',perf:94,color:'#8b5cf6'},
  {name:'Zain Khan',role:'Developer',dept:'IT',attendance:'92%',tasks:20,salary:'₨ 120K',perf:96,color:'#10b981'},
  {name:'Mehwish R.',role:'Accountant',dept:'Finance',attendance:'94%',tasks:15,salary:'₨ 70K',perf:82,color:'#f59e0b'},
  {name:'Omar Sheikh',role:'Sales Rep',dept:'Sales',attendance:'90%',tasks:9,salary:'₨ 45K',perf:76,color:'#06b6d4'},
  {name:'Hira Baig',role:'Graphic Designer',dept:'Marketing',attendance:'97%',tasks:11,salary:'₨ 55K',perf:91,color:'#ef4444'},
];
const notifData = [
  {icon:'⚠️',bg:'rgba(245,158,11,0.12)',title:'Low Stock Alert',desc:'Samsung TV stock is below 10 units',time:'5 min ago',unread:true},
  {icon:'🛒',bg:'rgba(59,130,246,0.12)',title:'New Order Received',desc:'Order #ORD-0893 from Tariq Hussain',time:'12 min ago',unread:true},
  {icon:'💰',bg:'rgba(16,185,129,0.12)',title:'Payment Received',desc:'₨ 95,000 from Order #ORD-0892',time:'1 hr ago',unread:true},
  {icon:'📊',bg:'rgba(139,92,246,0.12)',title:'Monthly Report Ready',desc:'June 2025 report is now available',time:'3 hrs ago',unread:false},
  {icon:'👤',bg:'rgba(6,182,212,0.12)',title:'New Customer Signup',desc:'Tariq Hussain created an account',time:'4 hrs ago',unread:false},
  {icon:'❌',bg:'rgba(239,68,68,0.12)',title:'Order Cancelled',desc:'Order #ORD-0889 was cancelled',time:'5 hrs ago',unread:false},
  {icon:'📦',bg:'rgba(245,158,11,0.12)',title:'Shipment Delayed',desc:'3 orders may be delayed due to weather',time:'1 day ago',unread:false},
];

// ── RENDER ──
function renderAll() {
  renderRecentOrders();
  renderTopProducts();
  renderSalesTable();
  renderStockBars();
  renderInventory();
  renderCustomers();
  renderEmployees();
  renderNotifications();
}
function renderRecentOrders() {
  const tb = document.getElementById('recent-orders-body');
  tb.innerHTML = salesData.slice(0,5).map(s=>`
    <tr>
      <td><a class="table-link" href="#" onclick="showToast('Opening ${s.id}...');return false;" style="font-weight:600;">${s.id}</a></td>
      <td>${s.cust}</td>
      <td>${s.product}</td>
      <td style="font-weight:600;">${s.amt}</td>
      <td><span class="status-badge status-${s.status}">${s.status.charAt(0).toUpperCase()+s.status.slice(1)}</span></td>
    </tr>`).join('');
}
function renderTopProducts() {
  const products = [
    {name:'Samsung TV',sales:95,color:'#3b82f6'},
    {name:'Nike Air Max',sales:78,color:'#8b5cf6'},
    {name:'HP Laptop',sales:62,color:'#10b981'},
    {name:'Office Chair',sales:45,color:'#f59e0b'},
    {name:'Headphones',sales:38,color:'#06b6d4'},
  ];
  document.getElementById('top-products-list').innerHTML = products.map(p=>`
    <div class="progress-bar-wrap">
      <div class="progress-header">
        <div class="progress-name" style="display:flex;align-items:center;gap:6px;">
          <div class="product-dot" style="background:${p.color}"></div>${p.name}
        </div>
        <div class="progress-val">${p.sales} sold</div>
      </div>
      <div class="progress-track"><div class="progress-fill" style="width:${p.sales}%;background:${p.color};"></div></div>
    </div>`).join('');
}
function renderSalesTable() {
  document.getElementById('sales-table-body').innerHTML = salesData.map((s,i)=>`
    <tr>
      <td><span class="row-check" onclick="this.classList.toggle('checked')"></span></td>
      <td><a class="table-link" href="#" onclick="showToast('Opening ${s.id}...');return false;" style="font-weight:600;">${s.id}</a></td>
      <td style="color:var(--text-secondary);">${s.date}</td>
      <td>${s.cust}</td>
      <td>${s.product}</td>
      <td style="text-align:center;">${s.qty}</td>
      <td style="font-weight:600;">${s.amt}</td>
      <td><span class="status-badge status-${s.status}">${s.status.charAt(0).toUpperCase()+s.status.slice(1)}</span></td>
    </tr>`).join('');
}
function renderStockBars() {
  const cats = [{name:'Electronics',val:65,color:'#3b82f6'},{name:'Clothing',val:80,color:'#8b5cf6'},{name:'Food',val:92,color:'#10b981'},{name:'Furniture',val:20,color:'#f59e0b'},{name:'Accessories',val:35,color:'#06b6d4'}];
  document.getElementById('stock-bars').innerHTML = cats.map(c=>`
    <div class="progress-bar-wrap">
      <div class="progress-header">
        <div class="progress-name">${c.name}</div>
        <div class="progress-val">${c.val}% stocked</div>
      </div>
      <div class="progress-track"><div class="progress-fill" style="width:${c.val}%;background:${c.color};"></div></div>
    </div>`).join('');
}
function renderInventory() {
  document.getElementById('inventory-grid').innerHTML = inventoryData.map(p=>`
    <div class="inv-card">
      <div class="inv-tag">${p.cat}</div>
      <div class="inv-name">${p.name}</div>
      <div class="inv-stock" style="color:${p.low?'var(--accent-amber)':'var(--text-primary)'};">${p.qty}</div>
      <div class="inv-footer">
        <div>
          ${p.low ? '<span class="inv-alert">⚠️ Low Stock</span>' : '<span class="inv-ok">✅ In Stock</span>'}
        </div>
        <div style="display:flex;gap:6px;">
          <button onclick="showToast('Edit mode')" style="padding:4px 8px;border-radius:6px;border:1px solid var(--border);background:transparent;color:var(--text-secondary);cursor:pointer;font-size:11px;">Edit</button>
        </div>
      </div>
    </div>`).join('');
}
function renderCustomers() {
  const badges = {platinum:'loyalty-platinum',gold:'loyalty-gold',silver:'loyalty-silver'};
  document.getElementById('customers-grid').innerHTML = customersData.map(c=>`
    <div class="customer-card">
      <div class="cust-header">
        <div class="cust-avatar" style="background:${c.color};">${c.name.split(' ').map(n=>n[0]).join('')}</div>
        <div>
          <div class="cust-name">${c.name}</div>
          <div class="cust-email"><a class="link" href="mailto:${c.email}" style="color:var(--text-secondary);">${c.email}</a></div>
        </div>
      </div>
      <div class="cust-stats">
        <div class="cust-stat"><div class="cust-stat-val">${c.orders}</div><div class="cust-stat-lbl">Orders</div></div>
        <div class="cust-stat"><div class="cust-stat-val" style="font-size:13px;">${c.spent}</div><div class="cust-stat-lbl">Spent</div></div>
      </div>
      <span class="loyalty-badge ${badges[c.loyalty]}">⭐ ${c.loyalty.charAt(0).toUpperCase()+c.loyalty.slice(1)} Member</span>
    </div>`).join('');
}
function renderEmployees() {
  document.getElementById('employees-grid').innerHTML = employeesData.map(e=>`
    <div class="emp-card">
      <div class="emp-avatar" style="background:${e.color};">${e.name.split(' ').map(n=>n[0]).join('').slice(0,2)}</div>
      <div class="emp-name">${e.name}</div>
      <div class="emp-role">${e.role} · ${e.dept}</div>
      <div class="emp-stats">
        <div class="emp-stat"><div class="emp-stat-label">Attendance</div><div class="emp-stat-val" style="color:var(--accent-green);">${e.attendance}</div></div>
        <div class="emp-stat"><div class="emp-stat-label">Tasks</div><div class="emp-stat-val">${e.tasks}</div></div>
        <div class="emp-stat"><div class="emp-stat-label">Salary</div><div class="emp-stat-val" style="font-size:13px;">${e.salary}</div></div>
        <div class="emp-stat"><div class="emp-stat-label">Score</div><div class="emp-stat-val" style="color:${e.perf>=90?'var(--accent-green)':e.perf>=75?'var(--accent-amber)':'var(--accent-red)'};">${e.perf}%</div></div>
      </div>
      <div class="emp-perf">
        <div class="perf-label">Performance</div>
        <div class="perf-track"><div class="perf-fill" style="width:${e.perf}%;"></div></div>
      </div>
    </div>`).join('');
}
function renderNotifications() {
  document.getElementById('notif-list').innerHTML = notifData.map(n=>`
    <div class="notif-item ${n.unread?'unread':''}" onclick="this.classList.remove('unread')">
      <div class="notif-icon" style="background:${n.bg};">${n.icon}</div>
      <div class="notif-body">
        <div class="notif-title">${n.title} ${n.unread?'<span style="width:6px;height:6px;border-radius:50%;background:var(--accent-blue);display:inline-block;margin-left:4px;vertical-align:middle;"></span>':''}</div>
        <div class="notif-desc">${n.desc}</div>
        <div class="notif-time">${n.time}</div>
      </div>
    </div>`).join('');
}

// ── ADD RECORDS ──
function addSaleRecord() {
  document.getElementById('modal-add-sale').classList.remove('open');
  showToast('Sale recorded successfully!');
}
function addProduct() {
  const name = document.getElementById('new-prod-name').value;
  const qty = parseInt(document.getElementById('new-prod-qty').value)||0;
  if(!name) { showToast('Enter product name','error'); return; }
  inventoryData.unshift({name,cat:'General',qty,low:qty<10,color:'#3b82f6'});
  renderInventory();
  document.getElementById('modal-add-product').classList.remove('open');
  showToast(`${name} added!`);
}
function addCustomer() {
  const name = document.getElementById('new-cust-name').value;
  const email = document.getElementById('new-cust-email').value;
  if(!name) { showToast('Enter customer name','error'); return; }
  const colors = ['#3b82f6','#8b5cf6','#10b981','#f59e0b','#06b6d4'];
  customersData.unshift({name,email,orders:0,spent:'₨ 0',loyalty:'silver',color:colors[Math.floor(Math.random()*colors.length)]});
  renderCustomers();
  document.getElementById('modal-add-customer').classList.remove('open');
  showToast(`${name} added!`);
}
function addEmployee() {
  const name = document.getElementById('new-emp-name').value;
  const role = document.getElementById('new-emp-role').value;
  if(!name) { showToast('Enter name','error'); return; }
  const colors = ['#3b82f6','#8b5cf6','#10b981','#f59e0b','#06b6d4'];
  employeesData.unshift({name,role:role||'Staff',dept:'General',attendance:'--',tasks:0,salary:'₨ 0',perf:75,color:colors[Math.floor(Math.random()*5)]});
  renderEmployees();
  document.getElementById('modal-add-employee').classList.remove('open');
  showToast(`${name} added!`);
}

// ── CHARTS ──
let chartsInited = false;
function initCharts() {
  if(chartsInited) return;
  chartsInited = true;

  const isDark = !isLight;
  const gridColor = isDark?'rgba(255,255,255,0.05)':'rgba(0,0,0,0.05)';
  const tickColor = isDark?'#475569':'#94a3b8';

  // Revenue Chart
  new Chart(document.getElementById('revenueChart'),{
    type:'bar',
    data:{
      labels:['Jan','Feb','Mar','Apr','May','Jun','Jul','Aug','Sep','Oct','Nov','Dec'],
      datasets:[{
        label:'Revenue',
        data:[2.8,3.2,2.9,3.8,4.1,4.2,3.6,4.5,4.8,5.2,4.9,5.8],
        backgroundColor:'rgba(59,130,246,0.7)',
        borderRadius:6,
        borderSkipped:false,
      },{
        label:'Expenses',
        data:[1.8,2.0,1.9,2.2,2.5,2.6,2.3,2.8,3.0,3.2,3.0,3.5],
        backgroundColor:'rgba(139,92,246,0.5)',
        borderRadius:6,
        borderSkipped:false,
      }]
    },
    options:{
      responsive:true, maintainAspectRatio:false,
      plugins:{legend:{display:false}},
      scales:{
        x:{grid:{color:gridColor},ticks:{color:tickColor,font:{size:11}},border:{display:false}},
        y:{grid:{color:gridColor},ticks:{color:tickColor,font:{size:11},callback:v=>v+'M'},border:{display:false}}
      }
    }
  });

  // Category donut
  new Chart(document.getElementById('categoryChart'),{
    type:'doughnut',
    data:{
      labels:['Electronics','Clothing','Food','Other'],
      datasets:[{
        data:[38,24,22,16],
        backgroundColor:['#3b82f6','#8b5cf6','#10b981','#f59e0b'],
        borderWidth:0, hoverOffset:8,
      }]
    },
    options:{
      responsive:true, maintainAspectRatio:false, cutout:'72%',
      plugins:{legend:{display:false}}
    }
  });

  // Sales trend
  new Chart(document.getElementById('salesTrendChart'),{
    type:'line',
    data:{
      labels:['Mon','Tue','Wed','Thu','Fri','Sat','Sun'],
      datasets:[{
        label:'This Week',
        data:[120,185,142,210,168,230,195],
        borderColor:'#3b82f6', backgroundColor:'rgba(59,130,246,0.1)',
        tension:0.4, fill:true, pointRadius:4, pointBackgroundColor:'#3b82f6',
      },{
        label:'Last Week',
        data:[100,165,130,190,150,210,175],
        borderColor:'#8b5cf6', backgroundColor:'transparent',
        tension:0.4, borderDash:[5,5], pointRadius:3, pointBackgroundColor:'#8b5cf6',
      }]
    },
    options:{
      responsive:true, maintainAspectRatio:false,
      plugins:{legend:{display:false}},
      scales:{
        x:{grid:{color:gridColor},ticks:{color:tickColor,font:{size:11}},border:{display:false}},
        y:{grid:{color:gridColor},ticks:{color:tickColor,font:{size:11},callback:v=>'₨'+v+'K'},border:{display:false}}
      }
    }
  });

  // Annual chart
  new Chart(document.getElementById('annualChart'),{
    type:'bar',
    data:{
      labels:['Q1','Q2','Q3','Q4'],
      datasets:[
        {label:'Revenue',data:[8.9,12.1,14.3,16.8],backgroundColor:'rgba(59,130,246,0.75)',borderRadius:6,borderSkipped:false},
        {label:'Profit',data:[3.2,4.8,6.1,7.4],backgroundColor:'rgba(16,185,129,0.75)',borderRadius:6,borderSkipped:false},
        {label:'Expenses',data:[5.7,7.3,8.2,9.4],backgroundColor:'rgba(245,158,11,0.75)',borderRadius:6,borderSkipped:false},
      ]
    },
    options:{
      responsive:true, maintainAspectRatio:false,
      plugins:{legend:{display:false}},
      scales:{
        x:{grid:{color:gridColor},ticks:{color:tickColor},border:{display:false}},
        y:{grid:{color:gridColor},ticks:{color:tickColor,callback:v=>v+'M'},border:{display:false}}
      }
    }
  });
}

// ── RESPONSIVE SIDEBAR TOGGLE ──
function toggleSidebar() {
  document.getElementById('sidebar').classList.toggle('open');
}
// Close sidebar on outside click on mobile
document.addEventListener('click',e=>{
  if(window.innerWidth<768){
    const sb = document.getElementById('sidebar');
    if(!sb.contains(e.target)) sb.classList.remove('open');
  }
});

// ── INIT ──
// Set light mode default to off (dark)
document.getElementById('theme-toggle').classList.add('on');

// ── CHATBOT ──
let chatInited = false;
let chatOpened = false;
function initChat() {
  if(chatInited) return;
  chatInited = true;
  addBotMessage(`Hi ${currentUser ? currentUser.name.split(' ')[0] : 'there'} 👋 I'm your BizPulse assistant. Ask me about today's sales, low stock items, top customers, or anything else on your dashboard.`);
}
function toggleChat() {
  const win = document.getElementById('chat-window');
  chatOpened = !chatOpened;
  win.classList.toggle('open', chatOpened);
  if(chatOpened) {
    document.getElementById('chat-badge').style.display = 'none';
    document.getElementById('chat-input').focus();
  }
}
function addBotMessage(text) {
  const body = document.getElementById('chat-body');
  const div = document.createElement('div');
  div.className = 'chat-msg bot';
  div.textContent = text;
  body.appendChild(div);
  body.scrollTop = body.scrollHeight;
}
function addUserMessage(text) {
  const body = document.getElementById('chat-body');
  const div = document.createElement('div');
  div.className = 'chat-msg user';
  div.textContent = text;
  body.appendChild(div);
  body.scrollTop = body.scrollHeight;
}
function showTyping() {
  const body = document.getElementById('chat-body');
  const div = document.createElement('div');
  div.className = 'chat-msg bot typing';
  div.id = 'typing-indicator';
  div.innerHTML = '<span></span><span></span><span></span>';
  body.appendChild(div);
  body.scrollTop = body.scrollHeight;
}
function hideTyping() {
  const t = document.getElementById('typing-indicator');
  if(t) t.remove();
}
function sendChat() {
  const input = document.getElementById('chat-input');
  const text = input.value.trim();
  if(!text) return;
  input.value = '';
  askBot(text);
}
function askBot(text) {
  addUserMessage(text);
  showTyping();
  setTimeout(()=>{
    hideTyping();
    addBotMessage(getBotReply(text));
  }, 600 + Math.random()*500);
}
function getBotReply(raw) {
  const q = raw.toLowerCase();

  if(/today.*sale|sale.*today/.test(q)) {
    return "Today's sales total ₨ 148,000 so far. This week you're at ₨ 896,000, and this month at ₨ 4.2M — up 12.5% from last month.";
  }
  if(/low stock|stock.*low|out of stock/.test(q)) {
    const lows = inventoryData.filter(p=>p.low).map(p=>`${p.name} (${p.qty} left)`);
    return lows.length
      ? `You have ${lows.length} low-stock items: ${lows.join(', ')}. Want me to take you to the Inventory page?`
      : "Good news — nothing is running low right now.";
  }
  if(/top customer|best customer|loyal customer/.test(q)) {
    const top = [...customersData].sort((a,b)=>parseFloat(b.spent.replace(/[^\d.]/g,''))-parseFloat(a.spent.replace(/[^\d.]/g,'')))[0];
    return `Your top customer is ${top.name} with ${top.orders} orders and ${top.spent} spent — a ${top.loyalty} member.`;
  }
  if(/revenue|profit margin|how.*business|how.*doing/.test(q)) {
    return "Total revenue is ₨ 4.2M this month with a 68.4% profit margin. Revenue is trending up 12.5% month over month.";
  }
  if(/order|recent order/.test(q)) {
    const last = salesData[0];
    return `The most recent order is ${last.id} from ${last.cust} for ${last.product}, totaling ${last.amt} (${last.status}).`;
  }
  if(/employee|staff|team/.test(q)) {
    const top = [...employeesData].sort((a,b)=>b.perf-a.perf)[0];
    return `You have ${employeesData.length} employees on record. Top performer right now is ${top.name} (${top.role}) at ${top.perf}%.`;
  }
  if(/notification/.test(q)) {
    const unread = notifData.filter(n=>n.unread).length;
    return `You have ${unread} unread notification${unread===1?'':'s'}. I can take you there if you'd like.`;
  }
  if(/help|what can you do/.test(q)) {
    return "I can help with quick answers about sales, inventory, customers, employees, and notifications. Try asking things like \"how are sales today\" or \"show low stock items\".";
  }
  if(/hi|hello|hey/.test(q)) {
    return "Hello! What would you like to know about your business today?";
  }
  if(/thank/.test(q)) {
    return "You're welcome! Let me know if there's anything else you'd like to check.";
  }
  return "I'm not totally sure about that one yet, but I can help with sales, inventory, customers, employees, or notifications. Try one of the quick options below the chat.";
}
</script>
</body>
</html>