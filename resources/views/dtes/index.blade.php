<x-app-layout>
   <link rel="stylesheet" href="https://cdn.datatables.net/1.13.7/css/jquery.dataTables.min.css">
    
    <style>
        /* Parche para que el buscador no se vea "apretado" con Tailwind */
        .dataTables_wrapper { padding: 20px; }
        .dataTables_filter input { 
            border: 1px solid #d1d5db !important; 
            border-radius: 0.375rem !important; 
            margin-bottom: 15px;
        }
        .dataTables_length select { border-radius: 0.375rem !important; padding-right: 2rem !important; }
    </style>

    <x-slot name="header">
        <div class="flex justify-between items-center">
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">
                {{ __('Historial de Documentos (DTE)') }}
            </h2>
            <a href="{{ route('dtes.create') }}" 
               style="background-color: #4f46e5 !important; color: white !important;"
               class="inline-flex items-center px-4 py-2 border border-transparent rounded-md font-bold text-xs uppercase tracking-widest hover:opacity-90 transition shadow-sm">
                <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"></path></svg>
                Nueva Venta
            </a>
        </div>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            
            @if(session('success'))
                <div class="mb-4 p-4 bg-green-100 border-l-4 border-green-500 text-green-700 font-bold shadow-sm">
                    {{ session('success') }}
                </div>
            @endif 

            @if ($errors->any())
                <div class="mt-6 p-4 text-sm text-red-700 bg-red-100 rounded-lg">
                    <ul class="list-disc ms-4">
                        @foreach ($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
            @endif

            <div class="bg-white overflow-hidden shadow-lg sm:rounded-lg border border-gray-200">
                <table class="w-full text-sm text-gray-500" id="tablaDtes">
                    <thead class="text-xs text-gray-700 uppercase bg-gray-100 border-b border-gray-300">
                        <tr>
                            <th class="px-6 py-4 text-center">Fecha / Control</th>
                            <th class="px-6 py-4 text-center">Cliente</th>
                            <th class="px-6 py-4 text-center">Tipo</th>
                            <th class="px-6 py-4 text-center">Total</th>
                            <th class="px-6 py-4 text-center">Estado</th>
                            <th class="px-6 py-4 text-center">Acciones</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-200">
                        @forelse($dtes as $dte)
                            <tr class="bg-white hover:bg-indigo-50 transition-colors duration-200">
                                <td class="px-6 py-4 text-center">
                                    <div class="flex flex-col items-center">
                                        <span class="text-gray-900 font-bold">{{ $dte->fecha_emision->format('d/m/Y') }}</span>
                                        <span class="text-xs text-indigo-600 font-mono">{{ $dte->numero_control }}</span>
                                    </div>
                                </td>

                                <td class="px-6 py-4 text-center">
                                    <div class="flex flex-col items-center">
                                        <span class="font-bold text-gray-800">{{ $dte->customer->nombre }}</span>
                                        <span class="text-xs text-gray-400 italic">{{ $dte->customer->num_documento }}</span>
                                    </div>
                                </td>

                                <td class="px-6 py-4 text-center">
                                    <span class="px-2 py-1 rounded bg-gray-200 text-gray-700 text-[10px] font-black uppercase">
                                        {{ $dte->tipo_dte == '01' ? 'Factura' : 'Crédito Fiscal' }}
                                    </span>
                                </td>

                                <td class="px-6 py-4 text-center font-black text-gray-900 text-base">
                                    $ {{ number_format($dte->total_pagar, 2) }}
                                </td>

                                <td class="px-6 py-4 text-center">
                                    @php
                                        $config = [
                                            'BORRADOR'  => ['bg' => '#f3f4f6', 'text' => '#4b5563', 'border' => '#d1d5db'],
                                            'PROCESADO' => ['bg' => '#dcfce7', 'text' => '#15803d', 'border' => '#86efac'],
                                            'RECHAZADO' => ['bg' => '#fee2e2', 'text' => '#b91c1c', 'border' => '#fca5a5'],
                                            'ANULADO'   => ['bg' => '#ffedd5', 'text' => '#c2410c', 'border' => '#fdba74'],
                                        ];
                                        $estilo = $config[$dte->estado] ?? ['bg' => '#dbeafe', 'text' => '#1d4ed8', 'border' => '#93c5fd'];
                                    @endphp
                                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-bold border"
                                          style="background-color: {{ $estilo['bg'] }} !important; color: {{ $estilo['text'] }} !important; border-color: {{ $estilo['border'] }} !important;">
                                        {{ $dte->estado }}
                                    </span>
                                </td>

                                <td class="px-6 py-4 whitespace-nowrap text-sm font-medium">
                                    <div class="flex items-center justify-center space-x-3">
                                        @if(strtoupper($dte->estado) == 'BORRADOR')
                                            <form action="{{ route('dtes.enviar', $dte->id) }}" method="POST" class="inline">
                                                @csrf
                                                <button type="submit" 
                                                        class="inline-flex items-center px-3 py-1.5 rounded-md font-bold text-[10px] uppercase tracking-tighter shadow-sm transition"
                                                        style="background-color: #0d6efd !important; color: #ffffff !important; border: 1px solid #0a58ca !important;">
                                                    ENVIAR MH
                                                </button>
                                            </form>
                                        @endif

                                        @if($dte->estado == 'PROCESADO')
                                            <a href="{{ route('dtes.verPdf', $dte->id) }}" target="_blank" title="Ver PDF" class="text-rose-500 hover:text-rose-600 transition transform hover:scale-125">
                                                <svg class="w-6 h-6" fill="currentColor" viewBox="0 0 24 24"><path d="M9 2.221V7H4.221a2 2 0 0 1 .365-.5L8.5 2.586A2 2 0 0 1 9 2.22ZM11 2v5a2 2 0 0 1-2 2H4a2 2 0 0 0-2 2v7a2 2 0 0 0 2 2 2 2 0 0 0 2 2h12a2 2 0 0 0 2-2 2 2 0 0 0 2-2v-7a2 2 0 0 0-2-2V4a2 2 0 0 0-2-2h-7Z"/></svg>
                                            </a>
                                            <a href="{{ route('dtes.downloadJson', $dte->id) }}" title="Descargar JSON" class="text-indigo-500 hover:text-indigo-600 transition transform hover:scale-125">
                                                <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 20l4-16m4 4l4 4-4 4M6 16l-4-4 4-4" /></svg>
                                            </a>
                                            <form action="{{ route('dtes.reenviarCorreo', $dte->id) }}" method="POST" class="inline">
                                                @csrf
                                                <button type="submit" title="Reenviar Correo" class="text-cyan-500 hover:text-cyan-600 transition transform hover:scale-125">
                                                    <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z" /></svg>
                                                </button>
                                            </form>
                                            <button type="button" 
                                                    onclick="confirmarAnulacion({{ $dte->id }}, '{{ $dte->numero_control }}')"
                                                    title="Anular DTE" class="text-red-500 hover:text-red-700 transition transform hover:scale-125">
                                                <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 14l2-2m0 0l2-2m-2 2l-2-2m2 2l2 2m7-2a9 9 0 11-18 0 9 9 0 0118 0z" /></svg>
                                            </button>
                                        @endif
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="6" class="px-6 py-12 text-center text-gray-400 italic">No hay registros.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    
    <script>
    function confirmarAnulacion(id, numControl) {
        Swal.fire({
            title: '¿Anular Documento?',
            text: "Se enviará un DTE de Invalidación para el control: " + numControl,
            icon: 'warning',
            input: 'textarea',
            inputPlaceholder: 'Escriba el motivo de la anulación aquí...',
            showCancelButton: true,
            confirmButtonColor: '#d33',
            cancelButtonColor: '#3085d6',
            confirmButtonText: 'Sí, Anular en Hacienda',
            cancelButtonText: 'Cancelar',
            inputValidator: (value) => {
                if (!value) return '¡Es obligatorio indicar un motivo!'
            },
            showLoaderOnConfirm: true,
            preConfirm: (motivo) => {
                const form = document.createElement('form');
                form.method = 'POST';
                form.action = `/dtes/${id}/anular`;
                
                const csrfToken = document.querySelector('meta[name="csrf-token"]').getAttribute('content');
                
                form.innerHTML = `
                    <input type="hidden" name="_token" value="${csrfToken}">
                    <input type="hidden" name="motivo" value="${motivo}">
                `;
                
                document.body.appendChild(form);
                form.submit();
            }
        });
    }
    </script>

    <script>
    // Función para cargar scripts dinámicamente en orden
    function loadScript(src, callback) {
        var script = document.createElement('script');
        script.type = 'text/javascript';
        script.src = src;
        script.onload = callback;
        document.head.appendChild(script);
    }

    // 1. Cargamos jQuery
    loadScript("https://code.jquery.com/jquery-3.7.0.min.js", function() {
        console.log("jQuery cargado.");
        window.$ = window.jQuery = jQuery;

        // 2. Cargamos DataTables después de jQuery
        loadScript("https://cdn.datatables.net/1.13.7/js/jquery.dataTables.min.js", function() {
            console.log("DataTables cargado.");

            // 3. Inicializamos la tabla
            $(document).ready(function() {
                if ($.fn.DataTable) {
                    $('#tablaDtes').DataTable({
                        "order": [[0, "desc"]],
                        "language": {
                            "url": "https://cdn.datatables.net/plug-ins/1.13.7/i18n/es-ES.json"
                        },
                        "pageLength": 10,
                        "dom": '<"flex justify-between mb-4"lf>rt<"flex justify-between mt-4"ip>'
                    });
                }
            });
        });
        
        // 4. Cargamos SweetAlert (puede ser paralelo)
        loadScript("https://cdn.jsdelivr.net/npm/sweetalert2@11", function() {
            console.log("SweetAlert2 cargado.");
        });
    });
</script>
</x-app-layout>