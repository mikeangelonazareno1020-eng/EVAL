<?php
/**
 * Filename: question_management.php
 * Path: /admin/question_management.php
 * Manage evaluation questions for all 3 types:
 * Student → Teacher, Peer (Teacher → Teacher), Chair → Teacher
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

        <!-- Page Header -->
        <div class="d-flex justify-content-between align-items-center mb-4">
            <div>
                <h3 class="mb-1">Evaluation Questions</h3>
                <p class="text-muted mb-0">Manage questions for all evaluation types</p>
            </div>
            <button class="btn btn-primary" onclick="openAddModal()">
                <i class="bi bi-plus-circle me-2"></i>Add Question
            </button>
        </div>

        <!-- Type Tabs -->
        <ul class="nav nav-tabs mb-4" id="typeTabs">
            <li class="nav-item">
                <button class="nav-link active" id="tab-student" onclick="switchType('student')">
                    <i class="bi bi-person-check me-1"></i>Student → Teacher
                    <span class="badge bg-info ms-1" id="count-student">—</span>
                </button>
            </li>
            <li class="nav-item">
                <button class="nav-link" id="tab-peer" onclick="switchType('peer')">
                    <i class="bi bi-people me-1"></i>Peer (Teacher → Teacher)
                    <span class="badge bg-primary ms-1" id="count-peer">—</span>
                </button>
            </li>
            <li class="nav-item">
                <button class="nav-link" id="tab-chair" onclick="switchType('chair')">
                    <i class="bi bi-award me-1"></i>Chair → Teacher
                    <span class="badge bg-warning text-dark ms-1" id="count-chair">—</span>
                </button>
            </li>
        </ul>

        <!-- Questions Table -->
        <div class="card">
            <div class="card-header bg-white border-0 pt-3 d-flex justify-content-between align-items-center">
                <h5 class="mb-0" id="tableTitle">Student Evaluation Questions</h5>
                <div class="d-flex gap-2">
                    <select class="form-select form-select-sm" id="categoryFilter" onchange="loadQuestions()"
                        style="width:180px;">
                        <option value="">All Categories</option>
                    </select>
                    <select class="form-select form-select-sm" id="statusFilter" onchange="loadQuestions()"
                        style="width:130px;">
                        <option value="">All Status</option>
                        <option value="1">Active</option>
                        <option value="0">Inactive</option>
                    </select>
                </div>
            </div>
            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table table-hover align-middle mb-0">
                        <thead class="table-light">
                            <tr>
                                <th class="ps-3" width="5%">#</th>
                                <th width="40%">Question</th>
                                <th width="15%">Category</th>
                                <th width="10%">Type</th>
                                <th width="8%">Order</th>
                                <th width="8%">Status</th>
                                <th width="14%" class="text-center pe-3">Actions</th>
                            </tr>
                        </thead>
                        <tbody id="questionsTableBody">
                            <tr>
                                <td colspan="7" class="text-center py-5">
                                    <div class="spinner-border text-primary" role="status"></div>
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

    </div>
</div>

<!-- ══ ADD / EDIT MODAL ══ -->
<div class="modal fade" id="questionModal" tabindex="-1" aria-labelledby="questionModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="questionModalLabel">Add Question</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form id="questionForm">
                <div class="modal-body">
                    <input type="hidden" id="qId">
                    <input type="hidden" id="qEvalType">

                    <div class="mb-3">
                        <label class="form-label">Evaluation Type <span class="text-danger">*</span></label>
                        <select class="form-select" id="qEvalTypeSelect" required onchange="onEvalTypeChange()">
                            <option value="">Select type</option>
                            <option value="student">Student → Teacher</option>
                            <option value="peer">Peer (Teacher → Teacher)</option>
                            <option value="chair">Chair → Teacher</option>
                        </select>
                    </div>

                    <div class="mb-3">
                        <label class="form-label">Question <span class="text-danger">*</span></label>
                        <textarea class="form-control" id="qText" rows="3" required
                            placeholder="Enter the evaluation question..."></textarea>
                        <div class="invalid-feedback">Question text is required.</div>
                    </div>

                    <div class="row g-3">
                        <div class="col-md-4">
                            <label class="form-label">Question Type <span class="text-danger">*</span></label>
                            <select class="form-select" id="qType" required>
                                <option value="quantitative">Quantitative (1–5 Rating)</option>
                                <option value="qualitative">Qualitative (Text Response)</option>
                            </select>
                        </div>
                        <div class="col-md-4">
                            <label class="form-label">Category <span class="text-danger">*</span></label>
                            <div class="input-group">
                                <input type="text" class="form-control" id="qCategory" required
                                    placeholder="e.g. Teaching Quality" list="categoryList">
                                <datalist id="categoryList"></datalist>
                            </div>
                            <small class="text-muted">Type or pick existing category</small>
                        </div>
                        <div class="col-md-4">
                            <label class="form-label">Sort Order</label>
                            <input type="number" class="form-control" id="qOrder" value="0" min="0">
                            <small class="text-muted">Lower = shown first</small>
                        </div>
                    </div>

                    <div class="mt-3">
                        <div class="form-check form-switch">
                            <input class="form-check-input" type="checkbox" id="qIsActive" checked>
                            <label class="form-check-label" for="qIsActive">Active (shown in evaluations)</label>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-primary" id="qSubmitBtn">Save Question</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- ══ DELETE MODAL ══ -->
<div class="modal fade" id="deleteModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header bg-danger text-white">
                <h5 class="modal-title">Delete Question</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <p>Are you sure you want to delete this question?</p>
                <p class="text-muted mb-0 fst-italic" id="deleteQText"></p>
                <div class="alert alert-warning mt-3 mb-0 small">
                    <i class="bi bi-exclamation-triangle me-1"></i>
                    Any existing responses to this question will also be deleted.
                </div>
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
    let currentType = 'student';
    let deleteId = null;
    let deleteType = null;

    const TYPE_LABELS = {
        student: 'Student Evaluation Questions',
        peer: 'Peer Evaluation Questions',
        chair: 'Chair Evaluation Questions',
    };

    const TYPE_COLORS = {
        student: 'info',
        peer: 'primary',
        chair: 'warning',
    };

    // ── INIT ───────────────────────────────────────────────────
    window.onload = () => setTimeout(() => {
        document.getElementById('loadingOverlay').style.display = 'none';
        document.getElementById('dashboardContent').style.display = 'block';
        loadAllCounts();
        loadQuestions();
    }, 600);

    // ── SWITCH TYPE TAB ────────────────────────────────────────
    function switchType(type) {
        currentType = type;
        document.querySelectorAll('#typeTabs .nav-link').forEach(b => b.classList.remove('active'));
        document.getElementById(`tab-${type}`).classList.add('active');
        document.getElementById('tableTitle').textContent = TYPE_LABELS[type];
        document.getElementById('categoryFilter').innerHTML = '<option value="">All Categories</option>';
        loadQuestions();
    }

    // ── LOAD ALL COUNTS ────────────────────────────────────────
    function loadAllCounts() {
        ['student', 'peer', 'chair'].forEach(type => {
            fetch(`api/questions.php?action=read&type=${type}&limit=999`)
                .then(r => r.json()).then(d => {
                    if (!d.success) return;
                    document.getElementById(`count-${type}`).textContent = d.pagination.total_records;
                });
        });
    }

    // ── LOAD QUESTIONS ─────────────────────────────────────────
    function loadQuestions() {
        const tb = document.getElementById('questionsTableBody');
        tb.innerHTML = '<tr><td colspan="7" class="text-center py-4"><div class="spinner-border text-primary" role="status"></div></td></tr>';

        const cat = document.getElementById('categoryFilter').value;
        const status = document.getElementById('statusFilter').value;

        let url = `api/questions.php?action=read&type=${currentType}`;
        if (cat) url += `&category=${encodeURIComponent(cat)}`;
        if (status !== '') url += `&status=${status}`;

        fetch(url).then(r => r.json()).then(d => {
            if (!d.success) { tb.innerHTML = '<tr><td colspan="7" class="text-center text-danger py-4">Error loading questions.</td></tr>'; return; }

            // Build category datalist & filter options
            const cats = [...new Set(d.data.map(q => q.category).filter(Boolean))];
            const catSel = document.getElementById('categoryFilter');
            const existing = Array.from(catSel.options).map(o => o.value);
            cats.forEach(c => { if (!existing.includes(c)) catSel.innerHTML += `<option value="${escH(c)}">${escH(c)}</option>`; });

            if (!d.data.length) {
                tb.innerHTML = '<tr><td colspan="7" class="text-center py-4 text-muted">No questions found.</td></tr>'; return;
            }

            let html = '';
            d.data.forEach((q, i) => {
                const typeBadge = q.type === 'quantitative'
                    ? '<span class="badge bg-primary bg-opacity-10  border">Rating</span>'
                    : '<span class="badge bg-success bg-opacity-10  border">Text</span>';
                const statusBadge = q.is_active == 1
                    ? '<span class="badge bg-success">Active</span>'
                    : '<span class="badge bg-secondary">Inactive</span>';

                html += `
            <tr>
                <td class="ps-3 text-muted">${i + 1}</td>
                <td>
                    <div style="font-size:.88rem;line-height:1.5;">${escH(q.question)}</div>
                </td>
                <td>
                    <span class="badge bg-light text-dark border" style="font-size:.75rem;">
                        ${escH(q.category || '—')}
                    </span>
                </td>
                <td>${typeBadge}</td>
                <td>
                    <span class="badge bg-light text-dark border">${q.sort_order}</span>
                </td>
                <td>${statusBadge}</td>
                <td class="text-center pe-3">
                    <div class="btn-group btn-group-sm">
                        <button class="btn btn-outline-primary" onclick="editQuestion(${q.id},'${currentType}')" title="Edit">
                            <i class="bi bi-pencil"></i>
                        </button>
                        <button class="btn btn-outline-${q.is_active == 1 ? 'warning' : 'success'}"
                                onclick="toggleQuestion(${q.id},'${currentType}')"
                                title="${q.is_active == 1 ? 'Deactivate' : 'Activate'}">
                            <i class="bi bi-toggle-${q.is_active == 1 ? 'on' : 'off'}"></i>
                        </button>
                        <button class="btn btn-outline-danger" onclick="confirmDelete(${q.id},'${currentType}','${escH(q.question.substring(0, 60))}...')" title="Delete">
                            <i class="bi bi-trash"></i>
                        </button>
                    </div>
                </td>
            </tr>`;
            });

            tb.innerHTML = html;
            document.getElementById(`count-${currentType}`).textContent = d.data.length;
        });
    }

    // ── OPEN ADD MODAL ─────────────────────────────────────────
    function openAddModal() {
        document.getElementById('questionForm').reset();
        document.getElementById('questionForm').classList.remove('was-validated');
        document.getElementById('qId').value = '';
        document.getElementById('qEvalTypeSelect').value = currentType;
        document.getElementById('qIsActive').checked = true;
        document.getElementById('questionModalLabel').textContent = 'Add Question';
        document.getElementById('qSubmitBtn').textContent = 'Save Question';
        loadCategoryDatalist(currentType);
        new bootstrap.Modal(document.getElementById('questionModal')).show();
    }

    // ── EDIT QUESTION ──────────────────────────────────────────
    function editQuestion(id, type) {
        fetch(`api/questions.php?action=read_one&id=${id}&type=${type}`)
            .then(r => r.json()).then(d => {
                if (!d.success) { showToast('Error', 'Failed to load question.', 'error'); return; }
                const q = d.data;
                document.getElementById('qId').value = q.id;
                document.getElementById('qEvalTypeSelect').value = type;
                document.getElementById('qText').value = q.question;
                document.getElementById('qType').value = q.type;
                document.getElementById('qCategory').value = q.category || '';
                document.getElementById('qOrder').value = q.sort_order;
                document.getElementById('qIsActive').checked = q.is_active == 1;
                document.getElementById('questionModalLabel').textContent = 'Edit Question';
                document.getElementById('qSubmitBtn').textContent = 'Update Question';
                loadCategoryDatalist(type);
                new bootstrap.Modal(document.getElementById('questionModal')).show();
            });
    }

    // ── LOAD CATEGORY DATALIST ─────────────────────────────────
    function loadCategoryDatalist(type) {
        fetch(`api/questions.php?action=categories&type=${type}`)
            .then(r => r.json()).then(d => {
                if (!d.success) return;
                const dl = document.getElementById('categoryList');
                dl.innerHTML = d.data.map(c => `<option value="${escH(c)}">`).join('');
            });
    }

    function onEvalTypeChange() {
        loadCategoryDatalist(document.getElementById('qEvalTypeSelect').value);
    }

    // ── SAVE QUESTION ──────────────────────────────────────────
    document.getElementById('questionForm').addEventListener('submit', function (e) {
        e.preventDefault();
        if (!this.checkValidity()) { this.classList.add('was-validated'); return; }

        const btn = document.getElementById('qSubmitBtn');
        btn.disabled = true;
        btn.innerHTML = '<span class="spinner-border spinner-border-sm me-2"></span>Saving...';

        const id = document.getElementById('qId').value;
        const evalType = document.getElementById('qEvalTypeSelect').value;

        fetch(`api/questions.php?action=${id ? 'update' : 'create'}`, {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify({
                id: id || undefined,
                eval_type: evalType,
                question: document.getElementById('qText').value,
                type: document.getElementById('qType').value,
                category: document.getElementById('qCategory').value,
                sort_order: parseInt(document.getElementById('qOrder').value) || 0,
                is_active: document.getElementById('qIsActive').checked ? 1 : 0,
            })
        }).then(r => r.json()).then(d => {
            if (d.success) {
                showToast('Success', d.message, 'success');
                bootstrap.Modal.getInstance(document.getElementById('questionModal')).hide();
                loadQuestions();
                loadAllCounts();
            } else {
                showToast('Error', d.message, 'error');
            }
        }).catch(() => showToast('Error', 'An error occurred.', 'error'))
            .finally(() => {
                btn.disabled = false;
                btn.textContent = id ? 'Update Question' : 'Save Question';
            });
    });

    // ── TOGGLE ACTIVE ──────────────────────────────────────────
    function toggleQuestion(id, type) {
        fetch('api/questions.php?action=toggle', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify({ id, eval_type: type })
        }).then(r => r.json()).then(d => {
            if (d.success) { showToast('Success', d.message, 'success'); loadQuestions(); }
            else showToast('Error', d.message, 'error');
        });
    }

    // ── DELETE ─────────────────────────────────────────────────
    function confirmDelete(id, type, preview) {
        deleteId = id;
        deleteType = type;
        document.getElementById('deleteQText').textContent = preview;
        new bootstrap.Modal(document.getElementById('deleteModal')).show();
    }

    document.getElementById('confirmDeleteBtn').addEventListener('click', function () {
        if (!deleteId) return;
        this.disabled = true;
        this.innerHTML = '<span class="spinner-border spinner-border-sm me-2"></span>Deleting...';

        fetch('api/questions.php?action=delete', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify({ id: deleteId, eval_type: deleteType })
        }).then(r => r.json()).then(d => {
            if (d.success) {
                showToast('Success', d.message, 'success');
                bootstrap.Modal.getInstance(document.getElementById('deleteModal')).hide();
                loadQuestions();
                loadAllCounts();
            } else showToast('Error', d.message, 'error');
        }).finally(() => {
            this.disabled = false;
            this.textContent = 'Delete';
            deleteId = deleteType = null;
        });
    });

    // ── HELPERS ────────────────────────────────────────────────
    function escH(s) { if (!s && s !== 0) return ''; const d = document.createElement('div'); d.textContent = String(s); return d.innerHTML; }
    function showToast(title, message, type = 'info') {
        const c = { success: '#28a745', error: '#dc3545', warning: '#ffc107', info: '#17a2b8' };
        const el = document.createElement('div');
        el.className = 'position-fixed top-0 end-0 p-3'; el.style.zIndex = '9999';
        el.innerHTML = `<div class="toast show"><div class="toast-header" style="background:${c[type]};color:white;"><strong class="me-auto">${title}</strong><button type="button" class="btn-close btn-close-white" onclick="this.closest('.position-fixed').remove()"></button></div><div class="toast-body">${message}</div></div>`;
        document.body.appendChild(el); setTimeout(() => el.remove(), 3500);
    }
</script>

<script src="assets/js/dashboard.js"></script>
<style>
    .table th {
        font-weight: 600;
        text-transform: uppercase;
        font-size: .82rem;
        letter-spacing: .5px;
    }

    .nav-tabs .nav-link {
        cursor: pointer;
    }

    .btn-group-sm .btn {
        padding: .25rem .5rem;
    }
</style>

<?php include 'includes/footer.php'; ?>