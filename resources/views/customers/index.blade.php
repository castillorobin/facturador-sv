<x-app-layout>
    <x-slot name="header">
    <div class="flex justify-between items-center">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ __('Clientes') }}
        </h2>
        <a href="{{ route('customers.create') }}" class="inline-flex items-center px-4 py-2 bg-gray-800 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-gray-700 focus:bg-gray-700 active:bg-gray-900 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2 transition ease-in-out duration-150">
            {{ __('+ Nuevo Cliente') }}
        </a>
    </div>
</x-slot>
    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6 text-gray-900">
                    
                    @if(session('success'))
                        <div class="mb-4 p-4 bg-green-100 border-l-4 border-green-500 text-green-700">
                            {{ session('success') }}
                        </div>
                    @endif
@if(session('error'))
    <div class="mb-4 p-4 bg-red-100 border-l-4 border-red-500 text-red-700">
        {{ session('error') }}
    </div>
@endif
                    <div class="overflow-x-auto shadow-lg sm:rounded-lg border border-gray-200">
    <table class="w-full text-sm text-gray-500">
        <thead class="text-xs text-gray-700 uppercase bg-gray-100 border-b border-gray-300">
            <tr>
                <th scope="col" class="px-6 py-4 text-center">Cliente / Razón Social</th>
                <th scope="col" class="px-6 py-4 text-center">Documento</th>
                <th scope="col" class="px-6 py-4 text-center">Estado Fiscal</th>
                <th scope="col" class="px-6 py-4 text-center">Acciones</th>
            </tr>
        </thead>
        <tbody class="divide-y divide-gray-200">
    @forelse($customers as $customer)
        <tr class="bg-white hover:bg-indigo-50 odd:bg-white even:bg-gray-50 transition-colors duration-200">
            
            <td class="px-6 py-4 text-center font-medium text-gray-900">
                <div class="flex flex-col items-center justify-center">
                    <span class="font-bold">{{ $customer->nombre }}</span>
                    <span class="text-xs text-gray-400 font-normal">{{ $customer->email ?? 'Sin correo' }}</span>
                </div>
            </td>
            
            <td class="px-6 py-4">
                <div class="flex items-center justify-center space-x-2">
                    <span class="px-2 py-1 bg-gray-200 text-gray-700 rounded text-xs font-bold">
                        {{ $customer->tipo_documento == '36' ? 'NIT' : 'DUI' }}
                    </span>
                    <span class="text-gray-600">{{ $customer->num_documento }}</span>
                </div>
            </td>

            <td class="px-6 py-4">
                <div class="flex items-center justify-center">
                    @if($customer->nrc)
                        <span class="inline-flex items-center px-3 py-1 rounded-full text-xs font-medium bg-purple-100 text-purple-800 border border-purple-200">
                            <svg class="w-2 h-2 mr-1.5 fill-current text-purple-500" viewBox="0 0 8 8"><circle cx="4" cy="4" r="3"></circle></svg>
                            Contribuyente
                        </span>
                    @else
                        <span class="inline-flex items-center px-3 py-1 rounded-full text-xs font-medium bg-blue-100 text-blue-800 border border-blue-200">
                            <svg class="w-2 h-2 mr-1.5 fill-current text-blue-500" viewBox="0 0 8 8"><circle cx="4" cy="4" r="3"></circle></svg>
                            Consumidor Final
                        </span>
                    @endif
                </div>
            </td>

            <td class="px-6 py-4 text-center">
    <div class="flex justify-center items-center space-x-4">
        {{-- Botón Editar --}}
        <a href="{{ route('customers.edit', $customer) }}" class="text-indigo-600 hover:text-indigo-900 font-semibold flex items-center transition-transform hover:scale-105">
            <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"></path></svg>
            Editar
        </a>

        {{-- Botón Borrar --}}
        <button type="button" 
                class="text-red-600 hover:text-red-900 font-semibold flex items-center transition-transform hover:scale-105" 
                onclick="confirmDelete('{{ $customer->id }}')">
            <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"></path></svg>
            Borrar
        </button>

        {{-- Formulario Oculto --}}
        <form id="delete-form-{{ $customer->id }}" action="{{ route('customers.destroy', $customer) }}" method="POST" class="hidden">
            @csrf 
            @method('DELETE')
        </form>
    </div>
</td>
        </tr>
    @empty
        ...
    @endforelse
</tbody>
    </table>
</div>
                </div>
            </div>
        </div>
    </div>


    <script>
        function confirmDelete(id) {
    Swal.fire({
        title: '¿Estás seguro?',
        text: "Esta acción no se puede deshacer.",
        icon: 'warning',
        showCancelButton: true,
        confirmButtonColor: '#4f46e5', // Indigo-600
        cancelButtonColor: '#ef4444', // Red-600
        confirmButtonText: 'Sí, borrar',
        cancelButtonText: 'Cancelar'
    }).then((result) => {
        if (result.isConfirmed) {
            document.getElementById('delete-form-' + id).submit();
        }
    })
}
    </script>
</x-app-layout>