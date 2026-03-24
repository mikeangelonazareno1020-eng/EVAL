<?php
// ============================================================
// FILE: teacher_portal.php  (v3 – DB-driven, FK-aware, no fake fallback)
// ============================================================
session_start();

if (isset($_GET['logout'])) {
    session_destroy();
    header("Location: teacher_login.php");
    exit();
}
$allowed_roles = ['teacher', 'programchair'];
if (!isset($_SESSION['teacher_id']) || !in_array($_SESSION['role'] ?? '', $allowed_roles)) {
    header("Location: teacher_login.php");
    exit();
}

$teacher_id = (int) $_SESSION['teacher_id'];
$teacher_name = $_SESSION['teacher_name'];
$department = $_SESSION['department'];
$program = $_SESSION['program'];
$branch = $_SESSION['branch'];
$employee_id = $_SESSION['employee_id'];

require_once '../includes/conn.php';

$db_error = '';
$peers = [];
$active_forms = [];
$my_submissions = [];
$form_questions = [];

if (!$conn) {
    $db_error = 'Database connection failed. Check includes/conn.php.';
} else {
    $dept_safe = mysqli_real_escape_string($conn, $department);

    // Peers: same dept, not self, active
    $res = mysqli_query(
        $conn,
        "SELECT fac_id, employee_id, full_name, department, program, branch
         FROM tbl_faculty
         WHERE department='$dept_safe' AND fac_id!=$teacher_id AND is_active=1
         ORDER BY full_name ASC"
    );
    if ($res) {
        while ($row = mysqli_fetch_assoc($res))
            $peers[] = $row;
    } else
        $db_error = 'Error loading peers: ' . mysqli_error($conn);

    // Active peer-to-peer forms
    $res2 = mysqli_query(
        $conn,
        "SELECT * FROM eval_forms WHERE eval_type='peer_to_peer' AND status='active' ORDER BY created_at DESC"
    );
    if ($res2) {
        while ($row = mysqli_fetch_assoc($res2))
            $active_forms[] = $row;
    } else
        $db_error .= ' | Error loading forms: ' . mysqli_error($conn);

    // My submitted assignments
    $res3 = mysqli_query(
        $conn,
        "SELECT form_id, evaluatee_id, status FROM eval_assignments WHERE evaluator_id=$teacher_id"
    );
    if ($res3) {
        while ($row = mysqli_fetch_assoc($res3))
            $my_submissions[$row['form_id'] . '_' . $row['evaluatee_id']] = $row['status'];
    }

    // Questions per form
    foreach ($active_forms as $form) {
        $fid = (int) $form['id'];
        $qr = mysqli_query(
            $conn,
            "SELECT id, type, question FROM eval_questions WHERE form_id=$fid ORDER BY sort_order ASC"
        );
        $qs = [];
        if ($qr)
            while ($qrow = mysqli_fetch_assoc($qr))
                $qs[] = $qrow;
        $form_questions[$fid] = $qs;
    }
}

// Build buckets
$pending_items = [];
$done_items = [];
foreach ($active_forms as $form) {
    foreach ($peers as $peer) {
        $key = $form['id'] . '_' . $peer['fac_id'];
        $entry = ['peer' => $peer, 'form' => $form, 'key' => $key];
        if (($my_submissions[$key] ?? '') === 'submitted')
            $done_items[] = $entry;
        else
            $pending_items[] = $entry;
    }
}
$total_tasks = count($peers) * count($active_forms);
$done_count = count($done_items);
$pending_count = count($pending_items);

$R = 36;
$C = round(2 * M_PI * $R, 2);
$off = round($C * (1 - ($total_tasks > 0 ? $done_count / $total_tasks : 0)), 2);

