<?php
/**
 * Filename: student_management.php
 * Path: /admin/student_management.php
 * Student Management — CRUD + Subject Enrollment
 * Admin assigns any mix of subject sections to each student.
 * Supports irregular students who don't follow a fixed curriculum.
 */

session_start();
if (!isset($_SESSION['user_id'])) {
    header("Location: ../index.php");
    exit();
}
?>
<?php include 'includes/header.php'; ?>

<div id="loadingOverlay"><div class="loader"></div></div>

<div id="dashboardContent">
<div style="min-height:calc(100vh - 200px);padding:25px 0;">

    <!-- Page Header -->
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h3 class="mb-1">Student Management</h3>
            <p class="text-muted mb-0">Add students and assign their subjects — supports irregular enrollment</p>
        </div>
        <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#studentModal" onclick="resetForm()">
            <i class="bi bi-plus-circle me-2"></i>Add Student
        </button>
    </div>

    <!-- Stats -->
    <div class="row mb-4">
        <div class="col-lg-3 col-md-6 mb-3">
            <div class="card border-0 shadow-sm">
                <div class="card-body d-flex justify-content-between align-items-center">
                    <div><p class="text-muted mb-1 small">Total Students</p><h4 class="mb-0 fw-bold" id="statTotal">—</h4></div>
                    <div class="bg-primary bg-opacity-10 p-3 rounded"><i class='bx bx-user-circle fs-2 text-primary'></i></div>
                </div>
            </div>
        </div>
        <div class="col-lg-3 col-md-6 mb-3">
            <div class="card border-0 shadow-sm">
                <div class="card-body d-flex justify-content-between align-items-center">
                    <div><p class="text-muted mb-1 small">Active Students</p><h4 class="mb-0 fw-bold" id="statActive">—</h4></div>
                    <div class="bg-success bg-opacity-10 p-3 rounded"><i class='bx bx-check-circle fs-2 text-success'></i></div>
                </div>
            </div>
        </div>
        <div class="col-lg-3 col-md-6 mb-3">
            <div class="card border-0 shadow-sm">
                <div class="card-body d-flex justify-content-between align-items-center">
                    <div><p class="text-muted mb-1 small">Branches</p><h4 class="mb-0 fw-bold" id="statBranches">—</h4></div>
                    <div class="bg-info bg-opacity-10 p-3 rounded"><i class='bx bx-building-house fs-2 text-info'></i></div>
                </div>
            </div>
        </div>
        <div class="col-lg-3 col-md-6 mb-3">
            <div class="card border-0 shadow-sm">
                <div class="card-body d-flex justify-content-between align-items-center">
                    <div><p class="text-muted mb-1 small">Programs</p><h4 class="mb-0 fw-bold" id="statPrograms">—</h4></div>
                    <div class="bg-warning bg-opacity-10 p-3 rounded"><i class='bx bx-book-open fs-2 text-warning'></i></div>
                </div>
            </div>
        </div>
    </div>

    <!-- Filters -->
    <div class="card mb-4">
        <div class="card-body">
            <div class="row g-3">
                <div class="col-md-4">
                    <div class="input-group">
                        <span class="input-group-text"><i class="bi bi-search"></i></span>
                        <input type="text" class="form-control" id="searchInput" placeholder="Search by name, ID, program...">
                    </div>
                </div>
                <div class="col-md-2"><select class="form-select" id="branchFilter"><option value="">All Branches</option></select></div>
                <div class="col-md-2"><select class="form-select" id="programFilter"><option value="">All Programs</option></select></div>
                <div class="col-md-2">
                    <select class="form-select" id="statusFilter">
                        <option value="">All Status</option>
                        <option value="1">Active</option>
                        <option value="0">Inactive</option>
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

    <!-- Table -->
    <div class="card">
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-hover align-middle">
                    <thead class="table-light">
                        <tr>
                            <th width="4%">#</th>
                            <th width="12%">Student ID</th>
                            <th width="18%">Full Name</th>
                            <th width="13%">Program</th>
                            <th width="12%">Department</th>
                            <th width="11%">Branch</th>
                            <th width="8%">Subjects</th>
                            <th width="7%">Type</th>
                            <th width="7%">Status</th>
                            <th width="8%" class="text-center">Actions</th>
                        </tr>
                    </thead>
                    <tbody id="studentsTableBody">
                        <tr><td colspan="10" class="text-center py-5"><div class="spinner-border text-primary" role="status"></div></td></tr>
                    </tbody>
                </table>
            </div>
            <div class="d-flex justify-content-between align-items-center mt-3">
                <div id="paginationInfo" class="text-muted small"></div>
                <nav><ul class="pagination mb-0" id="pagination"></ul></nav>
            </div>
        </div>
    </div>

