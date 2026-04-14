<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ __('Editar Cliente (Receptor DTE)') }}: {{ $customer->nombre }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8 space-y-6">
            <form method="post" action="{{ route('customers.update', $customer) }}" class="p-4 sm:p-8 bg-white shadow sm:rounded-lg">
                @csrf
                @method('PATCH')

                <header>
                    <h2 class="text-lg font-medium text-gray-900">Identificación del Receptor</h2>
                    <p class="mt-1 text-sm text-gray-600">Actualiza los datos básicos requeridos para DTE.</p>
                </header>

                <div class="mt-6 grid grid-cols-1 md:grid-cols-2 gap-6">
                    <div class="md:col-span-2">
                        <x-input-label for="nombre" value="Nombre o Razón Social" />
                        <x-text-input name="nombre" type="text" class="mt-1 block w-full" required :value="old('nombre', $customer->nombre)" />
                        <x-input-error class="mt-2" :messages="$errors->get('nombre')" />
                    </div>

                    <div>
                        <x-input-label for="tipo_documento" value="Tipo de Documento" />
                        <select name="tipo_documento" class="mt-1 block w-full border-gray-300 rounded-md shadow-sm">
                            <option value="36" {{ old('tipo_documento', $customer->tipo_documento) == '36' ? 'selected' : '' }}>DUI/NIT</option>
                            <option value="37" {{ old('tipo_documento', $customer->tipo_documento) == '37' ? 'selected' : '' }}>Pasaporte</option>
                        </select>
                    </div>

                    <div>
                        <x-input-label for="num_documento" value="Número de Documento (Sin guiones)" />
                        <x-text-input name="num_documento" type="text" class="mt-1 block w-full" required :value="old('num_documento', $customer->num_documento)" />
                    </div>
                </div>

                <hr class="my-8 pt-4">

                <header>
                    <h2 class="text-lg font-medium text-gray-900">Datos de Contribuyente</h2>
                </header>

                <div class="mt-6 grid grid-cols-1 md:grid-cols-2 gap-6">
                    <div>
                        <x-input-label for="nrc" value="NRC" />
                        <x-text-input name="nrc" type="text" class="mt-1 block w-full" :value="old('nrc', $customer->nrc)" />
                    </div>

                    <div>
                        <x-input-label for="nombre_comercial" value="Nombre Comercial" />
                        <x-text-input name="nombre_comercial" type="text" class="mt-1 block w-full" :value="old('nombre_comercial', $customer->nombre_comercial)" />
                    </div>

                    <div class="md:col-span-2">
                        <x-input-label for="select-actividad" value="Actividad Económica" />
                        <select id="select-actividad" class="mt-1 block w-full select2">
                            <option value="">-- Seleccione Actividad --</option>
                            @foreach($actividades as $act)
                                <option value="{{ $act->codigo }}" {{ old('cod_actividad', $customer->cod_actividad) == $act->codigo ? 'selected' : '' }}>
                                    {{ $act->codigo }} | {{ $act->descripcion }}
                                </option>
                            @endforeach
                        </select>
                        <input type="hidden" name="cod_actividad" id="cod_actividad" value="{{ old('cod_actividad', $customer->cod_actividad) }}">
                        <input type="hidden" name="desc_actividad" id="desc_actividad" value="{{ old('desc_actividad', $customer->desc_actividad) }}">
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
                                <option value="{{ $depto->codigo }}" {{ old('departamento', $customer->departamento) == $depto->codigo ? 'selected' : '' }}>
                                    {{ $depto->valor }}
                                </option>
                            @endforeach
                        </select>
                    </div>

                    <div>
                        <x-input-label for="municipio" value="Municipio" />
                        <select name="municipio" id="municipio" class="mt-1 block w-full border-gray-300 rounded-md shadow-sm" required>
                            @foreach($municipios as $muni)
                                <option value="{{ $muni->codigo }}" {{ old('municipio', $customer->municipio) == $muni->codigo ? 'selected' : '' }}>
                                    {{ $muni->valor }}
                                </option>
                            @endforeach
                        </select>
                    </div>

                    <div class="md:col-span-2">
                        <x-input-label for="direccion_complemento" value="Dirección Completa" />
                        <x-text-input name="direccion_complemento" type="text" class="mt-1 block w-full" :value="old('direccion_complemento', $customer->direccion_complemento)" />
                    </div>

                    <div>
                        <x-input-label for="telefono" value="Teléfono" />
                        <x-text-input name="telefono" type="text" class="mt-1 block w-full" :value="old('telefono', $customer->telefono)" />
                    </div>

                    <div>
                        <x-input-label for="email" value="Correo Electrónico (Para envío de DTE)" />
                        <x-text-input name="email" type="email" class="mt-1 block w-full" :value="old('email', $customer->email)" />
                    </div>
                </div>

                <div class="flex items-center gap-4 mt-8">
                    <x-primary-button>{{ __('Actualizar Cliente') }}</x-primary-button>
                    <a href="{{ route('customers.index') }}" class="text-sm text-gray-600 hover:underline">Cancelar</a>
                </div>
            </form>
        </div>
    </div>

    @push('scripts')
    <script>
        $(document).ready(function() {
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


        $('#departamento').on('change', function() {
    let deptoId = $(this).val();
    if(deptoId) {
        $.ajax({
            url: '/api/municipios/' + deptoId, // Crea esta ruta en api.php o web.php
            type: "GET",
            dataType: "json",
            success:function(data) {
                $('#municipio').empty();
                $('#municipio').append('<option value="">-- Seleccione Municipio --</option>');
                $.each(data, function(key, value) {
                    $('#municipio').append('<option value="'+ value.codigo +'">'+ value.valor +'</option>');
                });
            }
        });
    } else {
        $('#municipio').empty();
    }
});
    </script>
    @endpush
</x-app-layout>