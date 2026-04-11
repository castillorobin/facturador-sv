<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            Nota de Crédito Manual para: <span class="text-indigo-600">{{ $dteOriginal->numero_control }}</span>
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-4xl mx-auto sm:px-6 lg:px-8">

         
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
            <form action="{{ route('notas.store') }}" method="POST" class="bg-white shadow-lg rounded-lg overflow-hidden">
                @csrf
                <input type="hidden" name="dte_origen_id" value="{{ $dteOriginal->id }}">
                <input type="hidden" name="modalidad" value="manual">

                <div class="bg-gray-800 p-4 text-white flex justify-between items-center">
                    <div>
                        <p class="text-xs uppercase opacity-70">Cliente</p>
                        <p class="font-bold">{{ $dteOriginal->customer->nombre }}</p>
                    </div>
                    <div class="text-right">
                        <p class="text-xs uppercase opacity-70">Monto Máximo (CCF)</p>
                        <p class="font-bold text-xl">$ {{ number_format($dteOriginal->total_pagar, 2) }}</p>
                        <input type="hidden" id="limite_maximo" value="{{ $dteOriginal->total_pagar }}">
                    </div>
                </div>

                <div class="p-6 space-y-6">
                    <div>
                        <label class="block text-sm font-bold text-gray-700 uppercase">Motivo del Ajuste</label>
                        <select name="motivo_id" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:ring-indigo-500">
                            <option value="2">Descuento concedido</option>
                            <option value="3">Anulación parcial por ajuste de precio</option>
                            <option value="4">Anulación parcial por error en el producto</option>
                            <option value="5">Anulación parcial por error en la cantidad</option>
                        </select>
                        <textarea name="motivo_detalle" rows="2" required class="mt-2 block w-full border-gray-300 rounded-md" placeholder="Describa el ajuste (ej: Descuento comercial por pronto pago)"></textarea>
                    </div>

                    <hr>

                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                        <div class="bg-indigo-50 p-4 rounded-lg border border-indigo-100">
                            <label class="block text-sm font-bold text-indigo-900">Monto de la Rebaja (Sin IVA)</label>
                            <div class="relative mt-1">
                                <span class="absolute inset-y-0 left-0 pl-3 flex items-center text-gray-500">$</span>
                                <input type="number" name="monto_ajuste" id="monto_ajuste" step="0.01" required
                                       class="block w-full pl-7 pr-12 border-gray-300 rounded-md focus:ring-indigo-500 text-lg font-bold">
                            </div>
                            <p class="text-[10px] text-indigo-600 mt-1 italic">* El sistema sumará el 13% automáticamente.</p>
                        </div>

                        <div class="bg-gray-50 p-4 rounded-lg border border-gray-200">
                            <div class="flex justify-between text-sm mb-1">
                                <span>Subtotal:</span>
                                <span id="resumen_subtotal">$ 0.00</span>
                            </div>
                            <div class="flex justify-between text-sm mb-1 text-gray-600">
                                <span>IVA (13%):</span>
                                <span id="resumen_iva">$ 0.00</span>
                            </div>
                            <div class="flex justify-between text-lg font-black border-t pt-2 text-red-600">
                                <span>TOTAL NOTA:</span>
                                <span id="resumen_total">$ 0.00</span>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="bg-gray-100 p-4 flex justify-end space-x-3">
                    <a href="{{ route('dtes.index') }}" class="px-4 py-2 text-gray-600 font-bold uppercase text-xs">Cancelar</a>
                    <button type="submit" id="btn_enviar" class="bg-red-600 hover:bg-red-700 text-white px-6 py-2 rounded-md font-bold uppercase text-xs shadow-md transition">
                        Emitir Nota de Crédito
                    </button>
                </div>
            </form>
        </div>
    </div>

    <script>
        document.getElementById('monto_ajuste').addEventListener('input', function(e) {
            const montoSinIva = parseFloat(e.target.value) || 0;
            const limiteMax = parseFloat(document.getElementById('limite_maximo').value);
            
            const iva = montoSinIva * 0.13;
            const total = montoSinIva + iva;

            // Actualizar resumen visual
            document.getElementById('resumen_subtotal').innerText = '$ ' + montoSinIva.toFixed(2);
            document.getElementById('resumen_iva').innerText = '$ ' + iva.toFixed(2);
            document.getElementById('resumen_total').innerText = '$ ' + total.toFixed(2);

            // Validar que no exceda el CCF original
            const btn = document.getElementById('btn_enviar');
            if (total > (limiteMax + 0.01)) { // Margen por decimales
                btn.disabled = true;
                btn.classList.add('opacity-50', 'cursor-not-allowed');
                alert("El total de la Nota de Crédito no puede ser mayor al CCF original.");
            } else {
                btn.disabled = false;
                btn.classList.remove('opacity-50', 'cursor-not-allowed');
            }
        });
    </script>

</x-app-layout>