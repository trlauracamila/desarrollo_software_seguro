<?php
ob_start();
session_start();
require_once "models/DataBase.php";

// Lista blanca de controladores permitidos
$allowed_controllers = [
    'Landing', 
    'Login', 
    'Home', 
    'User', 
    'Product'
    // Agrega aquí todos los controladores válidos de tu aplicación
];

// Validar el controlador
$controller = isset($_REQUEST['c']) ? $_REQUEST['c'] : "Landing";

// Verificar que el controlador está en la lista blanca
if (!in_array($controller, $allowed_controllers)) {
    // Si no es válido, redirigir o usar un controlador por defecto seguro
    header("Location:?");
    ob_end_flush();
    exit;
}

$route_controller = "controllers/" . $controller . ".php";

if (file_exists($route_controller)) {
    $view = $controller;
    require_once $route_controller;

    $controller = new $controller;
    
    // Validar la acción (también deberías tener una lista blanca)
    $action = isset($_REQUEST['a']) ? $_REQUEST['a'] : 'main';
    
    // Lista blanca de acciones permitidas para este controlador
    // Esto es un ejemplo - deberías adaptarlo según tu estructura
    $allowed_actions = ['main', 'create', 'edit', 'delete']; 
    
    if (!in_array($action, $allowed_actions)) {
        $action = 'main'; // Acción por defecto si no es válida
    }

    if ($view === 'Landing' || $view === 'Login') {
        require_once "views/company/header.view.php";
        if (is_callable(array($controller, $action))) {
            call_user_func(array($controller, $action));
        }
        require_once "views/company/footer.view.php";
    } elseif (!empty($_SESSION['session'])) {
        require_once "models/User.php";
        $profile = unserialize($_SESSION['profile']);
        $session = $_SESSION['session'];
        
        // Validar el rol de sesión
        $allowed_roles = ['admin', 'user', 'guest']; // Roles permitidos
        if (in_array($session, $allowed_roles)) {
            require_once "views/roles/".$session."/header.view.php";
            if (is_callable(array($controller, $action))) {
                call_user_func(array($controller, $action));
            }
            require_once "views/roles/".$session."/footer.view.php";
        } else {
            header("Location:?");
        }
    } else {
        header("Location:?");
    }
} else {
    header("Location:?");
}
ob_end_flush();
?>