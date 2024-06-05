@extends('layouts.layout')

@section('content')
    <div class="container-scroller">
        @include('dashboard.pertials.sideNav')
        <div class="container-fluid page-body-wrapper">
            <div id="theme-settings" class="settings-panel">
                <i class="settings-close mdi mdi-close"></i>
                <p class="settings-heading">SIDEBAR SKINS</p>
                <div class="sidebar-bg-options selected" id="sidebar-default-theme">
                    <div class="img-ss rounded-circle bg-light border mr-3"></div> Default
                </div>
                <div class="sidebar-bg-options" id="sidebar-dark-theme">
                    <div class="img-ss rounded-circle bg-dark border mr-3"></div> Dark
                </div>
                <p class="settings-heading mt-2">HEADER SKINS</p>
                <div class="color-tiles mx-0 px-4">
                    <div class="tiles light"></div>
                    <div class="tiles dark"></div>
                </div>
            </div>
            @include('dashboard.pertials.topNav')
            <div class="main-panel">
                <div class="content-wrapper">
                    <div class="page-header">
                        <h3 class="page-title">Sales Report</h3>
                        <nav aria-label="breadcrumb">
                            <ol class="breadcrumb">
                                <li class="breadcrumb-item"><a href="#">Reports</a></li>
                                <li class="breadcrumb-item active" aria-current="page"> Sales Report </li>
                            </ol>
                        </nav>
                    </div>
                    <div class="row">
                        <div class="col-md-8">
                            <div class="input-group mb-4">
                                <div class="input-group col-xs-12">
                                    <span class="input-group-append">
                                        <span class="file-upload-browse btn btn-primary"> From </span>
                                    </span>
                                    <input id="from" type="date" class="form-control file-upload-info" placeholder="From">
                                    <input id="to" type="date" class="form-control file-upload-info" placeholder="To">
                                    <span class="input-group-append">
                                        <span class="file-upload-browse btn btn-primary"> To </span>
                                    </span>
                                </div>
                            </div>
                            <div class="mb-4" id="error"></div>
                        </div>
                        <div class="col-md-4">
                            <div class="input-group mb-4">
                                <div class="input-group col-xs-12">
                                    <input id="singledate" type="date" name="singledate" class="form-control file-upload-info" placeholder="To">
                                    <span class="input-group-append">
                                        <span class="file-upload-browse btn btn-primary"> Date </span>
                                    </span>
                                </div>
                            </div>
                        </div>

                        <div class="col-md-4">
                            <div class="input-group mb-4">
                                <div class="input-group col-xs-12">
                                    <input id="Customer" type="text" name="Customer" class="form-control file-upload-info" placeholder="Enter Customer Mobile">
                                    <span class="input-group-append">
                                        <span class="file-upload-browse btn btn-primary"> Customer </span>
                                    </span>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="input-group mb-4">
                                <div class="input-group col-xs-12">
                                    <input id="Due" type="number" name="Due" class="form-control file-upload-info" placeholder="Enter Due">
                                    <span class="input-group-append">
                                        <span class="file-upload-browse btn btn-primary">Due</span>
                                    </span>
                                </div>
                            </div>
                        </div>

                         <div class="col-md-4">
                            <div class="input-group mb-4">
                                <div class="input-group col-xs-12">
                                <select name="payment_method"
                                    id="payment_method" class="form-control">
                                    @foreach ($getCartPaymentMethod as $getCartPaymentMethods)
                                    <option value="{{$getCartPaymentMethods->payment_method_id}}">{{$getCartPaymentMethods->payment_method}}</option>
                                    @endforeach
                                </select>
                                    {{-- <input id="payment_method" type="number" name="payment_method" class="form-control file-upload-info" placeholder="Enter Due"> --}}
                                    <span class="input-group-append">
                                        <span class="file-upload-browse btn btn-primary">Payment Method</span>
                                    </span>
                                </div>
                            </div>
                        </div>


                    </div>
                    <div id="printable" class="row a">
                        <div class="col-lg-12 grid-margin stretch-card">
                            <div class="card">
                                <div class="card-body">
                                    <div class="row">
                                        <div class="col-12 col-md-9 col-lg-9">
                                            <h4 class="card-title pb-3">SALES REPORT</h4>
                                        </div>
                                        <div class="col-12 col-md-3 col-lg-3 text-right">
                                            <button type="button"   class="print no-print btn btn-primary btn-icon-text"> Print <i class="mdi mdi-printer btn-icon-append"></i>
                                            </button>
                                        </div>
                                    </div>

                                    <table class="table table-bordered" style="display: block;
                                    max-width: -moz-fit-content;
                                    max-width: fit-content;
                                    margin: 0 auto;
                                    overflow-x: auto;
                                    white-space: nowrap;">
                                        <thead>
                                            <tr>
                                                <th>SL</th>
                                                <th>Date</th>
                                                <th>Article No</th>
                                                <th>Brand</th>
                                                <th>Payment Type</th>
                                                <th>Quantity</th>
                                                <th>Customer</th>
                                                <th>Bill Amount</th>
                                                <th>Discount</th>
                                                <th>Vat</th>
                                                <th>Payable</th>
                                                <th>Paid</th>
                                                <th>Due</th>
                                                {{-- <th>Status</th> --}}
                                            </tr>
                                        </thead>
                                        <tbody id="sales"></tbody>
                                        <tfoot id="summary"></tfoot>
                                    </table>

                                </div>
                            </div>
                        </div>
                    </div>
                    <!-- main-panel ends -->

                    {{-- New Summary
                    <div id="printable-new" class="row a">
                        <div class="col-lg-12 grid-margin stretch-card">
                            <div class="card">
                                <div class="card-body">
                                    <div class="row">
                                        <div class="col-12 col-md-9 col-lg-9">
                                            <h4 class="card-title pb-3">SALES Summary Report</h4>
                                        </div>
                                        <div class="col-12 col-md-3 col-lg-3 text-right">
                                            <button type="button"   class="print-new no-print btn btn-primary btn-icon-text"> Print <i class="mdi mdi-printer btn-icon-append"></i>
                                            </button>
                                        </div>
                                    </div>

                                    <table class="table table-bordered">
                                        <thead>
                                            <tr>
                                                <th>SL</th>
                                                <th>Date</th>
                                                <th>Product</th>
                                                <th>Quantity</th>
                                                <th>Customer</th>
                                                <th>Bill Amount</th>
                                                <th>Discount</th>
                                                <th>Vat</th>
                                                <th>Payable</th>
                                                <th>Paid</th>
                                                <th>Due</th>
                                                <th>Status</th>
                                            </tr>
                                        </thead>
                                        <tbody id="sales-summary"></tbody>
                                        <tfoot id="summary-total"></tfoot>
                                    </table>

                                </div>
                            </div>
                        </div>
                    </div> --}}


                </div>
                <!-- page-body-wrapper ends -->
            </div>
            <script src="https://cdn.jsdelivr.net/npm/jquery@3.5.1/dist/jquery.slim.min.js"
                integrity="sha384-DfXdz2htPH0lsSSs5nCTpuj/zy4C+OGpamoFVy38MVBnE+IbbVYUew+OrCXaRkfj" crossorigin="anonymous">
            </script>

            <script type="text/javascript">
                $(document).ready(function() {
                    var nodata='<tr><td colspan="10" class="text-center"><p>Select Date For Result</p></td></tr>'
                    $('#sales').append(nodata);
                    $("#error").empty();

                    $(document).on('change', '#singledate', function(e) {
                        var singledate=$(this).val();
                        console.log(singledate);
                        $('#sales').empty();
                        $('#sales-summary').empty();
                        $('#summary-total').empty();
                        $('#summary').empty();
                         $("#error").empty();
                        $.ajax({
                                url: 'single-date-sales/'+singledate,
                                type: "GET",
                                data: {
                                    "_token": "{{ csrf_token() }}",
                                },
                                dataType: "json",
                                success: function(data) {
                                    var i=0;
                                    var dshtml;
                                    console.log(data);
                                    $.each(data.singleDateSales, function(col, dsdata) {
                                        i=i+1;
                                        dshtml='<tr>'
                                            dshtml+='<td>'+ i +'</td>'
                                            dshtml+='<td>'+ dsdata.cart_date.slice(0, 10) +'</td>'
                                            dshtml+='<td>'+ dsdata.barcode +'</td>'
                                            dshtml+='<td>'+ dsdata.brand_type_name +'</td>'
                                            dshtml+='<td>'+ dsdata.payment_method +'</td>'
                                            dshtml+='<td>'+ dsdata.quantity +'</td>'
                                            dshtml+='<td>'+ dsdata.mobile_no +'</td>'
                                            dshtml+='<td>'+ dsdata.total_cart_amount +'</td>'
                                            dshtml+='<td>'+ dsdata.total_discount +'</td>'
                                            dshtml+='<td>'+ dsdata.vat_amount +'</td>'
                                            dshtml+='<td>'+ dsdata.total_payable_amount +'</td>'
                                            dshtml+='<td>'+ dsdata.paid_amount +'</td>'
                                            dshtml+='<td>'+ dsdata.due_amount +'</td>'
                                            // dshtml+='<td>'+ dsdata.cart_status +'</td>'
                                        dshtml+='</tr>';
                                        $('#sales').append(dshtml);
                                    });
                                    i = 0;
                                    $.each(data.singleDateSales, function(col, dsdata) {
                                        i=i+1;
                                        dshtml='<tr>'
                                            dshtml+='<td>'+ i +'</td>'
                                            dshtml+='<td>'+ dsdata.cart_date.slice(0, 10) +'</td>'
                                            dshtml+='<td>'+ dsdata.product_material_name +'</td>'
                                            dshtml+='<td>'+ dsdata.quantity +'</td>'
                                            dshtml+='<td>'+ dsdata.mobile_no +'</td>'
                                            dshtml+='<td>'+ dsdata.total_cart_amount +'</td>'
                                            dshtml+='<td>'+ dsdata.total_discount +'</td>'
                                            dshtml+='<td>'+ dsdata.vat_amount +'</td>'
                                            dshtml+='<td>'+ dsdata.total_payable_amount +'</td>'
                                            dshtml+='<td>'+ dsdata.paid_amount +'</td>'
                                            dshtml+='<td>'+ dsdata.due_amount +'</td>'
                                            dshtml+='<td>'+ dsdata.cart_status +'</td>'
                                        dshtml+='</tr>';
                                        $('#sales-summary').append(dshtml);
                                    });

                                    var summary_data='<tr class="h4 bg-secondary">'
                                            summary_data+='<td colspan="2">Summary</td>'
                                            summary_data+='<td></td>'
                                            summary_data+='<td></td>'
                                            summary_data+='<td></td>'
                                            summary_data+='<td>'+ data.summary.totalQuantity +'</td>'
                                            summary_data+='<td></td>'


                                            summary_data+='<td>'+ data.summary.invoice_amount +'</td>'
                                            summary_data+='<td>'+ data.summary.discount +'</td>'
                                            summary_data+='<td>'+ data.summary.vat +'</td>'
                                            summary_data+='<td>'+ data.summary.payable +'</td>'
                                            summary_data+='<td>'+ data.summary.paid_amount +'</td>'
                                            summary_data+='<td>'+ data.summary.due +'</td>'
                                            summary_data+='<td></td>'
                                        summary_data+='</tr>';
                                    $('#summary').append(summary_data);
                                //    var summary_data='<tr class="h4 bg-secondary">'
                                //             summary_data+='<td colspan="3">Summary</td>'
                                //             summary_data+='<td>'+ data.summary.totalQuantity +'</td>'
                                //             summary_data+='<td></td>'
                                //             summary_data+='<td>'+ data.summary.invoice_amount +'</td>'
                                //             summary_data+='<td>'+ data.summary.discount +'</td>'
                                //             summary_data+='<td>'+ data.summary.vat +'</td>'
                                //             summary_data+='<td>'+ data.summary.payable +'</td>'
                                //             summary_data+='<td>'+ data.summary.paid +'</td>'
                                //             summary_data+='<td>'+ data.summary.due +'</td>'
                                //             summary_data+='<td></td>'
                                //         summary_data+='</tr>';
                                //     $('#summary-total').append(summary_data);
                                }

                            });
                    });




                    $(document).on('change', '#from', function(e) {
                        $('#sales').empty();
                        $('#summary').empty();
                        var from=$("#from").val();
                        var to=$("#to").val();
                        console.log(to);
                        if(!to){
                            $("#error").append('<div class="alert alert-danger"> To Date Is Required</div>');
                            return;
                        }
                        if(!from){
                            $("#error").append('<div class="alert alert-danger"> From Date Is Required</div>');
                            return;
                        }

                        $.ajax({
                                url: 'multi-date-sales/'+from+'/'+to,
                                type: "GET",
                                data: {
                                    "_token": "{{ csrf_token() }}",
                                },
                                dataType: "json",
                                success: function(data) {
                                    console.log(data);
                                    var i=0;
                                    var dshtml;
                                    console.log(data);
                                    $.each(data.singleDateSales, function(col, dsdata) {
                                        i=i+1;
                                        dshtml='<tr>'
                                            dshtml+='<td>'+ i +'</td>'
                                            dshtml+='<td>'+ dsdata.cart_date.slice(0, 10) +'</td>'
                                            dshtml+='<td>'+ dsdata.barcode +'</td>'
                                            dshtml+='<td>'+ dsdata.brand_type_name +'</td>'
                                            dshtml+='<td>'+ dsdata.payment_method +'</td>'
                                            dshtml+='<td>'+ dsdata.quantity +'</td>'
                                            dshtml+='<td>'+ dsdata.mobile_no +'</td>'
                                            dshtml+='<td>'+ dsdata.total_cart_amount +'</td>'
                                            dshtml+='<td>'+ dsdata.total_discount +'</td>'
                                            dshtml+='<td>'+ dsdata.vat_amount +'</td>'
                                            dshtml+='<td>'+ dsdata.total_payable_amount +'</td>'
                                            dshtml+='<td>'+ dsdata.paid_amount +'</td>'
                                            dshtml+='<td>'+ dsdata.due_amount +'</td>'
                                            // dshtml+='<td>'+ dsdata.cart_status +'</td>'
                                        dshtml+='</tr>';
                                        $('#sales').append(dshtml);
                                    });

                                    var summary_data='<tr class="h4 bg-secondary">'
                                            summary_data+='<td colspan="2">Summary</td>'
                                            summary_data+='<td></td>'
                                            summary_data+='<td></td>'
                                            summary_data+='<td></td>'
                                            summary_data+='<td>'+ data.summary.totalQuantity +'</td>'
                                            summary_data+='<td></td>'


                                            summary_data+='<td>'+ data.summary.invoice_amount +'</td>'
                                            summary_data+='<td>'+ data.summary.discount +'</td>'
                                            summary_data+='<td>'+ data.summary.vat +'</td>'
                                            summary_data+='<td>'+ data.summary.payable +'</td>'
                                            summary_data+='<td>'+ data.summary.paid_amount +'</td>'
                                            summary_data+='<td>'+ data.summary.due +'</td>'
                                            summary_data+='<td></td>'
                                        summary_data+='</tr>';
                                    $('#summary').append(summary_data);

                                }

                            });
                    });
                    $(document).on('change', '#to', function(e) {
                        $('#sales').empty();
                        $('#summary').empty();
                        var from=$("#from").val();
                        var to=$("#to").val();
                        if(!to){
                            $("#error").append('<div class="alert alert-danger"> To Date Is Required</div>');
                            return;
                        }
                        if(!from){
                            $("#error").append('<div class="alert alert-danger"> From Date Is Required</div>');
                            return;
                        }

                        $.ajax({
                                url: 'multi-date-sales/'+from+'/'+to,
                                type: "GET",
                                data: {
                                    "_token": "{{ csrf_token() }}",
                                },
                                dataType: "json",
                                success: function(data) {
                                    console.log(data);
                                    var i=0;
                                    var dshtml;
                                    console.log(data);
                                    $.each(data.singleDateSales, function(col, dsdata) {
                                        i=i+1;
                                        dshtml='<tr>'
                                            dshtml+='<td>'+ i +'</td>'
                                            dshtml+='<td>'+ dsdata.cart_date.slice(0, 10) +'</td>'
                                            dshtml+='<td>'+ dsdata.barcode +'</td>'
                                            dshtml+='<td>'+ dsdata.brand_type_name +'</td>'
                                            dshtml+='<td>'+ dsdata.payment_method +'</td>'
                                            dshtml+='<td>'+ dsdata.quantity +'</td>'
                                            dshtml+='<td>'+ dsdata.mobile_no +'</td>'
                                            dshtml+='<td>'+ dsdata.total_cart_amount +'</td>'
                                            dshtml+='<td>'+ dsdata.total_discount +'</td>'
                                            dshtml+='<td>'+ dsdata.vat_amount +'</td>'
                                            dshtml+='<td>'+ dsdata.total_payable_amount +'</td>'
                                            dshtml+='<td>'+ dsdata.paid_amount +'</td>'
                                            dshtml+='<td>'+ dsdata.due_amount +'</td>'
                                            // dshtml+='<td>'+ dsdata.cart_status +'</td>'
                                        dshtml+='</tr>';
                                        $('#sales').append(dshtml);
                                    });

                                    var summary_data='<tr class="h4 bg-secondary">'
                                            summary_data+='<td colspan="2">Summary</td>'
                                            summary_data+='<td></td>'
                                            summary_data+='<td></td>'
                                            summary_data+='<td></td>'
                                            summary_data+='<td>'+ data.summary.totalQuantity +'</td>'
                                            summary_data+='<td></td>'


                                            summary_data+='<td>'+ data.summary.invoice_amount +'</td>'
                                            summary_data+='<td>'+ data.summary.discount +'</td>'
                                            summary_data+='<td>'+ data.summary.vat +'</td>'
                                            summary_data+='<td>'+ data.summary.payable +'</td>'
                                            summary_data+='<td>'+ data.summary.paid_amount +'</td>'
                                            summary_data+='<td>'+ data.summary.due +'</td>'
                                            summary_data+='<td></td>'
                                        summary_data+='</tr>';
                                    $('#summary').append(summary_data);

                                }

                            });
                    });


                    $(document).on('change','#Customer', function (event) {
                        var customerdata =  $(this).val();
                        // console.log(data);
                        $('#sales').empty();
                        $('#summary').empty();
                        $("#error").empty();
                        $.ajax({
                                url: 'customer-mobile/'+customerdata,
                                type: "GET",
                                data: {
                                    "_token": "{{ csrf_token() }}",
                                },
                                dataType: "json",
                                success: function(data) {
                                    var i=0;
                                    var dshtml;
                                    console.log(data);
                                    $.each(data.singleDateSales, function(col, dsdata) {
                                        i=i+1;
                                        dshtml='<tr>'
                                            dshtml+='<td>'+ i +'</td>'
                                            dshtml+='<td>'+ dsdata.cart_date.slice(0, 10) +'</td>'
                                            dshtml+='<td>'+ dsdata.barcode +'</td>'
                                            dshtml+='<td>'+ dsdata.brand_type_name +'</td>'
                                            dshtml+='<td>'+ dsdata.payment_method +'</td>'
                                            dshtml+='<td>'+ dsdata.quantity +'</td>'
                                            dshtml+='<td>'+ dsdata.mobile_no +'</td>'
                                            dshtml+='<td>'+ dsdata.total_cart_amount +'</td>'
                                            dshtml+='<td>'+ dsdata.total_discount +'</td>'
                                            dshtml+='<td>'+ dsdata.vat_amount +'</td>'
                                            dshtml+='<td>'+ dsdata.total_payable_amount +'</td>'
                                            dshtml+='<td>'+ dsdata.paid_amount +'</td>'
                                            dshtml+='<td>'+ dsdata.due_amount +'</td>'
                                            // dshtml+='<td>'+ dsdata.cart_status +'</td>'
                                        dshtml+='</tr>';
                                        $('#sales').append(dshtml);
                                    });

                                    var summary_data='<tr class="h4 bg-secondary">'
                                            summary_data+='<td colspan="2">Summary</td>'
                                            summary_data+='<td></td>'
                                            summary_data+='<td></td>'
                                            summary_data+='<td></td>'
                                            summary_data+='<td>'+ data.summary.totalQuantity +'</td>'
                                            summary_data+='<td></td>'


                                            summary_data+='<td>'+ data.summary.invoice_amount +'</td>'
                                            summary_data+='<td>'+ data.summary.discount +'</td>'
                                            summary_data+='<td>'+ data.summary.vat +'</td>'
                                            summary_data+='<td>'+ data.summary.payable +'</td>'
                                            summary_data+='<td>'+ data.summary.paid_amount +'</td>'
                                            summary_data+='<td>'+ data.summary.due +'</td>'
                                            summary_data+='<td></td>'
                                        summary_data+='</tr>';
                                    $('#summary').append(summary_data);

                                }

                            });
                    });


                    // Payment Method

                    $(document).on('change','#payment_method', function (event) {
                        var payment_method =  $(this).val();
                        // console.log(data);
                        $('#sales').empty();
                        $('#summary').empty();
                        $("#error").empty();

                        var from=$("#from").val();
                        var to=$("#to").val();
                        var singledate=$('#singledate').val();
                        // console.log(singledate);
                        $.ajax({
                                url: 'payment-method/'+payment_method,
                                type: "GET",
                                data: {
                                    "_token": "{{ csrf_token() }}",
                                    "from":from,
                                    "to":to,
                                    "singledate":singledate
                                },
                                dataType: "json",
                                success: function(data) {
                                    var i=0;
                                    var dshtml;
                                    console.log(data);
                                    $.each(data.singleDateSales, function(col, dsdata) {
                                        i=i+1;
                                        dshtml='<tr>'
                                            dshtml+='<td>'+ i +'</td>'
                                            dshtml+='<td>'+ dsdata.cart_date.slice(0, 10) +'</td>'
                                            dshtml+='<td>'+ dsdata.barcode +'</td>'
                                            dshtml+='<td>'+ dsdata.brand_type_name +'</td>'
                                            dshtml+='<td>'+ dsdata.payment_method +'</td>'
                                            dshtml+='<td>'+ dsdata.quantity +'</td>'
                                            dshtml+='<td>'+ dsdata.mobile_no +'</td>'
                                            dshtml+='<td>'+ dsdata.total_cart_amount +'</td>'
                                            dshtml+='<td>'+ dsdata.total_discount +'</td>'
                                            dshtml+='<td>'+ dsdata.vat_amount +'</td>'
                                            dshtml+='<td>'+ dsdata.total_payable_amount +'</td>'
                                            dshtml+='<td>'+ dsdata.paid_amount +'</td>'
                                            dshtml+='<td>'+ dsdata.due_amount +'</td>'
                                            // dshtml+='<td>'+ dsdata.cart_status +'</td>'
                                        dshtml+='</tr>';
                                        $('#sales').append(dshtml);
                                    });

                                    var summary_data='<tr class="h4 bg-secondary">'
                                            summary_data+='<td colspan="2">Summary</td>'
                                            summary_data+='<td></td>'
                                            summary_data+='<td></td>'
                                            summary_data+='<td></td>'
                                            summary_data+='<td>'+ data.summary.totalQuantity +'</td>'
                                            summary_data+='<td></td>'


                                            summary_data+='<td>'+ data.summary.invoice_amount +'</td>'
                                            summary_data+='<td>'+ data.summary.discount +'</td>'
                                            summary_data+='<td>'+ data.summary.vat +'</td>'
                                            summary_data+='<td>'+ data.summary.payable +'</td>'
                                            summary_data+='<td>'+ data.summary.paid_amount +'</td>'
                                            summary_data+='<td>'+ data.summary.due +'</td>'
                                            summary_data+='<td></td>'
                                        summary_data+='</tr>';
                                    $('#summary').append(summary_data);

                                }

                            });
                    });

                    $(document).on('change','#Due', function (event) {
                        var due =  $(this).val();
                        console.log(due);
                        $('#sales').empty();
                        $('#summary').empty();
                        $("#error").empty();
                        $.ajax({
                                url: 'due-list/'+due,
                                type: "GET",
                                data: {
                                    "_token": "{{ csrf_token() }}",
                                },
                                dataType: "json",
                                success: function(data) {
                                    var i=0;
                                    var dshtml;
                                    console.log(data);
                                    $.each(data.singleDateSales, function(col, dsdata) {
                                        i=i+1;
                                        dshtml='<tr>'
                                            dshtml+='<td>'+ i +'</td>'
                                            dshtml+='<td>'+ dsdata.cart_date.slice(0, 10) +'</td>'
                                            dshtml+='<td>'+ dsdata.barcode +'</td>'
                                            dshtml+='<td>'+ dsdata.brand_type_name +'</td>'
                                            dshtml+='<td>'+ dsdata.payment_method +'</td>'
                                            dshtml+='<td>'+ dsdata.quantity +'</td>'
                                            dshtml+='<td>'+ dsdata.mobile_no +'</td>'
                                            dshtml+='<td>'+ dsdata.total_cart_amount +'</td>'
                                            dshtml+='<td>'+ dsdata.total_discount +'</td>'
                                            dshtml+='<td>'+ dsdata.vat_amount +'</td>'
                                            dshtml+='<td>'+ dsdata.total_payable_amount +'</td>'
                                            dshtml+='<td>'+ dsdata.paid_amount +'</td>'
                                            dshtml+='<td>'+ dsdata.due_amount +'</td>'
                                            // dshtml+='<td>'+ dsdata.cart_status +'</td>'
                                        dshtml+='</tr>';
                                        $('#sales').append(dshtml);
                                    });

                                    var summary_data='<tr class="h4 bg-secondary">'
                                            summary_data+='<td colspan="2">Summary</td>'
                                            summary_data+='<td></td>'
                                            summary_data+='<td></td>'
                                            summary_data+='<td></td>'
                                            summary_data+='<td>'+ data.summary.totalQuantity +'</td>'
                                            summary_data+='<td></td>'


                                            summary_data+='<td>'+ data.summary.invoice_amount +'</td>'
                                            summary_data+='<td>'+ data.summary.discount +'</td>'
                                            summary_data+='<td>'+ data.summary.vat +'</td>'
                                            summary_data+='<td>'+ data.summary.payable +'</td>'
                                            summary_data+='<td>'+ data.summary.paid_amount +'</td>'
                                            summary_data+='<td>'+ data.summary.due +'</td>'
                                            summary_data+='<td></td>'
                                        summary_data+='</tr>';
                                    $('#summary').append(summary_data);

                                }

                            });


                    });

                    //print Sales  Report

                    $("#printable").find('.print').on('click', function() {
                    $.print("#printable");
                        });
                    // Print New Sales Report

                    $("#printable-new").find('.print-new').on('click', function() {
                    $.print("#printable-new");
                        });

                });
            </script>

@endsection
