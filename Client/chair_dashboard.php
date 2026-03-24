<?php
// File: chair_dashboard.php
// Path: /Client/chair_dashboard.php

session_start();
if (!isset($_SESSION['client_id']) || $_SESSION['client_type'] !== 'faculty') {
    header('Location: ../Client_login.php?role=teacher');
    exit;
}
if ($_SESSION['client_role'] !== 'programchair') {
    header('Location: teacher_dashboard.php');
    exit;
}

$fac_id = $_SESSION['client_id'];
$fac_name = $_SESSION['client_name'];
$fac_dept = $_SESSION['client_department'] ?? '';
$fac_branch = $_SESSION['client_branch'] ?? '';
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Program Chair Portal — Holy Cross College</title>
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
            background: linear-gradient(180deg, var(--navy) 0%, #1a3a6e 100%);
            display: flex;
            flex-direction: column;
            z-index: 200;
            overflow: hidden;
            transition: transform .3s ease;
        }

        .sidebar-brand {
            padding: 1.5rem 1.4rem 1rem;
            border-bottom: 1px solid rgba(200, 169, 110, .2);
            display: flex;
            align-items: center;
            gap: 10px;
        }

        .brand-cross {
            width: 22px;
            height: 26px;
            flex-shrink: 0;
            position: relative;
        }

        .brand-cross::before,
        .brand-cross::after {
            content: '';
            position: absolute;
            background: var(--gold);
            border-radius: 2px;
        }

        .brand-cross::before {
            width: 4px;
            height: 100%;
            left: 50%;
            transform: translateX(-50%);
        }

        .brand-cross::after {
            width: 100%;
            height: 4px;
            top: 28%;
            transform: translateY(-50%);
        }

        .brand-text {
            font-family: 'Cormorant Garamond', serif;
            font-size: 1.2rem;
            font-weight: 300;
            color: var(--white);
            white-space: nowrap;
        }

        .brand-text span {
            font-style: italic;
            color: var(--gold);
        }

        .faculty-card {
            margin: 1rem .8rem;
            background: rgba(200, 169, 110, .08);
            border: 1px solid rgba(200, 169, 110, .2);
            border-radius: 8px;
            padding: .9rem 1rem;
        }

        .faculty-avatar {
            width: 40px;
            height: 40px;
            border-radius: 50%;
            background: linear-gradient(135deg, var(--gold), #a07840);
            display: flex;
            align-items: center;
            justify-content: center;
            font-family: 'Cormorant Garamond', serif;
            font-size: 1rem;
            font-weight: 600;
            color: var(--white);
            margin-bottom: .5rem;
        }

        .faculty-name {
            font-size: .82rem;
            font-weight: 500;
            color: var(--white);
        }

        .faculty-meta {
            font-size: .68rem;
            color: rgba(200, 169, 110, .6);
            margin-top: 2px;
        }

        .role-badge {
            display: inline-block;
            margin-top: 5px;
            font-size: .62rem;
            letter-spacing: .08em;
            text-transform: uppercase;
            background: rgba(200, 169, 110, .2);
            border: 1px solid rgba(200, 169, 110, .5);
            color: var(--gold);
            border-radius: 3px;
            padding: 2px 7px;
        }

        .nav-list {
            padding: .5rem 0;
            flex: 1;
        }

        .nav-link-item {
            display: flex;
            align-items: center;
            gap: 10px;
            padding: .65rem 1.4rem;
            color: rgba(200, 169, 110, .5);
            text-decoration: none;
            font-size: .84rem;
            transition: all .2s;
            border-left: 3px solid transparent;
            cursor: pointer;
        }

        .nav-link-item:hover {
            color: var(--gold);
            background: rgba(200, 169, 110, .05);
        }

        .nav-link-item.active {
            color: var(--gold);
            border-left-color: var(--gold);
            background: rgba(200, 169, 110, .08);
        }

        .nav-link-item i {
            font-size: 1.1rem;
            flex-shrink: 0;
        }

        .sidebar-footer {
            padding: 1rem .8rem;
            border-top: 1px solid rgba(200, 169, 110, .15);
        }

        .logout-btn {
            display: flex;
            align-items: center;
            gap: 10px;
            padding: .65rem 1rem;
            width: 100%;
            background: rgba(220, 53, 69, .12);
            border: 1px solid rgba(220, 53, 69, .2);
            border-radius: 6px;
            color: #ff8a8a;
            font-size: .83rem;
            font-family: 'DM Sans', sans-serif;
            cursor: pointer;
            text-decoration: none;
            transition: all .2s;
        }

        .logout-btn:hover {
            background: rgba(220, 53, 69, .22);
            color: #ffaaaa;
        }

        /* ── OVERLAY (mobile) ── */
        .sidebar-overlay {
            display: none;
            position: fixed;
            inset: 0;
            background: rgba(0, 0, 0, .45);
            z-index: 199;
        }

        .sidebar-overlay.show {
            display: block;
        }

        /* ── TOPBAR (mobile hamburger) ── */
        .mobile-topbar {
            display: none;
            position: fixed;
            top: 0;
            left: 0;
            right: 0;
            height: 56px;
            background: var(--navy);
            align-items: center;
            justify-content: space-between;
            padding: 0 1rem;
            z-index: 190;
            box-shadow: 0 2px 12px rgba(0, 0, 0, .25);
        }

        .mobile-topbar .brand-text {
            font-size: 1rem;
        }

        .hamburger {
            background: none;
            border: none;
            color: var(--gold);
            font-size: 1.6rem;
            cursor: pointer;
            line-height: 1;
            display: flex;
            align-items: center;
        }

        /* ── MAIN CONTENT ── */
        .main-content {
            margin-left: var(--sidebar-w);
            min-height: 100vh;
            padding: 2rem;
        }

        .topbar {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 2rem;
        }

        .page-title {
            font-family: 'Cormorant Garamond', serif;
            font-size: 1.8rem;
            font-weight: 300;
        }

        .page-title span {
            font-style: italic;
            color: var(--gold);
        }

        .portal-tabs {
            display: flex;
            border-bottom: 2px solid var(--ice);
            margin-bottom: 1.5rem;
            overflow-x: auto;
            -webkit-overflow-scrolling: touch;
        }

        .portal-tabs::-webkit-scrollbar {
            display: none;
        }

        .portal-tab {
            padding: .7rem 1.4rem;
            background: transparent;
            border: none;
            font-family: 'DM Sans', sans-serif;
            font-size: .85rem;
            color: var(--text-light);
            cursor: pointer;
            border-bottom: 3px solid transparent;
            margin-bottom: -2px;
            transition: all .2s;
            display: flex;
            align-items: center;
            gap: 7px;
            white-space: nowrap;
            flex-shrink: 0;
        }

        .portal-tab:hover {
            color: var(--mid-blue);
        }

        .portal-tab.active {
            color: var(--mid-blue);
            border-bottom-color: var(--mid-blue);
            font-weight: 500;
        }

        .tab-badge {
            border-radius: 10px;
            padding: 1px 7px;
            font-size: .68rem;
            color: var(--white);
        }

        /* ── STAT CARDS ── */
        .stat-card {
            background: var(--white);
            border-radius: 8px;
            padding: 1.2rem 1.4rem;
            border: 1px solid var(--ice);
            display: flex;
            justify-content: space-between;
            align-items: center;
        }

        .stat-num {
            font-family: 'Cormorant Garamond', serif;
            font-size: 2rem;
            font-weight: 300;
            color: var(--mid-blue);
        }

        .stat-lbl {
            font-size: .72rem;
            letter-spacing: .1em;
            text-transform: uppercase;
            color: var(--text-light);
            margin-top: 4px;
        }

        .stat-icon {
            width: 44px;
            height: 44px;
            border-radius: 8px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 1.3rem;
        }

        /* ── PANELS ── */
        .panel {
            background: var(--white);
            border: 1px solid var(--ice);
            border-radius: 8px;
            overflow: hidden;
            margin-bottom: 1.5rem;
        }

        .panel-header {
            padding: 1rem 1.4rem;
            border-bottom: 1px solid var(--ice);
            display: flex;
            justify-content: space-between;
            align-items: center;
            flex-wrap: wrap;
            gap: .5rem;
        }

        .panel-title {
            font-family: 'Cormorant Garamond', serif;
            font-size: 1.15rem;
            font-weight: 400;
        }

        .panel-body {
            padding: 1.4rem;
        }

        /* ── STAFF GRID ── */
        .staff-grid {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(260px, 1fr));
            gap: 1rem;
        }

        .staff-card {
            border: 1px solid var(--ice);
            border-radius: 8px;
            padding: 1.2rem;
            background: var(--frost);
            position: relative;
            overflow: hidden;
            transition: box-shadow .2s, transform .2s;
        }

        .staff-card:hover {
            box-shadow: 0 4px 20px rgba(26, 74, 138, .1);
            transform: translateY(-2px);
        }

        .staff-card::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            height: 3px;
            background: linear-gradient(90deg, var(--gold), #a07840);
        }

        .staff-card.evaluated {
            background: linear-gradient(145deg, #f0faf4, #e8f5ed);
            border-color: rgba(40, 167, 69, .25);
        }

        .staff-card.evaluated::before {
            background: linear-gradient(90deg, #28a745, #1a7a4a);
        }

        .staff-avatar {
            width: 48px;
            height: 48px;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            font-family: 'Cormorant Garamond', serif;
            font-size: 1.1rem;
            font-weight: 600;
            color: white;
            flex-shrink: 0;
        }

        .staff-avatar.pending {
            background: linear-gradient(135deg, var(--gold), #a07840);
        }

        .staff-avatar.done {
            background: linear-gradient(135deg, #28a745, #1a7a4a);
        }

        .staff-name {
            font-size: .92rem;
            font-weight: 500;
            color: var(--text-dark);
        }

        .staff-card.evaluated .staff-name {
            color: var(--green);
        }

        .staff-dept {
            font-size: .75rem;
            color: var(--text-light);
            margin-top: 2px;
        }

        .eval-stamp {
            position: absolute;
            top: 10px;
            right: 12px;
            font-size: .6rem;
            font-weight: 700;
            letter-spacing: .1em;
            text-transform: uppercase;
            color: rgba(40, 167, 69, .5);
            border: 1.5px solid rgba(40, 167, 69, .3);
            border-radius: 4px;
            padding: 2px 7px;
            transform: rotate(3deg);
            pointer-events: none;
        }

        .btn-evaluate {
            width: 100%;
            padding: 9px;
            background: linear-gradient(135deg, var(--gold), #a07840);
            color: var(--white);
            border: none;
            border-radius: 6px;
            font-family: 'DM Sans', sans-serif;
            font-size: .82rem;
            font-weight: 500;
            cursor: pointer;
            transition: all .25s;
            margin-top: 10px;
        }

        .btn-evaluate:hover {
            opacity: .9;
            transform: translateY(-1px);
        }

        .evaluated-badge {
            width: 100%;
            padding: 9px;
            text-align: center;
            background: linear-gradient(135deg, rgba(40, 167, 69, .12), rgba(26, 122, 74, .08));
            border: 1.5px solid rgba(40, 167, 69, .35);
            border-radius: 6px;
            font-size: .82rem;
            color: var(--green);
            font-weight: 600;
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 6px;
            margin-top: 10px;
        }

        .check-icon {
            width: 18px;
            height: 18px;
            border-radius: 50%;
            background: var(--green-mid);
            color: white;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: .65rem;
            flex-shrink: 0;
        }

        /* ── TABLE ── */
        .sub-table {
            width: 100%;
        }

        .sub-table th {
            font-size: .75rem;
            text-transform: uppercase;
            letter-spacing: .08em;
            font-weight: 600;
            color: var(--text-light);
            padding: .7rem 1rem;
            background: var(--frost);
            border-bottom: 1px solid var(--ice);
        }

        .sub-table td {
            padding: .8rem 1rem;
            border-bottom: 1px solid var(--ice);
            font-size: .88rem;
            vertical-align: middle;
        }

        .sub-table tr:last-child td {
            border-bottom: none;
        }

        .sub-table tbody tr {
            cursor: pointer;
            transition: background .15s;
        }

        .sub-table tbody tr:hover {
            background: var(--frost);
        }

        /* Responsive table scroll */
        .table-responsive-wrap {
            overflow-x: auto;
            -webkit-overflow-scrolling: touch;
        }

        /* ── EVAL MODAL ── */
        .eval-overlay {
            position: fixed;
            inset: 0;
            background: rgba(10, 31, 60, .7);
            backdrop-filter: blur(4px);
            z-index: 999;
            display: none;
            align-items: center;
            justify-content: center;
            padding: 1rem;
        }

        .eval-overlay.show {
            display: flex;
            animation: fadeIn .25s ease;
        }

        @keyframes fadeIn {
            from {
                opacity: 0
            }

            to {
                opacity: 1
            }
        }

        .eval-modal {
            background: var(--white);
            border-radius: 12px;
            width: 100%;
            max-width: 700px;
            max-height: 90vh;
            overflow-y: auto;
            box-shadow: 0 30px 60px rgba(5, 15, 35, .4);
            animation: slideUp .3s cubic-bezier(.22, 1, .36, 1);
        }

        @keyframes slideUp {
            from {
                transform: translateY(24px);
                opacity: 0
            }

            to {
                transform: translateY(0);
                opacity: 1
            }
        }

        .modal-hd {
            background: linear-gradient(135deg, #1a1000, #6b4c00);
            padding: 1.4rem 1.6rem;
            display: flex;
            justify-content: space-between;
            align-items: flex-start;
            position: sticky;
            top: 0;
            z-index: 10;
        }

        .modal-title {
            font-family: 'Cormorant Garamond', serif;
            font-size: 1.3rem;
            font-weight: 300;
            color: var(--white);
        }

        .modal-sub {
            font-size: .75rem;
            color: rgba(200, 169, 110, .7);
            margin-top: 3px;
        }

        .close-btn {
            background: rgba(255, 255, 255, .1);
            border: none;
            color: var(--white);
            width: 30px;
            height: 30px;
            border-radius: 50%;
            cursor: pointer;
            font-size: 1rem;
            display: flex;
            align-items: center;
            justify-content: center;
            flex-shrink: 0;
        }

        .close-btn:hover {
            background: rgba(255, 255, 255, .2);
        }

        .modal-bd {
            padding: 1.6rem;
        }

        .modal-ft {
            padding: 1rem 1.6rem 1.4rem;
            border-top: 1px solid var(--ice);
            display: flex;
            justify-content: flex-end;
            gap: .8rem;
            flex-wrap: wrap;
        }

        .prog-wrap {
            margin-bottom: 1.4rem;
        }

        .prog-label {
            display: flex;
            justify-content: space-between;
            font-size: .72rem;
            color: var(--text-light);
            margin-bottom: 6px;
            letter-spacing: .06em;
            text-transform: uppercase;
        }

        .prog-bg {
            height: 4px;
            background: var(--ice);
            border-radius: 2px;
            overflow: hidden;
        }

        .prog-fill {
            height: 100%;
            background: linear-gradient(90deg, var(--gold), #a07840);
            border-radius: 2px;
            transition: width .4s;
        }

        .q-block {
            margin-bottom: 1.2rem;
            padding: 12px 14px;
            background: #fafbff;
            border: 1px solid #e8eef8;
            border-radius: 6px;
        }

        .q-category {
            font-size: .65rem;
            text-transform: uppercase;
            letter-spacing: .12em;
            color: var(--gold);
            margin-bottom: 4px;
            font-weight: 600;
        }

        .q-text {
            font-size: .85rem;
            color: var(--text-dark);
            margin-bottom: 8px;
            line-height: 1.5;
        }

        .rating-labels {
            display: flex;
            justify-content: space-between;
            font-size: .65rem;
            color: var(--text-light);
            margin-bottom: 4px;
        }

        .rating-group {
            display: flex;
            gap: 8px;
        }

        .rating-btn {
            flex: 1;
            padding: 10px 4px;
            text-align: center;
            border: 1.5px solid var(--ice);
            border-radius: 6px;
            background: var(--frost);
            cursor: pointer;
            transition: all .2s;
            font-size: .85rem;
            font-weight: 500;
            color: var(--text-mid);
            min-width: 0;
        }

        .rating-btn:hover {
            border-color: var(--gold);
            color: var(--gold);
        }

        .rating-btn.selected {
            background: #6b4c00;
            border-color: #6b4c00;
            color: var(--white);
        }

        .rating-btn .rl {
            font-size: .58rem;
            display: block;
            margin-top: 2px;
            font-weight: 300;
            opacity: .8;
        }

        .qual-textarea {
            width: 100%;
            border: 1.5px solid var(--ice);
            border-radius: 6px;
            padding: 10px 12px;
            font-family: 'DM Sans', sans-serif;
            font-size: .88rem;
            color: var(--text-dark);
            resize: vertical;
            min-height: 90px;
            outline: none;
            background: var(--frost);
        }

        .qual-textarea:focus {
            border-color: var(--gold);
            background: var(--white);
            box-shadow: 0 0 0 3px rgba(200, 169, 110, .15);
        }

        .btn-submit {
            padding: 10px 28px;
            background: linear-gradient(135deg, var(--gold), #a07840);
            color: var(--white);
            border: none;
            border-radius: 4px;
            font-family: 'DM Sans', sans-serif;
            font-size: .85rem;
            font-weight: 500;
            cursor: pointer;
        }

        .btn-submit:disabled {
            opacity: .6;
            cursor: not-allowed;
        }

        .btn-cancel {
            padding: 10px 24px;
            background: transparent;
            border: 1px solid var(--ice);
            border-radius: 4px;
            font-family: 'DM Sans', sans-serif;
            font-size: .85rem;
            color: var(--text-light);
            cursor: pointer;
        }

        .success-screen {
            text-align: center;
            padding: 2.5rem 1.6rem;
            display: none;
        }

        .success-icon {
            width: 64px;
            height: 64px;
            border-radius: 50%;
            background: rgba(200, 169, 110, .1);
            border: 2px solid rgba(200, 169, 110, .4);
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 1.8rem;
            color: var(--gold);
            margin: 0 auto 1rem;
        }

        .detail-strip {
            background: #f8f9fa;
            border: 1px solid #e9ecef;
            border-radius: 6px;
            padding: 12px 14px;
            margin-bottom: 1.2rem;
        }

        .d-label {
            font-size: .65rem;
            text-transform: uppercase;
            letter-spacing: .08em;
            color: var(--text-light);
        }

        .d-value {
            font-size: .85rem;
            font-weight: 500;
            color: var(--text-dark);
        }

        .loader {
            width: 32px;
            height: 32px;
            border: 3px solid var(--ice);
            border-top-color: var(--gold);
            border-radius: 50%;
            animation: spin .7s linear infinite;
        }

        @keyframes spin {
            to {
                transform: rotate(360deg)
            }
        }

        /* ══ RESPONSIVE BREAKPOINTS ══ */

        /* Tablet (≤ 991px) */
        @media (max-width: 991.98px) {
            .sidebar {
                transform: translateX(-100%);
            }

            .sidebar.open {
                transform: translateX(0);
            }

            .main-content {
                margin-left: 0;
                padding: 1.25rem;
                padding-top: calc(56px + 1.25rem);
            }

            .mobile-topbar {
                display: flex;
            }
        }

        /* Mobile (≤ 575px) */
        @media (max-width: 575.98px) {
            .main-content {
                padding: 1rem;
                padding-top: calc(56px + 1rem);
            }

            .page-title {
                font-size: 1.4rem;
            }

            .stat-num {
                font-size: 1.5rem;
            }

            .staff-grid {
                grid-template-columns: 1fr;
            }

            .portal-tab {
                padding: .6rem .9rem;
                font-size: .8rem;
            }

            /* Stack modal fields */
            .modal-bd .row>[class*="col-"] {
                flex: 0 0 100%;
                max-width: 100%;
            }

            /* Smaller rating buttons on tiny screens */
            .rating-btn {
                font-size: .75rem;
                padding: 8px 2px;
            }

            .rating-btn .rl {
                display: none;
            }

            .rating-labels {
                font-size: .6rem;
            }

            .modal-ft {
                flex-direction: column-reverse;
            }

            .modal-ft .btn-submit,
            .modal-ft .btn-cancel {
                width: 100%;
                text-align: center;
            }

            .eval-modal {
                border-radius: 8px;
                max-height: 95vh;
            }

            .eval-overlay {
                padding: .5rem;
                align-items: flex-end;
            }

            .eval-modal {
                border-bottom-left-radius: 0;
                border-bottom-right-radius: 0;
                max-height: 92vh;
            }
        }

        /* Very small table adjustments */
        @media (max-width: 767.98px) {

            .sub-table th,
            .sub-table td {
                padding: .6rem .7rem;
                font-size: .8rem;
            }

            /* Hide less important columns on mobile */
            .sub-table .col-hide-sm {
                display: none;
            }
        }
    </style>
</head>

<body>

    <!-- Mobile Topbar -->
    <div class="mobile-topbar">
        <div style="display:flex;align-items:center;gap:10px;">
            <div class="brand-cross"></div>
            <div class="brand-text">Holy Cross <span>College</span></div>
        </div>
        <button class="hamburger" onclick="toggleSidebar()" aria-label="Toggle menu">
            <i class='bx bx-menu' id="hamburgerIcon"></i>
        </button>
    </div>

    <!-- Sidebar Overlay -->
    <div class="sidebar-overlay" id="sidebarOverlay" onclick="closeSidebar()"></div>

    <!-- SIDEBAR -->
    <aside class="sidebar" id="sidebar">
        <div class="sidebar-brand">
            <div class="brand-cross"></div>
            <div class="brand-text">Holy Cross <span>College</span></div>
        </div>
        <div class="faculty-card">
            <div class="faculty-avatar" id="sidebarAvatar">--</div>
            <div class="faculty-name"><?= htmlspecialchars($fac_name) ?></div>
            <div class="faculty-meta"><?= htmlspecialchars($fac_dept) ?></div>
            <div class="faculty-meta"><?= htmlspecialchars($fac_branch) ?></div>
            <div class="role-badge">Program Chair</div>
        </div>
        <div class="nav-list">
            <div class="nav-link-item active" id="navStaff" onclick="switchTab('staff');closeSidebar()">
                <i class='bx bx-group'></i> Department Staff
            </div>
            <div class="nav-link-item" id="navMine" onclick="switchTab('mine');closeSidebar()">
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
                <div class="page-title">Program Chair <span>Portal</span></div>
                <div style="font-size:.75rem;color:var(--text-light);" id="topbarDate"></div>
            </div>
            <div style="font-size:.78rem;color:var(--text-light);display:none;" class="d-md-block">
                Welcome, <strong><?= htmlspecialchars($fac_name) ?></strong>
            </div>
        </div>

        <!-- Stats -->
        <div class="row g-3 mb-4">
            <div class="col-6 col-md-3">
                <div class="stat-card">
                    <div>
                        <div class="stat-num" id="statTotal">—</div>
                        <div class="stat-lbl">Dept. Teachers</div>
                    </div>
                    <div class="stat-icon" style="background:rgba(200,169,110,.1)">
                        <i class='bx bx-group' style="color:var(--gold)"></i>
                    </div>
                </div>
            </div>
            <div class="col-6 col-md-3">
                <div class="stat-card">
                    <div>
                        <div class="stat-num" id="statPending">—</div>
                        <div class="stat-lbl">Pending</div>
                    </div>
                    <div class="stat-icon" style="background:rgba(255,193,7,.1)">
                        <i class='bx bx-time' style="color:#ffc107"></i>
                    </div>
                </div>
            </div>
            <div class="col-6 col-md-3">
                <div class="stat-card">
                    <div>
                        <div class="stat-num" id="statDone">—</div>
                        <div class="stat-lbl">Evaluated</div>
                    </div>
                    <div class="stat-icon" style="background:rgba(40,167,69,.1)">
                        <i class='bx bx-check-circle' style="color:#28a745"></i>
                    </div>
                </div>
            </div>
            <div class="col-6 col-md-3">
                <div class="stat-card">
                    <div>
                        <div class="stat-num" id="statDept" style="font-size:1rem;padding-top:.4rem;">
                            <?= htmlspecialchars($fac_dept) ?>
                        </div>
                        <div class="stat-lbl">My Department</div>
                    </div>
                    <div class="stat-icon" style="background:rgba(200,169,110,.1)">
                        <i class='bx bx-building' style="color:var(--gold)"></i>
                    </div>
                </div>
            </div>
        </div>

        <!-- Tabs -->
        <div class="portal-tabs">
            <button class="portal-tab active" id="tabStaff_btn" onclick="switchTab('staff')">
                <i class='bx bx-group'></i> Department Teachers
            </button>
            <button class="portal-tab" id="tabMine_btn" onclick="switchTab('mine')">
                <i class='bx bx-notepad'></i> My Evaluations
                <span class="tab-badge" style="background:var(--green-mid);" id="evalBadge">0</span>
            </button>
        </div>

        <!-- TAB: Staff -->
        <div id="panelStaff">
            <div class="panel">
                <div class="panel-header">
                    <div class="panel-title">Department Teachers — Chair Evaluation</div>
                    <div style="font-size:.72rem;color:var(--text-light);">Official faculty evaluation by Program Chair
                    </div>
                </div>
                <div class="panel-body">
                    <div id="staffLoader" style="display:flex;align-items:center;justify-content:center;padding:3rem;">
                        <div class="loader"></div>
                    </div>
                    <div class="staff-grid" id="staffGrid" style="display:none;"></div>
                    <div id="noStaff" style="display:none;text-align:center;padding:2rem;color:var(--text-light);">
                        <i class='bx bx-group'
                            style="font-size:2.5rem;opacity:.3;display:block;margin-bottom:.5rem;"></i>
                        No teachers found in your department.
                    </div>
                </div>
            </div>
        </div>

        <!-- TAB: My Evaluations -->
        <div id="panelMine" style="display:none;">
            <div class="panel">
                <div class="panel-header">
                    <div class="panel-title">My Submitted Chair Evaluations</div>
                    <div style="font-size:.72rem;color:var(--text-light);" id="mineCount"></div>
                </div>
                <div class="panel-body p-0">
                    <div id="mineLoader" style="display:flex;align-items:center;justify-content:center;padding:3rem;">
                        <div class="loader"></div>
                    </div>
                    <div id="mineTableWrap" style="display:none;">
                        <div class="table-responsive-wrap">
                            <table class="sub-table w-100">
                                <thead>
                                    <tr>
                                        <th class="ps-4">#</th>
                                        <th>Teacher Evaluated</th>
                                        <th class="col-hide-sm">Department</th>
                                        <th class="col-hide-sm">School Year</th>
                                        <th>Semester</th>
                                        <th class="col-hide-sm">Submitted</th>
                                        <th class="text-center pe-4">View</th>
                                    </tr>
                                </thead>
                                <tbody id="mineTableBody"></tbody>
                            </table>
                        </div>
                    </div>
                    <div id="noMine" style="display:none;text-align:center;padding:2.5rem;color:var(--text-light);">
                        <i class='bx bx-notepad'
                            style="font-size:2.5rem;opacity:.3;display:block;margin-bottom:.5rem;"></i>
                        You have not submitted any chair evaluations yet.
                    </div>
                </div>
            </div>
        </div>
    </main>

    <!-- EVAL MODAL -->
    <div class="eval-overlay" id="evalOverlay">
        <div class="eval-modal">
            <div class="modal-hd">
                <div>
                    <div class="modal-title" id="modalTitle">Faculty Evaluation</div>
                    <div class="modal-sub" id="modalSub"></div>
                </div>
                <button class="close-btn" onclick="closeModal()"><i class='bx bx-x'></i></button>
            </div>
            <div class="modal-bd" id="evalBody">
                <div class="prog-wrap">
                    <div class="prog-label"><span>Progress</span><span id="progText">0 / 0 answered</span></div>
                    <div class="prog-bg">
                        <div class="prog-fill" id="progFill" style="width:0%"></div>
                    </div>
                </div>
                <div class="row g-2 mb-3">
                    <div class="col-6">
                        <label
                            style="font-size:.72rem;text-transform:uppercase;letter-spacing:.08em;color:var(--text-light);display:block;margin-bottom:4px;">School
                            Year</label>
                        <input type="text" id="fieldSchoolYear" class="form-control form-control-sm" value="2025-2026">
                    </div>
                    <div class="col-6">
                        <label
                            style="font-size:.72rem;text-transform:uppercase;letter-spacing:.08em;color:var(--text-light);display:block;margin-bottom:4px;">Semester</label>
                        <select id="fieldSemester" class="form-select form-select-sm">
                            <option value="1st">1st Semester</option>
                            <option value="2nd">2nd Semester</option>
                            <option value="summer">Summer</option>
                        </select>
                    </div>
                </div>
                <div id="questionsContainer"></div>
            </div>
            <div class="success-screen" id="successScreen">
                <div class="success-icon"><i class='bx bx-check'></i></div>
                <div
                    style="font-family:'Cormorant Garamond',serif;font-size:1.6rem;font-weight:300;margin-bottom:.5rem;">
                    Evaluation Submitted</div>
                <p style="font-size:.88rem;color:var(--text-light);line-height:1.7;">Your chair evaluation has been
                    recorded.</p>
                <button class="btn-submit mt-3" onclick="closeModal(true)"
                    style="width:auto;padding:10px 32px;">Done</button>
            </div>
            <div class="modal-bd" id="detailBody" style="display:none;"></div>
            <div class="modal-ft" id="evalFooter">
                <button class="btn-cancel" onclick="closeModal()">Cancel</button>
                <button class="btn-submit" id="submitBtn" onclick="submitEval()">Submit Evaluation</button>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        const FAC_ID = <?= (int) $fac_id ?>;
        const FAC_DEPT = <?= json_encode($fac_dept) ?>;
        const RATING_LABELS = ['', 'Poor', 'Fair', 'Good', 'Very Good', 'Excellent'];
        const RATING_COLORS = ['', 'danger', 'warning', 'info', 'primary', 'success'];

        let currentEvaluateeId = null;
        let questions = [], answers = {};
        const evaluatedIds = new Set();

        // Sidebar toggle
        function toggleSidebar() {
            const s = document.getElementById('sidebar');
            const o = document.getElementById('sidebarOverlay');
            const icon = document.getElementById('hamburgerIcon');
            const isOpen = s.classList.toggle('open');
            o.classList.toggle('show', isOpen);
            icon.className = isOpen ? 'bx bx-x' : 'bx bx-menu';
        }
        function closeSidebar() {
            document.getElementById('sidebar').classList.remove('open');
            document.getElementById('sidebarOverlay').classList.remove('show');
            document.getElementById('hamburgerIcon').className = 'bx bx-menu';
        }

        document.addEventListener('DOMContentLoaded', () => {
            document.getElementById('topbarDate').textContent =
                new Date().toLocaleDateString('en-PH', { weekday: 'long', year: 'numeric', month: 'long', day: 'numeric' });
            const name = <?= json_encode($fac_name) ?>;
            document.getElementById('sidebarAvatar').textContent =
                name.split(' ').map(w => w[0]).slice(0, 2).join('').toUpperCase();
            loadStaff();
        });

        function switchTab(tab) {
            document.getElementById('panelStaff').style.display = tab === 'staff' ? 'block' : 'none';
            document.getElementById('panelMine').style.display = tab === 'mine' ? 'block' : 'none';
            document.querySelectorAll('.portal-tab').forEach(b => b.classList.remove('active'));
            document.querySelectorAll('.nav-link-item').forEach(b => b.classList.remove('active'));
            if (tab === 'staff') {
                document.getElementById('tabStaff_btn')?.classList.add('active');
                document.getElementById('navStaff')?.classList.add('active');
            } else {
                document.getElementById('tabMine_btn')?.classList.add('active');
                document.getElementById('navMine')?.classList.add('active');
                loadMyEvals();
            }
        }

        function loadStaff() {
            fetch(`../ClientBackend/api_faculty.php?action=get_dept_staff`)
                .then(r => r.json()).then(d => {
                    document.getElementById('staffLoader').style.display = 'none';
                    if (!d.success || !d.data.length) { document.getElementById('noStaff').style.display = 'block'; return; }
                    document.getElementById('staffGrid').style.display = 'grid';
                    let pending = 0, done = 0, html = '';
                    d.data.forEach(f => {
                        const evaluated = f.already_evaluated == 1;
                        evaluated ? done++ : pending++;
                        if (evaluated) evaluatedIds.add(f.fac_id);
                        const ini = f.full_name.split(' ').map(w => w[0]).slice(0, 2).join('').toUpperCase();
                        if (evaluated) {
                            html += `<div class="staff-card evaluated">
                            <div class="eval-stamp">Done</div>
                            <div class="d-flex align-items-center gap-3 mb-2">
                                <div class="staff-avatar done">${ini}</div>
                                <div>
                                    <div class="staff-name">${escH(f.full_name)}</div>
                                    <div class="staff-dept">${escH(f.department)}</div>
                                    <small style="color:#1a7a4a;">${escH(f.course)}</small>
                                </div>
                            </div>
                            <div class="evaluated-badge">
                                <div class="check-icon"><i class='bx bx-check'></i></div>
                                Chair Evaluation Submitted
                            </div>
                        </div>`;
                        } else {
                            html += `<div class="staff-card">
                            <div class="d-flex align-items-center gap-3 mb-2">
                                <div class="staff-avatar pending">${ini}</div>
                                <div>
                                    <div class="staff-name">${escH(f.full_name)}</div>
                                    <div class="staff-dept">${escH(f.department)}</div>
                                    <small style="color:var(--text-light);">${escH(f.course)}</small>
                                </div>
                            </div>
                            <button class="btn-evaluate" onclick="openEvalModal(${f.fac_id},'${escH(f.full_name)}','${escH(f.department)}')">
                                Evaluate Teacher
                            </button>
                        </div>`;
                        }
                    });
                    document.getElementById('staffGrid').innerHTML = html;
                    document.getElementById('evalBadge').textContent = done;
                    document.getElementById('statTotal').textContent = d.data.length;
                    document.getElementById('statPending').textContent = pending;
                    document.getElementById('statDone').textContent = done;
                });
        }

        function openEvalModal(evaluateeId, name, dept) {
            if (evaluatedIds.has(evaluateeId)) { showToast('Already evaluated this teacher.', 'warning'); return; }
            currentEvaluateeId = evaluateeId;
            answers = {};
            document.getElementById('modalTitle').textContent = `Evaluate: ${name}`;
            document.getElementById('modalSub').textContent = `${dept} · Chair Evaluation`;
            document.getElementById('evalBody').style.display = 'block';
            document.getElementById('evalFooter').style.display = 'flex';
            document.getElementById('successScreen').style.display = 'none';
            document.getElementById('detailBody').style.display = 'none';
            document.getElementById('questionsContainer').innerHTML =
                '<div style="text-align:center;padding:2rem;"><div class="loader" style="margin:auto;"></div></div>';
            document.getElementById('evalOverlay').classList.add('show');
            document.body.style.overflow = 'hidden';
            fetch('../ClientBackend/api_faculty.php?action=get_chair_questions')
                .then(r => r.json()).then(d => { if (!d.success) return; questions = d.data; renderQuestions(); });
        }

        function closeModal(reload = false) {
            document.getElementById('evalOverlay').classList.remove('show');
            document.body.style.overflow = '';
            if (reload) {
                document.getElementById('staffLoader').style.display = 'flex';
                document.getElementById('staffGrid').style.display = 'none';
                loadStaff();
            }
        }

        function renderQuestions() {
            let html = '';
            questions.forEach((q, i) => {
                html += `<div class="q-block" id="qb_${q.id}">
                <div class="q-category">${escH(q.category)}</div>
                <div class="q-text"><strong>${i + 1}.</strong> ${escH(q.question)}</div>`;
                if (q.type === 'quantitative') {
                    html += `<div class="rating-labels"><span>Poor</span><span>Fair</span><span>Good</span><span>Very Good</span><span>Excellent</span></div>
                <div class="rating-group">`;
                    for (let v = 1; v <= 5; v++) html += `<div class="rating-btn" id="rb_${q.id}_${v}" onclick="rate(${q.id},${v})">${v}<span class="rl">${RATING_LABELS[v]}</span></div>`;
                    html += `</div>`;
                } else {
                    html += `<textarea class="qual-textarea" id="qa_${q.id}" placeholder="Write your response..." oninput="txt(${q.id},this.value)"></textarea>`;
                }
                html += `</div>`;
            });
            document.getElementById('questionsContainer').innerHTML = html;
            updateProg();
        }

        function rate(qId, val) {
            answers[qId] = { type: 'quantitative', value: val };
            for (let v = 1; v <= 5; v++) document.getElementById(`rb_${qId}_${v}`)?.classList.toggle('selected', v === val);
            updateProg();
        }
        function txt(qId, val) { answers[qId] = { type: 'qualitative', value: val.trim() }; updateProg(); }
        function updateProg() {
            const total = questions.length;
            const answered = questions.filter(q => answers[q.id] && (q.type === 'qualitative' || answers[q.id].value)).length;
            const pct = total ? Math.round(answered / total * 100) : 0;
            document.getElementById('progFill').style.width = pct + '%';
            document.getElementById('progText').textContent = `${answered} / ${total} answered`;
        }

        function submitEval() {
            const unanswered = questions.filter(q => q.type === 'quantitative' && !answers[q.id]);
            if (unanswered.length) {
                const el = document.getElementById(`qb_${unanswered[0].id}`);
                if (el) { el.scrollIntoView({ behavior: 'smooth', block: 'center' }); el.style.outline = '2px solid #dc3545'; setTimeout(() => el.style.outline = '', 2500); }
                showToast('Please answer all rating questions.', 'warning'); return;
            }
            const btn = document.getElementById('submitBtn');
            btn.disabled = true; btn.innerHTML = '<span class="spinner-border spinner-border-sm me-2"></span>Submitting...';
            fetch('../ClientBackend/api_faculty.php?action=submit_chair_eval', {
                method: 'POST', headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify({
                    evaluator_id: FAC_ID, evaluatee_id: currentEvaluateeId,
                    school_year: document.getElementById('fieldSchoolYear').value,
                    semester: document.getElementById('fieldSemester').value,
                    responses: questions.map(q => ({
                        question_id: q.id, type: q.type,
                        rating_value: q.type === 'quantitative' ? (answers[q.id]?.value || null) : null,
                        text_response: q.type === 'qualitative' ? (answers[q.id]?.value || '') : null
                    }))
                })
            }).then(r => r.json()).then(d => {
                if (d.success) {
                    document.getElementById('evalBody').style.display = 'none';
                    document.getElementById('evalFooter').style.display = 'none';
                    document.getElementById('successScreen').style.display = 'block';
                    evaluatedIds.add(currentEvaluateeId);
                } else showToast(d.message || 'Submission failed.', 'error');
            }).catch(() => showToast('Network error.', 'error'))
                .finally(() => { btn.disabled = false; btn.textContent = 'Submit Evaluation'; });
        }

        function loadMyEvals() {
            document.getElementById('mineLoader').style.display = 'flex';
            document.getElementById('mineTableWrap').style.display = 'none';
            document.getElementById('noMine').style.display = 'none';
            fetch(`../ClientBackend/api_faculty.php?action=my_chair_submissions&fac_id=${FAC_ID}`)
                .then(r => r.json()).then(d => {
                    document.getElementById('mineLoader').style.display = 'none';
                    if (!d.success || !d.data.length) { document.getElementById('noMine').style.display = 'block'; return; }
                    document.getElementById('mineTableWrap').style.display = 'block';
                    document.getElementById('mineCount').textContent = `${d.data.length} submission(s)`;
                    const semColors = { '1st': 'primary', '2nd': 'info', 'summer': 'warning' };
                    let html = '';
                    d.data.forEach((r, i) => {
                        const date = new Date(r.submitted_at).toLocaleDateString('en-PH', { month: 'short', day: 'numeric', year: 'numeric' });
                        html += `<tr onclick="viewDetail(${r.evaluatee_id},'${escH(r.school_year)}','${escH(r.semester)}')">
                        <td class="ps-4 text-muted">${i + 1}</td>
                        <td><div class="fw-semibold" style="font-size:.85rem;">${escH(r.evaluatee_name)}</div></td>
                        <td class="col-hide-sm"><small>${escH(r.evaluatee_dept)}</small></td>
                        <td class="col-hide-sm"><small>${escH(r.school_year)}</small></td>
                        <td><span class="badge bg-${semColors[r.semester] || 'secondary'} bg-opacity-10 text-${semColors[r.semester] || 'secondary'} border">${escH(r.semester)}</span></td>
                        <td class="col-hide-sm"><small class="text-muted">${date}</small></td>
                        <td class="text-center pe-4">
                            <button class="btn btn-sm btn-outline-success" onclick="event.stopPropagation();viewDetail(${r.evaluatee_id},'${escH(r.school_year)}','${escH(r.semester)}')">
                                <i class="bi bi-eye"></i>
                            </button>
                        </td>
                    </tr>`;
                    });
                    document.getElementById('mineTableBody').innerHTML = html;
                });
        }

        function viewDetail(evaluateeId, schoolYear, semester) {
            document.getElementById('evalBody').style.display = 'none';
            document.getElementById('evalFooter').style.display = 'none';
            document.getElementById('successScreen').style.display = 'none';
            document.getElementById('detailBody').style.display = 'block';
            document.getElementById('detailBody').innerHTML =
                '<div style="text-align:center;padding:2rem;"><div class="loader" style="margin:auto;"></div></div>';
            document.getElementById('modalTitle').textContent = 'My Chair Evaluation';
            document.getElementById('modalSub').textContent = 'Read-only';
            document.getElementById('evalOverlay').classList.add('show');
            document.body.style.overflow = 'hidden';
            fetch(`../ClientBackend/api_faculty.php?action=my_chair_detail&evaluator_id=${FAC_ID}&evaluatee_id=${evaluateeId}&school_year=${encodeURIComponent(schoolYear)}&semester=${encodeURIComponent(semester)}`)
                .then(r => r.json()).then(d => { if (!d.success) { document.getElementById('detailBody').innerHTML = '<p class="text-danger p-3">Failed.</p>'; return; } renderDetail(d.data); });
        }

        function renderDetail(data) {
            const info = data.info; const resps = data.responses;
            document.getElementById('modalTitle').textContent = info.evaluatee_name;
            document.getElementById('modalSub').textContent = `Chair Evaluation · ${info.school_year} · ${info.semester} Sem · Read-only`;
            let html = `<div class="detail-strip row g-2 mb-4">
            <div class="col-6"><div class="d-label">Teacher</div><div class="d-value">${escH(info.evaluatee_name)}</div></div>
            <div class="col-6"><div class="d-label">Department</div><div class="d-value">${escH(info.evaluatee_dept)}</div></div>
            <div class="col-6"><div class="d-label">School Year</div><div class="d-value">${escH(info.school_year)}</div></div>
            <div class="col-6"><div class="d-label">Submitted</div><div class="d-value">${new Date(info.submitted_at).toLocaleDateString('en-PH', { month: 'long', day: 'numeric', year: 'numeric' })}</div></div>
        </div>
        <div class="d-flex align-items-center gap-2 mb-3 p-2 rounded" style="background:rgba(40,167,69,.08);border:1px solid rgba(40,167,69,.2);">
            <i class='bx bx-lock-alt' style="color:#28a745;font-size:1.1rem;"></i>
            <small style="color:#1a7a4a;">This evaluation is locked and cannot be edited.</small>
        </div>`;
            let lastCat = '';
            resps.forEach((r, i) => {
                if (r.category !== lastCat) {
                    if (lastCat !== '') html += '</div>';
                    html += `<div class="mb-3"><div style="font-size:.67rem;text-transform:uppercase;letter-spacing:.1em;color:var(--gold);font-weight:700;padding:5px 0;border-bottom:2px solid var(--ice);margin-bottom:8px;">${escH(r.category)}</div>`;
                    lastCat = r.category;
                }
                html += `<div class="q-block"><div class="q-text"><span class="text-muted me-1">${i + 1}.</span>${escH(r.question)}</div>`;
                if (r.type === 'quantitative') {
                    const v = r.rating_value ?? 0; const pct = v ? Math.round((v / 5) * 100) : 0;
                    html += `<div class="d-flex align-items-center gap-3">
                    <span class="badge bg-${RATING_COLORS[v]}" style="font-size:.85rem;padding:5px 14px;">${v} — ${RATING_LABELS[v] || '—'}</span>
                    <div style="flex:1;height:6px;background:#e9ecef;border-radius:3px;overflow:hidden;"><div style="width:${pct}%;height:100%;background:var(--bs-${RATING_COLORS[v]});border-radius:3px;"></div></div>
                    <small class="text-muted">${pct}%</small>
                </div>`;
                } else {
                    const ans = r.text_response?.trim() || '';
                    html += `<div style="background:#fff;border:1px solid #dee2e6;border-radius:4px;padding:10px 12px;font-style:${ans ? 'normal' : 'italic'};color:${ans ? 'inherit' : '#adb5bd'};">${ans ? escH(ans) : 'No response.'}</div>`;
                }
                html += `</div>`;
            });
            if (lastCat !== '') html += '</div>';
            document.getElementById('detailBody').innerHTML = html;
        }

        document.getElementById('evalOverlay').addEventListener('click', e => { if (e.target === e.currentTarget) closeModal(); });

        function escH(s) { if (!s && s !== 0) return ''; const d = document.createElement('div'); d.textContent = String(s); return d.innerHTML; }
        function showToast(msg, type = 'info') {
            const c = { success: '#28a745', error: '#dc3545', warning: '#ffc107', info: '#17a2b8' };
            const el = document.createElement('div');
            el.style.cssText = 'position:fixed;top:1rem;right:1rem;z-index:9999;min-width:240px;max-width:90vw;';
            el.innerHTML = `<div style="background:${c[type]};color:white;padding:12px 16px;border-radius:6px;font-size:.85rem;box-shadow:0 4px 16px rgba(0,0,0,.2);">${msg}</div>`;
            document.body.appendChild(el); setTimeout(() => el.remove(), 4000);
        }
    </script>
</body>

</html>