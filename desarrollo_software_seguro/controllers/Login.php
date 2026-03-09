<?php
require_once "models/User.php";

class Login {
    
    // Definir constante para la ruta de login
    private const LOGIN_VIEW = "views/company/login.view.php";
    
    /**
     * Controlador Principal
     */
    public function main() {
        // Manejar petición GET
        if ($_SERVER['REQUEST_METHOD'] == 'GET') {
            $this->handleGetRequest();
        }
        
        // Manejar petición POST
        if ($_SERVER['REQUEST_METHOD'] == 'POST') {
            $this->handlePostRequest();
        }
    }
    
    /**
     * Maneja las peticiones GET
     */
    private function handleGetRequest() {
        if (empty($_SESSION['session'])) {
            $message = "";
            require_once self::LOGIN_VIEW;
        } else {
            header("Location: ?c=Dashboard");
            exit;
        }
    }
    
    /**
     * Maneja las peticiones POST (login)
     */
    private function handlePostRequest() {
        try {
            // Validar que los campos POST existen
            if (!isset($_POST['user_email']) || !isset($_POST['user_pass'])) {
                throw new Exception("Campos de login incompletos");
            }
            
            // Sanitizar entradas
            $email = filter_var($_POST['user_email'], FILTER_SANITIZE_EMAIL);
            $password = $_POST['user_pass']; // La contraseña no se sanitiza, se usa directo para hash
            
            // Validar email
            if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
                $message = "Formato de email inválido";
                require_once self::LOGIN_VIEW;
                return;
            }
            
            // Crear instancia del usuario
            $profile = new User($email, $password);
            $profile = $profile->login();
            
            if ($profile) {
                $this->handleSuccessfulLogin($profile);
            } else {
                $this->handleFailedLogin("Credenciales incorrectas o el usuario no existe");
            }
            
        } catch (Exception $e) {
            error_log("Error en login: " . $e->getMessage());
            $this->handleFailedLogin("Error en el proceso de login");
        }
    }
    
    /**
     * Maneja un login exitoso
     */
    private function handleSuccessfulLogin($profile) {
        $active = $profile->getUserState();
        
        if ($active != 0) {
            $_SESSION['session'] = $profile->getRolName();
            $_SESSION['profile'] = serialize($profile);
            $_SESSION['user_id'] = $profile->getId(); // Asumiendo que existe método getId()
            $_SESSION['login_time'] = time();
            
            // Redirigir al dashboard
            header("Location: ?c=Dashboard");
            exit;
        } else {
            $this->handleFailedLogin("El usuario no está activo");
        }
    }
    
    /**
     * Maneja un login fallido
     */
    private function handleFailedLogin($message) {
        $message = $message;
        require_once self::LOGIN_VIEW;
    }
}
?>