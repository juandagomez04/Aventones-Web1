<?php
// login_user.php
// session_start(); // REMOVED - ya se llama en login.php
require_once __DIR__ . '/../../../Database/db_conexion.php'; // Ruta corregida

class LoginUser
{
    public static function authenticate($email, $password)
    {
        global $pdo;

        // Buscar usuario en la base de datos (incluyendo el estado)
        $sql = "SELECT id, first_name, last_name, email, password_hash, role, status 
                FROM users 
                WHERE email = :email";

        $stmt = $pdo->prepare($sql);
        $stmt->execute([':email' => $email]);
        $user = $stmt->fetch(PDO::FETCH_ASSOC);

        // DEBUG TEMPORAL - quitar después
        error_log("User found: " . ($user ? 'YES' : 'NO'));
        if ($user) {
            error_log("User status: " . $user['status']);
            error_log("Password provided: " . $password);
            error_log("Password hash in DB: " . $user['password_hash']);
            error_log("Password verify result: " . (password_verify($password, $user['password_hash']) ? 'TRUE' : 'FALSE'));
        }

        // Verificar si el usuario existe
        if (!$user) {
            return [
                'success' => false,
                'message' => 'Invalid email or password'
            ];
        }

        // Verificar el estado de la cuenta
        if ($user['status'] === 'pending') {
            return [
                'success' => false,
                'message' => 'Your account is pending approval. Please wait for activation.'
            ];
        }

        if ($user['status'] === 'inactive') {
            return [
                'success' => false,
                'message' => 'Your account is inactive. Please contact administrator.'
            ];
        }

        // Verificar contraseña solo si la cuenta está activa
        if ($user['status'] === 'active' && password_verify($password, $user['password_hash'])) {
            // Guardar información del usuario en sesión
            $_SESSION['user_id'] = $user['id'];
            $_SESSION['user_email'] = $user['email'];
            $_SESSION['user_role'] = $user['role'];
            $_SESSION['user_name'] = $user['first_name'] . ' ' . $user['last_name'];
            $_SESSION['user_status'] = $user['status'];

            return [
                'success' => true,
                'role' => $user['role'],
                'message' => 'Login successful'
            ];
        }

        // Si llegamos aquí, la contraseña es incorrecta
        return [
            'success' => false,
            'message' => 'Invalid email or password'
        ];
    }

    public static function logout()
    {
        // Destruir la sesión completamente
        $_SESSION = array();

        // Si se desea destruir la cookie de sesión también
        if (ini_get("session.use_cookies")) {
            $params = session_get_cookie_params();
            setcookie(
                session_name(),
                '',
                time() - 42000,
                $params["path"],
                $params["domain"],
                $params["secure"],
                $params["httponly"]
            );
        }

        session_destroy();

        return [
            'success' => true,
            'message' => 'Logged out successfully'
        ];
    }

    public static function isLoggedIn()
    {
        return isset($_SESSION['user_id']) && $_SESSION['user_status'] === 'active';
    }

    public static function isAdmin()
    {
        return isset($_SESSION['user_role']) && $_SESSION['user_role'] === 'admin' && self::isLoggedIn();
    }

    public static function isDriver()
    {
        return isset($_SESSION['user_role']) && $_SESSION['user_role'] === 'driver' && self::isLoggedIn();
    }

    public static function isPassenger()
    {
        return isset($_SESSION['user_role']) && $_SESSION['user_role'] === 'passenger' && self::isLoggedIn();
    }

    public static function requireAuth($allowedRoles = [])
    {
        if (!self::isLoggedIn()) {
            header('Location: ../auth/login.php');
            exit;
        }

        if (!empty($allowedRoles) && !in_array($_SESSION['user_role'], $allowedRoles)) {
            header('Location: ../auth/access_denied.php');
            exit;
        }

        return true;
    }

    public static function requireAdmin()
    {
        return self::requireAuth(['admin']);
    }

    public static function getCurrentUser()
    {
        if (self::isLoggedIn()) {
            return [
                'id' => $_SESSION['user_id'],
                'email' => $_SESSION['user_email'],
                'role' => $_SESSION['user_role'],
                'name' => $_SESSION['user_name'],
                'status' => $_SESSION['user_status']
            ];
        }
        return null;
    }

    // Función para verificar si un usuario tiene cuenta pendiente
    public static function isAccountPending($email)
    {
        global $pdo;

        $sql = "SELECT status FROM users WHERE email = :email";
        $stmt = $pdo->prepare($sql);
        $stmt->execute([':email' => $email]);
        $user = $stmt->fetch(PDO::FETCH_ASSOC);

        return $user && $user['status'] === 'pending';
    }

    // Función para verificar si un usuario tiene cuenta inactiva
    public static function isAccountInactive($email)
    {
        global $pdo;

        $sql = "SELECT status FROM users WHERE email = :email";
        $stmt = $pdo->prepare($sql);
        $stmt->execute([':email' => $email]);
        $user = $stmt->fetch(PDO::FETCH_ASSOC);

        return $user && $user['status'] === 'inactive';
    }
}