<div>
    <dialog id="myModallink" class="h-1/1 w-80 lg:w-96  p-3 rounded-2xl ">

        <!--bt_cerrar_modal-->
        <button id="bt_cerrar_modal_link" type="button"
            class="cursor-pointer absolute top-0 right-0 mt-2 mr-2 text-gray-500 hover:text-gray-700 transition duration-150 ease-in-out rounded focus:ring-2 focus:outline-none focus:ring-gray-600">
            <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" viewBox="0 0 20 20" fill="currentColor">
                <path fill-rule="evenodd"
                    d="M5.293 6.707a1 1 0 011.414 0L10 8.586l3.293-3.293a1 1 0 111.414 1.414L11.414 10l3.293 3.293a1 1 0 01-1.414 1.414L10 11.414l-3.293 3.293a1 1 0 01-1.414-1.414L8.586 10 5.293 6.707z"
                    clip-rule="evenodd" />
            </svg>
        </button>

        <div class=" flex items-center justify-center ">

            <div class="container mx-auto p-4">
                <h2 class="text-2xl font-bold mb-4 border-b-2">Agregar Link</h2>
                <div id="atributos" class="">
                    <div class="mb-4">
                        {{-- datos para mi js by Julico --}}
                        <div>
                            <x-label for="mensaje" value="{{ __('Nombre del Metodo:') }}" />
                            <x-input id="mensaje" class="block mt-1 w-full" type="text" name="mensaje" required autofocus/>
                        </div>
                    </div>
                    <div class="mb-4">
                        <x-label for="metodo" value="{{ __('Tipo:') }}" />
                        <select name="metodo" id="metodo" class="w-full border border-gray-300 px-4 py-2 rounded-md">
                            <option disabled selected>Elija el metodo</option>
                                <option value="GET()">GET</option>
                                <option value="SET()">SET</option>
                                <option value="PUT()">UPDATE</option>
                                <option value="DELETE">DELET</option>
                        </select>
                    </div>
                    <div class="text-center">
                        <x-button id="bt_save_link" class="ml-4">
                            {{ __('Guardar') }}
                        </x-button>
                    </div>
                </div>
            </div>
        </div>
    </dialog>
</div>