$COLORS = ['#1a56db', '#059669', '#d97706', '#7c3aed', '#dc2626', '#0891b2', '#be185d', '#0f766e'];
function initials(string $n): string
{
    $w = array_filter(explode(' ', $n));
    return implode('', array_map(fn($x) => strtoupper($x[0]), array_slice($w, 0, 2)));
}
function avatarColor(int $id, array $pal): string
{
    return $pal[$id % count($pal)];
}
function esc(string $s): string
{
    return htmlspecialchars($s, ENT_QUOTES, 'UTF-8');
}
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width,initial-scale=1.0">
    <title>My Evaluations — HCC Faculty Portal</title>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@300;400;500;600;700;800&display=swap"
        rel="stylesheet">
    <link href="https://unpkg.com/boxicons@2.1.4/css/boxicons.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        :root {
            --navy: #0c1e3c;
            --blue: #1a56db;
            --blue-lt: #eff4ff;
            --gold: #f59e0b;
            --green: #16a34a;
            --grn-lt: #f0fdf4;
            --red: #dc2626;
            --muted: #6b7280;
            --border: #e5e7eb;
            --bg: #f5f7fa;
            --white: #ffffff;
            --r: 14px;
            --sh-lg: 0 12px 40px rgba(0, 0, 0, .14)
        }

        *,
        *::before,
        *::after {
            box-sizing: border-box;
            margin: 0;
            padding: 0
        }

        body {
            font-family: 'Plus Jakarta Sans', sans-serif;
            background: var(--bg);
            color: var(--navy);
            min-height: 100vh
        }

        .hdr {
            position: sticky;
            top: 0;
            z-index: 200;
            background: var(--white);
            border-bottom: 1px solid var(--border);
            height: 60px;
            display: flex;
            align-items: center;
            justify-content: space-between;
            padding: 0 28px;
            box-shadow: 0 1px 0 var(--border)
        }

        .hdr-brand {
            display: flex;
            align-items: center;
            gap: 10px
        }

        .hdr-logo {
            width: 34px;
            height: 34px;
            border-radius: 9px;
            background: var(--blue);
            display: flex;
            align-items: center;
            justify-content: center
        }

        .hdr-logo i {
            font-size: 18px;
            color: #fff
        }

        .brand {
            font-size: .95rem;
            font-weight: 800;
            color: var(--navy)
        }

        .brand span {
            color: var(--blue)
        }

        .hdr-right {
            display: flex;
            align-items: center;
            gap: 12px
        }

        .dept-pill {
            background: var(--blue-lt);
            color: var(--blue);
            font-size: .75rem;
            font-weight: 700;
            padding: 5px 12px;
            border-radius: 20px;
            display: flex;
            align-items: center;
            gap: 5px
        }

        .u-av {
            width: 34px;
            height: 34px;
            border-radius: 50%;
            background: linear-gradient(135deg, var(--blue), #60a5fa);
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 12px;
            font-weight: 800;
            color: #fff;
            cursor: pointer
        }

        .u-name {
            font-size: .825rem;
            font-weight: 600;
            max-width: 160px;
            overflow: hidden;
            text-overflow: ellipsis;
            white-space: nowrap
        }

        .wrap {
            max-width: 860px;
            margin: 0 auto;
            padding: 32px 20px 80px
        }

        .db-err {
            background: #fef2f2;
            border: 1px solid #fecaca;
            border-radius: 10px;
            padding: 14px 18px;
            margin-bottom: 24px;
            font-size: .85rem;
            color: #b91c1c;
            display: flex;
            align-items: flex-start;
            gap: 10px
        }

        .db-err i {
            font-size: 18px;
            flex-shrink: 0;
            margin-top: 1px
        }

        .db-err strong {
            display: block;
            margin-bottom: 3px
        }

        .db-err code {
            font-size: .78rem;
            opacity: .85;
            word-break: break-all
        }

        .banner {
            background: linear-gradient(130deg, var(--navy) 0%, #1e3a6e 100%);
            border-radius: var(--r);
            padding: 26px 28px;
            margin-bottom: 28px;
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: 20px;
            position: relative;
            overflow: hidden
        }

        .banner::after {
            content: '';
            position: absolute;
            right: -30px;
            top: -40px;
            width: 180px;
            height: 180px;
            background: rgba(255, 255, 255, .04);
            border-radius: 50%
        }

        .banner::before {
            content: '';
            position: absolute;
            right: 70px;
            bottom: -50px;
            width: 130px;
            height: 130px;
            background: rgba(26, 86, 219, .22);
            border-radius: 50%
        }

        .banner-l {
            position: relative;
            z-index: 1
        }

        .banner-l h2 {
            font-size: 1.2rem;
            font-weight: 800;
            color: #fff;
            margin-bottom: 4px
        }

        .banner-l p {
            font-size: .82rem;
            color: rgba(255, 255, 255, .6);
            margin: 0
        }

        .banner-r {
            position: relative;
            z-index: 1;
            flex-shrink: 0
        }

        .prog-wrap {
            position: relative;
            display: inline-flex;
            align-items: center;
            justify-content: center
        }

        .prog-svg {
            transform: rotate(-90deg)
        }

        .prog-track {
            fill: none;
            stroke: rgba(255, 255, 255, .12);
            stroke-width: 5
        }

        .prog-fill {
            fill: none;
            stroke: var(--gold);
            stroke-width: 5;
            stroke-linecap: round;
            transition: stroke-dashoffset .6s cubic-bezier(.4, 0, .2, 1)
        }

        .prog-lbl {
            position: absolute;
            text-align: center
        }

        .prog-num {
            font-size: 1.05rem;
            font-weight: 800;
            color: #fff;
            display: block;
            line-height: 1
        }

        .prog-sub {
            font-size: .62rem;
            color: rgba(255, 255, 255, .5);
            display: block;
            margin-top: 2px
        }

        .tabs {
            display: flex;
            gap: 0;
            background: var(--white);
            border: 1px solid var(--border);
            border-radius: 11px;
            padding: 4px;
            margin-bottom: 22px;
            width: fit-content
        }

        .tab {
            padding: 8px 20px;
            border-radius: 8px;
            border: none;
            font-family: inherit;
            font-size: .825rem;
            font-weight: 600;
            cursor: pointer;
            transition: all .15s;
            display: flex;
            align-items: center;
            gap: 7px;
            color: var(--muted);
            background: transparent
        }

        .tab .n {
            min-width: 20px;
            height: 20px;
            border-radius: 20px;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            font-size: .7rem;
            font-weight: 800;
            padding: 0 5px;
            background: var(--border);
            color: var(--muted);
            transition: all .15s
        }

        .tab.on {
            background: var(--navy);
            color: #fff
        }

        .tab.on .n {
            background: rgba(255, 255, 255, .2);
            color: #fff
        }

        .tab.on.ptab .n {
            background: var(--gold);
            color: #000
        }

        .tab.on.vtab .n {
            background: #7c3aed;
            color: #fff
        }

        .frow {
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: 12px;
            margin-bottom: 20px;
            flex-wrap: wrap
        }

        .sbox {
            display: flex;
            align-items: center;
            gap: 8px;
            background: var(--white);
            border: 1.5px solid var(--border);
            border-radius: 10px;
            padding: 8px 14px;
            width: 260px;
            transition: border-color .15s
        }

        .sbox:focus-within {
            border-color: var(--blue)
        }

        .sbox i {
            color: var(--muted);
            font-size: 17px;
            flex-shrink: 0
        }

        .sbox input {
            border: none;
            outline: none;
            font-family: inherit;
            font-size: .85rem;
            background: transparent;
            width: 100%
        }

        .fsel {
            background: var(--white);
            border: 1.5px solid var(--border);
            border-radius: 10px;
            padding: 8px 14px;
            font-family: inherit;
            font-size: .82rem;
            font-weight: 500;
            color: var(--navy);
            cursor: pointer;
            outline: none
        }

        .elist {
            display: flex;
            flex-direction: column;
            gap: 10px
        }

        .eitem {
            background: var(--white);
            border: 1px solid var(--border);
            border-radius: var(--r);
            padding: 16px 20px;
            display: flex;
            align-items: center;
            gap: 14px;
            transition: box-shadow .18s, border-color .18s, transform .18s;
            animation: inUp .3s ease both;
            position: relative;
            overflow: hidden
        }

        .eitem::before {
            content: '';
            position: absolute;
            left: 0;
            top: 0;
            bottom: 0;
            width: 4px;
            border-radius: 4px 0 0 4px;
            background: var(--blue);
            opacity: 0;
            transition: opacity .18s
        }

        .eitem:not(.done):hover {
            box-shadow: var(--sh-lg);
            border-color: #c7d7ff;
            transform: translateX(3px)
        }

        .eitem:not(.done):hover::before {
            opacity: 1
        }

        .eitem.done {
            opacity: .72
        }

        .eitem.done::before {
            background: var(--green);
            opacity: 1
        }

        @keyframes inUp {
            from {
                opacity: 0;
                transform: translateY(10px)
            }

            to {
                opacity: 1;
                transform: translateY(0)
            }
        }

        .iav {
            width: 48px;
            height: 48px;
            border-radius: 13px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 16px;
            font-weight: 800;
            color: #fff;
            flex-shrink: 0
        }

        .iinfo {
            flex: 1;
            min-width: 0
        }

        .iname {
            font-size: .93rem;
            font-weight: 700;
            color: var(--navy);
            margin-bottom: 3px
        }

        .imeta {
            display: flex;
            align-items: center;
            gap: 7px;
            flex-wrap: wrap
        }

        .itag {
            font-size: .72rem;
            color: var(--muted);
            display: flex;
            align-items: center;
            gap: 3px
        }

        .itag i {
            font-size: 13px
        }

        .isep {
            color: var(--border);
            font-size: .75rem
        }

        .iform {
            font-size: .71rem;
            font-weight: 700;
            color: var(--blue);
            background: var(--blue-lt);
            padding: 2px 8px;
            border-radius: 20px
        }

        .iright {
            display: flex;
            align-items: center;
            gap: 10px;
            flex-shrink: 0
        }

        .chip {
            display: flex;
            align-items: center;
            gap: 5px;
            font-size: .74rem;
            font-weight: 700;
            padding: 5px 11px;
            border-radius: 20px
        }

        .chip.pend {
            background: #fffbeb;
            color: #92400e;
            border: 1px solid #fde68a
        }

        .chip.done {
            background: var(--grn-lt);
            color: var(--green);
            border: 1px solid #bbf7d0
        }

        .brate {
            padding: 9px 20px;
            border: none;
            border-radius: 9px;
            font-family: inherit;
            font-size: .825rem;
            font-weight: 700;
            cursor: pointer;
            display: flex;
            align-items: center;
            gap: 7px;
            transition: all .15s;
            white-space: nowrap
        }

        .brate.go {
            background: var(--blue);
            color: #fff;
            box-shadow: 0 3px 10px rgba(26, 86, 219, .28)
        }

        .brate.go:hover {
            background: #1648c0;
            transform: translateY(-1px)
        }

        .brate.view-btn {
            background: #f3f4f6;
            color: var(--navy);
            border: 1px solid var(--border)
        }

        .brate.view-btn:hover {
            background: var(--border);
            transform: translateY(-1px)
        }

        .estate {
            text-align: center;
            padding: 56px 20px;
            background: var(--white);
            border: 1px solid var(--border);
            border-radius: var(--r)
        }

        .estate i {
            font-size: 3rem;
            opacity: .3;
            display: block;
            margin-bottom: 12px
        }

        .estate h5 {
            font-size: 1rem;
            font-weight: 700;
            margin-bottom: 5px
        }

        .estate p {
            font-size: .85rem;
            color: var(--muted);
            margin: 0
        }

        .alldone {
            text-align: center;
            padding: 56px 20px;
            background: linear-gradient(135deg, #f0fdf4, #dcfce7);
            border: 1px solid #bbf7d0;
            border-radius: var(--r)
        }

        .alldone i {
            font-size: 3.5rem;
            color: var(--green);
            display: block;
            margin-bottom: 14px
        }

        .alldone h5 {
            font-size: 1.1rem;
            font-weight: 800;
            margin-bottom: 5px
        }

        .alldone p {
            font-size: .85rem;
            color: var(--green);
            margin: 0
        }

        .moverlay {
            position: fixed;
            inset: 0;
            z-index: 900;
            background: rgba(12, 30, 60, .62);
            backdrop-filter: blur(6px);
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 16px;
            opacity: 0;
            pointer-events: none;
            transition: opacity .2s
        }

        .moverlay.open {
            opacity: 1;
            pointer-events: all
        }

        .modal-box {
            background: var(--white);
            border-radius: 18px;
            width: 100%;
            max-width: 660px;
            max-height: 92vh;
            display: flex;
            flex-direction: column;
            box-shadow: 0 32px 80px rgba(0, 0, 0, .25);
            transform: scale(.96) translateY(16px);
            transition: transform .25s cubic-bezier(.34, 1.56, .64, 1)
        }

        .moverlay.open .modal-box {
            transform: scale(1) translateY(0)
        }

        .mhead {
            padding: 20px 24px 17px;
            border-bottom: 1px solid var(--border);
            display: flex;
            align-items: center;
            justify-content: space-between;
            flex-shrink: 0
        }

        .mwho {
            display: flex;
            align-items: center;
            gap: 12px
        }

        .mav {
            width: 44px;
            height: 44px;
            border-radius: 12px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 15px;
            font-weight: 800;
            color: #fff;
            flex-shrink: 0
        }

        .mname {
            font-size: .97rem;
            font-weight: 800;
            color: var(--navy)
        }

        .mform-sub {
            font-size: .76rem;
            color: var(--muted);
            margin-top: 2px
        }

        .mclose {
            width: 34px;
            height: 34px;
            border-radius: 9px;
            background: var(--bg);
            border: none;
            cursor: pointer;
            display: flex;
            align-items: center;
            justify-content: center;
            color: var(--muted);
            font-size: 20px;
            transition: background .15s
        }

        .mclose:hover {
            background: var(--border)
        }

        .mbody {
            padding: 22px 24px;
            overflow-y: auto;
            flex: 1
        }

        .pbar-wrap {
            margin-bottom: 22px
        }

        .pbar-top {
            display: flex;
            justify-content: space-between;
            font-size: .74rem;
            color: var(--muted);
            margin-bottom: 6px
        }

        .pbar {
            height: 5px;
            background: var(--border);
            border-radius: 3px;
            overflow: hidden
        }

        .pbar-fill {
            height: 100%;
            background: linear-gradient(90deg, var(--blue), #60a5fa);
            border-radius: 3px;
            transition: width .3s
        }

        .qblock {
            margin-bottom: 26px
        }

        .qlabel {
            display: flex;
            align-items: flex-start;
            gap: 10px;
            margin-bottom: 13px
        }

        .qnum {
            flex-shrink: 0;
            width: 26px;
            height: 26px;
            border-radius: 7px;
            background: var(--blue-lt);
            color: var(--blue);
            font-size: .74rem;
            font-weight: 800;
            display: flex;
            align-items: center;
            justify-content: center;
            margin-top: 1px
        }

        .qtext {
            font-size: .875rem;
            font-weight: 600;
            color: var(--navy);
            line-height: 1.45;
            flex: 1
        }

        .qbadge {
            flex-shrink: 0;
            font-size: .67rem;
            font-weight: 700;
            padding: 3px 9px;
            border-radius: 20px;
            margin-top: 2px
        }

        .qbadge.qt {
            background: var(--blue-lt);
            color: var(--blue)
        }

        .qbadge.ql {
            background: #f5f5f5;
            color: var(--muted)
        }

        .req {
            color: var(--red);
            margin-left: 2px
        }

        .rrow {
            display: flex;
            gap: 8px;
            flex-wrap: wrap
        }

        .ropt input {
            display: none
        }

        .ropt label {
            width: 58px;
            height: 60px;
            border-radius: 12px;
            border: 2px solid var(--border);
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: center;
            cursor: pointer;
            transition: all .15s;
            gap: 3px;
            user-select: none
        }

        .ropt label .rn {
            font-size: 1rem;
            font-weight: 800;
            color: var(--muted)
        }

        .ropt label .rl {
            font-size: .6rem;
            font-weight: 600;
            color: var(--muted);
            text-align: center;
            line-height: 1.1;
            padding: 0 3px
        }

        .ropt input:checked+label {
            background: var(--blue);
            border-color: var(--blue);
            transform: scale(1.06);
            box-shadow: 0 4px 14px rgba(26, 86, 219, .35)
        }

        .ropt input:checked+label .rn,
        .ropt input:checked+label .rl {
            color: #fff
        }

        .ropt label:hover {
            border-color: var(--blue)
        }

        .ropt label:hover .rn,
        .ropt label:hover .rl {
            color: var(--blue)
        }

        .qta {
            width: 100%;
            padding: 12px 14px;
            border: 1.5px solid var(--border);
            border-radius: 11px;
            font-family: inherit;
            font-size: .875rem;
            color: var(--navy);
            resize: vertical;
            min-height: 88px;
            outline: none;
            transition: border-color .15s, box-shadow .15s;
            background: var(--bg)
        }

        .qta:focus {
            border-color: var(--blue);
            background: var(--white);
            box-shadow: 0 0 0 3px rgba(26, 86, 219, .1)
        }

        .mfoot {
            padding: 16px 24px;
            border-top: 1px solid var(--border);
            display: flex;
            align-items: center;
            justify-content: space-between;
            flex-shrink: 0
        }

        .plbl {
            font-size: .78rem;
            color: var(--muted);
            font-weight: 500
        }

        .plbl strong {
            color: var(--navy)
        }

        .bsub {
            padding: 10px 24px;
            border: none;
            border-radius: 10px;
            background: var(--blue);
            color: #fff;
            font-family: inherit;
            font-size: .875rem;
            font-weight: 700;
            cursor: pointer;
            display: flex;
            align-items: center;
            gap: 8px;
            box-shadow: 0 4px 14px rgba(26, 86, 219, .3);
            transition: all .15s
        }

        .bsub:hover {
            background: #1648c0;
            transform: translateY(-1px)
        }

        .bsub:disabled {
            opacity: .6;
            cursor: not-allowed;
            transform: none
        }

        .bcan {
            padding: 10px 16px;
            border: 1px solid var(--border);
            border-radius: 10px;
            background: var(--bg);
            font-family: inherit;
            font-size: .875rem;
            font-weight: 600;
            color: var(--muted);
            cursor: pointer;
            transition: all .15s
        }

        .bcan:hover {
            background: var(--border)
        }

        .vfoot {
            padding: 16px 24px;
            border-top: 1px solid var(--border);
            display: flex;
            justify-content: flex-end;
            flex-shrink: 0
        }

        .readonly-notice {
            display: flex;
            align-items: center;
            gap: 8px;
            background: #fffbeb;
            border: 1px solid #fde68a;
            border-radius: 10px;
            padding: 11px 14px;
            font-size: .8rem;
            color: #92400e;
            margin-bottom: 20px
        }

        .readonly-notice i {
            font-size: 16px;
            flex-shrink: 0
        }

        .qview {
            margin-bottom: 24px
        }

        .qview-resp {
            padding: 11px 14px;
            border-radius: 10px;
            font-size: .875rem;
            margin-top: 10px
        }

        .qview-resp.rating-resp {
            background: var(--blue-lt);
            color: var(--blue);
            font-weight: 700;
            display: inline-flex;
            align-items: center;
            gap: 6px
        }

        .qview-resp.text-resp {
            background: var(--bg);
            color: var(--navy);
            border: 1px solid var(--border);
            display: block;
            white-space: pre-wrap
        }

        .date-tag {
            font-size: .7rem;
            color: var(--muted);
            display: flex;
            align-items: center;
            gap: 4px;
            margin-bottom: 18px
        }

        .date-tag i {
            font-size: 12px
        }

        @keyframes spin {
            to {
                transform: rotate(360deg)
            }
        }

        .spin-wrap {
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 60px 0
        }

        .spin-wrap i {
            font-size: 2rem;
            color: var(--blue);
            animation: spin .7s linear infinite
        }

        .twrap {
            position: fixed;
            bottom: 28px;
            left: 50%;
            transform: translateX(-50%) translateY(20px);
            z-index: 9999;
            opacity: 0;
            pointer-events: none;
            transition: opacity .2s, transform .2s
        }

        .twrap.show {
            opacity: 1;
            transform: translateX(-50%) translateY(0)
        }

        .tmsg {
            padding: 12px 20px;
            border-radius: 12px;
            font-size: .875rem;
            font-weight: 600;
            display: flex;
            align-items: center;
            gap: 9px;
            box-shadow: 0 8px 24px rgba(0, 0, 0, .18);
            white-space: nowrap
        }

        .tmsg.ok {
            background: var(--navy);
            color: #fff
        }

        .tmsg.err {
            background: #fef2f2;
            color: var(--red);
            border: 1px solid #fecaca
        }

        .tmsg i {
            font-size: 18px
        }

        @media(max-width:600px) {
            .hdr {
                padding: 0 14px
            }

            .wrap {
                padding: 20px 12px 60px
            }

            .dept-pill,
            .u-name {
                display: none
            }

            .eitem {
                flex-wrap: wrap
            }

            .iright {
                width: 100%;
                justify-content: flex-end;
                margin-top: 4px
            }

            .ropt label {
                width: 50px;
                height: 54px
            }
        }
    </style>
</head>

<body>

    <header class="hdr">
        <div class="hdr-brand">
            <div class="hdr-logo"><i class='bx bxs-graduation'></i></div>
            <div class="brand">HCC <span>Faculty</span> Portal</div>
        </div>
        <div class="hdr-right">
            <div class="dept-pill"><i class='bx bx-building-house'></i><?= esc($department) ?></div>
            <div class="dropdown">
                <div class="d-flex align-items-center gap-2" style="cursor:pointer" data-bs-toggle="dropdown">
                    <div class="u-av"><?= initials($teacher_name) ?></div>
                    <div class="u-name"><?= esc($teacher_name) ?></div>
                    <i class='bx bx-chevron-down' style="color:var(--muted);font-size:16px"></i>
                </div>
                <ul class="dropdown-menu dropdown-menu-end border-0 shadow-sm mt-1">
                    <li>
                        <div class="dropdown-item-text py-2">
                            <div class="fw-bold small"><?= esc($teacher_name) ?></div>
                            <div class="text-muted" style="font-size:.74rem"><?= esc($employee_id) ?> ·
                                <?= esc($branch) ?></div>
                        </div>
                    </li>
                    <li>
                        <hr class="dropdown-divider my-1">
                    </li>
                    <li><a class="dropdown-item text-danger small" href="?logout=1"><i
                                class='bx bx-log-out me-2'></i>Sign Out</a></li>
                </ul>
            </div>
        </div>
    </header>

    <div class="wrap">

        <?php if ($db_error): ?>
            <div class="db-err"><i class='bx bx-error-circle'></i>
                <div>
                    <strong>Database error — please contact your administrator.</strong>
                    <code><?= esc($db_error) ?></code>
                </div>
            </div>
        <?php endif; ?>

        <div class="banner">
            <div class="banner-l">
                <h2>Hello, <?= esc(explode(' ', $teacher_name)[0]) ?>! 👋</h2>
                <p id="bannerMsg">
                    <?php if ($pending_count === 0 && $total_tasks > 0): ?>You've completed all evaluations. Excellent work!
                    <?php elseif ($pending_count === 1): ?>You have <strong style="color:var(--gold)">1 evaluation</strong>
                        left to submit.
                    <?php elseif ($total_tasks === 0): ?>No active evaluation forms assigned right now.
                    <?php else: ?>You have <strong style="color:var(--gold)"><?= $pending_count ?> evaluations</strong>
                        pending.<?php endif; ?>
                </p>
            </div>
            <div class="banner-r">
                <div class="prog-wrap">
                    <svg class="prog-svg" width="90" height="90" viewBox="0 0 90 90">
                        <circle class="prog-track" cx="45" cy="45" r="<?= $R ?>" />
                        <circle class="prog-fill" id="progRing" cx="45" cy="45" r="<?= $R ?>"
                            stroke-dasharray="<?= $C ?>" stroke-dashoffset="<?= $off ?>" />
                    </svg>
                    <div class="prog-lbl">
                        <span class="prog-num" id="progNum"><?= $done_count ?>/<?= $total_tasks ?></span>
                        <span class="prog-sub">done</span>
                    </div>
                </div>
            </div>
        </div>

        <div class="tabs">
            <button class="tab ptab on" id="tabP" onclick="switchTab('p')">
                <i class='bx bx-time-five'></i> Pending <span class="n" id="nP"><?= $pending_count ?></span>
            </button>
            <button class="tab" id="tabD" onclick="switchTab('d')">
                <i class='bx bx-check-circle'></i> Completed <span class="n" id="nD"><?= $done_count ?></span>
            </button>
            <button class="tab vtab" id="tabV" onclick="switchTab('v')">
                <i class='bx bx-receipt'></i> My Submissions <span class="n" id="nV">—</span>
            </button>
        </div>

        <div class="frow" id="filterRow">
            <div class="sbox"><i class='bx bx-search'></i><input type="text" id="srch" placeholder="Search colleague..."
                    oninput="filt()"></div>
            <?php if (count($active_forms) > 1): ?>
                <select class="fsel" id="fform" onchange="filt()">
                    <option value="">All Forms</option>
                        <?php foreach ($active_forms as $f): ?><option value="<?= $f['id'] ?>"><?= esc($f['title']) ?></option><?php endforeach; ?>
            </select>
    <?php endif; ?>
</div>

<!-- PENDING -->
<div id="panP">
<?php if (empty($peers) && !$db_error): ?>
        <div class="estate"><i class='bx bx-group'></i><h5>No colleagues found</h5><p>No other active faculty in your department (<?= esc($department) ?>).</p></div>
<?php elseif (empty($active_forms) && !$db_error): ?>
        <div class="estate"><i class='bx bx-file-blank'></i><h5>No active evaluation forms</h5><p>There are no peer-to-peer evaluation forms open right now.</p></div>
<?php elseif (empty($pending_items)): ?>
        <div class="alldone"><i class='bx bx-check-circle'></i><h5>All done! 🎉</h5><p>You've submitted evaluations for all colleagues in <?= esc($department) ?>.</p></div>
<?php else: ?>
        <div class="elist" id="listP">
        <?php foreach ($pending_items as $ix => $item):
            $p = $item['peer'];
            $f = $item['form'];
            $c = avatarColor((int) $p['fac_id'], $COLORS);
            $in = initials($p['full_name']);
            $nJs = addslashes(esc($p['full_name']));
            $eJs = addslashes(esc($p['employee_id']));
            ?>
                <div class="eitem" data-name="<?= esc(strtolower($p['full_name'])) ?>" data-fid="<?= $f['id'] ?>" style="animation-delay:<?= $ix * .04 ?>s">
                    <div class="iav" style="background:<?= $c ?>"><?= $in ?></div>
                    <div class="iinfo">
                        <div class="iname"><?= esc($p['full_name']) ?></div>
                        <div class="imeta">
                            <span class="itag"><i class='bx bx-id-card'></i><?= esc($p['employee_id']) ?></span>
                            <span class="isep">·</span><span class="itag"><i class='bx bx-book-open'></i><?= esc($p['program']) ?></span>
                            <span class="isep">·</span><span class="itag"><i class='bx bx-building'></i><?= esc($p['branch']) ?></span>
                            <?php if (count($active_forms) > 1): ?><span class="isep">·</span><span class="iform"><?= esc($f['title']) ?></span><?php endif; ?>
                        </div>
                    </div>
                    <div class="iright">
                        <span class="chip pend"><i class='bx bx-time'></i> Pending</span>
                        <button class="brate go" onclick="openM(<?= $p['fac_id'] ?>,<?= $f['id'] ?>,'<?= $nJs ?>','<?= $eJs ?>','<?= $c ?>','<?= $in ?>')">
                            <i class='bx bx-star'></i> Evaluate
                        </button>
                    </div>
                </div>
        <?php endforeach; ?>
        </div>
<?php endif; ?>
</div>

<!-- COMPLETED -->
<div id="panD" style="display:none">
<?php if (empty($done_items)): ?>
        <div class="estate"><i class='bx bx-notepad'></i><h5>No completed evaluations yet</h5><p>Switch to Pending to start rating your colleagues.</p></div>
<?php else: ?>
        <div class="elist" id="listD">
        <?php foreach ($done_items as $ix => $item):
            $p = $item['peer'];
            $f = $item['form'];
            $c = avatarColor((int) $p['fac_id'], $COLORS);
            $in = initials($p['full_name']);
            $nJs = addslashes(esc($p['full_name']));
            ?>
                <div class="eitem done" data-name="<?= esc(strtolower($p['full_name'])) ?>" data-fid="<?= $f['id'] ?>" style="animation-delay:<?= $ix * .04 ?>s">
                    <div class="iav" style="background:<?= $c ?>;opacity:.75"><?= $in ?></div>
                    <div class="iinfo">
                        <div class="iname"><?= esc($p['full_name']) ?></div>
                        <div class="imeta">
                            <span class="itag"><i class='bx bx-id-card'></i><?= esc($p['employee_id']) ?></span>
                            <span class="isep">·</span><span class="itag"><i class='bx bx-book-open'></i><?= esc($p['program']) ?></span>
                            <span class="isep">·</span><span class="itag"><i class='bx bx-building'></i><?= esc($p['branch']) ?></span>
                            <?php if (count($active_forms) > 1): ?><span class="isep">·</span><span class="iform"><?= esc($f['title']) ?></span><?php endif; ?>
                        </div>
                    </div>
                    <div class="iright">
                        <span class="chip done"><i class='bx bx-check-circle'></i> Submitted</span>
                        <button class="brate view-btn" onclick="openViewModal(<?= $p['fac_id'] ?>,<?= $f['id'] ?>,'<?= $nJs ?>','<?= $in ?>','<?= $c ?>')">
                            <i class='bx bx-show'></i> View
                        </button>
                    </div>
                </div>
        <?php endforeach; ?>
        </div>
<?php endif; ?>
</div>

<!-- MY SUBMISSIONS -->
<div id="panV" style="display:none">
    <div id="vLoading" class="spin-wrap"><i class='bx bx-loader-alt'></i></div>
    <div class="elist" id="listV" style="display:none"></div>
    <div class="estate" id="vEmpty" style="display:none"><i class='bx bx-inbox'></i><h5>No submissions yet</h5><p>Complete evaluations to see them here.</p></div>
</div>

</div>

<!-- EVALUATE MODAL -->
<div class="moverlay" id="mOv" onclick="outsideClose(event)">
  <div class="modal-box">
    <div class="mhead">
        <div class="mwho"><div class="mav" id="mAv"></div><div><div class="mname" id="mName"></div><div class="mform-sub" id="mFormSub"></div></div></div>
        <button class="mclose" onclick="closeM()"><i class='bx bx-x'></i></button>
    </div>
    <div class="mbody" id="mBody"></div>
    <div class="mfoot">
        <div class="plbl"><strong id="mAns">0</strong> / <strong id="mTot">0</strong> <span>answered</span></div>
        <div style="display:flex;gap:8px">
            <button class="bcan" onclick="closeM()">Cancel</button>
            <button class="bsub" id="bSub" onclick="doSubmit()"><i class='bx bx-send'></i> Submit Evaluation</button>
        </div>
    </div>
  </div>
</div>

<!-- VIEW-ONLY MODAL -->
<div class="moverlay" id="vOv" onclick="outsideCloseView(event)">
  <div class="modal-box">
    <div class="mhead">
        <div class="mwho"><div class="mav" id="vAv"></div><div><div class="mname" id="vName"></div><div class="mform-sub" id="vFormSub"></div></div></div>
        <button class="mclose" onclick="closeView()"><i class='bx bx-x'></i></button>
    </div>
    <div class="mbody" id="vBody"><div class="spin-wrap"><i class='bx bx-loader-alt'></i></div></div>
    <div class="vfoot"><button class="bcan" onclick="closeView()"><i class='bx bx-x me-1'></i>Close</button></div>
  </div>
</div>

<div class="twrap" id="tw"><div class="tmsg" id="tm"><i id="ti" class='bx bx-check-circle'></i><span id="tt"></span></div></div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
<script>
const FORMS     = <?= json_encode(array_column($active_forms, null, 'id'), JSON_HEX_TAG) ?>;
const FQ        = <?= json_encode($form_questions, JSON_HEX_TAG) ?>;
const PEERS_MAP = <?= json_encode(array_column($peers, null, 'fac_id'), JSON_HEX_TAG) ?>;
const COLORS    = ['#1a56db','#059669','#d97706','#7c3aed','#dc2626','#0891b2','#be185d','#0f766e'];
const C_TOTAL   = <?= $C ?>;
let done=<?= $done_count ?>,total=<?= $total_tasks ?>,curFac,curForm;

function switchTab(t){
    ['p','d','v'].forEach(id=>document.getElementById('pan'+id.toUpperCase()).style.display=(t===id)?'':'none');
    document.getElementById('tabP').className='tab ptab'+(t==='p'?' on':'');
    document.getElementById('tabD').className='tab'     +(t==='d'?' on':'');
    document.getElementById('tabV').className='tab vtab'+(t==='v'?' on':'');
    document.getElementById('filterRow').style.display=(t==='v')?'none':'';
    if(t==='v') loadSubmissions(); else filt();
}
function filt(){
    const q=document.getElementById('srch').value.toLowerCase().trim();
    const fv=document.getElementById('fform')?.value||'';
    ['listP','listD'].forEach(lid=>{
        const l=document.getElementById(lid);if(!l)return;
        l.querySelectorAll('.eitem').forEach(el=>el.style.display=(el.dataset.name.includes(q)&&(!fv||el.dataset.fid===fv))?'':'none');
    });
}
function openM(facId,formId,name,empId,color,init){
    curFac=facId;curForm=formId;
    const form=FORMS[formId],qs=FQ[formId]||[];
    const labels=form.rating_labels.split(','),min=+form.rating_min,max=+form.rating_max;
    document.getElementById('mAv').textContent=init;
    document.getElementById('mAv').style.background=color;
    document.getElementById('mName').textContent=name;
    document.getElementById('mFormSub').textContent=form.title+' · '+form.school_year+' · '+form.semester+' Semester';
    document.getElementById('mBody').innerHTML=
        '<div class="pbar-wrap"><div class="pbar-top"><span id="pTxt">0 of '+qs.length+' answered</span><span id="pPct">0%</span></div><div class="pbar"><div class="pbar-fill" id="pFill" style="width:0%"></div></div></div>'+
        qs.map((q,i)=>{
            if(q.type==='quantitative'){
                const btns=Array.from({length:max-min+1},(_,k)=>{
                    const v=min+k,l=labels[k]||v;
                    return '<div class="ropt"><input type="radio" name="q'+q.id+'" id="q'+q.id+'v'+v+'" value="'+v+'" onchange="calcProg()"><label for="q'+q.id+'v'+v+'"><span class="rn">'+v+'</span><span class="rl">'+l+'</span></label></div>';
                }).join('');
                return '<div class="qblock"><div class="qlabel"><div class="qnum">'+(i+1)+'</div><div class="qtext">'+q.question+'<span class="req">*</span></div><span class="qbadge qt">Rating</span></div><div class="rrow">'+btns+'</div></div>';
            } else {
                return '<div class="qblock"><div class="qlabel"><div class="qnum">'+(i+1)+'</div><div class="qtext">'+q.question+'</div><span class="qbadge ql">Open-ended</span></div><textarea class="qta" name="q'+q.id+'" placeholder="Write your response\u2026" oninput="calcProg()"></textarea></div>';
            }
        }).join('');
    document.getElementById('mTot').textContent=qs.length;
    document.getElementById('bSub').disabled=false;
    document.getElementById('bSub').innerHTML="<i class='bx bx-send'></i> Submit Evaluation";
    calcProg();
    document.getElementById('mOv').classList.add('open');
    document.body.style.overflow='hidden';
}
function closeM(){document.getElementById('mOv').classList.remove('open');document.body.style.overflow='';}
function outsideClose(e){if(e.target===document.getElementById('mOv'))closeM();}
function calcProg(){
    const qs=FQ[curForm]||[];let ans=0;
    qs.forEach(q=>{
        if(q.type==='quantitative'){if(document.querySelector('input[name="q'+q.id+'"]:checked'))ans++;}
        else{const t=document.querySelector('textarea[name="q'+q.id+'"]');if(t&&t.value.trim())ans++;}
    });
    const pct=qs.length?Math.round(ans/qs.length*100):0;
    document.getElementById('pTxt').textContent=ans+' of '+qs.length+' answered';
    document.getElementById('pPct').textContent=pct+'%';
    document.getElementById('pFill').style.width=pct+'%';
    document.getElementById('mAns').textContent=ans;
}
function doSubmit(){
    const qs=FQ[curForm]||[];
    for(const q of qs){
        if(q.type==='quantitative'&&!document.querySelector('input[name="q'+q.id+'"]:checked')){
            showToast('Please answer all required rating questions (*)','err');
            document.querySelector('input[name="q'+q.id+'"]')?.closest('.qblock')?.scrollIntoView({behavior:'smooth',block:'center'});
            return;
        }
    }
    const btn=document.getElementById('bSub');
    btn.disabled=true;btn.innerHTML="<i class='bx bx-loader-alt bx-spin'></i> Submitting\u2026";
    const responses=qs.map(q=>({
        question_id:q.id,type:q.type,
        value:q.type==='quantitative'?document.querySelector('input[name="q'+q.id+'"]:checked')?.value:document.querySelector('textarea[name="q'+q.id+'"]')?.value||''
    }));
    fetch('save_evaluation.php',{method:'POST',headers:{'Content-Type':'application/json'},body:JSON.stringify({form_id:curForm,evaluatee_id:curFac,responses})})
    .then(r=>r.json())
    .then(d=>{
        if(d.success){closeM();afterSubmit(curFac,curForm);showToast('Evaluation submitted successfully! \u2713','ok');submissionsLoaded=false;}
        else{showToast(d.message||'Submission failed.','err');btn.disabled=false;btn.innerHTML="<i class='bx bx-send'></i> Submit Evaluation";}
    })
    .catch(e=>{showToast('Network error: '+e.message,'err');btn.disabled=false;btn.innerHTML="<i class='bx bx-send'></i> Submit Evaluation";});
}
function afterSubmit(facId,formId){
    done++;
    document.getElementById('nP').textContent=Math.max(0,total-done);
    document.getElementById('nD').textContent=done;
    const fill=document.getElementById('progRing');
    if(fill)fill.style.strokeDashoffset=(C_TOTAL*(1-done/total)).toFixed(2);
    const num=document.getElementById('progNum');
    if(num)num.textContent=done+'/'+total;
    const rem=Math.max(0,total-done),bm=document.getElementById('bannerMsg');
    if(bm)bm.innerHTML=rem===0?"You've completed all evaluations. Excellent work!":rem===1?"You have <strong style=\"color:var(--gold)\">1 evaluation</strong> left to submit.":"You have <strong style=\"color:var(--gold)\">"+rem+" evaluations</strong> pending.";
    const lP=document.getElementById('listP');
    if(lP){
        const el=[...lP.querySelectorAll('.eitem')].find(e=>e.querySelector('[onclick*="openM('+facId+','+formId+'"]'));
        if(el){el.style.transition='opacity .3s,transform .3s';el.style.opacity='0';el.style.transform='translateX(20px)';
            setTimeout(()=>{el.remove();if(!lP.querySelector('.eitem'))lP.parentElement.innerHTML='<div class="alldone"><i class=\'bx bx-check-circle\'></i><h5>All done! \uD83C\uDF89</h5><p>All evaluations submitted.</p></div>';},320);}
    }
    const lD=document.getElementById('listD'),peer=PEERS_MAP[facId],form=FORMS[formId];
    if(lD&&peer){
        const color=COLORS[facId%COLORS.length];
        const init=peer.full_name.split(' ').slice(0,2).map(w=>w[0].toUpperCase()).join('');
        const safeN=peer.full_name.replace(/'/g,"\\'");
        const el=document.createElement('div');
        el.className='eitem done';el.dataset.name=peer.full_name.toLowerCase();el.dataset.fid=formId;el.style.opacity='0';
        el.innerHTML='<div class="iav" style="background:'+color+';opacity:.75">'+init+'</div><div class="iinfo"><div class="iname">'+peer.full_name+'</div><div class="imeta"><span class="itag"><i class=\'bx bx-id-card\'></i>'+peer.employee_id+'</span><span class="isep">\xB7</span><span class="itag"><i class=\'bx bx-book-open\'></i>'+peer.program+'</span><span class="isep">\xB7</span><span class="itag"><i class=\'bx bx-building\'></i>'+peer.branch+'</span></div></div><div class="iright"><span class="chip done"><i class=\'bx bx-check-circle\'></i> Submitted</span><button class="brate view-btn" onclick="openViewModal('+facId+','+formId+',\''+safeN+'\',\''+init+'\',\''+color+'\')"><i class=\'bx bx-show\'></i> View</button></div>';
        lD.prepend(el);requestAnimationFrame(()=>{el.style.transition='opacity .3s';el.style.opacity='1';});
        lD.parentElement.querySelector('.estate')?.remove();
    }
}
function openViewModal(facId,formId,name,init,color){
    document.getElementById('vAv').textContent=init;document.getElementById('vAv').style.background=color;
    document.getElementById('vName').textContent=name;
    const f=FORMS[formId];document.getElementById('vFormSub').textContent=f?(f.title+' \xB7 '+f.school_year+' \xB7 '+f.semester+' Semester'):'Evaluation';
    document.getElementById('vBody').innerHTML='<div class="spin-wrap"><i class=\'bx bx-loader-alt\'></i></div>';
    document.getElementById('vOv').classList.add('open');document.body.style.overflow='hidden';
    fetch('get_my_evaluations.php').then(r=>r.json()).then(d=>{
        if(!d.success){setViewErr(d.message);return;}
        const sub=d.data.find(s=>+s.evaluatee_id===+facId&&+s.form_id===+formId);
        if(!sub){setViewErr('Submission data not found.');return;}
        renderViewBody(sub);
    }).catch(e=>setViewErr('Network error: '+e.message));
}
function closeView(){document.getElementById('vOv').classList.remove('open');document.body.style.overflow='';}
function outsideCloseView(e){if(e.target===document.getElementById('vOv'))closeView();}
function renderViewBody(sub){
    const date=sub.submitted_at?new Date(sub.submitted_at).toLocaleDateString('en-PH',{year:'numeric',month:'long',day:'numeric',hour:'2-digit',minute:'2-digit'}):'';
    const items=sub.responses.map((r,i)=>{
        const isR=r.type==='quantitative';
        const resp=isR?'<div class="qview-resp rating-resp"><i class=\'bx bx-star\'></i>'+xss(r.response)+'</div>':'<div class="qview-resp text-resp">'+(xss(r.response)||'<em style="color:var(--muted)">No response provided.</em>')+'</div>';
        return '<div class="qview"><div class="qlabel"><div class="qnum">'+(i+1)+'</div><div class="qtext">'+xss(r.question)+'</div><span class="qbadge '+(isR?'qt':'ql')+'">'+(isR?'Rating':'Open-ended')+'</span></div>'+resp+'</div>';
    }).join('<hr style="margin:0 0 20px;border-color:var(--border)">');
    document.getElementById('vBody').innerHTML='<div class="readonly-notice"><i class=\'bx bx-lock-alt\'></i><span>Read-only \u2014 submitted evaluations cannot be edited.</span></div>'+(date?'<div class="date-tag"><i class=\'bx bx-calendar-check\'></i>Submitted on '+date+'</div>':'')+items;
}
function setViewErr(msg){document.getElementById('vBody').innerHTML='<div class="estate" style="border:none;padding:40px 0"><i class=\'bx bx-error-circle\' style="opacity:.3"></i><h5>Error</h5><p>'+xss(msg)+'</p></div>';}
let submissionsLoaded=false;
function loadSubmissions(){
    if(submissionsLoaded)return;
    document.getElementById('vLoading').style.display='';document.getElementById('listV').style.display='none';document.getElementById('vEmpty').style.display='none';
    fetch('get_my_evaluations.php').then(r=>r.json()).then(d=>{
        document.getElementById('vLoading').style.display='none';
        if(!d.success||!d.data.length){document.getElementById('vEmpty').style.display='';document.getElementById('nV').textContent='0';return;}
        const list=document.getElementById('listV');document.getElementById('nV').textContent=d.data.length;
        list.innerHTML=d.data.map((sub,ix)=>{
            const color=COLORS[sub.evaluatee_id%COLORS.length];
            const init=sub.evaluatee_name.split(' ').slice(0,2).map(w=>w[0].toUpperCase()).join('');
            const date=sub.submitted_at?new Date(sub.submitted_at).toLocaleDateString('en-PH',{month:'short',day:'numeric',year:'numeric'}):'';
            const safeN=sub.evaluatee_name.replace(/'/g,"\\'");
            return '<div class="eitem done" style="animation-delay:'+(ix*.04)+'s"><div class="iav" style="background:'+color+';opacity:.8">'+init+'</div><div class="iinfo"><div class="iname">'+xss(sub.evaluatee_name)+'</div><div class="imeta"><span class="itag"><i class=\'bx bx-id-card\'></i>'+xss(sub.evaluatee_emp_id)+'</span><span class="isep">\xB7</span><span class="itag"><i class=\'bx bx-book-open\'></i>'+xss(sub.evaluatee_program)+'</span><span class="isep">\xB7</span><span class="iform">'+xss(sub.form_title)+'</span></div>'+(date?'<div class="date-tag"><i class=\'bx bx-calendar-check\'></i>Submitted '+date+'</div>':'')+'</div><div class="iright"><span class="chip done"><i class=\'bx bx-check-circle\'></i> Submitted</span><button class="brate view-btn" onclick="openViewModal('+sub.evaluatee_id+','+sub.form_id+',\''+safeN+'\',\''+init+'\',\''+color+'\')"><i class=\'bx bx-show\'></i> View</button></div></div>';
        }).join('');
        list.style.display='';submissionsLoaded=true;
    }).catch(()=>{document.getElementById('vLoading').style.display='none';document.getElementById('vEmpty').style.display='';});
}
function xss(s){return String(s||'').replace(/&/g,'&amp;').replace(/</g,'&lt;').replace(/>/g,'&gt;').replace(/"/g,'&quot;');}
let _tt;
function showToast(msg,type='ok'){
    const w=document.getElementById('tw'),m=document.getElementById('tm'),i=document.getElementById('ti');
    m.className='tmsg '+type;i.className=type==='ok'?'bx bx-check-circle':'bx bx-error-circle';
    document.getElementById('tt').textContent=msg;w.classList.add('show');clearTimeout(_tt);
    _tt=setTimeout(()=>w.classList.remove('show'),4000);
}
</script>
</body>
</html>