</div>
</div>

<!-- ═══ ADD/EDIT MODAL ═══ -->
<div class="modal fade" id="studentModal" tabindex="-1" aria-labelledby="studentModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-xl">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="studentModalLabel">Add Student</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form id="studentForm">
                <div class="modal-body">
                    <input type="hidden" id="studentId">
                    <div class="row g-4">

                        <!-- LEFT: Info -->
                        <div class="col-lg-5">
                            <p class="text-uppercase fw-semibold text-muted mb-3" style="font-size:.72rem;letter-spacing:.1em;">Student Information</p>
                            <div class="row g-3">
                                <div class="col-6">
                                    <label class="form-label">Student ID <span class="text-danger">*</span></label>
                                    <input type="text" class="form-control" id="fieldStudentId" required placeholder="STU-2026-001">
                                    <div class="invalid-feedback">Required & must be unique.</div>
                                </div>
                                <div class="col-6">
                                    <label class="form-label">Full Name <span class="text-danger">*</span></label>
                                    <input type="text" class="form-control" id="fieldFullName" required placeholder="Juan Dela Cruz">
                                    <div class="invalid-feedback">Required.</div>
                                </div>
                                <div class="col-6">
                                    <label class="form-label">Department <span class="text-danger">*</span></label>
                                    <select class="form-select" id="fieldDepartment" required>
                                        <option value="">Select</option>
                                    </select>
                                </div>
                                <div class="col-6">
                                    <label class="form-label">Program <span class="text-danger">*</span></label>
                                    <select class="form-select" id="fieldProgram" required>
                                        <option value="">Select</option>
                                    </select>
                                </div>
                                <div class="col-6">
                                    <label class="form-label">Grade Level <span class="text-danger">*</span></label>
                                    <select class="form-select" id="fieldGradeLevel" required>
                                        <option value="">Select</option>
                                    </select>
                                </div>
                                <div class="col-6">
                                    <label class="form-label">Branch <span class="text-danger">*</span></label>
                                    <select class="form-select" id="fieldBranch" required>
                                        <option value="">Select</option>
                                    </select>
                                </div>
                                <div class="col-6">
                                    <label class="form-label">School Year</label>
                                    <input type="text" class="form-control" id="fieldSchoolYear" placeholder="2025-2026">
                                </div>
                                <div class="col-6">
                                    <label class="form-label">
                                        Password <span class="text-danger" id="pwStar">*</span>
                                        <small class="text-muted" id="pwHint">(Required)</small>
                                    </label>
                                    <div class="input-group">
                                        <input type="password" class="form-control" id="fieldPassword" placeholder="Set password">
                                        <button class="btn btn-outline-secondary" type="button" id="togglePw">
                                            <i class="bi bi-eye" id="eyeIcon"></i>
                                        </button>
                                    </div>
                                </div>
                                <div class="col-12">
                                    <div class="d-flex gap-4">
                                        <div class="form-check form-switch">
                                            <input class="form-check-input" type="checkbox" id="fieldIsActive" checked>
                                            <label class="form-check-label" for="fieldIsActive">Active Account</label>
                                        </div>
                                        <div class="form-check form-switch">
                                            <input class="form-check-input" type="checkbox" id="fieldIsIrregular">
                                            <label class="form-check-label" for="fieldIsIrregular">
                                                Irregular Student
                                                <span class="badge bg-warning text-dark ms-1" style="font-size:.62rem;">IRR</span>
                                            </label>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- RIGHT: Subject Enrollment Picker -->
                        <div class="col-lg-7">
                            <div class="d-flex justify-content-between align-items-center mb-1">
                                <p class="text-uppercase fw-semibold text-muted mb-0" style="font-size:.72rem;letter-spacing:.1em;">
                                    Assign Subjects
                                </p>
                                <span class="badge bg-primary" id="selectedCount">0 selected</span>
                            </div>
                            <p class="text-muted mb-2" style="font-size:.78rem;">
                                Pick the subject sections this student will take. Each section already has an assigned teacher.
                                Irregular students can mix subjects across any program.
                            </p>

                            <!-- Search filter -->
                            <div class="input-group input-group-sm mb-2">
                                <span class="input-group-text"><i class="bi bi-search"></i></span>
                                <input type="text" class="form-control" id="sectionSearch"
                                    placeholder="Filter by subject, teacher, section..." oninput="filterSections()">
                                <button class="btn btn-outline-secondary btn-sm" type="button" onclick="clearAllSections()">
                                    Clear all
                                </button>
                            </div>

                            <!-- Scrollable section picker -->
                            <div id="sectionPickerWrap"
                                style="border:1px solid #dee2e6;border-radius:6px;max-height:340px;overflow-y:auto;">
                                <div class="text-center py-4 text-muted small" id="sectionPickerLoader">
                                    <div class="spinner-border spinner-border-sm me-2"></div>Loading subjects...
                                </div>
                            </div>
                        </div>

                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-primary" id="submitBtn">Save Student</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- VIEW MODAL -->
