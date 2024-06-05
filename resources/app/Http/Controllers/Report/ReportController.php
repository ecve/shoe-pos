<?php

namespace App\Http\Controllers\Report;

use App\Http\Controllers\Controller;
use App\Models\CartInformtion;
use App\Models\ConsumerLogin;
use App\Models\CustomerPayment;
use App\Models\Expense;
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
        return view('possie.reports.salesReport');
    }
    public function purchaseReport()
    {
        return view('possie.reports.purchaseReport');
    }
    public function expenseReport()
    {
        return view('possie.reports.expenseReport');
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
        $singleDateSales = CartInformtion::join('consumer_login', 'consumer_login.login_id', '=', 'cart_informtion.consumer_id')
            ->where('cart_informtion.cart_date', 'like', '%' . $id . '%')
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
    }
    public function multiDateSales($from, $to)
    {
        $singleDateSales = CartInformtion::join('consumer_login', 'consumer_login.login_id', '=', 'cart_informtion.consumer_id')
            ->whereBetween('cart_informtion.cart_date', [$from, $to])
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
    }

    public function singleDatePurchase($id)
    {
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

    public function multiDatePurchase($from, $to)
    {
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

    public function singleDateExpense($id)
    {
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

    public function multiDateExpense($from, $to)
    {
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
