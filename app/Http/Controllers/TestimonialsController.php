<?php

namespace App\Http\Controllers;

use App\Models\Testimonials;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Session;
use Illuminate\Support\Facades\Validator;

class TestimonialsController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
    }

    public function index()
    {
        return view('testimonials');
    }

    public function create(Request $request)
    {
        return view('testimonials-add');
    }

    public function store(Request $request)
    {
        $input = $request->all();

        $rules = [
            'user_name' => 'required',
            'user_rating' => 'required|integer|min:1|max:5',
            'testimonials_image' => 'required|image|mimes:jpg,jpeg,png,svg,gif|max:2048',
            'testimonial_desc' => 'required',
        ];

        $customMessages = [
            'required' => 'The :attribute field is required.',
        ];
        $validator = Validator::make($request->all(), $rules, $customMessages);
        if ($validator->fails()) {
            Session::flash('error', __($validator->errors()->first()));
            return redirect()->back()->with('error', $validator->errors()->first());
        }

        //single display image 
        if ($request->file('testimonials_image')) {

            $image = $request->file('testimonials_image');
            $name = time() . '.' . $image->getClientOriginalExtension();

            $path = 'uploads/testimonials/';
            if (!file_exists($path)) {
                mkdir($path, 0777, true);
            }

            $destinationPath = public_path($path);
            $image->move($destinationPath, $name);
            $featuredImage = $path . $name;
        } else {
            $featuredImage = 'assets/img/avatars/5.png';
        }

        $discounts = Testimonials::create([
            'name' => $input['user_name'],
            'ratings' => $input['user_rating'],
            'image' => $featuredImage,
            'message' => $input['testimonial_desc'],
        ]);
        if ($discounts) {
            return redirect()->back()->with('success', 'Testimonial added successfully');
        } else {
            return redirect()->back()->with('error', 'something went wrong please try again!');
        }
    }

    public function getTestimonials(Request $request)
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
        $totalRecords = Testimonials::select('count(*) as allcount');
        if ($columnName_arr[4]['search']['value'] != '') {
            $totalRecords = $totalRecords->where('status', $columnName_arr[4]['search']['value']);
        }
        $totalRecords = $totalRecords->count();

        $totalRecordsWithFilter = Testimonials::select('count(*) as allcount')->where(function ($query) use ($searchValue) {
            $query->where('name', 'LIKE', '%' . $searchValue . '%');
        });
        if ($columnName_arr[4]['search']['value'] != '') {
            $totalRecordsWithFilter = $totalRecordsWithFilter->where('status', $columnName_arr[4]['search']['value']);
        }

        $totalRecordsWithFilter = $totalRecordsWithFilter->count();

        if (empty($searchValue)) {
            $columnName = 'created_at';
            $columnSortOrder = 'desc';
        }

        // Fetch records
        $records = Testimonials::orderBy($columnName, $columnSortOrder)
            ->where(function ($query) use ($searchValue) {
                $query->where('testimonials.name', 'LIKE', '%' . $searchValue . '%');
            });
        if ($columnName_arr[4]['search']['value'] != '') {
            $records = $records->where('status', $columnName_arr[4]['search']['value']);
        }

        $records = $records->select('testimonials.*')
        ->skip($start)
            ->take($rowPerPage)
            ->get();
        $data_arr = array();
        $sno = $start + 1;
        foreach ($records as $record) {
            $id = $record->id;
            $name = $record->name;
            $rating = $record->ratings;
            // $image = ProductImage::where('product_id', $record->id)->select('image_path')->first();
            $image = ($record->image) ? $record->image : null;
            // print_r($image);exit;
            $message = $record->message;

            $status = $record->status;

            $data_arr[] = array(
                "id" => $id,
                "name" => $name,
                "rating" => $rating,
                "image" => $image,
                "message" => $message,
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
        $testimonial = Testimonials::where('id', $id)->first();

        return view('testimonial-edit', compact(['testimonial']));
    }

    public function update(Request $request, $id)
    {
        $input = $request->all();

        $rules = [
            'user_name' => 'required',
            'user_rating' => 'required|integer|min:1|max:5',
            // 'testimonials_image' => 'required|image|mimes:jpg,jpeg,png,svg,gif|max:2048',
            'testimonial_desc' => 'required',
        ];

        $customMessages = [
            'required' => 'The :attribute field is required.',
        ];
        $validator = Validator::make($request->all(), $rules, $customMessages);
        if ($validator->fails()) {
            Session::flash('error', __($validator->errors()->first()));
            return redirect()->back()->with('error', $validator->errors()->first());
        }

        //single display image 
        $imagePath = $input['oldTestimonials_image'];
        if ($request->hasFile('testimonials_image')) {
            $rules = [
                'testimonials_image' => 'required|image|mimes:jpg,jpeg,png|max:2048',
            ];

            $validator = Validator::make($request->all(), $rules, $customMessages);
            if ($validator->fails()) {
                return redirect()->back()->withErrors($validator)->withInput();
            }

            $image = $request->file('testimonials_image');
            $name = time() . '.' . $image->getClientOriginalExtension();
            $path = 'uploads/testimonials/';
            $image->move(public_path($path), $name);
            $imagePath = $path . $name;
        }

        // $discounts = Testimonials::create([
        //     'name' => $input['user_name'],
        //     'ratings' => $input['user_rating'],
        //     'image' => $imagePath,
        //     'message' => $input['testimonial_desc'],
        // ]);
        $testimonial = Testimonials::findOrFail($id);
        $testimonial->name = $input['user_name'];
        $testimonial->ratings = $input['user_rating'];
        $testimonial->image = $imagePath;
        $testimonial->message = $input['testimonial_desc'];
        
        if ($testimonial->save()) {
            return redirect()->back()->with('success', 'Testimonial updated successfully');
        } else {
            return redirect()->back()->with('error', 'something went wrong please try again!');
        }
    }

    public function onlineTestimonialsAll(Request $request, $status)
    {
        if ($status == 1) {
            $message = 'Success. Activated successfully';
        } else {
            $message = 'Success. In-activated successfully';
        }
        if (!empty($request->ids) && count($request->ids) > 0) {
            foreach ($request->ids as $ids) {
                $testimonials = Testimonials::find($ids);
                $testimonials->status = $status;
                $testimonials->save();
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

    public function deleteTestimonialsAll(Request $request)
    {
        if (!empty($request->ids) && count($request->ids) > 0) {
            foreach ($request->ids as $ids) {
                $testimonials = Testimonials::find($ids);
                if ($testimonials) {
                    // $productImages = ProductImage::where('product_id', $ids)->get();
                    // foreach ($productImages as $prodImage) {
                    //     $imagePath = public_path($prodImage->image_path);
                    //     if (file_exists($imagePath)) {
                    //         @unlink($imagePath);
                    //     }
                    //     $prodImage->forceDelete();
                    // }

                    // ProductVariant::where('product_id', $ids)->forceDelete();

                    $testimonials->delete();
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

    public function destroy($id)
    {
        $testimonial = Testimonials::findOrFail($id);

        if (!empty($testimonial)) {
            // $productImages = ProductImage::where('product_id', $id)->get();
            // foreach ($productImages as $prodImage) {
            //     $imagePath = public_path($prodImage->image_path);
            //     if (file_exists($imagePath)) {
            //         @unlink($imagePath);
            //     }
            //     $prodImage->forceDelete();
            // }

            // ProductVariant::where('product_id', $id)->forceDelete();

            $testimonial->delete();
            Session::flash('success', __('Testimonial delete successfully'));
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
