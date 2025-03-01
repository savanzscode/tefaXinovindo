@extends('layouts.app')

@section('content')
<style>
    .pt-90 {
      padding-top: 90px !important;
    }

    .pr-6px {
      padding-right: 6px;
      text-transform: uppercase;
    }

    .my-account .page-title {
      font-size: 1.5rem;
      font-weight: 700;
      text-transform: uppercase;
      margin-bottom: 40px;
      border-bottom: 1px solid;
      padding-bottom: 13px;
    }

    .my-account .wg-box {
      display: -webkit-box;
      display: -moz-box;
      display: -ms-flexbox;
      display: -webkit-flex;
      display: flex;
      padding: 24px;
      flex-direction: column;
      gap: 24px;
      border-radius: 12px;
      background: var(--White);
      box-shadow: 0px 4px 24px 2px rgba(20, 25, 38, 0.05);
    }

    .bg-success {
      background-color: #40c710 !important;
    }

    .bg-danger {
      background-color: #f44032 !important;
    }

    .bg-warning {
      background-color: #f5d700 !important;
      color: #000;
    }

    .table-transaction>tbody>tr:nth-of-type(odd) {
      --bs-table-accent-bg: #fff !important;

    }

    .table-transaction th,
    .table-transaction td {
      padding: 0.625rem 1.5rem .25rem !important;
      color: #000 !important;
    }

    .table> :not(caption)>tr>th {
      padding: 0.625rem 1.5rem .25rem !important;
      background-color: #6a6e51 !important;
    }

    .table-bordered>:not(caption)>*>* {
      border-width: inherit;
      line-height: 32px;
      font-size: 14px;
      border: 1px solid #e1e1e1;
      vertical-align: middle;
    }

    .table-striped .image {
      display: flex;
      align-items: center;
      justify-content: center;
      width: 50px;
      height: 50px;
      flex-shrink: 0;
      border-radius: 10px;
      overflow: hidden;
    }

    .table-striped td:nth-child(1) {
      min-width: 250px;
      padding-bottom: 7px;
    }

    .pname {
      display: flex;
      gap: 13px;
    }

    .table-bordered> :not(caption)>tr>th,
    .table-bordered> :not(caption)>tr>td {
      border-width: 1px 1px;
      border-color: #6a6e51;
    }
  </style>
