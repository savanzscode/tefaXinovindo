@extends('layouts.admin')

@section('content')
<div class="main-content-inner">
    <div class="main-content-wrap">
        <div class="flex items-center flex-wrap justify-between gap20 mb-27">
            <h3>Product Detail</h3>
            <ul class="breadcrumbs flex items-center flex-wrap justify-start gap10">
                <li>
                    <a href="{{ route('admin.index') }}">
                        <div class="text-tiny">Dashboard</div>
                    </a>
                </li>
                <li><i class="icon-chevron-right"></i></li>
                <li>
                    <a href="{{ route('admin.contacts') }}">
                        <div class="text-tiny">Message</div>
                    </a>
                </li>
                <li><i class="icon-chevron-right"></i></li>
                <li><div class="text-tiny">Contact Detail</div></li>
            </ul>
        </div>

        <div class="wg-box">
            <div class="table-responsive">
                <table class="table table-striped table-bordered">
                    <tbody>
                        <tr><th>ID</th><td>{{ $contact->id }}</td></tr>
                        <tr><th>Name</th><td>{{ $contact->name }}</td></tr>
                        <tr><th>Phone</th><td>{{ $contact->phone }}</td></tr>
                        <tr><th>Email</th><td>{{ $contact->email }}</td></tr>
                        <tr><th>Comment</th><td>{{ $contact->comment }}</td></tr>
                        <tr><th>Date</th><td>{{ $contact->created_at }}</td></tr>
                    </tbody>
                </table>
            </div>

            <div class="flex justify-between items-center mt-4">
                <a href="{{ route('admin.contacts') }}" class="tf-button style-1"><i class="icon-arrow-left"></i> Back to Contact</a>
                <div class="flex gap-2">
                    <form action="{{ route('admin.contact.delete', ['id' => $contact->id]) }}" method="POST" class="inline-block">
                        @csrf
                        @method('DELETE')
                        <button type="submit" class="tf-button style-1 text-danger delete">
                            <i class="icon-trash-2"></i> Delete
                        </button>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@push('script')
<script>
    $(function(){
        $(".delete").on('click', function(e){
            e.preventDefault();
            var selectedForm = $(this).closest('form');
            swal({
                title: "Are you sure?",
                text: "You want to delete this product?",
                icon: "warning",
                buttons: ["Cancel", "Yes"],
                dangerMode: true,
            }).then((willDelete) => {
                if (willDelete) {
                    selectedForm.submit();
                }
            });
        });
    });
</script>
@endpush
