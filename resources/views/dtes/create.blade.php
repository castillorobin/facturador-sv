<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ __('Generar Nuevo DTE (Facturación Electrónica)') }}
        </h2>
    </x-slot>
    <link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet" />

    <style>
    .select2-container .select2-selection--single {
        height: 42px !important;
        border-color: rgb(209 213 219) !important;
        border-radius: 0.375rem !important;
        display: flex;
        align-items: center;
    }
    .select2-container--default .select2-selection--single .select2-selection__arrow {
        height: 40px !important;
    }
    .select2-container--default .select2-selection--single .select2-selection__rendered {
        line-height: 42px !important;
        color: #374151 !important;
    }
</style>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            @if ($errors->any())
    <div class="mb-6 p-4 bg-red-100 border-l-4 border-red-500 text-red-700">
        <ul class="list-disc ml-5">
            @foreach ($errors->all() as $error)
                <li>{{ $error }}</li>
            @endforeach
        </ul>
    </div>
@endif
            <form action="{{ route('dtes.store') }}" method="POST" id="dte-form">
                @csrf
                <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
                    
                    <div class="lg:col-span-2 space-y-6">
                        
                        <div class="bg-white p-6 rounded-lg shadow-sm border border-gray-200">
                            <h3 class="text-lg font-bold mb-4 text-indigo-700 border-b pb-2">1. Información del Receptor</h3>
                            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                                <div>
                                    <x-input-label for="customer_id" value="Seleccionar Cliente" />
                                    <select name="customer_id" id="customer_id" class="mt-1 block w-full border-gray-300 rounded-md shadow-sm select2" required>
                                        <option value="">-- Buscar Cliente --</option>
                                        @foreach($customers as $customer)
                                            <option value="{{ $customer->id }}">{{ $customer->nombre }} ({{ $customer->num_documento }})</option>
                                        @endforeach
                                    </select>
                                </div>
                                <div>
                                    <x-input-label for="tipo_dte" value="Tipo de Documento" />
                                    <select name="tipo_dte" id="tipo_dte" class="mt-1 block w-full border-gray-300 rounded-md shadow-sm">
                                        <option value="01">01 - Consumidor Final</option>
                                        <option value="03">03 - Comprobante de Crédito Fiscal</option>
                                        <option value="14">14 - Factura de Sujeto Excluido</option>
                                    </select>
                                </div>
                            </div>
                        </div>

                        <div class="bg-white p-6 rounded-lg shadow-sm border border-gray-200">
                            <h3 class="text-lg font-bold mb-4 text-indigo-700 border-b pb-2">2. Detalle de Productos</h3>
                            
                            <div class="flex gap-2 mb-6">
                                <div class="flex-1">
                                    <select id="product_search" class="w-full border-gray-300 rounded-md shadow-sm select2">
                                        <option value="">-- Buscar Producto --</option>
                                        @foreach($products as $product)
                                            <option value="{{ $product->id }}" 
                                                    data-precio="{{ $product->precio_unitario }}"
                                                    data-nombre="{{ $product->nombre }}"
                                                    data-exento="{{ $product->es_exento }}">
                                                {{ $product->nombre }} - ${{ number_format($product->precio_unitario, 2) }}
                                            </option>
                                        @endforeach
                                    </select>
                                </div>
                                <button type="button" id="btn-add-product" class="inline-flex items-center px-6 py-2 bg-gray-800 border border-transparent rounded-md font-bold text-xs text-white uppercase tracking-widest hover:bg-gray-700 active:bg-gray-900 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2 transition ease-in-out duration-150">
                                    + Agregar
                                </button>
                            </div>

                            <table class="w-full text-sm text-center border-collapse" id="items-table">
                                <thead class="bg-gray-50 text-gray-700 uppercase text-xs">
                                    <tr>
                                        <th class="p-3 border">Cant.</th>
                                        <th class="p-3 border text-left">Descripción</th>
                                        <th class="p-3 border">Precio Unit.</th>
                                        <th class="p-3 border">Subtotal</th>
                                        <th class="p-3 border">Acción</th>
                                    </tr>
                                </thead>
                                <tbody class="divide-y divide-gray-200">
                                    </tbody>
                            </table>
                        </div>
                    </div>

                    <div class="lg:col-span-1">
                        <div class="bg-white p-6 rounded-lg shadow-md border-2 border-indigo-100 sticky top-6">
                            <h3 class="text-lg font-bold mb-6 text-center text-gray-800 uppercase tracking-wider border-b pb-2">Resumen de Venta</h3>
                            
                            <div class="space-y-4">
                                <div class="flex justify-between text-gray-600">
                                    <span>Ventas Gravadas:</span>
                                    <span id="lbl-gravado">$ 0.00</span>
                                </div>
                                <div class="flex justify-between text-gray-600 border-b pb-2">
                                    <span>Ventas Exentas:</span>
                                    <span id="lbl-exento">$ 0.00</span>
                                </div>
                                <div class="flex justify-between text-lg font-semibold text-indigo-600">
                                    <span>IVA (13%):</span>
                                    <span id="lbl-iva">$ 0.00</span>
                                </div>
                                <div class="flex justify-between text-2xl font-black text-gray-900 pt-4 border-t-2 border-gray-100">
                                    <span>TOTAL:</span>
                                    <span id="lbl-total">$ 0.00</span>
                                </div>
                            </div>

                            <div class="mt-8 space-y-3 flex flex-col items-center"> <button type="submit" 
                                        style="background-color: #059669 !important; color: white !important;"
                                        class="w-full inline-flex justify-center items-center px-4 py-4 border border-transparent rounded-lg font-black text-sm uppercase tracking-widest hover:opacity-90 active:scale-95 transition-all duration-150 shadow-lg">
                                    Emitir Factura (DTE)
                                </button>
                                
                                <a href="{{ route('dashboard') }}" class="text-sm text-gray-500 hover:underline mt-4">
                                    Cancelar Venta
                                </a>
                            </div>
                        </div>
                    </div>

                </div>
            </form>
        </div>
    </div>

    @push('scripts')
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>
    <script>
    $(document).ready(function() {
        let items = [];

        // 1. Evento para el botón Agregar
        $('#btn-add-product').on('click', function() {
            const select = $('#product_search');
            const productId = select.val();
            
            if (!productId) {
                alert('Por favor selecciona un producto');
                return;
            }

            const option = select.find(':selected');
            const nombre = option.data('nombre');
            const precio = parseFloat(option.data('precio'));
            const esExento = option.data('exento') == 1;

            // Evitar duplicados (opcional, incrementa cantidad si ya existe)
            addItem(productId, nombre, precio, esExento);
            
            // Limpiar buscador
            select.val('').trigger('change');
        });

        function addItem(id, nombre, precio, exento) {
            const rowId = Date.now(); // ID único para la fila
            const row = `
    <tr id="row-${rowId}" class="item-row" data-exento="${exento}">
        <td class="p-2 border">
            <input type="number" name="items[${id}][cantidad]" value="1" min="1" 
                   class="w-20 text-center border-gray-300 rounded qty-input" 
                   onchange="calculateTotals()">
        </td>
        <td class="p-2 border text-left">
            <span class="font-medium">${nombre}</span>
            <input type="hidden" name="items[${id}][product_id]" value="${id}">
        </td>
        <td class="p-2 border font-mono">
            $${precio.toFixed(2)}
            <input type="hidden" name="items[${id}][precio_unitario]" class="price-input" value="${precio}">
        </td>
        <td class="p-2 border font-bold text-gray-700 subtotal-column">
            $${precio.toFixed(2)}
        </td>
        <td class="p-2 border">
            <button type="button" onclick="removeRow(${rowId})" class="text-red-500 hover:text-red-700">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"></path></svg>
            </button>
        </td>
    </tr>`;
            
            $('#items-table tbody').append(row);
            calculateTotals();
        }

        // 2. Función Global para eliminar filas
        window.removeRow = function(rowId) {
            $(`#row-${rowId}`).remove();
            calculateTotals();
        };

        // 3. El motor de cálculos (Lógica de El Salvador)
        window.calculateTotals = function() {
            let totalGravado = 0;
            let totalExento = 0;
            let totalIva = 0;

            $('.item-row').each(function() {
                const qty = parseFloat($(this).find('.qty-input').val()) || 0;
                const price = parseFloat($(this).find('.price-input').val()) || 0;
                const isExento = $(this).data('exento') == true;
                
                const subtotal = qty * price;
                $(this).find('.subtotal-column').text('$' + subtotal.toFixed(2));

                if (isExento) {
                    totalExento += subtotal;
                } else {
                    // En El Salvador, el precio unitario suele llevar el IVA incluido.
                    // Si el precio ya tiene IVA, debemos "desglosarlo" para el total gravado.
                    // Base = Subtotal / 1.13
                    const base = subtotal / 1.13;
                    const iva = subtotal - base;
                    
                    totalGravado += base;
                    totalIva += iva;
                }
            });

            const granTotal = totalGravado + totalExento + totalIva;

            // Actualizar etiquetas visuales
            $('#lbl-gravado').text('$' + totalGravado.toFixed(2));
            $('#lbl-exento').text('$' + totalExento.toFixed(2));
            $('#lbl-iva').text('$' + totalIva.toFixed(2));
            $('#lbl-total').text('$' + granTotal.toFixed(2));
        };

        $('#dte-form').on('submit', function(e) {
    console.log("Intentando enviar formulario...");
    const itemCount = $('.item-row').length;
    
    if (itemCount === 0) {
        e.preventDefault();
        alert('Debes agregar al menos un producto a la factura.');
        return false;
    }
});
    });

    $(document).ready(function() {
    // Inicializar Select2 en Cliente
    $('#customer_id').select2({
        placeholder: "Escriba nombre o documento del cliente...",
        allowClear: true,
        width: '100%'
    });

    // Inicializar Select2 en Buscador de Productos
    $('#product_search').select2({
        placeholder: "Escriba el nombre del producto...",
        allowClear: true,
        width: '100%'
    });

    // Tu lógica existente de agregar productos se mantiene igual, 
    // pero al limpiar el buscador debemos usar .trigger('change') para Select2
    $('#btn-add-product').on('click', function() {
        // ... (tu lógica existente) ...
        
        // Al final, para limpiar el buscador con Select2:
        $('#product_search').val(null).trigger('change');
    });
});
</script>
@endpush
  
</x-app-layout>