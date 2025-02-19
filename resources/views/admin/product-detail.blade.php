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
                    <a href="{{ route('admin.products') }}">
                        <div class="text-tiny">Products</div>
                    </a>
                </li>
                <li><i class="icon-chevron-right"></i></li>
                <li><div class="text-tiny">Product Detail</div></li>
            </ul>
        </div>

        <div class="wg-box">
            <div class="table-responsive">
                <table class="table table-striped table-bordered">
                    <tbody>
                        <tr><th>ID</th><td>{{ $product->id }}</td></tr>
                        <tr>
                            <th>Image</th>
                            <td><img src="{{ asset('uploads/products/' . $product->image) }}" alt="{{ $product->name }}" width="80"></td>
                        </tr>
                        <tr><th>Name</th><td>{{ $product->name }}</td></tr>
                        <tr><th>Slug</th><td>{{ $product->slug }}</td></tr>
                        <tr><th>Price</th><td>${{ $product->regular_price }}</td></tr>
                        <tr><th>Sale Price</th><td>${{ $product->sale_price }}</td></tr>
                        <tr><th>SKU</th><td>{{ $product->SKU }}</td></tr>
                        <tr><th>Category</th><td>{{ $product->category->name }}</td></tr>
                        <tr><th>Brand</th><td>{{ $product->brand->name }}</td></tr>
                        <tr><th>Stock</th><td>{{ $product->stock_status }}</td></tr>
                        <tr><th>Quantity</th><td>{{ $product->quantity }}</td></tr>
                        <tr><th>Featured</th><td>{{ $product->featured ? 'Yes' : 'No' }}</td></tr>
                    </tbody>
                </table>
            </div>

            <div class="flex justify-between items-center mt-4">
                <a href="{{ route('admin.products') }}" class="tf-button style-1"><i class="icon-arrow-left"></i> Back to Products</a>
                <div class="flex gap-2">
                    <a href="{{ route('admin.product.edit', ['id' => $product->id]) }}" class="tf-button style-1">
                        <i class="icon-edit-3"></i> Edit
                    </a>
                    <form action="{{ route('admin.product.delete', ['id' => $product->id]) }}" method="POST" class="inline-block">
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
