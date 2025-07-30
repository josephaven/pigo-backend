<div class="w-full overflow-x-auto border-b border-gray-200">
    <nav class="flex items-end gap-4 sm:gap-6 text-sm font-medium px-2">
        <a href="{{ route('pedidos') }}"
           class="{{ $tabActivo === 'pedidos'
                        ? 'text-[#003844] font-semibold border-b-2 border-[#003844]'
                        : 'text-gray-500 hover:text-[#003844] hover:border-[#003844] border-b-2 border-transparent' }} pb-2 transition whitespace-nowrap">
            General
        </a>
        <a href="{{ route('pedidos.para-elaboracion') }}"
           class="{{ $tabActivo === 'para-elaboracion'
                        ? 'text-[#003844] font-semibold border-b-2 border-[#003844]'
                        : 'text-gray-500 hover:text-[#003844] hover:border-[#003844] border-b-2 border-transparent' }} pb-2 transition whitespace-nowrap">
            Para elaboración
        </a>
    </nav>
</div>
