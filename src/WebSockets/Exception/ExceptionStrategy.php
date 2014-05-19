<?php

namespace WebSockets\Exception;

/**
 * Exception used when a file read or write fails.
 */
class ExceptionStrategy extends \RuntimeException {
    
    public function getMessage()
    {
       return parent::getMessage("#".$this->getCode()."\r\n ".$this->getFile()." ".$this->getLine()."\r\n".$this->getMessage());
    }
}
