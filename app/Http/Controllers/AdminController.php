<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class AdminController extends Controller
{
    public function category(){
        return view('category');
    }

    public function product(){
        return view('product');
    }

    public function orderPage(){
        return view('orderPage');
    }

    public function analytics(){
        return view('analyticsPage');
    }

    public function coupon(){
        return view('coupon');
    }

    public function banner(){
        return view('banner');
    }
}
