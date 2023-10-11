import { io } from 'socket.io-client';
// import go from 'gojs';

export const socket = io('http://localhost:3000', {
    transports: ['websocket'],
});


socket.on('connect', function () {
    console.log('conectado con el servidor');
});


const user = "Julico Suarez";
socket.emit('saludo', user);

// recibo el saludo del servidor y lo muestro en consola
socket.on('saludo_respuesta', (respuesta) => {
    console.log(respuesta);
});


// recibo el saludo del servidor y lo muestro en consola
socket.on('saludo_respuesta', (respuesta) => {
    console.log(respuesta);
});
