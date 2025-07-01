<x-guest-layout>
    <div class="min-h-screen flex items-center justify-center bg-[#F8F8F9] font-[Poppins]">
        <div class="bg-white px-8 py-10 rounded-xl shadow-md w-full max-w-sm">
            <div class="flex justify-center mb-6">
                <img src="{{ asset('img/logo-pigo.svg') }}" alt="Logotipo de PIGO" class="h-36 w-auto">
            </div>

            <form method="POST" action="{{ route('login') }}">
                @csrf

                <div class="mb-4">
                    <label for="email" class="block text-sm font-medium text-gray-700">Usuario</label>
                    <input id="email" class="mt-1 block w-full border border-gray-300 rounded-md shadow-sm focus:ring focus:ring-[#174F5C]/50" type="email" name="email" placeholder="Escribe tu usuario" required autofocus />
                </div>

                <div class="mb-6">
                    <label for="password" class="block text-sm font-medium text-gray-700">Contraseña</label>
                    <input id="password" class="mt-1 block w-full border border-gray-300 rounded-md shadow-sm focus:ring focus:ring-[#174F5C]/50" type="password" name="password" placeholder="Escribe tu contraseña" required />
                </div>

                <button type="submit" class="w-full bg-[#174F5C] text-white py-2 px-4 rounded-md hover:bg-[#143E47] transition-all">
                    Iniciar sesión
                </button>
            </form>
        </div>
    </div>
</x-guest-layout>
