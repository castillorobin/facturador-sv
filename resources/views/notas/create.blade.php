<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            Generar Nota de Crédito para CCF: <span class="text-indigo-600">{{ $dteOriginal->numero_control }}</span>
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <form action="{{ route('notas.store') }}" method="POST">
                @csrf
                <input type="hidden" name="dte_origen_id" value="{{ $dteOriginal->id }}">

                <div class="bg-gray-100 p-6 rounded-t-lg border-b border-gray-300 grid grid-cols-1 md:grid-cols-3 gap-4">
                    <div>
                        <p class="text-xs font-bold text-gray-500 uppercase">Cliente Receptor</p>
                        <p class="text-sm font-bold">{{ $dteOriginal->customer->nombre }}</p>
                        <p class="text-xs text-gray-400">NIT/NRC: {{ $dteOriginal->customer->nit }}</p>
                    </div>
                    <div>
                        <p class="text-xs font-bold text-gray-500 uppercase">Código Generación Original</p>
                        <p class="text-xs font-mono">{{ $dteOriginal->codigo_generacion }}</p>
                    </div>
                    <div>
                        <p class="text-xs font-bold text-gray-500 uppercase">Total Original</p>
                        <p class="text-lg font-black text-indigo-700">$ {{ number_format($dteOriginal->total_pagar, 2) }}</p>
                    </div>
                </div>

                <div class="bg-white p-6 border-b border-gray-200">
                    <label class="block text-sm font-medium text-gray-700">Motivo de la Nota de Crédito (Hacienda lo requiere)</label>
                    <select name="tipo_nota" class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:ring-indigo-500">
                        <option value="1">Devolución de mercadería</option>
                        <option value="2">Descuento concedido</option>
                        <option value="3">Error en precio/cálculo</option>
                    </select>
                    <textarea name="motivo_detalle" rows="2" class="mt-2 block w-full border-gray-300 rounded-md" placeholder="Explique brevemente el motivo..."></textarea>
                </div>

                <div class="bg-white overflow-hidden shadow-sm p-6">
                    <h3 class="text-md font-bold mb-4 text-gray-700">Productos del CCF Original</h3>
                    <table class="w-full text-sm text-left">
                        <thead class="bg-gray-50 uppercase text-xs">
                            <tr>
                                <th class="px-4 py-2">¿Aplicar?</th>
                                <th class="px-4 py-2">Producto</th>
                                <th class="px-4 py-2 text-center">Cant. Original</th>
                                <th class="px-4 py-2 text-center">Cant. a Devolver</th>
                                <th class="px-4 py-2 text-right">Precio Unit.</th>
                                <th class="px-4 py-2 text-right">Subtotal Rebaja</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($dteOriginal->details as $index => $item)
                            <tr class="border-b">
                                <td class="px-4 py-2 text-center">
                                    <input type="checkbox" name="items[{{$index}}][selected]" value="1" class="rounded text-indigo-600 focus:ring-indigo-500">
                                    <input type="hidden" name="items[{{$index}}][product_id]" value="{{ $item->product_id }}">
                                </td>
                                <td class="px-4 py-2 font-medium">
                                    {{ $item->product->descripcion ?? 'Producto no encontrado o eliminado' }}
                                </td>
                                <td class="px-4 py-2 text-center text-gray-400">{{ $item->cantidad }}</td>
                                <td class="px-4 py-2">
                                    <input type="number" name="items[{{$index}}][cantidad]" max="{{ $item->cantidad }}" min="1" step="0.01" 
                                           value="{{ $item->cantidad }}"
                                           class="w-20 rounded border-gray-300 text-center text-xs p-1">
                                </td>
                                <td class="px-4 py-2 text-right">$ {{ number_format($item->precio_unitario, 2) }}</td>
                                <td class="px-4 py-2 text-right font-bold text-red-600">
                                    $ <span class="row-total">0.00</span>
                                </td>
                            </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>

                <div class="bg-gray-50 p-6 rounded-b-lg flex justify-between items-center border-t border-gray-200">
                    <div class="text-right w-full">
                        <p class="text-sm text-gray-600 font-bold uppercase">Total Rebaja (IVA Incluido):</p>
                        <h2 class="text-3xl font-black text-red-600">$ <span id="gran-total-nota">0.00</span></h2>
                        <div class="mt-4 flex justify-end space-x-3">
                            <a href="{{ route('dtes.index') }}" class="bg-gray-300 px-4 py-2 rounded-md font-bold text-xs uppercase">Cancelar</a>
                            <button type="submit" class="bg-indigo-600 text-white px-6 py-2 rounded-md font-bold text-xs uppercase shadow-lg hover:bg-indigo-700">
                                Generar Nota de Crédito en Hacienda
                            </button>
                        </div>
                    </div>
                </div>
            </form>
        </div>
    </div>
</x-app-layout>