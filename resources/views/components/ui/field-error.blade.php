@props(['name'])

@error($name)
    <p {{ $attributes->merge(['class' => 'mt-1.5 text-xs font-bold leading-5 text-red-600']) }}>
        {{ $message }}
    </p>
@enderror
