<?php
/**
 * Filename: evaluation_submissions.php
 * Path: /admin/evaluation_submissions.php
 * All evaluation submissions — Student, Peer, Chair
 */
session_start();
if (!isset($_SESSION['user_id'])) {
    header("Location: ../index.php");
    exit();
}
?>
<?php include 'includes/header.php'; ?>

<div id="loadingOverlay">
    <div class="loader"></div>
</div>

<div id="dashboardContent">
    <div style="min-height:calc(100vh - 200px);padding:25px 0;">

        <div class="d-flex justify-content-between align-items-center mb-4">
            <div>
                <h3 class="mb-1">Evaluation Submissions</h3>
                <p class="text-muted mb-0">All submissions — Student evaluations, Peer evaluations, Chair evaluations
                </p>
            </div>
            <button class="btn btn-outline-secondary btn-sm" onclick="exportCSV()">
                <i class="bi bi-download me-1"></i>Export CSV
            </button>
        </div>

        <!-- Stats -->
        <div class="row mb-4">
            <div class="col-lg-3 col-md-6 mb-3">
                <div class="card border-0 shadow-sm">
                    <div class="card-body d-flex justify-content-between align-items-center">
                        <div>
                            <p class="text-muted mb-1 small">Total Submissions</p>
                            <h4 class="mb-0 fw-bold" id="statTotal">—</h4>
                        </div>
                        <div class="bg-primary bg-opacity-10 p-3 rounded"><i
                                class='bx bx-notepad fs-2 text-primary'></i></div>
                    </div>
                </div>
            </div>
            <div class="col-lg-3 col-md-6 mb-3">
                <div class="card border-0 shadow-sm">
                    <div class="card-body d-flex justify-content-between align-items-center">
                        <div>
                            <p class="text-muted mb-1 small">Student → Teacher</p>
                            <h4 class="mb-0 fw-bold" id="statStudent">—</h4>
                        </div>
                        <div class="bg-info bg-opacity-10 p-3 rounded"><i class='bx bx-user-check fs-2 text-info'></i>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-lg-3 col-md-6 mb-3">
                <div class="card border-0 shadow-sm">
                    <div class="card-body d-flex justify-content-between align-items-center">
                        <div>
                            <p class="text-muted mb-1 small">Peer (Teacher → Teacher)</p>
                            <h4 class="mb-0 fw-bold" id="statPeer">—</h4>
                        </div>
                        <div class="bg-primary bg-opacity-10 p-3 rounded"><i class='bx bx-group fs-2 text-primary'></i>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-lg-3 col-md-6 mb-3">
                <div class="card border-0 shadow-sm">
                    <div class="card-body d-flex justify-content-between align-items-center">
                        <div>
                            <p class="text-muted mb-1 small">Chair → Teacher</p>
                            <h4 class="mb-0 fw-bold" id="statChair">—</h4>
                        </div>
                        <div class="bg-warning bg-opacity-10 p-3 rounded"><i class='bx bx-medal fs-2 text-warning'></i>
                        </div>
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
                            <input type="text" class="form-control" id="searchInput"
                                placeholder="Search name or subject...">
                        </div>
                    </div>
                    <div class="col-md-2">
                        <select class="form-select" id="typeFilter">
                            <option value="">All Types</option>
                            <option value="student">Student → Teacher</option>
                            <option value="peer">Peer (Teacher → Teacher)</option>
                            <option value="chair">Chair → Teacher</option>
                        </select>
                    </div>
                    <div class="col-md-2">
                        <select class="form-select" id="teacherFilter">
                            <option value="">All Teachers</option>
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
            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table table-hover align-middle mb-0">
                        <thead class="table-light">
                            <tr>
                                <th class="ps-3" width="4%">#</th>
                                <th width="8%">Type</th>
                                <th width="18%">Evaluator</th>
                                <th width="18%">Evaluated</th>
                                <th width="18%">Subject / Context</th>
                                <th width="8%">Semester</th>
                                <th width="10%">Date</th>
                                <th width="6%" class="text-center pe-3">View</th>
                            </tr>
                        </thead>
                        <tbody id="tableBody">
                            <tr>
                                <td colspan="8" class="text-center py-5">
                                    <div class="spinner-border text-primary" role="status"></div>
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>
                <div class="d-flex justify-content-between align-items-center p-3 border-top">
                    <div id="paginationInfo" class="text-muted small"></div>
                    <nav>
                        <ul class="pagination mb-0" id="pagination"></ul>
                    </nav>
                </div>
            </div>
        </div>

    </div>
</div>

