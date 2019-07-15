<?php

namespace Scoutapm;

use Scoutapm\Events\Request;

class Connector
{
    private $agent;

    private $config;

    private $socket;

    private $connected;

    public function __construct(\Scoutapm\Agent $agent)
    {
        $this->agent = $agent;
        $this->config = $agent->getConfig();

        $this->socket = socket_create(AF_UNIX, SOCK_STREAM, 0);
        $this->connect();
        register_shutdown_function([&$this, 'shutdown']);
    }

    public function connect()
    {
        try {
            $this->connected = socket_connect($this->socket, $this->config->get('socket_path'));
        } catch (\Exception $e) {
            $this->connected = false;
        }
    }

    public function connected()
    {
        return $this->connected;
    }

    /**
     * @param $message needs to be a single jsonable command
     */
    public function sendMessage($message)
    {
        $message = json_encode($message);

        $size = strlen($message);
        socket_send($this->socket, pack('N', $size), 4, 0);
        socket_send($this->socket, $message, $size, 0);

        // Read the response back and drop it. Needed for socket liveness
        $responseLength = socket_read($this->socket, 4);
        socket_read($this->socket, unpack('N', $responseLength)[1]);
    }
    
    public function sendRequest(Request $request) : bool
    {
        $registerMessage = $this->sendMessage([
            'Register' => [
                'app' => $this->config->get('name'),
                'key' => $this->config->get('key'),
                'language' => 'php',
                'api_version' => $this->config->get('api_version'),
            ]
        ]);

        // Send the whole Request as a batch command
        // TODO: Can I Remove the `->jsonSerialize()` call, is it implicit?
        $request = $this->sendMessage([
            'BatchCommand' => [
                'commands' => $request->jsonSerialize(),
            ]
        ]);
        
        return socket_last_error($this->socket) === 0;
    }

    public function shutdown()
    {
        if ($this->connected == true) {
            socket_shutdown($this->socket, 2);
            socket_close($this->socket);
        }
    }
}
