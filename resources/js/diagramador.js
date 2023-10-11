import go from 'gojs';
import { socket } from './socket-client';

var id_diagrama_actual = document.getElementById('id_diagrama_actual').value;
// var id_user = document.getElementById('id_user').value;




window.addEventListener('DOMContentLoaded', init);

const LinePrefix = 20;  // vertical starting point in document for all Messages and Activations
const LineSuffix = 30;  // vertical length beyond the last message time
const MessageSpacing = 30;  // vertical distance between Messages at different steps
const ActivityWidth = 10;  // ancho de cada barra de actividad vertical
const ActivityStart = 5;  // height before start message time
const ActivityEnd = 5;  // height beyond end message time

var key_artefacto;
var loc = '0 0';

var miDiagrama;

function init() {
    console.log('acabo de entrar a init()');

    const $GG = go.GraphObject.make; //

    //creamos el diagrama
    miDiagrama = new go.Diagram(
        "diagramador", // id del div
        {
            allowCopy: false,// creando una nueva instancia
            linkingTool: $GG(MessagingTool),  // defined below
            "resizingTool.isGridSnapEnabled": true,
            draggingTool: $GG(MessageDraggingTool),  // defined below
            "draggingTool.gridSnapCellSize": new go.Size(1, MessageSpacing / 4),
            "draggingTool.isGridSnapEnabled": true,
            // automatically extend Lifelines as Activities are moved or resized
            "SelectionMoved": ensureLifelineHeights,
            "PartResized": ensureLifelineHeights,
            // "undoManager.isEnabled": true
        },
    );

    // metodo que permite desactivar el boton de guardar cuando no hay cambios, y que no le de guardar varias veces
    miDiagrama.addDiagramListener("Modified", e => {
        // console.warn('acabo de ejecutar el addDiagramListener()');
        const button = document.getElementById("btnGuardar");
        // console.log(button);
        if (button) button.disabled = !miDiagrama.isModified;
        // const idx = document.title.indexOf("*");
        //  console.log();
        // if (miDiagrama.isModified) {
        //   if (idx < 0) document.title += "*";
        // } else {
        //   if (idx >= 0) document.title = document.title.slice(0, idx);
        // }
    });


    // define the Lifeline Node template.
    miDiagrama.groupTemplate =
        $GG(go.Group, "Vertical",
            {
                locationSpot: go.Spot.Bottom,
                locationObjectName: "HEADER",

                minLocation: new go.Point(0, 0),
                maxLocation: new go.Point(9999, 0),
                selectionObjectName: "HEADER",
                click: function (e, node) {
                    // Esta función se ejecutará cuando se haga clic en el nodo
                    console.warn('le di click al titulo grupo: ', node.data.key);
                    // console.warn(node.data);

                    // Abre aquí el modal o realiza las acciones que necesites
                    // console.warn(node.data,);
                    // console.warn(e);
                    key_artefacto = node.data.key;
                    loc = node.data.loc;
                    // console.warn(  key_modal_controller);
                    abrir_modal_controller(key_artefacto, loc);
                }
            },
            new go.Binding("location", "loc", go.Point.parse).makeTwoWay(go.Point.stringify),
            $GG(go.Panel, "Auto",
                {
                    name: "HEADER",

                },
                // $GG(go.Shape, "Circle",
                //     {
                //         fill: $GG(go.Brush, "Linear", { 0: "#bbdefb", 1: go.Brush.darkenBy("#bbdefb", 0.1) }),
                //         stroke: null
                //     }),
                $GG(go.Shape, "Rectangle",
                    {
                        fill: $GG(go.Brush, "Linear",
                            {
                                0: "#faffe3",
                                1: go.Brush.darkenBy("#ffffff", 0.1),
                            }),
                        stroke: "black"
                    }),
                $GG(go.TextBlock,
                    {
                        margin: 20,
                        font: "400 10pt Source Sans Pro, sans-serif"
                    },
                    new go.Binding("text", "text")),

            ),
            $GG(go.Shape,
                {
                    figure: "LineV",
                    fill: null,
                    stroke: "gray",
                    strokeDashArray: [10, 10],
                    width: 5,
                    alignment: go.Spot.Center,
                    portId: "",
                    fromLinkable: true,
                    fromLinkableDuplicates: true,
                    toLinkable: true,
                    toLinkableDuplicates: true,
                    cursor: "pointer"
                },
                new go.Binding("height", "duration", computeLifelineHeight))
        );

    // define the Activity Node template
    miDiagrama.nodeTemplate =
        $GG(go.Node,
            {
                locationSpot: go.Spot.Top, // determina el punto de referencia para la posición del nodo
                locationObjectName: "SHAPE",
                //limites de ubicaiones del nodo
                minLocation: new go.Point(NaN, LinePrefix - ActivityStart),
                maxLocation: new go.Point(NaN, 19999),
                selectionObjectName: "SHAPE",
                resizable: true,
                resizeObjectName: "SHAPE",
                //poner un icono para mover el nodo
                resizeAdornmentTemplate:
                    $GG(go.Adornment, "Spot",
                        $GG(go.Placeholder),
                        $GG(go.Shape,  // only a bottom resize handle
                            {
                                alignment: go.Spot.Bottom, cursor: "col-resize",
                                desiredSize: new go.Size(6, 6), fill: "yellow"
                            })
                    ),
                click: function (e, node) {
                    // Esta función se ejecutará cuando se haga clic en el nodo
                    // Abre aquí el modal o realiza las acciones que necesites
                    // abrirModal();
                    console.log('le di click al nodo');
                    console.log(node.data.group);
                }
            },
            //disenio para los cuadros de actividades
            //computeActivityLocation = calcula la ubicación del nodo
            //backComputeActivityLocation =
            //makeTwoWay metodo de go.Binding
            //mantiene en linea o grupo el cuadrado que se cree
            new go.Binding("location", "", computeActivityLocation).makeTwoWay(backComputeActivityLocation),
            $GG(go.Shape, "Rectangle",
                {
                    name: "SHAPE",
                    fill: "white", stroke: "black",
                    width: ActivityWidth, //ancho del cuadrado
                    // allow Activities to be resized down to 1/4 of a time unit
                    minSize: new go.Size(ActivityWidth, computeActivityHeight(0.25))
                },
                //calculas la alutra
                new go.Binding("height", "duration", computeActivityHeight).makeTwoWay(backComputeActivityHeight)
            )
        );

    // define the Message Link template.
    miDiagrama.linkTemplate =
        $GG(MessageLink,  // defined below
            { selectionAdorned: true, curviness: 0 },
            $GG(go.Shape, "Rectangle",
                { stroke: "black" }),
            $GG(go.Shape,
                { toArrow: "Triangle", stroke: "black" }),
            $GG(go.TextBlock,
                {
                    font: "400 9pt Source Sans Pro, sans-serif",
                    segmentIndex: 0,
                    segmentOffset: new go.Point(NaN, NaN),
                    isMultiline: false,
                    editable: true
                },
                new go.Binding("text", "text").makeTwoWay())
        );

    load(); //cargamos el diagrama
};


