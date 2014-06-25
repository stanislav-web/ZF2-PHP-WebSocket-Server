<?php
/**
 * Configurator services module callable using ServiceManager
 */

return [

    'invokables'    =>  [
        'websocket.Server'      => 'Websocket\Service\WebsocketServer',       // Service for a permanent connection with
    ],
];
