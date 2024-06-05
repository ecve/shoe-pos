<?php

namespace App\Http\Controllers\Sales;

use App\Http\Controllers\Controller;
use App\Models\BannerInformation;
use App\Models\CartInformtion;
use App\Models\CartTemporary;
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
        $current_date = Carbon::now()->format('Y-m-d');
        $CartInfo = CartInformtion::join('backoffice_login as usr', 'usr.login_id', '=', 'cart_informtion.created_by')//->join('backoffice_login as wtr', 'wtr.login_id', '=', 'cart_informtion.waiter_id')
            ->where('cart_informtion.cart_date', 'like', '%' . $current_date . '%')
            ->select('cart_informtion.*', 'usr.full_name as created_by_name')//, 'wtr.full_name as waiter_name')
            ->get();

        return view('sales.dailySalesReport', compact(['CartInfo']));
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function allSalesReport()
    {
        $CartInfo = CartInformtion::join('backoffice_login as usr', 'usr.login_id', '=', 'cart_informtion.created_by')//->join('backoffice_login as wtr', 'wtr.login_id', '=', 'cart_informtion.waiter_id')
            ->select('cart_informtion.*', 'usr.full_name as created_by_name')//, 'wtr.full_name as waiter_name')
            ->get();

        return view('sales.allSalesReport', compact(['CartInfo']));
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
            ->join('products', 'products.product_id', '=', 'cart_items.product_id')
            ->where('cart_informtion.cart_id', $id)
            ->select('cart_informtion.*', 'cart_items.*', 'products.product_name', 'products.cost_price', 'products.sales_price', 'products.bulk_price')
            ->get();

        $CartInformtion = CartInformtion::find($id);

        return view('sales/print/printInvoice', compact(['CartInformtion', 'CartInformtionForPrint']));
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
