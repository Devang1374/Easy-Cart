<?php

use Livewire\Component;

use App\Models\Coupon;
use App\Models\product;
use App\Models\orderItems;
use App\Models\orderTable;
use App\Models\UserAddress;

use Illuminate\Support\Facades\DB;

use Livewire\Attributes\On;  

use App\Mail\OrderPlacedMail;
use Illuminate\Support\Facades\Mail;

new class extends Component
{
    public string $first_name = '';
    public string $last_name = '';

    public string $email = '';
    public string $phone = '';

    public string $address = '';
    public string $city = '';

    public string $state = '';
    public string $pincode = '';

    public $cart = [];
    public $paymentSessionId;

    public $order_id;

    public $addresses = [];
    public $selectedAddress = null;
    public string $title = 'Home';
    public string $customTitle = '';

    public function mount()
    {
        $user = auth()->user();

        $this->first_name = $user->name;
        $this->email = $user->email;

        $this->addresses = UserAddress::where('user_id', auth()->id())
            ->orderByDesc('is_default')
            ->get();
        
        $this->cart = session('cart', []);

        if(!empty($this->order_id)){
            $items = orderItems::where('order_table_id', $this->order_id)->get();
            $order = orderTable::where('id', $this->order_id)->first();

            $this->first_name = $order['first_name'];
            $this->last_name = $order['last_name'];

            $this->email = $order['email'];
            $this->phone = $order['phone'];

            $this->address = $order['address'];
            $this->city = $order['city'];
            
            $this->pincode = $order['pincode'];
            $this->state = $order['state'];

            $this->appliedCoupon = $order->coupon;
            if ($order->coupon) {
            
                $coupon = $order->coupon;
            
                if (
                    $coupon->is_active &&
                    (!$coupon->starts_at || now()->gte($coupon->starts_at)) &&
                    (!$coupon->expires_at || now()->lte($coupon->expires_at))
                ) {
                    $this->appliedCoupon = $coupon;
                    $this->couponCode = $coupon->code;
                } else {
                    // Coupon became invalid while order was pending
                    $this->appliedCoupon = null;
                    $this->couponCode = '';
            
                    $order->update([
                        'coupon_id' => null,
                        'coupon_code' => null,
                        'discount_amount' => 0,
                        'total_amount' => $this->cartTotal,
                    ]);
                }
            }

            foreach($items as $item){
                $product = product::where('id', $item['product_id'])->first();

                $this->cart[$item['id']] = [
                    'id'       => $product->id,
                    'name'     => $product->name,
                    'slug'     => $product->slug,
                    'price'    => $product->price,
                    'quantity' => $item['quantity'],
                    'image'    => $product->images->first()?->image,
                ];
            }
        }

        if (empty($this->cart)) {
            return redirect()->route('user/product');
        }
    }

    public function getCartTotalProperty()
    {
        if(!empty($this->order_id)){
            $order_items = orderItems::where('order_table_id', $this->order_id)->get();
            $total = 0;
            foreach($order_items as $item){
                $total += $item['price'] * $item['quantity'];
            }
            return $total;
        }else{
            return collect($this->cart)
                ->sum(function ($item) {
                    $productPrice = product::where('id', $item['id'])->value('price');
                    if($item['price'] != $productPrice){
                        $item['price'] = $productPrice;
                        $this->cart[$item['id']]['price']= $productPrice;
                        session()->put('cart', $this->cart);
                        $this->dispatch('product-update');
                    }
                    return $item['price'] * $item['quantity'];
                });
        }
    }

    public function placeOrder()
    {
        logger()->info([
            'couponCode' => $this->couponCode,
            'appliedCoupon' => $this->appliedCoupon?->code,
            'discount' => $this->discount,
            'finalTotal' => $this->finalTotal,
        ]);
        try{
        $this->validate();

        if (empty($this->cart)) {

            Flux::toast(
                variant: 'danger',
                heading: 'Cart Empty',
                text: 'Please add products before checkout.'
            );

            return;
        }

        foreach ($this->cart as $item) {

            $product = Product::find($item['id']);

            if (! $product) {

                Flux::toast(
                    variant: 'danger',
                    heading: 'Product Missing',
                    text: 'A product is no longer available.'
                );

                return;
            }

            if ($product->stock < $item['quantity']) {

                Flux::toast(
                    variant: 'danger',
                    heading: 'Insufficient Stock',
                    text: "{$product->name} no longer has enough stock."
                );

                return;
            }
        }

        DB::transaction(function () {
            if(empty($this->order_id)){
            $order = orderTable::create([
                'user_id' => auth()->id(),
            
                'order_number' => $this->generateOrderNumber(),
            
                'first_name' => $this->first_name,
                'last_name'  => $this->last_name,
            
                'email' => $this->email,
                'phone' => $this->phone,
            
                'address' => $this->address,
                'city'    => $this->city,
                'state'   => $this->state,
                'pincode' => $this->pincode,
            
                'total_amount' => $this->finalTotal,

                'coupon_id' => $this->appliedCoupon?->id,

                'coupon_code' => $this->appliedCoupon?->code,

                'discount_amount' => $this->discount,
            
                'status' => 'pending',
            ]);

            foreach ($this->cart as $item) {

                orderItems::create([
                    'order_table_id' => $order['id'],

                    'product_id' => $item['id'],

                    'product_name' => $item['name'],

                    'price' => $item['price'],

                    'quantity' => $item['quantity'],

                    'subtotal' => $item['price'] * $item['quantity'],
                ]);

                // $product = Product::find($item['id']);

                // $product->decrement(
                //     'stock',
                //     $item['quantity']
                // );
            }

            // session()->forget('cart');

            $order->load('items');

            Mail::to($order->email)
                ->queue(
                    new OrderPlacedMail($order)
            );

            // $curl = curl_init();

            // curl_setopt_array($curl, [
            //     CURLOPT_URL => "https://sandbox.cashfree.com/pg/orders",
            //     CURLOPT_RETURNTRANSFER => true,
            //     CURLOPT_ENCODING => "",
            //     CURLOPT_MAXREDIRS => 10,
            //     CURLOPT_TIMEOUT => 30,
            //     CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
            //     CURLOPT_CUSTOMREQUEST => "POST",
            //     CURLOPT_POSTFIELDS => json_encode([
            //         'order_id' => $order->order_number,
            //         'order_currency' => 'INR',
            //         'order_amount' => (float) $order->total_amount,

            //         'customer_details' => [
            //             'customer_id' => (string) auth()->id(),
            //             'customer_name' => $order->first_name . ' ' . $order->last_name,
            //             'customer_email' => $order->email,
            //             'customer_phone' => $order->phone,
            //         ],

            //         'order_meta' => [
            //             'return_url' => route(
            //                 'user/order-success',
            //                 ['order_id' => $order->order_number]
            //             ),
            //         ],
            //     ]),
            //     CURLOPT_HTTPHEADER => [
            //         "Content-Type: application/json",
            //         "x-api-version: 2025-01-01",
            //         "x-client-id: " . env('CASHFREE_APP_ID'),
            //         "x-client-secret: " . env('CASHFREE_SECRET_KEY'),
            //     ],
            // ]);

            // $response = curl_exec($curl);
            // $error = curl_error($curl);

            // curl_close($curl);

            // if ($error) {
            //     dd($error);
            // }

            // $result = json_decode($response, true);

            // $order->update([
            //     'cf_payment_id' => $result['payment_session_id']
            // ]); 

            }else{
                if($this->couponEdited){
                    $order = orderTable::where('id', $this->order_id)->first();

                    $order->update([
                        "order_number" => $this->generateOrderNumber(),
                        'total_amount' => $this->finalTotal,
                    ]);

                    // $curl = curl_init();

                    // curl_setopt_array($curl, [
                    //     CURLOPT_URL => "https://sandbox.cashfree.com/pg/orders",
                    //     CURLOPT_RETURNTRANSFER => true,
                    //     CURLOPT_ENCODING => "",
                    //     CURLOPT_MAXREDIRS => 10,
                    //     CURLOPT_TIMEOUT => 30,
                    //     CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
                    //     CURLOPT_CUSTOMREQUEST => "POST",
                    //     CURLOPT_POSTFIELDS => json_encode([
                    //         'order_id' => $order->order_number,
                    //         'order_currency' => 'INR',
                    //         'order_amount' => (float) $order->total_amount,

                    //         'customer_details' => [
                    //             'customer_id' => (string) auth()->id(),
                    //             'customer_name' => $order->first_name . ' ' . $order->last_name,
                    //             'customer_email' => $order->email,
                    //             'customer_phone' => $order->phone,
                    //         ],

                    //         'order_meta' => [
                    //             'return_url' => route(
                    //                 'user/order-success',
                    //                 ['order_id' => $order->order_number]
                    //             ),
                    //         ],
                    //     ]),
                    //     CURLOPT_HTTPHEADER => [
                    //         "Content-Type: application/json",
                    //         "x-api-version: 2025-01-01",
                    //         "x-client-id: " . env('CASHFREE_APP_ID'),
                    //         "x-client-secret: " . env('CASHFREE_SECRET_KEY'),
                    //     ],
                    // ]);

                    // $response = curl_exec($curl);
                    // $error = curl_error($curl);

                    // curl_close($curl);

                    // if ($error) {
                    //     dd($error);
                    // }

                    // $result = json_decode($response, true);

                    // orderTable::where('id', $order->id)->update([
                    //     'cf_payment_id' => $result['payment_session_id']
                    // ]);
                }
                $order = orderTable::where('id', $this->order_id)->first();
                $order->update([
                    'first_name' => $this->first_name,
                    'last_name'  => $this->last_name,

                    'email' => $this->email,
                    'phone' => $this->phone,

                    'address' => $this->address,
                    'city'    => $this->city,
                    'state'   => $this->state,
                    'pincode' => $this->pincode,

                    'total_amount' => $this->finalTotal,

                    'coupon_id' => $this->appliedCoupon?->id,

                    'coupon_code' => $this->appliedCoupon?->code,

                    'discount_amount' => $this->discount,
                ]);
            }

            session()->forget('cart');
            
            $this->paymentSessionId = $order['order_number'];

            $order->update([
                'pyment' => 'PAID',
                'order_status' => 'paid'
            ]);
            return redirect()->route(
                    'user/order-success',
                    ['order_id' => $this->paymentSessionId]
                    );
            //$this->dispatch('start-payment', ['paymentSessionId' => $this->paymentSessionId]);
        });

        } catch (\Exception $e) {

            Flux::toast(
                variant: 'danger',
                heading: 'Order Failed',
                text: $e->getMessage()
            );

            return;
        }
    }

    protected function rules()
    {
        return [

            'first_name' => ['required', 'max:255'],
            'last_name'  => ['required', 'max:255'],

            'email'      => ['required', 'email'],
            'phone'      => ['required'],

            'address'    => ['required'],
            'city'       => ['required'],
            'state'      => ['required'],
            'pincode'    => ['required'],

        ];
    }

    private function generateOrderNumber()
    {
        return 'ORD-' . now()->format('YmdHis');
    }

    public function selectAddress($id)
    {
        $address = UserAddress::where('user_id', auth()->id())
            ->findOrFail($id);

        $this->selectedAddress = $address->id;

        $this->first_name = $address->first_name;
        $this->last_name  = $address->last_name;

        $this->phone = $address->phone;

        $this->address = $address->address;

        $this->city = $address->city;
        $this->state = $address->state;
        $this->pincode = $address->pincode;

        // Load address title
        if (in_array($address->title, ['Home', 'Office', 'Hostel'])) {
            $this->title = $address->title;
            $this->customTitle = '';
        } else {
            $this->title = 'Other';
            $this->customTitle = $address->title;
        }
    }

    public function saveAddress()
    {
        $this->validate([
            'first_name' => 'required',
            'last_name'  => 'required',
            'phone'      => 'required',
            'address'    => 'required',
            'city'       => 'required',
            'state'      => 'required',
            'pincode'    => 'required',
            'title' => 'required',
            'customTitle' => 'required_if:title,Other|max:50',
        ]);

        UserAddress::create([
            'user_id'    => auth()->id(),

            'title' => $this->title === 'Other' 
                            ? $this->customTitle
                            : $this->title,

            'first_name' => $this->first_name,
            'last_name'  => $this->last_name,
            'phone'      => $this->phone,

            'address'    => $this->address,
            'city'       => $this->city,
            'state'      => $this->state,
            'pincode'    => $this->pincode,
            'is_default' => UserAddress::where('user_id', auth()->id())->doesntExist(),
        ]);

        $this->addresses = UserAddress::where('user_id', auth()->id())
            ->orderByDesc('is_default')
            ->get();

        Flux::toast(
            heading: 'Address Saved',
            text: 'Your address has been saved successfully.'
        );
    }

    public function deleteAddress($id)
    {
        $address = UserAddress::where('user_id', auth()->id())
            ->findOrFail($id);

        $address->delete();

        // Refresh the address list
        $this->addresses = UserAddress::where('user_id', auth()->id())
            ->orderByDesc('is_default')
            ->get();

        // Clear selection if the deleted address was selected
        if ($this->selectedAddress == $id) {
            $this->selectedAddress = null;

            $this->title = 'Home';
            $this->customTitle = '';

            $this->first_name = auth()->user()->name;
            $this->last_name = '';
            $this->phone = '';
            $this->address = '';
            $this->city = '';
            $this->state = '';
            $this->pincode = '';
        }

        Flux::toast(
            heading: 'Address Deleted',
            text: 'The address has been removed successfully.'
        );
    }

    public string $couponCode = '';
    public ?\App\Models\Coupon $appliedCoupon = null;

    public $couponEdited = null;
    public function applyCoupon()
    {
        $this->validate([
            'couponCode' => 'required|string',
        ]);

        $coupon = Coupon::where('code', strtoupper(trim($this->couponCode)))
            ->where('is_active', true)
            ->first();

        if (! $coupon) {
            $this->couponEdited = false;

            Flux::toast(
                variant: 'danger',
                heading: 'Invalid Coupon',
                text: 'Coupon code does not exist.'
            );

            return;
        }

        // Not started yet
        if ($coupon->starts_at && now()->lt($coupon->starts_at)) {
            $this->couponEdited = false;

            Flux::toast(
                variant: 'danger',
                heading: 'Coupon Not Started',
                text: 'This coupon is not available yet.'
            );

            return;
        }

        // Expired
        if ($coupon->expires_at && now()->gt($coupon->expires_at)) {

            $this->appliedCoupon = null;
            $this->couponCode = '';
            $this->couponEdited = false;

            Flux::toast(
                variant: 'danger',
                heading: 'Coupon Expired',
                text: 'This coupon has expired.'
            );

            return;
        }

        // Minimum order amount
        if ($this->cartTotal < $coupon->minimum_amount) {
            $this->couponEdited = false;

            Flux::toast(
                variant: 'danger',
                heading: 'Minimum Order Required',
                text: 'Minimum order amount is ₹' . number_format($coupon->minimum_amount, 2)
            );

            return;
        }

        // Usage limit
        if (
            !is_null($coupon->usage_limit) &&
            $coupon->used_count >= $coupon->usage_limit
        ) {
            $this->couponEdited = false;

            Flux::toast(
                variant: 'danger',
                heading: 'Coupon Limit Reached',
                text: 'This coupon is no longer available.'
            );

            return;
        }

        $this->appliedCoupon = $coupon;

        Flux::toast(
            heading: 'Coupon Applied',
            text: 'Coupon applied successfully.'
        );

        $this->couponEdited = true;
    }

    public function removeCoupon()
    {
        $this->appliedCoupon = null;
        $this->couponCode = "";

        $this->couponEdited = true;

        if(!empty($this->order_id)){
            orderTable::where('id', $this->order_id)->update([
                'coupon_id' => null,
                'coupon_code' => null
            ]);
        }

        Flux::toast(
            heading: 'Coupon Removed',
            text: 'The coupon has been removed.'
        );
    }

    public function getDiscountProperty()
    {
        if (!$this->appliedCoupon) {
            return 0;
        }

        $coupon = $this->appliedCoupon;

        if ($coupon->type === 'fixed') {
            return min($coupon->value, $this->cartTotal);
        }

        $discount = ($this->cartTotal * $coupon->value) / 100;

        if ($coupon->maximum_discount) {
            $discount = min($discount, $coupon->maximum_discount);
        }

        return round($discount, 2);
    }

    public function getFinalTotalProperty()
    {
        return max(0, $this->cartTotal - $this->discount);
    }
};
?>

