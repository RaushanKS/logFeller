<?php

namespace App\Http\Controllers;

use Carbon\Carbon;
use App\Models\Discount;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Session;
use Illuminate\Support\Facades\Validator;

class DiscountController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
    }

    public function index()
    {
        $totalRecords = Discount::count();
        $activated = Discount::where('status', 1)->count();
        $inactivated = Discount::where('status', 0)->count();
        $twoDaysAgo = Carbon::now()->subDays(30);

        $recentCoupons = Discount::where('created_at', '>=', $twoDaysAgo)->count();

        return view('discount-list', compact(['totalRecords', 'activated', 'inactivated', 'recentCoupons']));
    }

    public function create(Request $request)
    {
        return view('discount-coupon');
    }

    public function store(Request $request)
    {
        $input = $request->all();

        $rules = [
            'coupon_title' => 'required',
            'discount_type' => 'required',
            'start_date' => 'required',
            'end_date' => 'required',
        ];

        $customMessages = [
            'required' => 'The :attribute field is required.',
        ];
        $validator = Validator::make($request->all(), $rules, $customMessages);
        if ($validator->fails()) {
            Session::flash('error', __($validator->errors()->first()));
            return redirect()->back()->with('error', $validator->errors()->first());
        }

        $discounts = Discount::create([
            'name' => $input['coupon_title'],
            'discount_type' => $input['discount_type'],
            'discount_amount' => $input['discount_amount'],
            'discount_percent' => $input['discount_percent'],
            'max_discount' => $input['max_discount'],
            'min_order_amount' => $input['min_order_amount'],
            'description' => $input['coupon_description'],
            'start_date' => $input['start_date'],
            'end_date' => $input['end_date'],
            'status' => ($input['coupon_status']) ? $input['coupon_status'] : 0,
        ]);
        if ($discounts) {
            return redirect()->back()->with('success', 'Discount coupon created successfully');
        } else {
            return redirect()->back()->with('error', 'something went wrong please try again!');
        }
    }

    public function getDiscounts(Request $request)
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
        $totalRecords = Discount::select('count(*) as allcount');
        if ($columnName_arr[7]['search']['value'] != '') {
            $totalRecords = $totalRecords->where('status', $columnName_arr[7]['search']['value']);
        }
        $totalRecords = $totalRecords->count();

        $totalRecordsWithFilter = Discount::select('count(*) as allcount')->where(function ($query) use ($searchValue) {
            $query->where('name', 'LIKE', '%' . $searchValue . '%');
        });
        if ($columnName_arr[7]['search']['value'] != '') {
            $totalRecordsWithFilter = $totalRecordsWithFilter->where('status', $columnName_arr[7]['search']['value']);
        }

        $totalRecordsWithFilter = $totalRecordsWithFilter->count();
        if (empty($searchValue)) {
            $columnName = 'created_at';
            $columnSortOrder = 'desc';
        }

        // Fetch records
        $records = Discount::orderBy($columnName, $columnSortOrder)
            ->where(function ($query) use ($searchValue) {
                $query->where('discounts.name', 'LIKE', '%' . $searchValue . '%');
            });
        if ($columnName_arr[7]['search']['value'] != '') {
            $records = $records->where('status', $columnName_arr[7]['search']['value']);
        }

        $records = $records->select('discounts.*')
        ->skip($start)
            ->take($rowPerPage)
            ->get();
        $data_arr = array();
        $sno = $start + 1;
        foreach ($records as $record) {
            $id = $record->id;
            $name = $record->name;
            $discount_type = $record->discount_type;
            $max_discount = $record->max_discount;
            $min_order_amount = $record->min_order_amount;
            $discount_amount = $record->discount_amount;
            $discount_percent = $record->discount_percent;
            $description = $record->description;
            $start_date = $record->start_date;
            $end_date = $record->end_date;
            $created_at = $record->created_at;
            $status = $record->status;

            $data_arr[] = array(
                "id" => $id,
                "name" => $name,
                "discount_type" => $discount_type,
                "max_discount" => $max_discount,
                "min_order_amount" => ($min_order_amount) ? number_format((float) $min_order_amount, 2, '.', '') : 0.00,
                "discount_amount" => ($discount_amount) ? number_format((float) $discount_amount, 2, '.', '') : 0.00,
                "discount_percent" => $discount_percent,
                "start_date" => $start_date,
                "end_date" => $end_date,
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

    public function edit($id)
    {
        $discounts = Discount::select('id', 'name', 'discount_type', 'max_discount', 'min_order_amount', 'discount_amount', 'discount_percent', 'description', 'start_date', 'end_date', 'status')->where('id', $id)->first();
        
        return view('discount-edit', compact(['discounts']));
    }

    public function update(Request $request, $id)
    {
        $input = $request->all();

        $rules = [
            'coupon_title' => 'required',
            'discount_type' => 'required',
            'start_date' => 'required',
            'end_date' => 'required',
        ];

        $customMessages = [
            'required' => 'The :attribute field is required.',
        ];
        $validator = Validator::make($request->all(), $rules, $customMessages);
        if ($validator->fails()) {
            Session::flash('error', __($validator->errors()->first()));
            return redirect()->back()->with('error', $validator->errors()->first());
        }

        $discounts = Discount::find($id);
        $discounts->name = $input['coupon_title'];
        $discounts->discount_type = $input['discount_type'];
        $discounts->max_discount = $input['max_discount'];
        $discounts->min_order_amount = $input['min_order_amount'];
        $discounts->discount_amount = $input['discount_amount'];
        $discounts->discount_percent = $input['discount_percent'];
        $discounts->description = $input['coupon_description'];
        $discounts->start_date = $input['start_date'];
        $discounts->end_date = $input['end_date'];

        if ($discounts->save()) {
            return redirect()->back()->with('success', 'Coupon Updated Successfully');
        } else {
            return redirect()->back()->with('error', 'Something went wrong');
        }
    }

    public function onlineDiscountsAll(Request $request, $status)
    {
        if ($status == 1) {
            $message = 'Activated Successfully';
        } else {
            $message = 'Inactivated Successfully';
        }
        if (!empty($request->ids) && count($request->ids) > 0) {
            foreach ($request->ids as $ids) {
                $discounts = Discount::find($ids);
                $discounts->status = $status;
                $discounts->save();
            }
            return response()->json([
                'success' => true,
                'message' => $message,
            ]);
        } else {
            return response()->json([
                'success' => false,
                'message' => 'Please select at least one',
            ]);
        }
    }

    public function deleteDiscountsAll(Request $request)
    {
        if (!empty($request->ids) && count($request->ids) > 0) {
            foreach ($request->ids as $ids) {
                Discount::where('id', $ids)->delete();
            }
            Session::flash('success', __('Delete successfully'));
            return response()->json([
                'success' => true,
                'message' => 'Delete successfully',
            ]);
        } else {
            return response()->json([
                'success' => false,
                'message' => 'Please select at least one',
            ]);
        }
    }

    public function destroy($id)
    {

        if (Discount::where('id', $id)->delete()) {
            // Session::flash('success', __('Products delete successfully'));
            return response()->json([
                'success' => true,
                'message' => 'Deleted successfully',
            ]);
        } else {
            Session::flash('error', __('Something went wrong! try again'));
            return response()->json([
                'success' => false,
                'message' => 'Something went wrong! try again',
            ]);
        }
    }

}
