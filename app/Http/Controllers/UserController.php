<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Session;

class UserController extends Controller
{
    public function index()
    {
        return view('users-list');
    }

    public function getUsers(Request $request)
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
        $totalRecords = User::select('count(*) as allcount')->whereHas(
            'roles',
            function ($q) {
                $q->where('name', 'User');
            }
        );
        if ($columnName_arr[5]['search']['value'] != '') {
            $totalRecords = $totalRecords->where('status', $columnName_arr[5]['search']['value']);
        }
        $totalRecords = $totalRecords->count();

        $totalRecordsWithFilter = User::select('count(*) as allcount')->whereHas(
            'roles',
            function ($q) {
                $q->where('name', 'User');
            }
        )->where(function ($query) use ($searchValue) {
            $query->where('name', 'LIKE', '%' . $searchValue . '%')
                ->orWhere('phone', 'LIKE', '%' . $searchValue . '%')
                ->orWhere('email', 'LIKE', '%' . $searchValue . '%');
        });
        if ($columnName_arr[5]['search']['value'] != '') {
            $totalRecordsWithFilter = $totalRecordsWithFilter->where('status', $columnName_arr[5]['search']['value']);
        }

        $totalRecordsWithFilter = $totalRecordsWithFilter->count();
        if (empty($searchValue)) {
            $columnName = 'created_at';
            $columnSortOrder = 'desc';
        }

        // Fetch records
        $records = User::orderBy($columnName, $columnSortOrder)
            ->where(function ($query) use ($searchValue) {
                $query->where('users.name', 'LIKE', '%' . $searchValue . '%')
                    ->orWhere('users.phone', 'LIKE', '%' . $searchValue . '%')
                    ->orWhere('users.email', 'LIKE', '%' . $searchValue . '%');
            })->whereHas(
                'roles',
                function ($q) {
                    $q->where('name', 'User');
                }
            );
        if ($columnName_arr[5]['search']['value'] != '') {
            $records = $records->where('status', $columnName_arr[5]['search']['value']);
        }

        $records = $records->select('users.*')
            ->skip($start)
            ->take($rowPerPage)
            ->get();
        $data_arr = array();
        $sno = $start + 1;
        foreach ($records as $record) {
            $id = $record->id;
            $name = $record->name;
            $email = $record->email;
            $phone = $record->phone;
            $date = $record->created_at;
            $status = $record->status;

            $status = $record->status;

            $data_arr[] = array(
                "id" => $id,
                "name" => $name,
                "email" => $email,
                "phone" => $phone,
                "date" => (htmlspecialchars_decode(date('j<\s\up>S</\s\up> F Y', strtotime($date)))),
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

    public function destroy($id)
    {
        if (User::where('id', $id)->delete()) {
            Session::flash('success', __('User deleted successfully'));
            return response()->json([
                'success' => true,
                'message' => 'Blog Post deleted successfully',
            ]);
        } else {
            Session::flash('error', __('Something went wrong! try again'));
            return response()->json([
                'success' => false,
                'message' => 'Something went wrong! try again',
            ]);
        }
    }

    public function deleteUsersAll(Request $request)
    {
        if (!empty($request->ids) && count($request->ids) > 0) {
            foreach ($request->ids as $ids) {
                $user = User::find($ids);
                if ($user) {
                    $user->delete();
                }
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

    public function updateUserStatus(Request $request)
    {
        if ($request->ajax()) {
            $data = $request->all();
            // echo '<pre>';
            // print_r($data['status']); exit;
            if ($data['status'] == "Active") {
                $status = 0;
            } else {
                $status = 1;
            }
            User::where('id', $data['filter_id'])->update(['status' => $status]);
            return response()->json(['status' => $status, 'filter_id' => $data['filter_id']]);
        }
    }
}
