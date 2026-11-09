@extends('saas.layout')

@section('title', 'Super Admins')
@section('topbar_title', 'Super Admins')

@section('content')

<style>
    .admins-page-header { display: flex; justify-content: space-between; align-items: flex-start; margin-bottom: 28px; }
    .admins-page-header h1 { margin: 0; font-size: 28px; line-height: 1.2; letter-spacing: -.7px; color: #111827; }
    .admins-page-header p { margin: 8px 0 0; color: #6b7280; font-size: 14px; }
    .btn-add-admin { display: inline-flex; align-items: center; gap: 7px; min-height: 40px; padding: 0 18px; border-radius: 9px; border: none; background: #16a34a; color: #fff; font-size: 13px; font-weight: 700; cursor: pointer; text-decoration: none; box-shadow: 0 4px 12px rgba(22,163,74,.2); }
    .btn-add-admin:hover { background: #15803d; }
    .admins-table-card { background: #fff; border: 1px solid #e7e9ef; border-radius: 14px; overflow: hidden; box-shadow: 0 2px 8px rgba(17,24,39,.025); }
    .admins-table { width: 100%; border-collapse: collapse; }
    .admins-table th { background: #fff; color: #6b7280; font-size: 11px; text-transform: uppercase; letter-spacing: .5px; font-weight: 700; text-align: left; padding: 16px 20px; border-bottom: 1px solid #eef0f4; }
    .admins-table td { padding: 16px 20px; border-bottom: 1px solid #f0f1f4; font-size: 14px; color: #374151; vertical-align: middle; }
    .admins-table tbody tr:hover { background: #fafaff; }
    .admin-name-cell { display: flex; align-items: center; gap: 12px; }
    .admin-avatar { width: 38px; height: 38px; border-radius: 10px; background: linear-gradient(135deg, #3b82f6, #2563eb); color: #fff; display: flex; align-items: center; justify-content: center; font-size: 16px; font-weight: 800; flex-shrink: 0; }
    .admin-name { font-weight: 700; color: #111827; font-size: 14px; }
    .you-badge { display: inline-flex; align-items: center; gap: 4px; padding: 3px 8px; border-radius: 999px; background: #ecfdf5; color: #16a34a; font-size: 11px; font-weight: 700; margin-left: 6px; }
    .role-badge { display: inline-flex; align-items: center; padding: 5px 12px; border-radius: 999px; background: #16a34a; color: #fff; font-size: 11px; font-weight: 700; letter-spacing: .3px; }
    .btn-edit { display: inline-flex; align-items: center; gap: 6px; padding: 7px 14px; border-radius: 8px; border: 1px solid #e5e7eb; background: #fff; color: #374151; font-size: 12px; font-weight: 700; cursor: pointer; transition: all .15s ease; }
    .btn-edit:hover { background: #f9fafb; }
    .modal-backdrop { display: none; position: fixed; top: 0; left: 0; width: 100%; height: 100%; background: rgba(0,0,0,0.5); z-index: 9999; justify-content: center; align-items: center; }
    .modal-backdrop.show { display: flex; }
    .modal-panel { background: #fff; border-radius: 14px; width: 500px; max-width: 90%; padding: 28px; box-shadow: 0 20px 40px rgba(0,0,0,.15); }
    .modal-header { display: flex; justify-content: space-between; align-items: center; margin-bottom: 24px; }
    .modal-header h3 { margin: 0; font-size: 18px; font-weight: 800; }
    .modal-close { background: none; border: none; font-size: 22px; cursor: pointer; color: #6b7280; }
    .form-group { margin-bottom: 16px; }
    .form-group label { display: block; font-size: 13px; font-weight: 600; margin-bottom: 6px; color: #374151; }
    .form-group input { width: 100%; padding: 10px 14px; border: 1px solid #e5e7eb; border-radius: 8px; outline: none; font-size: 14px; }
    .form-group input:focus { border-color: #16a34a; }
    .form-group small { display: block; color: #9ca3af; font-size: 11px; margin-top: 4px; }
    .modal-actions { display: flex; justify-content: flex-end; gap: 10px; margin-top: 24px; }
    .btn-cancel { padding: 10px 18px; border-radius: 8px; border: 1px solid #e5e7eb; background: #fff; color: #374151; font-size: 13px; font-weight: 700; cursor: pointer; }
    .btn-save { padding: 10px 18px; border-radius: 8px; border: none; background: #16a34a; color: #fff; font-size: 13px; font-weight: 700; cursor: pointer; }
</style>

<div class="admins-page-header">
    <div>
        <h1>Super Admins</h1>
        <p>Manage users who have access to the super admin panel.</p>
    </div>
    <div>
        <button class="btn-add-admin" onclick="openModal('addAdminModal')">+ Add Admin</button>
    </div>
</div>

@if(session('success'))
    <div class="alert alert-success">✓ {{ session('success') }}</div>
@endif
@if($errors->any())
    <div class="alert alert-error">{{ $errors->first() }}</div>
@endif

<div class="admins-table-card">
    <table class="admins-table">
        <thead>
            <tr>
                <th style="width: 60px;">#</th>
                <th>NAME</th>
                <th>EMAIL</th>
                <th>ROLE</th>
                <th>CREATED</th>
                <th style="text-align: right;">ACTIONS</th>
            </tr>
        </thead>
        <tbody>
            @foreach($admins as $index => $admin)
                <tr>
                    <td>{{ $index + 1 }}</td>
                    <td>
                        <div class="admin-name-cell">
                            <div class="admin-avatar">{{ strtoupper(substr($admin->name, 0, 1)) }}</div>
                            <div>
                                <span class="admin-name">{{ $admin->name }}</span>
                                @if($admin->is_primary)<span class="you-badge">● You</span>@endif
                            </div>
                        </div>
                    </td>
                    <td>{{ $admin->email }}</td>
                    <td><span class="role-badge">{{ $admin->role }}</span></td>
                    <td>{{ $admin->created_at->format('M d, Y') }}</td>
                    <td style="text-align: right;">
                        <button class="btn-edit" onclick="openEditModal({{ $admin->id }}, '{{ addslashes($admin->name) }}', '{{ $admin->email }}', {{ $admin->is_primary ? 'true' : 'false' }})">
                            ✎ Edit
                        </button>
                    </td>
                </tr>
            @endforeach
        </tbody>
    </table>
</div>

{{-- ===========================
     ADD ADMIN MODAL
=========================== --}}
<div id="addAdminModal" class="modal-backdrop">
    <div class="modal-panel">
        <div class="modal-header">
            <h3>Add New Super Admin</h3>
            <button class="modal-close" onclick="closeModal('addAdminModal')">&times;</button>
        </div>
        <form method="post" action="/super/admins">
            @csrf
            <div class="form-group">
                <label>Full Name</label>
                <input name="name" required placeholder="John Doe">
            </div>
            <div class="form-group">
                <label>Email</label>
                <input name="email" type="email" required placeholder="admin@example.com">
            </div>
            <div class="form-group">
                <label>Password</label>
                <input name="password" type="password" required placeholder="Min. 8 characters">
            </div>
            <div class="modal-actions">
                <button type="button" class="btn-cancel" onclick="closeModal('addAdminModal')">Cancel</button>
                <button type="submit" class="btn-save">Create Admin</button>
            </div>
        </form>
    </div>
</div>

{{-- ===========================
     EDIT ADMIN MODAL
=========================== --}}
<div id="editAdminModal" class="modal-backdrop">
    <div class="modal-panel">
        <div class="modal-header">
            <h3>Edit Super Admin</h3>
            <button class="modal-close" onclick="closeModal('editAdminModal')">&times;</button>
        </div>
        <form method="post" id="editAdminForm">
            @csrf
            @method('PUT')

            <div class="form-group">
                <label>Full Name</label>
                <input name="name" id="edit_name" required>
            </div>

            <div class="form-group" id="email_group">
                <label>Email</label>
                <input name="email" id="edit_email" type="email" required>
                <small id="email_note"></small>
            </div>

            <div class="form-group">
                <label>New Password (leave blank to keep current)</label>
                <input name="password" type="password" placeholder="Min. 8 characters (optional)">
            </div>

            <div class="modal-actions">
                <button type="button" class="btn-cancel" onclick="closeModal('editAdminModal')">Cancel</button>
                <button type="submit" class="btn-save">Save Changes</button>
            </div>
        </form>
    </div>
</div>

<script>
    function openModal(id) {
        document.getElementById(id).classList.add('show');
    }

    function closeModal(id) {
        document.getElementById(id).classList.remove('show');
    }

    function openEditModal(id, name, email, isPrimary) {
        document.getElementById('editAdminForm').action = '/super/admins/' + id;
        document.getElementById('edit_name').value = name;
        document.getElementById('edit_email').value = email;

        const emailInput = document.getElementById('edit_email');
        const emailNote = document.getElementById('email_note');

        if (isPrimary) {
            emailInput.setAttribute('readonly', 'readonly');
            emailInput.style.background = '#f3f4f6';
            emailNote.textContent = 'Primary admin email cannot be changed.';
        } else {
            emailInput.removeAttribute('readonly');
            emailInput.style.background = '#fff';
            emailNote.textContent = '';
        }

        openModal('editAdminModal');
    }
</script>

@endsection