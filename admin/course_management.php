<?php
/**
 * Filename: course_management.php
 * Course Management - Main UI Page
 * Complete CRUD interface for managing courses
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
                <h3 class="mb-1">Course Management</h3>
                <p class="text-muted mb-0">Manage and organize courses in the system</p>
            </div>
            <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#courseModal" onclick="resetForm()">
                <i class="bi bi-plus-circle me-2"></i>Add Course
            </button>
        </div>

        <!-- Search and Filter Section -->
        <div class="card mb-4">
            <div class="card-body">
                <div class="row g-3">
                    <div class="col-md-4">
                        <div class="input-group">
                            <span class="input-group-text"><i class="bi bi-search"></i></span>
                            <input type="text" class="form-control" id="searchInput" placeholder="Search courses...">
                        </div>
                    </div>
                    <div class="col-md-2">
                        <select class="form-select" id="gradeLevelFilter">
                            <option value="">All Grade Levels</option>
                        </select>
                    </div>
                    <div class="col-md-2">
                        <select class="form-select" id="departmentFilter">
                            <option value="">All Departments</option>
                        </select>
                    </div>
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
                            <option value="100">100 per page</option>
                        </select>
                    </div>
                </div>
            </div>
        </div>

        <!-- Courses Table -->
        <div class="card">
            <div class="card-body">
                <div class="table-responsive">
                    <table class="table table-hover align-middle">
                        <thead class="table-light">
                            <tr>
                                <th width="4%">#</th>
                                <th width="10%">Code</th>
                                <th width="20%">Course Name</th>
                                <th width="15%">Department</th>
                                <th width="10%">Grade Level</th>
                                <th width="8%">Credits</th>
                                <th width="8%">Hours/Week</th>
                                <th width="8%">Status</th>
                                <th width="12%" class="text-center">Actions</th>
                            </tr>
                        </thead>
                        <tbody id="coursesTableBody">
                            <tr>
                                <td colspan="9" class="text-center py-5">
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

<!-- Course Modal -->
<div class="modal fade" id="courseModal" tabindex="-1" aria-labelledby="courseModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="courseModalLabel">Add Course</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form id="courseForm">
                <div class="modal-body">
                    <input type="hidden" id="courseId" name="id">

                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label for="courseCode" class="form-label">Course Code <span
                                    class="text-danger">*</span></label>
                            <input type="text" class="form-control" id="courseCode" name="course_code" required
                                placeholder="e.g., MATH101">
                            <div class="invalid-feedback">Please provide a unique course code.</div>
                        </div>

                        <div class="col-md-6 mb-3">
                            <label for="courseName" class="form-label">Course Name <span
                                    class="text-danger">*</span></label>
                            <input type="text" class="form-control" id="courseName" name="course_name" required
                                placeholder="e.g., Basic Mathematics">
                            <div class="invalid-feedback">Please provide a course name.</div>
                        </div>
                    </div>

                    <div class="mb-3">
                        <label for="description" class="form-label">Description</label>
                        <textarea class="form-control" id="description" name="description" rows="3"
                            placeholder="Course description"></textarea>
                    </div>

                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label for="department" class="form-label">Department</label>
                            <input type="text" class="form-control" id="department" name="department"
                                list="departmentList" placeholder="e.g., Mathematics">
                            <datalist id="departmentList"></datalist>
                        </div>

                        <div class="col-md-6 mb-3">
                            <label for="gradeLevel" class="form-label">Grade Level</label>
                            <select class="form-select" id="gradeLevel" name="grade_level_id">
                                <option value="">Select Grade Level</option>
                            </select>
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-md-4 mb-3">
                            <label for="credits" class="form-label">Credits</label>
                            <input type="number" class="form-control" id="credits" name="credits" value="3" min="0"
                                max="10">
                        </div>

                        <div class="col-md-4 mb-3">
                            <label for="hoursPerWeek" class="form-label">Hours/Week</label>
                            <input type="number" class="form-control" id="hoursPerWeek" name="hours_per_week" value="0"
                                min="0" max="40">
                        </div>

                        <div class="col-md-4 mb-3">
                            <label for="sortOrder" class="form-label">Sort Order</label>
                            <input type="number" class="form-control" id="sortOrder" name="sort_order" value="0"
                                min="0">
                        </div>
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
                    <button type="submit" class="btn btn-primary" id="submitBtn">Save Course</button>
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
                <p>Are you sure you want to delete this course?</p>
                <p class="text-muted mb-0"><strong id="deleteCourseName"></strong></p>
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
    let gradeLevelFilter = '';
    let departmentFilter = '';
    let statusFilter = '';
    let deleteId = null;
    let gradeLevels = [];
    let departments = [];

    // Initialize on page load
    window.onload = function () {
        setTimeout(() => {
            document.getElementById('loadingOverlay').style.display = 'none';
            document.getElementById('dashboardContent').style.display = 'block';
            loadGradeLevels();
            loadDepartments();
            loadCourses();
        }, 600);
    };

    // Event Listeners
    document.getElementById('searchInput').addEventListener('input', function (e) {
        searchTerm = e.target.value;
        currentPage = 1;
        loadCourses();
    });

    document.getElementById('gradeLevelFilter').addEventListener('change', function (e) {
        gradeLevelFilter = e.target.value;
        currentPage = 1;
        loadCourses();
    });

    document.getElementById('departmentFilter').addEventListener('change', function (e) {
        departmentFilter = e.target.value;
        currentPage = 1;
        loadCourses();
    });

    document.getElementById('statusFilter').addEventListener('change', function (e) {
        statusFilter = e.target.value;
        currentPage = 1;
        loadCourses();
    });

    document.getElementById('recordsPerPage').addEventListener('change', function (e) {
        recordsPerPage = parseInt(e.target.value);
        currentPage = 1;
        loadCourses();
    });

    document.getElementById('courseForm').addEventListener('submit', function (e) {
        e.preventDefault();
        saveCourse();
    });

    // Load Grade Levels
    function loadGradeLevels() {
        fetch('api/grade_levels.php?action=read&active_only=true')
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    gradeLevels = data.data;
                    populateGradeLevelSelects();
                }
            })
            .catch(error => console.error('Error loading grade levels:', error));
    }

    // Populate Grade Level Selects
    function populateGradeLevelSelects() {
        const gradeSelect = document.getElementById('gradeLevel');
        const gradeFilter = document.getElementById('gradeLevelFilter');

        gradeSelect.innerHTML = '<option value="">Select Grade Level</option>';
        gradeFilter.innerHTML = '<option value="">All Grade Levels</option>';

        gradeLevels.forEach(grade => {
            gradeSelect.innerHTML += `<option value="${grade.id}">${grade.grade_name}</option>`;
            gradeFilter.innerHTML += `<option value="${grade.id}">${grade.grade_name}</option>`;
        });
    }

    // Load Departments
    function loadDepartments() {
        fetch('api/courses.php?action=get_departments')
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    departments = data.data;
                    populateDepartmentSelects();
                }
            })
            .catch(error => console.error('Error loading departments:', error));
    }

    // Populate Department Selects
    function populateDepartmentSelects() {
        const deptList = document.getElementById('departmentList');
        const deptFilter = document.getElementById('departmentFilter');

        deptList.innerHTML = '';
        deptFilter.innerHTML = '<option value="">All Departments</option>';

        departments.forEach(dept => {
            deptList.innerHTML += `<option value="${dept}">`;
            deptFilter.innerHTML += `<option value="${dept}">${dept}</option>`;
        });
    }

    // Load Courses
    function loadCourses() {
        const tbody = document.getElementById('coursesTableBody');
        tbody.innerHTML = '<tr><td colspan="9" class="text-center py-4"><div class="spinner-border text-primary" role="status"></div></td></tr>';

        let url = `api/courses.php?action=read&page=${currentPage}&limit=${recordsPerPage}`;

        if (searchTerm) {
            url += `&search=${encodeURIComponent(searchTerm)}`;
        }
        if (gradeLevelFilter) {
            url += `&grade_level=${gradeLevelFilter}`;
        }
        if (departmentFilter) {
            url += `&department=${encodeURIComponent(departmentFilter)}`;
        }

        fetch(url)
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    displayCourses(data.data, data.pagination);
                } else {
                    tbody.innerHTML = '<tr><td colspan="9" class="text-center text-danger">Error loading data</td></tr>';
                }
            })
            .catch(error => {
                console.error('Error:', error);
                tbody.innerHTML = '<tr><td colspan="9" class="text-center text-danger">Error loading data</td></tr>';
            });
    }

    // Display Courses
    function displayCourses(courses, pagination) {
        const tbody = document.getElementById('coursesTableBody');

        if (courses.length === 0) {
            tbody.innerHTML = '<tr><td colspan="9" class="text-center py-4 text-muted">No courses found</td></tr>';
            return;
        }

        let html = '';
        const startIndex = (currentPage - 1) * recordsPerPage;

        courses.forEach((course, index) => {
            // Apply status filter
            if (statusFilter !== '' && course.is_active != statusFilter) {
                return;
            }

            const statusBadge = course.is_active == 1
                ? '<span class="badge bg-success">Active</span>'
                : '<span class="badge bg-secondary">Inactive</span>';

            const gradeName = course.grade_name || '<span class="text-muted">N/A</span>';

            html += `
                <tr>
                    <td>${startIndex + index + 1}</td>
                    <td><strong class="text-primary">${escapeHtml(course.course_code)}</strong></td>
                    <td>
                        <div>${escapeHtml(course.course_name)}</div>
                        <small class="text-muted">${escapeHtml(course.description ? course.description.substring(0, 50) + '...' : '')}</small>
                    </td>
                    <td><span class="badge bg-info text-dark">${escapeHtml(course.department || 'N/A')}</span></td>
                    <td>${gradeName}</td>
                    <td><span class="badge bg-light text-dark">${course.credits}</span></td>
                    <td><span class="badge bg-light text-dark">${course.hours_per_week}</span></td>
                    <td>${statusBadge}</td>
                    <td class="text-center">
                        <div class="btn-group btn-group-sm" role="group">
                            <button class="btn btn-outline-primary" onclick="editCourse(${course.id})" title="Edit">
                                <i class="bi bi-pencil"></i>
                            </button>
                            <button class="btn btn-outline-${course.is_active == 1 ? 'warning' : 'success'}" onclick="toggleStatus(${course.id}, ${course.is_active})" title="${course.is_active == 1 ? 'Deactivate' : 'Activate'}">
                                <i class="bi bi-${course.is_active == 1 ? 'toggle-on' : 'toggle-off'}"></i>
                            </button>
                            <button class="btn btn-outline-danger" onclick="confirmDelete(${course.id}, '${escapeHtml(course.course_name)}')" title="Delete">
                                <i class="bi bi-trash"></i>
                            </button>
                        </div>
                    </td>
                </tr>
            `;
        });

        tbody.innerHTML = html;
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

        html += `
            <li class="page-item ${pagination.current_page === 1 ? 'disabled' : ''}">
                <a class="page-link" href="#" onclick="changePage(${pagination.current_page - 1}); return false;">Previous</a>
            </li>
        `;

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
        loadCourses();
    }

    // Reset Form
    function resetForm() {
        document.getElementById('courseForm').reset();
        document.getElementById('courseId').value = '';
        document.getElementById('courseModalLabel').textContent = 'Add Course';
        document.getElementById('submitBtn').textContent = 'Save Course';
        document.getElementById('isActive').checked = true;
        document.getElementById('courseForm').classList.remove('was-validated');
    }

    // Edit Course
    function editCourse(id) {
        fetch(`api/courses.php?action=read_one&id=${id}`)
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    const course = data.data;
                    document.getElementById('courseId').value = course.id;
                    document.getElementById('courseCode').value = course.course_code;
                    document.getElementById('courseName').value = course.course_name;
                    document.getElementById('description').value = course.description;
                    document.getElementById('department').value = course.department;
                    document.getElementById('gradeLevel').value = course.grade_level_id || '';
                    document.getElementById('credits').value = course.credits;
                    document.getElementById('hoursPerWeek').value = course.hours_per_week;
                    document.getElementById('sortOrder').value = course.sort_order;
                    document.getElementById('isActive').checked = course.is_active == 1;

                    document.getElementById('courseModalLabel').textContent = 'Edit Course';
                    document.getElementById('submitBtn').textContent = 'Update Course';

                    const modal = new bootstrap.Modal(document.getElementById('courseModal'));
                    modal.show();
                } else {
                    showToast('Error', 'Failed to load course data', 'error');
                }
            })
            .catch(error => {
                console.error('Error:', error);
                showToast('Error', 'Failed to load course data', 'error');
            });
    }

    // Save Course
    function saveCourse() {
        const form = document.getElementById('courseForm');

        if (!form.checkValidity()) {
            form.classList.add('was-validated');
            return;
        }

        const submitBtn = document.getElementById('submitBtn');
        submitBtn.disabled = true;
        submitBtn.innerHTML = '<span class="spinner-border spinner-border-sm me-2"></span>Saving...';

        const id = document.getElementById('courseId').value;
        const action = id ? 'update' : 'create';

        const data = {
            id: id || undefined,
            course_code: document.getElementById('courseCode').value,
            course_name: document.getElementById('courseName').value,
            description: document.getElementById('description').value,
            department: document.getElementById('department').value,
            grade_level_id: document.getElementById('gradeLevel').value || null,
            credits: parseInt(document.getElementById('credits').value),
            hours_per_week: parseInt(document.getElementById('hoursPerWeek').value),
            sort_order: parseInt(document.getElementById('sortOrder').value),
            is_active: document.getElementById('isActive').checked ? 1 : 0
        };

        fetch(`api/courses.php?action=${action}`, {
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
                    bootstrap.Modal.getInstance(document.getElementById('courseModal')).hide();
                    loadCourses();
                    loadDepartments(); // Reload departments in case a new one was added
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
                submitBtn.textContent = id ? 'Update Course' : 'Save Course';
            });
    }

    // Toggle Status
    function toggleStatus(id, currentStatus) {
        fetch(`api/courses.php?action=toggle_active`, {
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
                    loadCourses();
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
        document.getElementById('deleteCourseName').textContent = name;
        const modal = new bootstrap.Modal(document.getElementById('deleteModal'));
        modal.show();
    }

    // Delete Course
    document.getElementById('confirmDeleteBtn').addEventListener('click', function () {
        if (!deleteId) return;

        this.disabled = true;
        this.innerHTML = '<span class="spinner-border spinner-border-sm me-2"></span>Deleting...';

        fetch(`api/courses.php?action=delete`, {
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
                    loadCourses();
                } else {
                    showToast('Error', data.message, 'error');
                }
            })
            .catch(error => {
                console.error('Error:', error);
                showToast('Error', 'Failed to delete course', 'error');
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