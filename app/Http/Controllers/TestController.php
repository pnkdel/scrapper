<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class TestController extends Controller
{
    public function testApi() {
        return response()->json([
            'my_data' => 'Test'
         ]);
    }
}