function ensureLifelineHeights(e) {
    // iterate over all Activities (ignore Groups)
    const arr = miDiagrama.model.nodeDataArray;
    let max = -1;
    for (let i = 0; i < arr.length; i++) {
        const act = arr[i];
        if (act.isGroup) continue;
        max = Math.max(max, act.start + act.duration);
    }
    if (max > 0) {
        // now iterate over only Groups
        for (let i = 0; i < arr.length; i++) {
            const gr = arr[i];
            if (!gr.isGroup) continue;
            if (max > gr.duration) {  // this only extends, never shrinks
                miDiagrama.model.setDataProperty(gr, "duration", max);
            }
        }
    }
}



function computeLifelineHeight(duration) {
    return LinePrefix + duration * MessageSpacing + LineSuffix;
}

function computeActivityLocation(act) {
    const groupdata = miDiagrama.model.findNodeDataForKey(act.group);
    if (groupdata === null) return new go.Point();
    // get location of Lifeline's starting point
    const grouploc = go.Point.parse(groupdata.loc);
    return new go.Point(grouploc.x, convertTimeToY(act.start) - ActivityStart);
}
function backComputeActivityLocation(loc, act) {
    miDiagrama.model.setDataProperty(act, "start", convertYToTime(loc.y + ActivityStart));
}

