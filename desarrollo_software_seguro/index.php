<?php
ob_start();
session_start();

require_once "models/DataBase.php";

/* Lista de controladores permitidos */
$allowedControllers = [
    "Landing",
    "Login",
    "User",
    "Product",
    "Dashboard"
];

/* Obtener controlador */
$controller = isset($_REQUEST['c']) ? $_REQUEST['c'] : "Landing";

/* Validar que el controlador esté permitido */
if (!in_array($controller, $allowedControllers)) {
    $controller = "Landing";
}

$route_controller = "controllers/" . $controller . ".php";

if (file_exists($route_controller)) {

    require_once $route_controller;

    $controller = new $controller;

    $action = isset($_REQUEST['a']) ? $_REQUEST['a'] : "main";

    if (!method_exists($controller, $action)) {
        $action = "main";
    }

    if ($controller instanceof Landing || $controller instanceof Login) {
        require_once "views/company/header.view.php";
        call_user_func([$controller, $action]);
        require_once "views/company/footer.view.php";
    } 
    elseif (!empty($_SESSION['session'])) {

        require_once "models/User.php";
        $profile = unserialize($_SESSION['profile']);

        call_user_func([$controller, $action]);
    }
}
?>