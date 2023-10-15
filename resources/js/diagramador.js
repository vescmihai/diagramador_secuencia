
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
var linkDataArray = [];

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
        // // console.log(button);
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
                    console.log('le di click al titulo grupo: ', node.data.key);
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
                // $(go.Picture,
                //     { maxSize: new go.Size(50, 50) },
                //     new go.Binding("source", "img")),
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
                    editable: true,
                },
                new go.Binding("text", "text").makeTwoWay()),
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
        max = Math.max(max, act.start);
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

// Función para calcular la altura de la línea de vida
function computeLifelineHeight(duration) {
    return LinePrefix + duration * MessageSpacing + LineSuffix;
    // return LinePrefix + duration * MessageSpacing + LineSuffix + 100;
}

// Función para calcular la ubicación de una actividad
function computeActivityLocation(act) {
    console.log('computeActivityLocation', act);
    const groupdata = miDiagrama.model.findNodeDataForKey(act.group);
    if (groupdata === null) return new go.Point();
    // Obtener la ubicación del punto de inicio de la línea de vida
    const grouploc = go.Point.parse(groupdata.loc);
    return new go.Point(grouploc.x, convertTimeToY(act.start) - ActivityStart);
}

// Función para calcular la ubicación de una actividad cuando se revierte el proceso
function backComputeActivityLocation(loc, act) {
    let data = {
        "loc": loc,
        "act": act,
        "ActivityStart": ActivityStart,
    }
    // socket.emit('ActivityLocation', (data));
    miDiagrama.model.setDataProperty(act, "start", convertYToTime(loc.y + ActivityStart));
}

// Función para calcular la altura de una actividad
function computeActivityHeight(duration) {
    return ActivityStart + duration * MessageSpacing + ActivityEnd;
}

// Función para calcular la altura de una actividad cuando se revierte el proceso
function backComputeActivityHeight(height) {
    return (height - ActivityStart - ActivityEnd) / MessageSpacing;
}

// El tiempo es solo un entero pequeño no negativo
// Aquí mapeamos entre un tiempo abstracto y una posición vertical
function convertTimeToY(t) {
    return t * MessageSpacing + LinePrefix;
}

// Función para convertir una posición vertical en tiempo cuando se revierte el proceso
function convertYToTime(y) {
    return (y - LinePrefix) / MessageSpacing;
}




// Clase para personalizar los enlaces entre nodos
class MessageLink extends go.Link {
    constructor() {
        console.log('Acabo de ejecutar el constructor de MessageLink()');
        super(); // Llama al constructor de la clase padre go.Link
        this.time = 0;  // Usa este valor de "tiempo" cuando este sea el enlace temporal
    }

    // Método para obtener las coordenadas del punto de conexión del enlace
    getLinkPoint(node, port, spot, from, ortho, othernode, otherport) {
        const p = port.getDocumentPoint(go.Spot.Center); // Obtiene la posición del punto central del puerto
        const r = port.getDocumentBounds();
        const op = otherport.getDocumentPoint(go.Spot.Center); // Obtiene la posición del punto central del puerto del otro nodo

        const data = this.data;
        const time = data !== null ? data.time : this.time;  // Si no está enlazado, asume que tiene su propia propiedad "time"

        const aw = this.getAnchoActividad(node, time); // Llama a una función para obtener el ancho de la actividad
        const x = (op.x > p.x ? p.x + aw / 2 : p.x - aw / 2);
        const y = convertTimeToY(time); // Convierte el tiempo en coordenadas Y
        return new go.Point(x, y); // Devuelve las coordenadas del punto de conexión.
    }

    // Método para calcular el ancho de la actividad
    getAnchoActividad(node, time) {
        let aw = ActivityWidth; // Define el ancho de la actividad
        if (node instanceof go.Group) { // Comprueba si el nodo es una instancia de la clase go.Group
            if (!node.memberParts.any(mem => {
                const act = mem.data;
                return (act !== null && act.start <= time && time <= act.start + act.duration);
            })) {
                aw = 0; // Si no cumple con ciertas condiciones, el ancho de la actividad se establece en 0
            }
        }
        return aw;
    }

