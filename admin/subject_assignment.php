<?php
/**
 * Filename: subject_assignment.php
 * Path: /admin/subject_assignment.php
 * Subject Assignment Management
 * Assign teachers to subjects, and enroll students into those subject sections
 */

session_start();

if (!isset($_SESSION['user_id'])) {
    header("Location: ../index.php");
    exit();
}
?>

<?php include 'includes/header.php'; ?>

<!-- Loading Overlay -->
<div id="loadingOverlay">
    <div class="loader"></div>
</div>

<!-- Dashboard Content -->
<div id="dashboardContent">
    <div style="min-height: calc(100vh - 200px); padding: 25px 0;">

        <!-- Page Header -->
        <div class="d-flex justify-content-between align-items-center mb-4">
            <div>
                <h3 class="mb-1">Subject Assignment</h3>
                <p class="text-muted mb-0">Assign teachers to subjects and enroll students into sections</p>
            </div>
            <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#subjectModal"
                onclick="resetSubjectForm()">
                <i class="bi bi-plus-circle me-2"></i>Create Subject Section
            </button>
        </div>

        <!-- Stats -->
        <div class="row mb-4">
            <div class="col-lg-3 col-md-6 mb-3">
                <div class="card border-0 shadow-sm">
                    <div class="card-body d-flex justify-content-between align-items-center">
                        <div>
                            <p class="text-muted mb-1 small">Total Sections</p>
                            <h4 class="mb-0 fw-bold" id="statSections">—</h4>
                        </div>
                        <div class="bg-primary bg-opacity-10 p-3 rounded">
                            <i class='bx bx-book fs-2 text-primary'></i>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-lg-3 col-md-6 mb-3">
                <div class="card border-0 shadow-sm">
                    <div class="card-body d-flex justify-content-between align-items-center">
                        <div>
                            <p class="text-muted mb-1 small">Teachers Assigned</p>
                            <h4 class="mb-0 fw-bold" id="statTeachers">—</h4>
                        </div>
                        <div class="bg-success bg-opacity-10 p-3 rounded">
                            <i class='bx bx-chalkboard fs-2 text-success'></i>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-lg-3 col-md-6 mb-3">
                <div class="card border-0 shadow-sm">
                    <div class="card-body d-flex justify-content-between align-items-center">
                        <div>
                            <p class="text-muted mb-1 small">Students Enrolled</p>
                            <h4 class="mb-0 fw-bold" id="statEnrolled">—</h4>
                        </div>
                        <div class="bg-info bg-opacity-10 p-3 rounded">
                            <i class='bx bx-group fs-2 text-info'></i>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-lg-3 col-md-6 mb-3">
                <div class="card border-0 shadow-sm">
                    <div class="card-body d-flex justify-content-between align-items-center">
                        <div>
                            <p class="text-muted mb-1 small">Active School Year</p>
                            <h4 class="mb-0 fw-bold" id="statSchoolYear">—</h4>
                        </div>
                        <div class="bg-warning bg-opacity-10 p-3 rounded">
                            <i class='bx bx-calendar fs-2 text-warning'></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Filter Bar -->
        <div class="card mb-4">
            <div class="card-body">
                <div class="row g-3">
                    <div class="col-md-4">
                        <div class="input-group">
                            <span class="input-group-text"><i class="bi bi-search"></i></span>
                            <input type="text" class="form-control" id="searchInput"
                                placeholder="Search subject or teacher name...">
                        </div>
                    </div>
                    <div class="col-md-2">
                        <select class="form-select" id="schoolYearFilter">
                            <option value="">All School Years</option>
                        </select>
                    </div>
                    <div class="col-md-2">
                        <select class="form-select" id="semesterFilter">
                            <option value="">All Semesters</option>
                            <option value="1st">1st Semester</option>
                            <option value="2nd">2nd Semester</option>
                            <option value="summer">Summer</option>
                        </select>
                    </div>
                    <div class="col-md-2">
                        <select class="form-select" id="branchFilter">
                            <option value="">All Branches</option>
                        </select>
                    </div>
                    <div class="col-md-2">
                        <select class="form-select" id="recordsPerPage">
                            <option value="10">10 per page</option>
                            <option value="25">25 per page</option>
                            <option value="50">50 per page</option>
                        </select>
                    </div>
                </div>
            </div>
        </div>

        <!-- Subject Sections Table -->
        <div class="card">
            <div class="card-body">
                <div class="table-responsive">
                    <table class="table table-hover align-middle">
                        <thead class="table-light">
                            <tr>
                                <th width="5%">#</th>
                                <th width="18%">Subject / Course</th>
                                <th width="16%">Section</th>
                                <th width="18%">Teacher</th>
                                <th width="10%">School Year</th>
                                <th width="8%">Semester</th>
                                <th width="8%">Students</th>
                                <th width="8%">Branch</th>
                                <th width="9%" class="text-center">Actions</th>
                            </tr>
                        </thead>
                        <tbody id="sectionsTableBody">
                            <tr>
                                <td colspan="9" class="text-center py-5">
                                    <div class="spinner-border text-primary" role="status"></div>
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>
                <div class="d-flex justify-content-between align-items-center mt-3">
                    <div id="paginationInfo" class="text-muted small"></div>
                    <nav>
                        <ul class="pagination mb-0" id="pagination"></ul>
                    </nav>
                </div>
            </div>
        </div>

    </div>
