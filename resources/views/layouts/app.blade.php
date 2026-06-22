<x-layouts::app.header :title="$title ?? null">
    <div class="p-2 lg:p-2">
        {{ $slot }}
    </div>
</x-layouts::app.header>
