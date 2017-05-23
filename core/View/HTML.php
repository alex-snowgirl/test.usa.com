<?php
/**
 * Created by PhpStorm.
 * User: snowgirl
 * Date: 4/13/17
 * Time: 7:33 PM
 */
namespace CORE\View;
use CORE\Response;
use CORE\View;

/**
 * Class HTML
 * @package CORE\View
 */
class HTML extends View
{
    public function prepare(Response $response)
    {
        $response->addHeader('Content-Type: text/html');
        return $this->generate($response->getBody());
    }

    public function generate($param)
    {
        return (string)$param;
    }
}