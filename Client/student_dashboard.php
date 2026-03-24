<?php
// File: student_dashboard.php
// Path: /Client/student_dashboard.php

session_start();
if (!isset($_SESSION['client_id']) || $_SESSION['client_type'] !== 'student') {
    header('Location: ../Client_login.php?role=student');
    exit;
}

$stud_id = $_SESSION['client_id'];
$stud_name = $_SESSION['client_name'];
$stud_dept = $_SESSION['client_department'] ?? '';
$stud_branch = $_SESSION['client_branch'] ?? '';
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Student Portal — Holy Cross College</title>
    <link
        href="https://fonts.googleapis.com/css2?family=Cormorant+Garamond:ital,wght@0,300;0,400;0,600;1,300&family=DM+Sans:wght@300;400;500&display=swap"
        rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://unpkg.com/boxicons@2.1.4/css/boxicons.min.css" rel="stylesheet">
    <style>
        :root {
            --navy: #0a1f3c;
            --deep-blue: #0d2d5e;
            --mid-blue: #1a4a8a;
            --sky: #4a90d9;
            --pale-sky: #a8c8f0;
            --ice: #ddeeff;
            --frost: #eef5ff;
            --white: #ffffff;
            --gold: #c8a96e;
            --text-dark: #0d1f35;
            --text-mid: #3a5278;
            --text-light: #6a8aaa;
            --green: #1a7a4a;
            --green-light: #d4edda;
            --green-mid: #28a745;
            --sidebar-w: 240px;
        }

        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: 'DM Sans', sans-serif;
            background: #f0f4f8;
            color: var(--text-dark);
        }

        /* ── SIDEBAR ── */
        .sidebar {
            position: fixed;
            top: 0;
            left: 0;
            width: var(--sidebar-w);
            height: 100vh;
            background: linear-gradient(180deg, var(--navy) 0%, var(--deep-blue) 100%);
            display: flex;
            flex-direction: column;
            z-index: 100; overflow:hidden; }
