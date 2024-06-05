<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Crypt;

use App\Models\CartItemReturn;
use App\Models\ConsumerLogin;
use App\Models\CartInformtion;
use App\Models\CartItem;
use App\Models\BackofficeLogin;
use App\Models\FinalStockTable;
use Carbon\Carbon;

class ReturnController extends Controller
{
    public function index()
    {
        $CartItemReturn = CartItemReturn::join('consumer_login', 'consumer_login.login_id', '=', 'cart_item_return.login_id')
            ->where('return_status', '<', 3)
            ->select('cart_item_return.*', 'consumer_login.mobile_no')
            ->get();
        return view('dashboard.return.allReturn', compact(['CartItemReturn']));
    }

    public function create()
    {
        $ConsumerLogin = ConsumerLogin::all();
        return view('dashboard.return.createReturn', compact(['ConsumerLogin']));
    }

    public function store(Request $request)
    {

        $cart_item_id = implode(",", $request->cart_item_id);

        $received_by_id = session()->get('LoggedUser');

        $cart_item_return = new CartItemReturn();
        $cart_item_return->login_id = $request->consumer_id;
        $cart_item_return->cart_id = $request->cart_id;
        $cart_item_return->cart_item_id = $cart_item_id;
        $cart_item_return->reason_of_return = $request->reason_of_return;
        $cart_item_return->total_amount = $request->total_amount;
        $cart_item_return->non_refundable_vat = $request->non_refundAble;
        $cart_item_return->refund_amount = $request->refundAble_amount;
        $cart_item_return->received_by_id = $received_by_id;
        $cart_item_return->return_date = Carbon::now()->toDateTimeString();
        $cart_item_return->return_status = 1;
        $save = $cart_item_return->save();

        if ($save) {
            return redirect()->route('backoffice.all-return')->with('success', 'Return Confirmed');
        } else {
            return redirect()->route('all-return')->with('fail', 'Something went wrong, failed to register');
        }
    }

    public function view($id)
    {
        $cart_item_return_id = Crypt::decryptString($id);

        $CartItemReturn = CartItemReturn::join('consumer_login', 'consumer_login.login_id', '=', 'cart_item_return.login_id')
            ->where('cart_item_return.cart_item_return_id', $cart_item_return_id)
            ->select('cart_item_return.*', 'consumer_login.mobile_no')
            ->get();

        foreach ($CartItemReturn as $Item) {
            $items = explode(',', $Item->cart_item_id);
        }

        $size = sizeof($items);

        for ($i = 0; $i < $size; $i = $i + 1) {
            $CartItems[$i] = CartItem::join('products', 'products.product_id', '=', 'cart_items.product_id')
                ->where('cart_item_id', $items[$i])
                ->select('cart_items.*', 'products.*')
                ->get();
        }

        $login_id = session()->get('LoggedUser');
        $backofficeLogin = BackofficeLogin::join('backoffice_role', 'backoffice_role.role_id', '=', 'backoffice_login.role_id')
            ->where('backoffice_login.login_id', $login_id)
            ->select('backoffice_login.*', 'backoffice_role.*')
            ->get();

        return view('dashboard.return.viewReturn', compact(['CartItemReturn', 'backofficeLogin', 'CartItems']));
    }

    public function completedReturns()
    {
        $CartItemReturn = CartItemReturn::join('consumer_login', 'consumer_login.login_id', '=', 'cart_item_return.login_id')
            ->where('return_status', '>=', 3)
            ->select('cart_item_return.*', 'consumer_login.mobile_no')
            ->get();
        return view('dashboard.return.completedReturn', compact(['CartItemReturn']));
    }

    public function Authorization($id, $authorize_status)
    {
        $cart_item_return_id = Crypt::decryptString($id);
        $login_id = session()->get('LoggedUser');

        $CartItemReturn = CartItemReturn::where('cart_item_return_id', $cart_item_return_id)->first();
        $CartItemReturn->return_status = $authorize_status;
        $CartItemReturn->authorized_by = $login_id;
        $CartItemReturn->authorize_date = Carbon::now();
        $CartItemReturn->save();

        $CartItemReturnNew = CartItemReturn::join('cart_informtion', 'cart_informtion.cart_id', '=', 'cart_item_return.cart_id')
            ->join('cart_items', 'cart_items.cart_id', '=', 'cart_informtion.cart_id')
            ->where('cart_item_return_id', $cart_item_return_id)
            ->select('cart_items.quantity', 'cart_items.stock_id', 'cart_items.cart_item_id', 'cart_items.product_id')
            ->get();

        foreach ($CartItemReturnNew as $ItemReturn) {
            $updateQuantity = FinalStockTable::where('product_id', $ItemReturn->product_id)
                ->where('stock_id', $ItemReturn->stock_id)
                ->first();
            $updateQuantity->total_sold_quantity = $updateQuantity->total_sold_quantity - $ItemReturn->quantity;
            $updateQuantity->temp_quantity = $updateQuantity->temp_quantity + $ItemReturn->quantity;
            $updateQuantity->final_quantity = $updateQuantity->final_quantity + $ItemReturn->quantity;
            $updateQuantity->save();
        }

        return redirect()->back()->with('success', 'Authorized Successfully !!');
    }

    public function getReturnCart($id)
    {
        $consumer_id = $id;
        $CartInformation = CartInformtion::where('consumer_id', $consumer_id)
            ->whereNotIn('cart_id', function ($query) {

                $query->select('cart_id')->from('cart_item_return');
            })
            ->get();
        return response()->json($CartInformation);
    }

    public function getReturnItem($id)
    {
        $cart_id = $id;
        $CartItem = CartItem::join('products', 'products.product_id', '=', 'cart_items.product_id')
            ->where('cart_items.cart_id', $cart_id)
            ->select('cart_items.*', 'products.product_name')
            ->get();
        $cartInfo = CartInformtion::where('cart_id', $id)->first();
        return response()->json(
            [
                "cartInfo" => $cartInfo,
                "CartItem" => $CartItem
            ]
        );
    }

    // public function recivedToWarehouse($id)
    // {
    //     $cart_item_return_id = Crypt::decryptString($id);

    //     $CartItemReturn = CartItemReturn::join('cart_informtion', 'cart_informtion.cart_id', '=', 'cart_item_return.cart_id')
    //         ->join('cart_items', 'cart_items.cart_id', '=', 'cart_informtion.cart_id')
    //         ->where('cart_item_return_id', $cart_item_return_id)
    //         ->select('cart_items.quantity', 'cart_items.stock_id', 'cart_items.product_id')
    //         ->get();

    //     foreach ($CartItemReturn as $ItemReturn) {
    //         $updateQuantity = FinalStockTable::where('product_id', $ItemReturn->product_id)->first();
    //         $updateQuantity->total_sold_quantity = $updateQuantity->total_sold_quantity - $ItemReturn->quantity;
    //         $updateQuantity->temp_quantity = $updateQuantity->temp_quantity + $ItemReturn->quantity;
    //         $updateQuantity->final_quantity = $updateQuantity->final_quantity + $ItemReturn->quantity;
    //         $updateQuantity->save();
    //     }

    //     $CartItemReturnStatus = CartItemReturn::where('cart_item_return_id', $cart_item_return_id)->first();
    //     $CartItemReturnStatus->return_status = 4;
    //     $CartItemReturnStatus->save();

    //     return redirect()->back()->with('success', 'Received Successfully !!');
    // }

    public function destroy($id)
    {
        //
    }
}