<main class="pt-90">
    <div class="mb-4 pb-4"></div>
    <section class="my-account container">
        <h2 class="page-title">Order's Details</h2>
        <div class="row">
            <div class="col-lg-2">
                @include('user.account-nav')
            </div>

            <div class="col-lg-10">
                @if(Session::has('status'))
                    <p class="alert alert-success">{{Session::get('status')}}</p>
                @endif
                <div class="wg-box mt-5 mb-5">
                    <div class="row">
                        <div class="col-6">
                            <h5>Ordered Details</h5>
                        </div>
                        <div class="col-6 text-right">
                            <a class="btn btn-sm btn-danger" href="{{route('user.account.orders')}}">Back</a>
                        </div>
                    </div>
                    <div class="table-responsive">
                        <table class="table table-striped table-bordered table-transaction">
                            <tr>
                                <th>Order No</th>
                                <td>{{"1" . str_pad($transaction->order->id,4,"0",STR_PAD_LEFT)}}</td>
                                <th>Mobile</th>
                                <td>{{$transaction->order->phone}}</td>
                                <th>Pin/Zip Code</th>
                                <td>{{$transaction->order->zip}}</td>
                            </tr>
                            <tr>
                                <th>Order Date</th>
                                <td>{{$transaction->order->created_at}}</td>
                                <th>Delivered Date</th>
                                <td>{{$transaction->order->delivered_date}}</td>
                                <th>Canceled Date</th>
                                <td>{{$transaction->order->canceled_date}}</td>
                            </tr>
                            <tr>
                                <th>Order Status</th>
                                <td colspan="5">
                                    @if($transaction->order->status=='delivered')
                                        <span class="badge bg-success">Delivered</span>
                                    @elseif($transaction->order->status=='canceled')
                                        <span class="badge bg-danger">Canceled</span>
                                    @else
                                        <span class="badge bg-warning">Ordered</span>
                                    @endif
                                </td>
                            </tr>
                        </table>
                    </div>
                </div>
                <div class="wg-box wg-table table-all-user">
                    <div class="row">
                        <div class="col-6">
                            <h5>Ordered Items</h5>
                        </div>
                        <div class="col-6 text-right">

                        </div>
                    </div>
                    <div class="table-responsive">
                        <table class="table table-striped table-bordered">
                            <thead>
                                <tr>
                                    <th>Name</th>
                                    <th class="text-center">Price</th>
                                    <th class="text-center">Quantity</th>
                                    <th class="text-center">SKU</th>
                                    <th class="text-center">Category</th>
                                    <th class="text-center">Brand</th>
                                    <th class="text-center">Options</th>
                                    <th class="text-center">Return Status</th>
                                    <th class="text-center">Action</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach ($orderItems as $orderitem)
                                <tr>

                                    <td class="pname">
                                        <div class="image">
                                            <img src="{{asset('uploads/products/thumbnails')}}/{{$orderitem->product->image}}" alt="" class="image">
                                        </div>
                                        <div class="name">
                                            <a href="{{route('shop.product.details',["product_slug"=>$orderitem->product->slug])}}" target="_blank" class="body-title-2">{{$orderitem->product->name}}</a>
                                        </div>
                                    </td>
                                    <td class="text-center">${{$orderitem->price}}</td>
                                    <td class="text-center">{{$orderitem->quantity}}</td>
                                    <td class="text-center">{{$orderitem->product->SKU}}</td>
                                    <td class="text-center">{{$orderitem->product->category->name}}</td>
                                    <td class="text-center">{{$orderitem->product->brand->name}}</td>
                                    <td class="text-center">{{$orderitem->options}}</td>
                                    <td class="text-center">{{$orderitem->rstatus == 0 ? "No":"Yes"}}</td>
                                    <td class="text-center">
                                        <a href="{{route('shop.product.details',["product_slug"=>$orderitem->product->slug])}}" target="_blank">
                                            <div class="list-icon-function view-icon">
                                                <div class="item eye">
                                                    <i class="fa fa-eye"></i>
                                                </div>
                                            </div>
                                        </a>
                                    </td>
                                </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>
                <div class="divider"></div>
                <div class="flex items-center justify-between flex-wrap gap10 wgp-pagination">
                    {{$orderItems->links('pagination::bootstrap-5')}}
                </div>

                <div class="wg-box mt-5">
                    <h5>Shipping Address</h5>
                    <div class="my-account__address-item col-md-6">
                        <div class="my-account__address-item__detail">
                            <p>{{$transaction->order->name}}</p>
                            <p>{{$transaction->order->address}}</p>
                            <p>{{$transaction->order->locality}}</p>
                            <p>{{$transaction->order->city}}, {{$transaction->order->country}}</p>
                            <p>{{$transaction->order->landmark}}</p>
                            <p>{{$transaction->order->zip}}</p>
                            <br />
                            <p>Mobile : {{$transaction->order->phone}}</p>
                        </div>
                    </div>
                </div>
                <div class="wg-box mt-5">
                    <h5>Transactions</h5>
                    <div class="table-responsive">
                    <table class="table table-striped table-bordered table-transaction">
                        <tr>
                            <th>Subtotal</th>
                            <td>${{$transaction->order->subtotal}}</td>
                            <th>Tax</th>
                            <td>${{$transaction->order->tax}}</td>
                            <th>Discount</th>
                            <td>${{$transaction->order->discount}}</td>
                        </tr>
                        <tr>
                            <th>Total</th>
                            <td>${{$transaction->order->total}}</td>
                            <th>Payment Mode</th>
                            <td>{{$transaction->mode}}</td>
                            <th>Status</th>
                            <td>
                                @if($transaction->status=='approved')
                                    <span class="badge bg-success">Approved</span>
                                @elseif($transaction->status=='declined')
                                    <span class="badge bg-danger">Declined</span>
                                @elseif($transaction->status=='refunded')
                                    <span class="badge bg-secondary">Refunded</span>
                                @else
                                    <span class="badge bg-warning">Pending</span>
                                @endif
                            </td>
                        </tr>
                        <tr>
                            <th>Payment status</th>
                            <td colspan="1">
                                @if($order->payment_status=='sudah_dibayar')
                                    <span class="badge bg-success">di bayar</span>
                                @elseif($order->payment_status=='pending')
                                    <span class="badge bg-warning">pending</span>
                                @elseif($order->payment_status=='belum_dibayar')
                                    <span class="badge bg-danger">belum di bayar</span>
                                @endif
                            </td>
                        </tr>
                    </table>
                    </div>
                    <div class="wg-box mt-5 text-right">
                        <form action="{{route('user.account_cancel_order')}}" method="POST">
                            @csrf
                            @method("PUT")
                            <input type="hidden" name="order_id" value="{{$order->id}}" />
                            <button type="submit" class="btn btn-danger cancel-order">Cancel Order</button>
                        </form>

                            @if($order->payment_status == 'belum_dibayar')
                                <button type="button" class="btn btn-primary pay-now mt-2"
                                    data-order-id="{{ $order->id }}"
                                     id="pay-button">
                                     Bayar Sekarang
                                </button>
                            @endif
                    </div>
                </div>
            </div>

        </div>
    </section>
</main>
@endsection

@push('script')
    <script>
        $(function(){
            $(".cancel-order").on('click',function(e){
                e.preventDefault();
                var selectedForm = $(this).closest('form');
                swal({
                    title: "Are you sure?",
                    text: "You want to cancle this order?",
                    type: "warning",
                    buttons: ["No!", "Yes!"],
                    confirmButtonColor: '#dc3545'
                }).then(function (result) {
                    if (result) {
                        selectedForm.submit();
                    }
                });
            });
        });
    </script>
    <script src="https://app.sandbox.midtrans.com/snap/snap.js" data-client-key="{{ env('MIDTRANS_CLIENT_KEY') }}"></script>
    <script>
        document.getElementById('pay-button').onclick = function(event) {
            let orderId = event.target.getAttribute('data-order-id');

            fetch(`/get-snap-token/${orderId}`)
                .then(response => response.json())
                .then(data => {
                    if (data.snap_token) {
                        snap.pay(data.snap_token, {
                            onSuccess: function(result) {
                                updatePaymentStatus(orderId, result);
                            },
                            onPending: function(result) {
                                alert("Pembayaran pending! Silakan cek kembali nanti.");
                            },
                            onError: function(result) {
                                alert("Pembayaran gagal! Silakan coba lagi.");
                            }
                        });
                    } else {
                        alert("Gagal mendapatkan token pembayaran!");
                    }
                })
                .catch(error => console.error('Error:', error));
        };

        function updatePaymentStatus(orderId, result) {
            fetch(`/update-payment-status/${orderId}`, {
                method: "POST",
                headers: {
                    "Content-Type": "application/json",
                    "X-CSRF-TOKEN": "{{ csrf_token() }}"
                },
                body: JSON.stringify({ transaction_status: result.transaction_status })
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    alert("Pembayaran berhasil! Status telah diperbarui.");
                    location.reload();
                } else {
                    alert("Gagal memperbarui status pembayaran!");
                }
            })
            .catch(error => console.error('Error:', error));
        }
    </script>
@endpush

