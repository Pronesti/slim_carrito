<?php

use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;
use Slim\Factory\AppFactory;

require __DIR__ . '/../vendor/autoload.php';

$app = AppFactory::create();
session_start();

$app->get('/', function (Request $request, Response $response, array $args) {
    return $response->withHeader('Location', 'listado');
});

$app->get('/listado', function (Request $request, Response $response, array $args) {
    include_once("productos.php");
    if (!isset($_SESSION['username'])) {
        $_SESSION['username'] = "";
    }
    $te = new \Library\TemplateEngine("../templates/index.template");
    $nav = new \Library\TemplateEngine("../templates/navbar.template");
    $nav->addVariable("username", $_SESSION['username']);
    $te->addVariable("navbar", $nav->render());

    if (isset($session['username'])) {
        $te->addVariable("username", $_SESSION['username']);
    }
    $lista = "";
    foreach ($productos as $key => $item) {
        $prod = new \Library\TemplateEngine("../templates/producto.template");
        $prod->addVariable("url", $item['url']);
        $prod->addVariable("name", $item['name']);
        $prod->addVariable("price", $item['price']);
        $prod->addVariable("quantity", $item['quantity']);
        $prod->addVariable("key", $key);
        $lista .= $prod->render();
    }
    $te->addVariable("contenido", $lista);

    $response->getBody()->write($te->render());
    return $response;
});

$app->post('/listado', function (Request $request, Response $response, array $args) {
    for ($i = 0; $i < $_POST['cantidad']; $i++) {
        $_SESSION['carrito'][] = $_POST['item'];
    }
    return $response->withHeader('Location', 'listado');
});

$app->get('/verCarrito', function (Request $request, Response $response, array $args) {
    include_once("productos.php");
    if (!isset($_SESSION['carrito'])) {
        $_SESSION['carrito'] = array();
    }
    if (!isset($_SESSION['username'])) {
        $_SESSION['username'] = "";
    }
    $total = 0;
    $listaCarrito = "";
    foreach ($_SESSION['carrito'] as $posicion => $itemKey) {
        foreach ($productos as $key => $item) {
            if ($key == $itemKey) {
                $prodCarrito = new \Library\TemplateEngine("../templates/elementoCarrito.template");
                $prodCarrito->addVariable("name", $item['name']);
                $prodCarrito->addVariable("key", $key);
                $prodCarrito->addVariable("posicion", $posicion);
                $prodCarrito->addVariable("price", $item['price']);
                $prodCarrito->addVariable("url", $item['url']);
                $listaCarrito .= $prodCarrito->render();
                $total += $item['price'];
            }
        }
    }
    $te = new \Library\TemplateEngine("../templates/index.template");
    $nav = new \Library\TemplateEngine("../templates/navbar.template");
    $nav->addVariable("username", $_SESSION['username']);
    $te->addVariable("navbar", $nav->render());
    $carrito = new \Library\TemplateEngine("../templates/carrito.template");
    $carrito->addVariable("listaCarrito", $listaCarrito);
    $carrito->addVariable("total", $total);
    $te->addVariable("contenido", $carrito->render());
    $response->getBody()->write($te->render());
    return $response;
});

$app->post('/verCarrito', function (Request $request, Response $response, array $args) {
    if (isset($_POST['deleteAll'])) {
        $_SESSION['carrito'] = array();
    } else {
        unset($_SESSION['carrito'][$_POST['item']]);
    }
    return $response->withHeader('Location', 'verCarrito');
});

$app->get('/login', function (Request $request, Response $response, array $args) {
    if (!isset($_SESSION['username'])) {
        $_SESSION['username'] = "";
    }
    $te = new \Library\TemplateEngine("../templates/index.template");
    $nav = new \Library\TemplateEngine("../templates/navbar.template");
    $nav->addVariable("username", $_SESSION['username']);
    $te->addVariable("navbar", $nav->render());

    $user = new \Library\TemplateEngine("../templates/user.template");

    $user->addVariable("title", "Login");
    $user->addVariable("url", "login");
    $user->addVariable("extra", "<a href='register' class='btn btn-warning float-right w-25'>Register</a>");
    $te->addVariable("contenido", $user->render());
    $response->getBody()->write($te->render());
    return $response;
});

$app->post('/login', function (Request $request, Response $response, array $args) {
    if (!isset($_POST['logout'])) {
        if (key_exists($_POST['username'], $_SESSION['usuarios']) && $_SESSION['usuarios'][$_POST['username']] == $_POST['password']) {
            $_SESSION['username'] = $_POST['username'];
            $_SESSION['isLogged'] = true;
            return $response->withHeader('Location', 'listado');
        } else {
            return $response->withHeader('Location', 'login');
        }
    } else {
        $_SESSION['isLogged'] = false;
        $_SESSION['username'] = null;
        $_SESSION['carrito'] = array();
        return $response->withHeader('Location', 'login');
    }
    $response->getBody()->write("Redireccionando");
    return $response->withHeader('Location', 'login');
});

$app->get('/register', function (Request $request, Response $response, array $args) {
    $te = new \Library\TemplateEngine("../templates/index.template");
    $nav = new \Library\TemplateEngine("../templates/navbar.template");
    $nav->addVariable("username", $_SESSION['username']);
    $te->addVariable("navbar", $nav->render());
    $user = new \Library\TemplateEngine("../templates/user.template");
    $user->addVariable("title", "Register");
    $user->addVariable("url", "register");
    $te->addVariable("contenido", $user->render());
    $response->getBody()->write($te->render());
    return $response;
});

$app->post('/register', function (Request $request, Response $response, array $args) {
    if(key_exists($_POST['username'], $_SESSION['usuarios'])){
        return $response->withHeader('Location', 'register');
    }else{
        $_SESSION['usuarios'][$_POST['username']] = $_POST['password'];
        $_SESSION['username'] = $_POST['username'];
        $_SESSION['isLogged'] = true;
        return $response->withHeader('Location', 'listado');
    } 
    return $response;
});

$app->get('/hello/{name}', function (Request $request, Response $response, array $args) {
    $name = $args['name'];
    $response->getBody()->write("Hello, $name");
    return $response;
});

$app->run();
