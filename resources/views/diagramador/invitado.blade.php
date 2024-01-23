@extends('layouts.app')

@section('content')
    <div class="container mx-auto p-4">
        <h1 class="text-2xl font-semibold mb-4">Invita a un Colaborador</h1>
        <div class="bg-white shadow-md rounded mx-auto p-4">
            <form action="{{ route('registrarInvitado') }}" method="POST" class="mb-4">
                @csrf
                <div class="flex justify-center mb-8">
                    <input type="text" name="id_diagrama" id="id_diagrama" value="{{ $diagramador->id }}" hidden>
                    <textarea name="invitado" placeholder="Correos de los invitados separados por comas" required
                        class="border border-gray-300 px-4 py-2 rounded-l-md w-64"></textarea>
                    <button class="bg-green-500 text-white px-4 py-2 rounded-r-md">Invitar</button>
                    <a href="{{ route('diagramador.index') }}"
                        class="bg-red-500 text-white px-4 py-5 ml-4 rounded-md">Cancelar</a>
                </div>
            </form>

            <dir class="flex justify-center w-full">
                <div class="max-w-lg  bg-white p-8 rounded-md shadow-md">
                    <h2 class="text-xl font-semibold mb-4">Lista de Invitados</h2>

                    <table class="min-w-full bg-white border border-gray-300">
                        <thead>
                            <tr>
                                <th class="py-2 px-4 border-b">#</th>
                                <th class="py-2 px-4 border-b">Invitado</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach ($invitados as $invitado)
                                <tr>
                                    <td class="py-2 px-4 border-b text-center">{{ $invitado->id }}</td>
                                    <td class="py-2 px-4 border-b text-center">{{ $invitado->invitado }}</td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </dir>
        </div>
    </div>
@endsection
