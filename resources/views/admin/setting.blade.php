@extends('layouts.admin')
@section('content')
<div class="main-content" style="max-width: 1000px; margin: 0 auto; padding: 20px; background: #f8f9fa; border-radius: 10px;">

    <style>
        .text-danger {
            font-size: initial;
            line-height: 36px;
        }
        .alert {
            font-size: initial;
        }
        .wg-box {
            background: #ffffff;
            padding: 30px;
            border-radius: 10px;
            box-shadow: 0px 4px 15px rgba(0, 0, 0, 0.1);
        }
        .form-control {
            border-radius: 5px;
            padding: 10px;
            width: 100%;
            border: 1px solid #ced4da;
        }
        .btn-primary {
            background: #007bff;
            border: none;
            padding: 10px 20px;
            border-radius: 5px;
            cursor: pointer;
        }
    </style>

    <div class="main-content-inner">
        <div class="main-content-wrap">
            <div class="flex items-center flex-wrap justify-between gap20 mb-27">
                <h3>Settings</h3>
                <ul class="breadcrumbs flex items-center flex-wrap justify-start gap10">
                    <li>
                        <a href="{{ route('admin.index') }}">
                            <div class="text-tiny">Dashboard</div>
                        </a>
                    </li>
                    <li>
                        <i class="icon-chevron-right"></i>
                    </li>
                    <li>
                        <div class="text-tiny">Settings</div>
                    </li>
                </ul>
            </div>

            <div class="wg-box">
                <div class="col-lg-12">
                    <div class="page-content my-account__edit">
                        <div class="my-account__edit-form">
                            <form name="account_edit_form" action="{{ route('admin.update.password') }}" method="POST" class="form-new-product form-style-1 needs-validation" novalidate="">
                                @csrf
                                <div class="row">
                                    @if(session('success'))
                                    <div class="alert alert-success">{{ session('success') }}</div>
                                @endif

                                @if(session('error'))
                                    <div class="alert alert-danger">{{ session('error') }}</div>
                                @endif

                                @if($errors->any())
                                    <div class="alert alert-danger">
                                        <ul>
                                            @foreach($errors->all() as $error)
                                                <li>{{ $error }}</li>
                                            @endforeach
                                        </ul>
                                    </div>
                                @endif
                                    <div class="col-md-12">
                                        <div class="my-3">
                                            <h5 class="text-uppercase mb-0">Password Change</h5>
                                        </div>
                                    </div>
                                    <div class="col-md-12">
                                        <fieldset class="name">
                                            <label class="body-title pb-3">Old password <span class="text-danger">*</span></label>
                                            <input class="form-control" type="password" placeholder="Old password" id="old_password" name="old_password" required>
                                        </fieldset>
                                    </div>
                                    <div class="col-md-12">
                                        <fieldset class="name">
                                            <label class="body-title pb-3">New password <span class="text-danger">*</span></label>
                                            <input class="form-control" type="password" placeholder="New password" id="new_password" name="new_password" required>
                                        </fieldset>
                                    </div>
                                    <div class="col-md-12">
                                        <fieldset class="name">
                                            <label class="body-title pb-3">Confirm new password <span class="text-danger">*</span></label>
                                            <input class="form-control" type="password" placeholder="Confirm new password" id="new_password_confirmation" name="new_password_confirmation" required>
                                            <div class="invalid-feedback">Passwords did not match!</div>
                                        </fieldset>
                                    </div>
                                    <div class="col-md-12 text-center">
                                        <div class="my-3">
                                            <button type="submit" class="btn btn-primary">Save Changes</button>
                                        </div>
                                    </div>
                                </div>
                            </form>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