function computeActivityHeight(duration) {
    return ActivityStart + duration * MessageSpacing + ActivityEnd;
}
function backComputeActivityHeight(height) {
    return (height - ActivityStart - ActivityEnd) / MessageSpacing;
}

// time is just an abstract small non-negative integer
// here we map between an abstract time and a vertical position
function convertTimeToY(t) {
    return t * MessageSpacing + LinePrefix;
}
function convertYToTime(y) {
    return (y - LinePrefix) / MessageSpacing;
}


//go.Link = personalizar las flechas entre nodos
class MessageLink extends go.Link {
    constructor() {
        console.log('acabo de ejeuctar el contrsutor de MessageLink()');
        super(); //ejecutar el contrsuctor de link
        this.time = 0;  // use este valor de "tiempo" cuando este sea el enlace temporal
    }

    //metodo para obtener las coordenadas del punto de conexión
    getLinkPoint(node, port, spot, from, ortho, othernode, otherport) {
        const p = port.getDocumentPoint(go.Spot.Center);
        const r = port.getDocumentBounds();
        const op = otherport.getDocumentPoint(go.Spot.Center);

        const data = this.data;
        const time = data !== null ? data.time : this.time;  // Si no está enlazada, asuma que esta tiene su propia propiedad "time"

        const aw = this.getAnchoActividad(node, time);
        const x = (op.x > p.x ? p.x + aw / 2 : p.x - aw / 2);
        const y = convertTimeToY(time);
        return new go.Point(x, y); //devuelve las coordenadas del punto de conexión.
    }


    //findActivityWidth - recibe un nodo y un tiempo, y devuelve el ancho de la actividad en ese momento.
    getAnchoActividad(node, time) {
        let aw = ActivityWidth; //ancho de la actividad
        if (node instanceof go.Group) { //node es una instancia de la clase go.Group ?
            // console.warn('node es una instancia de la clase go.Group');
            //vea si hay un Nodo de Actividad en este punto; si no, conecte el enlace directamente con la línea de vida del Grupo

            //si es grupo verificar si al menos un elemento cumple con ciertas condición
            if (!node.memberParts.any(mem => {
                const act = mem.data;
                return (act !== null && act.start <= time && time <= act.start + act.duration);
            })) {
                //si no cumple de retornara 0
                aw = 0;
            }
        }
        return aw;
    }

    //devuelve la direccion de la flecha
    getLinkDirection(node, port, linkpoint, spot, from, ortho, othernode, otherport) {
        const p = port.getDocumentPoint(go.Spot.Center); //posición del punto central del puerto (port) del nodo actua
        const op = otherport.getDocumentPoint(go.Spot.Center); // Se declara una variable op y se le asigna la posición del punto central del puerto (otherport)
        const right = op.x > p.x;
        return right ? 0 : 180;
        //Esta línea devuelve un valor que representa la dirección del enlace. Si right es true, lo que significa que el enlace va hacia la derecha, devuelve 0
    }

    //calcular los puntos de la flecha
    computePoints() {
        if (this.fromNode === this.toNode) {  // also handle a reflexive link as a simple orthogonal loop
            const data = this.data;
            const time = data !== null ? data.time : this.time;  // if not bound, assume this has its own "time" property
            const p = this.fromNode.port.getDocumentPoint(go.Spot.Center);
            const aw = this.getAnchoActividad(this.fromNode, time);

            const x = p.x + aw / 2;
            const y = convertTimeToY(time);
            this.clearPoints();
            this.addPoint(new go.Point(x, y));
            this.addPoint(new go.Point(x + 50, y));
            this.addPoint(new go.Point(x + 50, y + 5));
            this.addPoint(new go.Point(x, y + 5));
            return true;
        } else {
            return super.computePoints();
        }
    }
}
// end MessageLink

