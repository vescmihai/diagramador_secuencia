@extends('layouts.app')

@section('content')
    @vite(['resources/js/diagramador.js', 'resources/js/custom-modal.js'])
    <main>
        <x-custom-modal></x-custom-modal>

            <div class="mx-auto max-w-7xl py-6 sm:px-6 lg:px-8">

                <div class="flex justify-between">
                    <div class="flex space-x-4">
                        {{-- <button id="bt_adicionar" class="bg-blue-500 hover:bg-blue-700 text-white font-bold py-2 px-4 rounded">
                        bt_adicionar
                    </button> --}}
                        <button id="bt_abrir_modal"
                            class="bg-blue-500 hover:bg-blue-700 text-white font-bold py-2 px-4 rounded">
                            abrir modal
                        </button>
                        <a href="{{ route('exportarCodigoZip') }}"
                        class="bg-green-500 hover:bg-green-700 text-white font-bold py-2 px-4 rounded">
                        Exportar Código Zip
                        </a>

                        <a href="{{ route('exportarCase') }}"
                            class="bg-indigo-500 hover:bg-indigo-700 text-white font-bold py-2 px-4 rounded">
                            Exportar Case
                        </a>
                        <button id="bt_new_nodo" class="bg-blue-500 hover:bg-blue-700 text-white font-bold py-2 px-4 rounded">
                        Agregar Artefacto
                    </button>
                    </div>

                    <div class="flex items-center text-gray-500 text-sm">
                        <input type="text" id="id_diagrama_actual" value="{{ $diagramador->id }}" class="hidden">
                        <input type="text" id="id_user" value="{{ $user->id }}" class="">
                        @forelse ($invitadosArray as $i)
                            {{-- <span>{{ $i['invitado'] }}</span> --}}
                        @empty
                        @endforelse
                        <p>Cantidad de usuarios conectados: <span id="userCount"></span></p>
                    </div>
                </div>

            </div>
    </main>
    <div id="diagramador"
        style="border: 0px solid black; width: 100%; height: 570px; position: relative; -webkit-tap-highlight-color: rgba(255, 255, 255, 0); cursor: auto;">
    </div>
@endsection
