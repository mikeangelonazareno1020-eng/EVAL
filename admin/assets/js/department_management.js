/**
 * department management JS - CRUD operations
 */

let deptPage = 1;
let deptLimit = 10;
let deptSearch = '';
let deleteDeptId = null;

window.addEventListener('load', () => {
    setTimeout(() => {
        const lo = document.getElementById('loadingOverlay');
        if (lo) lo.style.display = 'none';
        const dc = document.getElementById('dashboardContent');
        if (dc) dc.style.display = 'block';
        loadDepartments();
    }, 300);
});

document.getElementById && document.getElementById('searchInput') && document.getElementById('searchInput').addEventListener('input', function(e){
    deptSearch = e.target.value;
    deptPage = 1;
    loadDepartments();
});

document.getElementById && document.getElementById('recordsPerPage') && document.getElementById('recordsPerPage').addEventListener('change', function(e){
    deptLimit = parseInt(e.target.value);
    deptPage = 1;
    loadDepartments();
});

document.getElementById && document.getElementById('departmentForm') && document.getElementById('departmentForm').addEventListener('submit', function(e){
    e.preventDefault();
    saveDepartment();
});

function loadDepartments(){
    const tbody = document.getElementById('departmentsTableBody');
    if (!tbody) return;
    tbody.innerHTML = '<tr><td colspan="4" class="text-center py-4"><div class="spinner-border"></div></td></tr>';

    let url = `api/departments.php?action=read&page=${deptPage}&limit=${deptLimit}`;
    if (deptSearch) url += `&search=${encodeURIComponent(deptSearch)}`;

    fetch(url)
    .then(r=>r.json())
    .then(data=>{
        if (data.success){
            displayDepartments(data.data, data.pagination);
        } else {
            tbody.innerHTML = '<tr><td colspan="4" class="text-center text-danger">Error loading departments</td></tr>';
        }
    }).catch(err=>{
        console.error(err);
        tbody.innerHTML = '<tr><td colspan="4" class="text-center text-danger">Error loading departments</td></tr>';
    });
}

function displayDepartments(departments, pagination){
    const tbody = document.getElementById('departmentsTableBody');
    if (!tbody) return;
    if (!departments || departments.length===0){
        tbody.innerHTML = '<tr><td colspan="4" class="text-center text-muted py-4">No departments found</td></tr>';
        document.getElementById('paginationInfo').textContent = '';
        document.getElementById('pagination').innerHTML = '';
        return;
    }

    let html='';
    const startIndex = (pagination.current_page-1)*pagination.records_per_page;
    departments.forEach((d,i)=>{
        const dateStr = new Date(d.date_entry).toLocaleDateString();
        html += `<tr>
            <td>${startIndex + i + 1}</td>
            <td>${escapeHtml(d.department_name)}</td>
            <td>${dateStr}</td>
            <td class="text-center">
                <div class="btn-group btn-group-sm" role="group">
                    <button class="btn btn-outline-info" onclick="viewDepartment(${d.dept_id})"><i class="bi bi-eye"></i></button>
                    <button class="btn btn-outline-primary" onclick="editDepartment(${d.dept_id})"><i class="bi bi-pencil"></i></button>
                    <button class="btn btn-outline-danger" onclick="confirmDelete(${d.dept_id}, '${escapeJs(d.department_name)}')"><i class="bi bi-trash"></i></button>
                </div>
            </td>
        </tr>`;
    });

    tbody.innerHTML = html;
    updatePagination(pagination);
}

function updatePagination(pagination){
    document.getElementById('paginationInfo').textContent = `Showing ${(pagination.current_page-1)*pagination.records_per_page+1} to ${Math.min(pagination.current_page*pagination.records_per_page,pagination.total_records)} of ${pagination.total_records} entries`;
    let html='';
    html += `<li class="page-item ${pagination.current_page===1?'disabled':''}"><a class="page-link" href="#" onclick="changePage(${pagination.current_page-1});return false;">Previous</a></li>`;
    for(let i=1;i<=pagination.total_pages;i++){
        if (i===1||i===pagination.total_pages||Math.abs(i-pagination.current_page)<=2){
            html += `<li class="page-item ${i===pagination.current_page?'active':''}"><a class="page-link" href="#" onclick="changePage(${i});return false;">${i}</a></li>`;
        } else if (i===pagination.current_page-3||i===pagination.current_page+3){
            html += `<li class="page-item disabled"><a class="page-link" href="#">...</a></li>`;
        }
    }
    html += `<li class="page-item ${pagination.current_page===pagination.total_pages?'disabled':''}"><a class="page-link" href="#" onclick="changePage(${pagination.current_page+1});return false;">Next</a></li>`;
    document.getElementById('pagination').innerHTML = html;
}

