<?php
/**
 * Filename: program_management.php
 * Program Management - Main UI Page
 * Complete CRUD interface for managing programs
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
                <h3 class="mb-1">Program Management</h3>
                <p class="text-muted mb-0">Manage academic programs and their courses</p>
            </div>
            <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#programModal" onclick="resetForm()">
                <i class="bi bi-plus-circle me-2"></i>Add Program
            </button>
        </div>

        <!-- Search and Filter Section -->
        <div class="card mb-4">
            <div class="card-body">
                <div class="row g-3">
                    <div class="col-md-4">
                        <div class="input-group">
                            <span class="input-group-text"><i class="bi bi-search"></i></span>
                            <input type="text" class="form-control" id="searchInput" placeholder="Search programs...">
                        </div>
                    </div>
                    <div class="col-md-2">
                        <select class="form-select" id="programTypeFilter">
                            <option value="">All Types</option>
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

        <!-- Programs Table -->
        <div class="card">
            <div class="card-body">
                <div class="table-responsive">
                    <table class="table table-hover align-middle">
                        <thead class="table-light">
                            <tr>
                                <th width="4%">#</th>
                                <th width="10%">Code</th>
                                <th width="18%">Program Name</th>
                                <th width="10%">Type</th>
                                <th width="12%">Department</th>
                                <th width="8%">Duration</th>
                                <th width="8%">Credits</th>
                                <th width="8%">Courses</th>
                                <th width="8%">Status</th>
                                <th width="14%" class="text-center">Actions</th>
                            </tr>
                        </thead>
                        <tbody id="programsTableBody">
                            <tr>
                                <td colspan="10" class="text-center py-5">
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

<!-- Program Modal -->
<div class="modal fade" id="programModal" tabindex="-1" aria-labelledby="programModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-xl">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="programModalLabel">Add Program</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form id="programForm">
                <div class="modal-body">
                    <input type="hidden" id="programId" name="id">

                    <div class="row">
                        <div class="col-md-4 mb-3">
                            <label for="programCode" class="form-label">Program Code <span
                                    class="text-danger">*</span></label>
                            <input type="text" class="form-control" id="programCode" name="program_code" required
                                placeholder="e.g., BSIT">
                            <div class="invalid-feedback">Please provide a unique program code.</div>
                        </div>

                        <div class="col-md-8 mb-3">
                            <label for="programName" class="form-label">Program Name <span
                                    class="text-danger">*</span></label>
                            <input type="text" class="form-control" id="programName" name="program_name" required
                                placeholder="e.g., Bachelor of Science in IT">
                            <div class="invalid-feedback">Please provide a program name.</div>
                        </div>
                    </div>

                    <div class="mb-3">
                        <label for="description" class="form-label">Description</label>
                        <textarea class="form-control" id="description" name="description" rows="2"
                            placeholder="Program description"></textarea>
                    </div>

                    <div class="row">
                        <div class="col-md-3 mb-3">
                            <label for="programType" class="form-label">Program Type</label>
                            <select class="form-select" id="programType" name="program_type">
                                <option value="Diploma">Diploma</option>
                            </select>
                        </div>

                        <div class="col-md-3 mb-3">
                            <label for="department" class="form-label">Department</label>
                            <input type="text" class="form-control" id="department" name="department"
                                list="departmentList" placeholder="e.g., IT">
                            <datalist id="departmentList"></datalist>
                        </div>

                        <div class="col-md-3 mb-3">
                            <label for="gradeLevel" class="form-label">Grade Level</label>
                            <select class="form-select" id="gradeLevel" name="grade_level_id">
                                <option value="">Select Grade Level</option>
                            </select>
                        </div>

                        <div class="col-md-3 mb-3">
                            <label for="durationYears" class="form-label">Duration (Years)</label>
                            <input type="number" class="form-control" id="durationYears" name="duration_years" value="4"
                                min="0" max="10" step="0.5">
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-md-3 mb-3">
                            <label for="totalCredits" class="form-label">Total Credits</label>
                            <input type="number" class="form-control" id="totalCredits" name="total_credits" value="120"
                                min="0">
                        </div>

                        <div class="col-md-3 mb-3">
                            <label for="maxStudents" class="form-label">Max Students</label>
                            <input type="number" class="form-control" id="maxStudents" name="max_students" value="0"
                                min="0">
                        </div>

                        <div class="col-md-3 mb-3">
                            <label for="tuitionFee" class="form-label">Tuition Fee</label>
                            <input type="number" class="form-control" id="tuitionFee" name="tuition_fee" value="0"
                                min="0" step="0.01">
                        </div>

                        <div class="col-md-3 mb-3">
                            <label for="sortOrder" class="form-label">Sort Order</label>
                            <input type="number" class="form-control" id="sortOrder" name="sort_order" value="0"
                                min="0">
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label for="startDate" class="form-label">Start Date</label>
                            <input type="date" class="form-control" id="startDate" name="start_date">
                        </div>

                        <div class="col-md-6 mb-3">
                            <label for="endDate" class="form-label">End Date</label>
                            <input type="date" class="form-control" id="endDate" name="end_date">
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
                    <button type="submit" class="btn btn-primary" id="submitBtn">Save Program</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- View Program Details Modal -->
<div class="modal fade" id="viewProgramModal" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Program Details</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body" id="programDetailsBody">
                <!-- Details will be loaded here -->
            </div>
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
                <p>Are you sure you want to delete this program?</p>
                <p class="text-muted mb-0"><strong id="deleteProgramName"></strong></p>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                <button type="button" class="btn btn-danger" id="confirmDeleteBtn">Delete</button>
            </div>
        </div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.0/dist/chart.umd.min.js"></script>
<script src="assets/js/program_management.js"></script>
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