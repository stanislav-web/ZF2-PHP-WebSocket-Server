<?php
namespace WebSockets; // объявляю пространство имен для текущего модуля "WebSockets"

use Zend\ModuleManager\Feature\ConfigProviderInterface;         // интерфейсы для конфигуратора
use Zend\ModuleManager\Feature\ConsoleUsageProviderInterface;   // интерфейсы для консоли
use Zend\Console\Adapter\AdapterInterface as Console;           // консоль

/**
 * Модуль для консольного запуска постоянного соединенияя WebSockets
 * @package Zend Framework 2
 * @subpackage Cronjob
 * @since PHP >=5.3.xx
 * @version 2.15
 * @author Stanislav WEB | Lugansk <stanisov@gmail.com>
 * @copyright Stanilav WEB
 * @license Zend Framework GUI licene
 * @filesource /module/WebSockets/Module.php
 */
class Module {
       
    /**
     * getConfig() метод загрузки конфигуратора приложения
     * @access public
     * @return file
     */
    public function getConfig()
    {
        return include __DIR__.'/config/module.config.php';
    }
    
    /**
     * getAutoloaderConfig() метод установки автозагрузчиков
     * В моем случае, я подключаю карту классов
     * и устанавливаю пространство имен для MVC директории приложения
     * @access public
     * @return array
     */
    public function getAutoloaderConfig()
    {
        return array(
            // устанавливаю пространство имен для MVC директории с приложением
            'Zend\Loader\StandardAutoloader'    =>  array(
                'namespaces'    =>  array(
                    __NAMESPACE__   =>  __DIR__.'/src/'.__NAMESPACE__,
                ),
            ),
        );        
    } 
    
    /**
     * getServiceConfig() метод загрузки сервисов
     * @access public
     * @return file
     */
    public function getServiceConfig()
    {
        return include __DIR__.'/config/service.config.php';
    }
    
    /**
     * getConsoleUsage(Console $console) загружаю консольные скрипты, описания комманд
     * @access public
     * @return console
     */
    public function getConsoleUsage(Console $console)
    {
        return array(
            
            // Тут я описываю консольную комманду
            
            'websocket open [--verbose|-v]' => 'Websocket server start',
            array('--verbose|-v'    =>  '(optional) turn on verbose mode'),
        );
    }
}
