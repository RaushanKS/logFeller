<?php

namespace App\Http\Controllers;

use Carbon\Carbon;
use App\Models\Products;
use Illuminate\Support\Str;
use App\Models\ProductImage;
use Illuminate\Http\Request;
use App\Models\ProductVariant;
use Illuminate\Support\Facades\Session;
use Illuminate\Support\Facades\Validator;

class ProductsController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
    }

    public function index()
    {
        $totalRecords = Products::count();
        $activated = Products::where('status', 1)->count();
        $inactivated = Products::where('status', 0)->count();
        $twoDaysAgo = Carbon::now()->subDays(30);

        $recentProducts = Products::where('created_at', '>=', $twoDaysAgo)->count();

        return view('products-list', compact(['totalRecords', 'activated', 'inactivated', 'recentProducts']));
    }

    public function create(Request $request)
    {
        return view('products-create');
    }

    public function slug($string, $separator = '-')
    {
        if (is_null($string)) {
            return "";
        }

        $string = trim($string);

        $string = mb_strtolower($string, "UTF-8");

        $string = preg_replace("/[^a-z0-9_\sءاأإآؤئبتثجحخدذرزسشصضطظعغفقكلمنهويةى]#u/", "", $string);

        $string = preg_replace("/[\s-]+/", " ", $string);

        $string = preg_replace("/[\s_]/", $separator, $string);

        return $string;
    }

    public function store(Request $request)
    {
        if ($request->has('has_variants')) {
            $rules = [
                'product_name' => 'required|string',
                'product_description' => 'required|string',
                'product_status' => 'required|in:0,1',
                'product_image.*' => 'required|image|mimes:jpeg,png,jpg,gif,svg|max:10240', 
                'variant_name.*' => 'required|string',
                'variant_price.*' => 'required|numeric',
            ];
        } else {
            $rules = [
                'product_name' => 'required|string',
                'product_price' => 'required|numeric',
                'product_description' => 'required|string',
                'product_status' => 'required|in:0,1',
                'product_image.*' => 'required|image|mimes:jpeg,png,jpg,gif,svg|max:10240', 
            ];
        }

        $customMessages = [
            'required' => 'The :attribute field is required.',
            'product_image.*.image' => 'The :attribute must be an image.',
            'product_image.*.mimes' => 'The :attribute must be a file of type: jpeg, png, jpg, gif, svg.',
            'product_image.*.max' => 'The :attribute may not be greater than :max kilobytes.',
        ];

        $validator = Validator::make($request->all(), $rules, $customMessages);

        if ($validator->fails()) {
            return redirect()->back()->withErrors($validator)->withInput();
        }

        if ($request->hasFile('product_image')) {
            $imageData = [];

            foreach ($request->file('product_image') as $file) {
                $extention = $file->getClientOriginalExtension();
                $filename = time() . '_' . rand(1000, 9999) . '.' . $extention;
                $path = 'uploads/products/';

                if (!file_exists($path)) {
                    mkdir($path, 0777, true);
                }

                $filePath = public_path($path);
                $file->move($filePath, $filename);
                $imageData[] = $path . $filename;
            }
        }

        if ($request->has('has_variants')) {
            $product = new Products();
            $product->name = $request->input('product_name');
            $product->slug = Str::slug($request->input('product_name'));
            $product->description = $request->input('product_description');
            $product->has_variant = 1;
            $product->status = $request->input('product_status');
            $product->is_important = ($request->input('product_important')) ? $request->input('product_important') : 0;

            $product->save();

            // Variants
            foreach ($request->input('variant_name') as $index => $variantName) {
                $variant = new ProductVariant();
                $variant->product_id = $product->id;
                $variant->name = $variantName;
                $variant->sale_price = $request->input('variant_price')[$index];
                $variant->save();
            }   

        } else {
            $product = new Products();
            $product->name = $request->input('product_name');
            $product->slug = Str::slug($request->input('product_name'));
            $product->sale_price = $request->input('product_price');
            $product->description = $request->input('product_description');
            $product->has_variant = 0;
            $product->status = $request->input('product_status');
            $product->is_important = ($request->input('product_important')) ? $request->input('product_important') : 0;
            $product->save();
        }

        if($product) {
            $productId = $product->id;

            foreach ($imageData as $imagePath) {
                ProductImage::create([
                    'product_id' => $productId,
                    'image_path' => $imagePath,
                ]);
            }

            return redirect()->back()->with('success', 'Product saved successfully!');
        } else {
            return redirect()->back()->with('error', 'Something went wrong please try again!');
        }

    }

    public function getProducts(Request $request)
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
        $totalRecords = Products::select('count(*) as allcount');
        if ($columnName_arr[4]['search']['value'] != '') {
            $totalRecords = $totalRecords->where('status', $columnName_arr[4]['search']['value']);
        }
        $totalRecords = $totalRecords->count();

        $totalRecordsWithFilter = Products::select('count(*) as allcount')->where(function ($query) use ($searchValue) {
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
        $records = Products::orderBy($columnName, $columnSortOrder)
            ->where(function ($query) use ($searchValue) {
                $query->where('products.name', 'LIKE', '%' . $searchValue . '%');
            });
        if ($columnName_arr[4]['search']['value'] != '') {
            $records = $records->where('status', $columnName_arr[4]['search']['value']);
        }

        $records = $records->select('products.*')
        ->skip($start)
            ->take($rowPerPage)
            ->get();
        $data_arr = array();
        $sno = $start + 1;
        foreach ($records as $record) {
            $id = $record->id;
            $name = $record->name;
            $price = $record->sale_price;
            $imageRecord = ProductImage::where('product_id', $record->id)->select('image_path')->first();
            $image = $imageRecord ? $imageRecord->image_path : null;
            // print_r($image);exit;
            // $description = $record->description;
            $description = $this->limitWords($record->description, 5);

            $status = $record->status;

            $data_arr[] = array(
                "id" => $id,
                "name" => $name,
                "price" => $price,
                "image" => $image,
                "description" => $description,
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


    /**
     * Limit a string to a specified number of words.
     *
     * @param string $string
     * @param int $wordLimit
     * @return string
     */
    private function limitWords($string, $wordLimit)
    {
        $words = explode(' ', $string);
        if (count($words) > $wordLimit) {
            return implode(' ', array_slice($words, 0, $wordLimit)) . '...';
        }
        return $string;
    }

    public function edit($id)
    {
        $product = Products::where('id', $id)->first();
        $productImages = ProductImage::where('product_id', $id)->get();
        $productVariant = ProductVariant::where('product_id', $id)->get();

        return view('product-edit', compact(['product', 'productImages', 'productVariant']));
    }

    public function update(Request $request, $id)
    {
        if ($request->has('has_variants')) {
            $rules = [
                'product_name' => 'required|string',
                'product_description' => 'required|string',
                'product_status' => 'required|in:0,1',
                // 'product_image.*' => 'required|image|mimes:jpeg,png,jpg,gif,svg|max:10240',
                'variant_name.*' => 'required|string',
                'variant_price.*' => 'required|numeric',
            ];
        } else {
            $rules = [
                'product_name' => 'required|string',
                'product_price' => 'required|numeric',
                'product_description' => 'required|string',
                'product_status' => 'required|in:0,1',
                // 'product_image.*' => 'required|image|mimes:jpeg,png,jpg,gif,svg|max:10240',
            ];
        }

        $customMessages = [
            'required' => 'The :attribute field is required.',
            // 'product_image.*.image' => 'The :attribute must be an image.',
            // 'product_image.*.mimes' => 'The :attribute must be a file of type: jpeg, png, jpg, gif, svg.',
            // 'product_image.*.max' => 'The :attribute may not be greater than :max kilobytes.',
        ];

        $validator = Validator::make($request->all(), $rules, $customMessages);

        if ($validator->fails()) {
            return redirect()->back()->withErrors($validator)->withInput();
        }

        //unique slug
        $prod = Products::where('id', $id)->first();
        $inputSlug = Str::slug($request->input('product_name'));
        $uniqueSlug = $inputSlug;

        $counter = 1;
        while (Products::where('slug', $uniqueSlug)->where('id', '!=', $prod->id)->exists()) {
            $uniqueSlug = $inputSlug . '-' . $counter;
            $counter++;
        }


        // Display Images
        $imageData = [];
        if ($request->hasFile('product_image')) {
            foreach ($request->file('product_image') as $file) {
                $extension = $file->getClientOriginalExtension();
                $filename = time() . '_' . rand(1000, 9999) . '.' . $extension;
                $path = 'uploads/products/';
                $file->move(public_path($path), $filename);
                $imageData[] = $path . $filename;
            }
        }


        if ($request->has('has_variants')) {
            $product = Products::findOrFail($id);
            $product->name = $request->input('product_name');
            $product->slug = $uniqueSlug;
            $product->sale_price = $request->input('product_price');
            $product->description = $request->input('product_description');
            $product->has_variant = 1;
            $product->status = $request->input('product_status');
            $product->is_important = ($request->input('product_important')) ? $request->input('product_important') : 0;

            $product->save();

            if($product->save()) {
                //update product specification
                ProductVariant::where('product_id', $product->id)->forceDelete();
                foreach ($request->input('variant_name', []) as $key => $variantText) {
                    $details = $request->input('variant_price')[$key] ?? null;
                    if (!empty($variantText)) {
                        ProductVariant::updateOrCreate([
                            'product_id' => $product->id,
                            'name' => $variantText,
                            'sale_price' => $details,
                        ]);
                    }
                }
            }
        } else {
            $product = Products::findOrFail($id);
            $product->name = $request->input('product_name');
            $product->slug = $uniqueSlug;
            $product->sale_price = $request->input('product_price');
            $product->description = $request->input('product_description');
            $product->has_variant = 0;
            $product->status = $request->input('product_status');
            $product->is_important = ($request->input('product_important')) ? $request->input('product_important') : 0;
            $product->save();

            if ($product->save()) {
                ProductVariant::where('product_id', $product->id)->forceDelete();
            }
        }

        if ($product) {
            $productId = $product->id;

            if (!empty($imageData)) {
                foreach ($imageData as $imagePath) {
                    ProductImage::create([
                        'product_id' => $productId,
                        'image_path' => $imagePath,
                    ]);
                }
            }


            return redirect()->back()->with('success', 'Product Updated successfully!');
        } else {
            return redirect()->back()->with('error', 'Something went wrong please try again!');
        }
    }

    public function onlineProductsAll(Request $request, $status)
    {
        if ($status == 1) {
            $message = 'Success. Product is active now';
        } else {
            $message = 'Success. Product is in-active now';
        }
        if (!empty($request->ids) && count($request->ids) > 0) {
            foreach ($request->ids as $ids) {
                $categories = Products::find($ids);
                $categories->status = $status;
                $categories->save();
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

    public function deleteProductsAll(Request $request)
    {
        if (!empty($request->ids) && count($request->ids) > 0) {
            foreach ($request->ids as $ids) {
                $product = Products::find($ids);
                if ($product) {
                    $productImages = ProductImage::where('product_id', $ids)->get();
                    foreach ($productImages as $prodImage) {
                        $imagePath = public_path($prodImage->image_path);
                        if (file_exists($imagePath)) {
                            @unlink($imagePath); 
                        }
                        $prodImage->forceDelete();
                    }

                    ProductVariant::where('product_id', $ids)->forceDelete();

                    $product->delete();
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

    public function destroy($id)
    {
        $product = Products::findOrFail($id);

        if (!empty($product)) {
            $productImages = ProductImage::where('product_id', $id)->get();
            foreach ($productImages as $prodImage) {
                $imagePath = public_path($prodImage->image_path);
                if (file_exists($imagePath)) {
                    @unlink($imagePath);
                }
                $prodImage->forceDelete();
            }

            ProductVariant::where('product_id', $id)->forceDelete();

            $product->delete();
            Session::flash('success', __('Product delete successfully'));
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

    //product image delete
    public function destroyImage(Request $request)
    {
        $serviceId = $request->id;
        $imagePath = $request->imagePath;

        $service = ProductImage::where('id', $serviceId)->first();
        if (!empty($service)) {
            unlink($service->image_path);
        }

        $service->delete();

        if ($service) {
            return response()->json(['status' => 'success', 'message' => 'Deleted successfully'], 200);
        } else {
            return response()->json(['status' => 'failed', 'message' => 'Something went wrong! Try again.'], 200);
        }
    }
}
