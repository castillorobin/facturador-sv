<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ __('Inicio') }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6 text-gray-900">
                    {{ __("¡Bienvenido a tu panel de control!") }}

                    <div class="grid grid-cols-1 md:grid-cols-3 gap-6 mb-8">
                        <div class="bg-white p-6 rounded-xl shadow-sm border border-gray-100">
                            <div class="flex items-center">
                                <div class="p-3 bg-green-100 rounded-full text-green-600">
                                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                                </div>
                                <div class="ml-4">
                                    <p class="text-sm font-medium text-gray-500 uppercase">Ventas del Mes</p>
                                    <p class="text-2xl font-bold text-gray-900">${{ number_format($totalVentasMes, 2) }}</p>
                                </div>
                            </div>
                        </div>

                        <div class="bg-white p-6 rounded-xl shadow-sm border border-gray-100">
                            <div class="flex items-center">
                                <div class="p-3 bg-indigo-100 rounded-full text-indigo-600">
                                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path></svg>
                                </div>
                                <div class="ml-4">
                                    <p class="text-sm font-medium text-gray-500 uppercase">DTEs Emitidos del mes</p>
                                    <p class="text-2xl font-bold text-gray-900">{{ $conteoDtesMes }}</p>
                                </div>
                            </div>
                        </div>

                        <div class="bg-white p-6 rounded-xl shadow-sm border {{ $pendientesContingencia > 0 ? 'border-red-200 bg-red-50' : 'border-gray-100' }}">
                            <div class="flex items-center">
                                <div class="p-3 {{ $pendientesContingencia > 0 ? 'bg-red-100 text-red-600' : 'bg-gray-100 text-gray-400' }} rounded-full">
                                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"></path></svg>
                                </div>
                                <div class="ml-4">
                                    <p class="text-sm font-medium {{ $pendientesContingencia > 0 ? 'text-red-700' : 'text-gray-500' }} uppercase">Pendientes Transmitir</p>
                                    <p class="text-2xl font-bold {{ $pendientesContingencia > 0 ? 'text-red-800' : 'text-gray-900' }}">{{ $pendientesContingencia }}</p>
                                </div>
                            </div>
                        </div>
                    </div>


                    <br>


                    <div class="bg-white rounded-xl shadow-sm border border-gray-100 overflow-hidden">
    <div class="p-6 border-b border-gray-100 flex justify-between items-center">
        <h3 class="font-bold text-gray-800 text-lg">Últimos Documentos Generados</h3>
        <a href="{{ route('dtes.index') }}" class="text-sm text-indigo-600 font-semibold hover:underline">Ver todos →</a>
    </div>
    <div class="overflow-x-auto">
        <table class="w-full text-left">
            <thead class="bg-gray-50 text-gray-500 text-xs uppercase font-semibold">
                <tr>
                    <th class="px-6 py-4">Código / Control</th>
                    <th class="px-6 py-4">Cliente</th>
                    <th class="px-6 py-4">Total</th>
                    <th class="px-6 py-4 text-center">Estado</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-gray-100">
                @foreach($ultimosDtes as $dte)
                <tr class="hover:bg-gray-50 transition">
                    <td class="px-6 py-4">
                        <div class="text-sm font-bold text-gray-900">{{ $dte->numero_control }}</div>
                        <div class="text-xs text-gray-400 font-mono">{{ substr($dte->codigo_generacion, 0, 8) }}...</div>
                    </td>
                    <td class="px-6 py-4 text-sm text-gray-600">
                        {{ $dte->customer->nombre }}
                    </td>
                    <td class="px-6 py-4 text-sm font-black text-gray-900">
                        ${{ number_format($dte->total_pagar, 2) }}
                    </td>
                    <td class="px-6 py-4 text-center">
                        <span class="px-3 py-1 rounded-full text-xs font-bold 
                            {{ $dte->estado == 'PROCESADO' ? 'bg-green-100 text-green-700' : 'bg-orange-100 text-orange-700' }}">
                            {{ $dte->estado }}
                        </span>
                    </td>
                </tr>
                @endforeach
            </tbody>
        </table>
    </div>
</div>
            </div>
        </div>
    </div>
</x-app-layout>
