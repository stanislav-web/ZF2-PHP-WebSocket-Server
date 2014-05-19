<?php
/**
 * Конфигуратор сервисов модуля, вызываемы с помощью ServiceManager
 * Helper
 * Model
 * Service
 * Validator
 */

return array(

    'invokables'    =>  array(
        'websocket.Server'      => 'Websocket\Service\WebsocketServer',       // сервер для работы с постоянным соединением
    ),
);
