<?php

use Phalcon\Mvc\Micro;
use Phalcon\Di\FactoryDefault;
use Phalcon\Mvc\Collection\Manager;
use function User\Token\generateToken;
use function User\Token\verifyToken;

define("BASE_PATH", (__DIR__));
require_once BASE_PATH . '/vendor/autoload.php';
require_once BASE_PATH . '/token.php';

// Use Loader() to autoload our model
$container = new FactoryDefault();
$container->set(
    'mongo',
    function () {
        $mongo = new MongoDB\Client(
            'mongodb+srv://root:VajsFVXK36vxh4M6@cluster0.nwpyx9q.mongodb.net/?retryWrites=true&w=majority'
        );
        return $mongo->api_store;
    },
    true
);
$container->set(
    'collectionManager',
    function () {
        return new Manager();
    }
);

$app = new Micro($container);

// Retrieves all products
$app->get(
    '/products/get',
    function () {
        $token = apache_request_headers()['Authorization'];
        $valid = verifyToken($token);
        $arr = explode(':', $valid);
        $result = $this->mongo->users->findOne(['app_key' => $arr[0], 'client_secret' => $arr[1]]);
        if (time() - $result->last_visit < 30) {
            echo "You can't put multiple request within 30 seconds";
        } elseif ($result->name != '') {
            $limit = 10;
            $page = 0;
            if (isset($_GET['per_page']) && $_GET['per_page'] > 0) {
                $limit = $_GET['per_page'];
            }
            if (isset($_GET['page']) && $_GET['page'] > 0) {
                $page = $_GET['page'];
            }
            $collection = $this->mongo->products;
            $productList = $collection->find([], ['limit' => (int) $limit, 'skip' => (int) $page * $limit]);
            $data = [];
            foreach ($productList as $product) {
                $data[] = [
                    'id' => $product['id'],
                    'name' => $product['name'],
                ];
            }
            echo json_encode($data);
            // update the last visit of user
            $this->mongo->users->updateOne(['client_secret' => $arr[1]], ['$set' => ['last_visit' => time()]]);
        } else {
            header('HTTP/1.0 403 Forbidden');
            echo '<h3>Not allowed!</h3>';
        }
    }
);

$app->post(
    '/register',
    function () {
        $collection = $this->mongo->users;
        $data = $_POST;
        $data['app_key'] = "5076b909d5d4913ff221";
        $data['client_secret'] = bin2hex(random_bytes(10));
        $status = $collection->insertOne($data);
        if ($status->getInsertedCount() > 0) {
            print_r(json_encode($data));
        }
    }
);

$app->post(
    '/getToken',
    function () {
        $api_key = $_GET['key'];
        $arr = explode(':', $api_key);
        return generateToken($arr[0], $arr[1]);
    }
);

$app->notFound(function () use ($app) {
    $app->response->setStatusCode(404, "Not Found")->sendHeaders();
    echo '<h1>This is crazy, but this page was not found!</h1>';
});

$app->handle($_SERVER['REQUEST_URI']);
