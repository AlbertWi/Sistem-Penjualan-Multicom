@extends('layouts.app')

@section('title', 'Edit Accessory')

@section('content')
<div class="container">
    <h3>Edit Accessory</h3>
    <form action="{{ route('manajer_operasional.accessories.update', $accessory->id) }}" method="POST">
        @csrf @method('PUT')
        <div class="mb-3">
            <label for="name">Nama</label>
            <input type="text" name="name" class="form-control" value="{{ $accessory->name }}" required>
        </div>
        <button type="submit" class="btn btn-primary">Update</button>
        <a href="{{ route('manajer_operasional.accessories.index') }}" class="btn btn-secondary">Batal</a>
    </form>
</div>
@endsection
