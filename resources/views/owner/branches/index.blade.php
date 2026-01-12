@extends('layouts.app')

@section('title', 'Cabang Toko')

@section('content')
<div class="card">
    <div class="card-header d-flex justify-content-between align-items-center">
        <h3 class="card-title">Daftar Cabang Toko</h3>
        <button class="btn btn-primary btn-sm" data-bs-toggle="modal" data-bs-target="#createBranchModal">
            + Tambah Cabang
        </button>
    </div>
    <div class="card-body">
        
        <!-- Tabs untuk memisahkan online/offline -->
        <ul class="nav nav-tabs mb-3" id="branchTab" role="tablist">
            <li class="nav-item" role="presentation">
                <button class="nav-link active" id="all-tab" data-bs-toggle="tab" data-bs-target="#all" type="button">
                    Semua Cabang
                </button>
            </li>
            <li class="nav-item" role="presentation">
                <button class="nav-link" id="online-tab" data-bs-toggle="tab" data-bs-target="#online" type="button">
                    Online <span class="badge bg-success">{{ $onlineBranches->count() }}</span>
                </button>
            </li>
            <li class="nav-item" role="presentation">
                <button class="nav-link" id="offline-tab" data-bs-toggle="tab" data-bs-target="#offline" type="button">
                    Offline <span class="badge bg-secondary">{{ $offlineBranches->count() }}</span>
                </button>
            </li>
        </ul>

        <div class="tab-content" id="branchTabContent">
            <!-- Tab Semua Cabang -->
            <div class="tab-pane fade show active" id="all">
                <table class="table table-bordered">
                    <thead>
                        <tr>
                            <th>Kode</th>
                            <th>Nama</th>
                            <th>Alamat</th>
                            <th>Tipe</th>
                            <th>Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($branches as $branch)
                            <tr>
                                <td>{{ $branch->code }}</td>
                                <td>{{ $branch->name }}</td>
                                <td>{{ $branch->address }}</td>
                                <td>
                                    <span class="badge {{ $branch->isOnline() ? 'bg-success' : 'bg-secondary' }}">
                                        {{ $branch->isOnline() ? 'Online' : 'Offline' }}
                                    </span>
                                </td>
                                <td>
                                    <button class="btn btn-warning btn-sm" data-bs-toggle="modal"
                                        data-bs-target="#editBranchModal{{ $branch->id }}">
                                        Edit
                                    </button>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>

            <!-- Tab Online -->
            <div class="tab-pane fade" id="online">
                <table class="table table-bordered">
                    <thead>
                        <tr>
                            <th>Kode</th>
                            <th>Nama</th>
                            <th>Alamat</th>
                            <th>Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($onlineBranches as $branch)
                            <tr>
                                <td>{{ $branch->code }}</td>
                                <td>{{ $branch->name }}</td>
                                <td>{{ $branch->address }}</td>
                                <td>
                                    <button class="btn btn-warning btn-sm" data-bs-toggle="modal"
                                        data-bs-target="#editBranchModal{{ $branch->id }}">
                                        Edit
                                    </button>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>

            <!-- Tab Offline -->
            <div class="tab-pane fade" id="offline">
                <table class="table table-bordered">
                    <thead>
                        <tr>
                            <th>Kode</th>
                            <th>Nama</th>
                            <th>Alamat</th>
                            <th>Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($offlineBranches as $branch)
                            <tr>
                                <td>{{ $branch->code }}</td>
                                <td>{{ $branch->name }}</td>
                                <td>{{ $branch->address }}</td>
                                <td>
                                    <button class="btn btn-warning btn-sm" data-bs-toggle="modal"
                                        data-bs-target="#editBranchModal{{ $branch->id }}">
                                        Edit
                                    </button>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

