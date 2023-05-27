<?php

require __DIR__ . '/vendor/autoload.php'; // this one on top
use Ratchet\Http\HttpServer;
use Ratchet\MessageComponentInterface;
use Ratchet\ConnectionInterface;
use Ratchet\Server\IoServer;
use Ratchet\WebSocket\WsServer;

class Chat implements MessageComponentInterface {
    protected $clients;
    protected $messages;

    public function __construct() {
        $this->clients = new \SplObjectStorage();
        $this->messages = [];
    }

    public function onOpen(ConnectionInterface $conn) {
        echo "Client connected\n";
        $this->clients->attach($conn);
        $conn->id = $this->generateId();
    }

    public function onMessage(ConnectionInterface $from, $msg) {
        echo "Message received: $msg\n";
        $this->messages[] = $msg;
        foreach ($this->clients as $client) {
            /* if ($client !== $from) { */
                $client->send($from->id . ": $msg");
            /* } */
        }
    }

    public function onClose(ConnectionInterface $conn) {
        $this->clients->detach($conn);
        echo "Client disconnected\n";
    }

    public function onError(ConnectionInterface $conn, \Exception $e) {
        echo "An error occurred: " . $e->getMessage() . "\n";
        $conn->close();
    }

    private function generateId() {
        return '_' . substr(base_convert(sha1(uniqid(mt_rand())), 16, 36), 0, 9);
    }
}

$server = IoServer::factory(
    new HttpServer(
        new WsServer(
            new Chat()
        )
    ),
    8080
);

$server->run();
