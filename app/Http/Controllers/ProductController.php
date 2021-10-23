<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Product;

class ProductController extends Controller
{
    /**
     * @OA\Get(
     *     path="/api/products/",
     *     @OA\Response(response="200", description="Display all products"),
     * )
     */    
    public function index()
    {
        // Select all products from DB
        $products = Product::all();

        return response()->json($products);
    }
}