</div>

<!-- ── CREATE / EDIT SUBJECT SECTION MODAL ── -->
<div class="modal fade" id="subjectModal" tabindex="-1" aria-labelledby="subjectModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="subjectModalLabel">Create Subject Section</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form id="subjectForm">
                <div class="modal-body">
                    <input type="hidden" id="sectionId" name="id">
                    <div class="row g-3">

                        <!-- Course -->
                        <div class="col-md-6">
                            <label class="form-label">Course / Subject <span class="text-danger">*</span></label>
                            <select class="form-select" id="fieldCourse" name="course_id" required>
                                <option value="">Select Course</option>
                            </select>
                            <div class="invalid-feedback">Please select a course.</div>
                        </div>

                        <!-- Section Name -->
                        <div class="col-md-6">
                            <label class="form-label">Section Name <span class="text-danger">*</span></label>
                            <input type="text" class="form-control" id="fieldSection" name="section_name" required
                                placeholder="e.g. BSIT-1A">
                            <div class="invalid-feedback">Section name is required.</div>
                        </div>

                        <!-- Teacher -->
                        <div class="col-md-6">
                            <label class="form-label">Assigned Teacher <span class="text-danger">*</span></label>
                            <select class="form-select" id="fieldTeacher" name="faculty_id" required>
                                <option value="">Select Teacher</option>
                            </select>
                            <div class="invalid-feedback">Please assign a teacher.</div>
                        </div>

                        <!-- Branch -->
                        <div class="col-md-6">
                            <label class="form-label">Branch <span class="text-danger">*</span></label>
                            <select class="form-select" id="fieldBranch" name="branch" required>
                                <option value="">Select Branch</option>
                            </select>
                            <div class="invalid-feedback">Please select a branch.</div>
                        </div>

                        <!-- School Year -->
                        <div class="col-md-6">
                            <label class="form-label">School Year <span class="text-danger">*</span></label>
                            <input type="text" class="form-control" id="fieldSchoolYear" name="school_year" required
                                placeholder="e.g. 2025-2026">
                            <div class="invalid-feedback">School year is required.</div>
                        </div>

                        <!-- Semester -->
                        <div class="col-md-6">
                            <label class="form-label">Semester <span class="text-danger">*</span></label>
                            <select class="form-select" id="fieldSemester" name="semester" required>
                                <option value="">Select Semester</option>
                                <option value="1st">1st Semester</option>
                                <option value="2nd">2nd Semester</option>
                                <option value="summer">Summer</option>
                            </select>
                            <div class="invalid-feedback">Please select a semester.</div>
                        </div>

                        <!-- Max Students -->
                        <div class="col-md-6">
                            <label class="form-label">Max Students</label>
                            <input type="number" class="form-control" id="fieldMaxStudents" name="max_students"
                                value="40" min="1">
                        </div>

                        <!-- Schedule -->
                        <div class="col-md-6">
                            <label class="form-label">Schedule</label>
                            <input type="text" class="form-control" id="fieldSchedule" name="schedule"
                                placeholder="e.g. MWF 8:00–9:00 AM">
                        </div>

                        <!-- Room -->
                        <div class="col-md-6">
                            <label class="form-label">Room</label>
                            <input type="text" class="form-control" id="fieldRoom" name="room"
                                placeholder="e.g. Room 201">
                        </div>

                        <!-- Active -->
                        <div class="col-md-6 d-flex align-items-end">
                            <div class="form-check form-switch">
                                <input class="form-check-input" type="checkbox" id="fieldIsActive" name="is_active"
                                    checked>
                                <label class="form-check-label" for="fieldIsActive">Active Section</label>
                            </div>
                        </div>

                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-primary" id="subjectSubmitBtn">Save Section</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- ── ENROLL STUDENTS MODAL ── -->
