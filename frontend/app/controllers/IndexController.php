<?php
namespace MyApp\Controllers;

use Phalcon\Mvc\Controller;

session_start();
class IndexController extends Controller
{
    public function indexAction()
    {
        // redirected to view
    }
    public function signupAction()
    {

        $arr = [
            'name' => $_POST['name'],
            'mail' => $_POST['mail'],
            'pass' => $_POST['pass'],
        ];
        if ($arr['pass'] != '' && $arr['name'] != '') {
            $ch = curl_init();
            $url = "http://172.25.0.4/register";
            curl_setopt($ch, CURLOPT_URL, $url);
            curl_setopt($ch, CURLOPT_POST, 1);
            curl_setopt($ch, CURLOPT_POSTFIELDS, $arr);

            // Receive server response ...
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);

            $output = curl_exec($ch);
            curl_close($ch);
            $output = json_decode($output, true);
            if ($output) {
                $str = $output['app_key'] . ':' . $output['client_secret'];
                $this->response->redirect("/index/token?key=$str");
            } else {
                echo "<h3>There was some error</h3>";
                die;
            }
        } else {
            echo "<h1>Enter Correct Details</h1>";
            die;
        }
    }

    public function tokenAction()
    {
        $api_key = $_GET['key'];
        $curl = curl_init();

        curl_setopt_array($curl, array(
            CURLOPT_URL => "http://172.25.0.4/getToken?key=$api_key",
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_MAXREDIRS => 10,
            CURLOPT_FOLLOWLOCATION => true,
            CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
            CURLOPT_CUSTOMREQUEST => 'POST',
        ));
        $response = curl_exec($curl);
        curl_close($curl);
        $_SESSION['access_token'] = $response;
        $this->response->redirect('/index/products');
    }

    public function productsAction()
    {
        if ($_SESSION['access_token'] == '') {
            echo "<h3>Please log in first</h3>";
            die;
        }
        $page = 0;
        $per_page = 10;
        $page = $_GET['page'];
        $per_page = $_GET['per_page'];
        $curl = curl_init();
        curl_setopt_array($curl, array(
            CURLOPT_URL => "http://172.25.0.4/products/get?page=$page&per_page=$per_page",
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_MAXREDIRS => 10,
            CURLOPT_FOLLOWLOCATION => true,
            CURLOPT_CUSTOMREQUEST => 'GET',
            CURLOPT_HTTPHEADER => array(
                "Authorization: $_SESSION[access_token]"
            ),
        ));

        $response = curl_exec($curl);
        curl_close($curl);
        $this->view->data = $response;
    }

    public function logoutAction()
    {
        session_unset();
        session_destroy();
        $this->response->redirect('/index/');
    }
}
