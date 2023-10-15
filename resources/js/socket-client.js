import { io } from 'socket.io-client';
// import go from 'gojs';

export const socket = io('http://localhost:3000', {
    transports: ['websocket'],
});


socket.on('connect', function () {
    console.log('conectado con el servidor');
    socket.emit('getGuestCount');
});

socket.on('guestCount', (count) => {
    console.log('Número de invitados en la sala:', count);
    //pasar count a la vista
    // document.getElementById('guestCount').innerText = count;
});


const user = document.getElementById('id_user').value;
socket.emit('saludo', user);

// recibo el saludo del servidor y lo muestro en consola
socket.on('saludo_respuesta', (respuesta) => {
    console.log(respuesta);
});
