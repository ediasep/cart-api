<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Product;

class ProductController extends Controller
{
    /**
     * @OA\Get(
     *     path="/api/products/",
     *     @OA\Response(response="200", description="Success"),
     * )
     */    
    public function index()
    {
        // Select all products from DB
        $products = Product::all();

        return response()->json($products);
    }

    /**
     * @OA\Get(
     *     path="/api/products/show",
     *     @OA\Response(response="200", description="Success"),
     *     @OA\Response(response="404", description="Not found"),
     *     @OA\Parameter(in="query", name="id", required=true),
     * )
     */    
    public function show(Request $request)
    {
        // Validate
        $request->validate([    
            'id' => 'required'
        ]);

        // Find product by id
        $product = Product::find($request->id);

        if(empty($product)) {
            return response('Product not found', 404);
        }

        return response()->json($product);
    }

}
