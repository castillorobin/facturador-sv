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

                                <td class="px-6 py-4 whitespace-nowrap text-sm font-medium">
    <div class="flex items-center justify-center space-x-5 gap-2">
        
      @if(isset($dte->estado) && strtoupper($dte->estado) == 'BORRADOR')
    <form action="{{ route('dtes.enviar', $dte->id) }}" method="POST" class="inline">
        @csrf
        <button type="submit" 
                class="inline-flex items-center px-4 py-2 rounded-md font-bold text-xs uppercase tracking-widest shadow-sm transition"
                style="background-color: #0d6efd !important; color: #ffffff !important; border: 1px solid #0a58ca !important; cursor: pointer;">
            
            <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 mr-2" fill="none" viewBox="0 0 24 24" stroke="white" style="width: 16px; height: 16px;">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z" />
            </svg>

            ENVIAR A MH
        </button>
    </form>
@endif

        @if($dte->estado == 'PROCESADO')
            <a href="{{ route('dtes.verPdf', $dte->id) }}" target="_blank" title="Ver Factura PDF"
               class="text-rose-500 hover:text-rose-600 transition transform hover:scale-125">
                <svg class="w-6 h-6 text-gray-800 dark:text-white" aria-hidden="true" xmlns="http://www.w3.org/2000/svg" width="24" height="24" fill="currentColor" viewBox="0 0 24 24">
  <path fill-rule="evenodd" d="M9 2.221V7H4.221a2 2 0 0 1 .365-.5L8.5 2.586A2 2 0 0 1 9 2.22ZM11 2v5a2 2 0 0 1-2 2H4a2 2 0 0 0-2 2v7a2 2 0 0 0 2 2 2 2 0 0 0 2 2h12a2 2 0 0 0 2-2 2 2 0 0 0 2-2v-7a2 2 0 0 0-2-2V4a2 2 0 0 0-2-2h-7Zm-6 9a1 1 0 0 0-1 1v5a1 1 0 1 0 2 0v-1h.5a2.5 2.5 0 0 0 0-5H5Zm1.5 3H6v-1h.5a.5.5 0 0 1 0 1Zm4.5-3a1 1 0 0 0-1 1v5a1 1 0 0 0 1 1h1.376A2.626 2.626 0 0 0 15 15.375v-1.75A2.626 2.626 0 0 0 12.375 11H11Zm1 5v-3h.375a.626.626 0 0 1 .625.626v1.748a.625.625 0 0 1-.626.626H12Zm5-5a1 1 0 0 0-1 1v5a1 1 0 1 0 2 0v-1h1a1 1 0 1 0 0-2h-1v-1h1a1 1 0 1 0 0-2h-2Z" clip-rule="evenodd"/>
</svg>

            </a>

            <a href="{{ route('dtes.downloadJson', $dte->id) }}" title="Descargar JSON"
               class="text-indigo-500 hover:text-indigo-600 transition transform hover:scale-125">
                <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 20l4-16m4 4l4 4-4 4M6 16l-4-4 4-4" />
                </svg>
            </a>
        @endif

        <button type="button" title="{{ $dte->estado == 'PROCESADO' ? 'Anular DTE' : 'Eliminar' }}"
                class="text-gray-400 hover:text-red-600 transition transform hover:scale-125">
            <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
            </svg>
        </button>

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