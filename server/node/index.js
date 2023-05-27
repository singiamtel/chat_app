const ws = require('ws');
const http = require('http');

const server = http.createServer();
const wss = new ws.Server({ server });

const messages = [];

const id = () => '_' + Math.random().toString(36).substr(2, 9);

wss.on('connection', (ws) => {
	console.log('Client connected');
	// save the client id
	ws.id = id();
	ws.on('message', (message) => {
		console.log('Message received: ' + message);
		messages.push(message);
		// Broadcast any received message to all clients
		wss.clients.forEach((client) => {
			// Dont send the message back to the client that sent it
			// if (client !== ws) return;
			// client.send(message.toString());
			client.send(`${ws.id}: ${message.toString()}`);
		})
	});
});


server.listen(8080, () => {
	console.log('Listening on http://localhost:8080');
});
