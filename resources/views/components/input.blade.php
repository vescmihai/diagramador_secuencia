@props(['disabled' => false])

<input {{ $disabled ? 'disabled' : '' }} {!! $attributes->merge(['class' => 'border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 rounded-md shadow-sm']) !!}>

{{-- pattern="[A-Za-z]+" title="Ingresa solo letras (sin símbolos ni números)" --}}
