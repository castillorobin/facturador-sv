<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ __('Editar Producto / Servicio') }}: {{ $product->nombre }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8 space-y-6">
            <form method="post" action="{{ route('products.update', $product) }}" class="p-4 sm:p-8 bg-white shadow sm:rounded-lg">
                @csrf
                @method('PATCH')

                <header>
                    <h2 class="text-lg font-medium text-gray-900 italic">Información del Ítem</h2>
                    <p class="mt-1 text-sm text-gray-600">Actualiza los detalles del producto o servicio.</p>
                </header>

                <div class="mt-6 grid grid-cols-1 md:grid-cols-2 gap-6">
                    <div class="md:col-span-2">
                        <x-input-label for="nombre" value="Nombre del Producto o Servicio" />
                        <x-text-input id="nombre" name="nombre" type="text" class="mt-1 block w-full" :value="old('nombre', $product->nombre)" required />
                        <x-input-error class="mt-2" :messages="$errors->get('nombre')" />
                    </div>

                    <div>
                        <x-input-label for="codigo_interno" value="Código Interno (Opcional)" />
                        <x-text-input id="codigo_interno" name="codigo_interno" type="text" class="mt-1 block w-full" :value="old('codigo_interno', $product->codigo_interno)" />
                    </div>

                    <div>
                        <x-input-label for="precio_unitario" value="Precio Unitario (Con IVA incluido)" />
                        <div class="relative mt-1">
                            <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                                <span class="text-gray-500 sm:text-sm">$</span>
                            </div>
                            <x-text-input id="precio_unitario" name="precio_unitario" type="number" step="0.01" class="block w-full pl-7" :value="old('precio_unitario', $product->precio_unitario)" required />
                        </div>
                        <x-input-error class="mt-2" :messages="$errors->get('precio_unitario')" />
                    </div>

                    <div>
                        <x-input-label for="unidad_medida" value="Unidad de Medida" />
                        <select name="unidad_medida" id="unidad_medida" class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:ring-indigo-500 focus:border-indigo-500">
                            <option value="59" {{ $product->unidad_medida == '59' ? 'selected' : '' }}>59 - Unidad</option>
                            <option value="34" {{ $product->unidad_medida == '34' ? 'selected' : '' }}>34 - Kilogramo</option>
                            <option value="23" {{ $product->unidad_medida == '23' ? 'selected' : '' }}>23 - Litro</option>
                            <option value="36" {{ $product->unidad_medida == '36' ? 'selected' : '' }}>36 - Libra</option>
                            <option value="22" {{ $product->unidad_medida == '22' ? 'selected' : '' }}>22 - Galón</option>
                        </select>
                    </div>

                    
                </div>

                <div class="flex items-center justify-end gap-4 mt-8 border-t pt-6">
                    <a href="{{ route('products.index') }}" class="text-sm text-gray-600 hover:underline">Cancelar</a>
                    <x-primary-button class="bg-indigo-600 hover:bg-indigo-700">
                        {{ __('Actualizar Producto') }}
                    </x-primary-button>
                </div>
            </form>
        </div>
    </div>
</x-app-layout>