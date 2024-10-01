<?php

namespace App\Http\Controllers\Api;

use App\Models\Api\Product;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Validator;
use App\Http\Resources\Api\ProductResource;

class ProductController extends Controller
{
    public function index()
    {
        $product = Product::all();

        if ($product) {
            return ProductResource::collection($product);
        } else {
            return response()->json([
                "error" => "product Not Found"
            ], status: 200);
        }
    }
    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            "name" => "required|string|max:255",
            "description" => "required",
            "price" => "required|integer",
            "image" => "required|image|mimes:jpeg,png,jpg,gif|max:2048"
        ]);

        if ($validator->fails()) {
            return response()->json([
                "message" => 'All fields required',
                "error" => $validator->errors()
            ], 422);
        }

        // Store the image file in the 'public/product' folder
        $imagePath = null;
        if ($request->hasFile('image')) {
            $image = $request->file('image');
            $imageName = time() . '_' . $image->getClientOriginalName();
            $image->move(public_path('product'), $imageName);
            $imagePath = 'product/' . $imageName;
        }

        $product = Product::create([
            "name" => $request->name,
            "description" => $request->description,
            "price" => $request->price,
            "image" => $imagePath
        ]);

        return response()->json([
            "success" => "Product Created Successfully",
            "data" => new ProductResource($product)
        ]);
    }



    public function show(Product $product)
    {
        return new ProductResource($product);
    }
    public function update(Request $request, Product $product)
    {
        $validator = Validator::make($request->all(), [
            "name" => "required|string|max:255",
            "description" => "required",
            "price" => "required|integer",
            "image" => "sometimes|image|mimes:jpeg,png,jpg,gif|max:2048"
        ]);

        if ($validator->fails()) {
            return response()->json([
                "message" => 'All fields required',
                "error" => $validator->errors()
            ], 422);
        }
        if ($request->hasFile('image')) {
            // Delete the old image (optional)
            if ($product->image && file_exists(public_path($product->image))) {
                unlink(public_path($product->image));
            }

            // Upload new image
            $image = $request->file('image');
            $imageName = time() . '_' . $image->getClientOriginalName();
            $image->move(public_path('product'), $imageName);  // Move file to public/product directory
            $product->image = 'product/' . $imageName;  // Store relative path in database
        }

        // Update the product details
        $product->update([
            "name" => $request->name,
            "description" => $request->description,
            "price" => $request->price,
            "image" => $product->image ?? null
        ]);

        return response()->json([
            "success" => "Product Updated Successfully",
            "data" => new ProductResource($product)
        ]);
    }




public function destroy(Product $product)
{
    // Check if the product has an image
    if ($product->image) {
        $imagePath = public_path($product->image); // Get the full path of the image
        
        // Check if the image file exists, and delete it
        if (file_exists($imagePath)) {
            unlink($imagePath); // Delete the image
        }
    }

    // Delete the product record from the database
    $product->delete();

    return response()->json([
        "success" => "Product and associated image deleted successfully"
    ]);
}

}
