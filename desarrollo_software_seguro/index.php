<?php
ob_start();
session_start();
// session_destroy();

require_once "models/DataBase.php";

/* Controladores permitidos */
$allowedControllers = ["Landing", "Login", "User", "Product"];

/* Obtener controlador */
$controller = isset($_GET['c']) ? $_GET['c'] : "Landing";

/* Validar controlador */
if (!in_array($controller, $allowedControllers)) {
    $controller = "Landing";
}

$route_controller = "controllers/" . $controller . ".php";

if (file_exists($route_controller)) {

    $view = $controller;

    require_once $route_controller;

    $controller = new $controller();

    /* Acción */
    $action = isset($_GET['a']) ? $_GET['a'] : "main";

    if (!method_exists($controller, $action)) {
        $action = "main";
    }

    if ($view === "Landing" || $view === "Login") {

        require_once "views/company/header.view.php";
        call_user_func([$controller, $action]);
        require_once "views/company/footer.view.php");

    } elseif (!empty($_SESSION['session'])) {

        require_once "models/User.php";

        $profile = unserialize($_SESSION['profile']);
        $session = $_SESSION['session'];

        require_once "views/roles/".$session."/header.view.php";

        call_user_func([$controller, $action]);

        require_once "views/roles/".$session."/footer.view.php";

    } else {

        header("Location: index.php?c=Login");
        exit;

    }

} else {

    header("Location: index.php?c=Landing");
    exit;

}

ob_end_flush();
?>