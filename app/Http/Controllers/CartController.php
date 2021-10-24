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

        // get related product by product id
        $product = Product::find($request->product_id);

        if($product->stock < $request->quantity) {
            return response('Stock not available, try lower product quantity', 400);
        }

        try {
            // insert into database
            $cart = Cart::create(array_merge($request->all(), ['product_version' => $product->version, 'product_stock' => $product->stock]));

        } catch (Exception $e) {
            
        }

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

            // check if cart exists
            if(empty($cart)) {
                return response('Cart not found', 404);
            }

            // update cart item
            $cart->update([
                'quantity' => intval($request->quantity)
            ]);
        } catch (Exception $e) {
            return response('Internal server error', 500);
        }

        return response()->json($cart);
    }

    /**
     * @OA\delete(
     *     path="/api/carts/",
     *     @OA\Response(response="200", description="Success"),
     *     @OA\Response(response="404", description="Cart item not found"),
     *     @OA\Response(response="500", description="Internal server error"),
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
            return response('internal server error', 500);
        }

        return response('success', 200);    
    }

    /**
     * @OA\Patch(
     *     path="/api/carts/checkout",
     *     @OA\Response(response="200", description="Success"),
     *     @OA\Response(response="404", description="Cart item not found"),
     *     @OA\Response(response="500", description="Internal server error"),
     *     @OA\Response(response="409", description="Resource conflict, out of stock"),
     *     @OA\Parameter(in="query", name="cart_id", required=true),
     * )
     */
    public function checkout(Request $request)
    {
        $cart = Cart::find($request->cart_id);
        
        if(empty($cart)) {
            return response('Cart not found', 404);
        }

        $product = Product::find($cart->product_id); // For validation only
        if(empty($product)) {
            return response('Product not found, probably it has been deleted', 404);
        }
        
        /* For this proof of concept, checkout process will only update the product stock and delete item from cart. In reality, a separate transaction table will exists and record will added to that table during checkout */

        try {
            $affected = Product::where('id', '=', $cart->product_id)
                  ->where('stock', '=', $cart->product_stock)
                  ->where('version', '=', $cart->product_version)
                  // sometimes users hold cart for long time. In that case, it's convenient to use below filter instead
                  // ->where('stock' , '>=', $cart->quantity)
                  ->update([
                        'stock' => $product->stock - intval($cart->quantity),
                        'version' => $product->version + 1
                    ]);

            // affected 0 mean there is no record updated, it will be caused by product out of stock
            if($affected == 0) {
                return response('checkout failed, expired data', 409);
            }

            $cart->delete();
        } catch (Exception $e) {
            return response('internal server error', 500);
        }

        return response('success', 200);    
    }

}
