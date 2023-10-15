<?php

namespace App\Http\Controllers;

use App\Models\artefacto;
use ZipArchive;
use App\Models\User;

use App\Models\invitado;
use App\Models\diagramador;
use App\Models\grupo;
use App\Models\link;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Barryvdh\DomPDF\Facade\Pdf;

class DiagramadorController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $id = auth()->user()->id;
        $diagramas = diagramador::where('autor', $id)->get();
        $arrayDiagramas = [];
        $invitados = [];
        foreach ($diagramas as $d) {
            // dd($d);
            $invitado = invitado::where('id_diagrama', $d->id)->get();
            foreach ($invitado as $i) {
                $invitados[] = $i->invitado;
            }
            // dd($invitados);
            $arrayDiagramas[] = [
                'invitados' => $invitados,
                'titulo' => $d->titulo,
                'autor' => $d->autor,
                'autornombre' => $d->autornombre,
                'id_diagrama' => $d->id,
            ];
            $invitados = [];
        }
        // dd($invitadosArray);
        //consulta para la tabla de INVITACIONES
        $user_email = auth()->user()->email;
        $invitaciones = invitado::where('invitado', $user_email)->get();
        $diagramasInvitados = [];

        if ($invitaciones) {
            foreach ($invitaciones as $inv) {
                $di = diagramador::where('id', $inv->id_diagrama)->first();

                $diagramasInvitados[] = $di;
            }
        } else {
            $diagramasInvitados = null;
        }
        // dd($diagramasInvitados);


        return view('diagramador.index', compact('arrayDiagramas', 'diagramasInvitados'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        return view('diagramador.create');
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        // dd($request);
        $autornombre = auth()->user()->name;
        $autor = auth()->user()->id;
        // dd($autor);
        $diagrama = new diagramador();
        $diagrama->titulo = $request->titulo;
        $diagrama->autornombre = $autornombre;
        $diagrama->autor = $autor;
        $diagrama->save();

        return redirect()->route('diagramador.index')->with('success', 'Diagrama creado correctamente');
    }

    // Esta funcion la uso para directo  hasta el diagramador
    // Este es el boton trabajar en el index de diagramador
    public function show(diagramador $diagramador)
    {
        // dd($diagramador);
        $id = auth()->user()->id;
        $invitados = invitado::where('id_diagrama', $diagramador->id)->get();
        $user = User::where('id', $id)->first();
        // dd($invitados);
        $invitadosArray = [];
        foreach ($invitados as $i) {
            if ($i->invitado == auth()->user()->email) {
                $invitadosArray[] = [
                    'invitado' => $i->invitado,
                    'id_diagrama' => $i->id_diagrama,
                ];
            }
        }

        $artefactos = artefacto::where('id_diagrama', $diagramador->id)->get();
        $enlaces = link::where('id_diagrama', $diagramador->id)->get();
        $grupos = grupo::where('id_diagrama', $diagramador->id)->get();


        $contadorGR = 0;
        foreach ($grupos as $g) {
            $contadorGR += $g->duration;
        }

        $contadorMAX = 0;
        foreach($enlaces as $e){
                $contadorMAX += $e->time;
        }



        // dd($invitadosArray);
        return view('diagramador.secuencia',[
            'diagramador' => $diagramador,
            'invitadosArray' => $invitadosArray,
            'user' => $user,
            'artefactos' => $artefactos,
            'enlaces' => $enlaces,
            'grupos' => $grupos,
            'gr' => $contadorGR,
            'max' => $contadorMAX,
        ]);
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(diagramador $diagramador)
    {
        // dd($diagramador);
        return view('diagramador.edit', compact('diagramador'));
    }
    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, diagramador $diagramador)
    {
        $diagramador->titulo = $request->input('titulo');
        $diagramador->save();

        // Redirigir a la vista deseada después de la actualización
        return redirect()->route('diagramador.index')->with('success', 'Título actualizado correctamente');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(diagramador $diagramador)
    {
        // Eliminar el registro de diagramador
        $diagramador->delete();

        // Redirigir a la vista deseada después de la eliminación
        return redirect()->route('diagramador.index')->with('success', 'Diagrama eliminado correctamente');
    }

    public function invitar(diagramador $diagramador)
    {
        // dd($diagramador);
        return view('diagramador.invitado', compact('diagramador'));
    }
    public function registrarInvitado(Request $request)
    {
        // dd($request);
        $invitado = new invitado();
        $invitado->invitado = $request->invitado;
        $invitado->id_diagrama = $request->id_diagrama;
        $invitado->save();

        return redirect()->route('diagramador.index');
    }

    public function eliminarInvitado(invitado $invitado)
    {
        // dd($invitado);
        $invitado->delete();

        return redirect()->route('diagramador.index');
    }

    public function exportarCodigoZip()
    {
        $artefactos = artefacto::where('id_diagrama', $diagramador->id)->get();
        //Exportar el diagramador a un archivo comprimido, con el codigo fuente
        // Obtener el contenido de las vistas como texto
        $java = view('Exportar.java');
        $php = view('Exportar.php');
        $python = view('Exportar.python');

        file_put_contents('code-java.java', $java);
        file_put_contents('code-php.php', $php);
        file_put_contents('code-python.py', $python);

        $zip = new ZipArchive;
        $nombre_zip = 'export-java-php-python.zip';

        if ($zip->open($nombre_zip, ZipArchive::CREATE) === TRUE) {
            $zip->addFile('code-java.java', 'code-java.java');
            $zip->addFile('code-php.php', 'code-php.php');
            $zip->addFile('code-python.py', 'code-python.py');
            $zip->close();
        }

        if (file_exists($nombre_zip)) {
            header('Content-Type: application/zip');
            header('Content-Disposition: attachment; filename="' . $nombre_zip . '"');
            header('Content-Length: ' . filesize($nombre_zip));
            readfile($nombre_zip);
            unlink($nombre_zip); // Elimina el archivo ZIP después de descargarlo
        }
    }

    public function exportarCase()
    {
        //exportacion del diagramador a xml compatible con Enterprice Architech
        // $view = view('Exportar.architec', ['array_clase' => $array_clase, 'di' => $di]);
        $view = view('Exportar.architec');

        $fecha = date('Y-m-d');
        $htmlContent = $view->render();

        $headers = [
            'Content-type'        => 'text/html; charset=UTF-8',
            'Content-Disposition' => 'attachment; filename="Mydiagram-' . $fecha . '.xml"',
        ];

        return new Response($htmlContent, 200, $headers);
    }
}
