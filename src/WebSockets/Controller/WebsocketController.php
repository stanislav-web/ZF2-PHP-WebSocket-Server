<?php
namespace WebSockets\Controller; // Namespaces of current controller

use Zend\Mvc\Controller\AbstractActionController;
use WebSockets\Service\WebsocketServer as Server;
use WebSockets\Exception;

/**
 * Controller to run through a browser
 * @package Zend Framework 2
 * @subpackage WebSockets
 * @since PHP >=5.4
 * @version 1.0
 * @author Stanislav WEB | Lugansk <stanisov@gmail.com>
 * @copyright Stanilav WEB
 * @license Zend Framework GUI licene
 * @filesource /module/Websocket/src/Websocket/Controller/WebsocketController.php
 */
class WebsocketController extends AbstractActionController
{

    /**
     * $_server Object server connection
     * @access private
     * @var resource
     */    
    private $_server = null;
    protected $serviceLocator;
    /**
     * openAction() Running socket - server
     * @access public
     * @return console
     */
    public function __construct($serviceLocator)
    {
        $this->serviceLocator = $serviceLocator;
    }

    public function openAction()
    {   
        // include config's
        $config = $this->serviceLocator->get('Config')['websockets'];

        // Try to start server
        
        try {        
            if($this->_server == null) $this->_server   = new Server($config['server']);
	    // running server 
            $this->_server->run();
        }
        catch(Exception\ExceptionStrategy $e) 
        {
            echo $e->getMessage();
        } 
    } 
}
