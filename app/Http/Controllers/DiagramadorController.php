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


use App\Mail\CorreosMailable;
use Illuminate\Support\Facades\Mail;

class DiagramadorController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $id = auth()->user()->id;
        $email = auth()->user()->email;
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


        return view('diagramador.index', compact('arrayDiagramas', 'diagramasInvitados', 'email'));
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
        foreach ($enlaces as $e) {
            $contadorMAX += $e->time;
        }



        // dd($invitadosArray);
        return view('diagramador.secuencia', [
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
        // Eliminar el registro de diagramador y sus invitados
        $eliminarInvitados = invitado::where('id_diagrama', $diagramador->id)->delete();
        $diagramador->delete();
        // Redirigir a la vista deseada después de la eliminación
        return redirect()->route('diagramador.index')->with('success', 'Diagrama eliminado correctamente');
    }

    public function invitar(diagramador $diagramador)
    {
        // dd($diagramador);
        $invitados = invitado::where('id_diagrama', $diagramador->id)->get();
        return view('diagramador.invitado', compact('diagramador', 'invitados'));
    }
    public function registrarInvitado(Request $request)
    {
        // dd($request);
        $invitado = new invitado();
        $invitado->invitado = $request->invitado;
        $invitado->id_diagrama = $request->id_diagrama;
        $invitado->save();

        $correo = new CorreosMailable($request->invitado);
        Mail::to($request->invitado)->send($correo);

        return redirect()->route('diagramador.index')->with('success', 'Invitaciones enviadas');
    }



    public function eliminarInvitado(invitado $invitado)
    {
        // dd($invitado);
        $invitado->delete();

        return redirect()->route('diagramador.index');
    }

    public function exportarCodigoZip()
    {
        // $artefactos = artefacto::where('id_diagrama', $diagramador->id)->get();
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

    public function exportarCase(Request $request, diagramador $diagramador)
    {
        //exportacion del diagramador a xml compatible con Enterprice Architech
        // dd($diagramador);
        $artefactos = artefacto::where('id_diagrama', $diagramador->id)->get();
        $enlaces = link::where('id_diagrama', $diagramador->id)->get();
        $grupos = grupo::where('id_diagrama', $diagramador->id)->get();

        $html = '<?xml version="1.0" encoding="windows-1252" standalone="no" ?>
        <XMI xmi.version="1.1" xmlns:UML="omg.org/UML1.3" timestamp="2023-10-15 04:42:08">
            <XMI.header>
                <XMI.documentation>
                    <XMI.exporter>Enterprise Architect</XMI.exporter>
                    <XMI.exporterVersion>2.5</XMI.exporterVersion>
                    <XMI.exporterID>1628</XMI.exporterID>
                </XMI.documentation>
            </XMI.header>
            <XMI.content>
                <UML:Model name="EA Model" xmi.id="MX_EAID_68FDBDA8_B4B6_41a3_90A1_3301F4D1425D">
                    <UML:Namespace.ownedElement>
                        <UML:Class name="EARootClass" xmi.id="EAID_11111111_5487_4080_A7F4_41526CB0AA00" isRoot="true" isLeaf="false" isAbstract="false"/>
                        <UML:Package name="Starter Sequence Diagram" xmi.id="EAPK_68FDBDA8_B4B6_41a3_90A1_3301F4D1425D" isRoot="false" isLeaf="false" isAbstract="false" visibility="public">
                            <UML:ModelElement.taggedValue>
                                <UML:TaggedValue tag="parent" value="EAPK_12F8B5EE_49E0_465a_93D6_ACD3208AE941"/>
                                <UML:TaggedValue tag="ea_package_id" value="3"/>
                                <UML:TaggedValue tag="created" value="2023-10-15 04:41:15"/>
                                <UML:TaggedValue tag="modified" value="2023-10-15 04:41:15"/>
                                <UML:TaggedValue tag="iscontrolled" value="0"/>
                                <UML:TaggedValue tag="lastloaddate" value="2023-10-15 04:41:14"/>
                                <UML:TaggedValue tag="lastsavedate" value="2023-10-15 04:41:14"/>
                                <UML:TaggedValue tag="version" value="1.0"/>
                                <UML:TaggedValue tag="isprotected" value="0"/>
                                <UML:TaggedValue tag="usedtd" value="0"/>
                                <UML:TaggedValue tag="logxml" value="0"/>
                                <UML:TaggedValue tag="tpos" value="1"/>
                                <UML:TaggedValue tag="batchsave" value="0"/>
                                <UML:TaggedValue tag="batchload" value="0"/>
                                <UML:TaggedValue tag="phase" value="1.0"/>
                                <UML:TaggedValue tag="status" value="Proposed"/>
                                <UML:TaggedValue tag="author" value="JSuarez"/>
                                <UML:TaggedValue tag="complexity" value="1"/>
                                <UML:TaggedValue tag="ea_stype" value="Public"/>
                                <UML:TaggedValue tag="tpos" value="1"/>
                                <UML:TaggedValue tag="gentype" value="&lt;none&gt;"/>
                            </UML:ModelElement.taggedValue>
                            <UML:Namespace.ownedElement>';
        $actor = '';
        foreach ($artefactos as $f) {
            if ($f->tipo == 'actor') {
                $actor .= '<UML:Actor name="' . $f->text . '" xmi.id="EAID_60C48589_16F7_4c60_B7B2_862227190FC' . $f->id . '" visibility="public" namespace="EAPK_68FDBDA8_B4B6_41a3_90A1_3301F4D1425D" isRoot="false" isLeaf="false" isAbstract="false">
                                    <UML:ModelElement.taggedValue>
                                        <UML:TaggedValue tag="isSpecification" value="false"/>
                                        <UML:TaggedValue tag="ea_stype" value="Actor"/>
                                        <UML:TaggedValue tag="ea_ntype" value="0"/>
                                        <UML:TaggedValue tag="version" value="1.0"/>
                                        <UML:TaggedValue tag="isActive" value="false"/>
                                        <UML:TaggedValue tag="package" value="EAPK_68FDBDA8_B4B6_41a3_90A1_3301F4D1425D"/>
                                        <UML:TaggedValue tag="date_created" value="2023-10-15 04:41:15"/>
                                        <UML:TaggedValue tag="date_modified" value="2023-10-15 04:41:15"/>
                                        <UML:TaggedValue tag="gentype" value="&lt;none&gt;"/>
                                        <UML:TaggedValue tag="tagged" value="0"/>
                                        <UML:TaggedValue tag="package_name" value="Starter Sequence Diagram"/>
                                        <UML:TaggedValue tag="phase" value="1.0"/>
                                        <UML:TaggedValue tag="author" value="JSuarez"/>
                                        <UML:TaggedValue tag="complexity" value="1"/>
                                        <UML:TaggedValue tag="status" value="Proposed"/>
                                        <UML:TaggedValue tag="tpos" value="0"/>
                                        <UML:TaggedValue tag="ea_localid" value="1"/>
                                        <UML:TaggedValue tag="ea_eleType" value="element"/>
                                        <UML:TaggedValue tag="style" value="BackColor=-1;BorderColor=-1;BorderWidth=-1;FontColor=-1;VSwimLanes=1;HSwimLanes=1;BorderStyle=0;"/>
                                    </UML:ModelElement.taggedValue>
                                </UML:Actor>
                                ';
            }
        }
        $html .= $actor;
        $objetos = '';
        $objetos .= '
                                <UML:Collaboration xmi.id="EAID_68FDBDA8_B4B6_41a3_90A1_3301F4D1425D_Collaboration" name="Collaborations">
                                    <UML:Namespace.ownedElement>';
        $iterador = 3;
        foreach ($artefactos as $f) {
            if ($f->tipo != 'actor') {
                $objetos .= '
                                        <UML:ClassifierRole name="' . $f->text . '" xmi.id="EAID_60C48589_16F7_4c60_B7B2_862227190FC' . $f->id . '" visibility="public" base="EAID_11111111_5487_4080_A7F4_41526CB0AA00">
                                            <UML:ModelElement.taggedValue>
                                                <UML:TaggedValue tag="isAbstract" value="false"/>
                                                <UML:TaggedValue tag="isSpecification" value="false"/>
                                                <UML:TaggedValue tag="ea_stype" value="Sequence"/>
                                                <UML:TaggedValue tag="ea_ntype" value="0"/>
                                                <UML:TaggedValue tag="version" value="1.0"/>
                                                <UML:TaggedValue tag="isActive" value="false"/>
                                                <UML:TaggedValue tag="package" value="EAPK_68FDBDA8_B4B6_41a3_90A1_3301F4D1425D"/>
                                                <UML:TaggedValue tag="date_created" value="2023-10-15 04:41:15"/>
                                                <UML:TaggedValue tag="date_modified" value="2023-10-15 04:41:15"/>
                                                <UML:TaggedValue tag="gentype" value="&lt;none&gt;"/>
                                                <UML:TaggedValue tag="tagged" value="0"/>
                                                <UML:TaggedValue tag="package_name" value="Starter Sequence Diagram"/>
                                                <UML:TaggedValue tag="phase" value="1.0"/>
                                                <UML:TaggedValue tag="author" value="JSuarez"/>
                                                <UML:TaggedValue tag="complexity" value="1"/>
                                                <UML:TaggedValue tag="status" value="Proposed"/>
                                                <UML:TaggedValue tag="tpos" value="0"/>
                                                <UML:TaggedValue tag="ea_localid" value="' . $f->id . '"/>
                                                <UML:TaggedValue tag="ea_eleType" value="element"/>
                                                <UML:TaggedValue tag="style" value="BackColor=-1;BorderColor=-1;BorderWidth=-1;FontColor=-1;VSwimLanes=1;HSwimLanes=1;BorderStyle=0;"/>
                                            </UML:ModelElement.taggedValue>
                                        </UML:ClassifierRole>
                                        ';
                $iterador++;
            }
        }
        $objetos .= '       </UML:Namespace.ownedElement>
                                <UML:Collaboration.interaction>
                                    <UML:Interaction xmi.id="EAID_68FDBDA8_B4B6_41a3_90A1_3301F4D1425D_INT" name="EAID_68FDBDA8_B4B6_41a3_90A1_3301F4D1425D_INT">
                                        <UML:Interaction.message>
                                        ';


        foreach ($enlaces as $e) {
            $from = artefacto::where('key', $e->from)->first();
            $to = artefacto::where('key', $e->to)->first();
            $objetos .= '



                                    <UML:Message name="' . $e->text . '" xmi.id="EAID_BE1BC252_1AB2_4cb3_9BDD_CC0BE0BB9A' . $e->id . '" visibility="public" sender="EAID_60C48589_16F7_4c60_B7B2_862227190FC' . $from->id . '" receiver="EAID_60C48589_16F7_4c60_B7B2_862227190FC' . $to->id . '">
                                        <UML:ModelElement.taggedValue>
                                            <UML:TaggedValue tag="style" value="1"/>
                                            <UML:TaggedValue tag="ea_type" value="Sequence"/>
                                            <UML:TaggedValue tag="direction" value="Source -&gt; Destination"/>
                                            <UML:TaggedValue tag="linemode" value="1"/>
                                            <UML:TaggedValue tag="linecolor" value="-1"/>
                                            <UML:TaggedValue tag="linewidth" value="0"/>
                                            <UML:TaggedValue tag="seqno" value="1"/>
                                            <UML:TaggedValue tag="headStyle" value="0"/>
                                            <UML:TaggedValue tag="lineStyle" value="0"/>
                                            <UML:TaggedValue tag="privatedata1" value="Synchronous"/>
                                            <UML:TaggedValue tag="privatedata2" value="retval=void;"/>
                                            <UML:TaggedValue tag="privatedata3" value="Call"/>
                                            <UML:TaggedValue tag="privatedata4" value="0"/>
                                            <UML:TaggedValue tag="ea_localid" value="376"/>
                                            <UML:TaggedValue tag="ea_sourceName" value="' . $e->from . '"/>
                                            <UML:TaggedValue tag="ea_targetName" value="' . $e->to . '"/>
                                            <UML:TaggedValue tag="ea_sourceType" value="Actor"/>
                                            <UML:TaggedValue tag="ea_targetType" value="Sequence"/>
                                            <UML:TaggedValue tag="ea_sourceID" value="' . $from->id . '"/>
                                            <UML:TaggedValue tag="ea_targetID" value="' . $to->id . '"/>
                                            <UML:TaggedValue tag="src_visibility" value="Public"/>
                                            <UML:TaggedValue tag="src_isOrdered" value="false"/>
                                            <UML:TaggedValue tag="src_targetScope" value="instance"/>
                                            <UML:TaggedValue tag="src_changeable" value="none"/>
                                            <UML:TaggedValue tag="src_isNavigable" value="false"/>
                                            <UML:TaggedValue tag="src_containment" value="Unspecified"/>
                                            <UML:TaggedValue tag="src_style" value="Union=0;Derived=0;AllowDuplicates=0;Owned=0;Navigable=Non-Navigable;"/>
                                            <UML:TaggedValue tag="dst_visibility" value="Public"/>
                                            <UML:TaggedValue tag="dst_aggregation" value="0"/>
                                            <UML:TaggedValue tag="dst_isOrdered" value="false"/>
                                            <UML:TaggedValue tag="dst_targetScope" value="instance"/>
                                            <UML:TaggedValue tag="dst_changeable" value="none"/>
                                            <UML:TaggedValue tag="dst_isNavigable" value="true"/>
                                            <UML:TaggedValue tag="dst_containment" value="Unspecified"/>
                                            <UML:TaggedValue tag="dst_style" value="Union=0;Derived=0;AllowDuplicates=0;Owned=0;Navigable=Navigable;"/>
                                            <UML:TaggedValue tag="privatedata5" value="SX=0;SY=-35;EX=0;EY=0;$LLB=;LLT=;LMT=;LMB=;LRT=;LRB=;IRHS=;ILHS=;"/>
                                            <UML:TaggedValue tag="sequence_points" value="PtStartX=50;PtStartY=-170;PtEndX=299;PtEndY=-170;"/>
                                            <UML:TaggedValue tag="stateflags" value="Activation=0;ExtendActivationUp=0;"/>
                                            <UML:TaggedValue tag="virtualInheritance" value="0"/>
                                            <UML:TaggedValue tag="diagram" value="EAID_153B4DB8_69DA_4d41_887F_57EF64196586"/>
                                            <UML:TaggedValue tag="mt" value="' . $e->text . '"/>
                                        </UML:ModelElement.taggedValue>
                                    </UML:Message>
                                    ';
        }


        $objetos .= '
                                </UML:Interaction.message>
                            </UML:Interaction>
                        </UML:Collaboration.interaction>
                    </UML:Collaboration>
                            </UML:Namespace.ownedElement>
                        </UML:Package>
                    </UML:Namespace.ownedElement>
                </UML:Model>
                <UML:Diagram name="Starter Sequence Diagram" xmi.id="EAID_153B4DB8_69DA_4d41_887F_57EF64196586" diagramType="SequenceDiagram" owner="EAPK_68FDBDA8_B4B6_41a3_90A1_3301F4D1425D" toolName="Enterprise Architect 2.5">
                    <UML:ModelElement.taggedValue>
                        <UML:TaggedValue tag="version" value="1.0"/>
                        <UML:TaggedValue tag="author" value="JSuarez"/>
                        <UML:TaggedValue tag="created_date" value="2023-10-15 04:41:15"/>
                        <UML:TaggedValue tag="modified_date" value="2023-10-15 04:41:15"/>
                        <UML:TaggedValue tag="package" value="EAPK_68FDBDA8_B4B6_41a3_90A1_3301F4D1425D"/>
                        <UML:TaggedValue tag="type" value="Sequence"/>
                        <UML:TaggedValue tag="swimlanes" value="locked=false;orientation=0;width=0;inbar=false;names=false;color=-1;bold=false;fcol=0;tcol=-1;ofCol=-1;ufCol=-1;hl=0;ufh=0;hh=0;cls=0;bw=0;hli=0;bro=0;SwimlaneFont=lfh:-13,lfw:0,lfi:0,lfu:0,lfs:0,lfface:Calibri,lfe:0,lfo:0,lfchar:1,lfop:0,lfcp:0,lfq:0,lfpf=0,lfWidth=0;"/>
                        <UML:TaggedValue tag="matrixitems" value="locked=false;matrixactive=false;swimlanesactive=true;kanbanactive=false;width=1;clrLine=0;"/>
                        <UML:TaggedValue tag="ea_localid" value="2"/>
                        <UML:TaggedValue tag="EAStyle" value="ShowPrivate=1;ShowProtected=1;ShowPublic=1;HideRelationships=0;Locked=0;Border=1;HighlightForeign=1;PackageContents=1;SequenceNotes=0;ScalePrintImage=0;PPgs.cx=1;PPgs.cy=1;DocSize.cx=826;DocSize.cy=1169;ShowDetails=0;Orientation=P;Zoom=100;ShowTags=0;OpParams=1;VisibleAttributeDetail=0;ShowOpRetType=1;ShowIcons=1;CollabNums=0;HideProps=0;ShowReqs=0;ShowCons=0;PaperSize=9;HideParents=0;UseAlias=0;HideAtts=0;HideOps=0;HideStereo=0;HideElemStereo=0;ShowTests=0;ShowMaint=0;ConnectorNotation=UML 2.1;ExplicitNavigability=0;ShowShape=1;AllDockable=0;AdvancedElementProps=1;AdvancedFeatureProps=1;AdvancedConnectorProps=1;m_bElementClassifier=1;SPT=1;ShowNotes=0;SuppressBrackets=0;SuppConnectorLabels=0;PrintPageHeadFoot=0;ShowAsList=0;"/>
                        <UML:TaggedValue tag="styleex" value="SaveTag=F6058435;ExcludeRTF=0;DocAll=0;HideQuals=0;AttPkg=1;ShowTests=0;ShowMaint=0;SuppressFOC=0;INT_ARGS=;INT_RET=;INT_ATT=;SeqTopMargin=50;MatrixActive=0;SwimlanesActive=1;KanbanActive=0;MatrixLineWidth=1;MatrixLineClr=0;MatrixLocked=0;TConnectorNotation=UML 2.1;TExplicitNavigability=0;AdvancedElementProps=1;AdvancedFeatureProps=1;AdvancedConnectorProps=1;m_bElementClassifier=1;SPT=1;MDGDgm=;STBLDgm=;ShowNotes=0;VisibleAttributeDetail=0;ShowOpRetType=1;SuppressBrackets=0;SuppConnectorLabels=0;PrintPageHeadFoot=0;ShowAsList=0;SuppressedCompartments=;Theme=:119;"/>
                    </UML:ModelElement.taggedValue>
                    <UML:Diagram.element>';

        $html .= $objetos;
        $left = 0;
        $resultado = 0;
        $i = 1;
        $pie = '';

        foreach ($artefactos as $a) {
            $resultado = $left + 90;
            $pie .= '
                        <UML:DiagramElement geometry="Left=' . $left . ';Top=50;Right=' . $resultado . ';Bottom=276;" subject="EAID_60C48589_16F7_4c60_B7B2_862227190FC' . $a->id . '" seqno="' . $i . '" style="DUID=12345JC' . $a->id . ';"/>
                        ';
            $left += 100;
            $i++;
        }
        foreach ($enlaces as $e) {

            $pie .= '
                        <UML:DiagramElement geometry="SX=0;SY=0;EX=0;EY=0;Path=;" subject="EAID_BE1BC252_1AB2_4cb3_9BDD_CC0BE0BB9A' . $e->id . '" style=";Hidden=0;"/>
                        ';
        }
        $pie .= '
                    </UML:Diagram.element>
                </UML:Diagram>
            </XMI.content>
            <XMI.difference/>
            <XMI.extensions xmi.extender="Enterprise Architect 2.5"/>
        </XMI>';

        $html .= $pie;


        $fecha = date('Y-m-d');

        $headers = [
            'Content-type'        => 'text/html; charset=UTF-8',
            'Content-Disposition' => 'attachment; filename="pizarra-' . $fecha . '.xml"',
        ];

        return new Response($html, 200, $headers);
    }

    public function codeJava(diagramador $diagramador)
    {
        $artefactos = artefacto::where('id_diagrama', $diagramador->id)->get();
        $enlaces = link::where('id_diagrama', $diagramador->id)->get();
        $grupos = grupo::where('id_diagrama', $diagramador->id)->get();

        $parte1 = '';
        $parte2 = '';

        $parte1 .= '@Controller
        public class ' . preg_replace('/[\s(){}\[\]-]/', '', $diagramador->titulo) . ' {
        ';
        foreach ($enlaces as $f) {
            $parte2 .= '
        @RequestMapping("/' . preg_replace('/[\s(){}\[\]-]/', '', $f->text) . '")
        public String ' . preg_replace('/[\s(){}\[\]-]/', '', $f->text) . '() {
        // Lógica para mostrar una página de inicio
        return "' . preg_replace('/[\s(){}\[\]-]/', '', $f->text) . '";
        }
        ';
        }
        $parte2 .= '
        }
        ';

        $parte1 .= $parte2;

        $fecha = date('Y-m-d');

        $headers = [
            'Content-type'        => 'text/html; charset=UTF-8',
            'Content-Disposition' => 'attachment; filename="codigo-java-' . $fecha . '.java"',
        ];

        return new Response($parte1, 200, $headers);
    }

    public function codePy(diagramador $diagramador)
    {
        $enlaces = link::where('id_diagrama', $diagramador->id)->get();

        $parte1 = '';
        $parte2 = '';

        $parte1 .= 'from django.shortcuts import render, redirect, get_object_or_404
        from .' . $diagramador->titulo . ' import Recurso
        ';
        foreach ($enlaces as $f) {
            $parte2 .= '
        def ' . preg_replace('/[\s(){}\[\]-]/', '', $f->text) . '(request):
        # Lógica para mostrar una vista principal
        return render(request,"' . preg_replace('/[\s(){}\[\]-]/', '', $f->text) . '")
        ';
        }

        $parte1 .= $parte2;

        $fecha = date('Y-m-d');

        $headers = [
            'Content-type'        => 'text/html; charset=UTF-8',
            'Content-Disposition' => 'attachment; filename="CodePy-' . $fecha . '.py"',
        ];

        return new Response($parte1, 200, $headers);
    }

    public function codePhp(diagramador $diagramador)
    {
        $artefactos = artefacto::where('id_diagrama', $diagramador->id)->get();
        $enlaces = link::where('id_diagrama', $diagramador->id)->get();
        $grupos = grupo::where('id_diagrama', $diagramador->id)->get();

        $parte1 = '';
        $parte2 = '';

        $parte1 .= '<?php

        namespace App\Http\Controllers;

        use App\Models\ ' . $diagramador->titulo . ';
        use Illuminate\Http\Request;

        class ' . $diagramador->titulo . ' extends Controller
        {
        ';
        foreach ($enlaces as $f) {
            $parte2 .= '
        public function ' . preg_replace('/[\s(){}\[\]-]/', '', $f->text) . '()
        {
            // Lógica para mostrar una vista principal
        }
        ';
        }
        $parte2 .= '
        }
        ';

        $parte1 .= $parte2;

        $fecha = date('Y-m-d');

        $headers = [
            'Content-type'        => 'text/html; charset=UTF-8',
            'Content-Disposition' => 'attachment; filename="CodePhp-' . $fecha . '.php"',
        ];

        return new Response($parte1, 200, $headers);
    }

    public function eliminarArtefacto(){
        
    }
}
