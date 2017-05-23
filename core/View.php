<?php

/**
 * Created by PhpStorm.
 * User: snowgirl
 * Date: 4/13/17
 * Time: 7:34 PM
 */
namespace CORE;

/**
 * Class View
 * @package CORE
 */
abstract class View
{
    /**
     * Similar to generate method, but:
     * 1) ::generate - could be used in any case without sending to the client
     * 2) ::generateFromResponse - prepare view for sending to the client
     *
     * @param Response $response
     * @return mixed
     */
    abstract public function prepare(Response $response);
}