<div class="modal fade" id="enrollModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header bg-primary text-white">
                <h5 class="modal-title" id="enrollModalTitle">Enroll Students</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <input type="hidden" id="enrollSectionId">

                <!-- Currently Enrolled -->
                <div class="mb-3">
                    <label class="form-label fw-semibold">Currently Enrolled Students</label>
                    <div id="enrolledList" class="border rounded p-2 bg-light"
                        style="min-height:60px;max-height:180px;overflow-y:auto;">
                        <span class="text-muted small">Loading...</span>
                    </div>
                </div>

                <!-- Add Students -->
                <div>
                    <label class="form-label fw-semibold">Add Students</label>
                    <div class="input-group mb-2">
                        <span class="input-group-text"><i class="bi bi-search"></i></span>
                        <input type="text" class="form-control" id="studentSearch"
                            placeholder="Search student by name or ID..." oninput="searchStudentsToEnroll()">
                    </div>
                    <div id="studentSearchResults" class="border rounded"
                        style="min-height:60px;max-height:200px;overflow-y:auto;">
                        <p class="text-muted small p-2 mb-0">Type to search students...</p>
                    </div>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
            </div>
        </div>
    </div>
</div>

<!-- ── VIEW SECTION MODAL ── -->
<div class="modal fade" id="viewSectionModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header bg-info text-white">
                <h5 class="modal-title">Section Details</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body" id="viewSectionBody"></div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
            </div>
        </div>
    </div>
</div>

<!-- ── DELETE MODAL ── -->
<div class="modal fade" id="deleteModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header bg-danger text-white">
                <h5 class="modal-title">Confirm Delete</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <p>Are you sure you want to delete this section? All student enrollments in this section will also be
                    removed.</p>
                <p class="fw-bold" id="deleteSectionName"></p>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                <button type="button" class="btn btn-danger" id="confirmDeleteBtn">Delete</button>
            </div>
        </div>
    </div>
</div>

