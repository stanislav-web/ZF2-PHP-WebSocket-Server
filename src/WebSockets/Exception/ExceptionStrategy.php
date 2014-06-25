<?php
namespace WebSockets\Exception;

/**
 * ExceptionStrategy for WebSocket module
 * @package Zend Framework 2
 * @subpackage WebSockets
 * @since PHP >=5.4
 * @version 1.0
 * @author Stanislav WEB | Lugansk <stanisov@gmail.com>
 * @copyright Stanilav WEB
 * @license Zend Framework GUI licene
 * @filesource /module/Websocket/src/Websocket/Exception/ExceptionStrategy.php
 */
class ExceptionStrategy extends \UnexpectedValueException{
    
    /**
     * $_message only message in console
     * @var type string
     * @access protected
     */
    protected $_message = null;
    
    
    /**
     * $_line only message in console
     * @var type string
     * @access protected
     */
    protected $_line = null;
    

    
    public function throwMessage()
    {
	return $this->getMessage().' [line: '.$this->getLine().']';
    }
}