<div class="modal fade" id="viewStudentModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header bg-primary text-white">
                <h5 class="modal-title">Student Details</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body" id="viewStudentBody"></div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
            </div>
        </div>
    </div>
</div>

<!-- DELETE MODAL -->
<div class="modal fade" id="deleteModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header bg-danger text-white">
                <h5 class="modal-title">Confirm Delete</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <p>Delete this student and all their subject enrollments?</p>
                <p class="fw-bold" id="deleteStudentName"></p>
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
let currentPage = 1, recordsPerPage = 10;
let searchTerm = '', branchFilter = '', programFilter = '', statusFilter = '';
let deleteId = null;
let allSections = [];
let selectedSections = new Set();

// ── INIT ───────────────────────────────────────────────────
window.onload = () => setTimeout(() => {
    document.getElementById('loadingOverlay').style.display = 'none';
    document.getElementById('dashboardContent').style.display = 'block';
    loadDropdowns();
    loadAllSections();
    loadStudents();
    loadStats();
}, 600);

// ── LISTENERS ──────────────────────────────────────────────
document.getElementById('searchInput').addEventListener('input',   e => { searchTerm  = e.target.value; currentPage = 1; loadStudents(); });
document.getElementById('branchFilter').addEventListener('change', e => { branchFilter = e.target.value; currentPage = 1; loadStudents(); });
document.getElementById('programFilter').addEventListener('change',e => { programFilter= e.target.value; currentPage = 1; loadStudents(); });
document.getElementById('statusFilter').addEventListener('change', e => { statusFilter = e.target.value; currentPage = 1; loadStudents(); });
document.getElementById('recordsPerPage').addEventListener('change',e=>{ recordsPerPage=parseInt(e.target.value); currentPage=1; loadStudents(); });
document.getElementById('studentForm').addEventListener('submit',  e => { e.preventDefault(); saveStudent(); });
document.getElementById('togglePw').addEventListener('click', () => {
    const inp = document.getElementById('fieldPassword');
    const ico = document.getElementById('eyeIcon');
    inp.type = inp.type === 'password' ? 'text' : 'password';
    ico.className = inp.type === 'password' ? 'bi bi-eye' : 'bi bi-eye-slash';
});

