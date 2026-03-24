<?php
// ============================================================
// FILE: evaluation_management.php
// ============================================================
session_start();

if (!isset($_SESSION['user_id'])) {
    header("Location: ../index.php");
    exit();
}

// Simulated session role - replace with actual DB query
$user_role = $_SESSION['role'] ?? 'teacher'; // admin | teacher | programchair
?>

<?php
// ============================================================
// FILE: evaluation_management.php
// ============================================================ include 'includes/header.php'; ?>

<!-- Loading Overlay -->
<div id="loadingOverlay">
    <div class="loader"></div>
</div>

<!-- Page Content -->
<div id="dashboardContent">
    <div style="min-height: calc(100vh - 200px); padding: 25px 0;">

        <!-- Page Header -->
        <div class="d-flex justify-content-between align-items-center mb-4">
            <div>
                <h3 class="mb-1">Evaluation Management</h3>
                <p class="text-muted mb-0">Manage peer evaluation forms and track submissions</p>
            </div>
            <?php
            // ============================================================
// FILE: evaluation_management.php
// ============================================================ if ($user_role === 'admin'): ?>
            <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#createEvaluationModal">
                <i class='bx bx-plus me-1'></i> Create Evaluation Form
            </button>
            <?php
            // ============================================================
// FILE: evaluation_management.php
// ============================================================ endif; ?>
        </div>

        <!-- Stats Cards -->
        <div class="row mb-4">
            <div class="col-lg-3 col-md-6 mb-3">
                <div class="card border-0 shadow-sm h-100">
                    <div class="card-body">
                        <div class="d-flex justify-content-between align-items-center">
                            <div>
                                <p class="text-muted mb-1 small">Total Forms</p>
                                <h3 class="mb-0 fw-bold" id="stat-total">0</h3>
                                <small class="text-primary"><i class='bx bx-layer'></i> All evaluation types</small>
                            </div>
                            <div class="bg-primary bg-opacity-10 p-3 rounded">
                                <i class='bx bx-file fs-2 text-primary'></i>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-lg-3 col-md-6 mb-3">
                <div class="card border-0 shadow-sm h-100">
                    <div class="card-body">
                        <div class="d-flex justify-content-between align-items-center">
                            <div>
                                <p class="text-muted mb-1 small">Peer to Peer</p>
                                <h3 class="mb-0 fw-bold" id="stat-peer">0</h3>
                                <small class="text-info"><i class='bx bx-transfer'></i> Teacher ↔ Teacher</small>
                            </div>
                            <div class="bg-info bg-opacity-10 p-3 rounded">
                                <i class='bx bx-group fs-2 text-info'></i>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-lg-3 col-md-6 mb-3">
                <div class="card border-0 shadow-sm h-100">
                    <div class="card-body">
                        <div class="d-flex justify-content-between align-items-center">
                            <div>
                                <p class="text-muted mb-1 small">Chair to Teacher</p>
                                <h3 class="mb-0 fw-bold" id="stat-chair">0</h3>
                                <small class="text-warning"><i class='bx bx-crown'></i> Program Chair eval</small>
                            </div>
                            <div class="bg-warning bg-opacity-10 p-3 rounded">
                                <i class='bx bx-award fs-2 text-warning'></i>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-lg-3 col-md-6 mb-3">
                <div class="card border-0 shadow-sm h-100">
                    <div class="card-body">
                        <div class="d-flex justify-content-between align-items-center">
                            <div>
                                <p class="text-muted mb-1 small">Active Forms</p>
                                <h3 class="mb-0 fw-bold" id="stat-active">0</h3>
                                <small class="text-success"><i class='bx bx-check-circle'></i> Currently open</small>
                            </div>
                            <div class="bg-success bg-opacity-10 p-3 rounded">
                                <i class='bx bx-toggle-right fs-2 text-success'></i>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Filter Tabs + Search -->
        <div class="card border-0 shadow-sm mb-4">
            <div class="card-body pb-0">
                <div class="d-flex justify-content-between align-items-center flex-wrap gap-2 mb-3">
                    <ul class="nav nav-tabs border-0" id="evalTabs">
                        <li class="nav-item">
                            <a class="nav-link active px-3" href="#" data-filter="all">All Forms</a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link px-3" href="#" data-filter="peer_to_peer">Peer to Peer</a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link px-3" href="#" data-filter="chair_to_teacher">Chair to Teacher</a>
                        </li>
                    </ul>
                    <div class="d-flex gap-2">
                        <input type="text" class="form-control form-control-sm" id="searchForms"
                            placeholder="Search forms..." style="width: 200px;">
                        <select class="form-select form-select-sm" id="filterStatus" style="width: 130px;">
                            <option value="">All Status</option>
                            <option value="active">Active</option>
                            <option value="inactive">Inactive</option>
                        </select>
                    </div>
                </div>
            </div>
        </div>

        <!-- Evaluation Forms Grid -->
        <div class="row" id="evaluationFormsContainer">
            <!-- Cards rendered by JS -->
        </div>

        <!-- Empty State -->
        <div id="emptyState" class="text-center py-5 d-none">
            <i class='bx bx-file-blank fs-1 text-muted'></i>
            <p class="text-muted mt-2">No evaluation forms found.</p>
            <?php
            // ============================================================
