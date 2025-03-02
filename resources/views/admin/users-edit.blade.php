@extends('layouts.admin')

@section('content')
<div class="main-content-inner">
    <div class="main-content-wrap">
        <div class="wg-box p-5 bg-white rounded-lg shadow-lg">
            <h3 class="text-2xl font-semibold text-gray-700 mb-4">Edit Profile</h3>
            @if(session('success'))
                <div class="p-3 mb-4 text-green-700 bg-green-100 border border-green-400 rounded-lg">
                    {{ session('success') }}
                </div>
            @endif
            <form action="{{ route('admin.update', ['id' => $user->id]) }}" method="POST" class="space-y-4">
                @csrf
                <div class="form-group">
                    <label for="name" class="block text-gray-600">Name</label>
                    <input type="text" name="name" id="name" class="w-full px-4 py-2 border rounded-lg focus:ring focus:ring-blue-200" value="{{ $user->name }}" required>
                </div>
                <div class="form-group">
                    <label for="email" class="block text-gray-600">Email</label>
                    <input type="email" name="email" id="email" class="w-full px-4 py-2 border rounded-lg focus:ring focus:ring-blue-200" value="{{ $user->email }}" required>
                </div>
                <div class="form-group">
                    <label for="phone" class="block text-gray-600">Phone</label>
                    <input type="text" name="phone" id="phone" class="w-full px-4 py-2 border rounded-lg focus:ring focus:ring-blue-200" value="{{ $user->mobile }}">
                </div>
                <button type="submit" class="tf-button w208">Update Profile</button>
            </form>
        </div>
    </div>
</div>
@endsection
