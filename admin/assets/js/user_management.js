/**
 * users management JS - simple CRUD
 */

let userPage = 1;
let userLimit = 10;
let userSearch = '';
let deleteUserId = null;

window.addEventListener('load', () => {
    setTimeout(() => {
        const lo = document.getElementById('loadingOverlay');
        if (lo) lo.style.display = 'none';
        const dc = document.getElementById('dashboardContent');
        if (dc) dc.style.display = 'block';
        loadUsers();
    }, 300);
});

document.getElementById && document.getElementById('searchInput') && document.getElementById('searchInput').addEventListener('input', function(e){
    userSearch = e.target.value;
    userPage = 1;
    loadUsers();
});

document.getElementById && document.getElementById('recordsPerPage') && document.getElementById('recordsPerPage').addEventListener('change', function(e){
    userLimit = parseInt(e.target.value);
    userPage = 1;
    loadUsers();
});

document.getElementById && document.getElementById('userForm') && document.getElementById('userForm').addEventListener('submit', function(e){
    e.preventDefault();
    saveUser();
});

function loadUsers(){
    const tbody = document.getElementById('usersTableBody');
    if (!tbody) return;
    tbody.innerHTML = '<tr><td colspan="6" class="text-center py-4"><div class="spinner-border"></div></td></tr>';

    let url = `api/users.php?action=read&page=${userPage}&limit=${userLimit}`;
    if (userSearch) url += `&search=${encodeURIComponent(userSearch)}`;

    fetch(url)
    .then(r=>r.json())
    .then(data=>{
        if (data.success){
            displayUsers(data.data, data.pagination);
        } else {
            tbody.innerHTML = '<tr><td colspan="6" class="text-center text-danger">Error loading users</td></tr>';
        }
    }).catch(err=>{
        console.error(err);
        tbody.innerHTML = '<tr><td colspan="6" class="text-center text-danger">Error loading users</td></tr>';
    });
}