// FILE: evaluation_management.php
// ============================================================ if ($user_role === 'admin'): ?>
            <button class="btn btn-sm btn-primary" data-bs-toggle="modal" data-bs-target="#createEvaluationModal">
                <i class='bx bx-plus'></i> Create one now
            </button>
            <?php
            // ============================================================
// FILE: evaluation_management.php
// ============================================================ endif; ?>
        </div>

    </div>
</div>

<!-- ===================== CREATE/EDIT EVALUATION MODAL ===================== -->
<?php
// ============================================================
// FILE: evaluation_management.php
// ============================================================ if ($user_role === 'admin'): ?>
<div class="modal fade" id="createEvaluationModal" tabindex="-1" data-bs-backdrop="static">
    <div class="modal-dialog modal-xl modal-dialog-scrollable">
        <div class="modal-content">
            <div class="modal-header border-0 pb-0">
                <div>
                    <h5 class="modal-title fw-bold mb-0" id="modalTitle">Create Evaluation Form</h5>
                    <small class="text-muted">Build a customizable evaluation form</small>
                </div>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body pt-2">

                <!-- Step Wizard -->
                <div class="eval-wizard mb-4">
                    <div class="d-flex align-items-center justify-content-center gap-0">
                        <div class="wizard-step active" data-step="1">
                            <div class="step-circle">1</div>
                            <span class="step-label">Basic Info</span>
                        </div>
                        <div class="wizard-line"></div>
                        <div class="wizard-step" data-step="2">
                            <div class="step-circle">2</div>
                            <span class="step-label">Questions</span>
                        </div>
                        <div class="wizard-line"></div>
                        <div class="wizard-step" data-step="3">
                            <div class="step-circle">3</div>
                            <span class="step-label">Preview</span>
                        </div>
                    </div>
                </div>

                <!-- Step 1: Basic Info -->
                <div class="wizard-panel" id="step1">
                    <div class="row g-3">
                        <div class="col-md-8">
                            <label class="form-label fw-semibold">Form Title <span class="text-danger">*</span></label>
                            <input type="text" class="form-control" id="formTitle"
                                placeholder="e.g. Mid-Year Peer Evaluation 2026">
                        </div>
                        <div class="col-md-4">
                            <label class="form-label fw-semibold">Evaluation Type <span
                                    class="text-danger">*</span></label>
                            <select class="form-select" id="evalType">
                                <option value="">Select type...</option>
                                <option value="peer_to_peer">Peer to Peer (Teacher → Teacher)</option>
                                <option value="chair_to_teacher">Program Chair → Teacher</option>
                            </select>
                        </div>
                        <div class="col-md-12">
                            <label class="form-label fw-semibold">Description</label>
                            <textarea class="form-control" id="formDescription" rows="2"
                                placeholder="Brief description of this evaluation form..."></textarea>
                        </div>
                        <div class="col-md-4">
                            <label class="form-label fw-semibold">School Year</label>
                            <input type="text" class="form-control" id="schoolYear" placeholder="e.g. 2025-2026">
                        </div>
                        <div class="col-md-4">
                            <label class="form-label fw-semibold">Semester</label>
                            <select class="form-select" id="semester">
                                <option value="1st">1st Semester</option>
                                <option value="2nd">2nd Semester</option>
                                <option value="summer">Summer</option>
                            </select>
                        </div>
                        <div class="col-md-4">
                            <label class="form-label fw-semibold">Status</label>
                            <select class="form-select" id="formStatus">
                                <option value="active">Active</option>
                                <option value="inactive">Inactive</option>
                            </select>
                        </div>

                        <!-- Rating Scale Config -->
                        <div class="col-12">
                            <div class="card bg-light border-0">
                                <div class="card-body">
                                    <h6 class="fw-semibold mb-3"><i class='bx bx-slider me-1'></i>Rating Scale (for
                                        Quantitative Questions)</h6>
                                    <div class="row g-2">
                                        <div class="col-md-3">
                                            <label class="form-label small">Min Value</label>
                                            <input type="number" class="form-control form-control-sm" id="ratingMin"
                                                value="1" min="0" max="1">
                                        </div>
                                        <div class="col-md-3">
                                            <label class="form-label small">Max Value</label>
                                            <input type="number" class="form-control form-control-sm" id="ratingMax"
                                                value="5" min="2" max="10">
                                        </div>
                                        <div class="col-md-6">
                                            <label class="form-label small">Labels (comma separated, e.g.
                                                Poor,Fair,Good,Very Good,Excellent)</label>
                                            <input type="text" class="form-control form-control-sm" id="ratingLabels"
                                                value="Poor,Fair,Good,Very Good,Excellent">
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Step 2: Questions Builder -->
                <div class="wizard-panel d-none" id="step2">
                    <div class="d-flex justify-content-between align-items-center mb-3">
                        <h6 class="mb-0 fw-semibold">Question Builder</h6>
                        <div class="d-flex gap-2">
                            <button class="btn btn-sm btn-outline-primary" onclick="addQuestion('quantitative')">
                                <i class='bx bx-bar-chart-alt-2 me-1'></i> Add Quantitative
                            </button>
                            <button class="btn btn-sm btn-outline-secondary" onclick="addQuestion('qualitative')">
                                <i class='bx bx-comment-detail me-1'></i> Add Qualitative
                            </button>
                        </div>
                    </div>

                    <!-- Legend -->
                    <div class="d-flex gap-3 mb-3">
                        <span
                            class="badge bg-primary bg-opacity-10 text-primary border border-primary border-opacity-25 fw-normal px-2 py-1">
                            <i class='bx bx-bar-chart-alt-2 me-1'></i>Quantitative = Rating scale (1–5)
                        </span>
                        <span
                            class="badge bg-secondary bg-opacity-10 text-secondary border border-secondary border-opacity-25 fw-normal px-2 py-1">
                            <i class='bx bx-comment-detail me-1'></i>Qualitative = Open-ended text response
                        </span>
                    </div>

                    <!-- Questions List -->
                    <div id="questionsList">
                        <div class="text-center text-muted py-4 border rounded" id="noQuestionsMsg">
                            <i class='bx bx-list-plus fs-3'></i>
                            <p class="mb-0 mt-1 small">No questions yet. Add quantitative or qualitative questions
                                above.</p>
                        </div>
                    </div>
                </div>

                <!-- Step 3: Preview -->
                <div class="wizard-panel d-none" id="step3">
                    <div id="previewContainer">
                        <!-- Preview rendered by JS -->
                    </div>
                </div>

            </div>
            <div class="modal-footer border-0">
                <button type="button" class="btn btn-light" id="btnBack" onclick="wizardBack()" style="display:none">
                    <i class='bx bx-chevron-left'></i> Back
                </button>
                <button type="button" class="btn btn-light" data-bs-dismiss="modal">Cancel</button>
                <button type="button" class="btn btn-primary" id="btnNext" onclick="wizardNext()">
                    Next <i class='bx bx-chevron-right'></i>
                </button>
                <button type="button" class="btn btn-success d-none" id="btnSave" onclick="saveForm()">
                    <i class='bx bx-save me-1'></i> Save Form
                </button>
            </div>
        </div>
    </div>