<!-- Modal Tambah Cabang -->
<div class="modal fade" id="createBranchModal" tabindex="-1" aria-labelledby="createBranchModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <form action="{{ route('owner.branches.store') }}" method="POST">
            @csrf
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="createBranchModalLabel">Tambah Cabang Baru</h5>
                </div>
                <div class="modal-body">
                    @if ($errors->any() && !old('_method'))
                        <div class="alert alert-danger">
                            <ul class="mb-0">
                                @foreach ($errors->all() as $error)
                                    <li>{{ $error }}</li>
                                @endforeach
                            </ul>
                        </div>
                    @endif
                    <div class="mb-3">
                        <label>Nama Cabang</label>
                        <input type="text" name="name" class="form-control" value="{{ old('name') }}">
                        @error('name')
                            @if(!old('_method'))
                                <div class="text-danger small mt-1">{{ $message }}</div>
                            @endif
                        @enderror
                    </div>
                    <div class="mb-3">
                        <label>Alamat</label>
                        <textarea name="address" class="form-control" rows="3">{{ old('address') }}</textarea>
                        @error('address')
                            @if(!old('_method'))
                                <div class="text-danger small mt-1">{{ $message }}</div>
                            @endif
                        @enderror
                    </div>
                    <div class="mb-3">
                        <label>Tipe Cabang</label>
                        <select name="branch_type" class="form-select">
                            <option value="">Pilih Tipe Cabang</option>
                            <option value="online" {{ old('branch_type') == 'online' ? 'selected' : '' }}>Online</option>
                            <option value="offline" {{ old('branch_type') == 'offline' ? 'selected' : '' }}>Offline</option>
                        </select>
                        @error('branch_type')
                            @if(!old('_method'))
                                <div class="text-danger small mt-1">{{ $message }}</div>
                            @endif
                        @enderror
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
                    <button type="submit" class="btn btn-primary">Simpan</button>
                </div>
            </div>
        </form>
    </div>
</div>

<!-- Modal Edit Cabang (Perlu diupdate juga) -->
@foreach($branches as $branch)
<div class="modal fade" id="editBranchModal{{ $branch->id }}" tabindex="-1"
    aria-labelledby="editBranchModalLabel{{ $branch->id }}" aria-hidden="true">
    <div class="modal-dialog">
        <form action="{{ route('owner.branches.update', $branch) }}" method="POST">
            @csrf
            @method('PUT')
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="editBranchModalLabel{{ $branch->id }}">Edit Cabang</h5>
                </div>
                <div class="modal-body">
                    @if ($errors->any() && old('_method') == 'PUT' && old('branch_id') == $branch->id)
                        <div class="alert alert-danger">
                            <ul class="mb-0">
                                @foreach ($errors->all() as $error)
                                    <li>{{ $error }}</li>
                                @endforeach
                            </ul>
                        </div>
                    @endif
                    <div class="mb-3">
                        <label>Nama Cabang</label>
                        <input type="text" name="name" class="form-control"
                            value="{{ old('name', $branch->name) }}">
                        @error('name')
                            @if(old('_method') == 'PUT' && old('branch_id') == $branch->id)
                                <div class="text-danger small mt-1">{{ $message }}</div>
                            @endif
                        @enderror
                    </div>
                    <div class="mb-3">
                        <label>Alamat</label>
                        <textarea name="address" class="form-control" rows="3">{{ old('address', $branch->address) }}</textarea>
                        @error('address')
                            @if(old('_method') == 'PUT' && old('branch_id') == $branch->id)
                                <div class="text-danger small mt-1">{{ $message }}</div>
                            @endif
                        @enderror
                    </div>
                    <div class="mb-3">
                        <label>Tipe Cabang</label>
                        <select name="branch_type" class="form-select">
                            <option value="online" {{ old('branch_type', $branch->branch_type) == 'online' ? 'selected' : '' }}>Online</option>
                            <option value="offline" {{ old('branch_type', $branch->branch_type) == 'offline' ? 'selected' : '' }}>Offline</option>
                        </select>
                        @error('branch_type')
                            @if(old('_method') == 'PUT' && old('branch_id') == $branch->id)
                                <div class="text-danger small mt-1">{{ $message }}</div>
                            @endif
                        @enderror
                    </div>
                    <input type="hidden" name="branch_id" value="{{ $branch->id }}">
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
                    <button type="submit" class="btn btn-primary">Simpan</button>
                </div>
            </div>
        </form>
    </div>
</div>
@endforeach

<script>
document.addEventListener('DOMContentLoaded', function() {
    @if ($errors->any())
        @if(old('_method') == 'PUT' && old('branch_id'))
            var editModal = new bootstrap.Modal(document.getElementById('editBranchModal{{ old("branch_id") }}'));
            editModal.show();
        @else
            var createModal = new bootstrap.Modal(document.getElementById('createBranchModal'));
            createModal.show();
        @endif
    @endif
});
</script>
@endsection