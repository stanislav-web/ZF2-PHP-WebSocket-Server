<?php

namespace WebSockets\Service;
use WebSockets\Exception;

/**
 * Сервер для соединения по протоколу WebSocket
 * @package Zend Framework 2
 * @subpackage WebSockets
 * @since PHP >=5.3.xx
 * @version 2.15
 * @author Stanislav WEB | Lugansk <stanisov@gmail.com>
 * @copyright Stanilav WEB
 * @license Zend Framework GUI licene
 * @filesource /module/Websocket/src/Websocket/Service/WebsocketServer.php
 */
class WebsocketServer {
    
    /**
     * $_config Конфигурация сервера
     * @access protected
     * @var  array
     */
    protected $_config = null;
    
    /**
     * $_connection Resource ID соединения
     * @access protected
     * @var  resourse
     */
    protected $_connection = null;
    
    /**
     * $__clients ID соединений, которые использует поток
     * @access private
     * @var  array
     */
    private $__clients = array();  
    
    /**
     * __construct(array $config) Конструктор инициализирует настройки
     * @param array $config массив с настройками соединения
     * @throws Exception
     */
    public function __construct(array $config) 
    {
        if(empty($config)) throw new Exception\ExceptionStrategy('Required parameters are incorrupted!');
        $this->_config    =   $config;
    }
    
    
    public function start()
    {
        $null = NULL;
        
        // открываю TCP/IP поток и вешаю указанный в конфиге порт
        $this->_connection = socket_create(AF_INET, SOCK_STREAM, SOL_TCP);
        socket_set_option($this->_connection, SOL_SOCKET, SO_REUSEADDR, 1);
        socket_bind($this->_connection, 0, $this->_config['port']);
        socket_listen($this->_connection);
        $this->__clients = array($this->_connection);  
        
        // запускаю бесконечное соединение
        
        while(true) 
        {
            // Получаю ID сокет соединения
            $changed = $this->__clients;
            socket_select($changed, $null, $null, 0, 10);
	
            // проверяю по листу новый ID соединения, и если он есть то работаю уже с ним
            if(in_array($this->_connection, $changed)) 
            {
		$socket_new = socket_accept($this->_connection); 
		$this->__clients[] = $socket_new; 
		
		$header = socket_read($socket_new, 1024);
		$this->__handShaking($header, $socket_new, $this->_config['host'],  $this->_config['port']); //perform websocket handshake
		socket_getpeername($socket_new, $ip); // получаю IP сокет соединения
                
                // создаю оповещение браузеру о новом соединении
		$response = $this->__mask(json_encode(
                        [
                            'type'      =>  'system', 
                            'message'   =>  $ip.' connected'
                        ]
                    )
                ); 
		$this->__sendMessage($response); 
                
		//убиваю использованный Connect ID перед созданием нового соединения
		$found_socket = array_search($this->_connection, $changed);
		unset($changed[$found_socket]);
            }
	
            // Теперь использую все соединения для получения ответов в чистом виде
            foreach($changed as $changed_socket) 
            {	
		// проверяю все входящие данные
		while(socket_recv($changed_socket, $buf, 1024, 0) >= 1)
		{
                    $received               = $this->__unmask($buf); // расшифровую посланные данные
                    $response_data    = (array)json_decode($received);
                        
                    // данные которые отправяться в браузер
                    $response_text = $this->__mask(json_encode($response_data));
                    $this->__sendMessage($response_text);
                    break 2; // закрываю соединение после отправки данных
		}
		
                // Получаю входящие данные
		$buf = @socket_read($changed_socket, 1024, PHP_NORMAL_READ);
		if($buf === false) 
                { 
                    // если их нет, то убиваю текущее соединение
                    $found_socket = array_search($changed_socket, $clients);
                    socket_getpeername($changed_socket, $ip);
                    unset($clients[$found_socket]);
			
                    // создаю оповещение браузеру о разрыве соединения
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

        // уничтожаю сокет
        socket_close($this->_connection);        
    }
    
    /**
     * __handShaking($receved_header,$client_conn, $host, $port) Создание заголовка соединения для браузера
     * @param text $receved_header Полученный заголовок
     * @param resourse $client_conn ID соединения
     * @param string $host Хост
     * @param int $port Порт
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
        
        // Шифрую ключ и обновляю Response заголовок
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
     * __mask($text) Шифрование входящего сообщения
     * @param string $text
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
     * __unmask($text) Расшифровка сообщения на выдачу в браузер
     * @param string $text
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
     * __sendMessage($msg) Выдача сообщения браузеру
     * @param string $msg Сообщение в нешифрованном виде
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