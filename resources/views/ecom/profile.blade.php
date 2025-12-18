@extends('layouts.catalog')

@section('content')
<div class="profile-wrapper">
    <div class="container">
        <div class="profile-header">
            <h3 class="section-title">Profil Saya</h3>
        </div>

        @if(session('success'))
        <div class="alert-success">
            <i class="fas fa-check-circle"></i>
            {{ session('success') }}
        </div>
        @endif

        <div class="profile-card">
            <div class="profile-card-header">
                <h5>Informasi Pribadi</h5>
                <button class="btn btn-primary btn-sm" onclick="openEditModal()">
                    <i class="fas fa-edit"></i> Edit Profil
                </button>
            </div>

            <div class="profile-info">
                <div class="info-row">
                    <span class="info-label">Nama</span>
                    <span class="info-value">{{ $customer->name }}</span>
                </div>
                <div class="info-row">
                    <span class="info-label">Email</span>
                    <span class="info-value">{{ $customer->email }}</span>
                </div>
                <div class="info-row">
                    <span class="info-label">No. Telepon</span>
                    <span class="info-value">{{ $customer->phone ?? '-' }}</span>
                </div>
                <div class="info-row">
                    <span class="info-label">Jenis Kelamin</span>
                    <span class="info-value">
                        @if($customer->jenis_kelamin == 'pria')
                        Pria
                        @elseif($customer->jenis_kelamin == 'wanita')
                        Wanita
                        @else
                        -
                        @endif
                    </span>
                </div>
                <div class="info-row">
                    <span class="info-label">Tanggal Lahir</span>
                    <span class="info-value">{{ $customer->tanggal_lahir ?? '-' }}</span>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Modal Edit Profile -->
<div class="modal-overlay" id="editModal" onclick="closeModalOnOutsideClick(event)">
    <div class="modal-container">
        <form method="POST" action="{{ route('ecom.profile.update') }}" class="modal-content-form">
            @csrf

            <div class="modal-header-custom">
                <h5 class="modal-title-custom">Edit Profil</h5>
                <button type="button" class="modal-close-btn" onclick="closeEditModal()">
                    <i class="fas fa-times"></i>
                </button>
            </div>

            <div class="modal-body-custom">
                <div class="form-group">
                    <label class="form-label">Nama</label>
                    <input name="name"
                        type="text"
                        class="form-control"
                        value="{{ $customer->name }}"
                        placeholder="Masukkan nama lengkap">
                </div>

                <div class="form-group">
                    <label class="form-label">Email</label>
                    <input name="email"
                        type="email"
                        class="form-control"
                        value="{{ $customer->email }}"
                        placeholder="Masukkan email">
                </div>

                <div class="form-group">
                    <label class="form-label">Telepon</label>
                    <input name="phone"
                        type="text"
                        class="form-control"
                        value="{{ $customer->phone }}"
                        placeholder="Masukkan nomor telepon">
                </div>

                <div class="form-group">
                    <label class="form-label">Jenis Kelamin</label>
                    <select name="jenis_kelamin" class="form-control">
                        <option value="">-- Pilih --</option>
                        <option value="pria" {{ $customer->jenis_kelamin=='pria' ? 'selected' : '' }}>Pria</option>
                        <option value="wanita" {{ $customer->jenis_kelamin=='wanita' ? 'selected' : '' }}>Wanita</option>
                    </select>
                </div>

                <div class="form-group">
                    <label class="form-label">Tanggal Lahir</label>
                    <input name="tanggal_lahir"
                        type="date"
                        class="form-control"
                        value="{{ $customer->tanggal_lahir }}">
                </div>
                <div class="form-group mt-3">
                    <label>Password Baru (opsional)</label>
                    <input type="password" name="password" class="form-control">
                </div>

                <div class="form-group mt-3">
                    <label>Password Lama (wajib jika ganti password)</label>
                    <input type="password" name="current_password" class="form-control">
                </div>

            </div>
            <div class="modal-footer-custom">
                <button type="button" class="btn btn-secondary" onclick="closeEditModal()">
                    <i class="fas fa-times"></i> Batal
                </button>
                <button type="submit" class="btn btn-primary">
                    <i class="fas fa-save"></i> Simpan Perubahan
                </button>
            </div>
        </form>
    </div>
</div>

