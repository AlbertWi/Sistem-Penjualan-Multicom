@extends('layouts.app')

@section('title', 'Daftar Aksesoris')

@section('content')
<div class="container">
    <h1 class="mb-4">Daftar Aksesoris</h1>
    <a href="{{ route('manajer_operasional.accessories.create') }}" class="btn btn-primary mb-3">+ Tambah Aksesoris</a>
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
                        <a href="{{ route('manajer_operasional.accessories.edit', $item->id) }}" class="btn btn-warning btn-sm">Edit</a>
                        <form action="{{ route('manajer_operasional.accessories.destroy', $item->id) }}" method="POST" style="display:inline-block">
                        </form>
                    </td>
                </tr>
            @empty
                <tr><td colspan="6">Belum ada data Aksesoris.</td></tr>
            @endforelse
        </tbody>
    </table>

    {{ $accessories->links() }}
</div>
@endsection
