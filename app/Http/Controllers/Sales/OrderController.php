<?php

namespace App\Http\Controllers\Sales;

use App\Http\Controllers\Controller;
use App\Models\BannerInformation;
use App\Models\CartInformtion;
use App\Models\CartItem;
use App\Models\CartTemporary;
use App\Models\ConsumerLogin;
use App\Models\ProductCategory;
use Illuminate\Http\Request;
use Carbon\Carbon;

class OrderController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function suspendedOrders()
    {
        $CartTemporary = CartTemporary::join('backoffice_login as usr', 'usr.login_id', '=', 'cart_temporary.created_by')->join('backoffice_login as wtr', 'wtr.login_id', '=', 'cart_temporary.waiter_id')
            ->where('is_suspended', 1)
            ->select('cart_temporary.*', 'usr.full_name as created_by_name', 'wtr.full_name as waiter_name')
            ->get();
        return view('sales.suspendedOrder', compact(['CartTemporary']));
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function dailySalesReport()
    {
        $user_id = session()->get('LoggedUser');
        $user_data = \App\Models\BackofficeLogin::join('backoffice_role', 'backoffice_role.role_id', '=', 'backoffice_login.role_id')
            ->where('login_id', $user_id)
            ->first();
         if($user_data->role_id==4){
            $current_date = Carbon::now()->format('Y-m-d');
        $CartInfo = CartInformtion::join('backoffice_login as usr', 'usr.login_id', '=', 'cart_informtion.created_by') //->join('backoffice_login as wtr', 'wtr.login_id', '=', 'cart_informtion.waiter_id')
            ->where('cart_informtion.cart_date', 'like', '%' . $current_date . '%')
            ->where('cart_informtion.is_vat_show',1)
            ->select('cart_informtion.*', 'usr.full_name as created_by_name') //, 'wtr.full_name as waiter_name')
            ->get();

        return view('sales.dailySalesReport', compact(['CartInfo']));
         }
         else{
            $current_date = Carbon::now()->format('Y-m-d');
            $CartInfo = CartInformtion::join('backoffice_login as usr', 'usr.login_id', '=', 'cart_informtion.created_by')
                ->join('consumer_login', 'consumer_login.login_id', '=', 'cart_informtion.consumer_id')
                ->join('cart_payment_methods', 'cart_payment_methods.payment_method_id', '=', 'cart_informtion.payment_method_id')
             //->join('backoffice_login as wtr', 'wtr.login_id', '=', 'cart_informtion.waiter_id')
                ->where('cart_informtion.cart_date', 'like', '%' . $current_date . '%')
                ->select('cart_informtion.*', 'usr.full_name as created_by_name','consumer_login.mobile_no','cart_payment_methods.payment_method') //, 'wtr.full_name as waiter_name')
                ->get();



            return view('sales.dailySalesReport', compact(['CartInfo']));
         }
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function allSalesReport()
    {
        $user_id = session()->get('LoggedUser');
        $user_data = \App\Models\BackofficeLogin::join('backoffice_role', 'backoffice_role.role_id', '=', 'backoffice_login.role_id')
            ->where('login_id', $user_id)
            ->first();
         if($user_data->role_id==4){
            $CartInfo = CartInformtion::join('backoffice_login as usr', 'usr.login_id', '=', 'cart_informtion.created_by') //->join('backoffice_login as wtr', 'wtr.login_id', '=', 'cart_informtion.waiter_id')
            ->where('cart_informtion.is_vat_show',1)
            ->select('cart_informtion.*', 'usr.full_name as created_by_name') //, 'wtr.full_name as waiter_name')
            ->get();
        $consumer = ConsumerLogin::all();
        return view('sales.allSalesReport', compact(['CartInfo', 'consumer']));
         }
         else{
            $CartInfo = CartInformtion::join('backoffice_login as usr', 'usr.login_id', '=', 'cart_informtion.created_by')
              ->join('consumer_login', 'consumer_login.login_id', '=', 'cart_informtion.consumer_id')
              ->join('cart_payment_methods', 'cart_payment_methods.payment_method_id', '=', 'cart_informtion.payment_method_id')
            //->join('backoffice_login as wtr', 'wtr.login_id', '=', 'cart_informtion.waiter_id')
            ->select('cart_informtion.*', 'usr.full_name as created_by_name','consumer_login.mobile_no','cart_payment_methods.payment_method') //, 'wtr.full_name as waiter_name')
            ->get();
        $consumer = ConsumerLogin::all();
        return view('sales.allSalesReport', compact(['CartInfo', 'consumer']));
         }

    }
    public function allSalesReportShowVatAdmin(Request $request){
        $getAllId = $request->selectedRowIds;
        $CartInfo = CartInformtion::whereIn('cart_id',$getAllId)->get();
        foreach($CartInfo as $CartInfo){
            $CartInfo->is_vat_show = 1;
            $CartInfo->save();
        }
        return response()->json([
            'status'=> 200,
            'success'=>'success'
        ]);
    }

    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function aboutRestaurant()
    {
        $bannerInfo = BannerInformation::first();
        return view('sales.aboutRestaurant', compact(['bannerInfo']));
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function printInvoice($id)
    {
        $CartInformtionForPrint = CartInformtion::join('cart_items', 'cart_items.cart_id', '=', 'cart_informtion.cart_id')
            ->join('consumer_login', 'consumer_login.login_id', '=', 'cart_informtion.consumer_id')
            ->join('products', 'products.product_id', '=', 'cart_items.product_id')
            ->join('product_materials', 'product_materials.product_material_id', '=', 'cart_items.product_id')
            ->join('colors', 'colors.colors_id', '=', 'cart_items.colors_id')
            ->join('sizes', 'sizes.size_id', '=', 'cart_items.size_id')
            ->where('cart_informtion.cart_id', $id)
            ->select('cart_informtion.*',
            'cart_items.*',
            'products.product_name',
            'products.cost_price',
            'products.sales_price',
            'products.bulk_price',
            'product_materials.product_material_name',
            'colors.colors_name',
            'sizes.size_name',
            'consumer_login.mobile_no'
            )
            ->get();

        $CartInformtion = CartInformtion::join('consumer_login', 'consumer_login.login_id', '=', 'cart_informtion.consumer_id')
        ->where('cart_informtion.cart_id', $id)
        ->select('cart_informtion.*',
        'consumer_login.mobile_no'
        )
        ->first();
        // return 'Hello';
        return view('sales/print/printInvoice', compact(['CartInformtion', 'CartInformtionForPrint']));
    }

    public function CatWiseSell($consumer_id)
    {
        $sells = CartInformtion::join('backoffice_login as usr', 'usr.login_id', '=', 'cart_informtion.created_by') //->join('backoffice_login as wtr', 'wtr.login_id', '=', 'cart_informtion.waiter_id')
            ->where('cart_informtion.consumer_id', $consumer_id)
            ->select('cart_informtion.*', 'usr.full_name as created_by_name') //, 'wtr.full_name as waiter_name')
            ->get();

        return response()->json($sells);
    }
    public function productNameQuantity($cart_id)
    {
        $cart_item_data = CartItem::join('products', 'products.product_id', '=', 'cart_items.product_id')
            ->where('cart_items.cart_id', $cart_id)
            ->select('cart_items.quantity', 'products.product_name')
            ->get();

        return response()->json($cart_item_data);
    }

    // public function CatWiseSell($category_id)
    // {
    //     $CartInfo = CartInformtion::join('backoffice_login as usr', 'usr.login_id', '=', 'cart_informtion.created_by') //->join('backoffice_login as wtr', 'wtr.login_id', '=', 'cart_informtion.waiter_id')
    //         ->select('cart_informtion.*', 'usr.full_name as created_by_name') //, 'wtr.full_name as waiter_name')
    //         ->get();
    //     $product_cat = ProductCategory::all();
    //     return view('sales.allSalesReport', compact(['CartInfo', 'product_cat']));
    // }

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
