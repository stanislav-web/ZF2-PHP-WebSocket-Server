ZF2 PHP WebSocket Server v1.3
![Alt text](https://encrypted-tbn2.gstatic.com/images?q=tbn:ANd9GcRpi209uZxeUrXP6cFLxuFbsTQkm9V0anTgp7Y-ltpEG6sw-txlvg "WebSockets")
--------------------------------------

#### Requirements
------------
* PHP 5.4+
* [Zend Framework 2](https://github.com/zendframework/zf2)

#### Changes
------------
v1.3
- Add logger

v1.2
- Add verbose turner (console show\hide) (@see module.config.php)
- Add socket function's exception handler
- Fixed CLI stdout>> encoding
- Add ViewHelper for a simple get server config params into view
```
<?php
// for example
echo $this->socket()->config('host'); // print 127.0.0.1 ?>
?>
```
v1.1.2
- Console stdout>> while starting server

v1.1
- Fixes some problem with startup
- Add console command interface for costom system commands (for example)
`
php -q index.php websocket system -v "whoami"
`
(Note for ZF2.2): if you have an exceptions `Notice Undefined offset: 0` while starting console server please follow this:

`vendor\ZF2\library\Zend\Mvc\View\Console\RouteNotFoundStrategy.php` 381 and replace line by 
```
<?php
$result .= isset($row[0]) ? $row[0] . "\n" : '';
?>
```
It might be fixed until not fix in the next update.
You're always can ask me for this module if you have write me [issue](https://github.com/zendframework/zf2https://github.com/stanislav-web/ZF2-PHP-WebSocket-Server/issues)

#### Installation and Running Server :
------------
1. That needs to be done is adding it to your application's list of active modules. Add module "WebSockets" in your application.config.php

2. Change host address in module.config.php

3. Go to your shell command-line interface and type (running server as background): `php -q index.php websocket open`

4. Setup your Client-side script's to communicating with the server .. ws://host:port/websocket/open communicating as similarity

--------------------------------------
In order to start using the module clone the repo in your vendor directory or add it as a submodule if you're already using git for your project:

    `
    git clone https://github.com/stanislav-web/ZF2-PHP-WebSocket-Server.git vendor/WebSockets
    or
    git submodule add     git clone https://github.com/stanislav-web/ZF2-PHP-WebSocket-Server.git vendor/WebSockets
    `
    
The module will also be available as a Composer package soon.

#### Libraries used
------------
- [Zend Framework 2.3](https://github.com/zendframework/zf2)

![Alt text](http://cs620519.vk.me/v620519508/a755/4lwSwtVe59g.jpg "WebSockets")