// ── DROPDOWNS ──────────────────────────────────────────────
function loadDropdowns() {
    fetch('api/students.php?action=get_branches').then(r=>r.json()).then(d => {
        if (!d.success) return;
        d.data.forEach(b => {
            document.getElementById('branchFilter').innerHTML  += `<option value="${escH(b.branch_name)}">${escH(b.branch_name)}</option>`;
            document.getElementById('fieldBranch').innerHTML   += `<option value="${escH(b.branch_name)}">${escH(b.branch_name)}</option>`;
        });
    });
    fetch('api/students.php?action=get_programs').then(r=>r.json()).then(d => {
        if (!d.success) return;
        d.data.forEach(p => {
            document.getElementById('programFilter').innerHTML += `<option value="${escH(p.program_name)}">${escH(p.program_name)}</option>`;
            document.getElementById('fieldProgram').innerHTML  += `<option value="${escH(p.program_name)}">${escH(p.program_name)}</option>`;
        });
    });
    fetch('api/students.php?action=get_departments').then(r=>r.json()).then(d => {
        if (!d.success) return;
        d.data.forEach(dep => document.getElementById('fieldDepartment').innerHTML += `<option value="${escH(dep.department_name)}">${escH(dep.department_name)}</option>`);
    });
    fetch('api/students.php?action=get_grade_levels').then(r=>r.json()).then(d => {
        if (!d.success) return;
        d.data.forEach(g => document.getElementById('fieldGradeLevel').innerHTML += `<option value="${escH(g.grade_name)}">${escH(g.grade_name)}</option>`);
    });
}

// ── ALL SECTIONS (loaded once) ─────────────────────────────
function loadAllSections() {
    fetch('api/students.php?action=get_sections').then(r=>r.json()).then(d => {
        if (!d.success) return;
        allSections = d.data;
        renderSectionPicker();
    });
}

// ── SECTION PICKER ─────────────────────────────────────────
function renderSectionPicker(filter = '') {
    const wrap = document.getElementById('sectionPickerWrap');
    const fl   = filter.toLowerCase();
    const list = fl ? allSections.filter(s =>
        s.course_name.toLowerCase().includes(fl) ||
        s.course_code.toLowerCase().includes(fl) ||
        s.teacher_name.toLowerCase().includes(fl) ||
        s.section_name.toLowerCase().includes(fl)
    ) : allSections;

    if (!list.length) {
        wrap.innerHTML = '<div class="text-center py-3 text-muted small">No subjects found.</div>';
        return;
    }

    // Group by school_year · semester
    const groups = {};
    list.forEach(s => {
        const k = `${s.school_year} · ${s.semester} Semester`;
        (groups[k] = groups[k] || []).push(s);
    });

    let html = '';
    for (const [grp, items] of Object.entries(groups)) {
        html += `<div style="background:#f8f9fa;border-bottom:1px solid #e9ecef;padding:5px 12px;">
                    <small class="fw-semibold text-uppercase text-muted" style="font-size:.67rem;letter-spacing:.09em;">${escH(grp)}</small>
                 </div>`;
        items.forEach(s => {
            const chk = selectedSections.has(s.id);
            html += `
            <label class="section-item d-flex align-items-start gap-2 px-3 py-2${chk ? ' selected' : ''}"
                   style="cursor:pointer;border-bottom:1px solid #f4f4f4;" for="sec_${s.id}">
                <input type="checkbox" class="form-check-input mt-1 flex-shrink-0"
                    id="sec_${s.id}" value="${s.id}" ${chk?'checked':''}
                    onchange="toggleSection(${s.id}, this.checked, this.closest('label'))">
                <div style="flex:1;min-width:0;">
                    <div style="font-size:.84rem;font-weight:600;">${escH(s.course_code)} — ${escH(s.course_name)}</div>
                    <div style="font-size:.74rem;color:#555;">
                        <i class="bi bi-person-fill me-1 text-primary"></i>${escH(s.teacher_name)}
                        <span class="badge bg-light text-secondary border ms-1 text-capitalize">${escH(s.teacher_role)}</span>
                    </div>
                    <div style="font-size:.7rem;color:#999;">
                        <i class="bi bi-collection me-1"></i>${escH(s.section_name)}
                        &nbsp;·&nbsp;<i class="bi bi-clock me-1"></i>${escH(s.schedule||'TBA')}
                        &nbsp;·&nbsp;<i class="bi bi-door-open me-1"></i>${escH(s.room||'TBA')}
                        &nbsp;·&nbsp;<i class="bi bi-building me-1"></i>${escH(s.branch)}
                    </div>
                </div>
            </label>`;
        });
    }

    wrap.innerHTML = html;
}

