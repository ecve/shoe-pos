@php
    use Carbon\Carbon;
    $user_id = session()->get('LoggedUser');
    $user_data = \App\Models\BackofficeLogin::join('backoffice_role', 'backoffice_role.role_id', '=', 'backoffice_login.role_id')
        ->where('login_id', $user_id)
        ->first();
@endphp

@extends('layouts.layout')

@section('content')
    @php
        $all_cart_info = \App\Models\CartInformtion::join('backoffice_login as usr', 'usr.login_id', '=', 'cart_informtion.created_by')
            ->join('backoffice_login as wtr', 'wtr.login_id', '=', 'cart_informtion.waiter_id')
            ->select('cart_informtion.*', 'usr.full_name as created_by_name', 'wtr.full_name as waiter_name')
            ->get();
    @endphp

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
                <div class="content-wrapper pb-0">
                    <div class="page-header flex-wrap">
                        <h3 class="mb-0"> Hi, welcome back!</h3>
                    </div>
                    @php
                        $all_sell = \App\Models\CartInformtion::join('consumer_login', 'consumer_login.login_id', '=', 'cart_informtion.consumer_id')
                            ->where('cart_informtion.cart_date', 'like', '%' . Carbon::today()->format('Y-m-d') . '%')
                            ->select('cart_informtion.*', 'consumer_login.mobile_no')
                            ->get();

                        $all_expense = \App\Models\ExpenseDetail::where('expense_details.date', 'like', '%' . Carbon::today()->format('Y-m-d') . '%')
                            ->select('expense_details.*')
                            ->get();
                    @endphp

                    @php
                        $singleDateSales = \App\Models\CartPaymentInformation::join('cart_informtion', 'cart_informtion.cart_id', '=', 'cart_payment_information.cart_id')
                ->join('consumer_login', 'consumer_login.login_id', '=', 'cart_informtion.consumer_id')
                ->join('cart_items', 'cart_items.cart_id', '=', 'cart_informtion.cart_id')
                ->join('product_materials', 'product_materials.product_material_id', '=', 'cart_items.product_id')
                ->join('foot_ware_categories', 'product_materials.foot_ware_categories_id', '=', 'foot_ware_categories.foot_ware_categories_id')
                ->join('types', 'product_materials.type_id', '=', 'types.type_id')
                ->join('material_types', 'product_materials.material_type_id', '=', 'material_types.material_type_id')
                ->join('brand_types', 'product_materials.brand_type_id', '=', 'brand_types.brand_type_id')
                ->join('sizes', 'sizes.size_id', '=', 'cart_items.size_id')
                ->join('colors', 'colors.colors_id', '=', 'cart_items.colors_id')
                ->join('cart_payment_methods', 'cart_payment_methods.payment_method_id', '=', 'cart_payment_information.payment_method_id')
                            ->where('cart_informtion.cart_date', 'like', '%' . Carbon::today()->format('Y-m-d') . '%')
                            ->select('cart_informtion.*', 'cart_payment_information.*', 'cart_items.barcode', 'brand_types.brand_type_name', 'cart_items.quantity', 'consumer_login.mobile_no', 'cart_payment_methods.payment_method')
                ->get();
                $getId = [];
            foreach ($singleDateSales as $singleDateSale) {
                $getId[] = $singleDateSale->cart_id;
            }
            $getId = array_unique($getId);
            // dd($getId);

            $matchingCarts = [];

            foreach ($getId as $cartId) {
                foreach ($singleDateSales as $cart) {
                    // Check if the current cart matches the given cartId
                    if ($cart["cart_id"] === $cartId) {
                        $isMatched = false;

                        // Check if the cart_id and payment_method combination is already in $matchingCarts
                        foreach ($matchingCarts as $matchingCart) {
                            if ($matchingCart["cart_id"] === $cart["cart_id"] && $matchingCart["payment_method"] === $cart["payment_method"]) {
                                $isMatched = true;
                                break; // No need to continue checking once a match is found
                            }
                        }

                        // If not matched, insert into $matchingCarts
                        if (!$isMatched) {
                            $matchingCarts[] = $cart;
                        }
                    }
                }
            }

            // dd($matchingCarts);
            $serializedCarts = [];
            foreach ($matchingCarts as $cart) {
                $serializedCarts[] = serialize($cart);
            }
            $uniqueSerializedCarts = array_unique($serializedCarts);
            $uniqueCarts = array_map("unserialize", $uniqueSerializedCarts);


            $getSpecificCardId = \App\Models\CartInformtion::whereIn('cart_id', $getId)->get();
            $getSpecificItem = \App\Models\CartItem::whereIn('cart_id', $getId)->get();

            $quantityArr = [];
            foreach ($getSpecificItem as $item) {
                if (!isset($quantityArr[$item->cart_id])) $quantityArr[$item->cart_id] = $item->quantity;
                else $quantityArr[$item->cart_id] += $item->quantity;
            }
            $mergedCarts = [];
            foreach ($uniqueCarts as $cart) {
                $cartId = $cart['cart_id'];
                $paidAmount = $cart['paid_amount'];
                $paymentMethod = $cart['payment_method'];
                // $quantity = $cart['quantity'];
                $barcode = $cart['barcode'];

                if (!isset($mergedCarts[$cartId])) {
                    $mergedCarts[$cartId] = $cart;
                } else {
                    $mergedCarts[$cartId]['paid_amount'] = "{$mergedCarts[$cartId]['paid_amount']} + {$paidAmount}";
                    $mergedCarts[$cartId]['payment_method'] = $mergedCarts[$cartId]['payment_method'] . " + " . $paymentMethod;
                    // $mergedCarts[$cartId]['barcode'] = $mergedCarts[$cartId]['barcode']. " + " . $barcode;
                    // $mergedCarts[$cartId]['quantity'] += $quantity;
                }
            }

            // dd($mergedCarts);
            $mergedCarts = array_values($mergedCarts);
            $singleDateSales = [];
            foreach ($mergedCarts as $singleCart) {
                $singleCart['quantity'] = $quantityArr[$singleCart->cart_id];
                $singleDateSales[] = $singleCart;
            }
            $discountAmount = 0;
            $paidAmount = 0;
            foreach ($singleDateSales as $single) {
                $discountAmount += floatval($single->discount_amount) ;
                $paidAmount +=  floatval($single->final_total_amount);
            }


            $total_orders = count($singleDateSales);
            $invoice_amount = $getSpecificCardId->sum('total_cart_amount');

            $vat = $getSpecificCardId->sum('vat_amount');
            $payable = $getSpecificCardId->sum('total_payable_amount');
            $due = $getSpecificCardId->sum('due_amount');
            $totalQuantity = $getSpecificItem->sum('quantity');





            $getOnlyExchnageValue = \App\Models\CartPaymentInformation::join('cart_informtion', 'cart_informtion.cart_id', '=', 'cart_payment_information.cart_id')
            ->join('cart_payment_methods', 'cart_payment_methods.payment_method_id', '=', 'cart_payment_information.payment_method_id')
            ->where('cart_informtion.cart_date', 'like', '%' . Carbon::today()->format('Y-m-d') . '%')
            ->where('cart_payment_information.payment_method_id', '=', 9)
            ->select('cart_payment_information.*')
            ->get();
            $getextotal = $getOnlyExchnageValue->sum('paid_amount');

            // $getPaidTotal = $total_sum_paid->sum('total_payable');
            // dd($paidAmount);
            $paid_amount = $paidAmount- $getextotal;

                        // $paid_amount = $singleDateSales->sum('total_payable');
                    @endphp

                    @if ($user_data->role_id == 1 || $user_data->role_id == 2)
                        <div class="row mt-5">
                            <div class="col-xl-3 col-md-3 stretch-card grid-margin grid-margin-sm-0 pb-sm-3">
                                <div class="card bg-warning">
                                    <div class="card-body px-3 py-4">
                                        <div class="d-flex justify-content-between align-items-start">
                                            <div class="color-card">
                                                <p class="mb-0 color-card-head">Today Sales</p>
                                                <h2 class="text-white">Tk.
                                                    <br> {{ $paid_amount }}<span class="h5">.00</span>
                                                </h2>
                                            </div>
                                            <i class="card-icon-indicator mdi mdi-basket bg-inverse-icon-warning"></i>
                                        </div>

                                        <!-- <div class="d-flex justify-content-between align-items-start">
                                        <div class="color-card">

                                            <h4 class="text-white"> Count <br>
                                                {{ $all_sell->count('final_total_amount') }} Orders
                                            </h4>
                                        </div>
                                    </div> -->
                                        <hr>
                                        <a class="btn text-light float-right"
                                            href="{{ route('backoffice.all_sales_report') }}">
                                            view report
                                        </a>
                                    </div>
                                </div>
                            </div>
                            <div
                                class="col-xl-3 col-md-3 stretch-card grid-margin grid-margin-sm-0 pb-sm-3 pb-lg-0 pb-xl-3">
                                <div class="card bg-primary">
                                    <div class="card-body px-3 py-4">
                                        <div class="d-flex justify-content-between align-items-start">
                                            <div class="color-card">
                                                <p class="mb-0 color-card-head">Orders</p>
                                                <h2 class="text-white"> {{ $all_sell->count('final_total_amount') }}
                                                </h2>
                                            </div>
                                            <i
                                                class="card-icon-indicator mdi mdi-briefcase-outline bg-inverse-icon-primary"></i>
                                        </div>
                                        <h6 class="text-white">Today</h6>
                                        <hr>
                                        <a class="btn text-light float-right"
                                            href="{{ route('backoffice.all_sales_report') }}">
                                            view report
                                        </a>
                                    </div>
                                </div>
                            </div>
                            <div class="col-xl-3 col-md-3 stretch-card grid-margin grid-margin-sm-0 pb-sm-3">
                                <div class="card bg-danger">
                                    <div class="card-body px-3 py-4">
                                        <div class="d-flex justify-content-between align-items-start">
                                            <div class="color-card">
                                                <p class="mb-0 color-card-head">Net Profit</p>
                                                <h2 class="text-white">Tk. {{ $all_sell->sum('net_profit') }}<span
                                                        class="h5">.00</span>
                                                </h2>
                                            </div>
                                            <i class="card-icon-indicator mdi mdi-cube-outline bg-inverse-icon-danger"></i>
                                        </div>
                                        <h6 class="text-white">Today</h6>
                                        <hr>
                                        <a class="btn text-light float-right"
                                            href="{{ route('backoffice.all_sales_report') }}">
                                            view report
                                        </a>
                                    </div>
                                </div>
                            </div>
                            <div class="col-xl-3 col-md-3 stretch-card grid-margin grid-margin-sm-0 pb-sm-3">
                                <div class="card bg-info">
                                    <div class="card-body px-3 py-4">
                                        <div class="d-flex justify-content-between align-items-start">
                                            <div class="color-card">
                                                <p class="mb-0 color-card-head">Expense</p>
                                                <h2 class="text-white">Tk.
                                                    <br>{{ $all_expense->sum('amount') }}<span class="h5">.00</span>
                                                </h2>
                                            </div>
                                            <i class="card-icon-indicator mdi mdi-margin bg-inverse-icon-danger"></i>
                                        </div>
                                        <h6 class="text-white">Today</h6>
                                        <hr>
                                        <a class="btn text-light float-right"
                                            href="{{ route('backoffice.all_sales_report') }}">
                                            view report
                                        </a>
                                    </div>
                                </div>
                            </div>
                        </div>
                    @endif
                </div>
            </div>
            <!-- main-panel ends -->
        </div>
        <!-- page-body-wrapper ends -->
    </div>
    <!-- container-scroller -->
@endsection