<style>
    /* Profile Page Styles */
    .profile-wrapper {
        padding: 40px 0;
        min-height: 60vh;
    }

    .profile-header {
        margin-bottom: 30px;
    }

    .alert-success {
        background-color: var(--success);
        color: white;
        padding: 15px 20px;
        border-radius: 8px;
        margin-bottom: 25px;
        display: flex;
        align-items: center;
        gap: 10px;
        animation: slideDown 0.3s ease;
    }

    @keyframes slideDown {
        from {
            opacity: 0;
            transform: translateY(-10px);
        }
        to {
            opacity: 1;
            transform: translateY(0);
        }
    }

    .profile-card {
        background: white;
        border-radius: 10px;
        box-shadow: 0 5px 15px rgba(0,0,0,0.08);
        overflow: hidden;
    }

    .profile-card-header {
        display: flex;
        justify-content: space-between;
        align-items: center;
        padding: 25px 30px;
        border-bottom: 2px solid var(--border-color);
        background: var(--light-gray);
    }

    .profile-card-header h5 {
        margin: 0;
        color: var(--dark);
        font-size: 20px;
        font-weight: 600;
    }

    .btn-sm {
        padding: 8px 18px;
        font-size: 14px;
    }

    .profile-info {
        padding: 30px;
    }

    .info-row {
        display: flex;
        justify-content: space-between;
        padding: 18px 0;
        border-bottom: 1px solid var(--border-color);
    }

    .info-row:last-child {
        border-bottom: none;
    }

    .info-label {
        color: var(--gray);
        font-weight: 500;
        min-width: 200px;
    }

    .info-value {
        color: var(--dark);
        font-weight: 600;
        text-align: right;
        flex: 1;
    }

    /* Modal Styles */
    .modal-overlay {
        display: none;
        position: fixed;
        top: 0;
        left: 0;
        width: 100%;
        height: 100%;
        background-color: rgba(0, 0, 0, 0.6);
        z-index: 1000;
        align-items: center;
        justify-content: center;
        animation: fadeIn 0.3s ease;
    }

    .modal-overlay.active {
        display: flex;
    }

    @keyframes fadeIn {
        from {
            opacity: 0;
        }
        to {
            opacity: 1;
        }
    }

    .modal-container {
        background: white;
        border-radius: 10px;
        width: 90%;
        max-width: 500px;
        max-height: 90vh;
        overflow: hidden;
        animation: slideUp 0.3s ease;
    }

    @keyframes slideUp {
        from {
            transform: translateY(50px);
            opacity: 0;
        }
        to {
            transform: translateY(0);
            opacity: 1;
        }
    }

    .modal-content-form {
        display: flex;
        flex-direction: column;
        max-height: 90vh;
        height: 100%;
    }

    .modal-header-custom {
        display: flex;
        justify-content: space-between;
        align-items: center;
        padding: 20px 25px;
        border-bottom: 2px solid var(--border-color);
        background: var(--light-gray);
    }

    .modal-title-custom {
        margin: 0;
        font-size: 20px;
        color: var(--dark);
        font-weight: 600;
    }

    .modal-close-btn {
        background: none;
        border: none;
        font-size: 24px;
        color: var(--gray);
        cursor: pointer;
        padding: 0;
        width: 32px;
        height: 32px;
        display: flex;
        align-items: center;
        justify-content: center;
        border-radius: 5px;
        transition: all 0.3s;
    }

    .modal-close-btn:hover {
        background: var(--border-color);
        color: var(--dark);
    }

    .modal-body-custom {
        padding: 25px;
        overflow-y: auto;
        overflow-x: hidden;
        flex: 1;
        max-height: calc(90vh - 140px);
    }

    .form-label {
        display: block;
        margin-bottom: 8px;
        color: var(--dark);
        font-weight: 600;
        font-size: 14px;
    }

    .modal-footer-custom {
        display: flex;
        justify-content: flex-end;
        gap: 12px;
        padding: 20px 25px;
        border-top: 2px solid var(--border-color);
        background: var(--light-gray);
    }

    .btn-secondary {
        background-color: var(--gray);
        color: white;
    }

    .btn-secondary:hover {
        background-color: #7f8c8d;
    }

    /* Responsive */
    @media (max-width: 768px) {
        .profile-card-header {
            flex-direction: column;
            gap: 15px;
            align-items: flex-start;
        }

        .profile-card-header .btn {
            width: 100%;
        }

        .info-row {
            flex-direction: column;
            gap: 8px;
        }

        .info-label {
            min-width: auto;
        }

        .info-value {
            text-align: left;
        }

        .modal-container {
            width: 95%;
        }

        .modal-footer-custom {
            flex-direction: column;
        }

        .modal-footer-custom .btn {
            width: 100%;
        }
    }
</style>

<script>
    function openEditModal() {
        document.getElementById('editModal').classList.add('active');
        document.body.style.overflow = 'hidden';
    }

    function closeEditModal() {
        document.getElementById('editModal').classList.remove('active');
        document.body.style.overflow = '';
    }

    function closeModalOnOutsideClick(event) {
        if (event.target.id === 'editModal') {
            closeEditModal();
        }
    }

    // Close modal on Escape key
    document.addEventListener('keydown', function(event) {
        if (event.key === 'Escape') {
            closeEditModal();
        }
    });
</script>

@endsection