function toggleSection(id, checked, lbl) {
    checked ? selectedSections.add(id) : selectedSections.delete(id);
    lbl.classList.toggle('selected', checked);
    updateSelCount();
}
function updateSelCount() {
    document.getElementById('selectedCount').textContent = `${selectedSections.size} selected`;
}
function filterSections() { renderSectionPicker(document.getElementById('sectionSearch').value); }
function clearAllSections() {
    selectedSections.clear();
    renderSectionPicker(document.getElementById('sectionSearch').value);
    updateSelCount();
}

// ── STATS ──────────────────────────────────────────────────
function loadStats() {
    fetch('api/students.php?action=stats').then(r=>r.json()).then(d => {
        if (!d.success) return;
        document.getElementById('statTotal').textContent    = d.data.total    ?? '—';
        document.getElementById('statActive').textContent   = d.data.active   ?? '—';
        document.getElementById('statBranches').textContent = d.data.branches ?? '—';
        document.getElementById('statPrograms').textContent = d.data.programs ?? '—';
    });
}

// ── LOAD & RENDER STUDENTS ─────────────────────────────────
function loadStudents() {
    const tb = document.getElementById('studentsTableBody');
    tb.innerHTML = '<tr><td colspan="10" class="text-center py-4"><div class="spinner-border text-primary" role="status"></div></td></tr>';

    let url = `api/students.php?action=read&page=${currentPage}&limit=${recordsPerPage}`;
    if (searchTerm)   url += `&search=${encodeURIComponent(searchTerm)}`;
    if (branchFilter) url += `&branch=${encodeURIComponent(branchFilter)}`;
    if (programFilter)url += `&program=${encodeURIComponent(programFilter)}`;
    if (statusFilter !== '') url += `&status=${statusFilter}`;

    fetch(url).then(r=>r.json()).then(d => {
        d.success ? renderStudents(d.data, d.pagination)
                  : (tb.innerHTML = '<tr><td colspan="10" class="text-center text-danger py-4">Error loading data.</td></tr>');
    }).catch(()=> tb.innerHTML = '<tr><td colspan="10" class="text-center text-danger py-4">Network error.</td></tr>');
}

function renderStudents(students, pagination) {
    const tb = document.getElementById('studentsTableBody');
    if (!students.length) {
        tb.innerHTML = '<tr><td colspan="10" class="text-center py-4 text-muted">No students found.</td></tr>';
        updatePagination({current_page:1,total_pages:1,total_records:0,records_per_page:recordsPerPage});
        return;
    }
    const colors = ['primary','info','success','warning','danger','secondary'];
    const start  = (currentPage-1)*recordsPerPage;
    let html = '';
    students.forEach((s,i) => {
        const ini   = s.full_name.split(' ').map(w=>w[0]).slice(0,2).join('').toUpperCase();
        const col   = colors[s.stud_id % colors.length];
        const irrBadge = s.is_irregular==1 ? '<span class="badge bg-warning text-dark ms-1" title="Irregular">IRR</span>' : '';
        html += `
        <tr>
            <td>${start+i+1}</td>
            <td><code class="text-primary">${escH(s.student_id)}</code></td>
            <td>
                <div class="d-flex align-items-center gap-2">
                    <div class="avatar-sm bg-${col} bg-opacity-10 rounded-circle d-flex align-items-center justify-content-center flex-shrink-0">
                        <span class="text-${col} fw-bold" style="font-size:12px">${ini}</span>
                    </div>
                    <span class="fw-semibold">${escH(s.full_name)}</span>${irrBadge}
                </div>
            </td>
            <td><small>${escH(s.program)}</small></td>
            <td><small>${escH(s.department)}</small></td>
            <td><small>${escH(s.branch)}</small></td>
            <td><span class="badge bg-primary bg-opacity-10 text-primary">${s.subject_count??0} subj.</span></td>
            <td>${s.is_irregular==1 ? '<span class="badge bg-warning text-dark">Irregular</span>' : '<span class="badge bg-light text-secondary border">Regular</span>'}</td>
            <td>${s.is_active==1 ? '<span class="badge bg-success">Active</span>' : '<span class="badge bg-secondary">Inactive</span>'}</td>
            <td class="text-center">
                <div class="btn-group btn-group-sm">
                    <button class="btn btn-outline-info"    onclick="viewStudent(${s.stud_id})" title="View"><i class="bi bi-eye"></i></button>
                    <button class="btn btn-outline-primary" onclick="editStudent(${s.stud_id})" title="Edit"><i class="bi bi-pencil"></i></button>
                    <button class="btn btn-outline-${s.is_active==1?'warning':'success'}"
                            onclick="toggleStatus(${s.stud_id})"
                            title="${s.is_active==1?'Deactivate':'Activate'}">
                        <i class="bi bi-toggle-${s.is_active==1?'on':'off'}"></i>
                    </button>
                    <button class="btn btn-outline-danger" onclick="confirmDelete(${s.stud_id},'${escH(s.full_name)}')" title="Delete"><i class="bi bi-trash"></i></button>
                </div>
            </td>
        </tr>`;
    });
    tb.innerHTML = html;
    updatePagination(pagination);
}

