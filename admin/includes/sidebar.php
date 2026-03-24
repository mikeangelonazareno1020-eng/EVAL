<?php
// File: sidebar.php
// Path: /admin/includes/sidebar.php

$current = basename($_SERVER['PHP_SELF']);
?>

<!-- Header -->
<header class="header" id="header">
    <div class="header_toggle">
        <i class='bx bx-menu' id="header-toggle"></i>
    </div>
    <div class="btn-group" style="width: 60px">
        <button type="button" class="dropdown-toggle" data-bs-toggle="dropdown" aria-expanded="false">
            <img src="https://res.cloudinary.com/dpjpz26qm/image/upload/v1674890312/codepen/avatar/man_1_cpqkhl.png"
                class="img-fluid" alt="" style="width: 35px;">
        </button>
        <ul class="dropdown-menu">
            <li><a class="dropdown-item" href="#"><i class='bx bx-user-circle'></i> Account</a></li>
            <li><a class="dropdown-item" href="#"><i class='bx bxs-widget'></i> Settings</a></li>
            <li><a class="dropdown-item" href="../logout.php"><i class='bx bx-exit'></i> Logout</a></li>
        </ul>
    </div>
</header>

<!-- Sidebar -->
<div class="l-navbar" id="nav-bar">
    <nav class="nav">
        <div>
            <a href="dashboard.php" class="nav_logo">
                <i class='bx bx-layer nav_logo-icon'></i>
                <span class="nav_logo-name">HCC</span>
            </a>

            <div class="nav_list">

                <a href="dashboard.php" class="nav_link <?= $current === 'dashboard.php' ? 'active' : '' ?>"
                    title="Dashboard">
                    <i class='bx bx-grid-alt nav_icon'></i>
                    <span class="nav_name">Dashboard</span>
                </a>

                <a href="department.php" class="nav_link <?= $current === 'department.php' ? 'active' : '' ?>"
                    title="Department">
                    <i class='bx bx-sitemap nav_icon'></i>
                    <span class="nav_name">Department</span>
                </a>

                <a href="program_management.php"
                    class="nav_link <?= $current === 'program_management.php' ? 'active' : '' ?>" title="Program">
                    <i class='bx bx-book-open nav_icon'></i>
                    <span class="nav_name">Program</span>
                </a>

                <a href="branch_management.php"
                    class="nav_link <?= $current === 'branch_management.php' ? 'active' : '' ?>" title="Branch">
                    <i class='bx bx-building nav_icon'></i>
                    <span class="nav_name">Branch</span>
                </a>

                <a href="grade_level.php" class="nav_link <?= $current === 'grade_level.php' ? 'active' : '' ?>"
                    title="Grade Level">
                    <i class='bx bx-layer nav_icon'></i>
                    <span class="nav_name">Grade Level</span>
                </a>

                <a href="course_management.php"
                    class="nav_link <?= $current === 'course_management.php' ? 'active' : '' ?>" title="Course">
                    <i class='bx bx-book nav_icon'></i>
                    <span class="nav_name">Course</span>
                </a>

                <!-- Student Management -->
                <a href="student_management.php"
                    class="nav_link <?= $current === 'student_management.php' ? 'active' : '' ?>"
                    title="Student Management">
                    <i class='bx bx-user-pin nav_icon'></i>
                    <span class="nav_name">Students</span>
                </a>

                <!-- Subject Assignment -->
                <a href="subject_assignment.php"
                    class="nav_link <?= $current === 'subject_assignment.php' ? 'active' : '' ?>"
                    title="Subject Assignment">
                    <i class='bx bx-chalkboard nav_icon'></i>
                    <span class="nav_name">Subjects</span>
                </a>

                <!-- Evaluation Submissions -->
                <a href="evaluation_submissions.php"
                    class="nav_link <?= $current === 'evaluation_submissions.php' ? 'active' : '' ?>"
                    title="Evaluation Submissions">
                    <i class='bx bx-notepad nav_icon'></i>
                    <span class="nav_name">Submissions</span>
                </a>

                <!-- Evaluation Questions -->
                <a href="question_management.php"
                    class="nav_link <?= $current === 'question_management.php' ? 'active' : '' ?>"
                    title="Evaluation Questions">
                    <i class='bx bx-list-check nav_icon'></i>
                    <span class="nav_name">Questions</span>
                </a>

                <a href="user_management.php" class="nav_link <?= $current === 'user_management.php' ? 'active' : '' ?>"
                    title="User Management">
                    <i class='bx bx-user-check nav_icon'></i>
                    <span class="nav_name">User Management</span>
                </a>

            </div>
        </div>

        <a href="../logout.php" class="nav_link" title="Sign Out">
            <i class='bx bx-log-out nav_icon'></i>
            <span class="nav_name">SignOut</span>
        </a>
    </nav>
</div>