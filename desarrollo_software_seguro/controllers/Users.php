<?php
require_once "models/User.php";

class Users {
    
    // Constantes para redirecciones
    private const REDIRECT_DASHBOARD = "Location: ?c=Dashboard";
    private const REDIRECT_USER_READ = "Location: ?c=Users&a=userRead";
    private const REDIRECT_ROL_READ = "Location: ?c=Users&a=rolRead";
    
    // Constantes para rutas de vistas
    private const VIEW_ROL_CREATE = "views/modules/users/rol_create.view.php";
    private const VIEW_ROL_READ = "views/modules/users/rol_read.view.php";
    private const VIEW_ROL_UPDATE = "views/modules/users/rol_update.view.php";
    private const VIEW_USER_CREATE = "views/modules/users/user_create.view.php";
    private const VIEW_USER_READ = "views/modules/users/user_read.view.php";
    private const VIEW_USER_UPDATE = "views/modules/users/user_update.view.php";
    
    // Roles permitidos
    private const ROLES_ADMIN_ONLY = ['admin'];
    private const ROLES_ADMIN_SELLER = ['admin', 'seller'];
    
    private $session;
    
    public function __construct() {
        // Validar que existe la sesión
        if (!isset($_SESSION['session'])) {
            header(self::REDIRECT_DASHBOARD);
            exit;
        }
        $this->session = $_SESSION['session'];
    }
    
    /**
     * Controlador Principal
     */
    public function main() {
        header(self::REDIRECT_DASHBOARD);
        exit;
    }
    
    /**
     * Verifica si el usuario tiene permiso para acceder
     */
    private function checkPermission(array $allowedRoles): bool {
        return in_array($this->session, $allowedRoles, true);
    }
    
    /**
     * Redirecciona si no tiene permiso
     */
    private function redirectIfNoPermission(array $allowedRoles): void {
        if (!$this->checkPermission($allowedRoles)) {
            header(self::REDIRECT_DASHBOARD);
            exit;
        }
    }
    
    // ============================================
    // CRUD DE ROLES (Solo Admin)
    // ============================================
    
    /**
     * Controlador Crear Rol
     */
    public function rolCreate() {
        $this->redirectIfNoPermission(self::ROLES_ADMIN_ONLY);
        
        if ($_SERVER['REQUEST_METHOD'] == 'GET') {
            require_once self::VIEW_ROL_CREATE;
        }
        
        if ($_SERVER['REQUEST_METHOD'] == 'POST') {
            $this->processRolCreate();
        }
    }
    
    /**
     * Procesa la creación de un rol
     */
    private function processRolCreate() {
        try {
            // Validar entrada
            if (empty($_POST['rol_name'])) {
                throw new Exception("El nombre del rol es requerido");
            }
            
            $rol = new User();
            $rol->setRolName($this->sanitizeInput($_POST['rol_name']));
            $rol->create_rol();
            
            header(self::REDIRECT_ROL_READ);
            exit;
            
        } catch (Exception $e) {
            error_log("Error en rolCreate: " . $e->getMessage());
            $error = "Error al crear el rol";
            require_once self::VIEW_ROL_CREATE;
        }
    }
    
    /**
     * Controlador Consultar Roles
     */
    public function rolRead() {
        $this->redirectIfNoPermission(self::ROLES_ADMIN_ONLY);
        
        try {
            $roles = new User();
            $roles = $roles->read_roles();
            require_once self::VIEW_ROL_READ;
            
        } catch (Exception $e) {
            error_log("Error en rolRead: " . $e->getMessage());
            header(self::REDIRECT_DASHBOARD);
            exit;
        }
    }
    
    /**
     * Controlador Actualizar Rol
     */
    public function rolUpdate() {
        $this->redirectIfNoPermission(self::ROLES_ADMIN_ONLY);
        
        if ($_SERVER['REQUEST_METHOD'] == 'GET') {
            $this->showRolUpdateForm();
        }
        
        if ($_SERVER['REQUEST_METHOD'] == 'POST') {
            $this->processRolUpdate();
        }
    }
    
    /**
     * Muestra formulario de actualización de rol
     */
    private function showRolUpdateForm() {
        try {
            if (empty($_GET['idRol'])) {
                throw new Exception("ID de rol no proporcionado");
            }
            
            $idRol = $this->sanitizeInput($_GET['idRol']);
            $rolId = new User();
            $rolId = $rolId->getrol_bycode($idRol);
            
            if (!$rolId) {
                throw new Exception("Rol no encontrado");
            }
            
            require_once self::VIEW_ROL_UPDATE;
            
        } catch (Exception $e) {
            error_log("Error en showRolUpdateForm: " . $e->getMessage());
            header(self::REDIRECT_ROL_READ);
            exit;
        }
    }
    
