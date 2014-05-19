<?php

namespace WebSockets\Exception;

/**
 * Exception used when a file read or write fails
 * @package Zend Framework 2
 * @subpackage WebSockets
 * @since PHP >=5.4
 * @version 1.0
 * @author Stanislav WEB | Lugansk <stanisov@gmail.com>
 * @copyright Stanilav WEB
 * @license Zend Framework GUI licene
 * @filesource /module/Websocket/src/Websocket/Exception/ExceptionStrategy.php
 */
class ExceptionStrategy extends \RuntimeException {
    
    public function getMessage()
    {
       return parent::getMessage("#".$this->getCode()."\r\n ".$this->getFile()." ".$this->getLine()."\r\n".$this->getMessage());
    }
}
