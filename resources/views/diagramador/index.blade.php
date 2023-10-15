@extends('layouts.app') <!-- Asegúrate de que estés utilizando la plantilla de Laravel que deseas -->

@section('content')

    @if (session('success'))
        <div class="animate-bounce fixed top-4 right-1/3 z-50" x-data="{ show: true }" x-show="show" x-init="setTimeout(() => show = false, 5000)">
            <div id="toast-default"
                class="flex items-center w-full max-w-xs p-4 text-gray-500 bg-white rounded-lg shadow dark:text-gray-400 dark:bg-gray-800"
                role="alert">
                <div
                    class="inline-flex items-center justify-center flex-shrink-0 w-8 h-8 text-blue-500 bg-blue-100 rounded-lg dark:bg-blue-800 dark:text-blue-200">
                    <svg class="w-5 h-5" aria-hidden="true" xmlns="http://www.w3.org/2000/svg" fill="currentColor"
                        viewBox="0 0 20 20">
                        <path
                            d="M10 .5a9.5 9.5 0 1 0 9.5 9.5A9.51 9.51 0 0 0 10 .5Zm3.707 8.207-4 4a1 1 0 0 1-1.414 0l-2-2a1 1 0 0 1 1.414-1.414L9 10.586l3.293-3.293a1 1 0 0 1 1.414 1.414Z" />
                    </svg>
                </div>
                <div class="ml-3 text-sm font-normal">{{ session('success') }}</div>
            </div>
        </div>
    @endif

    <div>
        <div class="container mx-auto mt-8">
            <div class="w-full bg-white rounded-lg shadow-md p-4">
                <div class="flex justify-between items-center">
                    <h1 class="text-3xl font-semibold my-4">Diagramas</h1>
                </div>
                <div class="text-right w-full">
                    <form action="{{ route('diagramador.store') }}" method="POST" class="mb-4">
                        @csrf
                        <div class="flex justify-center mb-8">
                            <input type="text" name="titulo" placeholder="Nombre del Diagrama" required
                                class="border border-gray-300 px-4 py-2 rounded-l-md w-64">
                            <button class="bg-green-500 text-white px-4 py-2 rounded-r-md">Crear</button>
                        </div>
                    </form>
                </div>

                <table class="min-w-full border border-collapse border-gray-300">
                    <thead>
                        <tr>
                            <th class="border border-gray-300 px-4 py-2">ID</th>
                            <th class="border border-gray-300 px-4 py-2">Título</th>
                            <th class="border border-gray-300 px-4 py-2">Invitados</th>
                            <th class="border border-gray-300 px-4 py-2">Autor</th>
                            <th class="border border-gray-300 px-4 py-2">Acciones</th>
                        </tr>
                    </thead>
                    <tbody>
                        {{-- @dd($arrayDiagramas) --}}
                        @foreach ($arrayDiagramas as $diagrama)
                            {{-- @dd($diagrama) --}}
                            <tr>
                                <td class="border border-gray-300 px-4 py-2 text-center">{{ $diagrama['id_diagrama'] }}</td>
                                <td class="border border-gray-300 px-4 py-2 text-center">{{ $diagrama['titulo'] }}</td>
                                <td class="border border-gray-300 px-4 py-2 text-center">
                                    @foreach ($diagrama['invitados'] as $i)
                                        <p>
                                            {{ $i }}
                                        </p>
                                    @endforeach
                                </td>
                                <td class="border border-gray-300 px-4 py-2 text-center">{{ $diagrama['autornombre'] }}</td>
                                <td class="border border-gray-300 px-4 py-2 text-center">
                                    <div class="flex items-center justify-center">
                                        <a href="{{ route('diagramador.edit', $diagrama['id_diagrama']) }}"
                                            class="text-blue-500 hover:text-blue-700 mr-2">Editar</a>
                                        <a href="{{ route('invitar', $diagrama['id_diagrama']) }}"
                                            class="text-black hover:text-blue-700 mr-2">Invitar</a>
                                        <a href="{{ route('diagramador.show', $diagrama['id_diagrama']) }}"
                                            class="text-green-500 hover:text-green-700 mr-2">Trabajar</a>
                                        <form action="{{ route('diagramador.destroy', $diagrama['id_diagrama']) }}"
                                            method="POST" class="inline">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" class="text-red-500 hover:text-red-700">Eliminar</button>
                                        </form>
                                    </div>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
    </div>



    @if ($diagramasInvitados != null)
        <div>
            <div class="container mx-auto mt-8">
                <div class="w-full bg-white rounded-lg shadow-md p-4">
                    <h1 class="text-3xl font-semibold my-4">Invitaciones</h1>
                    <table class="min-w-full border border-collapse border-gray-300">
                        <thead>
                            <tr>
                                <th class="border border-gray-300 px-4 py-2">ID</th>
                                <th class="border border-gray-300 px-4 py-2">Título</th>
                                <th class="border border-gray-300 px-4 py-2">Autor</th>
                                <th class="border border-gray-300 px-4 py-2">Acciones</th>
                            </tr>
                        </thead>
                        <tbody>
                            {{-- @dd($arrayDiagramas) --}}
                            @foreach ($diagramasInvitados as $diagrama)
                                {{-- @dd($diagrama) --}}
                                <tr>
                                    <td class="border border-gray-300 px-4 py-2 text-center">{{ $diagrama['id'] }}</td>
                                    <td class="border border-gray-300 px-4 py-2 text-center">{{ $diagrama['titulo'] }}</td>
                                    <td class="border border-gray-300 px-4 py-2 text-center">{{ $diagrama['autornombre'] }}
                                    </td>
                                    <td class="border border-gray-300 px-4 py-2 text-center">
                                        <div class="flex items-center justify-center">
                                            <a href="{{ route('diagramador.show', $diagrama['id']) }}"
                                                class="text-green-500 hover:text-green-700 mr-2">Trabajar</a>
                                            <form action="{{ route('invitadoDelete') }}" method="POST" class="inline">
                                                @csrf
                                                @method('POST')
                                                <input type="text" name="id_diagrama" value="{{ $diagrama['id'] }}" class="hidden">
                                                <input type="text" name="id_invitado" value="{{ $email }}" class="hidden">
                                                <button type="submit"
                                                    class="text-red-500 hover:text-red-700">Abandonar</button>
                                            </form>
                                        </div>
                                    </td>

                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    @endif
@endsection
