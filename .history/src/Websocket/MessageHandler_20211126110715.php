<?php
namespace App\Websocket;

use Exception;
use Ratchet\ConnectionInterface;
use Ratchet\MessageComponentInterface;
use SplObjectStorage;
use App\Entity\Commande;
use App\Repository\CommandeRepository;

class MessageHandler implements MessageComponentInterface
{
    protected $repo = null; 

    protected $connections;

    public function __construct(CommandeRepository $registry)
    {
        $this->connections = new SplObjectStorage;
        $this->repo = $registry;
    }

    public function onOpen(ConnectionInterface $conn)
    {
        $this->connections->attach($conn);
    }

    public function onMessage(ConnectionInterface $from, $msg)
    {
        foreach($this->connections as $connection)
        {
            if($connection === $from)
            {
                continue;
            }
            $state = true;
            $state = $this->postData($msg);
            // $com = new Commande();
            // $com->name = $request->get('name');
            if($state == true){
                $connection->send($msg);
            }
        }
    }

    public function onClose(ConnectionInterface $conn)
    {
        $this->connections->detach($conn);
    }

    public function onError(ConnectionInterface $conn, Exception $e)
    {
        $this->connections->detach($conn);
        $conn->close();
    }

    public function postData($data){
        $postdata = http_build_query(json_decode($data));
        $opts = array('http' =>
            array(
                'method' => 'POST',
                'header' => 'Content-type: application/json',
                'content' => $postdata
            )
        );
        $context = stream_context_create($opts);
        $result = file_get_contents('http://localhost:8000/api/commandes', false, $context);
        //print()
        return true;
    }
}