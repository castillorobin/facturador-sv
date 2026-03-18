<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ __('Configuración de Emisor (DTE)') }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8 space-y-6">
          
            <form method="post" action="{{ route('company.update') }}" class="p-4 sm:p-8 bg-white shadow sm:rounded-lg">
                @csrf
                @method('patch')

                <header>
                      @if (session('status') === 'company-updated')
                        <p class="text-sm text-gray-600" style="color: green;">{{ __('Datos guardados correctamente.') }}</p>
                    @endif
                    <h2 class="text-lg font-medium text-gray-900">Datos Fiscales</h2>
                    <p class="mt-1 text-sm text-gray-600">Actualiza la información legal de tu negocio para la firma de DTE.</p>
                </header>

                <div class="mt-6 grid grid-cols-1 md:grid-cols-2 gap-6">
                    <div>
                        <x-input-label for="nombre" value="Razón Social / Nombre" />
                        <x-text-input id="nombre" name="nombre" type="text" class="mt-1 block w-full" :value="old('nombre', $company->nombre)" required />
                        <x-input-error class="mt-2" :messages="$errors->get('nombre')" />
                    </div>

                    <div>
                        <x-input-label for="nombre_comercial" value="Nombre Comercial" />
                        <x-text-input id="nombre_comercial" name="nombre_comercial" type="text" class="mt-1 block w-full" :value="old('nombre_comercial', $company->nombre_comercial)" required />
                    </div>

                    <div>
                        <x-input-label for="nit" value="NIT (Sin guiones)" />
                        <x-text-input id="nit" name="nit" type="text" class="mt-1 block w-full" :value="old('nit', $company->nit)" required maxlength="14" />
                    </div>

                    <div>
                        <x-input-label for="nrc" value="NRC" />
                        <x-text-input id="nrc" name="nrc" type="text" class="mt-1 block w-full" :value="old('nrc', $company->nrc)" required />
                    </div>
                </div>

                <hr class="my-8">

                <header>
                    <h2 class="text-lg font-medium text-gray-900">Credenciales de API</h2>
                    <p class="mt-1 text-sm text-gray-600">Configura la conexión con el servidor de Facturación.</p>
                </header>

                <div class="mt-6 grid grid-cols-1 md:grid-cols-2 gap-6">
                    <div>
                        <x-input-label for="api_usuario" value="Usuario API" />
                        <x-text-input id="api_usuario" name="api_usuario" type="text" class="mt-1 block w-full" :value="old('api_usuario', $company->api_usuario)" />
                    </div>

                    <div>
                        <x-input-label for="api_password" value="Password API" />
                        <x-text-input id="api_password" name="api_password" type="password" class="mt-1 block w-full" :value="old('api_password', $company->api_password)" />
                    </div>

                    <div>
                        <x-input-label for="ambiente" value="Ambiente" />
                        <select name="ambiente" class="mt-1 block w-full border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 rounded-md shadow-sm">
                            <option value="00" {{ $company->ambiente == '00' ? 'selected' : '' }}>Pruebas (Sandbox)</option>
                            <option value="01" {{ $company->ambiente == '01' ? 'selected' : '' }}>Producción</option>
                        </select>
                    </div>
                </div>
                <div class="mt-6 grid grid-cols-1 md:grid-cols-2 gap-6">
    <div>
        <x-input-label for="cod_actividad" value="Código de Actividad (MH)" />
        <x-text-input id="cod_actividad" name="cod_actividad" type="text" class="mt-1 block w-full" :value="old('cod_actividad', $company->cod_actividad)" required />
        <x-input-error class="mt-2" :messages="$errors->get('cod_actividad')" />
    </div>

    <div class="mt-4">
    <x-input-label for="cod_actividad" value="Actividad Económica (Busca por nombre o código)" />
    <select name="cod_actividad" id="select-actividad" class="mt-1 block w-full border-gray-300 rounded-md shadow-sm select2">
        <option value="">-- Seleccione una actividad --</option>
        @foreach($actividades as $actividad)
            <option value="{{ $actividad->codigo }}" {{ $company->cod_actividad == $actividad->codigo ? 'selected' : '' }}>
                {{ $actividad->codigo }} | {{ $actividad->descripcion }}
            </option>
        @endforeach
    </select>
</div>

    <div class="mt-4">
    <x-input-label for="departamento" value="Departamento" />
    <select name="departamento" id="departamento" class="mt-1 block w-full border-gray-300 rounded-md shadow-sm">
        @foreach($departamentos as $depto)
            <option value="{{ $depto->codigo }}" {{ $company->departamento == $depto->codigo ? 'selected' : '' }}>
                {{ $depto->valor }}
            </option>
        @endforeach
    </select>
</div>

<div class="mt-4">
    <x-input-label for="municipio" value="Municipio" />
    <select name="municipio" id="municipio" class="mt-1 block w-full border-gray-300 rounded-md shadow-sm">
        @foreach($municipios as $muni)
            <option value="{{ $muni->codigo }}" {{ $company->municipio == $muni->codigo ? 'selected' : '' }}>
                {{ $muni->valor }}
            </option>
        @endforeach
    </select>
</div>

    <div class="md:col-span-2">
        <x-input-label for="direccion_complemento" value="Dirección Completa" />
        <x-text-input id="direccion_complemento" name="direccion_complemento" type="text" class="mt-1 block w-full" :value="old('direccion_complemento', $company->direccion_complemento)" required />
    </div>

    <div>
        <x-input-label for="telefono" value="Teléfono" />
        <x-text-input id="telefono" name="telefono" type="text" class="mt-1 block w-full" :value="old('telefono', $company->telefono)" required />
    </div>

    <div>
        <x-input-label for="email" value="Email de la Empresa" />
        <x-text-input id="email" name="email" type="email" class="mt-1 block w-full" :value="old('email', $company->email)" required />
    </div>
    
    <div>
        <x-input-label for="password_privado" value="Password Privado MH" />
        <x-text-input id="password_privado" name="password_privado" type="text" class="mt-1 block w-full" :value="old('password_privado', $company->password_privado)" />
    </div>
</div>

@if ($errors->any())
    <div class="mt-4 p-4 mb-4 text-sm text-red-700 bg-red-100 rounded-lg">
        <ul>
            @foreach ($errors->all() as $error)
                <li>{{ $error }}</li>
            @endforeach
        </ul>
    </div>
@endif

<br>

                <div class="flex items-center gap-4 mt-8">
                    <x-primary-button>{{ __('Guardar Cambios') }}</x-primary-button>

                    
                </div>
            </form>

        </div>
    </div>


    <script>
    $(document).ready(function() {
        $('#select-actividad').select2({
            placeholder: "Escribe para buscar actividad...",
            allowClear: true,
            width: '100%',
            // Opcional: Para que se vea bien con el diseño de Breeze/Tailwind
            selectionCssClass: 'mt-1 block w-full border-gray-300 rounded-md shadow-sm'
        });
    });
</script>
</x-app-layout>