</div>
<?php
// ============================================================
// FILE: evaluation_management.php
// ============================================================ endif; ?>

<!-- ===================== VIEW / FILL EVALUATION MODAL ===================== -->
<div class="modal fade" id="viewEvaluationModal" tabindex="-1" data-bs-backdrop="static">
    <div class="modal-dialog modal-lg modal-dialog-scrollable">
        <div class="modal-content">
            <div class="modal-header border-0 pb-0">
                <div>
                    <h5 class="modal-title fw-bold mb-0" id="viewModalTitle">Evaluation Form</h5>
                    <small class="text-muted" id="viewModalSubtitle"></small>
                </div>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body" id="viewModalBody">
                <!-- Rendered by JS -->
            </div>
            <div class="modal-footer border-0">
                <button type="button" class="btn btn-light" data-bs-dismiss="modal">Close</button>
                <button type="button" class="btn btn-primary" id="btnSubmitEval" onclick="submitEvaluation()">
                    <i class='bx bx-send me-1'></i> Submit Evaluation
                </button>
            </div>
        </div>
    </div>
</div>

<!-- ===================== DELETE CONFIRM MODAL ===================== -->
<div class="modal fade" id="deleteModal" tabindex="-1">
    <div class="modal-dialog modal-sm">
        <div class="modal-content">
            <div class="modal-body text-center py-4">
                <div class="bg-danger bg-opacity-10 rounded-circle d-inline-flex p-3 mb-3">
                    <i class='bx bx-trash fs-3 text-danger'></i>
                </div>
                <h6 class="fw-bold">Delete this form?</h6>
                <p class="text-muted small mb-0">This action cannot be undone.</p>
            </div>
            <div class="modal-footer border-0 justify-content-center pt-0">
                <button class="btn btn-light btn-sm" data-bs-dismiss="modal">Cancel</button>
                <button class="btn btn-danger btn-sm" onclick="confirmDelete()">Delete</button>
            </div>
        </div>
    </div>