    /**
     * Procesa la actualización de un rol
     */
    private function processRolUpdate() {
        try {
            // Validar entradas
            if (empty($_POST['rol_code']) || empty($_POST['rol_name'])) {
                throw new Exception("Todos los campos son requeridos");
            }
            
            $rolUpdate = new User();
            $rolUpdate->setRolCode($this->sanitizeInput($_POST['rol_code']));
            $rolUpdate->setRolName($this->sanitizeInput($_POST['rol_name']));
            $rolUpdate->update_rol();
            
            header(self::REDIRECT_ROL_READ);
            exit;
            
        } catch (Exception $e) {
            error_log("Error en processRolUpdate: " . $e->getMessage());
            $error = "Error al actualizar el rol";
            require_once self::VIEW_ROL_UPDATE;
        }
    }
    
    /**
     * Controlador Eliminar Rol
     */
    public function rolDelete() {
        $this->redirectIfNoPermission(self::ROLES_ADMIN_ONLY);
        
        try {
            if (empty($_GET['idRol'])) {
                throw new Exception("ID de rol no proporcionado");
            }
            
            $rol = new User();
            $rol->delete_rol($this->sanitizeInput($_GET['idRol']));
            
            header(self::REDIRECT_ROL_READ);
            exit;
            
        } catch (Exception $e) {
            error_log("Error en rolDelete: " . $e->getMessage());
            header(self::REDIRECT_ROL_READ);
            exit;
        }
    }
    
    // ============================================
    // CRUD DE USUARIOS (Admin y Seller)
    // ============================================
    
    /**
     * Controlador Crear Usuario
     */
    public function userCreate() {
        $this->redirectIfNoPermission(self::ROLES_ADMIN_SELLER);
        
        if ($_SERVER['REQUEST_METHOD'] == 'GET') {
            $this->showUserCreateForm();
        }
        
        if ($_SERVER['REQUEST_METHOD'] == 'POST') {
            $this->processUserCreate();
        }
    }
    
    /**
     * Muestra formulario de creación de usuario
     */
    private function showUserCreateForm() {
        try {
            $roles = new User();
            $roles = $roles->read_roles();
            require_once self::VIEW_USER_CREATE;
            
        } catch (Exception $e) {
            error_log("Error en showUserCreateForm: " . $e->getMessage());
            header(self::REDIRECT_USER_READ);
            exit;
        }
    }
    
    /**
     * Procesa la creación de un usuario
     */
    private function processUserCreate() {
        try {
            // Validar campos requeridos
            $required = ['rol_code', 'user_name', 'user_lastname', 'user_id', 'user_email', 'user_pass', 'user_state'];
            foreach ($required as $field) {
                if (empty($_POST[$field])) {
                    throw new Exception("El campo $field es requerido");
                }
            }
            
            // Validar email
            if (!filter_var($_POST['user_email'], FILTER_VALIDATE_EMAIL)) {
                throw new Exception("Email inválido");
            }
            
            // Sanitizar entradas
            $userData = [
                'rol_code' => $this->sanitizeInput($_POST['rol_code']),
                'user_name' => $this->sanitizeInput($_POST['user_name']),
                'user_lastname' => $this->sanitizeInput($_POST['user_lastname']),
                'user_id' => $this->sanitizeInput($_POST['user_id']),
                'user_email' => filter_var($_POST['user_email'], FILTER_SANITIZE_EMAIL),
                'user_pass' => $_POST['user_pass'], // Se hasheará en el modelo
                'user_state' => $this->sanitizeInput($_POST['user_state'])
            ];
            
            $user = new User(
                $userData['rol_code'],
                null,
                $userData['user_name'],
                $userData['user_lastname'],
                $userData['user_id'],
                $userData['user_email'],
                $userData['user_pass'],
                $userData['user_state']
            );
            
            $user->create_user();
            
            header(self::REDIRECT_USER_READ);
            exit;
            
        } catch (Exception $e) {
            error_log("Error en processUserCreate: " . $e->getMessage());
            $error = "Error al crear usuario: " . $e->getMessage();
            
            // Recargar roles para el formulario
            $roles = new User();
            $roles = $roles->read_roles();
            require_once self::VIEW_USER_CREATE;
        }
    }
    
