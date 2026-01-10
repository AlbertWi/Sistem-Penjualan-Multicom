@extends('layouts.app')

@section('title', 'Data Customer')

@section('content')
<div class="container-fluid">
    <div class="card shadow-sm">
        <div class="card-header">
            <h4 class="card-title mb-0">Daftar Customer</h4>
            <div class="card-tools">
                <button class="btn btn-primary btn-sm"
                    data-toggle="modal"
                    data-target="#createCustomerModal">
                    <i class="fas fa-plus"></i> Tambah Customer
                </button>
            </div>
        </div>


            <div class="table-responsive">
                <table class="table table-bordered table-striped">
                    <thead class="thead-light">
                        <tr>
                            <th>No</th>
                            <th>Nama</th>
                            <th>Telepon</th>
                            <th>Email</th>
                            <th>Jenis Kelamin</th>
                            <th>Tgl Lahir</th>
                            <th>Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse ($customers as $index => $customer)
                            <tr>
                                <td>{{ $loop->iteration }}</td>
                                <td>{{ $customer->name }}</td>
                                <td>{{ $customer->phone }}</td>
                                <td>{{ $customer->email }}</td>
                                <td>{{ $customer->jenis_kelamin }}</td>
                                <td>{{ $customer->tanggal_lahir }}</td>
                                <td>
                                    <!-- Tombol Edit -->
                                    <button class="btn btn-warning btn-sm" data-toggle="modal" data-target="#editCustomerModal{{ $customer->id }}">
                                        <i class="fas fa-edit"></i>
                                    </button>
                                </td>
                            </tr>

                            <!-- Modal Edit -->
                                <div class="modal fade" id="editCustomerModal{{ $customer->id }}" tabindex="-1" role="dialog"
                                    aria-labelledby="editCustomerModalLabel{{ $customer->id }}" aria-hidden="true">
                                    <div class="modal-dialog" role="document">
                                        <div class="modal-content">

                                            <form action="{{ route('manajer_operasional.customers.update', $customer->id) }}" method="POST">
                                                @csrf
                                                @method('PUT')

                                                <div class="modal-header">
                                                    <h5 class="modal-title" id="editCustomerModalLabel{{ $customer->id }}">
                                                        Edit Customer
                                                    </h5>
                                                    <button type="button" class="close" data-dismiss="modal">
                                                        <span>&times;</span>
                                                    </button>
                                                </div>

                                                <div class="modal-body">

                                                    {{-- Nama --}}
                                                    <div class="form-group">
                                                        <label>Nama</label>
                                                        <input type="text"
                                                            name="name"
                                                            class="form-control"
                                                            value="{{ $customer->name }}"
                                                            required>
                                                    </div>

                                                    {{-- No Telepon --}}
                                                    <div class="form-group">
                                                        <label>No. Telepon</label>
                                                        <input type="text"
                                                            name="phone"
                                                            class="form-control"
                                                            value="{{ $customer->phone }}">
                                                    </div>

                                                    {{-- Email --}}
                                                    <div class="form-group">
                                                        <label>Email</label>
                                                        <input type="email"
                                                            name="email"
                                                            class="form-control"
                                                            value="{{ $customer->email }}">
                                                    </div>

                                                    {{-- Jenis Kelamin --}}
                                                    <div class="form-group">
                                                        <label>Jenis Kelamin</label>
                                                        <select name="jenis_kelamin" class="form-control" required>
                                                            <option value="">- Pilih -</option>
                                                            <option value="pria" {{ $customer->jenis_kelamin == 'L' ? 'selected' : '' }}>
                                                                Pria
                                                            </option>
                                                            <option value="wanita" {{ $customer->jenis_kelamin == 'P' ? 'selected' : '' }}>
                                                                Wanita
                                                            </option>
                                                        </select>
                                                    </div>

                                                    {{-- Tanggal Lahir --}}
                                                    <div class="form-group">
                                                        <label>Tanggal Lahir</label>
                                                        <input type="date"
                                                            name="tanggal_lahir"
                                                            class="form-control"
                                                            value="{{ $customer->tanggal_lahir }}">
                                                    </div>

                                                    {{-- Password --}}
                                                    <div class="form-group">
                                                        <label>Password Baru (Opsional)</label>
                                                        <input type="password"
                                                            name="password"
                                                            class="form-control"
                                                            placeholder="Kosongkan jika tidak diubah">
                                                    </div>

                                                </div>

                                                <div class="modal-footer">
                                                    <button type="button" class="btn btn-secondary" data-dismiss="modal">
                                                        Batal
                                                    </button>
                                                    <button type="submit" class="btn btn-primary">
                                                        Simpan Perubahan
                                                    </button>
                                                </div>

                                            </form>

                                        </div>
                                    </div>
                                </div>
                            </div>
                        @empty
                            <tr>
                                <td colspan="7" class="text-center">Belum ada customer</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
                <div class="mt-3">
                    {{ $customers->links() }}
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Modal Tambah -->
<div class="modal fade" id="createCustomerModal" tabindex="-1" role="dialog" aria-labelledby="createCustomerModalLabel" aria-hidden="true">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <form action="{{ route('manajer_operasional.customers.store') }}" method="POST">
                @csrf
                <div class="modal-header">
                    <h5 class="modal-title" id="createCustomerModalLabel">Tambah Customer</h5>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <div class="modal-body">
                    <div class="form-group">
                        <label>Nama</label>
                        <input type="text" name="name" class="form-control" required>
                    </div>

                    <div class="form-group">
                        <label>No. Telepon</label>
                        <input type="text" name="phone" class="form-control">
                    </div>

                    <div class="form-group">
                        <label>Email</label>
                        <input type="email" name="email" class="form-control">
                    </div>

                    <div class="form-group">
                        <label>Jenis Kelamin</label>
                        <select name="jenis_kelamin" class="form-control">
                            <option value="">- Pilih -</option>
                            <option value="pria">Pria</option>
                            <option value="wanita">Wanita</option>
                        </select>
                    </div>

                    <div class="form-group">
                        <label>Tanggal Lahir</label>
                        <input type="date" name="tanggal_lahir" class="form-control">
                    </div>

                    <div class="form-group">
                        <label>Password</label>
                        <input type="password" name="password" class="form-control">
                    </div>

                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">Batal</button>
                    <button type="submit" class="btn btn-primary">Simpan</button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection
