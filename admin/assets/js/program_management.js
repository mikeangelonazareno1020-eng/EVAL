/**
 * Filename: assets/js/program_management.js
 * Program Management JavaScript
 * Handles all client-side operations for program management
 */

// Global variables
let currentPage = 1;
let recordsPerPage = 10;
let searchTerm = '';
let programTypeFilter = '';
let departmentFilter = '';
let statusFilter = '';
let deleteId = null;
let gradeLevels = [];
let departments = [];
let programTypes = [];

// Initialize on page load
window.onload = function () {
    setTimeout(() => {
        document.getElementById('loadingOverlay').style.display = 'none';
        document.getElementById('dashboardContent').style.display = 'block';
        loadGradeLevels();
        loadDepartments();
        loadProgramTypes();
        loadPrograms();
    }, 600);
};

// Event Listeners
document.getElementById('searchInput').addEventListener('input', function(e) {
    searchTerm = e.target.value;
    currentPage = 1;
    loadPrograms();
});

document.getElementById('programTypeFilter').addEventListener('change', function(e) {
    programTypeFilter = e.target.value;
    currentPage = 1;
    loadPrograms();
});

document.getElementById('departmentFilter').addEventListener('change', function(e) {
    departmentFilter = e.target.value;
    currentPage = 1;
    loadPrograms();
});

document.getElementById('statusFilter').addEventListener('change', function(e) {
    statusFilter = e.target.value;
    currentPage = 1;
    loadPrograms();
});

document.getElementById('recordsPerPage').addEventListener('change', function(e) {
    recordsPerPage = parseInt(e.target.value);
    currentPage = 1;
    loadPrograms();
});

document.getElementById('programForm').addEventListener('submit', function(e) {
    e.preventDefault();
    saveProgram();
});

// Load Grade Levels
function loadGradeLevels() {
    fetch('api/grade_levels.php?action=read&active_only=true')
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                gradeLevels = data.data;
                populateGradeLevelSelect();
            }
        })
        .catch(error => console.error('Error loading grade levels:', error));
}

// Populate Grade Level Select
function populateGradeLevelSelect() {
    const gradeSelect = document.getElementById('gradeLevel');
    gradeSelect.innerHTML = '<option value="">Select Grade Level</option>';
    
    gradeLevels.forEach(grade => {
        gradeSelect.innerHTML += `<option value="${grade.id}">${grade.grade_name}</option>`;
    });
}

// Load Departments
function loadDepartments() {
    fetch('api/programs.php?action=get_departments')
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

// Load Program Types
function loadProgramTypes() {
    fetch('api/programs.php?action=get_program_types')
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                programTypes = data.data;
                populateProgramTypeSelects();
            }
        })
        .catch(error => console.error('Error loading program types:', error));
}

// Populate Program Type Selects
function populateProgramTypeSelects() {
    const typeSelect = document.getElementById('programType');
    const typeFilter = document.getElementById('programTypeFilter');
    
    typeSelect.innerHTML = '';
    typeFilter.innerHTML = '<option value="">All Types</option>';
    
    programTypes.forEach(type => {
        typeSelect.innerHTML += `<option value="${type}">${type}</option>`;
        typeFilter.innerHTML += `<option value="${type}">${type}</option>`;
    });
}

// Load Programs
function loadPrograms() {
    const tbody = document.getElementById('programsTableBody');
    tbody.innerHTML = '<tr><td colspan="10" class="text-center py-4"><div class="spinner-border text-primary" role="status"></div></td></tr>';

    let url = `api/programs.php?action=read&page=${currentPage}&limit=${recordsPerPage}`;
    
    if (searchTerm) url += `&search=${encodeURIComponent(searchTerm)}`;
    if (programTypeFilter) url += `&program_type=${encodeURIComponent(programTypeFilter)}`;
    if (departmentFilter) url += `&department=${encodeURIComponent(departmentFilter)}`;

    fetch(url)
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                displayPrograms(data.data, data.pagination);
            } else {
                tbody.innerHTML = '<tr><td colspan="10" class="text-center text-danger">Error loading data</td></tr>';
            }
        })
        .catch(error => {
            console.error('Error:', error);
            tbody.innerHTML = '<tr><td colspan="10" class="text-center text-danger">Error loading data</td></tr>';
        });
}

