<meta charset="utf-8" />
<meta name="viewport" content="width=device-width, initial-scale=1.0" />

<title>
    {{ filled($title ?? null) ? $title.' - '.config('app.name', 'Laravel') : config('app.name', 'Laravel') }}
</title>

<link rel="icon" href="{{ Vite::asset('resources/images/easy-cart-logo.png') }}" sizes="any">
<link rel="icon" href="{{ Vite::asset('resources/images/easy-cart-logo.png') }}" type="image/svg+ml">
<link rel="apple-touch-icon" href="{{ Vite::asset('resources/images/easy-cart-logo.png') }}">

@fonts

@vite(['resources/css/app.css', 'resources/js/app.js'])
@fluxAppearance
