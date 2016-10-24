<?php
namespace WebSockets\Factory; // Namespaces of current controller

use Websockets\Controller\WebsocketCLIController;
use Zend\ServiceManager\FactoryInterface;
use Zend\ServiceManager\ServiceLocatorInterface;
/**
 * Created by IntelliJ IDEA.
 * User: jason
 * Date: 23/04/2016
 * Time: 3:19 PM
 */
class WebsocketCLIControllerFactory implements FactoryInterface
{
    public function createService(ServiceLocatorInterface $serviceLocator)
    {
        $realServiceLocator = $serviceLocator->getServiceLocator();
        return new WebsocketCLIController($realServiceLocator);
    }
}