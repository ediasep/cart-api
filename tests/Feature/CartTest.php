<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;
use App\Models\Cart;
use App\Models\User;
use App\Models\Product;

class CartTest extends TestCase
{
    /**
     * Test when cart data requested version is included in json
     *
     * @return void
     */
    public function test_when_cart_data_requested_version_is_included_in_json()
    {
        $user = User::factory()->create();
        $cart = Cart::factory()->create(['user_id' => $user->id]);

        $response = $this->get('/api/carts/?user_id='.$cart->user_id);

        // assert that json response contain product_version
        $response->assertJsonFragment(['product_version' => $cart->product_version]);
    }

    /**
     * Test when checkout success product version is incremented by 1
     *
     * @return void
     */
    public function test_when_checkout_product_version_is_incremented_by_1()
    {
        $product = Product::factory()->create();
        $cart = Cart::factory()->create(['product_id' => $product->id, 'product_version' => $product->version, 'product_stock' => $product->stock]);

        $this->patch('/api/carts/checkout', ['cart_id' => $cart->id]);

        $response = $this->get('/api/products/show/?id='.$product->id);
        $response->assertJsonFragment(['version' => $product->version + 1]);
    }

    /**
     * Test prevent overselling
     * Test when product version match checkout should success
     * Test when product version doesn't match checkout should fail
     * @return void
     */
    public function test_when_product_version_doesnt_match_checkout_should_fail()
    {
        $product = Product::factory()->create(['stock' => 2]);
        $cart = Cart::factory()->create(['product_id' => $product->id, 'product_version' => $product->version, 'product_stock' => $product->stock, 'quantity' => 2]);

        $cart2 = Cart::factory()->create(['product_id' => $product->id, 'product_version' => $product->version, 'product_stock' => $product->stock, 'quantity' => 2]);

        // In order to test this, checkout should executed twice, from different cart

        // Checkout 1
        $response1 = $this->patch('/api/carts/checkout', ['cart_id' => $cart->id]);

        // Checkout 2
        $response2 = $this->patch('/api/carts/checkout', ['cart_id' => $cart2->id]);

        // Checkout from cart 1 should be success because version match
        $response1->assertStatus(200);

        // Checkout from cart 2 should be failed because version doesn't match / version already updated by checkout 1
        $response2->assertStatus(409);
    }

}
