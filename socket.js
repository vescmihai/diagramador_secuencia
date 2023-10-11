import express from 'express';
import { Server } from 'socket.io';
import http from 'http';

const app = express();

const server = http.createServer(app);
const io = new Server(server);

const salaChat = {};

io.on('connection', socket => {
    console.log('conectau ' + socket.id);


    const sala = 'nombre_de_la_sala'; // Reemplaza con el nombre de tu sala
    if (!salaChat[sala]) {
        salaChat[sala] = [];
    }
    salaChat[sala].push(socket);

    // Maneja la desconexión del usuario
    socket.on('disconnect', () => {
        const index = salaChat[sala].indexOf(socket);
        if (index !== -1) {
            salaChat[sala].splice(index, 1);
        }
    });

    socket.on('getGuestCount', () => {
        const guestCount = salaChat[sala].length;
        socket.emit('guestCount', guestCount);
    });

    socket.on('addArtefacto', (artefacto) => {
        console.log('artefacto xdxdxdxd');
        io.emit('addArtefactoCliente', artefacto);
    });

    // socket.on('addActividad', (actividad) => {
    //     console.log('actividad xdxdxdxd');
    //     io.emit('addActividadCliente', actividad);
    // });

    socket.on('addlink', (linker) => {
        console.log('link xdxdxdxd', linker);
        io.emit('addlinkCliente', linker);
    });


    socket.on('saludo', (user) => {
        // console.log(user);
        const respuesta = "Hola user_id: " + user;
        // socket.broadcast.emit('saludo_respuesta', respuesta);
        io.emit('saludo_respuesta', respuesta);
    });


    socket.on('disconnect', () => {
        console.log('desconectau');
    });
});


server.listen(3000, (err) => {
    if (err) throw new Error(err);
    console.log('listening on *:3000');
});
