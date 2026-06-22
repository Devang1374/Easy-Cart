<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class UserController extends Controller
{
    public function homePage(){
        return view('front.homePage');
    }

    public function product(){
        return view('front.product');
    }

    public function productDetails($slug){
        return view('front.productDetail', ['slug' => "$slug"]);
    }
    public function category(){
        return view('front.category');
    }
}
