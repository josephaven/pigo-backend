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
            <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="lucide lucide-info-icon lucide-info"><circle cx="12" cy="12" r="10"/><path d="M12 16v-4"/><path d="M12 8h.01"/></svg>
        </template>

        <span class="text-sm font-medium" x-text="mensaje"></span>
    </div>
</div>
