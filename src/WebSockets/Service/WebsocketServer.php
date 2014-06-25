<?php
namespace WebSockets\Service; // Namespaces of current service

use WebSockets\Exception,
    Zend\Json\Json,
    Zend\Debug\Debug,
    Zend\Log\Logger; 

/**
 * Server for WebSocket's protocol connection
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
     * $_logger Log object
     * @access protected
     * @var  Zend\Log\Logger $_logger
     */
    protected $_logger = null;
    
    /**
     * $log Log state
     * @access private
     * @var  boolean $log
     */
    private $__log = false;
    
    /**
     * $_current Current connection
     * @access protected
     * @var  resourse #id
     */
    protected $_current = null;
    
    /**
     * $_connections Connections array
     * @access protected
     * @var  array
     */
    protected $_connections = array();   
    
    /**
     * $_clients active clients
     * @access protected
     * @var  int
     */
    protected $_clients = 0;  

    /**
     * __construct(array $config) Initializes the settings
     * @param array $config array with the connection config
     * @throws Exception
     */
    public function __construct(array $config) 
    {
	
        if(empty($config)) throw new Exception\ExceptionStrategy('Required parameters are incorrupted!');
        $this->_config    =   $config;

	
	// check if loging service is usable
	if(true === $this->_config['log'])
	{
	    // add log writer
	    if(null === $this->_logger)
	    {
		if(!file_exists($this->_config['logfile'])) 
		    throw new Exception\ExceptionStrategy("Error! File {$this->_config['logfile']} does not exist"); 
		$this->__log = true;
		$this->_logger  =	new Logger();
		$this->_logger->addWriter(new \Zend\Log\Writer\Stream($this->_config['logfile']));
	    }
	}	
	
	
	$this->console("Running server...");
	
        // open TCP / IP stream and hang port specified in the config
	$this->_current = socket_create(AF_INET, SOCK_STREAM, SOL_TCP);
		
	if(!is_resource($this->_current)) 
	    $this->console("(socket_create) Error [".socket_last_error()."]: ".socket_strerror(socket_last_error($this->_current)), true);

        //bind socket to specified host
	if(false == (socket_bind($this->_current, $this->_config['host'], $this->_config['port']))) 
	    $this->console("(socket_bind) Error [".socket_last_error()."]: ".socket_strerror(socket_last_error($this->_current)), true);

        socket_set_option($this->_current, SOL_SOCKET, SO_REUSEADDR, 1);
        
        // listen socket Resourse #id
	if(false == (socket_listen($this->_current, $this->_config['clients_limit']))) 
	    $this->console("(socket_listen) Error [".socket_last_error()."]: ".socket_strerror(socket_last_error($this->_current)), true);
        
        // add primary connection ID
        //$this->_sockets[] = $this->_current;  

        $this->console(sprintf("Listening on: %s:%d", $this->_config['host'], $this->_config['port']));
        $this->console(sprintf("Clients: %d / %d", $this->_clients, $this->_config['clients_limit']));
    }
    
    /**
     * run() Run connection
     * @access public
     * @return null
     */
    public function run()
    {
	do {
	    if(!!$connection = socket_accept($this->_current))
	    {
		if(!in_array($connection, $this->_connections))
		{
		    $current = $this->_clients;
		    $max = $this->_config['clients_limit'];
		    if($max > $current)
		    {
			socket_getpeername($connection, $ip);
			
			// perform websocket handshake
			$header = socket_read($connection, 1024);
			$this->_sendHandshake($connection, $header);
			
			// add used connected
			array_push($this->_connections, $connection);
			
			// add new connected ip (client)
			$this->_clients = $this->_clients+1;
			$this->console("{$ip} connected");
			
			// close connection
			$this->disconnect($connection);			
		    }
		    else $this->console("Max number of clients connected.");
		}
	    }
	    $i = 0; // prepare counter for the clients
	    foreach($this->_connections as $socket)
	    {
		// read response data
		$header = socket_read($socket, 1024);
		
		if(!$header)
		{
		    socket_getpeername($socket, $ip);
		    
		    $this->console("{$ip} disconnected");
		    $this->_clients = $this->_clients-1;
		    $this->disconnect($this->_connections[$i]);			
		}
		else
		{	
		    // decipher the data sent to this server
		    $msg	= $this->_unmask($header); 
		    
		    // So! here are date from client decoded as array
		    // It should made by costom client script and send via ws:// protocol
		    
		    $this->console($msg);
		    $this->onMessage(Json::encode($msg), $this->_connections[$i]);	

		    // disconnected after typing
		    $this->disconnect($this->_connections[$i]);			
		}
		$i = $i+1;
	    }
	    $i = 0;
	}
	while(true);
	
        // close primary listening socket
        $this->disconnect($this->_current);        
    }
    
    /**
     * disconnect($connection)
     * @param resource #id $connection current client
     * @access public
     * @return null
     */
    public function disconnect($connection)
    {
	unset($connection);
    }
    
    /**
     * _sendHandshake($connectionId, $headers) Do the handshaking between client and server
     * @param Resource #id $connectionId
     * @param string $headers response connection headers
     * @access protected
     * @return boolean
     */
    protected function _sendHandshake($connectionId, $headers) 
    {
	$this->console("Getting client WebSocket version...");
	if(preg_match("/Sec-WebSocket-Version: (.*)\r\n/", $headers, $match)) $version = $match[1];
	else 
	{
	    $this->console("The client doesn't support WebSocket");
	    return false;
	}
		
	$this->console("Client WebSocket version is {$version}, (required: 13)");
	if($version == 13) 
	{
	    // Extract header variables
	    if(preg_match("/GET (.*) HTTP/", $headers, $match))	    $root = $match[1];
	    if(preg_match("/Host: (.*)\r\n/", $headers, $match))    $host = $match[1];
	    if(preg_match("/Origin: (.*)\r\n/", $headers, $match))  $origin = $match[1];
	    
	    if(preg_match("/Sec-WebSocket-Key: (.*)\r\n/", $headers, $match)) $key = $match[1];
			
	    $this->console("Client headers are:");
	    $this->console("\t- Root: ".$root);
	    $this->console("\t- Host: ".$host);
	    $this->console("\t- Origin: ".$origin);
	    $this->console("\t- Sec-WebSocket-Key: ".$key);
			
	    //$this->console("Generating Sec-WebSocket-Accept key...");
	    $acceptKey = base64_encode(pack('H*', sha1($key. '258EAFA5-E914-47DA-95CA-C5AB0DC85B11')));
	    
	    // setting up new response headers
	    
	    $upgrade =  array(
		'HTTP/1.1 101 WebSocket Protocol Handshake',
		'Upgrade: websocket',
		'Connection: Upgrade',
		'WebSocket-Origin: '.$origin,
		'WebSocket-Location: ws://'.$host.$root,
		'Sec-WebSocket-Accept: '.$acceptKey
	    );
	    $upgrade = implode("\r\n", $upgrade)."\r\n\r\n";

	    //$this->console("Sending this response to the client {$connectionId}:\r\n".$upgrade);
	    if(false === socket_write($connectionId, $upgrade, strlen($upgrade))) // use ====, because might be 0 bytes returned
	    {
		$this->console("Error [".socket_last_error()."]: ".socket_strerror(socket_last_error()));
		// die() or callback from console
	    }
	    else $this->console("Handshake is successfully done!");
	    return true;
	}
	else 
	{
	    $this->console("WebSocket version 13 required (the client supports version {$version})");
	    return false;
	}
    }    

    /**
     * _mask($text) Encrypting incoming messages from client
     * @param string $text message
     * @access protected
     * @return string
     */
    protected function _mask($text)
    {
	$b1 = 0x80 | (0x1 & 0x0f);
	$length = strlen($text);
	if($length <= 125) $header = pack('CC', $b1, $length);
	elseif($length > 125 && $length < 65536) $header = pack('CCn', $b1, 126, $length);
	elseif($length >= 65536) $header = pack('CCNN', $b1, 127, $length);
	return $header.$text;
    }
  
    /**
     * _unmask($text) Explanation for issuing messages to the client
     * @param string $text message
     * @access private
     * @return string
     */
    protected function _unmask($text) 
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
     * onMessage($msg) message telling client
     * @param string $msg message unencrypted
     * @access public
     * @return boolean
     */
    public function onMessage($msg, $socket)
    {
	// encrypting received data
	$msg = $this->_mask($msg);

	//foreach($this->_sockets as $sock)
	//{
            @socket_write($socket, $msg, strlen($msg));
	//}
	return true;
    }
    
    /**
     * console($text, $exception = false, $exit = false) Output console message
     * @param string $data stdout data
     * @param boolean $exception throwed Eception
     * @param boolean $exit die console out
     * @acceess public
     */
    public function console($data, $exception = false, $exit = false)
    {
	// check if console is usable
	if(true === $this->_config['verbose'])
	{
	    if(is_array($data))
	    {
		Debug::dump($data, date('[Y-m-d H:i:s] '));
		if(isset($this->__log)) $this->_logger->info($data);
	    }
	    else
	    {
		if(!is_resource($data)) $data = mb_convert_encoding($data, $this->_config['encoding']);
		$text = date('[Y-m-d H:i:s] ').$data."\r\n";
		if($exception) 
		{
		    if($this->__log) $this->_logger->crit($text);
		    throw new Exception\ExceptionStrategy($text);
		}
		else 
		{
		    if($this->__log) $this->_logger->info($text);
		    echo $text;	
		}
	    }
	    if($exit) die();
	}
    }
}
?>