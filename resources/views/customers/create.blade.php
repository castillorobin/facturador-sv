<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ __('Nuevo Cliente (Receptor DTE)') }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8 space-y-6">
            <form method="post" action="{{ route('customers.store') }}" class="p-4 sm:p-8 bg-white shadow sm:rounded-lg">
                @csrf

                <header>
                    <h2 class="text-lg font-medium text-gray-900">Identificación del Receptor</h2>
                    <p class="mt-1 text-sm text-gray-600">Datos básicos requeridos para Facturas y Créditos Fiscales.</p>
                </header>

                <div class="mt-6 grid grid-cols-1 md:grid-cols-2 gap-6">
                    <div class="md:col-span-2">
                        <x-input-label for="nombre" value="Nombre o Razón Social" />
                        <x-text-input name="nombre" type="text" class="mt-1 block w-full" required :value="old('nombre')" />
                        <x-input-error class="mt-2" :messages="$errors->get('nombre')" />
                    </div>

                    <div>
                        <x-input-label for="tipo_documento" value="Tipo de Documento" />
                        <select name="tipo_documento" class="mt-1 block w-full border-gray-300 rounded-md shadow-sm">
                            
                            <option value="36">DUI/NIT</option>
                            <option value="37">Pasaporte</option>
                            
                        </select>
                    </div>

                    <div>
                        <x-input-label for="num_documento" value="Número de Documento (Sin guiones)" />
                        <x-text-input name="num_documento" type="text" class="mt-1 block w-full" required :value="old('num_documento')" />
                    </div>
                </div>

                <hr class="my-8 pt-4">

                <header>
                    <h2 class="text-lg font-medium text-gray-900">Datos de Contribuyente</h2>
                    
                </header>

                <div class="mt-6 grid grid-cols-1 md:grid-cols-2 gap-6">
                    <div>
                        <x-input-label for="nrc" value="NRC" />
                        <x-text-input name="nrc" type="text" class="mt-1 block w-full" :value="old('nrc')" />
                    </div>

                    <div>
                        <x-input-label for="nombre_comercial" value="Nombre Comercial" />
                        <x-text-input name="nombre_comercial" type="text" class="mt-1 block w-full" :value="old('nombre_comercial')" />
                    </div>

                    <div class="md:col-span-2">
                        <x-input-label for="select-actividad" value="Actividad Económica" />
                        <select id="select-actividad" class="mt-1 block w-full select2">
                            <option value="">-- Seleccione Actividad --</option>
                            @foreach($actividades as $act)
                                <option value="{{ $act->codigo }}">{{ $act->codigo }} | {{ $act->descripcion }}</option>
                            @endforeach
                        </select>
                        <input type="hidden" name="cod_actividad" id="cod_actividad">
                        <input type="hidden" name="desc_actividad" id="desc_actividad">
                    </div>
                </div>

                <hr class="my-8 pt-4">

<header>
    <h2 class="text-lg font-medium text-gray-900">Ubicación y Contacto</h2>
</header>

<div class="mt-6 grid grid-cols-1 md:grid-cols-2 gap-6">
    <div>
        <x-input-label for="departamento" value="Departamento" />
        <select name="departamento" id="departamento" class="mt-1 block w-full border-gray-300 rounded-md shadow-sm">
            <option value="">-- Seleccione --</option>
            @foreach($departamentos as $depto)
                <option value="{{ $depto->codigo }}">{{ $depto->valor }}</option>
            @endforeach
        </select>
    </div>

    <div>
        <x-input-label for="municipio" value="Municipio" />
        <select name="municipio" id="municipio" class="mt-1 block w-full border-gray-300 rounded-md shadow-sm" required>
            <option value="">-- Seleccione Municipio --</option>
            @foreach($municipios as $muni)
                <option value="{{ $muni->codigo }}" {{ old('municipio') == $muni->codigo ? 'selected' : '' }}>
                    {{ $muni->valor }}
                </option>
            @endforeach
        </select>
        <x-input-error class="mt-2" :messages="$errors->get('municipio')" />
    </div>

    <div class="md:col-span-2">
        <x-input-label for="direccion_complemento" value="Dirección Completa" />
        <x-text-input name="direccion_complemento" type="text" class="mt-1 block w-full" :value="old('direccion_complemento')" />
    </div>

    <div>
        <x-input-label for="telefono" value="Teléfono" />
        <x-text-input name="telefono" type="text" class="mt-1 block w-full" :value="old('telefono')" />
    </div>

    <div>
        <x-input-label for="email" value="Correo Electrónico (Para envío de DTE)" />
        <x-text-input name="email" type="email" class="mt-1 block w-full" :value="old('email')" />
    </div>
</div>
<br>
                <div class="flex items-center gap-4 mt-8">
                    <x-primary-button>{{ __('Guardar Cliente') }}</x-primary-button>
                    <a href="{{ route('customers.index') }}" class="text-sm text-gray-600 hover:underline">Cancelar</a>
                </div>
            </form>
        </div>
    </div>


@push('scripts')
<style>
    /* Contenedor principal para igualar la altura de Breeze */
    .select2-container .select2-selection--single {
        height: 42px !important; /* Altura estándar de inputs de Breeze */
        border-color: rgb(209 213 219) !important; /* gray-300 */
        border-radius: 0.375rem !important; /* rounded-md */
        display: flex;
        align-items: center;
    }

    /* Quitar el borde azul por defecto de Select2 y usar el de Tailwind */
    .select2-container--default .select2-selection--single .select2-selection__rendered {
        line-height: 42px !important;
        padding-left: 12px !important;
        color: #374151 !important; /* gray-700 */
    }

    /* Centrar la flechita lateral */
    .select2-container--default .select2-selection--single .select2-selection__arrow {
        height: 40px !important;
        top: 1px !important;
        right: 10px !important;
    }

    /* Estilo cuando el select está en focus */
    .select2-container--default.select2-container--focus .select2-selection--single {
        border-color: #6366f1 !important; /* indigo-500 */
        ring-width: 1px;
        --tw-ring-color: #6366f1;
        box-shadow: 0 0 0 1px #6366f1 !important;
    }
    
    /* Ajustar la caja de búsqueda desplegable */
    .select2-dropdown {
        border-color: rgb(209 213 219) !important;
        border-radius: 0.375rem !important;
    }
</style>

<script>
    $(document).ready(function() {
        // --- 1. Lógica de Select2 para Actividades ---
        $('#select-actividad').select2({
            placeholder: "Busca por nombre o código...",
            allowClear: true,
            width: '100%'
        }).on('select2:select', function (e) {
            let parts = e.params.data.text.split('|');
            $('#cod_actividad').val(e.params.data.id);
            if(parts.length > 1) {
                $('#desc_actividad').val(parts[1].trim());
            }
        });

        

    });
</script>
@endpush
</x-app-layout>