<!-- DETAIL MODAL -->
<div class="modal fade" id="detailModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header" style="background:linear-gradient(135deg,#0a1f3c,#1a4a8a);">
                <div>
                    <h5 class="modal-title text-white mb-0" id="modalTitle">Evaluation Detail</h5>
                    <small class="text-white-50" id="modalSub"></small>
                </div>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body" id="modalBody">
                <div class="text-center py-4">
                    <div class="spinner-border text-primary" role="status"></div>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
            </div>
        </div>
    </div>
</div>

<script>
    let currentPage = 1, recordsPerPage = 10;
    let searchTerm = '', typeFilter = '', teacherFilter = '', semesterFilter = '';
    let allData = [];

    const TYPE_BADGES = {
        student: '<span class="badge" style="background:#0dcaf0;font-size:.7rem;">Student</span>',
        peer: '<span class="badge bg-primary" style="font-size:.7rem;">Peer</span>',
        chair: '<span class="badge bg-warning text-dark" style="font-size:.7rem;">Chair</span>',
    };
    const RATING_LABELS = ['', 'Poor', 'Fair', 'Good', 'Very Good', 'Excellent'];
    const RATING_COLORS = ['', 'danger', 'warning', 'info', 'primary', 'success'];

    window.onload = () => setTimeout(() => {
        document.getElementById('loadingOverlay').style.display = 'none';
        document.getElementById('dashboardContent').style.display = 'block';
        loadFilters();
        loadStats();
        loadSubmissions();
    }, 600);

    document.getElementById('searchInput').addEventListener('input', e => { searchTerm = e.target.value; currentPage = 1; loadSubmissions(); });
    document.getElementById('typeFilter').addEventListener('change', e => { typeFilter = e.target.value; currentPage = 1; loadSubmissions(); });
    document.getElementById('teacherFilter').addEventListener('change', e => { teacherFilter = e.target.value; currentPage = 1; loadSubmissions(); });
    document.getElementById('semesterFilter').addEventListener('change', e => { semesterFilter = e.target.value; currentPage = 1; loadSubmissions(); });
    document.getElementById('recordsPerPage').addEventListener('change', e => { recordsPerPage = parseInt(e.target.value); currentPage = 1; loadSubmissions(); });

    function loadFilters() {
        fetch('api/eval_submissions.php?action=get_teachers').then(r => r.json()).then(d => {
            if (!d.success) return;
            d.data.forEach(t => {
                document.getElementById('teacherFilter').innerHTML +=
                    `<option value="${t.fac_id}">${escH(t.full_name)}</option>`;
            });
        });
    }

    function loadStats() {
        fetch('api/eval_submissions.php?action=stats').then(r => r.json()).then(d => {
            if (!d.success) return;
            document.getElementById('statTotal').textContent = d.data.total ?? '—';
            document.getElementById('statStudent').textContent = d.data.student ?? '—';
            document.getElementById('statPeer').textContent = d.data.peer ?? '—';
            document.getElementById('statChair').textContent = d.data.chair ?? '—';
        });
    }

    function loadSubmissions() {
        const tb = document.getElementById('tableBody');
        tb.innerHTML = '<tr><td colspan="8" class="text-center py-4"><div class="spinner-border text-primary" role="status"></div></td></tr>';

        let url = `api/eval_submissions.php?action=read&page=${currentPage}&limit=${recordsPerPage}`;
        if (searchTerm) url += `&search=${encodeURIComponent(searchTerm)}`;
        if (typeFilter) url += `&type=${typeFilter}`;
        if (teacherFilter) url += `&faculty_id=${teacherFilter}`;
        if (semesterFilter) url += `&semester=${encodeURIComponent(semesterFilter)}`;

        fetch(url).then(r => r.json()).then(d => {
            if (d.success) { allData = d.data; renderTable(d.data, d.pagination); }
            else tb.innerHTML = '<tr><td colspan="8" class="text-center text-danger py-4">Error loading data.</td></tr>';
        });
    }

    function renderTable(rows, pagination) {
        const tb = document.getElementById('tableBody');
        if (!rows.length) {
            tb.innerHTML = '<tr><td colspan="8" class="text-center py-5 text-muted">No submissions found.</td></tr>';
            updatePagination({ current_page: 1, total_pages: 1, total_records: 0, records_per_page: recordsPerPage });
            return;
        }

        const semColors = { '1st': 'primary', '2nd': 'info', 'summer': 'warning' };
        const start = (currentPage - 1) * recordsPerPage;
        let html = '';

        rows.forEach((r, i) => {
            const date = new Date(r.submitted_at).toLocaleDateString('en-PH', { month: 'short', day: 'numeric', year: 'numeric' });
            const semBadge = `<span class="badge bg-${semColors[r.semester] || 'secondary'} bg-opacity-10 text-${semColors[r.semester] || 'secondary'} border">${escH(r.semester)}</span>`;
            const typeBadge = TYPE_BADGES[r.eval_type] || r.eval_type;

            // Build onclick detail params
            let onclick = '';
            if (r.eval_type === 'student') {
                onclick = `viewDetail('student',${r.evaluator_id},${r.evaluatee_id},${r.section_id},'','')`;
            } else {
                onclick = `viewDetail('${r.eval_type}',${r.evaluator_id},${r.evaluatee_id},0,'${escH(r.school_year)}','${escH(r.semester)}')`;
            }

            html += `
        <tr style="cursor:pointer;" onclick="${onclick}">
            <td class="ps-3 text-muted">${start + i + 1}</td>
            <td>${typeBadge}</td>
            <td>
                <div class="fw-semibold" style="font-size:.85rem;">${escH(r.evaluator_name)}</div>
                <small class="text-muted text-capitalize">${escH(r.evaluator_role)}</small>
            </td>
            <td>
                <div class="fw-semibold" style="font-size:.85rem;">${escH(r.evaluatee_name)}</div>
                <small class="text-muted text-capitalize">${escH(r.evaluatee_role)}</small>
            </td>
            <td>
                <div style="font-size:.85rem;">${escH(r.context)}</div>
                ${r.section_name ? `<small class="text-muted">${escH(r.section_name)}</small>` : ''}
            </td>
            <td>${semBadge}</td>
            <td><small class="text-muted">${date}</small></td>
            <td class="text-center pe-3">
                <button class="btn btn-sm btn-outline-primary" onclick="event.stopPropagation();${onclick}">
                    <i class="bi bi-eye"></i>
                </button>
            </td>
        </tr>`;
        });

        tb.innerHTML = html;
        updatePagination(pagination);
    }

    function viewDetail(evalType, evaluatorId, evaluateeId, sectionId, schoolYear, semester) {
        const modal = new bootstrap.Modal(document.getElementById('detailModal'));
        document.getElementById('modalBody').innerHTML =
            '<div class="text-center py-4"><div class="spinner-border text-primary" role="status"></div></div>';
        document.getElementById('modalTitle').textContent = 'Evaluation Detail';
        document.getElementById('modalSub').textContent = '';
        modal.show();

        let url = `api/eval_submissions.php?action=detail&eval_type=${evalType}&evaluator_id=${evaluatorId}&evaluatee_id=${evaluateeId}`;
        if (evalType === 'student') url += `&section_id=${sectionId}`;
        else url += `&school_year=${encodeURIComponent(schoolYear)}&semester=${encodeURIComponent(semester)}`;

        fetch(url).then(r => r.json()).then(d => {
            if (!d.success) { document.getElementById('modalBody').innerHTML = '<p class="text-danger p-3">Failed to load.</p>'; return; }
            renderDetail(d.data, evalType);
        });
    }

    function renderDetail(data, evalType) {
        const info = data.info;
        const resps = data.responses;

        const typeLabel = evalType === 'student' ? 'Student Evaluation' : evalType === 'peer' ? 'Peer Evaluation' : 'Chair Evaluation';
        document.getElementById('modalTitle').textContent = `${typeLabel}: ${info.evaluatee_name}`;
        document.getElementById('modalSub').textContent = `By: ${info.evaluator_name} · ${info.school_year || ''} ${info.semester || ''} Sem`;

        let html = `
    <div class="row g-2 mb-4 p-3 rounded" style="background:#f8f9fa;border:1px solid #e9ecef;">
        <div class="col-6 col-md-3"><div style="font-size:.65rem;text-transform:uppercase;color:#6c757d;">Evaluator</div><div class="fw-semibold" style="font-size:.85rem;">${escH(info.evaluator_name)}</div><small class="text-muted text-capitalize">${escH(info.evaluator_role)}</small></div>
        <div class="col-6 col-md-3"><div style="font-size:.65rem;text-transform:uppercase;color:#6c757d;">Evaluated</div><div class="fw-semibold" style="font-size:.85rem;">${escH(info.evaluatee_name)}</div><small class="text-muted text-capitalize">${escH(info.evaluatee_role)}</small></div>
        <div class="col-6 col-md-3"><div style="font-size:.65rem;text-transform:uppercase;color:#6c757d;">${evalType === 'student' ? 'Subject' : 'Type'}</div><div class="fw-semibold" style="font-size:.85rem;">${escH(info.course_name || typeLabel)}</div></div>
        <div class="col-6 col-md-3"><div style="font-size:.65rem;text-transform:uppercase;color:#6c757d;">Submitted</div><div class="fw-semibold" style="font-size:.85rem;">${new Date(info.submitted_at).toLocaleDateString('en-PH', { month: 'long', day: 'numeric', year: 'numeric' })}</div></div>
    </div>`;

        let lastCat = '';
        resps.forEach((r, i) => {
            if (r.category !== lastCat) {
                if (lastCat !== '') html += '</div>';
                html += `<div class="mb-3"><div style="font-size:.67rem;text-transform:uppercase;letter-spacing:.1em;color:#1a4a8a;font-weight:700;padding:5px 0;border-bottom:2px solid #ddeeff;margin-bottom:8px;">${escH(r.category)}</div>`;
                lastCat = r.category;
            }
            html += `<div class="mb-3 p-3 rounded" style="background:#fafbff;border:1px solid #e8eef8;">
            <div style="font-size:.82rem;color:#374151;margin-bottom:8px;"><span class="text-muted me-1">${i + 1}.</span>${escH(r.question)}</div>`;
            if (r.type === 'quantitative') {
                const v = r.rating_value ?? 0;
                const pct = v ? Math.round((v / 5) * 100) : 0;
                html += `<div class="d-flex align-items-center gap-3">
                <span class="badge bg-${RATING_COLORS[v]}" style="font-size:.85rem;padding:5px 14px;">${v} — ${RATING_LABELS[v] || '—'}</span>
                <div style="flex:1;height:6px;background:#e9ecef;border-radius:3px;overflow:hidden;">
                    <div style="width:${pct}%;height:100%;background:var(--bs-${RATING_COLORS[v]});border-radius:3px;"></div>
                </div>
                <small class="text-muted">${pct}%</small>
            </div>`;
            } else {
                const ans = r.text_response?.trim() || '';
                html += `<div style="font-size:.88rem;background:#fff;border:1px solid #dee2e6;border-radius:6px;padding:10px 12px;min-height:44px;font-style:${ans ? 'normal' : 'italic'};color:${ans ? 'inherit' : '#adb5bd'};">
                ${ans ? escH(ans) : 'No response provided.'}
            </div>`;
            }
            html += `</div>`;
        });
        if (lastCat !== '') html += '</div>';

        document.getElementById('modalBody').innerHTML = html;
    }

    function updatePagination(p) {
        const s = (p.current_page - 1) * p.records_per_page + 1;
        const e = Math.min(s + p.records_per_page - 1, p.total_records);
        document.getElementById('paginationInfo').textContent = `Showing ${s}–${e} of ${p.total_records} submissions`;
        let html = `<li class="page-item ${p.current_page === 1 ? 'disabled' : ''}"><a class="page-link" href="#" onclick="changePage(${p.current_page - 1});return false;">Previous</a></li>`;
        for (let i = 1; i <= p.total_pages; i++) {
            if (i === 1 || i === p.total_pages || (i >= p.current_page - 2 && i <= p.current_page + 2))
                html += `<li class="page-item ${i === p.current_page ? 'active' : ''}"><a class="page-link" href="#" onclick="changePage(${i});return false;">${i}</a></li>`;
            else if (i === p.current_page - 3 || i === p.current_page + 3)
                html += '<li class="page-item disabled"><a class="page-link">...</a></li>';
        }
        html += `<li class="page-item ${p.current_page === p.total_pages ? 'disabled' : ''}"><a class="page-link" href="#" onclick="changePage(${p.current_page + 1});return false;">Next</a></li>`;
        document.getElementById('pagination').innerHTML = html;
    }
    function changePage(p) { currentPage = p; loadSubmissions(); }

    function exportCSV() {
        const headers = ['#', 'Type', 'Evaluator', 'Evaluator Role', 'Evaluated', 'Evaluated Role', 'Context', 'Semester', 'Date'];
        const rows = allData.map((r, i) => [
            i + 1, r.eval_type,
            `"${r.evaluator_name}"`, r.evaluator_role,
            `"${r.evaluatee_name}"`, r.evaluatee_role,
            `"${r.context}"`, r.semester,
            new Date(r.submitted_at).toLocaleDateString('en-PH')
        ]);
        const csv = [headers.join(','), ...rows.map(r => r.join(','))].join('\n');
        const a = document.createElement('a'); a.href = 'data:text/csv,' + encodeURIComponent(csv); a.download = 'eval_submissions.csv'; a.click();
    }

    function escH(s) { if (!s && s !== 0) return ''; const d = document.createElement('div'); d.textContent = String(s); return d.innerHTML; }
</script>

<script src="assets/js/dashboard.js"></script>
<style>
    .table th {
        font-weight: 600;
        text-transform: uppercase;
        font-size: .82rem;
        letter-spacing: .5px;
    }

    tbody tr:hover {
        background: #f0f6ff !important;
    }
</style>

<?php include 'includes/footer.php'; ?>