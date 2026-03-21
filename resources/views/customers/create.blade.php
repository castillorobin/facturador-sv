<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">Nuevo Cliente (Receptor)</h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <form action="{{ route('customers.store') }}" method="POST" class="bg-white p-6 shadow rounded-lg">
                @csrf
                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <div>
                        <x-input-label for="nombre" value="Nombre o Razón Social" />
                        <x-text-input name="nombre" class="w-full" required />
                    </div>
                    <div>
                        <x-input-label for="tipo_documento" value="Tipo de Documento" />
                        <select name="tipo_documento" class="w-full border-gray-300 rounded-md shadow-sm">
                            <option value="13">DUI</option>
                            <option value="36">NIT</option>
                            <option value="37">Pasaporte</option>
                        </select>
                    </div>
                    <div>
                        <x-input-label for="num_documento" value="Número de Documento" />
                        <x-text-input name="num_documento" class="w-full" required />
                    </div>
                    <div>
                        <x-input-label for="nrc" value="NRC (Para CCF)" />
                        <x-text-input name="nrc" class="w-full" />
                    </div>

                    <div class="md:col-span-2">
                        <x-input-label value="Actividad Económica" />
                        <select id="select-actividad" class="w-full select2">
                            <option value="">-- Seleccione Actividad --</option>
                            @foreach($actividades as $act)
                                <option value="{{ $act->codigo }}">{{ $act->codigo }} | {{ $act->descripcion }}</option>
                            @endforeach
                        </select>
                        <input type="hidden" name="cod_actividad" id="cod_actividad">
                        <input type="hidden" name="desc_actividad" id="desc_actividad">
                    </div>
                </div>

                <div class="mt-6">
                    <x-primary-button>Guardar Cliente</x-primary-button>
                </div>
            </form>
        </div>
    </div>

    @push('scripts')
    <script>
        $(document).ready(function() {
            $('#select-actividad').select2().on('select2:select', function (e) {
                let data = e.params.data.text.split('|');
                $('#cod_actividad').val(e.params.data.id);
                $('#desc_actividad').val(data[1].trim());
            });
        });
    </script>
    @endpush
</x-app-layout>