function updatePagination(p) {
    const s = (p.current_page-1)*p.records_per_page+1;
    const e = Math.min(s+p.records_per_page-1, p.total_records);
    document.getElementById('paginationInfo').textContent = `Showing ${s}–${e} of ${p.total_records} entries`;
    let html = `<li class="page-item ${p.current_page===1?'disabled':''}"><a class="page-link" href="#" onclick="changePage(${p.current_page-1});return false;">Previous</a></li>`;
    for (let i=1;i<=p.total_pages;i++) {
        if (i===1||i===p.total_pages||(i>=p.current_page-2&&i<=p.current_page+2))
            html += `<li class="page-item ${i===p.current_page?'active':''}"><a class="page-link" href="#" onclick="changePage(${i});return false;">${i}</a></li>`;
        else if (i===p.current_page-3||i===p.current_page+3)
            html += '<li class="page-item disabled"><a class="page-link">...</a></li>';
    }
    html += `<li class="page-item ${p.current_page===p.total_pages?'disabled':''}"><a class="page-link" href="#" onclick="changePage(${p.current_page+1});return false;">Next</a></li>`;
    document.getElementById('pagination').innerHTML = html;
}
function changePage(p) { currentPage=p; loadStudents(); }

// ── RESET FORM ─────────────────────────────────────────────
function resetForm() {
    document.getElementById('studentForm').reset();
    document.getElementById('studentForm').classList.remove('was-validated');
    document.getElementById('studentId').value = '';
    document.getElementById('studentModalLabel').textContent = 'Add Student';
    document.getElementById('submitBtn').textContent = 'Save Student';
    document.getElementById('fieldIsActive').checked    = true;
    document.getElementById('fieldIsIrregular').checked = false;
    document.getElementById('fieldPassword').required   = true;
    document.getElementById('pwStar').style.display     = '';
    document.getElementById('pwHint').textContent       = '(Required)';
    selectedSections.clear();
    document.getElementById('sectionSearch').value = '';
    renderSectionPicker();
    updateSelCount();
}

