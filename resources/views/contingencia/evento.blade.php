<x-app-layout>

<div class="max-w-2xl mx-auto bg-white p-8 rounded-lg shadow-md border-t-4 border-amber-500 py-10">
    <h3 class="text-lg font-bold mb-4 text-gray-800">Notificar Evento de Contingencia</h3>
    <p class="text-sm text-gray-600 mb-6">Complete este formulario para informar a Hacienda sobre la interrupción del servicio.</p>

    <form action="{{ route('contingencia.notificar') }}" method="POST" class="space-y-4">
        @csrf
        <div>
            <label class="block text-xs font-bold text-gray-700 uppercase">Motivo de Contingencia:</label>
            <select name="tipo_evento" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-amber-500 focus:ring-amber-500 sm:text-sm" required>
                <option value="1">No disponibilidad de servicios del Ministerio de Hacienda</option>
                <option value="2">Falla en el suministro de energía eléctrica</option>
                <option value="3">Mantenimiento preventivo de los sistemas informáticos</option>
                <option value="4">Falla en el servicio de Internet</option>
                <option value="5">Otras causas</option>
            </select>
        </div>

        <div class="grid grid-cols-2 gap-4">
            <div>
                <label class="block text-xs font-bold text-gray-700 uppercase">Fecha/Hora Inicio:</label>
                <input type="datetime-local" name="fecha_inicio" class="mt-1 block w-full rounded-md border-gray-300 sm:text-sm" required>
            </div>
            <div>
                <label class="block text-xs font-bold text-gray-700 uppercase">Fecha/Hora Fin:</label>
                <input type="datetime-local" name="fecha_fin" class="mt-1 block w-full rounded-md border-gray-300 sm:text-sm" required>
            </div>
        </div>

        <div>
            <label class="block text-xs font-bold text-gray-700 uppercase">Descripción detallada:</label>
            <textarea name="motivo" rows="3" class="mt-1 block w-full rounded-md border-gray-300 sm:text-sm" placeholder="Explique brevemente qué sucedió..." required></textarea>
        </div>

        <button type="submit" class="w-full bg-amber-600 text-white font-bold py-3 rounded-md hover:bg-amber-700 transition">
            Enviar Notificación a Hacienda
        </button>
    </form>
</div>

</x-app-layout>