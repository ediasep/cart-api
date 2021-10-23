<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     *
     * @return void
     */
    public function run()
    {
        /* Generate cart records along with it's product and user.

        See definition at : database/factories/CartFactory.php */

        \App\Models\Cart::factory(100)->create();
    }
}
