<meta charset="utf-8" />
<meta name="viewport" content="width=device-width, initial-scale=1.0" />

<title>
    {{ filled($title ?? null) ? $title.' - '.config('app.name', 'Laravel') : config('app.name', 'Laravel') }}
</title>

<link rel="icon" href="https://res.cloudinary.com/iowr4dh0/image/upload/v1783421188/easy-cart-logo_phefi2.png" sizes="any">
<link rel="icon" href="https://res.cloudinary.com/iowr4dh0/image/upload/v1783421188/easy-cart-logo_phefi2.png" type="image/svg+ml">
<link rel="apple-touch-icon" href="https://res.cloudinary.com/iowr4dh0/image/upload/v1783421188/easy-cart-logo_phefi2.png">

@fonts

@vite(['resources/css/app.css', 'resources/js/app.js'])
@fluxAppearance
