<div class="border-b border-gray-200 mb-4">
    <nav class="flex gap-6 text-sm font-medium -mb-[1px]">
        <a href="{{ route('inventario.insumos') }}"
           class="pb-2 border-b-2 {{ $tabActivo === 'insumos' ? 'border-[#003844] text-[#003844]' : 'border-transparent text-gray-500 hover:text-[#003844] hover:border-[#003844]' }}">
            Insumos
        </a>

        <a href="{{ route('inventario.categorias') }}"
           class="pb-2 border-b-2 {{ $tabActivo === 'categorias' ? 'border-[#003844] text-[#003844]' : 'border-transparent text-gray-500 hover:text-[#003844] hover:border-[#003844]' }}">
            Categorías
        </a>

        <a href="{{ route('inventario.traslados') }}"
           class="pb-2 border-b-2 {{ $tabActivo === 'traslados' ? 'border-[#003844] text-[#003844]' : 'border-transparent text-gray-500 hover:text-[#003844] hover:border-[#003844]' }}">
            Traslados
        </a>

        <a href="{{ route('inventario.mermas') }}"
           class="pb-2 border-b-2 {{ $tabActivo === 'mermas' ? 'border-[#003844] text-[#003844]' : 'border-transparent text-gray-500 hover:text-[#003844] hover:border-[#003844]' }}">
            Mermas
        </a>
    </nav>
</div>
