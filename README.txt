USAGE: cd www & php -S 0.0.0.0:8080 & http://0.0.0.0:8080/index.php

Test WEB & API project for usa Invoice Services Company
A lot of TODOs present, due to other interviews and time limits

FULLY(!) written by me (except jquery.js, fonts).

This project follows MVP concept,
that means - MINIMUM VIABLE PRODUCT,
that means - if you find something not fully developed or implemented, - probably I know about this,
and could easily fix, improve, add or build that (those) things,


Please take into account - this is a TEST(!) project

-Simple Config File-
Location - config.ini

-PHP-
@todo PHP7.1
@TODO no security(auth, checks) layer
@TODO no internal states
@todo tests
@todo async requests
@todo validations
@todo add more abstracts for each goal
@todo remove dependencies if present
@todo add normal config file, add services throw DI container
@todo cache
@todo docker file

!!! ASSUMING THIS IS WEB PROJECT... there are no any other cores except WEB libs,
no interfaces or lower level abstracts, test project...

Main Classes:
1) CORE\App - Simple Universal Application class
2) CORE\Request - Simple Universal Web Request manager
3) CORE\Response - Simple Universal Web Response manager
4) APP\Web\Handlers - Simple WEB requests handler (implemented instead of well-known Controller-Action pattern)
@todo split into routing and actions
5) APP\Api\Handlers - Simple API requests handler (implemented instead of well-known Controller-Action pattern)
@todo split into routing and actions
6) And other things...

-JavaScript-
Main File:
1) www/media/app.js - fully responsible for the Client application
2) App uses Browser local and session DBs
3) App uses API APP of Backend Application (api.php)
4) And more other things...
@todo pre-processors, minify
@todo split into separate classes (entity, storage, view etc.)
@todo add error handlers
@todo implement promises
@todo improve cache

-CSS-
1) www/media/app.css - fully responsible for the Client look and feel
@todo pre-processors, minify
@todo split into separate files page and app styles

-Crawler-
1) This is Restaurant Finder simulator
2) Load your source file with rest offers
3) Pick up what are you going to eat
4) Press "Crawl" and enjoy

5) States support
6) You could use Back button - to get on previous state
7) You could use Clear button - to clear all Browser data and begin your awesome trip from zero

Dependencies (Tested on):
1) SL: PHP 5.6
2) PHP ext: json
3) PHP composer (No deps so could be easily removed, but need to add your custom auto-loader)
4) OS: Debian

Usage:
1) cd /path/to/project
2) composer install

#if you aren't using dedicated web-server - you could use PHP's one
#cd www & php -S 0.0.0.0:8080 & http://0.0.0.0:8080/index.php

3) setup PHP + dependencies
4) setup Apache virtual host + dependencies (or skip if using build-in PHP web-server)
5) Document root should be fully managed by php (read & write)
6) enjoy
7) simple config present

Total time spent in general: 4 hours.

Questions - alex.snowgirl@gmail.com


