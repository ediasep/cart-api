<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Cart;
use App\Models\Product;

class CartController extends Controller
{
    /**
     * @OA\Get(
     *     path="/api/carts/",
     *     @OA\Response(response="200", description="Success"),
     *     @OA\Parameter(in="query", name="user_id", description="You can find valid user_id from user api", required=true)
     * )
     */
    public function show_user_cart(Request $request)
    {
        // validate request
        $request->validate([
            'user_id' => 'required'
        ]);

        // select cart records from DB based on user_id
        $carts = \DB::table('carts')
                 ->join('products', 'products.id', '=', 'carts.product_id')
                 ->where('carts.user_id', $request->user_id)
                 ->select('carts.*', 'products.name', 'products.price')
                 ->get();

        return response()->json($carts);
    }

    /**
     * @OA\Post(
     *     path="/api/carts/add",
     *     @OA\Response(response="200", description="Success"),
     *     @OA\Parameter(in="query", name="user_id", required=true),
     *     @OA\Parameter(in="query", name="product_id", required=true),
     *     @OA\Parameter(in="query", name="quantity", description="You are only able to add item to cart if only the quantity not exceed stock reduced by reserved quantity", required=true)
     * )
     */
    public function add_to_cart(Request $request)
    {
        // validate request
        $request->validate([
            'user_id' => 'required',
            'product_id' => 'required',
            'quantity' => 'required'
        ]);

        // insert into database
        $cart = Cart::create($request->all());

        return response()->json($cart);
    }

    /**
     * @OA\Put(
     *     path="/api/carts/update",
     *     @OA\Response(response="200", description="Success"),
     *     @OA\Parameter(in="query", name="cart_id", required=true),
     *     @OA\Parameter(in="query", name="quantity", required=true)
     * )
     */
    public function update_cart(Request $request)
    {
        // validate request
        $request->validate([
            'cart_id' => 'required',
            'quantity' => 'required'
        ]);

        try {
            // get cart item by id
            $cart = Cart::find($request->cart_id);

            // update cart item
            $cart->update([
                'quantity' => intval($request->quantity)
            ]);
        } catch (Exception $e) {
            return response('Failed', 403);
        }

        return response()->json($cart);
    }

    /**
     * @OA\delete(
     *     path="/api/carts/",
     *     @OA\Response(response="200", description="Success"),
     *     @OA\Response(response="404", description="Cart item not found"),
     *     @OA\Response(response="403", description="Internal server error"),
     *     @OA\Parameter(in="query", name="cart_id", required=true),
     * )
     */
    public function remove_from_cart(Request $request)
    {
        $cart = Cart::find($request->cart_id);
        if(empty($cart)) {
            return response('Cart not found', 404);
        }
        
        try {
            $cart->delete();
        } catch (Exception $e) {
            return response('internal server error', 403);
        }

        return response('success', 200);    
    }

    /**
     * @OA\Patch(
     *     path="/api/carts/checkout",
     *     @OA\Response(response="200", description="Success"),
     *     @OA\Response(response="404", description="Cart item not found"),
     *     @OA\Response(response="403", description="Internal server error"),
     *     @OA\Parameter(in="query", name="cart_id", required=true),
     * )
     */
    public function checkout(Request $request)
    {
        $cart = Cart::find($request->cart_id);
        
        if(empty($cart)) {
            return response('Cart not found', 404);
        }

        $product = Product::find($cart->product_id);

        if(empty($product)) {
            return response('Product not found, probably it has been deleted', 404);
        }

        if($product->stock < intval($cart->quantity)) {
            return response('Product stock is not enough / less than quantity needed');
        }
        
        /* For this proof of concept, checkout process will only update the product stock and delete item from cart. In reality, a separate transaction table will exists and record will added to that table during checkout */
        try {
            $product->update([
                'stock' => $product->stock - intval($cart->quantity)
            ]);

            $cart->delete();
        } catch (Exception $e) {
            return response('internal server error', 403);
        }

        return response('success', 200);    
    }

}
