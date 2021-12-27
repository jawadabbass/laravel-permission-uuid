@extends('vendor.jawad_permission.layouts.app')
@section('content')
    <h5>{{ __('Manage Users') }}</h5>
    @include('vendor.jawad_permission.layouts.alert')
    @include('vendor.jawad_permission.common_files.validation_errors')
    <form name="store_users" id="store_users" method="POST" action="{{ route('users.store') }}" class="form"
        enctype="multipart/form-data">
        @include('vendor.jawad_permission.user.forms.form')
        <label>{{ __('User has following roles!') }}</label>
        <div class="@error('role_ids') is-invalid @enderror">
            {!! generateRolesCheckBoxes($user) !!}
        </div>
        @error('role_ids')
            <div class="text-danger">{{ $message }}</div>
        @enderror
        <button type="submit" class="btn btn-success">{{ __('Submit') }}</button>
    </form>
@endsection
