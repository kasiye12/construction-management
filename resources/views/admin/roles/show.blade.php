@extends('layouts.app')

@section('title', $role->display_name . ' - CMS')

@section('content')
<div class="page-header">
    <h2>🛡️ {{ $role->display_name }}</h2>
</div>

<div class="row">
    <div class="col-md-8">
        <div class="table-card">
            <h5>Role Details</h5><hr>
            <p><strong>Name:</strong> {{ $role->name }}</p>
            <p><strong>Display Name:</strong> {{ $role->display_name }}</p>
            <p><strong>Description:</strong> {{ $role->description ?? 'N/A' }}</p>
            <p><strong>Users:</strong> {{ $role->users->count() }}</p>
            
            <h6 class="mt-4">Permissions</h6>
            @php $perms = is_string($role->permissions) ? json_decode($role->permissions, true) : ($role->permissions ?? []); @endphp
            <ul>
                @foreach($perms as $perm)
                    <li>{{ $perm }}</li>
                @endforeach
            </ul>
        </div>
    </div>
</div>
@endsection
