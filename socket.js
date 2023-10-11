import express from 'express';
import { Server } from 'socket.io';
import http from 'http';

const app = express();

const server = http.createServer(app);
const io = new Server(server);

io.on('connection', client => {
    console.log('conectau ' + client.id);



    client.on('disconnect', () => {
        console.log('desconectau');
    });
});


server.listen(3000, (err) => {
    if (err) throw new Error(err);
    console.log('listening on *:3000');
});