</div>

<!-- Toast Notification -->
<div class="position-fixed bottom-0 end-0 p-3" style="z-index: 9999">
    <div id="toastMsg" class="toast align-items-center text-bg-success border-0" role="alert">
        <div class="d-flex">
            <div class="toast-body" id="toastText">Action completed.</div>
            <button type="button" class="btn-close btn-close-white me-2 m-auto" data-bs-dismiss="toast"></button>
        </div>
    </div>
</div>

<style>
    /* Wizard Steps */
    .eval-wizard {
        padding: 10px 0;
    }

    .wizard-step {
        display: flex;
        flex-direction: column;
        align-items: center;
        gap: 4px;
    }

    .step-circle {
        width: 36px;
        height: 36px;
        border-radius: 50%;
        background: #e9ecef;
        color: #6c757d;
        display: flex;
        align-items: center;
        justify-content: center;
        font-weight: 700;
        font-size: 14px;
        transition: all .2s;
    }

    .wizard-step.active .step-circle,
    .wizard-step.done .step-circle {
        background: var(--bs-primary);
        color: #fff;
    }

    .step-label {
        font-size: 11px;
        color: #6c757d;
        white-space: nowrap;
    }

    .wizard-step.active .step-label {
        color: var(--bs-primary);
        font-weight: 600;
    }

    .wizard-line {
        flex: 1;
        height: 2px;
        background: #dee2e6;
        min-width: 60px;
        margin-bottom: 18px;
    }

    /* Question Cards */
    .question-card {
        border: 1px solid #dee2e6;
        border-radius: 10px;
        padding: 14px 16px;
        margin-bottom: 10px;
        background: #fff;
        position: relative;
        transition: box-shadow .15s;
    }

    .question-card:hover {
        box-shadow: 0 2px 8px rgba(0, 0, 0, .08);
    }

    .question-card.quantitative {
        border-left: 4px solid var(--bs-primary);
    }

    .question-card.qualitative {
        border-left: 4px solid #6c757d;
    }

    .question-drag {
        cursor: grab;
        color: #adb5bd;
    }

    /* Rating Radio Buttons in Preview */
    .rating-group {
        display: flex;
        gap: 8px;
        flex-wrap: wrap;
    }

    .rating-btn input[type=radio] {
        display: none;
    }

    .rating-btn label {
        width: 42px;
        height: 42px;
        border-radius: 8px;
        border: 2px solid #dee2e6;
        display: flex;
        flex-direction: column;
        align-items: center;
        justify-content: center;
        cursor: pointer;
        font-size: 13px;
        font-weight: 700;
        transition: all .15s;
        line-height: 1;
    }

    .rating-btn label small {
        font-size: 8px;
        font-weight: 400;
        margin-top: 2px;
    }

    .rating-btn input:checked+label {
        background: var(--bs-primary);
        border-color: var(--bs-primary);
        color: #fff;
    }

    .rating-btn label:hover {
        border-color: var(--bs-primary);
        color: var(--bs-primary);
    }

    /* Eval Form Cards */
    .eval-form-card {
        transition: transform .15s, box-shadow .15s;
        cursor: pointer;
    }

    .eval-form-card:hover {
        transform: translateY(-2px);
        box-shadow: 0 6px 20px rgba(0, 0, 0, .1) !important;
    }

    .type-badge-peer {
        background: rgba(13, 110, 253, .1);
        color: #0d6efd;
    }

    .type-badge-chair {
        background: rgba(255, 193, 7, .15);
        color: #856404;
    }
</style>

