<?php
namespace WebSockets\Controller; // пространтво имен текущего контроллера

use Zend\Mvc\Controller\AbstractActionController;
use WebSockets\Service\WebsocketServer as Server;
use WebSockets\Exception;

/**
 * Контроллер планировщика
 * @package Zend Framework 2
 * @subpackage WebSockets
 * @since PHP >=5.3.xx
 * @version 2.15
 * @author Stanislav WEB | Lugansk <stanisov@gmail.com>
 * @copyright Stanilav WEB
 * @license Zend Framework GUI licene
 * @filesource /module/Websocket/src/Websocket/Controller/WebsocketController.php
 */
class WebsocketController extends AbstractActionController
{

    /**
     * $_server Объект соединения с сервером
     * @access private
     * @var resource
     */    
    private $_server = null;    
    
    /**
     * zfService() Менеджер зарегистрированных сервисов ZF2
     * @access public
     * @return ServiceManager
     */
    public function zfService()
    {
        return $this->getServiceLocator();
    } 
    
    /**
     * openAction() Запуск сокет - сервера
     * @access public
     * @return console
     */    
    public function indexAction()
    {   
        // Подключаю настройки
        $config = $this->zfService()->get('Config')['websockets']; 

        // Запускаю сервер
        
        try {
            if($this->_server == null) $this->_server   = new Server($config['server']);
            $this->_server->start();
        } 
        catch (Exception\ExceptionStrategy $e) 
        {
            echo $e->getMessage();
        }
    } 
    
    /**
     * closeAction() Остановка сокет - сервера
     * @access public
     * @return console
     */    
    public function closeAction()
    {   

    }    
}
