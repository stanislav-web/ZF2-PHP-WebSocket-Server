<?php
namespace WebSockets\Controller; // Namespaces of current controller

use Zend\Mvc\Controller\AbstractActionController;
use Zend\Console\Request as ConsoleRequest; // limiting console output
use WebSockets\Service\WebsocketServer as Server; // add php server
use WebSockets\Exception;

/**
 * Controller to run through a CLI
 * @package Zend Framework 2
 * @subpackage WebSockets
 * @since PHP >=5.4
 * @version 1.0
 * @author Stanislav WEB | Lugansk <stanisov@gmail.com>
 * @copyright Stanilav WEB
 * @license Zend Framework GUI licene
 * @filesource /module/Websocket/src/Websocket/Controller/WebsocketCLIController.php
 */
class WebsocketCLIController extends AbstractActionController
{
    /**
     * $_server Object server connection
     * @access private
     * @var resource
     */    
    private $_server = null;    

    /**
     * openAction() Running socket - server
     * @access public
     * @return console
     */    
    public function openAction()
    {   
        $request    = $this->getRequest();

        if(!$request instanceof ConsoleRequest) {
            throw new \RuntimeException('Use only for CLI!');
        }
        
        // include config's
        $config = $this->getServiceLocator()->get('Config')['websockets']; 

        // Try to start server
        
        try {        
            if($this->_server == null) $this->_server   = new Server($config['server']);
            $this->_server->start();
        }
        catch(Exception\ExceptionStrategy $e) 
        {
            echo $e->getMessage();
        }        
    } 
    
    /**
     * systemAction() System command
     * @access public
     * @return console
     */    
    public function systemAction()
    {   
        $request    = $this->getRequest();

        if(!$request instanceof ConsoleRequest) {
            throw new \RuntimeException('Use only for CLI!');
        }
        
        // Try to start actions
        
        try {        
            // Get system service name  from console and check if the user used --verbose or -v flag
            $option     = $request->getParam('option', false);
            $verbose    = ($request->getParam('verbose')) ? $request->getParam('verbose') : $request->getParam('v');
            
            if($verbose) echo 'Command execute: '.$option.PHP_EOL;
            $option = preg_replace('#"#', '', $option);
            
            system($option, $val);
        }
        catch(Exception\ExceptionStrategy $e) 
        {
            echo $e->getMessage();
        }        
    }     
    
}
