ZF2 PHP WebSocket Server v1.0
--------------------------------------
Require PHP 5.4+

Running Server :

1. That needs to be done is adding it to your application's list of active modules. Add module "WebSockets" in your application.config.php

2. Change host address in module.config.php

3. Go to your shell command-line interface and type: php -q index.php websocket open

--------------------------------------
In order to start using the module clone the repo in your vendor directory or add it as a submodule if you're already using git for your project:

    git clone https://github.com/stanislav-web/ZF2-PHP-WebSocket-Server.git vendor/WebSockets
    or
    git submodule add     git clone https://github.com/stanislav-web/ZF2-PHP-WebSocket-Server.git vendor/WebSockets

The module will also be available as a Composer package soon.