<script>
    const USER_ROLE = '<?= $user_role ?>';
    let currentStep = 1;
    let questions = [];
    let evaluationForms = [];
    let editingFormId = null;
    let deletingFormId = null;
    let currentViewForm = null;

    // ─── SAMPLE DATA (replace with PHP/AJAX from DB) ───────────────────────
    evaluationForms = [
        {
            id: 1, title: 'Mid-Year Peer Evaluation 2026', type: 'peer_to_peer',
            description: 'Semester performance review between teaching peers.',
            school_year: '2025-2026', semester: '1st', status: 'active',
            rating_min: 1, rating_max: 5,
            rating_labels: ['Poor', 'Fair', 'Good', 'Very Good', 'Excellent'],
            questions: [
                { id: 1, type: 'quantitative', text: 'How effectively does this teacher communicate complex topics to students?' },
                { id: 2, type: 'quantitative', text: 'Rate the teacher\'s classroom management and discipline approach.' },
                { id: 3, type: 'quantitative', text: 'How well does the teacher prepare and deliver lesson materials?' },
                { id: 4, type: 'qualitative', text: 'What are the key strengths of this teacher that you have observed?' },
                { id: 5, type: 'qualitative', text: 'Suggest areas where this teacher can improve professionally.' },
            ]
        },
        {
            id: 2, title: 'Program Chair Evaluation – 2nd Sem', type: 'chair_to_teacher',
            description: 'Official evaluation by Program Chair for all faculty.',
            school_year: '2025-2026', semester: '2nd', status: 'active',
            rating_min: 1, rating_max: 5,
            rating_labels: ['Poor', 'Fair', 'Good', 'Very Good', 'Excellent'],
            questions: [
                { id: 1, type: 'quantitative', text: 'Adherence to syllabi and course outcomes.' },
                { id: 2, type: 'quantitative', text: 'Punctuality and professional conduct.' },
                { id: 3, type: 'quantitative', text: 'Responsiveness to student concerns and needs.' },
                { id: 4, type: 'quantitative', text: 'Participation in department activities and meetings.' },
                { id: 5, type: 'qualitative', text: 'Additional remarks or commendations for this faculty member.' },
            ]
        }
    ];

    // ─── RENDER FORMS ────────────────────────────────────────────────────────
    function renderForms(filter = 'all', search = '', status = '') {
        const container = document.getElementById('evaluationFormsContainer');
        const empty = document.getElementById('emptyState');
        let filtered = evaluationForms.filter(f => {
            const matchFilter = filter === 'all' || f.type === filter;
            const matchSearch = !search || f.title.toLowerCase().includes(search.toLowerCase());
            const matchStatus = !status || f.status === status;
            return matchFilter && matchSearch && matchStatus;
        });

        // Update stats
        document.getElementById('stat-total').textContent = evaluationForms.length;
        document.getElementById('stat-peer').textContent = evaluationForms.filter(f => f.type === 'peer_to_peer').length;
        document.getElementById('stat-chair').textContent = evaluationForms.filter(f => f.type === 'chair_to_teacher').length;
        document.getElementById('stat-active').textContent = evaluationForms.filter(f => f.status === 'active').length;

        if (!filtered.length) {
            container.innerHTML = '';
            empty.classList.remove('d-none');
            return;
        }
        empty.classList.add('d-none');

        container.innerHTML = filtered.map(form => {
            const typeBadge = form.type === 'peer_to_peer'
                ? `<span class="badge type-badge-peer"><i class='bx bx-transfer me-1'></i>Peer to Peer</span>`
                : `<span class="badge type-badge-chair"><i class='bx bx-crown me-1'></i>Chair to Teacher</span>`;
            const statusBadge = form.status === 'active'
                ? `<span class="badge bg-success-subtle text-success border border-success border-opacity-25">Active</span>`
                : `<span class="badge bg-secondary-subtle text-secondary">Inactive</span>`;
            const qCount = form.questions.length;
            const quant = form.questions.filter(q => q.type === 'quantitative').length;
            const qual = form.questions.filter(q => q.type === 'qualitative').length;

            const adminActions = USER_ROLE === 'admin' ? `
            <button class="btn btn-sm btn-outline-warning" onclick="editForm(${form.id})" title="Edit">
                <i class='bx bx-edit-alt'></i>
            </button>
            <button class="btn btn-sm btn-outline-danger" onclick="deleteForm(${form.id})" title="Delete">
                <i class='bx bx-trash'></i>
            </button>` : '';

            return `
        <div class="col-lg-6 col-xl-4 mb-4">
            <div class="card border-0 shadow-sm h-100 eval-form-card">
                <div class="card-body d-flex flex-column">
                    <div class="d-flex justify-content-between align-items-start mb-2">
                        ${typeBadge}
                        ${statusBadge}
                    </div>
                    <h6 class="fw-bold mb-1">${form.title}</h6>
                    <p class="text-muted small mb-3">${form.description || ''}</p>
                    <div class="d-flex gap-2 flex-wrap mb-3">
                        <span class="badge bg-light text-secondary border"><i class='bx bx-calendar me-1'></i>${form.school_year} · ${form.semester} Sem</span>
                        <span class="badge bg-light text-secondary border"><i class='bx bx-list-ul me-1'></i>${qCount} Questions</span>
                    </div>
                    <div class="d-flex gap-2 mb-3">
                        <span class="badge bg-primary bg-opacity-10 text-primary fw-normal">
                            <i class='bx bx-bar-chart-alt-2 me-1'></i>${quant} Quantitative
                        </span>
                        <span class="badge bg-secondary bg-opacity-10 text-secondary fw-normal">
                            <i class='bx bx-comment-detail me-1'></i>${qual} Qualitative
                        </span>
                    </div>
                    <div class="mt-auto d-flex gap-2 justify-content-end">
                        ${adminActions}
                        <button class="btn btn-sm btn-primary" onclick="viewForm(${form.id})">
                            <i class='bx bx-show me-1'></i>${USER_ROLE === 'admin' ? 'Preview' : 'Fill Out'}
                        </button>
                    </div>
                </div>
            </div>
        </div>`;
        }).join('');
    }

    // ─── FILTER TABS ─────────────────────────────────────────────────────────
    document.querySelectorAll('#evalTabs .nav-link').forEach(link => {
        link.addEventListener('click', e => {
            e.preventDefault();
            document.querySelectorAll('#evalTabs .nav-link').forEach(l => l.classList.remove('active'));
            link.classList.add('active');
            applyFilters();
        });
    });
    document.getElementById('searchForms').addEventListener('input', applyFilters);
    document.getElementById('filterStatus').addEventListener('change', applyFilters);

    function applyFilters() {
        const filter = document.querySelector('#evalTabs .nav-link.active').dataset.filter;
        const search = document.getElementById('searchForms').value;
        const status = document.getElementById('filterStatus').value;
        renderForms(filter, search, status);
    }

    // ─── WIZARD ──────────────────────────────────────────────────────────────
    function wizardNext() {
        if (currentStep === 1) {
            if (!document.getElementById('formTitle').value.trim()) {
                showToast('Please enter a form title.', 'warning'); return;
            }
            if (!document.getElementById('evalType').value) {
                showToast('Please select an evaluation type.', 'warning'); return;
            }
        }
        if (currentStep === 2) {
            if (questions.length === 0) {
                showToast('Please add at least one question.', 'warning'); return;
            }
            renderPreview();
        }
        goToStep(currentStep + 1);
    }

    function wizardBack() { goToStep(currentStep - 1); }

    function goToStep(step) {
        document.getElementById(`step${currentStep}`).classList.add('d-none');
        document.querySelectorAll('.wizard-step').forEach((s, i) => {
            s.classList.toggle('active', i + 1 === step);
            s.classList.toggle('done', i + 1 < step);
        });
        currentStep = step;
        document.getElementById(`step${step}`).classList.remove('d-none');
        document.getElementById('btnBack').style.display = step > 1 ? '' : 'none';
        document.getElementById('btnNext').classList.toggle('d-none', step === 3);
        document.getElementById('btnSave').classList.toggle('d-none', step !== 3);
    }

    // ─── QUESTION BUILDER ────────────────────────────────────────────────────
    let qIdCounter = 1;

    function addQuestion(type) {
        document.getElementById('noQuestionsMsg')?.remove();
        const q = { id: qIdCounter++, type, text: '' };
        questions.push(q);
        renderQuestions();
    }

    function renderQuestions() {
        const list = document.getElementById('questionsList');
        if (!questions.length) {
            list.innerHTML = `<div class="text-center text-muted py-4 border rounded" id="noQuestionsMsg">
            <i class='bx bx-list-plus fs-3'></i>
            <p class="mb-0 mt-1 small">No questions yet. Add quantitative or qualitative questions above.</p>
        </div>`;
            return;
        }
        list.innerHTML = questions.map((q, idx) => `
        <div class="question-card ${q.type}" id="qcard-${q.id}">
            <div class="d-flex align-items-start gap-2">
                <div class="pt-1">
                    <span class="question-drag"><i class='bx bx-grid-vertical fs-5'></i></span>
                </div>
                <div class="flex-grow-1">
                    <div class="d-flex align-items-center gap-2 mb-2">
                        <span class="badge ${q.type === 'quantitative' ? 'bg-primary' : 'bg-secondary'} fw-normal">
                            <i class='bx ${q.type === 'quantitative' ? 'bx-bar-chart-alt-2' : 'bx-comment-detail'} me-1'></i>
                            ${q.type === 'quantitative' ? 'Quantitative' : 'Qualitative'}
                        </span>
                        <span class="text-muted small">Q${idx + 1}</span>
                    </div>
                    <input type="text" class="form-control form-control-sm"
                        placeholder="${q.type === 'quantitative' ? 'Enter rating question...' : 'Enter open-ended question...'}"
                        value="${q.text}"
                        onchange="updateQuestion(${q.id}, this.value)">
                </div>
                <button class="btn btn-sm btn-link text-danger p-1" onclick="removeQuestion(${q.id})" title="Remove">
                    <i class='bx bx-x fs-5'></i>
                </button>
            </div>
        </div>
    `).join('');
    }

    function updateQuestion(id, val) {
        const q = questions.find(q => q.id === id);
        if (q) q.text = val;
    }

    function removeQuestion(id) {
        questions = questions.filter(q => q.id !== id);
        renderQuestions();
    }

    // ─── PREVIEW ─────────────────────────────────────────────────────────────
    function renderPreview() {
        const labels = document.getElementById('ratingLabels').value.split(',');
        const min = parseInt(document.getElementById('ratingMin').value);
        const max = parseInt(document.getElementById('ratingMax').value);
        const type = document.getElementById('evalType').value;
        const typeText = type === 'peer_to_peer' ? 'Peer to Peer' : 'Program Chair → Teacher';

        const qs = questions.map((q, idx) => {
            if (q.type === 'quantitative') {
                const radios = Array.from({ length: max - min + 1 }, (_, i) => {
                    const val = min + i;
                    const lbl = labels[i] || val;
                    return `<div class="rating-btn">
                    <input type="radio" name="prev_q${q.id}" id="prev_q${q.id}_${val}" value="${val}">
                    <label for="prev_q${q.id}_${val}">${val}<small>${lbl}</small></label>
                </div>`;
                }).join('');
                return `<div class="mb-4">
                <p class="fw-semibold mb-2">${idx + 1}. ${q.text || '<em class="text-muted">Question text empty</em>'}</p>
                <div class="rating-group">${radios}</div>
            </div>`;
            } else {
                return `<div class="mb-4">
                <p class="fw-semibold mb-2">${idx + 1}. ${q.text || '<em class="text-muted">Question text empty</em>'}</p>
                <textarea class="form-control" rows="3" placeholder="Type your response here..."></textarea>
            </div>`;
            }
        }).join('<hr class="my-3">');

        document.getElementById('previewContainer').innerHTML = `
        <div class="card border-0 bg-light rounded-3 p-4">
            <div class="mb-3">
                <span class="badge bg-primary bg-opacity-10 text-primary mb-2">${typeText}</span>
                <h5 class="fw-bold">${document.getElementById('formTitle').value}</h5>
                <p class="text-muted small">${document.getElementById('formDescription').value || ''}</p>
                <div class="d-flex gap-2">
                    <span class="badge bg-white border text-secondary">${document.getElementById('schoolYear').value} · ${document.getElementById('semester').value} Sem</span>
                    <span class="badge bg-white border text-secondary">${questions.length} Questions</span>
                </div>
            </div>
            <hr>
            <div class="bg-white rounded-3 p-4 shadow-sm">
                ${qs || '<p class="text-muted text-center">No questions added.</p>'}
            </div>
        </div>`;
    }

    // ─── SAVE FORM ───────────────────────────────────────────────────────────
    function saveForm() {
        const qs = questions.map(q => ({ ...q, text: document.querySelector(`#qcard-${q.id} input`)?.value || q.text }));
        const form = {
            id: editingFormId || (evaluationForms.length + 1),
            title: document.getElementById('formTitle').value,
            type: document.getElementById('evalType').value,
            description: document.getElementById('formDescription').value,
            school_year: document.getElementById('schoolYear').value,
            semester: document.getElementById('semester').value,
            status: document.getElementById('formStatus').value,
            rating_min: parseInt(document.getElementById('ratingMin').value),
            rating_max: parseInt(document.getElementById('ratingMax').value),
            rating_labels: document.getElementById('ratingLabels').value.split(','),
            questions: qs
        };

        if (editingFormId) {
            evaluationForms = evaluationForms.map(f => f.id === editingFormId ? form : f);
            showToast('Evaluation form updated successfully!', 'success');
        } else {
            evaluationForms.push(form);
            showToast('Evaluation form created successfully!', 'success');
        }

        bootstrap.Modal.getInstance(document.getElementById('createEvaluationModal')).hide();
        resetModal();
        renderForms();
    }

    function resetModal() {
        currentStep = 1;
        questions = [];
        editingFormId = null;
        document.getElementById('formTitle').value = '';
        document.getElementById('evalType').value = '';
        document.getElementById('formDescription').value = '';
        document.getElementById('schoolYear').value = '';
        document.getElementById('semester').value = '1st';
        document.getElementById('formStatus').value = 'active';
        document.getElementById('ratingMin').value = 1;
        document.getElementById('ratingMax').value = 5;
        document.getElementById('ratingLabels').value = 'Poor,Fair,Good,Very Good,Excellent';
        goToStep(1);
        renderQuestions();
        document.getElementById('modalTitle').textContent = 'Create Evaluation Form';
    }

    document.getElementById('createEvaluationModal')?.addEventListener('hidden.bs.modal', resetModal);

    // ─── EDIT FORM ───────────────────────────────────────────────────────────
    function editForm(id) {
        const form = evaluationForms.find(f => f.id === id);
        if (!form) return;
        editingFormId = id;
        document.getElementById('modalTitle').textContent = 'Edit Evaluation Form';
        document.getElementById('formTitle').value = form.title;
        document.getElementById('evalType').value = form.type;
        document.getElementById('formDescription').value = form.description;
        document.getElementById('schoolYear').value = form.school_year;
        document.getElementById('semester').value = form.semester;
        document.getElementById('formStatus').value = form.status;
        document.getElementById('ratingMin').value = form.rating_min;
        document.getElementById('ratingMax').value = form.rating_max;
        document.getElementById('ratingLabels').value = form.rating_labels.join(',');
        questions = form.questions.map(q => ({ ...q }));
        qIdCounter = Math.max(...questions.map(q => q.id)) + 1;
        goToStep(1);
        renderQuestions();
        new bootstrap.Modal(document.getElementById('createEvaluationModal')).show();
    }

    // ─── DELETE FORM ─────────────────────────────────────────────────────────
    function deleteForm(id) {
        deletingFormId = id;
        new bootstrap.Modal(document.getElementById('deleteModal')).show();
    }
    function confirmDelete() {
        evaluationForms = evaluationForms.filter(f => f.id !== deletingFormId);
        bootstrap.Modal.getInstance(document.getElementById('deleteModal')).hide();
        renderForms();
        showToast('Evaluation form deleted.', 'danger');
    }

    // ─── VIEW / FILL FORM ────────────────────────────────────────────────────
    function viewForm(id) {
        const form = evaluationForms.find(f => f.id === id);
        if (!form) return;
        currentViewForm = form;

        document.getElementById('viewModalTitle').textContent = form.title;
        document.getElementById('viewModalSubtitle').textContent =
            `${form.type === 'peer_to_peer' ? 'Peer to Peer' : 'Program Chair → Teacher'} · ${form.school_year} · ${form.semester} Semester`;

        const qs = form.questions.map((q, idx) => {
            if (q.type === 'quantitative') {
                const radios = Array.from({ length: form.rating_max - form.rating_min + 1 }, (_, i) => {
                    const val = form.rating_min + i;
                    const lbl = form.rating_labels[i] || val;
                    return `<div class="rating-btn">
                    <input type="radio" name="q${q.id}" id="q${q.id}_${val}" value="${val}">
                    <label for="q${q.id}_${val}">${val}<small>${lbl}</small></label>
                </div>`;
                }).join('');
                return `<div class="mb-4">
                <p class="fw-semibold mb-2">${idx + 1}. ${q.text}
                    <span class="badge bg-primary bg-opacity-10 text-primary fw-normal ms-1 small">Rating</span>
                </p>
                <div class="rating-group">${radios}</div>
            </div>`;
            } else {
                return `<div class="mb-4">
                <p class="fw-semibold mb-2">${idx + 1}. ${q.text}
                    <span class="badge bg-secondary bg-opacity-10 text-secondary fw-normal ms-1 small">Open-ended</span>
                </p>
                <textarea class="form-control" name="q${q.id}" rows="3" placeholder="Type your response here..."></textarea>
            </div>`;
            }
        }).join('<hr class="my-3">');

        const canSubmit = USER_ROLE !== 'admin';
        document.getElementById('btnSubmitEval').classList.toggle('d-none', USER_ROLE === 'admin');

        document.getElementById('viewModalBody').innerHTML = `
        <div class="alert alert-light border mb-4">
            <div class="row text-center">
                <div class="col-4 border-end">
                    <p class="mb-0 small text-muted">Type</p>
                    <strong class="small">${form.type === 'peer_to_peer' ? 'Peer to Peer' : 'Chair → Teacher'}</strong>
                </div>
                <div class="col-4 border-end">
                    <p class="mb-0 small text-muted">Questions</p>
                    <strong class="small">${form.questions.length}</strong>
                </div>
                <div class="col-4">
                    <p class="mb-0 small text-muted">Rating Scale</p>
                    <strong class="small">${form.rating_min}–${form.rating_max}</strong>
                </div>
            </div>
        </div>
        ${qs}`;

        new bootstrap.Modal(document.getElementById('viewEvaluationModal')).show();
    }

    function submitEvaluation() {
        showToast('Evaluation submitted successfully!', 'success');
        bootstrap.Modal.getInstance(document.getElementById('viewEvaluationModal')).hide();
    }

    // ─── TOAST ───────────────────────────────────────────────────────────────
    function showToast(msg, type = 'success') {
        const toast = document.getElementById('toastMsg');
        const text = document.getElementById('toastText');
        toast.className = `toast align-items-center text-bg-${type} border-0`;
        text.textContent = msg;
        new bootstrap.Toast(toast, { delay: 3000 }).show();
    }

    // ─── INIT ────────────────────────────────────────────────────────────────
    window.onload = function () {
        setTimeout(() => {
            document.getElementById('loadingOverlay').style.display = 'none';
            document.getElementById('dashboardContent').style.display = 'block';
            renderForms();
        }, 400);
    };
</script>

<?php
// ============================================================
// FILE: evaluation_management.php
// ============================================================ include 'includes/footer.php'; ?>