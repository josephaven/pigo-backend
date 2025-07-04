<x-app-layout>
    <x-slot name="header">
        <div class="flex justify-between items-center">
            <h2 class="text-2xl font-bold text-gray-800 dark:text-white">
                ¡Bienvenido, {{ Auth::user()->name }}!
            </h2>
            <div class="flex gap-4 items-center text-sm text-gray-500 dark:text-gray-300">
                <span class="font-semibold">{{ Auth::user()->rol->nombre ?? 'Rol desconocido' }}</span>
                <span class="px-2 py-1 rounded bg-gray-100 dark:bg-gray-700 text-xs">
                    {{ Auth::user()->sucursal->nombre ?? 'Sin sucursal' }}
                </span>
            </div>
        </div>
    </x-slot>

    <div class="py-10 px-6 max-w-7xl mx-auto">
        <div class="flex gap-4 mb-6">
            <a href="#" class="bg-indigo-600 hover:bg-indigo-700 text-white px-4 py-2 rounded shadow">+ Nuevo pedido</a>
            <a href="#" class="bg-indigo-600 hover:bg-indigo-700 text-white px-4 py-2 rounded shadow">+ Nuevo cliente</a>
        </div>

        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-6">
            <!-- Tarjeta 1 -->
            <div class="bg-white dark:bg-gray-800 p-5 rounded shadow">
                <p class="text-sm text-gray-500 dark:text-gray-300">Pedidos del día</p>
                <h3 class="text-xl font-bold text-gray-800 dark:text-white">5 pedidos</h3>
            </div>

            <!-- Tarjeta 2 -->
            <div class="bg-white dark:bg-gray-800 p-5 rounded shadow">
                <p class="text-sm text-gray-500 dark:text-gray-300">Ganancias del día</p>
                <h3 class="text-xl font-bold text-green-600">$1,245.00 MXN</h3>
            </div>

            <!-- Tarjeta 3 -->
            <div class="bg-white dark:bg-gray-800 p-5 rounded shadow">
                <p class="text-sm text-gray-500 dark:text-gray-300">Pedidos pendientes</p>
                <h3 class="text-xl font-bold text-gray-800 dark:text-white">10 pedidos</h3>
            </div>

            <!-- Tarjeta 4 -->
            <div class="bg-white dark:bg-gray-800 p-5 rounded shadow">
                <p class="text-sm text-gray-500 dark:text-gray-300">Insumos en stock</p>
                <h3 class="text-xl font-bold text-gray-800 dark:text-white">120 insumos</h3>
            </div>

            <!-- Tarjeta 5 -->
            <div class="bg-white dark:bg-gray-800 p-5 rounded shadow">
                <p class="text-sm text-gray-500 dark:text-gray-300">Alertas de insumos</p>
                <h3 class="text-xl font-bold text-red-600">5 en nivel mínimo</h3>
            </div>

            <!-- Tarjeta 6 -->
            <div class="bg-white dark:bg-gray-800 p-5 rounded shadow">
                <p class="text-sm text-gray-500 dark:text-gray-300">Mermas registradas</p>
                <h3 class="text-xl font-bold text-orange-500">2 hoy</h3>
            </div>
        </div>
    </div>
</x-app-layout>