//clase para crear enlaces entre nodos
class MessagingTool extends go.LinkingTool { //LinkingTool = crear enleces entre nodos
    constructor() {
        super();  //llama al constructor de la clase padre (go.LinkingTool)
        console.log('acabo de ejecutar el constructor de MessagingTool()');
        const $ = go.GraphObject.make; //enlace temporal

        //crea una variable temporalLink
        this.temporaryLink =
            $(MessageLink,
                $(go.Shape, "Rectangle",
                    { stroke: "black", strokeWidth: 2 }),
                $(go.Shape,
                    { toArrow: "Triangle", stroke: "black" }));
    }

    doActivate() {
        super.doActivate();
        const time = convertYToTime(this.diagram.firstInput.documentPoint.y);
        this.temporaryLink.time = Math.ceil(time);  // round up to an integer value
    }

    insertLink(fromnode, fromport, tonode, toport) {
        const newlink = super.insertLink(fromnode, fromport, tonode, toport);
        console.log('julico link: ', fromnode);
        if (newlink !== null) {
            const model = this.diagram.model;
            console.log('julico model: ', model);
            // specify the time of the message
            const start = this.temporaryLink.time;
            const duration = 1;
            newlink.data.time = start;
            model.setDataProperty(newlink.data, "text", "index()");
            // and create a new Activity node data in the "to" group data
            const newact = {
                group: newlink.data.to,
                start: start,
                duration: duration
            };
            model.addNodeData(newact);
            // now make sure all Lifelines are long enough
            ensureLifelineHeights();
        }
        console.log('xdxd de ejecutar el insertLink()', newlink);
        return newlink;
    }
}  //end MessagingTool


class MessageDraggingTool extends go.DraggingTool {
    // anular el comportamiento estándar para incluir todos los enlaces seleccionados,
    // even if not connected with any selected Nodes
    // constructor() {
    //   console.log('acabo de ejecutarse  MessageDraggingTool()');
    // }

    computeEffectiveCollection(parts, options) {
        const result = super.computeEffectiveCollection(parts, options);
        // add a dummy Node so that the user can select only Links and move them all
        result.add(new go.Node(), new go.DraggingInfo(new go.Point()));
        // normally this method removes any links not connected to selected nodes;
        // we have to add them back so that they are included in the "parts" argument to moveParts
        parts.each(part => {
            if (part instanceof go.Link) {
                result.add(part, new go.DraggingInfo(part.getPoint(0).copy()));
            }
        })
        return result;
    }

    // override to allow dragging when the selection only includes Links
    mayMove() {
        return !this.diagram.isReadOnly && this.diagram.allowMove;
    }

    // override to move Links (which are all assumed to be MessageLinks) by
    // updating their Link.data.time property so that their link routes will
    // have the correct vertical position
    moveParts(parts, offset, check) {
        super.moveParts(parts, offset, check);
        const it = parts.iterator;
        while (it.next()) {
            if (it.key instanceof go.Link) {
                const link = it.key;
                const startY = it.value.point.y;  // DraggingInfo.point.y
                let y = startY + offset.y;  // determine new Y coordinate value for this link
                const cellY = this.gridSnapCellSize.height;
                y = Math.round(y / cellY) * cellY;  // snap to multiple of gridSnapCellSize.height
                const t = Math.max(0, convertYToTime(y));
                link.diagram.model.set(link.data, "time", t);
                link.invalidateRoute();
            }
        }
    }
}
// end MessageDraggingTool

//loc es la ubicación del nodo, (eje x, eje y)
//duration es la duración del nodo, linea de vida
var datos = {
    "class": "go.GraphLinksModel",
    "nodeDataArray": [
        { "key": "1", "text": "Actor: Patron", "isGroup": true, "loc": "0 0", "duration": 9 },
        { "key": "2", "text": "Controller", "isGroup": true, "loc": "150 0", "duration": 9 },
        // { "key": "View", "text": "View", "isGroup": true, "loc": "250 0", "duration": 9 },
        // { "key": "Model", "text": "Model", "isGroup": true, "loc": "350 0", "duration": 9 },
        { "group": "2", "start": 1, "duration": 2 },
        // { "group": "View", "start": 2, "duration": 3 },
        // { "group": "Actor", "start": 3, "duration": 1 },
        // { "group": "Controller", "start": 5, "duration": 1 },
        // { "group": "Actor", "start": 6, "duration": 2 },
        // { "group": "Model", "start": 8, "duration": 1 }
    ],
    "linkDataArray": [
        { "from": "1", "to": "2", "text": "order", "time": 1 },
        // { "from": "Controller", "to": "View", "text": "order food", "time": 2 },
        // { "from": "Controller", "to": "Actor", "text": "serve drinks", "time": 3 },
        // { "from": "View", "to": "Controller", "text": "finish cooking", "time": 5 },
        // { "from": "Controller", "to": "Actor", "text": "serve food", "time": 6 },
        // { "from": "Actor", "to": "Model", "text": "pay", "time": 8 }
    ]
}

