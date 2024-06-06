<?php

namespace App\Http\Controllers;

use App\Models\ContactUs;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Session;

class EnquiryController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
    }

    public function index()
    {
        return view('contact-us');
    }

    public function getEnquiries(Request $request)
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
        $totalRecords = ContactUs::select('count(*) as allcount');
        // if ($columnName_arr[4]['search']['value'] != '') {
        //     $totalRecords = $totalRecords->where('status', $columnName_arr[4]['search']['value']);
        // }
        $totalRecords = $totalRecords->count();

        $totalRecordsWithFilter = ContactUs::select('count(*) as allcount')->where(function ($query) use ($searchValue) {
            $query->where('name', 'LIKE', '%' . $searchValue . '%');
        });
        // if ($columnName_arr[4]['search']['value'] != '') {
        //     $totalRecordsWithFilter = $totalRecordsWithFilter->where('status', $columnName_arr[4]['search']['value']);
        // }

        $totalRecordsWithFilter = $totalRecordsWithFilter->count();

        if (empty($searchValue)) {
            $columnName = 'created_at';
            $columnSortOrder = 'desc';
        }

        // Fetch records
        $records = ContactUs::orderBy($columnName, $columnSortOrder)
            ->where(function ($query) use ($searchValue) {
                $query->where('contact_us.name', 'LIKE', '%' . $searchValue . '%');
            });
        // if ($columnName_arr[4]['search']['value'] != '') {
        //     $records = $records->where('status', $columnName_arr[4]['search']['value']);
        // }

        $records = $records->select('contact_us.*')
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
            $subject = $record->subject;
            $message = $record->message;

            $data_arr[] = array(
                "id" => $id,
                "name" => $name,
                "email" => $email,
                "phone" => $phone,
                "subject" => $subject,
                "message" => $message,
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

    public function getEnquiryDetails($contactId)
    {
        $contact = ContactUs::where('id', $contactId)->first();

        if (!$contact) {
            return response()->json(['error' => 'Enquiry not found'], 404);
        }

        $result = [
            'productContact' => $contact,
        ];

        return response()->json($result);
    }

    public function destroy($id)
    {
        $contactus = ContactUs::find($id);

        if (!$contactus) {
            Session::flash('error', __('Enquiry not found!'));
            return response()->json([
                'success' => false,
                'message' => 'Enquiry not found!',
            ]);
        }

        if ($contactus->delete()) {
            Session::flash('success', __('Enquiry deleted successfully!'));
            return response()->json([
                'success' => true,
                'message' => 'Enquiry deleted successfully!',
            ]);
        } else {
            Session::flash('error', __('Something went wrong! Try again.'));
            return response()->json([
                'success' => false,
                'message' => 'Something went wrong! Try again.',
            ]);
        }
    }

    public function deleteEnquiriesAll(Request $request)
    {
        if (!empty($request->ids) && count($request->ids) > 0) {
            foreach ($request->ids as $ids) {
                $enquiries = ContactUs::find($ids);
                if ($enquiries) {
                    $enquiries->delete();
                }
            }
            Session::flash('success', __('Deleted successfully'));
            return response()->json([
                'success' => true,
                'message' => 'Deleted successfully',
            ]);
        } else {
            return response()->json([
                'success' => false,
                'message' => 'Please select at least one',
            ]);
        }
    }
}
