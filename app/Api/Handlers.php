<?php
/**
 * Created by PhpStorm.
 * User: snowgirl
 * Date: 5/23/17
 * Time: 12:25 AM
 */
namespace APP\Api;

use CORE\App;
use CORE\Config;
use CORE\Request;

/**
 * Class Handlers
 * @package APP\Api
 */
class Handlers extends \CORE\Handlers
{
    public function bindCustom(Request $request)
    {
        $request->on('post', 'items', array($this, 'setItems'))
            ->on('get', 'items', array($this, 'getItems'))
            ->on('get', 'restaurant', array($this, 'getRestaurant'));
    }

    public function bindDefault(Request $request)
    {
        $request->onUnknown(function (App $app) {
            $app->response->setCode(404)->setBody('Not Found');
        });
    }

    /**
     * !!! Simple items file loader and import handler
     * @todo use file manager... ($name = FileManager::save($file))
     * @todo use file parser... ($items = FileParser::parse($name))
     * @todo remove all logic from here... left upper abstractions...
     * @todo validate...
     * @todo could be any storage (mounted - is a way better with uni interface),
     * @todo...for example, import into RDBMS (MySQL or smth.), for simplicity - store data as is (file)
     * @todo - is a way faster then RDBMS, but slower then NoSQL (RAM)
     *
     * @param App $app
     * @return $this
     */
    public function setItems(App $app)
    {
        if (!$file = $app->request->file) {
            return $app->response->setCode(400);
        }

        //@todo use file manager... ($name = FileManager::save($file))
        if (!move_uploaded_file($file['tmp_name'], $this->makeItemsFilePath($app->config))) {
            return $app->response->setCode(500);
        }

        return $app->response->setCode(200);
    }

    /**
     * !!! Simple items (unique) fetcher
     * @todo remove all logic from here... left upper abstractions...
     *
     * @param App $app
     * @return $this
     */
    public function getItems(App $app)
    {
        $offers = array();
        $items = array();

        $this->walkItems($app, function ($offer) use (&$offers, &$items) {
            $offers[] = $offer;
            $items = array_merge($items, $offer['items']);
        });

        return $app->response->setCode(200)
            ->setBody(array(
                'offers' => $offers,
                'items' => array_values(array_unique($items)),
                'columns' => $this->makeOfferColumns()
            ));
    }

    /**
     * !!! Simple restaurant crawler - using initial storage(file)
     * @todo remove all logic from here... left upper abstractions...
     * @todo considering we are using raw file - so we operate items names (no ids or something...)
     *
     * @todo assuming restaurant do not have separate item if it is already present in it's meal...
     *
     * @param App $app
     * @return $this
     */
    public function getRestaurant(App $app)
    {
        if (!$items = $app->request->items) {
            return $app->response->setCode(400);
        }


        //item => array(restaurant => price)
        $tmp = array();

        $this->walkItems($app, function ($offer) use ($items, &$tmp) {
            foreach ($items as $item) {
                if (in_array($item, $offer['items'])) {
                    if (!isset($tmp[$item])) {
                        $tmp[$item] = array();
                    }

                    $tmp[$item][$offer['id']] = $offer['price'];
                }
            }
        });

//        echo '<pre>';
//        print_r($tmp);

        $data = array(
            'id' => null,
            'price' => 0
        );

        $tmp = array_values($tmp);

        if (sizeof($items) == sizeof($tmp)) {
            //restaurant => totalPrice
            $tmp2 = $tmp[0];

            for ($i = 1, $l = sizeof($tmp); $i < $l; $i++) {
                $tmp3 = array_intersect_key($tmp[$i], array_flip(array_keys($tmp2)));

                foreach ($tmp2 as $id => $price) {
                    if (isset($tmp3[$id])) {
                        $tmp2[$id] += $tmp3[$id];
                    } else {
                        unset($tmp2[$id]);
                    }
                }
            }

            if (sizeof($tmp2)) {
                $data['price'] = number_format(min($tmp2), 2);
                $data['id'] = array_keys($tmp2, $data['price'])[0];
            }
        }

//        echo '<pre>';
//        print_r($tmp2);

        return $app->response->setCode(200)
            ->setBody($data);
    }

    /**
     * !!! Very Simple File Parser (fast, file stored as is)
     * @todo use file parser... ($data = FileParser::parse($name))
     *
     * @param App $app
     * @param \Closure $fn
     */
    protected function walkItems(App $app, \Closure $fn)
    {
        $columns = $this->makeOfferColumns();

        $columnsSize = sizeof($columns);

        $handler = fopen($this->makeItemsFilePath($app->config), 'r');

        while ($line = rtrim(fgets($handler))) {
            $tmp = array_combine($columns, explode(', ', $line, $columnsSize));
            $tmp['items'] = explode(', ', $tmp['items']);

            if (false === $fn($tmp)) {
                continue;
            }
        }

        fclose($handler);
    }

    /**
     * @todo implement path builders ($path = Config::makePath('tmp'))
     *
     * @param Config $config
     * @return string
     */
    protected function makeItemsFilePath(Config $config)
    {
        return join('/', array(
            $config->root,
            $config->raw->app->{'dir.tmp'},
            'items.csv'
        ));
    }

    protected function makeOfferColumns()
    {
        return array('id', 'price', 'items');
    }
}