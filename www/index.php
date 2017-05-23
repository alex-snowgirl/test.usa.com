<?php
/**
 * Created by PhpStorm.
 * User: snowgirl
 * Date: 4/13/17
 * Time: 5:13 PM
 */
use CORE\App;
use CORE\Config;
use APP\Web\Handlers;
use CORE\View\HTML;
use CORE\Exception;

$loader = require_once '../ini.php';

/**
 * !!! Simple application with some control (mounted components)
 */
$app = new App(
    new Config($loader, $tmp = realpath(__DIR__ . '/..'), $tmp . '/config.ini'),
    new Handlers(),
    new HTML()
);

$app->on(App::EVENT_EXCEPTION, function (App $app, Exception $ex) {
    $app->response->setCode(500);

    if ('dev' == $app->config->raw->app->env) {
        $app->response->setBody($ex->getTraceAsString());
    } else {
        $app->response->setBody('Ooops! Something bad happened over here...');
    }

    $app->response->send();
});

$app->config->logger->log('running...');
$app->run();