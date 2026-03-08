<?php
class DataBase {
    private static $connection = null;
    
    /**
     * Establece la conexión con la base de datos
     * @return PDO
     */
    public static function connection(): PDO {
        if (self::$connection === null) {
            try {
                // Configuración de la conexión
                $hostname = "localhost";
                $port = "3308";
                $database = "db_tps_nc_iv_2771440";
                $username = "root";
                $password = "";
                
                // Crear conexión PDO con opciones de seguridad
                $pdo = new PDO(
                    "mysql:host=$hostname;port=$port;dbname=$database;charset=utf8mb4",
                    $username,
                    $password,
                    [
                        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
                        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                        PDO::ATTR_EMULATE_PREPARES => false
                    ]
                );
                
                self::$connection = $pdo;
                
            } catch (PDOException $e) {
                // Registrar error sin exponer detalles
                error_log("Error de base de datos: " . $e->getMessage());
                throw new PDOException("Error al conectar con la base de datos");
            }
        }
        
        return self::$connection;
    }
}
?>