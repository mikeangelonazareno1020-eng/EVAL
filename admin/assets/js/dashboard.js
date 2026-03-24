function initializeCharts() {
    // Performance Trend Chart
    const performanceCtx = document.getElementById('performanceChart').getContext('2d');
    new Chart(performanceCtx, {
        type: 'line',
        data: {
            labels: ['August', 'September', 'October', 'November', 'December', 'January'],
            datasets: [{
                label: 'Average Performance Score',
                data: [3.8, 3.9, 4.0, 4.1, 4.15, 4.2],
                borderColor: '#030b9e',
                backgroundColor: 'rgba(3, 11, 158, 0.1)',
                tension: 0.4,
                fill: true
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: true,
            plugins: {
                legend: {
                    display: false
                }
            },
            scales: {
                y: {
                    beginAtZero: false,
                    min: 3.5,
                    max: 5,
                    ticks: {
                        callback: function (value) {
                            return value.toFixed(1);
                        }
                    }
                }
            }
        }
    });

    // Department Distribution Pie Chart
    const departmentCtx = document.getElementById('departmentChart').getContext('2d');
    new Chart(departmentCtx, {
        type: 'doughnut',
        data: {
            labels: ['Engineering', 'Marketing', 'Sales', 'HR', 'Operations'],
            datasets: [{
                data: [85, 42, 38, 28, 55],
                backgroundColor: [
                    '#030b9e',
                    '#0d6efd',
                    '#0dcaf0',
                    '#198754',
                    '#ffc107'
                ]
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: true,
            plugins: {
                legend: {
                    position: 'bottom'
                }
            }
        }
    });

    // Evaluation Status Pie Chart
    const statusCtx = document.getElementById('statusChart').getContext('2d');
    new Chart(statusCtx, {
        type: 'pie',
        data: {
            labels: ['Completed', 'Pending', 'In Review', 'Overdue'],
            datasets: [{
                data: [180, 34, 22, 12],
                backgroundColor: [
                    '#198754',
                    '#ffc107',
                    '#0dcaf0',
                    '#dc3545'
                ]
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: true,
            plugins: {
                legend: {
                    position: 'bottom'
                }
            }
        }
    });

    // Performance by Category Bar Chart
    const categoryCtx = document.getElementById('categoryChart').getContext('2d');
    new Chart(categoryCtx, {
        type: 'bar',
        data: {
            labels: ['Communication', 'Teamwork', 'Problem Solving', 'Leadership', 'Technical Skills', 'Time Management'],
            datasets: [{
                label: 'Average Score',
                data: [4.3, 4.5, 4.1, 3.9, 4.4, 4.0],
                backgroundColor: '#030b9e',
                borderRadius: 5
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: true,
            plugins: {
                legend: {
                    display: false
                }
            },
            scales: {
                y: {
                    beginAtZero: false,
                    min: 3,
                    max: 5,
                    ticks: {
                        callback: function (value) {
                            return value.toFixed(1);
                        }
                    }
                }
            }
        }
    });
}