<?php
/**
 * Filename: grade_level_management.php
 * Grade Level Management - Main UI Page
 * Complete CRUD interface for managing grade levels
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
                <h3 class="mb-1">Grade Level Management</h3>
                <p class="text-muted mb-0">Manage and organize grade levels in the system</p>
            </div>
            <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#gradeLevelModal"
                onclick="resetForm()">
                <i class="bi bi-plus-circle me-2"></i>Add Grade Level
            </button>
        </div>

        <!-- Search and Filter Section -->
        <div class="card mb-4">
            <div class="card-body">
                <div class="row g-3">
                    <div class="col-md-6">
                        <div class="input-group">
                            <span class="input-group-text"><i class="bi bi-search"></i></span>
                            <input type="text" class="form-control" id="searchInput"
                                placeholder="Search grade levels...">
                        </div>
                    </div>
                    <div class="col-md-3">
                        <select class="form-select" id="statusFilter">
                            <option value="">All Status</option>
                            <option value="1">Active</option>
                            <option value="0">Inactive</option>
                        </select>
                    </div>
                    <div class="col-md-3">
                        <select class="form-select" id="recordsPerPage">
                            <option value="10">10 per page</option>
                            <option value="25">25 per page</option>
                            <option value="50">50 per page</option>
                            <option value="100">100 per page</option>
                        </select>
                    </div>
                </div>
            </div>
        </div>

        <!-- Grade Levels Table -->
        <div class="card">
            <div class="card-body">
                <div class="table-responsive">
                    <table class="table table-hover align-middle">
                        <thead class="table-light">
                            <tr>
                                <th width="5%">#</th>
                                <th width="20%">Grade Code</th>
                                <th width="25%">Grade Name</th>
                                <th width="25%">Description</th>
                                <th width="10%">Order</th>
                                <th width="10%">Status</th>
                                <th width="15%" class="text-center">Actions</th>
                            </tr>
                        </thead>
                        <tbody id="gradeLevelsTableBody">
                            <tr>
                                <td colspan="7" class="text-center py-5">
                                    <div class="spinner-border text-primary" role="status">
                                        <span class="visually-hidden">Loading...</span>
                                    </div>
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>

                <!-- Pagination -->
                <div class="d-flex justify-content-between align-items-center mt-3">
                    <div id="paginationInfo" class="text-muted"></div>
                    <nav>
                        <ul class="pagination mb-0" id="pagination"></ul>
                    </nav>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Grade Level Modal -->
<div class="modal fade" id="gradeLevelModal" tabindex="-1" aria-labelledby="gradeLevelModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="gradeLevelModalLabel">Add Grade Level</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form id="gradeLevelForm">
                <div class="modal-body">
                    <input type="hidden" id="gradeLevelId" name="id">

                    <div class="mb-3">
                        <label for="gradeCode" class="form-label">Grade Code <span class="text-danger">*</span></label>
                        <input type="text" class="form-control" id="gradeCode" name="grade_code" required
                            placeholder="e.g., G1, G2">
                        <div class="invalid-feedback">Please provide a unique grade code.</div>
                    </div>

                    <div class="mb-3">
                        <label for="gradeName" class="form-label">Grade Name <span class="text-danger">*</span></label>
                        <input type="text" class="form-control" id="gradeName" name="grade_name" required
                            placeholder="e.g., Grade 1">
                        <div class="invalid-feedback">Please provide a grade name.</div>
                    </div>

                    <div class="mb-3">
                        <label for="description" class="form-label">Description</label>
                        <textarea class="form-control" id="description" name="description" rows="3"
                            placeholder="Optional description"></textarea>
                    </div>

                    <div class="mb-3">
                        <label for="sortOrder" class="form-label">Sort Order</label>
                        <input type="number" class="form-control" id="sortOrder" name="sort_order" value="0" min="0">
                        <small class="text-muted">Lower numbers appear first</small>
                    </div>

                    <div class="mb-3">
                        <div class="form-check form-switch">
                            <input class="form-check-input" type="checkbox" id="isActive" name="is_active" checked>
                            <label class="form-check-label" for="isActive">Active</label>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-primary" id="submitBtn">Save Grade Level</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Delete Confirmation Modal -->
<div class="modal fade" id="deleteModal" tabindex="-1" aria-labelledby="deleteModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header bg-danger text-white">
                <h5 class="modal-title" id="deleteModalLabel">Confirm Delete</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"
                    aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <p>Are you sure you want to delete this grade level?</p>
                <p class="text-muted mb-0"><strong id="deleteGradeName"></strong></p>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                <button type="button" class="btn btn-danger" id="confirmDeleteBtn">Delete</button>
            </div>
        </div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.0/dist/chart.umd.min.js"></script>
<script>
    // Global variables
    let currentPage = 1;
    let recordsPerPage = 10;
    let searchTerm = '';
    let statusFilter = '';
    let deleteId = null;

    // Initialize on page load
    window.onload = function () {
        setTimeout(() => {
            document.getElementById('loadingOverlay').style.display = 'none';
            document.getElementById('dashboardContent').style.display = 'block';
            loadGradeLevels();
        }, 600);
    };

    // Event Listeners
    document.getElementById('searchInput').addEventListener('input', function (e) {
        searchTerm = e.target.value;
        currentPage = 1;
        loadGradeLevels();
    });

    document.getElementById('statusFilter').addEventListener('change', function (e) {
        statusFilter = e.target.value;
        currentPage = 1;
        loadGradeLevels();
    });

    document.getElementById('recordsPerPage').addEventListener('change', function (e) {
        recordsPerPage = parseInt(e.target.value);
        currentPage = 1;
        loadGradeLevels();
    });

    document.getElementById('gradeLevelForm').addEventListener('submit', function (e) {
        e.preventDefault();
        saveGradeLevel();
    });

    // Load Grade Levels
    function loadGradeLevels() {
        const tbody = document.getElementById('gradeLevelsTableBody');
        tbody.innerHTML = '<tr><td colspan="7" class="text-center py-4"><div class="spinner-border text-primary" role="status"></div></td></tr>';

        let url = `api/grade_levels.php?action=read&page=${currentPage}&limit=${recordsPerPage}`;

        if (searchTerm) {
            url += `&search=${encodeURIComponent(searchTerm)}`;
        }

        fetch(url)
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    displayGradeLevels(data.data, data.pagination);
                } else {
                    tbody.innerHTML = '<tr><td colspan="7" class="text-center text-danger">Error loading data</td></tr>';
                }
            })
            .catch(error => {
                console.error('Error:', error);
                tbody.innerHTML = '<tr><td colspan="7" class="text-center text-danger">Error loading data</td></tr>';
            });
    }

    // Display Grade Levels
    function displayGradeLevels(gradeLevels, pagination) {
        const tbody = document.getElementById('gradeLevelsTableBody');

        if (gradeLevels.length === 0) {
            tbody.innerHTML = '<tr><td colspan="7" class="text-center py-4 text-muted">No grade levels found</td></tr>';
            return;
        }

        let html = '';
        const startIndex = (currentPage - 1) * recordsPerPage;

        gradeLevels.forEach((grade, index) => {
            // Apply status filter
            if (statusFilter !== '' && grade.is_active != statusFilter) {
                return;
            }

            const statusBadge = grade.is_active == 1
                ? '<span class="badge bg-success">Active</span>'
                : '<span class="badge bg-secondary">Inactive</span>';

            html += `
                <tr>
                    <td>${startIndex + index + 1}</td>
                    <td><strong>${escapeHtml(grade.grade_code)}</strong></td>
                    <td>${escapeHtml(grade.grade_name)}</td>
                    <td><small class="text-muted">${escapeHtml(grade.description || 'N/A')}</small></td>
                    <td><span class="badge bg-light text-dark">${grade.sort_order}</span></td>
                    <td>${statusBadge}</td>
                    <td class="text-center">
                        <div class="btn-group btn-group-sm" role="group">
                            <button class="btn btn-outline-primary" onclick="editGradeLevel(${grade.id})" title="Edit">
                                <i class="bi bi-pencil"></i>
                            </button>
                            <button class="btn btn-outline-${grade.is_active == 1 ? 'warning' : 'success'}" onclick="toggleStatus(${grade.id}, ${grade.is_active})" title="${grade.is_active == 1 ? 'Deactivate' : 'Activate'}">
                                <i class="bi bi-${grade.is_active == 1 ? 'toggle-on' : 'toggle-off'}"></i>
                            </button>
                            <button class="btn btn-outline-danger" onclick="confirmDelete(${grade.id}, '${escapeHtml(grade.grade_name)}')" title="Delete">
                                <i class="bi bi-trash"></i>
                            </button>
                        </div>
                    </td>
                </tr>
            `;
        });

        tbody.innerHTML = html;

        // Update pagination
        updatePagination(pagination);
    }

    // Update Pagination
    function updatePagination(pagination) {
        const paginationInfo = document.getElementById('paginationInfo');
        const paginationUl = document.getElementById('pagination');

        const start = (pagination.current_page - 1) * pagination.records_per_page + 1;
        const end = Math.min(start + pagination.records_per_page - 1, pagination.total_records);

        paginationInfo.innerHTML = `Showing ${start} to ${end} of ${pagination.total_records} entries`;

        let html = '';

        // Previous button
        html += `
            <li class="page-item ${pagination.current_page === 1 ? 'disabled' : ''}">
                <a class="page-link" href="#" onclick="changePage(${pagination.current_page - 1}); return false;">Previous</a>
            </li>
        `;

        // Page numbers
        for (let i = 1; i <= pagination.total_pages; i++) {
            if (i === 1 || i === pagination.total_pages || (i >= pagination.current_page - 2 && i <= pagination.current_page + 2)) {
                html += `
                    <li class="page-item ${i === pagination.current_page ? 'active' : ''}">
                        <a class="page-link" href="#" onclick="changePage(${i}); return false;">${i}</a>
                    </li>
                `;
            } else if (i === pagination.current_page - 3 || i === pagination.current_page + 3) {
                html += '<li class="page-item disabled"><a class="page-link" href="#">...</a></li>';
            }
        }

        // Next button
        html += `
            <li class="page-item ${pagination.current_page === pagination.total_pages ? 'disabled' : ''}">
                <a class="page-link" href="#" onclick="changePage(${pagination.current_page + 1}); return false;">Next</a>
            </li>
        `;

        paginationUl.innerHTML = html;
    }

    // Change Page
    function changePage(page) {
        currentPage = page;
        loadGradeLevels();
    }

    // Reset Form
    function resetForm() {
        document.getElementById('gradeLevelForm').reset();
        document.getElementById('gradeLevelId').value = '';
        document.getElementById('gradeLevelModalLabel').textContent = 'Add Grade Level';
        document.getElementById('submitBtn').textContent = 'Save Grade Level';
        document.getElementById('isActive').checked = true;
        document.getElementById('gradeLevelForm').classList.remove('was-validated');
    }

    // Edit Grade Level
    function editGradeLevel(id) {
        fetch(`api/grade_levels.php?action=read_one&id=${id}`)
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    const grade = data.data;
                    document.getElementById('gradeLevelId').value = grade.id;
                    document.getElementById('gradeCode').value = grade.grade_code;
                    document.getElementById('gradeName').value = grade.grade_name;
                    document.getElementById('description').value = grade.description;
                    document.getElementById('sortOrder').value = grade.sort_order;
                    document.getElementById('isActive').checked = grade.is_active == 1;

                    document.getElementById('gradeLevelModalLabel').textContent = 'Edit Grade Level';
                    document.getElementById('submitBtn').textContent = 'Update Grade Level';

                    const modal = new bootstrap.Modal(document.getElementById('gradeLevelModal'));
                    modal.show();
                } else {
                    showToast('Error', 'Failed to load grade level data', 'error');
                }
            })
            .catch(error => {
                console.error('Error:', error);
                showToast('Error', 'Failed to load grade level data', 'error');
            });
    }

    // Save Grade Level
    function saveGradeLevel() {
        const form = document.getElementById('gradeLevelForm');

        if (!form.checkValidity()) {
            form.classList.add('was-validated');
            return;
        }

        const submitBtn = document.getElementById('submitBtn');
        submitBtn.disabled = true;
        submitBtn.innerHTML = '<span class="spinner-border spinner-border-sm me-2"></span>Saving...';

        const id = document.getElementById('gradeLevelId').value;
        const action = id ? 'update' : 'create';

        const data = {
            id: id || undefined,
            grade_code: document.getElementById('gradeCode').value,
            grade_name: document.getElementById('gradeName').value,
            description: document.getElementById('description').value,
            sort_order: parseInt(document.getElementById('sortOrder').value),
            is_active: document.getElementById('isActive').checked ? 1 : 0
        };

        fetch(`api/grade_levels.php?action=${action}`, {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
            },
            body: JSON.stringify(data)
        })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    showToast('Success', data.message, 'success');
                    bootstrap.Modal.getInstance(document.getElementById('gradeLevelModal')).hide();
                    loadGradeLevels();
                    resetForm();
                } else {
                    showToast('Error', data.message, 'error');
                }
            })
            .catch(error => {
                console.error('Error:', error);
                showToast('Error', 'An error occurred while saving', 'error');
            })
            .finally(() => {
                submitBtn.disabled = false;
                submitBtn.textContent = id ? 'Update Grade Level' : 'Save Grade Level';
            });
    }

    // Toggle Status
    function toggleStatus(id, currentStatus) {
        fetch(`api/grade_levels.php?action=toggle_active`, {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
            },
            body: JSON.stringify({ id: id })
        })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    showToast('Success', data.message, 'success');
                    loadGradeLevels();
                } else {
                    showToast('Error', data.message, 'error');
                }
            })
            .catch(error => {
                console.error('Error:', error);
                showToast('Error', 'Failed to update status', 'error');
            });
    }

    // Confirm Delete
    function confirmDelete(id, name) {
        deleteId = id;
        document.getElementById('deleteGradeName').textContent = name;
        const modal = new bootstrap.Modal(document.getElementById('deleteModal'));
        modal.show();
    }

    // Delete Grade Level
    document.getElementById('confirmDeleteBtn').addEventListener('click', function () {
        if (!deleteId) return;

        this.disabled = true;
        this.innerHTML = '<span class="spinner-border spinner-border-sm me-2"></span>Deleting...';

        fetch(`api/grade_levels.php?action=delete`, {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
            },
            body: JSON.stringify({ id: deleteId })
        })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    showToast('Success', data.message, 'success');
                    bootstrap.Modal.getInstance(document.getElementById('deleteModal')).hide();
                    loadGradeLevels();
                } else {
                    showToast('Error', data.message, 'error');
                }
            })
            .catch(error => {
                console.error('Error:', error);
                showToast('Error', 'Failed to delete grade level', 'error');
            })
            .finally(() => {
                this.disabled = false;
                this.textContent = 'Delete';
                deleteId = null;
            });
    });

    // Show Toast Notification
    function showToast(title, message, type = 'info') {
        const toastColors = {
            success: '#28a745',
            error: '#dc3545',
            warning: '#ffc107',
            info: '#17a2b8'
        };

        const toast = document.createElement('div');
        toast.className = 'position-fixed top-0 end-0 p-3';
        toast.style.zIndex = '9999';
        toast.innerHTML = `
            <div class="toast show" role="alert">
                <div class="toast-header" style="background-color: ${toastColors[type]}; color: white;">
                    <strong class="me-auto">${title}</strong>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="toast"></button>
                </div>
                <div class="toast-body">
                    ${message}
                </div>
            </div>
        `;

        document.body.appendChild(toast);

        setTimeout(() => {
            toast.remove();
        }, 3000);
    }

    // Escape HTML
    function escapeHtml(text) {
        if (!text) return '';
        const div = document.createElement('div');
        div.textContent = text;
        return div.innerHTML;
    }
</script>

<script src="assets/js/dashboard.js"></script>
<style>
    .avatar-sm {
        width: 40px;
        height: 40px;
        font-size: 14px;
    }

    .btn-group-sm .btn {
        padding: 0.25rem 0.5rem;
        font-size: 0.875rem;
    }

    .table th {
        font-weight: 600;
        text-transform: uppercase;
        font-size: 0.85rem;
        letter-spacing: 0.5px;
    }

    .badge {
        padding: 0.35em 0.65em;
    }

    .was-validated .form-control:invalid {
        border-color: #dc3545;
    }

    .was-validated .form-control:valid {
        border-color: #28a745;
    }
</style>

<?php include 'includes/footer.php'; ?>