// ── VIEW ───────────────────────────────────────────────────
function viewStudent(id) {
    fetch(`api/students.php?action=read_one&id=${id}`).then(r=>r.json()).then(d => {
        if (!d.success) { showToast('Error','Failed to load','error'); return; }
        const s = d.data;
        const subjHtml = s.enrolled_sections?.length
            ? s.enrolled_sections.map(sec =>
                `<div class="d-flex align-items-center gap-2 mb-2 p-2 border rounded">
                    <span class="badge bg-primary">${escH(sec.course_code)}</span>
                    <div>
                        <div style="font-size:.85rem;font-weight:600;">${escH(sec.course_name)}</div>
                        <div style="font-size:.75rem;color:#6c757d;">
                            <i class="bi bi-person-fill me-1"></i>${escH(sec.teacher_name)}
                            &nbsp;·&nbsp;<i class="bi bi-collection me-1"></i>${escH(sec.section_name)}
                        </div>
                    </div>
                </div>`).join('')
            : '<p class="text-muted">No subjects assigned.</p>';

        document.getElementById('viewStudentBody').innerHTML = `
        <div class="row">
            <div class="col-md-5">
                <table class="table table-sm table-borderless">
                    <tr><th width="42%">Student ID</th><td><code>${escH(s.student_id)}</code></td></tr>
                    <tr><th>Full Name</th><td>${escH(s.full_name)}</td></tr>
                    <tr><th>Department</th><td>${escH(s.department)}</td></tr>
                    <tr><th>Program</th><td>${escH(s.program)}</td></tr>
                    <tr><th>Grade Level</th><td>${escH(s.grade_level)}</td></tr>
                    <tr><th>Branch</th><td>${escH(s.branch)}</td></tr>
                    <tr><th>School Year</th><td>${escH(s.school_year||'—')}</td></tr>
                    <tr><th>Type</th><td>${s.is_irregular==1?'<span class="badge bg-warning text-dark">Irregular</span>':'Regular'}</td></tr>
                    <tr><th>Status</th><td>${s.is_active==1?'<span class="badge bg-success">Active</span>':'<span class="badge bg-secondary">Inactive</span>'}</td></tr>
                    <tr><th>Last Login</th><td>${escH(s.last_login||'Never')}</td></tr>
                </table>
            </div>
            <div class="col-md-7">
                <h6 class="fw-semibold mb-3">Enrolled Subjects (${s.enrolled_sections?.length??0})</h6>
                ${subjHtml}
            </div>
        </div>`;
        new bootstrap.Modal(document.getElementById('viewStudentModal')).show();
    });
}

// ── EDIT ───────────────────────────────────────────────────
function editStudent(id) {
    fetch(`api/students.php?action=read_one&id=${id}`).then(r=>r.json()).then(d => {
        if (!d.success) { showToast('Error','Failed to load','error'); return; }
        const s = d.data;
        document.getElementById('studentId').value       = s.stud_id;
        document.getElementById('fieldStudentId').value  = s.student_id;
        document.getElementById('fieldFullName').value   = s.full_name;
        document.getElementById('fieldDepartment').value = s.department;
        document.getElementById('fieldProgram').value    = s.program;
        document.getElementById('fieldGradeLevel').value = s.grade_level;
        document.getElementById('fieldBranch').value     = s.branch;
        document.getElementById('fieldSchoolYear').value = s.school_year||'';
        document.getElementById('fieldIsActive').checked    = s.is_active==1;
        document.getElementById('fieldIsIrregular').checked = s.is_irregular==1;
        document.getElementById('fieldPassword').value   = '';
        document.getElementById('fieldPassword').required= false;
        document.getElementById('pwStar').style.display  = 'none';
        document.getElementById('pwHint').textContent    = '(Leave blank to keep current)';

        selectedSections.clear();
        (s.enrolled_sections||[]).forEach(sec => selectedSections.add(sec.section_id));
        document.getElementById('sectionSearch').value = '';
        renderSectionPicker();
        updateSelCount();

        document.getElementById('studentModalLabel').textContent = 'Edit Student';
        document.getElementById('submitBtn').textContent = 'Update Student';
        new bootstrap.Modal(document.getElementById('studentModal')).show();
    });
}

