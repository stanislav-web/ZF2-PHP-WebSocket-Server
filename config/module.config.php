<?php
/** 
  * Configurator router current module (Websocket) 
  * Here are set settings aliases and URL template processing 
  * Recorded all controllers in the process of creating an application 
  * Set the path to the application by default 
  */
return array(
    
    // The parameters of the compound (WS)
    
    'websockets'    => array(
        'server'    => array( // коннект к серверу
            'host'          =>  '127.0.0.1',  
            'port'          =>  9000
        ),
    ),
    
     /**
      * Namespace for all controllers
      */
    'controllers' => array(
        'invokables' => array(
            'websocket.Controller'      => 'WebSockets\Controller\WebsocketController',         // call controller connection management
            'websocket.CLI'             => 'WebSockets\Controller\WebsocketCLIController',      // controller to run through the CLI
        ),
    ),

    /**
     * Configure the router module
     */

    'router' => array(
        'routes' => array(

            // Rout for socket server
                
            'websocket' => array( // opening a connection through a browser (not recomended)
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
                'websocket-console' => array( // opening a connection through a CLI
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