bt_save_object.addEventListener('click', function () {
    console.warn('le di click en adicionar');
    let key = document.getElementById('key').value;
    let text = document.getElementById('text').value;
    console.log('key: ', key);
    console.log('text: ', text);

    console.warn('localizacion1 ',loc);
    if(loc != '0 0'){
        console.warn('localizacion2 ',loc);
        loc = parseInt(loc);
        console.log('loc1: ', loc);
        loc += 100;
        loc = `${loc} 0`;
        console.log('loc2: ', loc);
    }

    var newNodeData = {
        key: key,
        text: text,
        isGroup: true,
        loc: loc,
        duration: 9
    };
    // ip_controller.value = '';
    // Agrega el nuevo nodo al modelo de datos del diagrama
    miDiagrama.model.addNodeData(newNodeData);
    guardarArtefacto(newNodeData);
    miDiagrama.select(miDiagrama.findNodeForKey(key));
    if(loc == '0 0'){
        loc = parseInt(loc);
        loc += 150;
        loc = `${loc} 0`;
    }
    document.getElementById('myModal').close();
});

function guardarArtefacto(newNodeData) {
    let token = document.querySelector('meta[name="csrf-token"]').getAttribute("content");
    let tipo = document.getElementById('tipo').value;

    let formulario = new FormData();
    formulario.append("key", newNodeData.key);
    formulario.append("text", newNodeData.text);
    formulario.append("loc", loc);
    formulario.append("tipo", tipo);
    formulario.append("id_diagrama", id_diagrama_actual);

    fetch('/artefactoStore', {
        headers: {
            "X-CSRF-TOKEN": token,
        },
        method: 'POST',
        body: formulario

    }).then((newNodeData) => newNodeData.json())
        .then((newNodeData) => {
            console.log(newNodeData);
        });
}


function save() {
    console.log('le di guardar en save ');
    datos = miDiagrama.model.toJson();
    miDiagrama.isModified = false;
}
function load() {
    console.log('le di cargar en load ');
    miDiagrama.model = go.Model.fromJson(datos);
}

// let btnCargar = document.getElementById('btnCargar');
// btnCargar.addEventListener('click', function () {
//     console.log('le di cargar');
//     load();
// });


// let btnGuardar = document.getElementById('btnGuardar');
// btnGuardar.addEventListener('click', function () {
//     console.log('le di guardar');
//     save();
// });




let modal_controler = document.getElementById('modal_controler');
const abrir_modal_controller = (key,loc) => {
    console.log('abrir_modal_controller');
    console.log('la key es:', key,' ',loc);
    // let datosxd = datos;
    var ultimaKey = key;
    var ultimaLoc = loc;
}


// var saveButton = document.getElementById('saveButton');
// saveButton.onclick = function () {
//     console.log('le di guardar');
// }

let bt_new_nodo = document.getElementById('bt_new_nodo');
bt_new_nodo.addEventListener('click', function () {
    console.log('le di click en bt_new_nodo');
    // modal_controler.classList.toggle('hidden');
    //calcular la ubicaoin en pixles


    //Crea un nuevo objeto de nodo
    var newNodeData = {
        group: key_artefacto, // Asigna el grupo al que pertenece el nodo
        start: 2, // Propiedad personalizada "start"
        duration: 3 // Propiedad personalizada "duration"
    };

    // Agrega el nuevo nodo al modelo de datos del diagrama
    miDiagrama.model.addNodeData(newNodeData);

    // Luego, puedes forzar la actualización de la vista del diagrama para mostrar el nuevo nodo
    miDiagrama.requestUpdate(); // Refresca la vista del diagra
});