// ── SAVE ───────────────────────────────────────────────────
function saveStudent() {
    const form = document.getElementById('studentForm');
    if (!form.checkValidity()) { form.classList.add('was-validated'); return; }

    const btn = document.getElementById('submitBtn');
    btn.disabled = true;
    btn.innerHTML = '<span class="spinner-border spinner-border-sm me-2"></span>Saving...';

    const id = document.getElementById('studentId').value;
    const payload = {
        id:           id||undefined,
        student_id:   document.getElementById('fieldStudentId').value,
        full_name:    document.getElementById('fieldFullName').value,
        department:   document.getElementById('fieldDepartment').value,
        program:      document.getElementById('fieldProgram').value,
        grade_level:  document.getElementById('fieldGradeLevel').value,
        branch:       document.getElementById('fieldBranch').value,
        school_year:  document.getElementById('fieldSchoolYear').value,
        is_active:    document.getElementById('fieldIsActive').checked    ?1:0,
        is_irregular: document.getElementById('fieldIsIrregular').checked ?1:0,
        section_ids:  Array.from(selectedSections),
    };
    const pw = document.getElementById('fieldPassword').value;
    if (pw) payload.password = pw;

    fetch(`api/students.php?action=${id?'update':'create'}`, {
        method:'POST', headers:{'Content-Type':'application/json'}, body:JSON.stringify(payload)
    }).then(r=>r.json()).then(d => {
        if (d.success) {
            showToast('Success', d.message, 'success');
            bootstrap.Modal.getInstance(document.getElementById('studentModal')).hide();
            loadStudents(); loadStats(); resetForm();
        } else showToast('Error', d.message, 'error');
    }).catch(()=>showToast('Error','An error occurred.','error'))
    .finally(()=>{ btn.disabled=false; btn.textContent=id?'Update Student':'Save Student'; });
}

// ── TOGGLE / DELETE ────────────────────────────────────────
function toggleStatus(id) {
    fetch('api/students.php?action=toggle_active',{method:'POST',headers:{'Content-Type':'application/json'},body:JSON.stringify({id})})
    .then(r=>r.json()).then(d=>{ if(d.success){showToast('Success',d.message,'success');loadStudents();loadStats();}else showToast('Error',d.message,'error'); });
}
function confirmDelete(id, name) {
    deleteId=id;
    document.getElementById('deleteStudentName').textContent=name;
    new bootstrap.Modal(document.getElementById('deleteModal')).show();
}
document.getElementById('confirmDeleteBtn').addEventListener('click', function(){
    if(!deleteId) return;
    this.disabled=true; this.innerHTML='<span class="spinner-border spinner-border-sm me-2"></span>Deleting...';
    fetch('api/students.php?action=delete',{method:'POST',headers:{'Content-Type':'application/json'},body:JSON.stringify({id:deleteId})})
    .then(r=>r.json()).then(d=>{
        if(d.success){showToast('Success',d.message,'success');bootstrap.Modal.getInstance(document.getElementById('deleteModal')).hide();loadStudents();loadStats();}
        else showToast('Error',d.message,'error');
    }).finally(()=>{this.disabled=false;this.textContent='Delete';deleteId=null;});
});

// ── HELPERS ────────────────────────────────────────────────
function escH(s){if(!s&&s!==0)return'';const d=document.createElement('div');d.textContent=String(s);return d.innerHTML;}
function showToast(title,message,type='info'){
    const c={success:'#28a745',error:'#dc3545',warning:'#ffc107',info:'#17a2b8'};
    const el=document.createElement('div');
    el.className='position-fixed top-0 end-0 p-3';el.style.zIndex='9999';
    el.innerHTML=`<div class="toast show"><div class="toast-header" style="background:${c[type]};color:white;"><strong class="me-auto">${title}</strong><button type="button" class="btn-close btn-close-white" onclick="this.closest('.position-fixed').remove()"></button></div><div class="toast-body">${message}</div></div>`;
    document.body.appendChild(el);setTimeout(()=>el.remove(),3500);
}
</script>

<script src="assets/js/dashboard.js"></script>
<style>
.avatar-sm{width:38px;height:38px;}
.table th{font-weight:600;text-transform:uppercase;font-size:.82rem;letter-spacing:.5px;}
.btn-group-sm .btn{padding:.25rem .5rem;}
.section-item:hover{background:#f0f6ff!important;}
.section-item.selected{background:#e8f0fe!important;}
#sectionPickerWrap::-webkit-scrollbar{width:5px;}
#sectionPickerWrap::-webkit-scrollbar-track{background:#f1f1f1;}
#sectionPickerWrap::-webkit-scrollbar-thumb{background:#c0cfe8;border-radius:3px;}
</style>

<?php include 'includes/footer.php'; ?>