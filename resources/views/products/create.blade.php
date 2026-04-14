<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ __('Registrar Nuevo Producto / Servicio') }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8 space-y-6">
            <form method="post" action="{{ route('products.store') }}" class="p-4 sm:p-8 bg-white shadow sm:rounded-lg">
                @csrf

                <header>
                    <h2 class="text-lg font-medium text-gray-900 italic">Información del Ítem</h2>
                    <p class="mt-1 text-sm text-gray-600">Define los detalles del producto o servicio para la facturación.</p>
                </header>

                <div class="mt-6 grid grid-cols-1 md:grid-cols-2 gap-6">
                    <div class="md:col-span-2">
                        <x-input-label for="nombre" value="Nombre del Producto o Servicio" />
                        <x-text-input id="nombre" name="nombre" type="text" class="mt-1 block w-full" :value="old('nombre')" required placeholder="Nombre" />
                        <x-input-error class="mt-2" :messages="$errors->get('nombre')" />
                    </div>

                    <div>
                        <x-input-label for="codigo_interno" value="Código Interno (Opcional)" />
                        <x-text-input id="codigo_interno" name="codigo_interno" type="text" class="mt-1 block w-full" :value="old('codigo_interno')" placeholder="Ej: ART-001" />
                    </div>

                    <div>
                        <x-input-label for="precio_unitario" value="Precio Unitario (Con IVA incluido)" />
                        <div class="relative mt-1">
                            <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                                <span class="text-gray-500 sm:text-sm">$</span>
                            </div>
                            <x-text-input id="precio_unitario" name="precio_unitario" type="number" step="0.01" class="block w-full pl-7" :value="old('precio_unitario')" required placeholder="0.00" />
                        </div>
                        <x-input-error class="mt-2" :messages="$errors->get('precio_unitario')" />
                    </div>

                    <div>
                        <x-input-label for="unidad_medida" value="Unidad de Medida" />
                        <select name="unidad_medida" id="unidad_medida" class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:ring-indigo-500 focus:border-indigo-500">
                            <option value="59">59 - Unidad</option>
                            <option value="34">34 - Kilogramo</option>
                            <option value="23">23 - Litro</option>
                            <option value="36">36 - Libra</option>


                        </select>
                        <p class="mt-1 text-xs text-gray-500 italic">El código 59 es el estándar para productos individuales.</p>
                    </div>

                    <div class="flex items-center space-x-3 mt-4">
                        <input type="checkbox" id="es_exento" name="es_exento" value="1" class="rounded border-gray-300 text-indigo-600 shadow-sm focus:ring-indigo-500" {{ old('es_exento') ? 'checked' : '' }}>
                        <x-input-label for="es_exento" value="Este producto es EXENTO de IVA" class="inline-block" />
                    </div>
                </div>

                <div class="flex items-center justify-end gap-4 mt-8 border-t pt-6">
                    <a href="{{ route('products.index') }}" class="text-sm text-gray-600 hover:underline">Cancelar</a>
                    <x-primary-button class="bg-indigo-600 hover:bg-indigo-700">
                        {{ __('Guardar Producto') }}
                    </x-primary-button>
                </div>
            </form>
        </div>
    </div>
</x-app-layout>