<div class="mx-auto max-w-7xl px-6 py-12">

    <div class="mb-10">

        <h1 class="text-4xl font-black">
            Checkout
        </h1>

        <p class="mt-2 text-zinc-500 dark:text-zinc-400">
            Complete your order details below.
        </p>

    </div>

    <div class="grid gap-8 lg:grid-cols-3">

        <div class="lg:col-span-2">
            
            @if($addresses->count())
            <div class="rounded-3xl mb-6 border border-zinc-200 bg-white p-6 dark:border-zinc-800 dark:bg-zinc-900">

                <div class="mb-6">

                    <h2 class="mb-4 text-xl font-bold">
                        Saved Addresses
                    </h2>

                    <div class="space-y-3">

                        @foreach($addresses as $saved)

                            <div
                                wire:click="selectAddress({{ $saved->id }})"
                                class="cursor-pointer rounded-2xl border p-4 transition
                                    {{ $selectedAddress == $saved->id
                                        ? 'border-blue-500 bg-blue-50 dark:bg-blue-900/20'
                                        : 'border-zinc-200 dark:border-zinc-700 hover:border-blue-400'
                                    }}"
                            >

                                <div class="flex items-start justify-between">

                                    <div>

                                        <p class="font-semibold">

                                            {{ $saved->title }}

                                            @if($saved->is_default)
                                                <span class="text-xs text-blue-600">
                                                    (Default)
                                                </span>
                                            @endif

                                        </p>

                                        <p class="text-sm text-zinc-500">

                                            {{ $saved->address }}

                                        </p>

                                        <p class="text-sm text-zinc-500">

                                            {{ $saved->city }},
                                            {{ $saved->state }}
                                            -
                                            {{ $saved->pincode }}

                                        </p>

                                    </div>
                                    <flux:button
                                        variant="danger"
                                        size="sm"
                                        wire:click.stop="deleteAddress({{ $saved->id }})"
                                        wire:confirm="Are you sure you want to delete this address?"
                                    >
                                        Delete
                                    </flux:button>
                                </div>

                            </div>

                        @endforeach

                    </div>

                </div>

            </div>
            @endif
            

            {{-- Address --}}
            <div class="rounded-3xl border border-zinc-200 bg-white p-6 dark:border-zinc-800 dark:bg-zinc-900">

                <h2 class="text-xl font-bold">
                    Contact Information
                </h2>
            
                <div class="mt-6 grid gap-4 md:grid-cols-2">
            
                    <flux:input
                        wire:model.blur="first_name"
                        label="First Name"
                    />
            
                    <flux:input
                        wire:model.blur="last_name"
                        label="Last Name"
                    />
            
                    <flux:input
                        wire:model.blur="email"
                        type="email"
                        label="Email"
                        readonly
                    />
            
                    <flux:input
                        wire:model.blur="phone"
                        label="Phone"
                    />
            
                </div>
            </div>
            <div class="mt-6 rounded-3xl border border-zinc-200 bg-white p-6 dark:border-zinc-800 dark:bg-zinc-900">
                <h2 class="text-xl font-bold">
                    Shipping Address
                </h2>

                <div class="mt-6 space-y-4">

                    <flux:select
                        wire:model.live="title"
                        label="Address Title"
                    >
                        <option value="Home">Home</option>
                        <option value="Office">Office</option>
                        <option value="Hostel">Hostel</option>
                        <option value="Other">Other</option>
                    </flux:select>

                    @if($title === 'Other')

                        <flux:input
                            wire:model.blur="customTitle"
                            label="Custom Address Title"
                            placeholder="Grandma's House"
                        />

                    @endif

                    <flux:textarea
                        wire:model.blur="address"
                        label="Address"
                        autocomplete="address"
                    />

                    <div class="grid gap-4 md:grid-cols-3">

                        <flux:input
                            wire:model.blur="city"
                            label="City"
                        />

                        <flux:input
                            wire:model.blur="state"
                            label="State"
                        />

                        <flux:input
                            wire:model.blur="pincode"
                            label="Pincode"
                        />

                    </div>

                    <flux:button
                        wire:click="saveAddress"
                        variant="filled"
                        class="mb-3 w-full"
                    >
                        Save Address
                    </flux:button>
                </div>

            </div>

        </div>

        <div>

            {{-- Order Summary --}}
            <div class="sticky top-24 rounded-3xl border border-zinc-200 bg-white p-6 dark:border-zinc-800 dark:bg-zinc-900">

                <h2 class="text-xl font-bold">
                    Order Summary
                </h2>

                <div class="mt-6 space-y-3">

                    @foreach($cart as $item)

                        <div class="flex justify-between">

                            <span>
                                {{ $item['name'] }}
                                ×
                                {{ $item['quantity'] }}
                            </span>

                            @php
                                $this->getCartTotalProperty();
                            @endphp
                            <span>
                                ₹{{ number_format($item['price'] * $item['quantity'], 2) }}
                            </span>

                        </div>

                    @endforeach

                </div>

                <div class="mt-6 border-t border-zinc-200 pt-6 dark:border-zinc-800">

                @if(!$appliedCoupon)

                    <flux:input
                        wire:model.defer="couponCode"
                        label="Coupon Code"
                        placeholder="Enter coupon code"
                    />

                    <flux:button
                        wire:click="applyCoupon"
                        class="mt-3 w-full"
                        variant="filled"
                    >
                        Apply Coupon
                    </flux:button>

                @else

                    <div class="rounded-xl border border-green-200 bg-green-50 p-4 dark:border-green-800 dark:bg-green-900/20">

                        <div class="flex items-center justify-between">

                            <div>

                                <p class="font-semibold text-green-700 dark:text-green-300">
                                    Coupon Applied
                                </p>

                                <p class="text-sm text-zinc-600 dark:text-zinc-400">
                                    {{ $appliedCoupon->code }}
                                </p>

                            </div>

                            <flux:button
                                wire:click="removeCoupon"
                                size="sm"
                                variant="danger"
                            >
                                Remove
                            </flux:button>

                        </div>

                    </div>

                @endif

                </div>
                <div class="mt-6 border-t border-zinc-200 pt-6 dark:border-zinc-800">

                    @if($appliedCoupon)

                        <div class="mb-2 flex justify-between text-green-600">
                            <span>
                                Discount ({{ $appliedCoupon->code }})
                            </span>

                            <span>
                                -₹{{ number_format($this->discount, 2) }}
                            </span>
                        </div>

                    @endif

                    <div class="mt-4 flex justify-between border-t pt-4 text-lg font-bold">
                        <span>Total</span>

                        <span>
                            ₹{{ number_format($this->finalTotal, 2) }}
                        </span>
                    </div>

                </div>

                <script src="https://sdk.cashfree.com/js/v3/cashfree.js"></script>

                <script>
                    const cashfree = Cashfree({
                        mode: "sandbox"
                    });

                    document.addEventListener('livewire:init', () => {

                        Livewire.on('start-payment', (event) => {

                            console.log("Payment event fired");
                            console.log(event);

                            cashfree.checkout({
                                paymentSessionId: event[0]['paymentSessionId']
                            });

                        });

                    });
                </script>

                <flux:button
                    wire:click="placeOrder"
                    variant="primary"
                    class="mt-6 w-full"
                >
                    Place Order
                </flux:button>

            </div>
        </div>

    </div>

</div>