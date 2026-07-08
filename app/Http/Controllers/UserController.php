<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class UserController extends Controller
{
    public function homePage(){
        return view('front.homePage');
    }

    public function product(?string $category = null){
        return view('front.product', ['category' => "$category"]);
    }

    public function productDetails($slug){
        return view('front.productDetail', ['slug' => "$slug"]);
    }

    public function category(){
        return view('front.category');
    }

    public function cart(){
        return view('front.cart');
    }

    public function order(){
        return view('front.order');
    }

    public function wishlist(){
        return view('front.Wishlist');
    }

    public function checkout(?string $order_id = null){
        return view('front.checkout', ['order_id' => $order_id]);
    }

    public function orderSuccess($order_id){
        return view('front.order-success', ['order_id' => $order_id]);
    }
}