function changePage(p){ deptPage = p; loadDepartments(); }

function resetForm(){
    const f = document.getElementById('departmentForm'); if (!f) return; f.reset(); document.getElementById('deptId').value=''; document.getElementById('departmentModalLabel').textContent='Add Department'; document.getElementById('submitBtn').textContent='Save Department';
}

function viewDepartment(id){
    fetch(`api/departments.php?action=read_one&id=${id}`)
    .then(r=>r.json()).then(data=>{
        if (data.success){
            const d = data.data;
            const dateStr = new Date(d.date_entry).toLocaleDateString();
            const body = document.getElementById('departmentDetailsBody');
            body.innerHTML = `
                <p><strong>Department Name:</strong> ${escapeHtml(d.department_name)}</p>
                <p><strong>Date Created:</strong> ${dateStr}</p>
            `;
            new bootstrap.Modal(document.getElementById('viewDepartmentModal')).show();
        }
    }).catch(console.error);
}

function editDepartment(id){
    fetch(`api/departments.php?action=read_one&id=${id}`)
    .then(r=>r.json()).then(data=>{
        if (data.success){
            const d = data.data;
            document.getElementById('deptId').value = d.dept_id;
            document.getElementById('departmentName').value = d.department_name;
            document.getElementById('departmentModalLabel').textContent='Edit Department';
            document.getElementById('submitBtn').textContent='Update Department';
            new bootstrap.Modal(document.getElementById('departmentModal')).show();
        }
    }).catch(console.error);
}

function saveDepartment(){
    const form = document.getElementById('departmentForm'); if (!form) return;
    if (!form.checkValidity()){ form.classList.add('was-validated'); return; }

    const id = document.getElementById('deptId').value;
    const action = id ? 'update' : 'create';

    const payload = {
        dept_id: id || undefined,
        department_name: document.getElementById('departmentName').value
    };

    const btn = document.getElementById('submitBtn'); btn.disabled = true; btn.innerHTML = '<span class="spinner-border spinner-border-sm me-2"></span>Saving...';

    fetch(`api/departments.php?action=${action}`, {
        method: 'POST', headers: {'Content-Type':'application/json'}, body: JSON.stringify(payload)
    }).then(r=>r.json()).then(data=>{
        if (data.success){
            showToast('Success', data.message, 'success');
            bootstrap.Modal.getInstance(document.getElementById('departmentModal')).hide();
            loadDepartments(); resetForm();
        } else {
            showToast('Error', data.message || 'Error', 'error');
        }
    }).catch(err=>{ console.error(err); showToast('Error','Request failed','error'); })
    .finally(()=>{ btn.disabled=false; btn.textContent = id ? 'Update Department' : 'Save Department'; });
}

function confirmDelete(id, name){ deleteDeptId = id; document.getElementById('deleteDepartmentName').textContent = name; new bootstrap.Modal(document.getElementById('deleteModal')).show(); }

document.getElementById && document.getElementById('confirmDeleteBtn') && document.getElementById('confirmDeleteBtn').addEventListener('click', function(){
    if (!deleteDeptId) return;
    const btn = this; btn.disabled=true; btn.innerHTML = '<span class="spinner-border spinner-border-sm me-2"></span>Deleting...';
    fetch('api/departments.php?action=delete', { method:'POST', headers:{'Content-Type':'application/json'}, body: JSON.stringify({dept_id: deleteDeptId}) })
    .then(r=>r.json()).then(data=>{
        if (data.success){ showToast('Success', data.message, 'success'); bootstrap.Modal.getInstance(document.getElementById('deleteModal')).hide(); loadDepartments(); }
        else showToast('Error', data.message || 'Delete failed', 'error');
    }).catch(err=>{ console.error(err); showToast('Error','Request failed','error'); })
    .finally(()=>{ btn.disabled=false; btn.textContent='Delete'; deleteDeptId=null; });
});

function showToast(title, message, type='info'){
    const toastColors = { success:'#28a745', error:'#dc3545', info:'#17a2b8' };
    const toast = document.createElement('div'); toast.className='position-fixed top-0 end-0 p-3'; toast.style.zIndex='9999';
    toast.innerHTML = `<div class="toast show" role="alert"><div class="toast-header" style="background:${toastColors[type]};color:#fff"><strong class="me-auto">${title}</strong><button type="button" class="btn-close btn-close-white" data-bs-dismiss="toast"></button></div><div class="toast-body">${message}</div></div>`;
    document.body.appendChild(toast); setTimeout(()=>toast.remove(),3000);
}

function escapeHtml(text){ if (!text) return ''; const d=document.createElement('div'); d.textContent = text; return d.innerHTML; }
function escapeJs(text){ return (text||'').replace(/'/g,"\\'").replace(/\n/g,' '); }