    /**
     * Controlador Consultar Usuarios
     */
    public function userRead() {
        $this->redirectIfNoPermission(self::ROLES_ADMIN_SELLER);
        
        try {
            $users = new User();
            $users = $users->read_users(); // Asumiendo que existe este método
            require_once self::VIEW_USER_READ;
            
        } catch (Exception $e) {
            error_log("Error en userRead: " . $e->getMessage());
            header(self::REDIRECT_DASHBOARD);
            exit;
        }
    }
    
    /**
     * Controlador Actualizar Usuario
     */
    public function userUpdate() {
        $this->redirectIfNoPermission(self::ROLES_ADMIN_SELLER);
        
        if ($_SERVER['REQUEST_METHOD'] == 'GET') {
            $this->showUserUpdateForm();
        }
        
        if ($_SERVER['REQUEST_METHOD'] == 'POST') {
            $this->processUserUpdate();
        }
    }
    
    /**
     * Muestra formulario de actualización de usuario
     */
    private function showUserUpdateForm() {
        try {
            if (empty($_GET['idUser'])) {
                throw new Exception("ID de usuario no proporcionado");
            }
            
            $idUser = $this->sanitizeInput($_GET['idUser']);
            $roles = new User();
            $roles = $roles->read_roles();
            
            $user = new User();
            $user = $user->getUserById($idUser); // Asumiendo que existe este método
            
            if (!$user) {
                throw new Exception("Usuario no encontrado");
            }
            
            require_once self::VIEW_USER_UPDATE;
            
        } catch (Exception $e) {
            error_log("Error en showUserUpdateForm: " . $e->getMessage());
            header(self::REDIRECT_USER_READ);
            exit;
        }
    }
    
    /**
     * Procesa la actualización de un usuario
     */
    private function processUserUpdate() {
        try {
            // Validar campos requeridos
            $required = ['user_code', 'rol_code', 'user_name', 'user_lastname', 'user_id', 'user_email', 'user_state'];
            foreach ($required as $field) {
                if (empty($_POST[$field])) {
                    throw new Exception("El campo $field es requerido");
                }
            }
            
            // Validar email
            if (!filter_var($_POST['user_email'], FILTER_VALIDATE_EMAIL)) {
                throw new Exception("Email inválido");
            }
            
            // Sanitizar entradas
            $userData = [
                'user_code' => $this->sanitizeInput($_POST['user_code']),
                'rol_code' => $this->sanitizeInput($_POST['rol_code']),
                'user_name' => $this->sanitizeInput($_POST['user_name']),
                'user_lastname' => $this->sanitizeInput($_POST['user_lastname']),
                'user_id' => $this->sanitizeInput($_POST['user_id']),
                'user_email' => filter_var($_POST['user_email'], FILTER_SANITIZE_EMAIL),
                'user_pass' => isset($_POST['user_pass']) ? $_POST['user_pass'] : null,
                'user_state' => $this->sanitizeInput($_POST['user_state'])
            ];
            
            $userUpdate = new User(
                $userData['rol_code'],
                $userData['user_code'],
                $userData['user_name'],
                $userData['user_lastname'],
                $userData['user_id'],
                $userData['user_email'],
                $userData['user_pass'],
                $userData['user_state']
            );
            
            $userUpdate->update_user();
            
            header(self::REDIRECT_USER_READ);
            exit;
            
        } catch (Exception $e) {
            error_log("Error en processUserUpdate: " . $e->getMessage());
            $error = "Error al actualizar usuario: " . $e->getMessage();
            
            // Recargar datos para el formulario
            $roles = new User();
            $roles = $roles->read_roles();
            require_once self::VIEW_USER_UPDATE;
        }
    }
    
    /**
     * Controlador Eliminar Usuario
     */
    public function userDelete() {
        $this->redirectIfNoPermission(self::ROLES_ADMIN_ONLY); // Solo admin puede eliminar
        
        try {
            if (empty($_GET['idUser'])) {
                throw new Exception("ID de usuario no proporcionado");
            }
            
            $user = new User();
            $user->delete_user($this->sanitizeInput($_GET['idUser']));
            
            header(self::REDIRECT_USER_READ);
            exit;
            
        } catch (Exception $e) {
            error_log("Error en userDelete: " . $e->getMessage());
            header(self::REDIRECT_USER_READ);
            exit;
        }
    }
    
    /**
     * Sanitiza entradas de texto
     */
    private function sanitizeInput($input) {
        if ($input === null) {
            return '';
        }
        return htmlspecialchars(trim($input), ENT_QUOTES, 'UTF-8');
    }
}
?>