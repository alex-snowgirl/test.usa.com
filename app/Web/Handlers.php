<?php
/**
 * Created by PhpStorm.
 * User: snowgirl
 * Date: 5/23/17
 * Time: 11:49 PM
 */
namespace APP\Web;

use CORE\App;
use CORE\Request;

/**
 * Class Handlers
 * @package APP\Web
 */
class Handlers extends \CORE\Handlers
{
    public function bindCustom(Request $request)
    {
        $request->on('get', '/', array($this, 'index'));
    }

    public function bindDefault(Request $request)
    {
        $request->onUnknown(function (App $app) {
            $app->response->setCode(404)
                ->setBody('Not Found');
        });
    }

    public function index(App $app)
    {
        $tmp = time();

        $config = json_encode(array(
            'apiEndpoint' => 'api.php',
            'isCacheItems' => $app->config->raw->app->{'cache.items'},
            'isCrawlOnClientSide' => $app->config->raw->app->{'crawl_on_client_side'}
        ));

        /**
         * !!! Simple template engine
         * @todo View object and templates...
         */
        $output = <<<HTML
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>The Rest Crawler</title>
    <link rel="stylesheet" href="media/app.css?_=$tmp">
</head>
<body>
    <h1>The Rest Crawler</h1>
    <div id="app" class="loading"></div>
    <script type="text/javascript" src="media/jquery.min.js"></script>
    <script type="text/javascript" src="media/app.js?_=$tmp"></script>
    <script type="text/javascript">new restCrawlerApp('app', $config);</script>
    <link href="https://fonts.googleapis.com/css?family=Oswald" rel="stylesheet">
</body>
</html>
HTML;

        $app->response->setCode(200)
            ->setBody($output);
    }
}