<script>
    // ── STATE ──────────────────────────────────────────────────
    let currentPage = 1;
    let recordsPerPage = 10;
    let searchTerm = '';
    let syFilter = '';
    let semFilter = '';
    let branchFilter = '';
    let deleteId = null;

    // ── INIT ───────────────────────────────────────────────────
    window.onload = function () {
        setTimeout(() => {
            document.getElementById('loadingOverlay').style.display = 'none';
            document.getElementById('dashboardContent').style.display = 'block';
            loadDropdowns();
            loadSections();
            loadStats();
        }, 600);
    };

    // ── EVENT LISTENERS ────────────────────────────────────────
    document.getElementById('searchInput').addEventListener('input', e => {
        searchTerm = e.target.value; currentPage = 1; loadSections();
    });
    document.getElementById('schoolYearFilter').addEventListener('change', e => {
        syFilter = e.target.value; currentPage = 1; loadSections();
    });
    document.getElementById('semesterFilter').addEventListener('change', e => {
        semFilter = e.target.value; currentPage = 1; loadSections();
    });
    document.getElementById('branchFilter').addEventListener('change', e => {
        branchFilter = e.target.value; currentPage = 1; loadSections();
    });
    document.getElementById('recordsPerPage').addEventListener('change', e => {
        recordsPerPage = parseInt(e.target.value); currentPage = 1; loadSections();
    });
    document.getElementById('subjectForm').addEventListener('submit', e => {
        e.preventDefault(); saveSection();
    });

    // ── LOAD DROPDOWNS ─────────────────────────────────────────
    function loadDropdowns() {
        // Branches
        fetch('api/subject_assignments.php?action=get_branches')
            .then(r => r.json()).then(d => {
                if (!d.success) return;
                const bFilter = document.getElementById('branchFilter');
                const bField = document.getElementById('fieldBranch');
                d.data.forEach(b => {
                    bFilter.innerHTML += `<option value="${escH(b.branch_name)}">${escH(b.branch_name)}</option>`;
                    bField.innerHTML += `<option value="${escH(b.branch_name)}">${escH(b.branch_name)}</option>`;
                });
            });

        // Teachers
        fetch('api/subject_assignments.php?action=get_teachers')
            .then(r => r.json()).then(d => {
                if (!d.success) return;
                const sel = document.getElementById('fieldTeacher');
                d.data.forEach(t => {
                    sel.innerHTML += `<option value="${t.fac_id}">${escH(t.full_name)} (${escH(t.role)})</option>`;
                });
            });

        // Courses
        fetch('api/subject_assignments.php?action=get_courses')
            .then(r => r.json()).then(d => {
                if (!d.success) return;
                const sel = document.getElementById('fieldCourse');
                d.data.forEach(c => {
                    sel.innerHTML += `<option value="${c.id}">${escH(c.course_code)} — ${escH(c.course_name)}</option>`;
                });
            });

        // School Years
        fetch('api/subject_assignments.php?action=get_school_years')
            .then(r => r.json()).then(d => {
                if (!d.success) return;
                const sel = document.getElementById('schoolYearFilter');
                d.data.forEach(y => {
                    sel.innerHTML += `<option value="${escH(y)}">${escH(y)}</option>`;
                });
                if (d.data.length > 0) {
                    document.getElementById('statSchoolYear').textContent = d.data[0];
                }
            });
    }

    // ── LOAD STATS ─────────────────────────────────────────────
    function loadStats() {
        fetch('api/subject_assignments.php?action=stats')
            .then(r => r.json()).then(d => {
                if (!d.success) return;
                document.getElementById('statSections').textContent = d.data.sections ?? '—';
                document.getElementById('statTeachers').textContent = d.data.teachers ?? '—';
                document.getElementById('statEnrolled').textContent = d.data.enrolled ?? '—';
            });
    }

    // ── LOAD SECTIONS ──────────────────────────────────────────
    function loadSections() {
        const tbody = document.getElementById('sectionsTableBody');
        tbody.innerHTML = '<tr><td colspan="9" class="text-center py-4"><div class="spinner-border text-primary" role="status"></div></td></tr>';

        let url = `api/subject_assignments.php?action=read&page=${currentPage}&limit=${recordsPerPage}`;
        if (searchTerm) url += `&search=${encodeURIComponent(searchTerm)}`;
        if (syFilter) url += `&school_year=${encodeURIComponent(syFilter)}`;
        if (semFilter) url += `&semester=${encodeURIComponent(semFilter)}`;
        if (branchFilter) url += `&branch=${encodeURIComponent(branchFilter)}`;

        fetch(url)
            .then(r => r.json())
            .then(d => {
                if (d.success) renderSections(d.data, d.pagination);
                else tbody.innerHTML = '<tr><td colspan="9" class="text-center text-danger py-4">Error loading sections.</td></tr>';
            })
            .catch(() => {
                tbody.innerHTML = '<tr><td colspan="9" class="text-center text-danger py-4">Failed to fetch data.</td></tr>';
            });
    }

    // ── RENDER SECTIONS ────────────────────────────────────────
    function renderSections(sections, pagination) {
        const tbody = document.getElementById('sectionsTableBody');
        if (!sections.length) {
            tbody.innerHTML = '<tr><td colspan="9" class="text-center py-4 text-muted">No sections found.</td></tr>';
            updatePagination({ current_page: 1, total_pages: 1, total_records: 0, records_per_page: recordsPerPage });
            return;
        }

        const semColors = { '1st': 'primary', '2nd': 'info', 'summer': 'warning' };
        let html = '';
        const start = (currentPage - 1) * recordsPerPage;

        sections.forEach((s, i) => {
            const semBadge = `<span class="badge bg-${semColors[s.semester] || 'secondary'}">${escH(s.semester)}</span>`;
            const actBadge = s.is_active == 1
                ? '<span class="badge bg-success-subtle text-success">Active</span>'
                : '<span class="badge bg-secondary-subtle text-secondary">Inactive</span>';

            html += `
            <tr>
                <td>${start + i + 1}</td>
                <td>
                    <div class="fw-semibold">${escH(s.course_name)}</div>
                    <small class="text-muted">${escH(s.course_code)}</small>
                </td>
                <td><span class="badge bg-light text-dark border">${escH(s.section_name)}</span></td>
                <td>
                    <div class="fw-semibold">${escH(s.teacher_name)}</div>
                    <small class="text-muted text-capitalize">${escH(s.teacher_role)}</small>
                </td>
                <td><small>${escH(s.school_year)}</small></td>
                <td>${semBadge}</td>
                <td>
                    <span class="badge bg-primary bg-opacity-10 text-primary">
                        ${s.student_count} / ${s.max_students}
                    </span>
                </td>
                <td><small>${escH(s.branch)}</small></td>
                <td class="text-center">
                    <div class="btn-group btn-group-sm">
                        <button class="btn btn-outline-info"    onclick="viewSection(${s.id})"   title="View"><i class="bi bi-eye"></i></button>
                        <button class="btn btn-outline-success" onclick="openEnroll(${s.id},'${escH(s.section_name)}')" title="Enroll Students"><i class="bi bi-person-plus"></i></button>
                        <button class="btn btn-outline-primary" onclick="editSection(${s.id})"   title="Edit"><i class="bi bi-pencil"></i></button>
                        <button class="btn btn-outline-danger"  onclick="confirmDelete(${s.id},'${escH(s.section_name)}')" title="Delete"><i class="bi bi-trash"></i></button>
                    </div>
                </td>
            </tr>`;
        });

        tbody.innerHTML = html;
        updatePagination(pagination);
    }

    // ── PAGINATION ─────────────────────────────────────────────
    function updatePagination(p) {
        const info = document.getElementById('paginationInfo');
        const ul = document.getElementById('pagination');
        const start = (p.current_page - 1) * p.records_per_page + 1;
        const end = Math.min(start + p.records_per_page - 1, p.total_records);
        info.textContent = `Showing ${start}–${end} of ${p.total_records} entries`;

        let html = `<li class="page-item ${p.current_page === 1 ? 'disabled' : ''}">
            <a class="page-link" href="#" onclick="changePage(${p.current_page - 1});return false;">Previous</a></li>`;
        for (let i = 1; i <= p.total_pages; i++) {
            if (i === 1 || i === p.total_pages || (i >= p.current_page - 2 && i <= p.current_page + 2)) {
                html += `<li class="page-item ${i === p.current_page ? 'active' : ''}">
                    <a class="page-link" href="#" onclick="changePage(${i});return false;">${i}</a></li>`;
            } else if (i === p.current_page - 3 || i === p.current_page + 3) {
                html += '<li class="page-item disabled"><a class="page-link">...</a></li>';
            }
        }
        html += `<li class="page-item ${p.current_page === p.total_pages ? 'disabled' : ''}">
            <a class="page-link" href="#" onclick="changePage(${p.current_page + 1});return false;">Next</a></li>`;
        ul.innerHTML = html;
    }

    function changePage(p) { currentPage = p; loadSections(); }

    // ── RESET FORM ─────────────────────────────────────────────
    function resetSubjectForm() {
        document.getElementById('subjectForm').reset();
        document.getElementById('subjectForm').classList.remove('was-validated');
        document.getElementById('sectionId').value = '';
        document.getElementById('subjectModalLabel').textContent = 'Create Subject Section';
        document.getElementById('subjectSubmitBtn').textContent = 'Save Section';
        document.getElementById('fieldIsActive').checked = true;
    }

    // ── VIEW SECTION ───────────────────────────────────────────
    function viewSection(id) {
        fetch(`api/subject_assignments.php?action=read_one&id=${id}`)
            .then(r => r.json()).then(d => {
                if (!d.success) { showToast('Error', 'Failed to load section', 'error'); return; }
                const s = d.data;
                document.getElementById('viewSectionBody').innerHTML = `
                    <table class="table table-sm table-borderless">
                        <tr><th width="40%">Course</th><td>${escH(s.course_name)} (${escH(s.course_code)})</td></tr>
                        <tr><th>Section</th><td>${escH(s.section_name)}</td></tr>
                        <tr><th>Teacher</th><td>${escH(s.teacher_name)}</td></tr>
                        <tr><th>School Year</th><td>${escH(s.school_year)}</td></tr>
                        <tr><th>Semester</th><td>${escH(s.semester)}</td></tr>
                        <tr><th>Branch</th><td>${escH(s.branch)}</td></tr>
                        <tr><th>Schedule</th><td>${escH(s.schedule || '—')}</td></tr>
                        <tr><th>Room</th><td>${escH(s.room || '—')}</td></tr>
                        <tr><th>Students</th><td>${s.student_count} / ${s.max_students}</td></tr>
                        <tr><th>Status</th><td>${s.is_active == 1 ? '<span class="badge bg-success">Active</span>' : '<span class="badge bg-secondary">Inactive</span>'}</td></tr>
                    </table>`;
                new bootstrap.Modal(document.getElementById('viewSectionModal')).show();
            });
    }

    // ── EDIT SECTION ───────────────────────────────────────────
    function editSection(id) {
        fetch(`api/subject_assignments.php?action=read_one&id=${id}`)
            .then(r => r.json()).then(d => {
                if (!d.success) { showToast('Error', 'Failed to load section', 'error'); return; }
                const s = d.data;
                document.getElementById('sectionId').value = s.id;
                document.getElementById('fieldCourse').value = s.course_id;
                document.getElementById('fieldSection').value = s.section_name;
                document.getElementById('fieldTeacher').value = s.faculty_id;
                document.getElementById('fieldBranch').value = s.branch;
                document.getElementById('fieldSchoolYear').value = s.school_year;
                document.getElementById('fieldSemester').value = s.semester;
                document.getElementById('fieldMaxStudents').value = s.max_students;
                document.getElementById('fieldSchedule').value = s.schedule || '';
                document.getElementById('fieldRoom').value = s.room || '';
                document.getElementById('fieldIsActive').checked = s.is_active == 1;
                document.getElementById('subjectModalLabel').textContent = 'Edit Subject Section';
                document.getElementById('subjectSubmitBtn').textContent = 'Update Section';
                new bootstrap.Modal(document.getElementById('subjectModal')).show();
            });
    }

    // ── SAVE SECTION ───────────────────────────────────────────
    function saveSection() {
        const form = document.getElementById('subjectForm');
        if (!form.checkValidity()) { form.classList.add('was-validated'); return; }

        const btn = document.getElementById('subjectSubmitBtn');
        btn.disabled = true;
        btn.innerHTML = '<span class="spinner-border spinner-border-sm me-2"></span>Saving...';

        const id = document.getElementById('sectionId').value;
        const action = id ? 'update' : 'create';

        fetch(`api/subject_assignments.php?action=${action}`, {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify({
                id: id || undefined,
                course_id: document.getElementById('fieldCourse').value,
                section_name: document.getElementById('fieldSection').value,
                faculty_id: document.getElementById('fieldTeacher').value,
                branch: document.getElementById('fieldBranch').value,
                school_year: document.getElementById('fieldSchoolYear').value,
                semester: document.getElementById('fieldSemester').value,
                max_students: parseInt(document.getElementById('fieldMaxStudents').value),
                schedule: document.getElementById('fieldSchedule').value,
                room: document.getElementById('fieldRoom').value,
                is_active: document.getElementById('fieldIsActive').checked ? 1 : 0,
            })
        })
            .then(r => r.json())
            .then(d => {
                if (d.success) {
                    showToast('Success', d.message, 'success');
                    bootstrap.Modal.getInstance(document.getElementById('subjectModal')).hide();
                    loadSections(); loadStats(); resetSubjectForm();
                } else {
                    showToast('Error', d.message, 'error');
                }
            })
            .catch(() => showToast('Error', 'An error occurred while saving.', 'error'))
            .finally(() => {
                btn.disabled = false;
                btn.textContent = id ? 'Update Section' : 'Save Section';
            });
    }

    // ── ENROLL STUDENTS ────────────────────────────────────────
    function openEnroll(sectionId, sectionName) {
        document.getElementById('enrollSectionId').value = sectionId;
        document.getElementById('enrollModalTitle').textContent = `Enroll Students — ${sectionName}`;
        document.getElementById('studentSearch').value = '';
        document.getElementById('studentSearchResults').innerHTML = '<p class="text-muted small p-2 mb-0">Type to search students...</p>';
        loadEnrolledStudents(sectionId);
        new bootstrap.Modal(document.getElementById('enrollModal')).show();
    }

    function loadEnrolledStudents(sectionId) {
        document.getElementById('enrolledList').innerHTML = '<span class="text-muted small">Loading...</span>';
        fetch(`api/subject_assignments.php?action=get_enrolled&section_id=${sectionId}`)
            .then(r => r.json()).then(d => {
                const el = document.getElementById('enrolledList');
                if (!d.success || !d.data.length) {
                    el.innerHTML = '<span class="text-muted small">No students enrolled yet.</span>';
                    return;
                }
                el.innerHTML = d.data.map(s => `
                    <div class="d-flex justify-content-between align-items-center py-1 border-bottom">
                        <div>
                            <span class="fw-semibold small">${escH(s.full_name)}</span>
                            <code class="ms-2 small text-muted">${escH(s.student_id)}</code>
                        </div>
                        <button class="btn btn-sm btn-outline-danger py-0"
                            onclick="unenrollStudent(${sectionId},${s.stud_id})">
                            <i class="bi bi-x"></i>
                        </button>
                    </div>`).join('');
            });
    }

    let searchTimeout;
    function searchStudentsToEnroll() {
        clearTimeout(searchTimeout);
        const q = document.getElementById('studentSearch').value.trim();
        const sectionId = document.getElementById('enrollSectionId').value;
        if (!q) {
            document.getElementById('studentSearchResults').innerHTML = '<p class="text-muted small p-2 mb-0">Type to search students...</p>';
            return;
        }
        searchTimeout = setTimeout(() => {
            fetch(`api/subject_assignments.php?action=search_students&q=${encodeURIComponent(q)}&section_id=${sectionId}`)
                .then(r => r.json()).then(d => {
                    const el = document.getElementById('studentSearchResults');
                    if (!d.success || !d.data.length) {
                        el.innerHTML = '<p class="text-muted small p-2 mb-0">No students found.</p>';
                        return;
                    }
                    el.innerHTML = d.data.map(s => `
                        <div class="d-flex justify-content-between align-items-center p-2 border-bottom">
                            <div>
                                <span class="fw-semibold small">${escH(s.full_name)}</span>
                                <code class="ms-2 small text-muted">${escH(s.student_id)}</code>
                                <small class="text-muted ms-1">— ${escH(s.program)}</small>
                            </div>
                            <button class="btn btn-sm btn-outline-primary py-0"
                                onclick="enrollStudent(${sectionId},${s.stud_id})"
                                ${s.already_enrolled ? 'disabled' : ''}>
                                ${s.already_enrolled ? 'Enrolled' : '<i class="bi bi-plus"></i> Add'}
                            </button>
                        </div>`).join('');
                });
        }, 350);
    }

    function enrollStudent(sectionId, studentId) {
        fetch('api/subject_assignments.php?action=enroll', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify({ section_id: sectionId, stud_id: studentId })
        })
            .then(r => r.json()).then(d => {
                if (d.success) {
                    showToast('Success', d.message, 'success');
                    loadEnrolledStudents(sectionId);
                    searchStudentsToEnroll();
                    loadStats();
                    loadSections();
                } else {
                    showToast('Error', d.message, 'error');
                }
            });
    }

    function unenrollStudent(sectionId, studentId) {
        fetch('api/subject_assignments.php?action=unenroll', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify({ section_id: sectionId, stud_id: studentId })
        })
            .then(r => r.json()).then(d => {
                if (d.success) {
                    showToast('Success', d.message, 'success');
                    loadEnrolledStudents(sectionId);
                    loadStats();
                    loadSections();
                } else {
                    showToast('Error', d.message, 'error');
                }
            });
    }

    // ── DELETE ─────────────────────────────────────────────────
    function confirmDelete(id, name) {
        deleteId = id;
        document.getElementById('deleteSectionName').textContent = name;
        new bootstrap.Modal(document.getElementById('deleteModal')).show();
    }

    document.getElementById('confirmDeleteBtn').addEventListener('click', function () {
        if (!deleteId) return;
        this.disabled = true;
        this.innerHTML = '<span class="spinner-border spinner-border-sm me-2"></span>Deleting...';

        fetch('api/subject_assignments.php?action=delete', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify({ id: deleteId })
        })
            .then(r => r.json()).then(d => {
                if (d.success) {
                    showToast('Success', d.message, 'success');
                    bootstrap.Modal.getInstance(document.getElementById('deleteModal')).hide();
                    loadSections(); loadStats();
                } else {
                    showToast('Error', d.message, 'error');
                }
            })
            .finally(() => {
                this.disabled = false;
                this.textContent = 'Delete';
                deleteId = null;
            });
    });

    // ── HELPERS ────────────────────────────────────────────────
    function escH(str) {
        if (!str && str !== 0) return '';
        const d = document.createElement('div');
        d.textContent = String(str);
        return d.innerHTML;
    }

    function showToast(title, message, type = 'info') {
        const colors = { success: '#28a745', error: '#dc3545', warning: '#ffc107', info: '#17a2b8' };
        const el = document.createElement('div');
        el.className = 'position-fixed top-0 end-0 p-3';
        el.style.zIndex = '9999';
        el.innerHTML = `
            <div class="toast show">
                <div class="toast-header" style="background:${colors[type]};color:white;">
                    <strong class="me-auto">${title}</strong>
                    <button type="button" class="btn-close btn-close-white" onclick="this.closest('.position-fixed').remove()"></button>
                </div>
                <div class="toast-body">${message}</div>
            </div>`;
        document.body.appendChild(el);
        setTimeout(() => el.remove(), 3500);
    }
</script>

<script src="assets/js/dashboard.js"></script>
<style>
    .table th {
        font-weight: 600;
        text-transform: uppercase;
        font-size: 0.82rem;
        letter-spacing: 0.5px;
    }

    .btn-group-sm .btn {
        padding: 0.25rem 0.5rem;
    }
</style>

<?php include 'includes/footer.php'; ?>