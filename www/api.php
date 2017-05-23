<?php
/**
 * Created by PhpStorm.
 * User: snowgirl
 * Date: 4/14/17
 * Time: 7:41 PM
 */
use CORE\App;
use CORE\Config;
use APP\Api\Handlers;
use CORE\View\JSON;
use CORE\Exception;

$loader = require_once '../ini.php';

/**
 * !!! Simple application with wide control (mounted components)
 */

$app = new App(
    new Config($loader, $tmp = realpath(__DIR__ . '/..'), $tmp . '/config.ini'),
    new Handlers(),
    new JSON()
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