.sidebar-brand { padding:1.5rem 1.4rem 1rem; border-bottom:1px solid rgba(168,200,240,.1); display:flex; align-items:center; gap:10px; }
.brand-cross { width:22px; height:26px; flex-shrink:0; position:relative; }
.brand-cross::before,.brand-cross::after { content:''; position:absolute; background:var(--gold); border-radius:2px; }
.brand-cross::before { width:4px; height:100%; left:50%; transform:translateX(-50%); }
.brand-cross::after  { width:100%; height:4px; top:28%; transform:translateY(-50%); }
.brand-text { font-family:'Cormorant Garamond',serif; font-size:1.2rem; font-weight:300; color:var(--white); white-space:nowrap; }
.brand-text span { font-style:italic; color:var(--pale-sky); }
.student-card { margin:1rem .8rem; background:rgba(255,255,255,.06); border:1px solid rgba(168,200,240,.12); border-radius:8px; padding:.9rem 1rem; }
.student-avatar { width:40px; height:40px; border-radius:50%; background:linear-gradient(135deg,var(--sky),var(--mid-blue)); display:flex; align-items:center; justify-content:center; font-family:'Cormorant Garamond',serif; font-size:1rem; font-weight:600; color:var(--white); margin-bottom:.5rem; }
.student-name { font-size:.82rem; font-weight:500; color:var(--white); }
.student-meta { font-size:.68rem; color:rgba(168,200,240,.55); margin-top:2px; }
.nav-list { padding:.5rem 0; flex:1; }
.nav-link-item { display:flex; align-items:center; gap:10px; padding:.65rem 1.4rem; color:rgba(168,200,240,.6); text-decoration:none; font-size:.84rem; transition:all .2s; border-left:3px solid transparent; cursor:pointer; }
.nav-link-item:hover { color:var(--white); background:rgba(255,255,255,.05); }
.nav-link-item.active { color:var(--white); border-left-color:var(--gold); background:rgba(255,255,255,.07); }
.nav-link-item i { font-size:1.1rem; flex-shrink:0; }
.sidebar-footer { padding:1rem .8rem; border-top:1px solid rgba(168,200,240,.1); }
.logout-btn { display:flex; align-items:center; gap:10px; padding:.65rem 1rem; width:100%; background:rgba(220,53,69,.12); border:1px solid rgba(220,53,69,.2); border-radius:6px; color:#ff8a8a; font-size:.83rem; font-family:'DM Sans',sans-serif; cursor:pointer; text-decoration:none; transition:all .2s; }
.logout-btn:hover { background:rgba(220,53,69,.22); color:#ffaaaa; }

/* ── MAIN ── */
.main-content { margin-left:var(--sidebar-w); min-height:100vh; padding:2rem; }
.topbar { display:flex; justify-content:space-between; align-items:center; margin-bottom:2rem; }
.page-title { font-family:'Cormorant Garamond',serif; font-size:1.8rem; font-weight:300; }
.page-title span { font-style:italic; color:var(--mid-blue); }

/* ── TABS ── */
.portal-tabs { display:flex; gap:0; border-bottom:2px solid var(--ice); margin-bottom:1.5rem; }
.portal-tab { padding:.7rem 1.4rem; background:transparent; border:none; font-family:'DM Sans',sans-serif; font-size:.85rem; color:var(--text-light); cursor:pointer; border-bottom:3px solid transparent; margin-bottom:-2px; transition:all .2s; display:flex; align-items:center; gap:7px; }
.portal-tab:hover { color:var(--mid-blue); }
.portal-tab.active { color:var(--mid-blue); border-bottom-color:var(--mid-blue); font-weight:500; }
.tab-badge { background:var(--mid-blue); color:var(--white); border-radius:10px; padding:1px 7px; font-size:.68rem; }
.tab-badge.green { background:var(--green-mid); }

/* ── STAT CARDS ── */
.stat-card { background:var(--white); border-radius:8px; padding:1.2rem 1.4rem; border:1px solid var(--ice); display:flex; justify-content:space-between; align-items:center; }
.stat-num { font-family:'Cormorant Garamond',serif; font-size:2rem; font-weight:300; color:var(--mid-blue); }
.stat-lbl { font-size:.72rem; letter-spacing:.1em; text-transform:uppercase; color:var(--text-light); margin-top:4px; }
.stat-icon { width:44px; height:44px; border-radius:8px; display:flex; align-items:center; justify-content:center; font-size:1.3rem; }

/* ── PANEL ── */
.panel { background:var(--white); border:1px solid var(--ice); border-radius:8px; overflow:hidden; margin-bottom:1.5rem; }
.panel-header { padding:1rem 1.4rem; border-bottom:1px solid var(--ice); display:flex; justify-content:space-between; align-items:center; }
.panel-title { font-family:'Cormorant Garamond',serif; font-size:1.15rem; font-weight:400; }
.panel-body { padding:1.4rem; }

/* ── SUBJECT GRID ── */
.subject-grid { display:grid; grid-template-columns:repeat(auto-fill,minmax(280px,1fr)); gap:1rem; }

/* ── PENDING CARD (not yet evaluated) ── */
.subject-card {
    border:1px solid var(--ice);
    border-radius:8px; padding:1.2rem;
    background:var(--frost);
    transition:box-shadow .2s, transform .2s;
    position:relative; overflow:hidden;
}
.subject-card:hover { box-shadow:0 4px 20px rgba(26,74,138,.1); transform:translateY(-2px); }
.subject-card::before {
    content:''; position:absolute; top:0; left:0; right:0; height:3px;
    background:linear-gradient(90deg,var(--sky),var(--mid-blue));
}

/* ── DONE CARD (already evaluated) ── */
.subject-card.evaluated {
    background: linear-gradient(145deg, #f0faf4, #e8f5ed);
    border-color: rgba(40,167,69,.25);
}
.subject-card.evaluated::before {
    background: linear-gradient(90deg, #28a745, #1a7a4a);
}
.subject-card.evaluated:hover {
    box-shadow: 0 4px 20px rgba(26,120,69,.12);
    transform: translateY(-2px);
}

/* Teacher avatar — pending vs done */
.teacher-avatar {
    width:36px; height:36px; border-radius:50%; flex-shrink:0;
    display:flex; align-items:center; justify-content:center;
    font-size:.72rem; font-weight:600; color:white;
}
.teacher-avatar.pending { background:linear-gradient(135deg,var(--sky),var(--mid-blue)); }
.teacher-avatar.done    { background:linear-gradient(135deg,#28a745,#1a7a4a); }

.subject-code { font-size:.7rem; letter-spacing:.14em; text-transform:uppercase; margin-bottom:4px; }
.subject-card.evaluated .subject-code { color:#1a7a4a; }
.subject-card:not(.evaluated) .subject-code { color:var(--sky); }

.subject-name { font-family:'Cormorant Garamond',serif; font-size:1.1rem; font-weight:400; margin-bottom:8px; }
.subject-meta { font-size:.78rem; color:var(--text-light); line-height:1.7; }
.subject-meta i { margin-right:4px; }
.subject-card.evaluated .subject-meta i { color:#28a745; }
.subject-card:not(.evaluated) .subject-meta i { color:var(--sky); }

.teacher-name { font-size:.82rem; font-weight:500; }
.subject-card.evaluated .teacher-name { color:var(--green); }
.subject-card:not(.evaluated) .teacher-name { color:var(--text-mid); }

/* Evaluate button */
.btn-evaluate {
    width:100%; padding:9px;
    background:linear-gradient(135deg,var(--sky),var(--mid-blue));
    color:var(--white); border:none; border-radius:6px;
    font-family:'DM Sans',sans-serif; font-size:.82rem; font-weight:500;
    letter-spacing:.06em; cursor:pointer; transition:all .25s;
}
.btn-evaluate:hover { opacity:.9; transform:translateY(-1px); }

/* Evaluated badge — replaces button */
.evaluated-badge {
    width:100%; padding:9px; text-align:center;
    background:linear-gradient(135deg,rgba(40,167,69,.12),rgba(26,122,74,.08));
    border:1.5px solid rgba(40,167,69,.35);
    border-radius:6px; font-size:.82rem;
    color:var(--green); font-weight:600;
    display:flex; align-items:center; justify-content:center; gap:6px;
}
.evaluated-badge .check-icon {
    width:18px; height:18px; border-radius:50%;
    background:var(--green-mid); color:white;
    display:flex; align-items:center; justify-content:center;
    font-size:.65rem; flex-shrink:0;
}

/* Evaluated watermark stamp */
.eval-stamp {
    position:absolute; top:10px; right:12px;
    font-size:.62rem; font-weight:700; letter-spacing:.1em; text-transform:uppercase;
    color:rgba(40,167,69,.5); border:1.5px solid rgba(40,167,69,.3);
    border-radius:4px; padding:2px 7px;
    transform:rotate(3deg);
    pointer-events:none;
}

/* ── SUBMISSIONS TABLE ── */
.submissions-table { width:100%; }
.submissions-table th { font-size:.75rem; text-transform:uppercase; letter-spacing:.08em; font-weight:600; color:var(--text-light); padding:.7rem 1rem; background:var(--frost); border-bottom:1px solid var(--ice); }
.submissions-table td { padding:.8rem 1rem; border-bottom:1px solid var(--ice); font-size:.88rem; vertical-align:middle; }
.submissions-table tr:last-child td { border-bottom:none; }
.submissions-table tbody tr { cursor:pointer; transition:background .15s; }
.submissions-table tbody tr:hover { background:var(--frost); }

/* ── EVAL MODAL ── */
.eval-modal-overlay { position:fixed; inset:0; background:rgba(10,31,60,.7); backdrop-filter:blur(4px); z-index:999; display:none; align-items:center; justify-content:center; padding:1rem; }
.eval-modal-overlay.show { display:flex; animation:fadeIn .25s ease; }
@keyframes fadeIn { from{opacity:0}to{opacity:1} }
.eval-modal { background:var(--white); border-radius:12px; width:100%; max-width:680px; max-height:90vh; overflow-y:auto; box-shadow:0 30px 60px rgba(5,15,35,.4); animation:slideUp .3s cubic-bezier(.22,1,.36,1); }
@keyframes slideUp { from{transform:translateY(24px);opacity:0}to{transform:translateY(0);opacity:1} }
.eval-modal-header { background:linear-gradient(135deg,var(--navy),var(--mid-blue)); padding:1.4rem 1.6rem; display:flex; justify-content:space-between; align-items:flex-start; }
.eval-modal-title { font-family:'Cormorant Garamond',serif; font-size:1.3rem; font-weight:300; color:var(--white); }
.eval-modal-sub { font-size:.75rem; color:rgba(168,200,240,.6); margin-top:3px; }
.eval-close-btn { background:rgba(255,255,255,.1); border:none; color:var(--white); width:30px; height:30px; border-radius:50%; cursor:pointer; font-size:1rem; display:flex; align-items:center; justify-content:center; }
.eval-close-btn:hover { background:rgba(255,255,255,.2); }
.eval-modal-body { padding:1.6rem; }
.eval-modal-footer { padding:1rem 1.6rem 1.4rem; border-top:1px solid var(--ice); display:flex; justify-content:flex-end; gap:.8rem; }
.eval-progress-wrap { margin-bottom:1.4rem; }
.eval-progress-label { display:flex; justify-content:space-between; font-size:.72rem; color:var(--text-light); margin-bottom:6px; letter-spacing:.06em; text-transform:uppercase; }
.eval-pb-bg { height:4px; background:var(--ice); border-radius:2px; overflow:hidden; }
.eval-pb-fill { height:100%; background:linear-gradient(90deg,var(--sky),var(--mid-blue)); border-radius:2px; transition:width .4s; }
.rating-labels { display:flex; justify-content:space-between; font-size:.65rem; color:var(--text-light); margin-bottom:4px; }
.rating-group { display:flex; gap:8px; }
.rating-btn { flex:1; padding:10px 4px; text-align:center; border:1.5px solid var(--ice); border-radius:6px; background:var(--frost); cursor:pointer; transition:all .2s; font-size:.85rem; font-weight:500; color:var(--text-mid); }
.rating-btn:hover { border-color:var(--sky); color:var(--sky); }
.rating-btn.selected { background:var(--mid-blue); border-color:var(--mid-blue); color:var(--white); }
.rating-btn .rl { font-size:.58rem; display:block; margin-top:2px; font-weight:300; opacity:.8; }
.qual-textarea { width:100%; border:1.5px solid var(--ice); border-radius:6px; padding:10px 12px; font-family:'DM Sans',sans-serif; font-size:.88rem; color:var(--text-dark); resize:vertical; min-height:90px; outline:none; background:var(--frost); }
.qual-textarea:focus { border-color:var(--sky); background:var(--white); box-shadow:0 0 0 3px rgba(74,144,217,.1); }
.btn-submit-eval { padding:10px 28px; background:linear-gradient(135deg,var(--sky),var(--mid-blue)); color:var(--white); border:none; border-radius:4px; font-family:'DM Sans',sans-serif; font-size:.85rem; font-weight:500; cursor:pointer; transition:all .25s; }
.btn-submit-eval:disabled { opacity:.6; cursor:not-allowed; }
.btn-cancel-modal { padding:10px 24px; background:transparent; border:1px solid var(--ice); border-radius:4px; font-family:'DM Sans',sans-serif; font-size:.85rem; color:var(--text-light); cursor:pointer; }
.question-block { margin-bottom:1.2rem; padding:12px 14px; background:#fafbff; border:1px solid #e8eef8; border-radius:6px; }
.question-category { font-size:.65rem; text-transform:uppercase; letter-spacing:.12em; color:var(--sky); margin-bottom:4px; font-weight:600; }
.question-text { font-size:.85rem; color:var(--text-dark); margin-bottom:8px; line-height:1.5; }
.eval-success { text-align:center; padding:2.5rem 1.6rem; display:none; }
.success-icon { width:64px; height:64px; border-radius:50%; background:rgba(40,167,69,.1); border:2px solid rgba(40,167,69,.3); display:flex; align-items:center; justify-content:center; font-size:1.8rem; color:#28a745; margin:0 auto 1rem; }
.detail-info-strip { background:#f8f9fa; border:1px solid #e9ecef; border-radius:6px; padding:12px 14px; margin-bottom:1.2rem; }
.detail-label { font-size:.65rem; text-transform:uppercase; letter-spacing:.08em; color:var(--text-light); }
.detail-value { font-size:.85rem; font-weight:500; color:var(--text-dark); }
.loader { width:32px; height:32px; border:3px solid var(--ice); border-top-color:var(--mid-blue); border-radius:50%; animation:spin .7s linear infinite; }
@keyframes spin { to{transform:rotate(360deg)} }
#pageLoader { display:flex; align-items:center; justify-content:center; padding:3rem; }
</style>
</head>
<body>

<!-- SIDEBAR -->
<aside class="sidebar">
    <div class="sidebar-brand">
        <div class="brand-cross"></div>
        <div class="brand-text">Holy Cross <span>College</span></div>
    </div>
    <div class="student-card">
        <div class="student-avatar" id="sidebarAvatar">--</div>
        <div class="student-name"><?= htmlspecialchars($stud_name) ?></div>
        <div class="student-meta"><?= htmlspecialchars($stud_dept) ?></div>
        <div class="student-meta"><?= htmlspecialchars($stud_branch) ?></div>
    </div>
    <div class="nav-list">
        <div class="nav-link-item active" id="navDashboard" onclick="switchTab('subjects')">
            <i class='bx bx-grid-alt'></i> Dashboard
        </div>
        <div class="nav-link-item" id="navSubjects" onclick="switchTab('subjects')">
            <i class='bx bx-book-open'></i> My Subjects
        </div>
        <div class="nav-link-item" id="navEvals" onclick="switchTab('evaluations')">
            <i class='bx bx-notepad'></i> My Evaluations
        </div>
    </div>
    <div class="sidebar-footer">
        <a href="../logout.php" class="logout-btn"><i class='bx bx-log-out'></i> Sign Out</a>
    </div>
</aside>

<!-- MAIN -->
<main class="main-content">
    <div class="topbar">
        <div>
            <div class="page-title">Student <span>Portal</span></div>
            <div style="font-size:.75rem;color:var(--text-light);letter-spacing:.06em;" id="topbarDate"></div>
        </div>
        <div style="font-size:.78rem;color:var(--text-light);">
            Welcome, <strong><?= htmlspecialchars($stud_name) ?></strong>
        </div>
    </div>

    <!-- Stats -->
    <div class="row g-3 mb-4">
        <div class="col-6 col-md-3">
            <div class="stat-card">
                <div><div class="stat-num" id="statSubjects">—</div><div class="stat-lbl">Subjects</div></div>
                <div class="stat-icon" style="background:rgba(74,144,217,.1)"><i class='bx bx-book' style="color:var(--sky)"></i></div>
            </div>
        </div>
        <div class="col-6 col-md-3">
            <div class="stat-card">
                <div><div class="stat-num" id="statPending">—</div><div class="stat-lbl">Pending</div></div>
                <div class="stat-icon" style="background:rgba(255,193,7,.1)"><i class='bx bx-time' style="color:#ffc107"></i></div>
            </div>
        </div>
        <div class="col-6 col-md-3">
            <div class="stat-card">
                <div><div class="stat-num" id="statDone">—</div><div class="stat-lbl">Evaluated</div></div>
                <div class="stat-icon" style="background:rgba(40,167,69,.1)"><i class='bx bx-check-circle' style="color:#28a745"></i></div>
            </div>
        </div>
        <div class="col-6 col-md-3">
            <div class="stat-card">
                <div><div class="stat-num" id="statTeachers">—</div><div class="stat-lbl">Teachers</div></div>
                <div class="stat-icon" style="background:rgba(26,74,138,.1)"><i class='bx bx-chalkboard' style="color:var(--mid-blue)"></i></div>
            </div>
        </div>
    </div>

    <!-- Tabs -->
    <div class="portal-tabs">
        <button class="portal-tab active" id="tabSubjects" onclick="switchTab('subjects')">
            <i class='bx bx-book-open'></i> My Subjects
        </button>
        <button class="portal-tab" id="tabEvaluations" onclick="switchTab('evaluations')">
            <i class='bx bx-notepad'></i> My Evaluations
            <span class="tab-badge green" id="evalBadge">0</span>
        </button>
    </div>

    <!-- TAB: Subjects -->
    <div id="tabPanelSubjects">
        <div class="panel">
            <div class="panel-header">
                <div class="panel-title">My Subjects &amp; Teacher Evaluations</div>
                <div style="font-size:.72rem;color:var(--text-light);" id="semesterLabel"></div>
            </div>
            <div class="panel-body">
                <div id="pageLoader"><div class="loader"></div></div>
                <div class="subject-grid" id="subjectGrid" style="display:none;"></div>
                <div id="noSubjects" style="display:none;text-align:center;padding:2rem;color:var(--text-light);">
                    <i class='bx bx-book-open' style="font-size:2.5rem;opacity:.3;display:block;margin-bottom:.5rem;"></i>
                    You are not enrolled in any subjects yet. Please contact your administrator.
                </div>
            </div>
        </div>
    </div>

    <!-- TAB: My Evaluations -->
    <div id="tabPanelEvaluations" style="display:none;">
        <div class="panel">
            <div class="panel-header">
                <div class="panel-title">My Submitted Evaluations</div>
                <div style="font-size:.72rem;color:var(--text-light);" id="evalCount"></div>
            </div>
            <div class="panel-body p-0">
                <div id="evalLoader" style="display:flex;align-items:center;justify-content:center;padding:3rem;">
                    <div class="loader"></div>
                </div>
                <div id="evalTableWrap" style="display:none;">
                    <table class="submissions-table w-100">
                        <thead>
                            <tr>
                                <th class="ps-4">#</th>
                                <th>Teacher Evaluated</th>
                                <th>Subject</th>
                                <th>Section</th>
                                <th>Semester</th>
                                <th>Date Submitted</th>
                                <th class="text-center pe-4">View</th>
                            </tr>
                        </thead>
                        <tbody id="evalTableBody"></tbody>
                    </table>
                </div>
                <div id="noEvals" style="display:none;text-align:center;padding:2.5rem;color:var(--text-light);">
                    <i class='bx bx-notepad' style="font-size:2.5rem;opacity:.3;display:block;margin-bottom:.5rem;"></i>
                    You have not submitted any evaluations yet.
                </div>
            </div>
        </div>
    </div>
</main>

<!-- ══ EVAL MODAL ══ -->
<div class="eval-modal-overlay" id="evalOverlay">
    <div class="eval-modal">
        <div class="eval-modal-header">
            <div>
                <div class="eval-modal-title" id="evalModalTitle">Teacher Evaluation</div>
                <div class="eval-modal-sub"   id="evalModalSub"></div>
            </div>
            <button class="eval-close-btn" onclick="closeModal()"><i class='bx bx-x'></i></button>
        </div>

        <!-- Evaluate form -->
        <div class="eval-modal-body" id="evalBody">
            <div class="eval-progress-wrap">
                <div class="eval-progress-label">
                    <span>Progress</span>
                    <span id="progressText">0 / 0 answered</span>
                </div>
                <div class="eval-pb-bg">
                    <div class="eval-pb-fill" id="progressFill" style="width:0%"></div>
                </div>
            </div>
            <div id="questionsContainer"></div>
        </div>

        <!-- Success screen -->
        <div class="eval-success" id="evalSuccess">
            <div class="success-icon"><i class='bx bx-check'></i></div>
            <div style="font-family:'Cormorant Garamond',serif;font-size:1.6rem;font-weight:300;margin-bottom:.5rem;">
                Evaluation Submitted
            </div>
            <p style="font-size:.88rem;color:var(--text-light);line-height:1.7;">
                Thank you for your honest feedback.<br>Your response has been recorded and cannot be changed.
            </p>
            <button class="btn-submit-eval mt-3" onclick="closeModal(true)" style="width:auto;padding:10px 32px;">
                Done
            </button>
        </div>

        <!-- Read-only detail view -->
        <div class="eval-modal-body" id="detailBody" style="display:none;"></div>

        <div class="eval-modal-footer" id="evalFooter">
            <button class="btn-cancel-modal" onclick="closeModal()">Cancel</button>
            <button class="btn-submit-eval" id="submitEvalBtn" onclick="submitEvaluation()">
                Submit Evaluation
            </button>
        </div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
<script>
const STUD_ID       = <?= (int) $stud_id ?>;
const RATING_LABELS = ['','Poor','Fair','Good','Very Good','Excellent'];
const RATING_COLORS = ['','danger','warning','info','primary','success'];

let currentSectionId = null;
let currentFacultyId = null;
let questions = [];
let answers   = {};
let modalMode = 'evaluate';
const evaluatedSections = new Set(); // tracks section IDs already submitted — blocks modal reopening

// ── INIT ───────────────────────────────────────────────────
document.addEventListener('DOMContentLoaded', () => {
    document.getElementById('topbarDate').textContent =
        new Date().toLocaleDateString('en-PH',{weekday:'long',year:'numeric',month:'long',day:'numeric'});
    const name = <?= json_encode($stud_name) ?>;
    document.getElementById('sidebarAvatar').textContent =
        name.split(' ').map(w=>w[0]).slice(0,2).join('').toUpperCase();
    loadSubjects();
    // Sync badge immediately
    fetch(`../ClientBackend/api_student.php?action=my_submissions`)
        .then(r=>r.json()).then(d=>{
            if (d.success) document.getElementById('evalBadge').textContent = d.data.length;
        });
});

// ── TAB SWITCH ─────────────────────────────────────────────
function switchTab(tab) {
    document.getElementById('tabPanelSubjects').style.display    = tab==='subjects'    ? 'block':'none';
    document.getElementById('tabPanelEvaluations').style.display = tab==='evaluations' ? 'block':'none';
    ['tabSubjects','tabEvaluations','navSubjects','navEvals','navDashboard'].forEach(id => {
        const el = document.getElementById(id);
        if (!el) return;
        const isSubj = id.toLowerCase().includes('subj') || id==='navDashboard';
        el.classList.toggle('active', tab==='subjects' ? isSubj : !isSubj && id!=='navDashboard');
    });
    if (tab==='evaluations') loadMyEvaluations();
}

// ── LOAD SUBJECTS ──────────────────────────────────────────
function loadSubjects() {
    fetch(`../ClientBackend/api_student.php?action=my_subjects&stud_id=${STUD_ID}`)
        .then(r=>r.json()).then(d => {
            document.getElementById('pageLoader').style.display = 'none';
            if (!d.success || !d.data.length) {
                document.getElementById('noSubjects').style.display = 'block';
                updateStats(0,0,0,0); return;
            }
            document.getElementById('subjectGrid').style.display = 'grid';
            if (d.data[0]) document.getElementById('semesterLabel').textContent =
                `${d.data[0].school_year} · ${d.data[0].semester} Semester`;

            let pending = 0, done = 0;
            const teacherSet = new Set();
            let html = '';

            d.data.forEach(s => {
                teacherSet.add(s.faculty_id);
                const evaluated = s.already_evaluated == 1;
                evaluated ? done++ : pending++;
                if (evaluated) evaluatedSections.add(s.section_id);

                const ini = s.teacher_name.split(' ').map(w=>w[0]).slice(0,2).join('').toUpperCase();

                // ── EVALUATED CARD (green, locked) ──
                if (evaluated) {
                    html += `
                    <div class="subject-card evaluated">
                        <div class="eval-stamp">Done</div>
                        <div class="subject-code">${escH(s.course_code)}</div>
                        <div class="subject-name">${escH(s.course_name)}</div>
                        <div class="subject-meta">
                            <i class='bx bx-calendar-alt'></i>${escH(s.schedule||'TBA')}
                            &nbsp;&nbsp;<i class='bx bx-door-open'></i>${escH(s.room||'TBA')}
                        </div>
                        <div class="subject-meta" style="margin-top:2px;">
                            <i class='bx bx-layer'></i>${escH(s.section_name)}
                            &nbsp;|&nbsp;<i class='bx bx-building-house'></i>${escH(s.branch)}
                        </div>
                        <div class="d-flex align-items-center gap-2 mt-3 mb-2">
                            <div class="teacher-avatar done">${ini}</div>
                            <div>
                                <div class="teacher-name">${escH(s.teacher_name)}</div>
                                <div style="font-size:.68rem;color:#1a7a4a;text-transform:capitalize;opacity:.8;">${escH(s.teacher_role)}</div>
                            </div>
                        </div>
                        <div class="evaluated-badge">
                            <div class="check-icon"><i class='bx bx-check'></i></div>
                            Evaluation Submitted — Cannot be changed
                        </div>
                    </div>`;

                // ── PENDING CARD (blue, can evaluate) ──
                } else {
                    html += `
                    <div class="subject-card">
                        <div class="subject-code">${escH(s.course_code)}</div>
                        <div class="subject-name">${escH(s.course_name)}</div>
                        <div class="subject-meta">
                            <i class='bx bx-calendar-alt'></i>${escH(s.schedule||'TBA')}
                            &nbsp;&nbsp;<i class='bx bx-door-open'></i>${escH(s.room||'TBA')}
                        </div>
                        <div class="subject-meta" style="margin-top:2px;">
                            <i class='bx bx-layer'></i>${escH(s.section_name)}
                            &nbsp;|&nbsp;<i class='bx bx-building-house'></i>${escH(s.branch)}
                        </div>
                        <div class="d-flex align-items-center gap-2 mt-3 mb-2">
                            <div class="teacher-avatar pending">${ini}</div>
                            <div>
                                <div class="teacher-name">${escH(s.teacher_name)}</div>
                                <div style="font-size:.68rem;color:var(--text-light);text-transform:capitalize;">${escH(s.teacher_role)}</div>
                            </div>
                        </div>
                        <button class="btn-evaluate"
                            onclick="openEvalModal(${s.section_id},${s.faculty_id},'${escH(s.teacher_name)}','${escH(s.course_name)}')">
                            Evaluate Teacher
                        </button>
                    </div>`;
                }
            });

            document.getElementById('subjectGrid').innerHTML = html;
            document.getElementById('evalBadge').textContent = done;
            updateStats(d.data.length, pending, done, teacherSet.size);
        })
        .catch(() => {
            document.getElementById('pageLoader').style.display = 'none';
            document.getElementById('noSubjects').style.display = 'block';
        });
}

function updateStats(s,p,d,t) {
    document.getElementById('statSubjects').textContent = s;
    document.getElementById('statPending').textContent  = p;
    document.getElementById('statDone').textContent     = d;
    document.getElementById('statTeachers').textContent = t;
}

// ── MY EVALUATIONS TAB ─────────────────────────────────────
function loadMyEvaluations() {
    document.getElementById('evalLoader').style.display    = 'flex';
    document.getElementById('evalTableWrap').style.display = 'none';
    document.getElementById('noEvals').style.display       = 'none';

    fetch(`../ClientBackend/api_student.php?action=my_submissions&stud_id=${STUD_ID}`)
        .then(r=>r.json()).then(d => {
            document.getElementById('evalLoader').style.display = 'none';
            if (!d.success||!d.data.length) {
                document.getElementById('noEvals').style.display = 'block'; return;
            }
            document.getElementById('evalTableWrap').style.display = 'block';
            document.getElementById('evalCount').textContent = `${d.data.length} submission(s)`;
            document.getElementById('evalBadge').textContent = d.data.length;

            const semColors = {'1st':'primary','2nd':'info','summer':'warning'};
            let html = '';
            d.data.forEach((r,i) => {
                const date = new Date(r.submitted_at).toLocaleDateString('en-PH',{month:'short',day:'numeric',year:'numeric'});
                html += `
                <tr onclick="viewMyDetail(${r.section_id})">
                    <td class="ps-4 text-muted">${i+1}</td>
                    <td>
                        <div class="fw-semibold" style="font-size:.85rem;">${escH(r.teacher_name)}</div>
                        <small class="text-muted text-capitalize">${escH(r.teacher_role)}</small>
                    </td>
                    <td>
                        <div style="font-size:.85rem;">${escH(r.course_name)}</div>
                        <small class="text-muted">${escH(r.course_code)}</small>
                    </td>
                    <td><span class="badge bg-light text-dark border">${escH(r.section_name)}</span></td>
                    <td><span class="badge bg-${semColors[r.semester]||'secondary'} bg-opacity-10 text-${semColors[r.semester]||'secondary'} border">${escH(r.semester)}</span></td>
                    <td><small class="text-muted">${date}</small></td>
                    <td class="text-center pe-4">
                        <button class="btn btn-sm btn-outline-success" onclick="event.stopPropagation();viewMyDetail(${r.section_id})" title="View my answers">
                            <i class="bi bi-eye"></i>
                        </button>
                    </td>
                </tr>`;
            });
            document.getElementById('evalTableBody').innerHTML = html;
        });
}

// ── VIEW OWN SUBMISSION (read-only) ───────────────────────
function viewMyDetail(section_id) {
    modalMode = 'detail';
    document.getElementById('evalBody').style.display    = 'none';
    document.getElementById('evalFooter').style.display  = 'none';
    document.getElementById('evalSuccess').style.display = 'none';
    document.getElementById('detailBody').style.display  = 'block';
    document.getElementById('detailBody').innerHTML =
        '<div style="text-align:center;padding:2rem;"><div class="loader" style="margin:auto;"></div></div>';
    document.getElementById('evalModalTitle').textContent = 'My Evaluation';
    document.getElementById('evalModalSub').textContent   = 'Read-only — submissions cannot be edited';
    document.getElementById('evalOverlay').classList.add('show');
    document.body.style.overflow = 'hidden';

    fetch(`../ClientBackend/api_student.php?action=my_detail&stud_id=${STUD_ID}&section_id=${section_id}`)
        .then(r=>r.json()).then(d => {
            if (!d.success) { document.getElementById('detailBody').innerHTML='<p class="text-danger p-3">Failed to load.</p>'; return; }
            renderReadOnlyDetail(d.data);
        });
}

function renderReadOnlyDetail(data) {
    const info      = data.info;
    const responses = data.responses;
    document.getElementById('evalModalTitle').textContent = `${info.course_name}`;
    document.getElementById('evalModalSub').textContent   = `${info.teacher_name} · ${info.section_name} · Read-only`;

    let html = `
    <div class="detail-info-strip row g-2 mb-4">
        <div class="col-6"><div class="detail-label">Teacher</div><div class="detail-value">${escH(info.teacher_name)}</div></div>
        <div class="col-6"><div class="detail-label">Subject</div><div class="detail-value">${escH(info.course_name)}</div></div>
        <div class="col-6"><div class="detail-label">Section</div><div class="detail-value">${escH(info.section_name)}</div></div>
        <div class="col-6"><div class="detail-label">Submitted</div><div class="detail-value">${new Date(info.submitted_at).toLocaleDateString('en-PH',{month:'long',day:'numeric',year:'numeric'})}</div></div>
    </div>
    <!-- Locked notice -->
    <div class="d-flex align-items-center gap-2 mb-3 p-2 rounded" style="background:rgba(40,167,69,.08);border:1px solid rgba(40,167,69,.2);">
        <i class='bx bx-lock-alt' style="color:#28a745;font-size:1.1rem;"></i>
        <small style="color:#1a7a4a;">This evaluation has been submitted and is locked. You cannot edit your responses.</small>
    </div>`;

    let lastCat = '';
    responses.forEach((resp, i) => {
        if (resp.category !== lastCat) {
            if (lastCat !== '') html += '</div>';
            html += `<div class="mb-3"><div style="font-size:.67rem;text-transform:uppercase;letter-spacing:.1em;color:var(--sky);font-weight:700;padding:5px 0;border-bottom:2px solid var(--ice);margin-bottom:8px;">${escH(resp.category)}</div>`;
            lastCat = resp.category;
        }
        html += `<div class="question-block">
            <div class="question-text"><span class="text-muted me-1">${i+1}.</span>${escH(resp.question)}</div>`;

        if (resp.type === 'quantitative') {
            const v   = resp.rating_value ?? 0;
            const pct = v ? Math.round((v/5)*100) : 0;
            html += `<div class="d-flex align-items-center gap-3">
                <span class="badge bg-${RATING_COLORS[v]}" style="font-size:.85rem;padding:5px 14px;">${v} — ${RATING_LABELS[v]||'—'}</span>
                <div style="flex:1;height:6px;background:#e9ecef;border-radius:3px;overflow:hidden;">
                    <div style="width:${pct}%;height:100%;background:var(--bs-${RATING_COLORS[v]});border-radius:3px;"></div>
                </div>
                <small class="text-muted">${pct}%</small>
            </div>`;
        } else {
            const ans = resp.text_response?.trim()||'';
            html += `<div style="background:#fff;border:1px solid #dee2e6;border-radius:4px;padding:10px 12px;font-style:${ans?'normal':'italic'};color:${ans?'inherit':'#adb5bd'};">
                ${ans ? escH(ans) : 'No response provided.'}
            </div>`;
        }
        html += `</div>`;
    });
    if (lastCat !== '') html += '</div>';
    document.getElementById('detailBody').innerHTML = html;
}

// ── OPEN EVALUATE MODAL ────────────────────────────────────
function openEvalModal(sectionId, facultyId, teacherName, courseName) {
    // Hard block — prevents modal from opening if already evaluated
    if (evaluatedSections.has(sectionId)) {
        showToast('You have already evaluated this teacher. Submissions cannot be changed.', 'warning');
        return;
    }

    modalMode = 'evaluate';
    currentSectionId = sectionId;
    currentFacultyId = facultyId;
    answers = {};
    document.getElementById('evalModalTitle').textContent = `Evaluate: ${teacherName}`;
    document.getElementById('evalModalSub').textContent   = `${courseName} · Please answer all questions honestly`;
    document.getElementById('evalBody').style.display     = 'block';
    document.getElementById('evalFooter').style.display   = 'flex';
    document.getElementById('evalSuccess').style.display  = 'none';
    document.getElementById('detailBody').style.display   = 'none';
    document.getElementById('questionsContainer').innerHTML =
        '<div style="text-align:center;padding:2rem;"><div class="loader" style="margin:auto;"></div></div>';
    document.getElementById('evalOverlay').classList.add('show');
    document.body.style.overflow = 'hidden';

    fetch('../ClientBackend/api_student.php?action=get_questions')
        .then(r=>r.json()).then(d => {
            if (!d.success) return;
            questions = d.data;
            renderQuestions();
        });
}

function closeModal(reload=false) {
    document.getElementById('evalOverlay').classList.remove('show');
    document.body.style.overflow = '';
    if (reload) {
        document.getElementById('pageLoader').style.display = 'flex';
        document.getElementById('subjectGrid').style.display = 'none';
        loadSubjects();
    }
}

// ── RENDER QUESTIONS ───────────────────────────────────────
function renderQuestions() {
    let html = '';
    questions.forEach((q,i) => {
        html += `<div class="question-block" id="qb_${q.id}">
            <div class="question-category">${escH(q.category)}</div>
            <div class="question-text"><strong>${i+1}.</strong> ${escH(q.question)}</div>`;
        if (q.type==='quantitative') {
            html += `<div class="rating-labels"><span>Poor</span><span>Fair</span><span>Good</span><span>Very Good</span><span>Excellent</span></div>
            <div class="rating-group">`;
            for (let v=1;v<=5;v++) html += `<div class="rating-btn" id="rb_${q.id}_${v}" onclick="selectRating(${q.id},${v})">${v}<span class="rl">${RATING_LABELS[v]}</span></div>`;
            html += `</div>`;
        } else {
            html += `<textarea class="qual-textarea" id="qa_${q.id}" placeholder="Write your response here..." oninput="recordText(${q.id},this.value)"></textarea>`;
        }
        html += `</div>`;
    });
    document.getElementById('questionsContainer').innerHTML = html;
    updateProgress();
}

function selectRating(qId, val) {
    answers[qId] = {type:'quantitative',value:val};
    for (let v=1;v<=5;v++) document.getElementById(`rb_${qId}_${v}`)?.classList.toggle('selected', v===val);
    updateProgress();
}
function recordText(qId, val) { answers[qId]={type:'qualitative',value:val.trim()}; updateProgress(); }
function updateProgress() {
    const total    = questions.length;
    const answered = questions.filter(q => answers[q.id] && (q.type==='qualitative'||answers[q.id].value)).length;
    const pct      = total ? Math.round(answered/total*100) : 0;
    document.getElementById('progressFill').style.width = pct+'%';
    document.getElementById('progressText').textContent = `${answered} / ${total} answered`;
}

// ── SUBMIT EVALUATION ──────────────────────────────────────
function submitEvaluation() {
    const unanswered = questions.filter(q=>q.type==='quantitative'&&!answers[q.id]);
    if (unanswered.length) {
        const el = document.getElementById(`qb_${unanswered[0].id}`);
        if (el) { el.scrollIntoView({behavior:'smooth',block:'center'}); el.style.outline='2px solid #dc3545'; setTimeout(()=>el.style.outline='',2500); }
        showToast('Please answer all rating questions before submitting.','warning');
        return;
    }
    const btn = document.getElementById('submitEvalBtn');
    btn.disabled = true;
    btn.innerHTML = '<span class="spinner-border spinner-border-sm me-2"></span>Submitting...';

    fetch('../ClientBackend/api_student.php?action=submit_evaluation',{
        method:'POST', headers:{'Content-Type':'application/json'},
        body: JSON.stringify({
            section_id: currentSectionId,
            faculty_id: currentFacultyId,
            stud_id:    STUD_ID,
            responses:  questions.map(q=>({
                question_id:   q.id,
                type:          q.type,
                rating_value:  q.type==='quantitative' ? (answers[q.id]?.value||null) : null,
                text_response: q.type==='qualitative'  ? (answers[q.id]?.value||'')   : null,
            }))
        })
    }).then(r=>r.json()).then(d=>{
        if (d.success) {
            document.getElementById('evalBody').style.display    = 'none';
            document.getElementById('evalFooter').style.display  = 'none';
            document.getElementById('evalSuccess').style.display = 'block';
        } else {
            showToast(d.message||'Submission failed. Please try again.','error');
        }
    }).catch(()=>showToast('Network error. Please try again.','error'))
    .finally(()=>{ btn.disabled=false; btn.textContent='Submit Evaluation'; });
}

document.getElementById('evalOverlay').addEventListener('click', e=>{
    if (e.target===e.currentTarget) closeModal();
});

function escH(s) { if(!s&&s!==0)return''; const d=document.createElement('div'); d.textContent=String(s); return d.innerHTML; }
function showToast(msg, type='info') {
    const c={success:'#28a745',error:'#dc3545',warning:'#ffc107',info:'#17a2b8'};
    const el=document.createElement('div');
    el.style.cssText='position:fixed;top:1rem;right:1rem;z-index:9999;min-width:280px;';
    el.innerHTML=`<div style="background:${c[type]};color:white;padding:12px 16px;border-radius:6px;font-size:.85rem;box-shadow:0 4px 16px rgba(0,0,0,.2);">${msg}</div>`;
    document.body.appendChild(el); setTimeout(()=>el.remove(),4000);
}
</script>
</body>
</html>