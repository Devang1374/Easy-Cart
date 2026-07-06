<meta charset="utf-8" />
<meta name="viewport" content="width=device-width, initial-scale=1.0" />

<title>
    {{ filled($title ?? null) ? $title.' - '.config('app.name', 'Laravel') : config('app.name', 'Laravel') }}
</title>

<link rel="icon" href="https://res.cloudinary.com/iowr4dh0/image/upload/v1783336799/easy-cart-logo_ygytxm.png" sizes="any">
<link rel="icon" href="https://res.cloudinary.com/iowr4dh0/image/upload/v1783336799/easy-cart-logo_ygytxm.png" type="image/svg+ml">
<link rel="apple-touch-icon" href="https://res.cloudinary.com/iowr4dh0/image/upload/v1783336799/easy-cart-logo_ygytxm.png">

@fonts

@vite(['resources/css/app.css', 'resources/js/app.js'])
@fluxAppearance
