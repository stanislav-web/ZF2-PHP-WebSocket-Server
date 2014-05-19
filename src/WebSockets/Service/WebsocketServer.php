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
     * $__clients ID compounds which uses stream
     * @access private
     * @var  array
     */
    private $__clients = array();  
    
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
        $null = NULL;
        
        // open TCP / IP stream and hang port specified in the config
        $this->_connection = socket_create(AF_INET, SOCK_STREAM, SOL_TCP);
        socket_set_option($this->_connection, SOL_SOCKET, SO_REUSEADDR, 1);
        socket_bind($this->_connection, 0, $this->_config['port']);
        socket_listen($this->_connection);
        $this->__clients = array($this->_connection);  
        
        // run endless connection
        
        while(true) 
        {
            // Get socket connection ID
            $changed = $this->__clients;
            socket_select($changed, $null, $null, 0, 10);
	
            // check list for new connection ID, and if it is what is already working with him
            if(in_array($this->_connection, $changed)) 
            {
		$socket_new = socket_accept($this->_connection); 
		$this->__clients[] = $socket_new; 
		
		$header = socket_read($socket_new, 1024);
		$this->__handShaking($header, $socket_new, $this->_config['host'],  $this->_config['port']); // perform websocket handshake
		socket_getpeername($socket_new, $ip); // get IP socket connection
                
                // Create alert browser of the new connection
		$response = $this->__mask(json_encode(
                        [
                            'type'      =>  'system', 
                            'message'   =>  $ip.' connected'
                        ]
                    )
                ); 
		$this->__sendMessage($response); 
                
		// kill Connect ID used before creating a new connection
		$found_socket = array_search($this->_connection, $changed);
		unset($changed[$found_socket]);
            }
	
            // I now use all the connections and get responses from pure
            foreach($changed as $changed_socket) 
            {	
		// check all incoming data
		while(socket_recv($changed_socket, $buf, 1024, 0) >= 1)
		{
                    $received               = $this->__unmask($buf); // decipher the data sent
                    $response_data    = (array)json_decode($received);
                        
                    // data that went into the client
                    $response_text = $this->__mask(json_encode($response_data));
                    $this->__sendMessage($response_text);
                    break 2; // close the connection after sending data
		}
		
                // Read incoming data from stream
		$buf = @socket_read($changed_socket, 1024, PHP_NORMAL_READ);
		if($buf === false) 
                { 
                    // if they not exist, kill the current connection
                    $found_socket = array_search($changed_socket, $clients);
                    socket_getpeername($changed_socket, $ip);
                    unset($clients[$found_socket]);
			
                    // Create alert for the clien about disconnection
                    $response = $this->__mask(json_encode(
                            [
                                'type'      =>  'system', 
                                'message'   =>  $ip.' disconnected'
                            ]
                        )
                    );
                    $this->__sendMessage($response);
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
	foreach($this->__clients as $changed_socket)
	{
            @socket_write($changed_socket, $msg, strlen($msg));
	}
	return true;
    }
}
?>