<?php
namespace WebSockets\View\Helper;

use Zend\View\Helper\AbstractHelper,
    Zend\ServiceManager\ServiceLocatorAwareInterface,  
    Zend\ServiceManager\ServiceLocatorInterface;  

/**
 * ViewHelper for WebSockets module
 * @package Zend Framework 2
 * @subpackage WebSockets
 * @since PHP >=5.3.xx
 * @version 2.15
 * @author Stanislav WEB | Lugansk <stanisov@gmail.com>
 * @copyright Stanilav WEB
 * @license Zend Framework GUI licene
 * @filesource /vendor/WebSockets/src/WebSockets/View/Helper/Socket.php
 */
class Socket extends AbstractHelper implements ServiceLocatorAwareInterface {

    /**
     * Service Manager instance
     * @access protected
     * @var object $sm ServiceManager Instance object
     */
    protected $sm;

    /** 
     * Set the service locator. 
     * 
     * @param ServiceLocatorInterface $serviceLocator 
     * @return CustomHelper 
     */  
    public function setServiceLocator(ServiceLocatorInterface $serviceLocator)  
    {  
        $this->sm = $serviceLocator;  
        return $this;  
    }  
    /** 
     * Get the service locator. 
     * 
     * @return \Zend\ServiceManager\ServiceLocatorInterface 
     */  
    public function getServiceLocator()  
    {  
        return $this->sm;  
    }     

    public function __invoke() {
	return $this;
    }    
    
    /**
     * config($key) get confuration module param
     * @access public
     * @return string
     */
    public function config($key)
    {
        $config = $this->sm->getServiceLocator()->get('Configuration')['websockets']['server'];
        if(isset($config[$key])) return $config[$key];
        else return null;
    }
}