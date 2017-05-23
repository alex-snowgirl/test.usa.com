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
 * Class JSON
 * @package CORE\View
 */
class JSON extends View
{
    public function prepare(Response $response)
    {
        $response->addHeader('Content-Type: application/json');
        return $this->generate(array(
            'code' => $response->getCode(),
            'body' => $response->getBody()
        ));
    }

    public function generate($param)
    {
        return json_encode((array)$param);
    }
}