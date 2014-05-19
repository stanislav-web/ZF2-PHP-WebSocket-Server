<?php
/**
 * Конфигуратор маршрутизатора текущего модуля (Websocket)
 * Тут задаются настройки алиасов, а также шаблон обработки URL
 * Записываются все контроллеры в процессе создания приложения
 * Устанавливается путь к приложению по умолчанию
 */
return array(
    
    //Параметры постоянного соединения (WS)
    
    'websockets'    => array(
        'server'    => array( // коннект к серверу
            'host'          =>  '127.0.0.1',  
            'port'          =>  9000
        ),
        'allowed_origins'   =>  array(   // допустимые хосты
            'zf.local',
            'www.zf.local'
        ),
    ),
    
     /**
      * Пространство имен для всех Cron консольных контроллеров
      */
    'controllers' => array(
        'invokables' => array(
            'websocket.Controller'      => 'WebSockets\Controller\WebsocketController',         // вызов контоллера управления соединением
            'websocket.CLI'             => 'WebSockets\Controller\WebsocketCLIController',      // контроллер для запуска через CLI
        ),
    ),

    /**
     * Настройка маршрутизатора модуля
     */

    'router' => array(
        'routes' => array(

            // Роут сокет-сервера
                
            'websocket' => array( // открытие соединения через браузер
                'type'          => 'Segment',
                'options'       => array(
                    'route'         => '/websocket[/:action]',
                    'constraints'   => array(
                        'action'        => 'open',
                    ),
                    'defaults' => array(
                        'controller'    => 'websocket.Controller',
                        'action'        => 'open',
                    ),
                ),
            ),
        ),
    ), 
    
    'console' => array(
        'router' => array(
            'routes' => array(
                'websocket-console' => array( // открытие соединения через CLI
                    'options'   => array(
                        'route' => 'websocket open [--verbose|-v]',
                        'defaults' => array(
                            'controller'    => 'websocket.CLI',
                            'action'        => 'open',
                        ),
                    ),
                ),            
            ),
        ),
    ),
);
