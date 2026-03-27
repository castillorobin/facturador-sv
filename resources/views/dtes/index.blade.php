<x-app-layout>
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
                <table class="w-full text-sm text-gray-500">
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
                                
                                <td class="px-6 py-4">
                                    <div class="flex flex-col items-center">
                                        <span class="text-gray-900 font-bold">{{ $dte->fecha_emision->format('d/m/Y') }}</span>
                                        <span class="text-xs text-indigo-600 font-mono">{{ $dte->numero_control }}</span>
                                    </div>
                                </td>

                                <td class="px-6 py-4">
                                    <div class="flex flex-col items-center">
                                        <span class="font-bold text-gray-800">{{ $dte->customer->nombre }}</span>
                                        <span class="text-xs text-gray-400 italic">{{ $dte->customer->num_documento }}</span>
                                    </div>
                                </td>

                                <td class="px-6 py-4">
                                    <div class="flex justify-center">
                                        <span class="px-2 py-1 rounded bg-gray-200 text-gray-700 text-[10px] font-black uppercase">
                                            {{ $dte->tipo_dte == '01' ? 'Factura' : 'Crédito Fiscal' }}
                                        </span>
                                    </div>
                                </td>

                                <td class="px-6 py-4">
                                    <div class="flex justify-center font-black text-gray-900 text-base">
                                        $ {{ number_format($dte->total_pagar, 2) }}
                                    </div>
                                </td>

                                <td class="px-6 py-4">
    <div class="flex justify-center">
        @php
            // Definimos los colores hexadecimales directos
            $config = [
                'BORRADOR'  => ['bg' => '#f3f4f6', 'text' => '#4b5563', 'border' => '#d1d5db'],
                'PROCESADO' => ['bg' => '#dcfce7', 'text' => '#15803d', 'border' => '#86efac'],
                'RECHAZADO' => ['bg' => '#fee2e2', 'text' => '#b91c1c', 'border' => '#fca5a5'],
                'ANULADO'   => ['bg' => '#ffedd5', 'text' => '#c2410c', 'border' => '#fdba74'],
            ];
            
            $estilo = $config[$dte->estado] ?? ['bg' => '#dbeafe', 'text' => '#1d4ed8', 'border' => '#93c5fd'];
        @endphp

        <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-bold border"
              style="background-color: {{ $estilo['bg'] }} !important; 
                     color: {{ $estilo['text'] }} !important; 
                     border-color: {{ $estilo['border'] }} !important;">
            {{ $dte->estado }}
        </span>
    </div>
</td>

                                <td class="px-6 py-4">
                                    <div class="flex justify-center items-center space-x-3">
                                        <button title="Ver PDF" class="text-red-500 hover:text-red-700 transition transform hover:scale-110" >
                                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 21h10a2 2 0 002-2V9.414a1 1 0 00-.293-.707l-5.414-5.414A1 1 0 0012.586 3H7a2 2 0 00-2 2v14a2 2 0 002 2z"></path></svg>
                                        </button>
                                        @if($dte->estado == 'PROCESADO')
    <a href="{{ route('dtes.downloadJson', $dte->id) }}" 
       title="Descargar JSON" 
       class="text-blue-500 hover:text-blue-700 transition transform hover:scale-110">
        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 20l4-16m4 4l4 4-4 4M6 16l-4-4 4-4"></path>
        </svg>
    </a>
@else
    <span class="text-gray-300 cursor-not-allowed" title="JSON no disponible">
        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 20l4-16m4 4l4 4-4 4M6 16l-4-4 4-4"></path>
        </svg>
    </span>
@endif
                                        @if($dte->estado == 'BORRADOR')
                                            <form action="{{ route('dtes.enviar', $dte->id) }}" method="POST" class="inline" style="margin-left: 10px;">
                                                @csrf
                                                <button type="submit" 
                                                        title="Enviar a Hacienda" 
                                                        style="background-color: #1d558a !important; color: white !important;"
                                                        class="p-2 rounded-md hover:opacity-90 transition-all shadow-sm flex items-center justify-center active:scale-90" >
                                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z"></path>
                                                    </svg> Enviar a Hacienda
                                                </button>
                                            </form>
                                        @endif
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="6" class="px-6 py-12 text-center text-gray-400 italic">
                                    No hay registros de ventas emitidas.
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</x-app-layout>