function displayUsers(users, pagination){
    const tbody = document.getElementById('usersTableBody');
    if (!tbody) return;
    if (!users || users.length===0){
        tbody.innerHTML = '<tr><td colspan="6" class="text-center text-muted py-4">No users found</td></tr>';
        document.getElementById('paginationInfo').textContent = '';
        document.getElementById('pagination').innerHTML = '';
        return;
    }

    let html='';
    const startIndex = (pagination.current_page-1)*pagination.records_per_page;
    users.forEach((u,i)=>{
        const name = `${escapeHtml(u.first_name)} ${escapeHtml(u.middle_name||'')} ${escapeHtml(u.last_name)}`.replace(/\s+/g,' ').trim();
        html += `<tr>
            <td>${startIndex + i + 1}</td>
            <td>${name}</td>
            <td>${escapeHtml(u.email)}</td>
            <td>${escapeHtml(u.role||'')}</td>
            <td>${escapeHtml(u.branch||'')}</td>
            <td class="text-center">
                <div class="btn-group btn-group-sm" role="group">
                    <button class="btn btn-outline-info" onclick="viewUser(${u.id})"><i class="bi bi-eye"></i></button>
                    <button class="btn btn-outline-primary" onclick="editUser(${u.id})"><i class="bi bi-pencil"></i></button>
                    <button class="btn btn-outline-danger" onclick="confirmDelete(${u.id}, '${escapeJs(name)}')"><i class="bi bi-trash"></i></button>
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

function changePage(p){ userPage = p; loadUsers(); }

function resetForm(){
    const f = document.getElementById('userForm'); if (!f) return; f.reset(); document.getElementById('userId').value=''; document.getElementById('userModalLabel').textContent='Add User'; document.getElementById('submitBtn').textContent='Save User';
}

function viewUser(id){
    fetch(`api/users.php?action=read_one&id=${id}`)
    .then(r=>r.json()).then(data=>{
        if (data.success){
            const u = data.data;
            const body = document.getElementById('userDetailsBody');
            body.innerHTML = `
                <p><strong>Name:</strong> ${escapeHtml(u.first_name)} ${escapeHtml(u.middle_name||'')} ${escapeHtml(u.last_name)}</p>
                <p><strong>Email:</strong> ${escapeHtml(u.email)}</p>
                <p><strong>Role:</strong> ${escapeHtml(u.role)}</p>
                <p><strong>Branch:</strong> ${escapeHtml(u.branch)}</p>
            `;
            new bootstrap.Modal(document.getElementById('viewUserModal')).show();
        }
    }).catch(console.error);
}

function editUser(id){
    fetch(`api/users.php?action=read_one&id=${id}`)
    .then(r=>r.json()).then(data=>{
        if (data.success){
            const u = data.data;
            document.getElementById('userId').value = u.id;
            document.getElementById('firstName').value = u.first_name;
            document.getElementById('middleName').value = u.middle_name || '';
            document.getElementById('lastName').value = u.last_name;
            document.getElementById('email').value = u.email;
            document.getElementById('role').value = u.role || '';
            document.getElementById('branch').value = u.branch || '';
            document.getElementById('userModalLabel').textContent='Edit User';
            document.getElementById('submitBtn').textContent='Update User';
            new bootstrap.Modal(document.getElementById('userModal')).show();
        }
    }).catch(console.error);
}

function saveUser(){
    const form = document.getElementById('userForm'); if (!form) return;
    if (!form.checkValidity()){ form.classList.add('was-validated'); return; }

    const id = document.getElementById('userId').value;
    const action = id ? 'update' : 'create';

    const payload = {
        id: id || undefined,
        first_name: document.getElementById('firstName').value,
        middle_name: document.getElementById('middleName').value,
        last_name: document.getElementById('lastName').value,
        email: document.getElementById('email').value,
        password: document.getElementById('password').value,
        role: document.getElementById('role').value,
        branch: document.getElementById('branch').value,
        profile: ''
    };

    const btn = document.getElementById('submitBtn'); btn.disabled = true; btn.innerHTML = '<span class="spinner-border spinner-border-sm me-2"></span>Saving...';

    fetch(`api/users.php?action=${action}`, {
        method: 'POST', headers: {'Content-Type':'application/json'}, body: JSON.stringify(payload)
    }).then(r=>r.json()).then(data=>{
        if (data.success){
            showToast('Success', data.message, 'success');
            bootstrap.Modal.getInstance(document.getElementById('userModal')).hide();
            loadUsers(); resetForm();
        } else {
            showToast('Error', data.message || 'Error', 'error');
        }
    }).catch(err=>{ console.error(err); showToast('Error','Request failed','error'); })
    .finally(()=>{ btn.disabled=false; btn.textContent = id ? 'Update User' : 'Save User'; });
}

function confirmDelete(id, name){ deleteUserId = id; document.getElementById('deleteUserName').textContent = name; new bootstrap.Modal(document.getElementById('deleteModal')).show(); }

document.getElementById && document.getElementById('confirmDeleteBtn') && document.getElementById('confirmDeleteBtn').addEventListener('click', function(){
    if (!deleteUserId) return;
    const btn = this; btn.disabled=true; btn.innerHTML = '<span class="spinner-border spinner-border-sm me-2"></span>Deleting...';
    fetch('api/users.php?action=delete', { method:'POST', headers:{'Content-Type':'application/json'}, body: JSON.stringify({id: deleteUserId}) })
    .then(r=>r.json()).then(data=>{
        if (data.success){ showToast('Success', data.message, 'success'); bootstrap.Modal.getInstance(document.getElementById('deleteModal')).hide(); loadUsers(); }
        else showToast('Error', data.message || 'Delete failed', 'error');
    }).catch(err=>{ console.error(err); showToast('Error','Request failed','error'); })
    .finally(()=>{ btn.disabled=false; btn.textContent='Delete'; deleteUserId=null; });
});

function showToast(title, message, type='info'){
    const toastColors = { success:'#28a745', error:'#dc3545', info:'#17a2b8' };
    const toast = document.createElement('div'); toast.className='position-fixed top-0 end-0 p-3'; toast.style.zIndex='9999';
    toast.innerHTML = `<div class="toast show" role="alert"><div class="toast-header" style="background:${toastColors[type]};color:#fff"><strong class="me-auto">${title}</strong><button type="button" class="btn-close btn-close-white" data-bs-dismiss="toast"></button></div><div class="toast-body">${message}</div></div>`;
    document.body.appendChild(toast); setTimeout(()=>toast.remove(),3000);
}

function escapeHtml(text){ if (!text) return ''; const d=document.createElement('div'); d.textContent = text; return d.innerHTML; }
function escapeJs(text){ return (text||'').replace(/'/g,"\\'").replace(/\n/g,' '); }