// Display Programs
function displayPrograms(programs, pagination) {
    const tbody = document.getElementById('programsTableBody');
    
    if (programs.length === 0) {
        tbody.innerHTML = '<tr><td colspan="10" class="text-center py-4 text-muted">No programs found</td></tr>';
        return;
    }

    let html = '';
    const startIndex = (currentPage - 1) * recordsPerPage;
    
    programs.forEach((program, index) => {
        if (statusFilter !== '' && program.is_active != statusFilter) return;

        const statusBadge = program.is_active == 1 
            ? '<span class="badge bg-success">Active</span>' 
            : '<span class="badge bg-secondary">Inactive</span>';
        
        const typeBadge = getTypeBadge(program.program_type);
        
        html += `
            <tr>
                <td>${startIndex + index + 1}</td>
                <td><strong class="text-primary">${escapeHtml(program.program_code)}</strong></td>
                <td>
                    <div>${escapeHtml(program.program_name)}</div>
                    <small class="text-muted">${escapeHtml(program.description ? program.description.substring(0, 40) + '...' : '')}</small>
                </td>
                <td>${typeBadge}</td>
                <td><span class="badge bg-info text-dark">${escapeHtml(program.department || 'N/A')}</span></td>
                <td><span class="badge bg-light text-dark">${program.duration_years} yrs</span></td>
                <td><span class="badge bg-light text-dark">${program.total_credits}</span></td>
                <td><span class="badge bg-primary">${program.course_count || 0}</span></td>
                <td>${statusBadge}</td>
                <td class="text-center">
                    <div class="btn-group btn-group-sm" role="group">
                        <button class="btn btn-outline-info" onclick="viewProgram(${program.id})" title="View">
                            <i class="bi bi-eye"></i>
                        </button>
                        <button class="btn btn-outline-primary" onclick="editProgram(${program.id})" title="Edit">
                            <i class="bi bi-pencil"></i>
                        </button>
                        <button class="btn btn-outline-${program.is_active == 1 ? 'warning' : 'success'}" onclick="toggleStatus(${program.id})" title="${program.is_active == 1 ? 'Deactivate' : 'Activate'}">
                            <i class="bi bi-${program.is_active == 1 ? 'toggle-on' : 'toggle-off'}"></i>
                        </button>
                        <button class="btn btn-outline-danger" onclick="confirmDelete(${program.id}, '${escapeHtml(program.program_name)}')" title="Delete">
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

// Get Type Badge
function getTypeBadge(type) {
    const badges = {
        'Diploma': 'bg-primary',
        'Certificate': 'bg-success',
        'Degree': 'bg-danger',
        'Masters': 'bg-warning text-dark',
        'Doctorate': 'bg-dark',
        'Vocational': 'bg-info text-dark',
        'Short Course': 'bg-secondary',
        'Other': 'bg-light text-dark'
    };
    
    const badgeClass = badges[type] || 'bg-light text-dark';
    return `<span class="badge ${badgeClass}">${type}</span>`;
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
    loadPrograms();
}

// Reset Form
function resetForm() {
    document.getElementById('programForm').reset();
    document.getElementById('programId').value = '';
    document.getElementById('programModalLabel').textContent = 'Add Program';
    document.getElementById('submitBtn').textContent = 'Save Program';
    document.getElementById('isActive').checked = true;
    document.getElementById('programForm').classList.remove('was-validated');
}

// View Program
function viewProgram(id) {
    fetch(`api/programs.php?action=read_one&id=${id}`)
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                const program = data.data;
                displayProgramDetails(program);
            } else {
                showToast('Error', 'Failed to load program data', 'error');
            }
        })
        .catch(error => {
            console.error('Error:', error);
            showToast('Error', 'Failed to load program data', 'error');
        });
}

// Display Program Details
function displayProgramDetails(program) {
    const detailsBody = document.getElementById('programDetailsBody');
    
    detailsBody.innerHTML = `
        <div class="row">
            <div class="col-md-6 mb-3">
                <strong>Program Code:</strong> ${escapeHtml(program.program_code)}
            </div>
            <div class="col-md-6 mb-3">
                <strong>Program Type:</strong> ${getTypeBadge(program.program_type)}
            </div>
            <div class="col-md-12 mb-3">
                <strong>Program Name:</strong> ${escapeHtml(program.program_name)}
            </div>
            <div class="col-md-12 mb-3">
                <strong>Description:</strong><br>${escapeHtml(program.description || 'N/A')}
            </div>
            <div class="col-md-6 mb-3">
                <strong>Department:</strong> ${escapeHtml(program.department || 'N/A')}
            </div>
            <div class="col-md-6 mb-3">
                <strong>Duration:</strong> ${program.duration_years} years
            </div>
            <div class="col-md-6 mb-3">
                <strong>Total Credits:</strong> ${program.total_credits}
            </div>
            <div class="col-md-6 mb-3">
                <strong>Max Students:</strong> ${program.max_students || 'Unlimited'}
            </div>
            <div class="col-md-6 mb-3">
                <strong>Tuition Fee:</strong> $${parseFloat(program.tuition_fee).toFixed(2)}
            </div>
            <div class="col-md-6 mb-3">
                <strong>Status:</strong> ${program.is_active == 1 ? '<span class="badge bg-success">Active</span>' : '<span class="badge bg-secondary">Inactive</span>'}
            </div>
        </div>
    `;
    
    const modal = new bootstrap.Modal(document.getElementById('viewProgramModal'));
    modal.show();
}

// Edit Program
function editProgram(id) {
    fetch(`api/programs.php?action=read_one&id=${id}`)
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                const program = data.data;
                document.getElementById('programId').value = program.id;
                document.getElementById('programCode').value = program.program_code;
                document.getElementById('programName').value = program.program_name;
                document.getElementById('description').value = program.description;
                document.getElementById('programType').value = program.program_type;
                document.getElementById('department').value = program.department;
                document.getElementById('gradeLevel').value = program.grade_level_id || '';
                document.getElementById('durationYears').value = program.duration_years;
                document.getElementById('totalCredits').value = program.total_credits;
                document.getElementById('maxStudents').value = program.max_students;
                document.getElementById('tuitionFee').value = program.tuition_fee;
                document.getElementById('sortOrder').value = program.sort_order;
                document.getElementById('startDate').value = program.start_date;
                document.getElementById('endDate').value = program.end_date;
                document.getElementById('isActive').checked = program.is_active == 1;
                
                document.getElementById('programModalLabel').textContent = 'Edit Program';
                document.getElementById('submitBtn').textContent = 'Update Program';
                
                const modal = new bootstrap.Modal(document.getElementById('programModal'));
                modal.show();
            } else {
                showToast('Error', 'Failed to load program data', 'error');
            }
        })
        .catch(error => {
            console.error('Error:', error);
            showToast('Error', 'Failed to load program data', 'error');
        });
}

// Save Program
function saveProgram() {
    const form = document.getElementById('programForm');
    
    if (!form.checkValidity()) {
        form.classList.add('was-validated');
        return;
    }

    const submitBtn = document.getElementById('submitBtn');
    submitBtn.disabled = true;
    submitBtn.innerHTML = '<span class="spinner-border spinner-border-sm me-2"></span>Saving...';

    const id = document.getElementById('programId').value;
    const action = id ? 'update' : 'create';
    
    const data = {
        id: id || undefined,
        program_code: document.getElementById('programCode').value,
        program_name: document.getElementById('programName').value,
        description: document.getElementById('description').value,
        program_type: document.getElementById('programType').value,
        department: document.getElementById('department').value,
        grade_level_id: document.getElementById('gradeLevel').value || null,
        duration_years: parseFloat(document.getElementById('durationYears').value),
        total_credits: parseInt(document.getElementById('totalCredits').value),
        max_students: parseInt(document.getElementById('maxStudents').value),
        tuition_fee: parseFloat(document.getElementById('tuitionFee').value),
        sort_order: parseInt(document.getElementById('sortOrder').value),
        start_date: document.getElementById('startDate').value || null,
        end_date: document.getElementById('endDate').value || null,
        is_active: document.getElementById('isActive').checked ? 1 : 0
    };

    fetch(`api/programs.php?action=${action}`, {
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
            bootstrap.Modal.getInstance(document.getElementById('programModal')).hide();
            loadPrograms();
            loadDepartments();
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
        submitBtn.textContent = id ? 'Update Program' : 'Save Program';
    });
}

// Toggle Status
function toggleStatus(id) {
    fetch(`api/programs.php?action=toggle_active`, {
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
            loadPrograms();
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
    document.getElementById('deleteProgramName').textContent = name;
    const modal = new bootstrap.Modal(document.getElementById('deleteModal'));
    modal.show();
}

// Delete Program
document.getElementById('confirmDeleteBtn').addEventListener('click', function() {
    if (!deleteId) return;

    this.disabled = true;
    this.innerHTML = '<span class="spinner-border spinner-border-sm me-2"></span>Deleting...';

    fetch(`api/programs.php?action=delete`, {
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
            loadPrograms();
        } else {
            showToast('Error', data.message, 'error');
        }
    })
    .catch(error => {
        console.error('Error:', error);
        showToast('Error', 'Failed to delete program', 'error');
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