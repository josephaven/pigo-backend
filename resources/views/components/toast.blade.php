<div
    x-data="{ mensaje: '', tipo: 'info', visible: false }"
    x-on:toast.window="
        mensaje = $event.detail.mensaje;
        tipo = $event.detail.tipo || 'info';
        visible = true;
        setTimeout(() => visible = false, 3000);
    "
    x-show="visible"
    x-transition
    x-cloak
    class="fixed bottom-6 right-6 z-50"
>
    <div
        class="flex items-center gap-2 text-white px-4 py-2 rounded-lg shadow-lg"
        :class="{
            'bg-blue-600': tipo === 'info',
            'bg-green-600': tipo === 'success',
            'bg-yellow-500': tipo === 'warning',
            'bg-red-600': tipo === 'error'
        }"
    >
        <template x-if="tipo === 'success'">
            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
            </svg>
        </template>

        <template x-if="tipo === 'error'">
            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
            </svg>
        </template>

        <template x-if="tipo === 'info'">
            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M12 18h.01" />
            </svg>
        </template>

        <span class="text-sm font-medium" x-text="mensaje"></span>
    </div>
</div>
