<?php

namespace WebSockets\Service; // Namespaces of current service

use WebSockets\Exception; // add an exception class

/**
 * Server for WebSocket protocol connection
 * @package Zend Framework 2
 * @subpackage WebSockets
 * @since PHP >=5.4
 * @version 1.0
 * @author Stanislav WEB | Lugansk <stanisov@gmail.com>
 * @copyright Stanilav WEB
 * @license Zend Framework GUI licene
 * @filesource /module/Websocket/src/Websocket/Service/WebsocketServer.php
 */
class WebsocketServer {
    
    /**
     * $_config Server configuration
     * @access protected
     * @var  array
     */
    protected $_config = null;
    
    /**
     * $_connection Resource ID
     * @access protected
     * @var  resourse
     */
    protected $_connection = null;
    
    /**
     * $_sockets Connections array
     * @access protected
     * @var  array
     */
    protected $_sockets = null;    
 
    /**
     * __construct(array $config) Initializes the settings
     * @param array $config array with the connection config
     * @throws Exception
     */
    public function __construct(array $config) 
    {
        if(empty($config)) throw new Exception\ExceptionStrategy('Required parameters are incorrupted!');
        $this->_config    =   $config;
    }
    
    /**
     * start() Running stream method
     * @access public
     * @return null
     */
    public function start()
    {
        // open TCP / IP stream and hang port specified in the config
        $this->_connection = socket_create(AF_INET, SOCK_STREAM, SOL_TCP);
	
        //bind socket to specified host
        socket_bind($this->_connection, $this->_config['host'], $this->_config['port']);        
        // reuseable port
	
        socket_set_option($this->_connection, SOL_SOCKET, SO_REUSEADDR, 1);
        
        // listen socket Resourse #id
        socket_listen($this->_connection, $this->_config['connections_limit']);
        
        // add Master connection ID
        $this->_sockets[] = $this->_connection;  
        
        $this->__sendConsoleMsg("Server started\nListening on: ".$this->_config['host'].':'.$this->_config['port']);
        $this->__sendConsoleMsg("Primary socket: ".$this->_connection);
        
        sleep(3);

        while(true) // run endless connection. Non Stop!!
        {
            $read = $this->_sockets;
            $write = $except = null;
	    
            // returns the socket resources in $read socket's array
            // $read array will be modified after
            socket_select($read, $write, $except, 0, 10);

            // check list for new connection ID, and if it is what is already working with him
            if(in_array($this->_connection, $read)) 
            {
		$client = socket_accept($this->_connection); 
		$this->_sockets[] = $client; 

		$header = socket_read($client, 1024);
		$this->__handShaking($header, $client, $this->_config['host'],  $this->_config['port']); // perform websocket handshake
		socket_getpeername($client, $ip);  //get ip address of connected socket

                // Create alert browser of the new connection
		$response = $this->__mask(json_encode([
                            'type'      =>  'system', 
                            'message'   =>  $ip.' connected'
                    ])
                ); 
		
		$this->__sendMessage($response);          

		// kill Connect ID used before creating a new connection
		$found_socket = array_search($this->_connection, $read);
		unset($read[$found_socket]);
            }
	
            // I now use all the connections and get responses from pure
            foreach($read as $sock) 
            {	
		// check all incoming data
		while(socket_recv($sock, $buf, 1024, 0) >= 1)
		{
                    $received         = $this->__unmask($buf); // decipher the data sent
                    $response_data    = (array)json_decode($received);
                        
                    // data that went into the client
                    $response_text = $this->__mask(json_encode($response_data));
                    $this->__sendMessage($response_text);
                    break 2; // close the connection after sending data
		}
		
                // Read incoming data from stream
		$buf = @socket_read($sock, 1024, PHP_NORMAL_READ);
		if($buf === false) 
                { 
                    // if they not exist, kill the current connection
                    $found_socket = array_search($sock, $this->_sockets);
                    socket_getpeername($sock, $ip);
                    unset($this->_sockets[$found_socket]);
			
                    // Create alert for the clien about disconnection
                    $response = $this->__mask(json_encode([
                                'type'      =>  'system', 
                                'message'   =>  $ip.' disconnected'
                        ])
                    );
		}
            }
        }
        // connection destroy
        socket_close($this->_connection);        
    }
    
    /**
     * __handShaking($receved_header,$client_conn, $host, $port) Creating a connection header to the client
     * @param text $receved_header received header of connection
     * @param resourse $client_conn connection ID
     * @param string $host host
     * @param int $port port
     * @access private
     * @return null
     */
    private function __handShaking($receved_header, $client_conn, $host, $port)
    {
	$headers = array();
	$lines = preg_split("/\r\n/", $receved_header);
	foreach($lines as $line)
	{
            $line = chop($line);
            if(preg_match('/\A(\S+): (.*)\z/', $line, $matches))
            {
		$headers[$matches[1]] = $matches[2];
            }
	}
        
        // Encrypts the key and update the Response header
	$secKey = $headers['Sec-WebSocket-Key'];
	$secAccept = base64_encode(pack('H*', sha1($secKey . '258EAFA5-E914-47DA-95CA-C5AB0DC85B11')));
        $upgrade =  \Zend\Http\Request::fromString("<<<EOS 
                HTTP/1.1 101 Web Socket Protocol Handshake\r\n
                Upgrade: websocket\r\n
                Connection: Upgrade\r\n
                WebSocket-Origin: $host\r\n
                WebSocket-Location: ws://$host:$port/websocket\r\n
                Sec-WebSocket-Accept: $secAccept\r\n\r\n
                EOS");
	socket_write($client_conn,$upgrade,strlen($upgrade));
    }    

    /**
     * __mask($text)Encrypting incoming messages from client
     * @param string $text message
     * @access private
     * @return string
     */
    private function __mask($text)
    {
	$b1 = 0x80 | (0x1 & 0x0f);
	$length = strlen($text);
	if($length <= 125) $header = pack('CC', $b1, $length);
	elseif($length > 125 && $length < 65536) $header = pack('CCn', $b1, 126, $length);
	elseif($length >= 65536) $header = pack('CCNN', $b1, 127, $length);
	return $header.$text;
    }
  
    /**
     * __unmask($text) Explanation for issuing messages to the client
     * @param string $text message
     * @access private
     * @return string
     */
    private function __unmask($text) 
    {
	$length = ord($text[1]) & 127;
	if($length == 126) 
        {
            $masks = substr($text, 4, 4);
            $data = substr($text, 8);
	}
	elseif($length == 127) 
        {
            $masks = substr($text, 10, 4);
            $data = substr($text, 14);
	}
	else 
        {
            $masks = substr($text, 2, 4);
            $data = substr($text, 6);
	}
	$text = "";
	for($i = 0; $i < strlen($data); ++$i) 
        {
            $text .= $data[$i] ^ $masks[$i%4];
	}
	return $text;
    }
    
    /**
     * __sendMessage($msg) A message telling client
     * @param string $msg Message unencrypted
     * @return boolean
     */
    private function __sendMessage($msg)
    {
	foreach($this->sockets as $sock)
	{
            @socket_write($sock, $msg, strlen($msg));
	}
	return true;
    }
    
    /**
     * sendConsoleMsg($data, $label = false) Output console message
     * @param mixed $data stdout data
     * @param string $label title
     * @acceess private
     */
    private function __sendConsoleMsg($data, $label = false)
    {
        if(is_array($data)) \Zend\Debug\Debug::dump($data, $label);
        else 
        {
            if($label) 
            {
                echo <<<EOS
                ==== $label ====\n
                $data\n
EOS;
            }
            else echo $data."\n";
        }
    }
}
?>