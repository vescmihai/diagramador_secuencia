@extends('layouts.app')

@section('content')
    @vite(['resources/js/diagramador.js'])
    <main>
        <div class="mx-auto max-w-7xl py-6 sm:px-6 lg:px-8">

            <div class="flex justify-between">
                <div class="flex space-x-4">
                    <button id="bt_new_nodo" class="bg-blue-500 hover:bg-blue-700 text-white font-bold py-2 px-4 rounded">
                        Agregar Artefacto
                    </button>


                    <a href="{{ route('exportarCodigoZip') }}"
                        class="bg-green-500 hover:bg-green-700 text-white font-bold py-2 px-4 rounded">
                        Exportar Código Zip
                    </a>

                    <a href="{{ route('exportarCase') }}"
                        class="bg-indigo-500 hover:bg-indigo-700 text-white font-bold py-2 px-4 rounded">
                        Exportar Case
                    </a>
                </div>

                <div class="flex items-center text-gray-500 text-sm">
                    @forelse ($invitadosArray as $i)
                        <span>{{ $i['invitado'] }}</span>
                    @empty
                    @endforelse
                    <span> Conectados: 4</span>
                </div>
            </div>

        </div>
    </main>
    <div id="diagramador"
        style="border: 0px solid black; width: 100%; height: 570px; position: relative; -webkit-tap-highlight-color: rgba(255, 255, 255, 0); cursor: auto;">
    </div>
@endsection
