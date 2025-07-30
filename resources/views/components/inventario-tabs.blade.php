<div class="w-full overflow-x-auto border-b border-gray-200">
    <nav class="flex items-end gap-4 sm:gap-6 text-sm font-medium px-2">
        <a href="{{ route('inventario.insumos') }}"
           class="{{ $tabActivo === 'insumos'
                        ? 'text-[#003844] font-semibold border-b-2 border-[#003844]'
                        : 'text-gray-500 hover:text-[#003844] hover:border-[#003844] border-b-2 border-transparent' }} pb-2 transition whitespace-nowrap">
            Insumos
        </a>

        <a href="{{ route('inventario.traslados') }}"
           class="{{ $tabActivo === 'traslados'
                        ? 'text-[#003844] font-semibold border-b-2 border-[#003844]'
                        : 'text-gray-500 hover:text-[#003844] hover:border-[#003844] border-b-2 border-transparent' }} pb-2 transition whitespace-nowrap">
            Transferencia de insumos
        </a>

        <a href="{{ route('inventario.mermas') }}"
           class="{{ $tabActivo === 'mermas'
                        ? 'text-[#003844] font-semibold border-b-2 border-[#003844]'
                        : 'text-gray-500 hover:text-[#003844] hover:border-[#003844] border-b-2 border-transparent' }} pb-2 transition whitespace-nowrap">
            Merma
        </a>

        <a href="{{ route('inventario.categorias') }}"
           class="{{ $tabActivo === 'categorias'
                        ? 'text-[#003844] font-semibold border-b-2 border-[#003844]'
                        : 'text-gray-500 hover:text-[#003844] hover:border-[#003844] border-b-2 border-transparent' }} pb-2 transition whitespace-nowrap">
            Categorías
        </a>
    </nav>
</div>
