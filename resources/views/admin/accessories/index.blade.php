@extends('layouts.app')

@section('title', 'Daftar Accessories')

@section('content')
<div class="container">
    <h1 class="mb-4">Daftar Accessories</h1>
    <a href="{{ route('accessories.create') }}" class="btn btn-primary mb-3">+ Tambah Accessory</a>

    @if(session('success'))
        <div class="alert alert-success">{{ session('success') }}</div>
    @endif

    <table class="table table-bordered table-striped">
        <thead class="table-dark">
            <tr>
                <th>ID</th>
                <th>Nama</th>
                <th>Aksi</th>
            </tr>
        </thead>
        <tbody>
            @forelse ($accessories as $item)
                <tr>
                    <td>{{ $item->id }}</td>
                    <td>{{ $item->name }}</td>
                    <td>
                        <a href="{{ route('accessories.edit', $item->id) }}" class="btn btn-warning btn-sm">Edit</a>
                        <form action="{{ route('accessories.destroy', $item->id) }}" method="POST" style="display:inline-block">
                            @csrf @method('DELETE')
                            <button type="submit" class="btn btn-danger btn-sm" onclick="return confirm('Hapus accessory ini?')">Hapus</button>
                        </form>
                    </td>
                </tr>
            @empty
                <tr><td colspan="6">Belum ada data accessories.</td></tr>
            @endforelse
        </tbody>
    </table>

    {{ $accessories->links() }}
</div>
@endsection
