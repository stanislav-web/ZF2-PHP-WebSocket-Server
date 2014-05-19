<?php
namespace WebSockets; // declare namespace for the current module "WebSockets"

use Zend\ModuleManager\Feature\ConfigProviderInterface;         // interfaces for configurator
use Zend\ModuleManager\Feature\ConsoleUsageProviderInterface;   // interfaces for CLI
use Zend\Console\Adapter\AdapterInterface as Console;           // add adapter for provider

/**
 * Module for the console launch permanent connection WebSockets
 * @package Zend Framework 2
 * @subpackage WebSockets
 * @since PHP >=5.4
 * @version 1.0
 * @author Stanislav WEB | Lugansk <stanisov@gmail.com>
 * @copyright Stanilav WEB
 * @license Zend Framework GUI licene
 * @filesource /module/WebSockets/Module.php
 */
class Module {
       
    /**
     * getConfig() configurator boot method for application
     * @access public
     * @return file
     */
    public function getConfig()
    {
        return include __DIR__.'/config/module.config.php';
    }
    
    /**
     * getAutoloaderConfig() installation method autoloaders 
     * In my case, I connect the class map 
     * And set the namespace for the MVC application directory
     * @access public
     * @return array
     */
    public function getAutoloaderConfig()
    {
        return array(
            // install namespace for MVC application directory
            'Zend\Loader\StandardAutoloader'    =>  array(
                'namespaces'    =>  array(
                    __NAMESPACE__   =>  __DIR__.'/src/'.__NAMESPACE__,
                ),
            ),
        );        
    } 
    
    /**
     * getServiceConfig() method of loading services
     * @access public
     * @return file
     */
    public function getServiceConfig()
    {
        return include __DIR__.'/config/service.config.php';
    }
    
    /**
     * getConsoleUsage(Console $console) cantilever load scripts, descriptions of commands
     * @access public
     * @return console
     */
    public function getConsoleUsage(Console $console)
    {
        return array(
            
            // Here I describe the console Command
            
            'websocket open [--verbose|-v]' => 'Websocket server start',
            array('--verbose|-v'    =>  '(optional) turn on verbose mode'),
        );
    }
}
