<?php
/**
 * Configurator services module callable using ServiceManager
 */

return array(

    'invokables'    =>  array(
        'websocket.Server'      => 'Websocket\Service\WebsocketServer',       // Service for a permanent connection with
    ),
);
