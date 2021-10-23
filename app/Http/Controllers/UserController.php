<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\User;

class UserController extends Controller
{
    /**
     * @OA\Get(
     *     path="/api/users/",
     *     @OA\Response(response="200", description="Display all users"),
     * )
     */
    public function index()
    {
        // select all users and return as JSON
        return response()->json(User::all());
    }

}
