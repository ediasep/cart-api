<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Cart extends Model
{
    use HasFactory;

    protected $fillable = ['user_id', 'product_id', 'quantity', 'product_version', 'product_stock'];

    /**
     * Get the user that owns the cart.
     */
    public function User()
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Get the product that added to the cart
     */
    public function Product()
    {
        return $this->belongsTo(Product::class);
    }
}
