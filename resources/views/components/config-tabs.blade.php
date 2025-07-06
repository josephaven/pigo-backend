<div class="w-fit border-b border-gray-200">
    <nav class="flex gap-6 text-sm font-medium">
        <a href="{{ route('configuracion.empleados') }}"
           class="{{ $tabActivo === 'empleados'
                        ? 'text-[#003844] font-semibold border-b-2 border-[#003844]'
                        : 'text-gray-500 hover:text-[#003844] hover:border-[#003844] border-b-2 border-transparent' }} pb-2 transition">
            Empleados
        </a>
        <a href="{{ route('configuracion.sucursales') }}"
           class="{{ $tabActivo === 'sucursales'
                        ? 'text-[#003844] font-semibold border-b-2 border-[#003844]'
                        : 'text-gray-500 hover:text-[#003844] hover:border-[#003844] border-b-2 border-transparent' }} pb-2 transition">
            Sucursales
        </a>
        <a href="{{ route('configuracion.metodos-pago') }}"
           class="{{ $tabActivo === 'metodos'
                        ? 'text-[#003844] font-semibold border-b-2 border-[#003844]'
                        : 'text-gray-500 hover:text-[#003844] hover:border-[#003844] border-b-2 border-transparent' }} pb-2 transition">
            Métodos de pago
        </a>
    </nav>
</div>
