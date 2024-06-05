<?php

namespace App\Http\Controllers\Report;

use App\Http\Controllers\Controller;
use App\Models\CartInformtion;
use App\Models\CartItem;
use App\Models\CartPaymentInformation;
use App\Models\CartPaymentMethod;
use App\Models\ConsumerLogin;
use App\Models\CustomerPayment;
use App\Models\Expense;
use App\Models\ExpenseCategory;
use App\Models\PurchaseInfo;
use App\Models\Supplier;
use App\Models\SupplierPayment;
use Illuminate\Http\Request;

class ReportController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function salesReport()
    {
        $getCartPaymentMethod = CartPaymentMethod::select('payment_method_id', 'payment_method')->get();
        return view('possie.reports.salesReport', compact(['getCartPaymentMethod']));
    }
    public function purchaseReport()
    {
        return view('possie.reports.purchaseReport');
    }
    public function expenseReport()
    {
        $getExpenseCategory = ExpenseCategory::all();
        return view('possie.reports.expenseReport', ['getExpenseCategory' => $getExpenseCategory]);
    }
    public function SupplierBalance()
    {
        return view('possie.reports.SupplierBalance');
    }
    public function CustomerBalance()
    {
        return view('possie.reports.CustomerBalance');
    }
    public function ajaxGetCustomer()
    {
        $consumer = ConsumerLogin::all();

        return response()->json($consumer);
    }
    public function ajaxGetSupplier()
    {
        $consumer = Supplier::all();

        return response()->json($consumer);
    }
    public function ajaxGetCustomerDetails($id)
    {
        $cart = CartInformtion::where('consumer_id', $id)->get();
        $total_sales = $cart->sum("final_total_amount");
        $paid_amount = $cart->sum("paid_amount");
        $due_amount = $cart->sum("due_amount");

        $cp = CustomerPayment::where('customer_id', $id)
            ->where('sales_info_id', null)
            ->get();
        $customer_payment = $cp->sum("paid_amount");

        $balance = $customer_payment + $paid_amount - $total_sales;

        $summary = array(
            'total_sales' => $total_sales,
            'paid_amount' => $paid_amount,
            'due_amount' => $due_amount,
            'customer_payment' => $customer_payment,
            'balance' => $balance
        );

        return response()->json($summary);
    }
    public function ajaxGetSupplierDetails($id)
    {
        $cart = PurchaseInfo::join('supplier_payments', 'supplier_payments.purchase_id', '=', 'purchase_info.purchase_id')
            ->where('supplier_payments.supplier_id', $id)
            ->select('purchase_info.*', 'supplier_payments.supplier_id')
            ->get();
        $total_sales = $cart->sum("total_payable");
        $paid_amount = $cart->sum("paid_amount");
        $due_amount = $cart->sum("due_amount");

        $cp = SupplierPayment::where('supplier_id', $id)
            ->where('purchase_id', null)
            ->get();
        $customer_payment = $cp->sum("paid_amount");

        $balance = $customer_payment + $paid_amount - $total_sales;

        $summary = array(
            'total_sales' => $total_sales,
            'paid_amount' => $paid_amount,
            'due_amount' => $due_amount,
            'customer_payment' => $customer_payment,
            'balance' => $balance
        );

        return response()->json($summary);
    }
    public function singleDateSales($id)
    {
        $user_id = session()->get('LoggedUser');
        $user_data = \App\Models\BackofficeLogin::join('backoffice_role', 'backoffice_role.role_id', '=', 'backoffice_login.role_id')
            ->where('login_id', $user_id)
            ->first();
        $banner_Information = \App\Models\BannerInformation::first();
        if ($user_data->role_id == 4) {
            $singleDateSales = CartInformtion::join('consumer_login', 'consumer_login.login_id', '=', 'cart_informtion.consumer_id')
                ->join('cart_payment_methods', 'cart_payment_methods.payment_method_id', '=', 'cart_informtion.payment_method_id')
                ->where('cart_informtion.cart_date', 'like', '%' . $id . '%')
                ->select('cart_informtion.*', 'consumer_login.mobile_no', 'cart_payment_methods.payment_method')
                ->get();
            $total_orders = $singleDateSales->count();
            $invoice_amount = $singleDateSales->sum('total_cart_amount');
            $discount = $singleDateSales->sum('total_discount');
            $vat = $singleDateSales->sum('vat_amount');
            $payable = $singleDateSales->sum('total_payable_amount');
            $paid = $singleDateSales->sum('paid_amount');
            $due = $singleDateSales->sum('due_amount');

            $summary = array(
                'total_orders' => $total_orders,
                'discount' => $discount,
                'invoice_amount' => $invoice_amount,
                'vat' => $vat,
                'payable' => $payable,
                'paid' => $paid,
                'due' => $due,
            );

            foreach ($singleDateSales as $singleDateSales) {
                $cart_item_data = CartItem::join('products', 'products.product_id', '=', 'cart_items.product_id')
                    ->join('product_materials', 'product_materials.product_material_id', '=', 'cart_items.product_id')
                    ->join('sizes', 'sizes.size_id', '=', 'cart_items.size_id')
                    ->join('colors', 'colors.colors_id', '=', 'cart_items.colors_id')
                    ->where('cart_items.cart_id', $singleDateSales->cart_id)
                    ->select('cart_items.quantity', 'products.product_name', 'product_materials.product_material_name', 'colors.colors_name', 'sizes.size_name')
                    ->get();
            }

            return response()->json([
                'summary' => $summary,
                'singleDateSales' => $singleDateSales,
                'cart_item_data' => $cart_item_data
            ]);
        } else {
            $singleDateSales = CartPaymentInformation::join('cart_informtion', 'cart_informtion.cart_id', '=', 'cart_payment_information.cart_id')
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
                ->where('cart_informtion.cart_date', 'like', '%' . $id . '%')
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


            $getSpecificCardId = CartInformtion::whereIn('cart_id', $getId)->get();
            $getSpecificItem = CartItem::whereIn('cart_id', $getId)->get();

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
                $discountAmount += $single->discount_amount;
                $paidAmount += $single->final_total_amount;
            }


            $total_orders = count($singleDateSales);
            $invoice_amount = $getSpecificCardId->sum('total_cart_amount');

            $vat = $getSpecificCardId->sum('vat_amount');
            $payable = $getSpecificCardId->sum('total_payable_amount');
            $due = $getSpecificCardId->sum('due_amount');
            $totalQuantity = $getSpecificItem->sum('quantity');





            $getOnlyExchnageValue = CartPaymentInformation::join('cart_informtion', 'cart_informtion.cart_id', '=', 'cart_payment_information.cart_id')
            ->join('cart_payment_methods', 'cart_payment_methods.payment_method_id', '=', 'cart_payment_information.payment_method_id')
            ->where('cart_informtion.cart_date', 'like', '%' . $id . '%')
            ->where('cart_payment_information.payment_method_id', '=', 9)
            ->select('cart_payment_information.*')
            ->get();
            $getextotal = $getOnlyExchnageValue->sum('paid_amount');

            // $getPaidTotal = $total_sum_paid->sum('total_payable');
            // dd($paidAmount);
            $paid_amount = $paidAmount- $getextotal;


            $summary = array(
                'total_orders' => $total_orders,
                'discount' => $discountAmount,
                'invoice_amount' => $invoice_amount,
                'vat' => $vat,
                'payable' => $payable,
                'paid' => $paidAmount,
                'due' => $due,
                'totalQuantity' => $totalQuantity,
                'paid_amount' => $paid_amount,
            );
            return response()->json([
                'summary' => $summary,
                'singleDateSales' => $singleDateSales,
                'getSpecificCardId' => $getSpecificCardId,

                // 'cartPaymentInformation' => $cartPaymentInformation,
            ]);
        }
    }
    public function customerMobile($id)
    {
        $user_id = session()->get('LoggedUser');
        $user_data = \App\Models\BackofficeLogin::join('backoffice_role', 'backoffice_role.role_id', '=', 'backoffice_login.role_id')
            ->where('login_id', $user_id)
            ->first();
        $banner_Information = \App\Models\BannerInformation::first();
        if ($user_data->role_id == 4) {
            $singleDateSales = CartInformtion::join('consumer_login', 'consumer_login.login_id', '=', 'cart_informtion.consumer_id')
                ->join('cart_items', 'cart_items.cart_id', '=', 'cart_informtion.cart_id')
                ->join('product_materials', 'product_materials.product_material_id', '=', 'cart_items.product_id')
                ->join('foot_ware_categories', 'product_materials.foot_ware_categories_id', '=', 'foot_ware_categories.foot_ware_categories_id')
                ->join('types', 'product_materials.type_id', '=', 'types.type_id')
                ->join('material_types', 'product_materials.material_type_id', '=', 'material_types.material_type_id')
                ->join('brand_types', 'product_materials.brand_type_id', '=', 'brand_types.brand_type_id')
                ->join('sizes', 'sizes.size_id', '=', 'cart_items.size_id')
                ->join('colors', 'colors.colors_id', '=', 'cart_items.colors_id')
                ->join('cart_payment_methods', 'cart_payment_methods.payment_method_id', '=', 'cart_informtion.payment_method_id')
                ->where('consumer_login.mobile_no', 'like', '%' . $id . '%')
                ->where('cart_informtion.is_vat_show', 1)
                ->select('cart_informtion.*', 'cart_items.quantity', 'cart_items.barcode', 'consumer_login.mobile_no', 'product_materials.product_material_name', 'colors.colors_name', 'sizes.size_name', 'foot_ware_categories.foot_ware_categories_name', 'types.type_name', 'material_types.material_type_name', 'brand_types.brand_type_name', 'cart_payment_methods.payment_method')
                ->get();
            $total_orders = $singleDateSales->count();
            $invoice_amount = $singleDateSales->sum('total_cart_amount');
            $discount = $singleDateSales->sum('total_discount');
            $vat = $singleDateSales->sum('vat_amount');
            $payable = $singleDateSales->sum('total_payable_amount');
            $paid = $singleDateSales->sum('paid_amount');
            $due = $singleDateSales->sum('due_amount');
            $totalQuantity = $singleDateSales->sum('quantity');

            $summary = array(
                'total_orders' => $total_orders,
                'discount' => $discount,
                'invoice_amount' => $invoice_amount,
                'vat' => $vat,
                'payable' => $payable,
                'paid' => $paid,
                'due' => $due,
                'totalQuantity' => $totalQuantity,
            );

            return response()->json([
                'summary' => $summary,
                'singleDateSales' => $singleDateSales
            ]);
        } else {
            $singleDateSales = CartPaymentInformation::join('cart_informtion', 'cart_informtion.cart_id', '=', 'cart_payment_information.cart_id')
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
                ->where('consumer_login.mobile_no', 'like', '%' . $id . '%')
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


            $getSpecificCardId = CartInformtion::whereIn('cart_id', $getId)->get();
            $getSpecificItem = CartItem::whereIn('cart_id', $getId)->get();

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
                $discountAmount += $single->discount_amount;
                $paidAmount += $single->final_total_amount;
            }


            $total_orders = count($singleDateSales);
            $invoice_amount = $getSpecificCardId->sum('total_cart_amount');

            $vat = $getSpecificCardId->sum('vat_amount');
            $payable = $getSpecificCardId->sum('total_payable_amount');
            $due = $getSpecificCardId->sum('due_amount');
            $totalQuantity = $getSpecificItem->sum('quantity');


            $getOnlyExchnageValue = CartPaymentInformation::join('cart_informtion', 'cart_informtion.cart_id', '=', 'cart_payment_information.cart_id')
            ->join('cart_payment_methods', 'cart_payment_methods.payment_method_id', '=', 'cart_payment_information.payment_method_id')
            ->where('consumer_login.mobile_no', 'like', '%' . $id . '%')
            ->where('cart_payment_information.payment_method_id', '=', 9)
            ->select('cart_payment_information.*')
            ->get();
            $getextotal = $getOnlyExchnageValue->sum('paid_amount');

            // $getPaidTotal = $total_sum_paid->sum('total_payable');
            // dd($paidAmount);
            $paid_amount = $paidAmount- $getextotal;

            $summary = array(
                'total_orders' => $total_orders,
                'discount' => $discountAmount,
                'invoice_amount' => $invoice_amount,
                'vat' => $vat,
                'payable' => $payable,
                'paid' => $paidAmount,
                'due' => $due,
                'totalQuantity' => $totalQuantity,
                'paid_amount' => $paid_amount,
            );
            return response()->json([
                'summary' => $summary,
                'singleDateSales' => $singleDateSales,
                'getSpecificCardId' => $getSpecificCardId,

                // 'cartPaymentInformation' => $cartPaymentInformation,
            ]);
        }
    }
    public function dueList($id)
    {
        $user_id = session()->get('LoggedUser');
        $user_data = \App\Models\BackofficeLogin::join('backoffice_role', 'backoffice_role.role_id', '=', 'backoffice_login.role_id')
            ->where('login_id', $user_id)
            ->first();
        $banner_Information = \App\Models\BannerInformation::first();
        if ($user_data->role_id == 4) {
            $singleDateSales = CartInformtion::join('consumer_login', 'consumer_login.login_id', '=', 'cart_informtion.consumer_id')
                ->where('cart_informtion.due_amount', 'like', '%' . $id . '%')
                ->where('cart_informtion.is_vat_show', 1)
                ->select('cart_informtion.*', 'consumer_login.mobile_no')
                ->get();
            $total_orders = $singleDateSales->count();
            $invoice_amount = $singleDateSales->sum('total_cart_amount');
            $discount = $singleDateSales->sum('total_discount');
            $vat = $singleDateSales->sum('vat_amount');
            $payable = $singleDateSales->sum('total_payable_amount');
            $paid = $singleDateSales->sum('paid_amount');
            $due = $singleDateSales->sum('due_amount');

            $summary = array(
                'total_orders' => $total_orders,
                'discount' => $discount,
                'invoice_amount' => $invoice_amount,
                'vat' => $vat,
                'payable' => $payable,
                'paid' => $paid,
                'due' => $due,
            );

            return response()->json([
                'summary' => $summary,
                'singleDateSales' => $singleDateSales
            ]);
        } else {
            $singleDateSales = CartPaymentInformation::join('cart_informtion', 'cart_informtion.cart_id', '=', 'cart_payment_information.cart_id')
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
                ->where('cart_informtion.due_amount', 'like', '%' . $id . '%')
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


            $getSpecificCardId = CartInformtion::whereIn('cart_id', $getId)->get();
            $getSpecificItem = CartItem::whereIn('cart_id', $getId)->get();

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
                $discountAmount += $single->discount_amount;
                $paidAmount += $single->final_total_amount;
            }


            $total_orders = count($singleDateSales);
            $invoice_amount = $getSpecificCardId->sum('total_cart_amount');

            $vat = $getSpecificCardId->sum('vat_amount');
            $payable = $getSpecificCardId->sum('total_payable_amount');
            $due = $getSpecificCardId->sum('due_amount');
            $totalQuantity = $getSpecificItem->sum('quantity');


            $getOnlyExchnageValue = CartPaymentInformation::join('cart_informtion', 'cart_informtion.cart_id', '=', 'cart_payment_information.cart_id')
            ->join('cart_payment_methods', 'cart_payment_methods.payment_method_id', '=', 'cart_payment_information.payment_method_id')
            ->where('cart_informtion.due_amount', 'like', '%' . $id . '%')
            ->where('cart_payment_information.payment_method_id', '=', 9)
            ->select('cart_payment_information.*')
            ->get();
            $getextotal = $getOnlyExchnageValue->sum('paid_amount');

            // $getPaidTotal = $total_sum_paid->sum('total_payable');
            // dd($paidAmount);
            $paid_amount = $paidAmount- $getextotal;

            $summary = array(
                'total_orders' => $total_orders,
                'discount' => $discountAmount,
                'invoice_amount' => $invoice_amount,
                'vat' => $vat,
                'payable' => $payable,
                'paid' => $paidAmount,
                'due' => $due,
                'totalQuantity' => $totalQuantity,
                'paid_amount' => $paid_amount,
            );
            return response()->json([
                'summary' => $summary,
                'singleDateSales' => $singleDateSales,
                'getSpecificCardId' => $getSpecificCardId,

                // 'cartPaymentInformation' => $cartPaymentInformation,
            ]);
        }
    }

    function paymentMethod($id, Request $request)
    {
        if ($request->from && $request->to) {
            $singleDateSales = CartPaymentInformation::join('cart_informtion', 'cart_informtion.cart_id', '=', 'cart_payment_information.cart_id')
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
                ->where('cart_payment_information.payment_method_id', $id)
                ->whereBetween('cart_informtion.cart_date', [$request->from, $request->to])
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


            $getSpecificCardId = CartInformtion::whereIn('cart_id', $getId)->get();
            $getSpecificItem = CartItem::whereIn('cart_id', $getId)->get();

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
                $discountAmount += $single->discount_amount;
                $paidAmount += $single->final_total_amount;
            }


            $total_orders = count($singleDateSales);
            $invoice_amount = $getSpecificCardId->sum('total_cart_amount');

            $vat = $getSpecificCardId->sum('vat_amount');
            $payable = $getSpecificCardId->sum('total_payable_amount');
            $due = $getSpecificCardId->sum('due_amount');
            $totalQuantity = $getSpecificItem->sum('quantity');


            $getOnlyExchnageValue = CartPaymentInformation::join('cart_informtion', 'cart_informtion.cart_id', '=', 'cart_payment_information.cart_id')
            ->join('cart_payment_methods', 'cart_payment_methods.payment_method_id', '=', 'cart_payment_information.payment_method_id')
            ->where('cart_payment_information.payment_method_id', $id)
            ->whereBetween('cart_informtion.cart_date', [$request->from, $request->to])
            ->where('cart_payment_information.payment_method_id', '=', 9)
            ->select('cart_payment_information.*')
            ->get();
            $getextotal = $getOnlyExchnageValue->sum('paid_amount');

            // $getPaidTotal = $total_sum_paid->sum('total_payable');
            // dd($paidAmount);
            $paid_amount = $paidAmount- $getextotal;

            $summary = array(
                'total_orders' => $total_orders,
                'discount' => $discountAmount,
                'invoice_amount' => $invoice_amount,
                'vat' => $vat,
                'payable' => $payable,
                'paid' => $paidAmount,
                'due' => $due,
                'totalQuantity' => $totalQuantity,
                'paid_amount' => $paid_amount,
            );
            return response()->json([
                'summary' => $summary,
                'singleDateSales' => $singleDateSales,
                'getSpecificCardId' => $getSpecificCardId,

                // 'cartPaymentInformation' => $cartPaymentInformation,
            ]);
        } elseif ($request->singledate) {
            $singleDateSales = CartPaymentInformation::join('cart_informtion', 'cart_informtion.cart_id', '=', 'cart_payment_information.cart_id')
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
                ->where('cart_payment_information.payment_method_id', $id)
                ->where('cart_informtion.cart_date', $request->singledate)
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


            $getSpecificCardId = CartInformtion::whereIn('cart_id', $getId)->get();
            $getSpecificItem = CartItem::whereIn('cart_id', $getId)->get();

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
                $discountAmount += $single->discount_amount;
                $paidAmount += $single->final_total_amount;
            }


            $total_orders = count($singleDateSales);
            $invoice_amount = $getSpecificCardId->sum('total_cart_amount');

            $vat = $getSpecificCardId->sum('vat_amount');
            $payable = $getSpecificCardId->sum('total_payable_amount');
            $due = $getSpecificCardId->sum('due_amount');
            $totalQuantity = $getSpecificItem->sum('quantity');


            $getOnlyExchnageValue = CartPaymentInformation::join('cart_informtion', 'cart_informtion.cart_id', '=', 'cart_payment_information.cart_id')
            ->join('cart_payment_methods', 'cart_payment_methods.payment_method_id', '=', 'cart_payment_information.payment_method_id')
            ->where('cart_payment_information.payment_method_id', $id)
            ->where('cart_informtion.cart_date', $request->singledate)
            ->where('cart_payment_information.payment_method_id', '=', 9)
            ->select('cart_payment_information.*')
            ->get();
            $getextotal = $getOnlyExchnageValue->sum('paid_amount');

            // $getPaidTotal = $total_sum_paid->sum('total_payable');
            // dd($paidAmount);
            $paid_amount = $paidAmount- $getextotal;

            $summary = array(
                'total_orders' => $total_orders,
                'discount' => $discountAmount,
                'invoice_amount' => $invoice_amount,
                'vat' => $vat,
                'payable' => $payable,
                'paid' => $paidAmount,
                'due' => $due,
                'totalQuantity' => $totalQuantity,
                'paid_amount' => $paid_amount,
            );
            return response()->json([
                'summary' => $summary,
                'singleDateSales' => $singleDateSales,
                'getSpecificCardId' => $getSpecificCardId,

                // 'cartPaymentInformation' => $cartPaymentInformation,
            ]);
        } else {
            $singleDateSales = CartPaymentInformation::join('cart_informtion', 'cart_informtion.cart_id', '=', 'cart_payment_information.cart_id')
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
                ->where('cart_payment_information.payment_method_id', $id)
                // ->where('cart_informtion.cart_date', $request->singledate)
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


            $getSpecificCardId = CartInformtion::whereIn('cart_id', $getId)->get();
            $getSpecificItem = CartItem::whereIn('cart_id', $getId)->get();

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
                $discountAmount += $single->discount_amount;
                $paidAmount += $single->final_total_amount;
            }


            $total_orders = count($singleDateSales);
            $invoice_amount = $getSpecificCardId->sum('total_cart_amount');

            $vat = $getSpecificCardId->sum('vat_amount');
            $payable = $getSpecificCardId->sum('total_payable_amount');
            $due = $getSpecificCardId->sum('due_amount');
            $totalQuantity = $getSpecificItem->sum('quantity');


            $getOnlyExchnageValue = CartPaymentInformation::join('cart_informtion', 'cart_informtion.cart_id', '=', 'cart_payment_information.cart_id')
            ->join('cart_payment_methods', 'cart_payment_methods.payment_method_id', '=', 'cart_payment_information.payment_method_id')
            ->where('cart_payment_information.payment_method_id', $id)
            ->where('cart_payment_information.payment_method_id', '=', 9)
            ->select('cart_payment_information.*')
            ->get();
            $getextotal = $getOnlyExchnageValue->sum('paid_amount');

            // $getPaidTotal = $total_sum_paid->sum('total_payable');
            // dd($paidAmount);
            $paid_amount = $paidAmount- $getextotal;

            $summary = array(
                'total_orders' => $total_orders,
                'discount' => $discountAmount,
                'invoice_amount' => $invoice_amount,
                'vat' => $vat,
                'payable' => $payable,
                'paid' => $paidAmount,
                'due' => $due,
                'totalQuantity' => $totalQuantity,
                'paid_amount' => $paid_amount,
            );
            return response()->json([
                'summary' => $summary,
                'singleDateSales' => $singleDateSales,
                'getSpecificCardId' => $getSpecificCardId,

                // 'cartPaymentInformation' => $cartPaymentInformation,
            ]);
        }
    }
    public function multiDateSales($from, $to)
    {
        $user_id = session()->get('LoggedUser');
        $user_data = \App\Models\BackofficeLogin::join('backoffice_role', 'backoffice_role.role_id', '=', 'backoffice_login.role_id')
            ->where('login_id', $user_id)
            ->first();
        $banner_Information = \App\Models\BannerInformation::first();
        if ($user_data->role_id == 4) {
            $singleDateSales = CartInformtion::join('consumer_login', 'consumer_login.login_id', '=', 'cart_informtion.consumer_id')
                ->join('cart_items', 'cart_items.cart_id', '=', 'cart_informtion.cart_id')
                ->join('product_materials', 'product_materials.product_material_id', '=', 'cart_items.product_id')
                ->join('foot_ware_categories', 'product_materials.foot_ware_categories_id', '=', 'foot_ware_categories.foot_ware_categories_id')
                ->join('types', 'product_materials.type_id', '=', 'types.type_id')
                ->join('material_types', 'product_materials.material_type_id', '=', 'material_types.material_type_id')
                ->join('brand_types', 'product_materials.brand_type_id', '=', 'brand_types.brand_type_id')
                ->join('sizes', 'sizes.size_id', '=', 'cart_items.size_id')
                ->join('colors', 'colors.colors_id', '=', 'cart_items.colors_id')

                ->whereBetween('cart_informtion.cart_date', [$from, $to])
                ->where('cart_informtion.is_vat_show', 1)
                ->select('cart_informtion.*', 'cart_items.quantity', 'cart_items.barcode', 'consumer_login.mobile_no', 'product_materials.product_material_name', 'colors.colors_name', 'sizes.size_name', 'foot_ware_categories.foot_ware_categories_name', 'types.type_name', 'material_types.material_type_name', 'brand_types.brand_type_name')
                ->get();
            $total_orders = $singleDateSales->count();
            $invoice_amount = $singleDateSales->sum('total_cart_amount');
            $discount = $singleDateSales->sum('total_discount');
            $vat = $singleDateSales->sum('vat_amount');
            $payable = $singleDateSales->sum('total_payable_amount');
            $paid = $singleDateSales->sum('paid_amount');
            $due = $singleDateSales->sum('due_amount');
            $totalQuantity = $singleDateSales->sum('quantity');
            $summary = array(
                'total_orders' => $total_orders,
                'discount' => $discount,
                'invoice_amount' => $invoice_amount,
                'vat' => $vat,
                'payable' => $payable,
                'paid' => $paid,
                'due' => $due,
                'totalQuantity' => $totalQuantity,
            );

            return response()->json([
                'summary' => $summary,
                'singleDateSales' => $singleDateSales
            ]);
        } else {
            $singleDateSales = CartPaymentInformation::join('cart_informtion', 'cart_informtion.cart_id', '=', 'cart_payment_information.cart_id')
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
                ->whereBetween('cart_informtion.cart_date', [$from, $to])
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


                $getSpecificCardId = CartInformtion::whereIn('cart_id', $getId)->get();
                $getSpecificItem = CartItem::whereIn('cart_id', $getId)->get();

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
                    $discountAmount += $single->discount_amount;
                    $paidAmount += $single->final_total_amount;
                }


                $total_orders = count($singleDateSales);
                $invoice_amount = $getSpecificCardId->sum('total_cart_amount');

                $vat = $getSpecificCardId->sum('vat_amount');
                $payable = $getSpecificCardId->sum('total_payable_amount');
                $due = $getSpecificCardId->sum('due_amount');
                $totalQuantity = $getSpecificItem->sum('quantity');


                $getOnlyExchnageValue = CartPaymentInformation::join('cart_informtion', 'cart_informtion.cart_id', '=', 'cart_payment_information.cart_id')
            ->join('cart_payment_methods', 'cart_payment_methods.payment_method_id', '=', 'cart_payment_information.payment_method_id')
            ->whereBetween('cart_informtion.cart_date', [$from, $to])
            ->where('cart_payment_information.payment_method_id', '=', 9)
            ->select('cart_payment_information.*')
            ->get();
            $getextotal = $getOnlyExchnageValue->sum('paid_amount');

            // $getPaidTotal = $total_sum_paid->sum('total_payable');
            // dd($paidAmount);
            $paid_amount = $paidAmount- $getextotal;
                $summary = array(
                    'total_orders' => $total_orders,
                    'discount' => $discountAmount,
                    'invoice_amount' => $invoice_amount,
                    'vat' => $vat,
                    'payable' => $payable,
                    'paid' => $paidAmount,
                    'due' => $due,
                    'totalQuantity' => $totalQuantity,
                    'paid_amount' => $paid_amount,
                );
                return response()->json([
                    'summary' => $summary,
                    'singleDateSales' => $singleDateSales,
                    'getSpecificCardId' => $getSpecificCardId,

                    // 'cartPaymentInformation' => $cartPaymentInformation,
                ]);
        }
    }

    public function singleDatePurchase($id)
    {
        $user_id = session()->get('LoggedUser');
        $user_data = \App\Models\BackofficeLogin::join('backoffice_role', 'backoffice_role.role_id', '=', 'backoffice_login.role_id')
            ->where('login_id', $user_id)
            ->first();
        $banner_Information = \App\Models\BannerInformation::first();
        if ($user_data->role_id == 4) {
            $singleDateSales = PurchaseInfo::where('pur_date', 'like', '%' . $id . '%')
                ->where('is_vat_show', 1)
                ->get();

            $total_orders = $singleDateSales->count();
            $invoice_amount = $singleDateSales->sum('total_item_price');
            $discount = $singleDateSales->sum('discount');
            $vat = $singleDateSales->sum('total_vat');
            $payable = $singleDateSales->sum('total_payable');
            $paid = $singleDateSales->sum('paid_amount');
            $due = $singleDateSales->sum('due_amount');

            $summary = array(
                'total_orders' => $total_orders,
                'discount' => $discount,
                'invoice_amount' => $invoice_amount,
                'vat' => $vat,
                'payable' => $payable,
                'paid' => $paid,
                'due' => $due,
            );

            return response()->json([
                'summary' => $summary,
                'singleDateSales' => $singleDateSales
            ]);
        } else {
            $singleDateSales = PurchaseInfo::where('pur_date', 'like', '%' . $id . '%')->get();

            $total_orders = $singleDateSales->count();
            $invoice_amount = $singleDateSales->sum('total_item_price');
            $discount = $singleDateSales->sum('discount');
            $vat = $singleDateSales->sum('total_vat');
            $payable = $singleDateSales->sum('total_payable');
            $paid = $singleDateSales->sum('paid_amount');
            $due = $singleDateSales->sum('due_amount');

            $summary = array(
                'total_orders' => $total_orders,
                'discount' => $discount,
                'invoice_amount' => $invoice_amount,
                'vat' => $vat,
                'payable' => $payable,
                'paid' => $paid,
                'due' => $due,
            );

            return response()->json([
                'summary' => $summary,
                'singleDateSales' => $singleDateSales
            ]);
        }
    }

    public function multiDatePurchase($from, $to)
    {
        $user_id = session()->get('LoggedUser');
        $user_data = \App\Models\BackofficeLogin::join('backoffice_role', 'backoffice_role.role_id', '=', 'backoffice_login.role_id')
            ->where('login_id', $user_id)
            ->first();
        $banner_Information = \App\Models\BannerInformation::first();
        if ($user_data->role_id == 4) {
            $singleDateSales = PurchaseInfo::whereBetween('pur_date', [$from, $to])
                ->where('is_vat_show', 1)
                ->get();

            $total_orders = $singleDateSales->count();
            $invoice_amount = $singleDateSales->sum('total_item_price');
            $discount = $singleDateSales->sum('discount');
            $vat = $singleDateSales->sum('total_vat');
            $payable = $singleDateSales->sum('total_payable');
            $paid = $singleDateSales->sum('paid_amount');
            $due = $singleDateSales->sum('due_amount');

            $summary = array(
                'total_orders' => $total_orders,
                'discount' => $discount,
                'invoice_amount' => $invoice_amount,
                'vat' => $vat,
                'payable' => $payable,
                'paid' => $paid,
                'due' => $due,
            );

            return response()->json([
                'summary' => $summary,
                'singleDateSales' => $singleDateSales
            ]);
        } else {
            $singleDateSales = PurchaseInfo::whereBetween('pur_date', [$from, $to])->get();

            $total_orders = $singleDateSales->count();
            $invoice_amount = $singleDateSales->sum('total_item_price');
            $discount = $singleDateSales->sum('discount');
            $vat = $singleDateSales->sum('total_vat');
            $payable = $singleDateSales->sum('total_payable');
            $paid = $singleDateSales->sum('paid_amount');
            $due = $singleDateSales->sum('due_amount');

            $summary = array(
                'total_orders' => $total_orders,
                'discount' => $discount,
                'invoice_amount' => $invoice_amount,
                'vat' => $vat,
                'payable' => $payable,
                'paid' => $paid,
                'due' => $due,
            );

            return response()->json([
                'summary' => $summary,
                'singleDateSales' => $singleDateSales
            ]);
        }
    }

    public function singleDateExpense($id)
    {
        $user_id = session()->get('LoggedUser');
        $user_data = \App\Models\BackofficeLogin::join('backoffice_role', 'backoffice_role.role_id', '=', 'backoffice_login.role_id')
            ->where('login_id', $user_id)
            ->first();
        $banner_Information = \App\Models\BannerInformation::first();
        if ($user_data->role_id == 4) {
            $singleDateSales = Expense::join('expense_details', 'expense_details.expense_id', '=', 'expenses.expense_id')
                ->leftJoin('expense_categories', 'expense_categories.expense_category_id', '=', 'expenses.expense_category_id')
                ->where('date', 'like', '%' . $id . '%')
                ->where('expense_details.is_vat_show', 1)
                ->select('expenses.*', 'expense_categories.*', 'expense_details.*')
                ->get();

            $total_orders = $singleDateSales->count();
            $amount = $singleDateSales->sum('amount');

            $summary = array(
                'total_orders' => $total_orders,
                'amount' => $amount
            );

            return response()->json([
                'summary' => $summary,
                'singleDateSales' => $singleDateSales
            ]);
        } else {
            $singleDateSales = Expense::join('expense_details', 'expense_details.expense_id', '=', 'expenses.expense_id')
                ->leftJoin('expense_categories', 'expense_categories.expense_category_id', '=', 'expenses.expense_category_id')
                ->where('date', 'like', '%' . $id . '%')
                ->select('expenses.*', 'expense_categories.*', 'expense_details.*')
                ->get();

            $total_orders = $singleDateSales->count();
            $amount = $singleDateSales->sum('amount');

            $summary = array(
                'total_orders' => $total_orders,
                'amount' => $amount
            );

            return response()->json([
                'summary' => $summary,
                'singleDateSales' => $singleDateSales
            ]);
        }
    }
    public function singleExpenseCategory($id)
    {
        $user_id = session()->get('LoggedUser');
        $user_data = \App\Models\BackofficeLogin::join('backoffice_role', 'backoffice_role.role_id', '=', 'backoffice_login.role_id')
            ->where('login_id', $user_id)
            ->first();
        $banner_Information = \App\Models\BannerInformation::first();
        if ($user_data->role_id == 4) {
            $singleDateSales = Expense::join('expense_details', 'expense_details.expense_id', '=', 'expenses.expense_id')
                ->leftJoin('expense_categories', 'expense_categories.expense_category_id', '=', 'expenses.expense_category_id')
                ->where('expense_categories.expense_category_name', 'like', '%' . $id . '%')
                ->where('expense_details.is_vat_show', 1)
                ->select('expenses.*', 'expense_categories.*', 'expense_details.*')
                ->get();

            $total_orders = $singleDateSales->count();
            $amount = $singleDateSales->sum('amount');

            $summary = array(
                'total_orders' => $total_orders,
                'amount' => $amount
            );

            return response()->json([
                'summary' => $summary,
                'singleDateSales' => $singleDateSales
            ]);
        } else {
            $singleDateSales = Expense::join('expense_details', 'expense_details.expense_id', '=', 'expenses.expense_id')
                ->leftJoin('expense_categories', 'expense_categories.expense_category_id', '=', 'expenses.expense_category_id')
                ->where('expense_categories.expense_category_name', 'like', '%' . $id . '%')
                ->select('expenses.*', 'expense_categories.*', 'expense_details.*')
                ->get();

            $total_orders = $singleDateSales->count();
            $amount = $singleDateSales->sum('amount');

            $summary = array(
                'total_orders' => $total_orders,
                'amount' => $amount
            );

            return response()->json([
                'summary' => $summary,
                'singleDateSales' => $singleDateSales
            ]);
        }
    }

    public function multiDateExpense($from, $to)
    {
        $user_id = session()->get('LoggedUser');
        $user_data = \App\Models\BackofficeLogin::join('backoffice_role', 'backoffice_role.role_id', '=', 'backoffice_login.role_id')
            ->where('login_id', $user_id)
            ->first();
        $banner_Information = \App\Models\BannerInformation::first();
        if ($user_data->role_id == 4) {
            $singleDateSales = Expense::join('expense_details', 'expense_details.expense_id', '=', 'expenses.expense_id')
                ->leftJoin('expense_categories', 'expense_categories.expense_category_id', '=', 'expenses.expense_category_id')
                ->whereBetween('date', [$from, $to])
                ->where('expense_details.is_vat_show', 1)
                ->select('expenses.*', 'expense_categories.*', 'expense_details.*')
                ->get();

            $total_orders = $singleDateSales->count();
            $amount = $singleDateSales->sum('amount');

            $summary = array(
                'total_orders' => $total_orders,
                'amount' => $amount
            );

            return response()->json([
                'summary' => $summary,
                'singleDateSales' => $singleDateSales
            ]);
        } else {
            $singleDateSales = Expense::join('expense_details', 'expense_details.expense_id', '=', 'expenses.expense_id')
                ->leftJoin('expense_categories', 'expense_categories.expense_category_id', '=', 'expenses.expense_category_id')
                ->whereBetween('date', [$from, $to])
                ->select('expenses.*', 'expense_categories.*', 'expense_details.*')
                ->get();

            $total_orders = $singleDateSales->count();
            $amount = $singleDateSales->sum('amount');

            $summary = array(
                'total_orders' => $total_orders,
                'amount' => $amount
            );

            return response()->json([
                'summary' => $summary,
                'singleDateSales' => $singleDateSales
            ]);
        }
    }
    /**
     * Show the form for editing the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function edit($id)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, $id)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
        //
    }
}