    // Método para determinar la dirección de la flecha del enlace
    getLinkDirection(node, port, linkpoint, spot, from, ortho, othernode, otherport) {
        const p = port.getDocumentPoint(go.Spot.Center); // Obtiene la posición del punto central del puerto
        const op = otherport.getDocumentPoint(go.Spot.Center); // Obtiene la posición del punto central del puerto del otro nodo
        const right = op.x > p.x; // Comprueba si el enlace va hacia la derecha
        return right ? 0 : 180; // Devuelve 0 si va hacia la derecha, o 180 si va hacia la izquierda
    }

    // Método para calcular los puntos del enlace
    computePoints() {
        if (this.fromNode === this.toNode) {  // Maneja un enlace reflexivo como un bucle ortogonal simple
            const data = this.data;
            const time = data !== null ? data.time : this.time;  // Si no está enlazado, asume que tiene su propia propiedad "time"
            const p = this.fromNode.port.getDocumentPoint(go.Spot.Center);
            const aw = this.getAnchoActividad(this.fromNode, time);

            const x = p.x + aw / 2;
            const y = convertTimeToY(time); // Convierte el tiempo en coordenadas Y
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
// Fin de MessageLink



// Clase para crear enlaces entre nodos
class MessagingTool extends go.LinkingTool { // LinkingTool = crear enlaces entre nodos

    constructor() {
        super();  // Llama al constructor de la clase padre (go.LinkingTool)
        console.log('Acabo de ejecutar el constructor de MessagingTool()');
        const $ = go.GraphObject.make; // Hace un enlace temporal

        // Crea una variable temporalLink que define cómo se verá el enlace antes de crearse
        this.temporaryLink =
            $(MessageLink, // Utiliza la clase MessageLink para el enlace temporal
                $(go.Shape, "Rectangle",
                    { stroke: "black", strokeWidth: 2 }), // Define la forma del enlace
                $(go.Shape,
                    { toArrow: "Triangle", stroke: "black" })); // Define la flecha al final del enlace
    }

    // Se activa cuando se comienza a crear un enlace
    doActivate() {
        super.doActivate();
        const time = convertYToTime(this.diagram.firstInput.documentPoint.y);
        this.temporaryLink.time = Math.ceil(time);  // Redondea hacia arriba a un valor entero
    }


    insertLink(fromnode, fromport, tonode, toport) {
        const mensaje = prompt("Para eliminar el registro, ingresa 'confirmar':");
        if (mensaje === null || mensaje.trim() === "") {
            mensaje = "get()"; // Asigna el valor predeterminado
        }

        // msgLink contiene el valor ingresado o el valor predeterminado
        // console.log("Mensaje ingresado:", msgLink);


        // document.getElementById('myModallink').showModal();

        // bt_save_link.addEventListener('click', () => {
        //     var mensajeInput = document.getElementById('mensaje');
        //     var metodoInput = document.getElementById('metodo');
        //     // var modal = document.getElementById('myModallink');

        //     // Obtén el valor del input
        //     var mensaje = mensajeInput.value;
        //     var metodo = metodoInput.value;

        //     document.getElementById('myModallink').close();


        //continua con la insersion del link
        const newlink = super.insertLink(fromnode, fromport, tonode, toport);
        // console.log('Julico link: ', fromnode);
        if (newlink !== null) {
            const model = this.diagram.model;
            // console.log('Julico model: ', model);
            // Especifica el tiempo del mensaje
            const start = this.temporaryLink.time;
            const duration = 1;
            newlink.data.time = start;
            model.setDataProperty(newlink.data, "text", mensaje);

            // Crea un nuevo nodo de actividad en los datos del grupo "to"
            const newact = {
                group: newlink.data.to,
                start: start,
                duration: duration
            };
            model.addNodeData(newact);

            // Asegura que todas las líneas de vida tengan suficiente altura
            ensureLifelineHeights();

            console.log('julico duratio ', model.Tc[0].duration);
            // Crea un objeto "puto" con información del nuevo enlace y actividad
            let puto = {
                newlink: newlink.data,
                newact: newact,
                duracion: model.Tc[0].duration,
                metodo: metodo,
            };
            guardarLink(puto); // Llama a una función "guardarLink" y emite un evento "addlink" a través de un socket.
            socket.emit('addlink', (puto));
        }
        // mensajeInput.value = "";
        // metodoInput.value = "";
        return newlink;
        // });
    }
}  // Fin de MessagingTool



function guardarLink(linker) {
    let token = document.querySelector('meta[name="csrf-token"]').getAttribute("content");

    let formulario = new FormData();
    formulario.append("from", linker.newlink.from);
    formulario.append("to", linker.newlink.to);
    formulario.append("text", linker.newlink.text);
    formulario.append("time", linker.newlink.time);

    formulario.append("group", linker.newact.group);
    formulario.append("start", linker.newact.start);
    formulario.append("duration", linker.newact.duration);
    formulario.append("id_diagrama", id_diagrama_actual);
    formulario.append("duracion", linker.duracion);

    fetch('/linkStore', {
        headers: {
            "X-CSRF-TOKEN": token,
        },
        method: 'POST',
        body: formulario

    }).then((linker) => linker.json())
        .then((linker) => {
            console.log(linker);
        });
}


class MessageDraggingTool extends go.DraggingTool {
    // Anula el comportamiento estándar para incluir todos los enlaces seleccionados,
    // incluso si no están conectados a ningún nodo seleccionado.
    computeEffectiveCollection(parts, options) {
        const result = super.computeEffectiveCollection(parts, options);
        // Agrega un Nodo ficticio para que el usuario pueda seleccionar solo los enlaces y moverlos todos.
        result.add(new go.Node(), new go.DraggingInfo(new go.Point()));
        // Normalmente, este método elimina cualquier enlace no conectado a nodos seleccionados;
        // debemos agregarlos de nuevo para que estén incluidos en el argumento "parts" en moveParts.
        parts.each(part => {
            if (part instanceof go.Link) {
                result.add(part, new go.DraggingInfo(part.getPoint(0).copy()));
            }
        });
        return result;
    }

    // Anula para permitir el arrastre cuando la selección incluye solo enlaces.
    mayMove() {
        // let movimiento = !this.diagram.isReadOnly && this.diagram.allowMove;
        return  !this.diagram.isReadOnly && this.diagram.allowMove;
    }

    // Anula para mover enlaces (asumidos como MessageLinks) actualizando su propiedad Link.data.time,
    // de modo que las rutas de los enlaces tengan la posición vertical correcta.
    moveParts(parts, offset, check) {
        console.log('Julico parts: ', parts);
        super.moveParts(parts, offset, check);
        const it = parts.iterator;
        while (it.next()) {
            if (it.key instanceof go.Link) {
                const link = it.key;
                const startY = it.value.point.y;  // La coordenada Y de DraggingInfo.point
                let y = startY + offset.y;  // Determina el nuevo valor de coordenada Y para este enlace
                const cellY = this.gridSnapCellSize.height;
                y = Math.round(y / cellY) * cellY;  // Ajusta a un múltiplo de gridSnapCellSize.height
                const t = Math.max(0, convertYToTime(y));
                link.diagram.model.set(link, "time", data);
                link.invalidateRoute();
            }
        }
    }
}
// Fin de MessageDraggingTool


//recargar datos desde la base de datos
var artefactos = document.querySelectorAll('input[name="artefactos"]');
var artObjetos = JSON.parse(artefactos[0].value);

var enlaces = document.querySelectorAll('input[name="enlaces"]');
var enlacesObjetos = JSON.parse(enlaces[0].value);

var grupos = document.querySelectorAll('input[name="grupos"]');
var gruposObjetos = JSON.parse(grupos[0].value);

var nodeDataArray = [];
var grupo = [];
var enlace = [];
console.log('artefactos: ');
for (const ar of artObjetos) {
    let arrayAuxiliar = {
        "key": ar.key,
        "text": ar.text,
        "isGroup": ar.isGroup,
        "loc": ar.loc,
        "duration": ar.duration
    }
    nodeDataArray.push(arrayAuxiliar);
}

for (const gr of gruposObjetos) {
    let arrayAuxiliar = {
        "group": gr.group,
        "start": gr.start,
        "duration": gr.duration
    }
    nodeDataArray.push(arrayAuxiliar);
}

for (const en of enlacesObjetos) {
    let arrayAuxiliar = {
        "from": en.from,
        "to": en.to,
        "time": en.time,
        "text": en.text
    }
    linkDataArray.push(arrayAuxiliar);
}
console.log('julico ', nodeDataArray);

var datos = {
    "class": "go.GraphLinksModel",
    "nodeDataArray": nodeDataArray,
    "linkDataArray": linkDataArray,
}

bt_save_object.addEventListener('click', function () {
    console.warn('le di click en adicionar');
    let key = document.getElementById('key').value;
    // let text = document.getElementById('text').value;
    console.log('key: ', key);
    // console.log('text: ', text);

    console.warn('localizacion1 ', loc);
    if (loc != '0 0') {
        console.warn('localizacion2 ', loc);
        loc = parseInt(loc);
        console.log('loc1: ', loc);
        loc += 100;
        loc = `${loc} 0`;
        console.log('loc2: ', loc);
    }

    var newNodeData = {
        key: key,
        text: key,
        isGroup: true,
        loc: loc,
        duration: 9
    };

    // Agrega el nuevo nodo al modelo de datos del diagrama
    miDiagrama.model.addNodeData(newNodeData);
    socket.emit('addArtefacto', newNodeData);
    guardarArtefacto(newNodeData);
    miDiagrama.select(miDiagrama.findNodeForKey(key));
    if (loc == '0 0') {
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
    console.log('le di cargar en load ', datos);
    miDiagrama.model = go.Model.fromJson(datos);
}


let modal_controler = document.getElementById('modal_controler');
const abrir_modal_controller = (key, loc) => {
    console.log('abrir_modal_controller');
    console.log('la key es:', key);
    // let datosxd = datos;
    var ultimaKey = key;
    var ultimaLoc = loc;
}

// socket cliente escucha eventos
socket.on('addArtefactoCliente', function (artefacto) {
    miDiagrama.startTransaction("addArtefactoCliente");
    miDiagrama.model.addNodeData(artefacto);
    miDiagrama.commitTransaction("addArtefactoCliente");
});


socket.on('addlinkCliente', function (linker) {
    miDiagrama.startTransaction("addlinkCliente");
    miDiagrama.model.addNodeData(linker.newact);
    miDiagrama.model.addLinkData(linker.newlink);
    ensureLifelineHeights();
    // miDiagrama.model.setDataProperty(linker.gr, "duration", linker.max);
    miDiagrama.commitTransaction("addlinkCliente");
});

socket.on('addDurationCliente', function (gr, max) {
    console.log('addDurationCliente');
    miDiagrama.startTransaction("addDurationCliente");
    miDiagrama.model.setDataProperty(gr, "duration", max);
    miDiagrama.commitTransaction("addDurationCliente");
});


// socket.on('moviemintoCliente', function (data) {
//     miDiagrama.startTransaction("moviemintoCliente");
//     miDiagrama.commitTransaction("moviemintoCliente");
// });



// socket.on('ActivityLocationCliente', function (data) {
//     console.log('ActivityLocationCliente',data.act, data.loc);
//     miDiagrama.startTransaction("ActivityLocationCliente");
//     miDiagrama.model.setDataProperty(data.act, "start", convertYToTime(data.loc.y + data.ActivityStart));
//     // miDiagrama.model.setDataProperty(act, "start", convertYToTime(loc.y + ActivityStart));
//     // computeActivityLocation(data.act);
//     // backComputeActivityLocation(data.loc, data.act);
//     miDiagrama.commitTransaction("ActivityLocationCliente");
// });






// let bt_new_nodo = document.getElementById('bt_new_nodo');
// bt_new_nodo.addEventListener('click', function () {
//     console.log('le di click en bt_new_nodo');
//     // modal_controler.classList.toggle('hidden');
//     //calcular la ubicaoin en pixles


//     //Crea un nuevo objeto de nodo
//     var newNodeData = {
//         group: key_artefacto, // Asigna el grupo al que pertenece el nodo
//         start: 2, // Propiedad personalizada "start"
//         duration: 3 // Propiedad personalizada "duration"
//     };

//     // Agrega el nuevo nodo al modelo de datos del diagrama
//     miDiagrama.model.addNodeData(newNodeData);

//     // Luego, puedes forzar la actualización de la vista del diagrama para mostrar el nuevo nodo
//     miDiagrama.requestUpdate(); // Refresca la vista del diagra
// });
