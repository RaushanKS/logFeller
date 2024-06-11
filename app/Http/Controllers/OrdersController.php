<?php

namespace App\Http\Controllers;

use Carbon\Carbon;
use App\Models\User;
use App\Models\Orders;
use App\Models\Products;
use App\Models\Shipping;
use App\Models\OrderItems;
use App\Models\ProductImage;
use Illuminate\Http\Request;
use App\Models\ProductVariant;

class OrdersController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
    }

    public function index()
    {
        // $totalRecords = Discount::count();
        // $activated = Discount::where('status', 1)->count();
        // $inactivated = Discount::where('status', 0)->count();
        // $twoDaysAgo = Carbon::now()->subDays(30);

        // $recentCoupons = Discount::where('created_at', '>=', $twoDaysAgo)->count();

        // return view('discount-list', compact(['totalRecords', 'activated', 'inactivated', 'recentCoupons']));
        return view('orders');
    }

    public function getOrders(Request $request)
    {

        $draw = $request->get('draw');
        $start = $request->get("start");
        $rowPerPage = $request->get("length"); // Rows display per page

        $columnIndex_arr = $request->get('order');
        $columnName_arr = $request->get('columns');
        $order_arr = $request->get('order');
        $search_arr = $request->get('search');

        $columnIndex = $columnIndex_arr[0]['column']; // Column index
        $columnName = $columnName_arr[$columnIndex]['data']; // Column name
        $columnSortOrder = $order_arr[0]['dir']; // asc or desc
        $searchValue = $search_arr['value']; // Search value

        // Total records
        $totalRecords = Orders::select('count(*) as allcount');

        if ($columnName_arr[8]['search']['value'] != '') {
            $totalRecords = $totalRecords->where('status', $columnName_arr[8]['search']['value']);
        }
        $totalRecords = $totalRecords->count();

        $totalRecordsWithFilter = Orders::select('count(*) as allcount')->where(function ($query) use ($searchValue) {
            $query->where('order_id', 'LIKE', '%' . $searchValue . '%')
                ->orWhere('payment_type', 'LIKE', '%' . $searchValue . '%');
        });
        if ($columnName_arr[8]['search']['value'] != '') {
            $totalRecordsWithFilter = $totalRecordsWithFilter->where('status', $columnName_arr[8]['search']['value']);
        }

        $totalRecordsWithFilter = $totalRecordsWithFilter->count();
        if (empty($searchValue)) {
            $columnName = 'created_at';
            $columnSortOrder = 'desc';
        }
        // Fetch records
        $records = Orders::orderBy($columnName, $columnSortOrder)
            ->where(function ($query) use ($searchValue) {
                $query->where('orders.order_id', 'LIKE', '%' . $searchValue . '%')
                    ->orWhere('orders.payment_type', 'LIKE', '%' . $searchValue . '%');
            });
        if ($columnName_arr[8]['search']['value'] != '') {
            $records = $records->where('status', $columnName_arr[8]['search']['value']);
        }

        $records = $records->select('orders.*')
            ->skip($start)
            ->take($rowPerPage)
            ->get();
        $data_arr = array();
        $sno = $start + 1;
        foreach ($records as $record) {
            $customer = User::where('id', $record->user_id)->withTrashed()->first();
            $totalItems = OrderItems::where('order_id', $record->id)->count();

            $id = $record->id;
            $order_id = $record->order_id;
            $total_amount = $record->total_amount;
            $pay_amount = $record->pay_amount;
            $discount_amount = $record->discount_amount;
            $transaction_id = $record->transaction_id;
            $payment_type = $record->payment_type;
            $payment_status = $record->payment_status;
            $customerName = $customer->name;
            $created_at = $record->created_at;
            $status = $record->status;

            $data_arr[] = array(
                "id" => $id,
                "order_id" => $order_id,
                "customerName" => $customerName,
                "total_amount" => $total_amount,
                "pay_amount" => $pay_amount,
                "discount_amount" => $discount_amount,
                "transaction_id" => $transaction_id,
                "payment_type" => $payment_type,
                "payment_status" => $payment_status,
                "totalItems" => $totalItems,
                "created_at" => Carbon::createFromFormat('Y-m-d H:i:s', $created_at)->format('Y-m-d'),
                "status" => ($status) ? $status : '0',
            );
        }

        $response = array(
            "draw" => intval($draw),
            "iTotalRecords" => $totalRecords,
            "iTotalDisplayRecords" => $totalRecordsWithFilter,
            "aaData" => $data_arr,
        );

        echo json_encode($response);
        exit;
    }

    // public function orderSingleView($id)
    // {
    //     $order = Orders::where('id', $id)->first();
    //     $orderItems = OrderItems::where('order_id', $id)->get();
    //     if ($orderItems) {
    //         foreach ($orderItems as $key => $items) {
    //             $product = Products::where('id', $items->product_id)->with(['images', 'variants'])->withTrashed()->first();
    //         }
    //     }

    //     $shippings = Shipping::where('order_id', $id)->withTrashed()->first();
    //     $customer = User::where('id', $order->user_id)->withTrashed()->first();
    //     return view('order-details', compact(['orderItems', 'order', 'shippings', 'customer']));
    // }

    public function orderSingleView($id)
    {
        $order = Orders::where('id', $id)->first();
        $orderCount = Orders::where('user_id', $order->user_id)->count();
        $orderItems = OrderItems::where('order_id', $id)
            ->with(['product.images', 'product.variants'])
            ->get();
        $shippings = Shipping::where('order_id', $id)->withTrashed()->first();
        $customer = User::where('id', $order->user_id)->withTrashed()->first();

        return view('order-details', compact(['orderItems', 'order', 'shippings', 'customer', 'orderCount']));
    }

    public function itemsSingleView($id)
    {
        $orderItems = OrderItems::where('id', $id)->first();

        if (!$orderItems) {
            return response()->json(['error' => 'Order item not found'], 404);
        }

        $order = Orders::where('id', $orderItems->order_id)->first();
        $productVariant = ProductVariant::where('product_id', $orderItems->product_id)->where('id', $orderItems->variation_id)->first();
        $product = Products::where('id', $orderItems->product_id)
            ->with(['images', 'variants'])
            ->withTrashed()
            ->first();

        if (!$product) {
            return response()->json(['error' => 'Product not found'], 404);
        }

        $result = [
            'product' => $product,
            'order' => $order,
            'orderItem' => $orderItems,
            'variation' => $productVariant
        ];

        return response()->json($result);
    }
}
