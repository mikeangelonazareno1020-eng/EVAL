<?php
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
                <h3 class="mb-1">Employee Evaluation Dashboard</h3>
                <p class="text-muted mb-0">Monitor and analyze employee performance metrics</p>
            </div>
        </div>

        <!-- Key Metrics Cards -->
        <div class="row mb-4">
            <div class="col-lg-3 col-md-6 mb-3">
                <div class="card border-0 shadow-sm h-100">
                    <div class="card-body">
                        <div class="d-flex justify-content-between align-items-center">
                            <div>
                                <p class="text-muted mb-1 small">Total Employees</p>
                                <h3 class="mb-0 fw-bold">248</h3>
                                <small class="text-success"><i class='bx bx-trending-up'></i> +12% this month</small>
                            </div>
                            <div class="bg-primary bg-opacity-10 p-3 rounded">
                                <i class='bx bx-user fs-2 text-primary'></i>
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
                                <p class="text-muted mb-1 small">Pending Reviews</p>
                                <h3 class="mb-0 fw-bold">34</h3>
                                <small class="text-warning"><i class='bx bx-time'></i> Due this week</small>
                            </div>
                            <div class="bg-warning bg-opacity-10 p-3 rounded">
                                <i class='bx bx-file fs-2 text-warning'></i>
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
                                <p class="text-muted mb-1 small">Average Score</p>
                                <h3 class="mb-0 fw-bold">4.2/5.0</h3>
                                <small class="text-success"><i class='bx bx-trending-up'></i> +0.3 improvement</small>
                            </div>
                            <div class="bg-success bg-opacity-10 p-3 rounded">
                                <i class='bx bx-star fs-2 text-success'></i>
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
                                <p class="text-muted mb-1 small">Top Performers</p>
                                <h3 class="mb-0 fw-bold">67</h3>
                                <small class="text-info"><i class='bx bx-trophy'></i> Rating ≥ 4.5</small>
                            </div>
                            <div class="bg-info bg-opacity-10 p-3 rounded">
                                <i class='bx bx-medal fs-2 text-info'></i>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Charts Row -->
        <div class="row mb-4">
            <!-- Performance Trend Chart -->
            <div class="col-lg-8 mb-3">
                <div class="card border-0 shadow-sm h-100">
                    <div class="card-header bg-white border-0 pt-3">
                        <h5 class="mb-0">Performance Trend (Last 6 Months)</h5>
                    </div>
                    <div class="card-body">
                        <canvas id="performanceChart" height="80"></canvas>
                    </div>
                </div>
            </div>

            <!-- Department Distribution -->
            <div class="col-lg-4 mb-3">
                <div class="card border-0 shadow-sm h-100">
                    <div class="card-header bg-white border-0 pt-3">
                        <h5 class="mb-0">Department Distribution</h5>
                    </div>
                    <div class="card-body">
                        <canvas id="departmentChart"></canvas>
                    </div>
                </div>
            </div>
        </div>

        <!-- Additional Charts Row -->
        <div class="row mb-4">
            <!-- Evaluation Status -->
            <div class="col-lg-4 mb-3">
                <div class="card border-0 shadow-sm h-100">
                    <div class="card-header bg-white border-0 pt-3">
                        <h5 class="mb-0">Evaluation Status</h5>
                    </div>
                    <div class="card-body">
                        <canvas id="statusChart"></canvas>
                    </div>
                </div>
            </div>

            <!-- Performance Categories -->
            <div class="col-lg-8 mb-3">
                <div class="card border-0 shadow-sm h-100">
                    <div class="card-header bg-white border-0 pt-3">
                        <h5 class="mb-0">Performance by Category</h5>
                    </div>
                    <div class="card-body">
                        <canvas id="categoryChart" height="80"></canvas>
                    </div>
                </div>
            </div>
        </div>

        <!-- Recent Evaluations Table -->
        <div class="row mb-4">
            <div class="col-12">
                <div class="card border-0 shadow-sm">
                    <div class="card-header bg-white border-0 pt-3 d-flex justify-content-between align-items-center">
                        <h5 class="mb-0">Recent Evaluations</h5>
                        <a href="#" class="btn btn-sm btn-outline-primary">View All</a>
                    </div>
                    <div class="card-body p-0">
                        <div class="table-responsive">
                            <table class="table table-hover mb-0">
                                <thead class="bg-light">
                                    <tr>
                                        <th class="border-0 ps-3">Employee</th>
                                        <th class="border-0">Department</th>
                                        <th class="border-0">Position</th>
                                        <th class="border-0">Score</th>
                                        <th class="border-0">Status</th>
                                        <th class="border-0">Date</th>
                                        <th class="border-0 pe-3">Action</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <tr>
                                        <td class="ps-3">
                                            <div class="d-flex align-items-center">
                                                <div
                                                    class="avatar-sm bg-primary bg-opacity-10 rounded-circle d-flex align-items-center justify-content-center me-2">
                                                    <span class="text-primary fw-bold">JD</span>
                                                </div>
                                                <div>
                                                    <div class="fw-semibold">John Doe</div>
                                                    <small class="text-muted">john.doe@company.com</small>
                                                </div>
                                            </div>
                                        </td>
                                        <td>Engineering</td>
                                        <td>Senior Developer</td>
                                        <td><span class="badge bg-success">4.8/5.0</span></td>
                                        <td><span class="badge bg-success-subtle text-success">Completed</span></td>
                                        <td>Jan 15, 2026</td>
                                        <td class="pe-3">
                                            <button class="btn btn-sm btn-link text-primary">View</button>
                                        </td>
                                    </tr>
                                    <tr>
                                        <td class="ps-3">
                                            <div class="d-flex align-items-center">
                                                <div
                                                    class="avatar-sm bg-info bg-opacity-10 rounded-circle d-flex align-items-center justify-content-center me-2">
                                                    <span class="text-info fw-bold">AS</span>
                                                </div>
                                                <div>
                                                    <div class="fw-semibold">Alice Smith</div>
                                                    <small class="text-muted">alice.smith@company.com</small>
                                                </div>
                                            </div>
                                        </td>
                                        <td>Marketing</td>
                                        <td>Marketing Manager</td>
                                        <td><span class="badge bg-success">4.6/5.0</span></td>
                                        <td><span class="badge bg-success-subtle text-success">Completed</span></td>
                                        <td>Jan 14, 2026</td>
                                        <td class="pe-3">
                                            <button class="btn btn-sm btn-link text-primary">View</button>
                                        </td>
                                    </tr>
                                    <tr>
                                        <td class="ps-3">
                                            <div class="d-flex align-items-center">
                                                <div
                                                    class="avatar-sm bg-warning bg-opacity-10 rounded-circle d-flex align-items-center justify-content-center me-2">
                                                    <span class="text-warning fw-bold">MJ</span>
                                                </div>
                                                <div>
                                                    <div class="fw-semibold">Michael Johnson</div>
                                                    <small class="text-muted">michael.j@company.com</small>
                                                </div>
                                            </div>
                                        </td>
                                        <td>Sales</td>
                                        <td>Sales Representative</td>
                                        <td><span class="badge bg-warning">3.9/5.0</span></td>
                                        <td><span class="badge bg-warning-subtle text-warning">Pending</span></td>
                                        <td>Jan 13, 2026</td>
                                        <td class="pe-3">
                                            <button class="btn btn-sm btn-link text-primary">View</button>
                                        </td>
                                    </tr>
                                    <tr>
                                        <td class="ps-3">
                                            <div class="d-flex align-items-center">
                                                <div
                                                    class="avatar-sm bg-danger bg-opacity-10 rounded-circle d-flex align-items-center justify-content-center me-2">
                                                    <span class="text-danger fw-bold">EB</span>
                                                </div>
                                                <div>
                                                    <div class="fw-semibold">Emily Brown</div>
                                                    <small class="text-muted">emily.b@company.com</small>
                                                </div>
                                            </div>
                                        </td>
                                        <td>HR</td>
                                        <td>HR Specialist</td>
                                        <td><span class="badge bg-success">4.7/5.0</span></td>
                                        <td><span class="badge bg-success-subtle text-success">Completed</span></td>
                                        <td>Jan 12, 2026</td>
                                        <td class="pe-3">
                                            <button class="btn btn-sm btn-link text-primary">View</button>
                                        </td>
                                    </tr>
                                    <tr>
                                        <td class="ps-3">
                                            <div class="d-flex align-items-center">
                                                <div
                                                    class="avatar-sm bg-secondary bg-opacity-10 rounded-circle d-flex align-items-center justify-content-center me-2">
                                                    <span class="text-secondary fw-bold">DW</span>
                                                </div>
                                                <div>
                                                    <div class="fw-semibold">David Wilson</div>
                                                    <small class="text-muted">david.w@company.com</small>
                                                </div>
                                            </div>
                                        </td>
                                        <td>Engineering</td>
                                        <td>QA Engineer</td>
                                        <td><span class="badge bg-info">4.3/5.0</span></td>
                                        <td><span class="badge bg-info-subtle text-info">In Review</span></td>
                                        <td>Jan 11, 2026</td>
                                        <td class="pe-3">
                                            <button class="btn btn-sm btn-link text-primary">View</button>
                                        </td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        </div>

    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.0/dist/chart.umd.min.js"></script>
<script>
    window.onload = function () {
        setTimeout(() => {
            document.getElementById('loadingOverlay').style.display = 'none';
            document.getElementById('dashboardContent').style.display = 'block';
            initializeCharts();
        }, 600);
    };
</script>
<script src="assets/js/dashboard.js"></script>
<style>
    .avatar-sm {
        width: 40px;
        height: 40px;
        font-size: 14px;
    }
</style>

<?php include 'includes/footer.php'; ?>