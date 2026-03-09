<?php
class Dashboard {
    private $role_config;
    
    public function __construct() {
        // Configuración centralizada de roles y vistas
        $this->role_config = [
            'admin' => [
                'view' => 'admin/dashboard.php',
                'allowed' => true,
                'redirect' => null
            ],
            'user' => [
                'view' => 'user/profile.php',
                'allowed' => true,
                'redirect' => null
            ],
            'guest' => [
                'view' => 'guest/welcome.php',
                'allowed' => true,
                'redirect' => '?c=Login&a=main'
            ],
            'supervisor' => [
                'view' => 'supervisor/panel.php',
                'allowed' => true,
                'redirect' => null
            ]
        ];
    }
    
    public function main() {
        try {
            // Validar sesión
            if (!isset($_SESSION['session'])) {
                $this->redirectToLogin();
                return;
            }
            
            $role = $_SESSION['session'];
            
            // Validar rol contra configuración
            if (!isset($this->role_config[$role])) {
                $this->showError("Rol no autorizado");
                return;
            }
            
            $config = $this->role_config[$role];
            
            // Verificar si el rol tiene acceso
            if (!$config['allowed']) {
                $this->redirectToLogin();
                return;
            }
            
            // Redirigir si es necesario
            if ($config['redirect']) {
                header("Location: " . $config['redirect']);
                exit;
            }
            
            // Construir ruta segura
            $base_path = realpath(__DIR__ . "/../views/roles/");
            $view_file = $config['view'];
            
            // Prevenir path traversal
            $view_file = str_replace(['..', './', '\\'], '', $view_file);
            
            $full_path = $base_path . DIRECTORY_SEPARATOR . $view_file;
            $real_path = realpath($full_path);
            
            // Verificar que el archivo existe y está dentro del directorio permitido
            if ($real_path && strpos($real_path, $base_path) === 0 && file_exists($real_path)) {
                require_once $real_path;
            } else {
                $this->showError("Vista no encontrada");
            }
            
        } catch (Exception $e) {
            error_log("Dashboard Error: " . $e->getMessage());
            $this->showError("Error interno");
        }
    }
    
    private function redirectToLogin() {
        header("Location: ?c=Login&a=main");
        exit;
    }
    
    private function showError($message) {
        $error_file = realpath(__DIR__ . "/../views/error/404.view.php");
        if ($error_file && file_exists($error_file)) {
            require_once $error_file;
        } else {
            echo "<h1>Error: $message</h1>";
        }
    }
}
?>