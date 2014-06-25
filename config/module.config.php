<?php
/** 
  * Configurator router current module (Websocket) 
  * Here are set settings aliases and URL template processing 
  * Recorded all controllers in the process of creating an application 
  * Set the path to the application by default 
  */
return [
    
    // The parameters of the compound (WS)
    
    'websockets'    => [
        'server'    => [ // setup WebSocket connection
            'host'			=>  '127.0.0.1',  
            'port'			=>  9000,
	    'clients_limit'		=>  10,		// limit active connections per socket loop
	    'max_disconnections_time'	=>  30,		// limit active connections per socket loop
	    'verbose'			=>  true,		// console stdout>>
	    'encoding'			=>  'UTF-8',		// console encoding
	    'log'			=>  true,
	    'logfile'			=>  'logs/socket/actions.log',
	    'action'		=>  '/websocket/open' // open controller/action from websocket.Controller
        ],
    ],
    
     /**
      * Namespace for all controllers
      */
    'controllers' => [
        'invokables' => [
            'websocket.Controller'      => 'WebSockets\Controller\WebsocketController',         // call controller connection management
            'websocket.CLI'             => 'WebSockets\Controller\WebsocketCLIController',      // controller to run through the CLI
        ],
    ],

    /**
     * Configure the router module
     */

    'router' => [
        'routes' => [

            // Rout for socket server
                
            'websocket' => [ // opening a connection through a browser (not recomended)
                'type'          => 'Segment',
                'options'       => [
                    'route'         => '/websocket[/:action]',
                    'constraints'   => [
                        'action'        => 'open',
                    ],
                    'defaults' => [
                        'controller'    => 'websocket.Controller',
                        'action'        => 'open',
                    ],
                ],
            ],
        ],
    ], 
    
    'console' => [
        'router' => [
            'routes' => [
                'websocket-console' => [ // opening a connection through a CLI
                    'options'   => [
                        'route' => 'websocket open',
                        'defaults' => [
                            '__NAMESPACE__' => 'WebSockets\Controller\WebsocketCLIController',
                            'controller'    => 'websocket.CLI',
                            'action'        => 'open',
                        ],
                    ],
                ],  
                'websocket-console-info' => [ // costom system command
                    'options'   => [
                        'route' => 'websocket system [--verbose|-v] <option>',
                        'defaults' => [
                            '__NAMESPACE__' => 'WebSockets\Controller\WebsocketCLIController',
                            'controller'    => 'websocket.CLI',
                            'action'        => 'system',
                        ],
                    ],
                ],                
            ],
        ],
    ],
];
