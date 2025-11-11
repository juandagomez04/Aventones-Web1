<?php
session_start();
require_once '../../Application/Services/Auth/login_user.php';
require_once '../../Application/Services/Auth/manageprofile.php';

/* ================================
   Helpers (declarados una sola vez)
   ================================ */
if (!function_exists('normalizeDateForInput')) {
    function normalizeDateForInput($v)
    {
        if (!$v)
            return '';
        $v = trim((string) $v);
        if ($v === '0000-00-00' || $v === '0000-00-00 00:00:00')
            return '';
        $ts = @strtotime($v);
        if ($ts === false)
            return '';
        return date('Y-m-d', $ts);
    }
}

/**
 * Recibe el path guardado en BD (ej. public/assets/img/users/archivo.png),
 * valida en el filesystem, y devuelve la URL para el navegador.
 */
if (!function_exists('urlFromDbPhotoPath')) {
    function urlFromDbPhotoPath(?string $dbPath, string $fallback = 'public/assets/img/avatar.png'): string
    {
        // Si no hay path en BD o está vacío, usar avatar por defecto
        if (empty($dbPath) || trim($dbPath) === '') {
            return '../../../' . ltrim($fallback, '/');
        }

        $rel = str_replace('\\', '/', $dbPath);
        $rel = ltrim($rel, '/');

        // Resuelve ruta absoluta en disco del proyecto
        $baseAbs = realpath(__DIR__ . '/../../../') ?: (__DIR__ . '/../../../');
        $abs = $baseAbs . '/' . $rel;

        // Si el archivo existe, usar esa imagen
        if (is_file($abs)) {
            return '../../../' . $rel;
        }

        // Si no existe, usar el avatar por defecto
        return '../../../' . ltrim($fallback, '/');
    }
}

/* ================================
   Logout y validación de sesión
   ================================ */
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['logout'])) {
    LoginUser::logout();
    header('Location: ../auth/login.php');
    exit;
}

if (!LoginUser::isLoggedIn()) {
    header('Location: ../auth/login.php');
    exit;
}

$userId = (int) $_SESSION['user_id'];
$userData = [];
$flash = null;

/* ================================
   Cargar perfil actual
   ================================ */
try {
    $userData = ManageProfile::getUserProfile($userId);
} catch (Throwable $e) {
    $flash = ['danger', 'Error al cargar el perfil: ' . htmlspecialchars($e->getMessage())];
}

$birthForInput = normalizeDateForInput($userData['birth_date'] ?? '');
$defaultAvatarRel = 'public/assets/img/avatar.png';
$currentPhotoRel = !empty($userData['photo_path']) ? $userData['photo_path'] : $defaultAvatarRel;
$currentPhotoUrl = urlFromDbPhotoPath($currentPhotoRel, $defaultAvatarRel);

/* ================================
   Manejo de POST (perfil / contraseña)
   ================================ */
if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    // Guardar cambios del perfil
    // En la sección de manejo de POST en editprofile.php
    if (isset($_POST['update_profile'])) {
        try {
            $updateData = [
                'first_name' => trim($_POST['first_name'] ?? ''),
                'last_name' => trim($_POST['last_name'] ?? ''),
                'national_id' => trim($_POST['national_id'] ?? ''),
                'birth_date' => trim($_POST['birth_date'] ?? ''),
                'email' => trim($_POST['email'] ?? ''),
                'phone' => trim($_POST['phone'] ?? ''),
            ];

            // Pasa el archivo tal cual (o null). La clase se encarga de validar, guardar y borrar la foto anterior.
            $photoFile = $_FILES['photo'] ?? null;

            // Actualiza en BD (y procesa foto si existe)
            $saved = ManageProfile::updateUserProfile($userId, $updateData, $photoFile);
            if (!$saved) {
                throw new RuntimeException('No se pudo actualizar el perfil en la base de datos.');
            }

            // Recargar datos para reflejar cambios
            $userData = ManageProfile::getUserProfile($userId);
            $birthForInput = normalizeDateForInput($userData['birth_date'] ?? '');
            $currentPhotoRel = !empty($userData['photo_path']) ? $userData['photo_path'] : $defaultAvatarRel;
            $currentPhotoUrl = urlFromDbPhotoPath($currentPhotoRel, $defaultAvatarRel);

            $flash = ['success', 'Perfil actualizado correctamente.'];
        } catch (Throwable $e) {
            $flash = ['danger', 'No se pudo actualizar el perfil: ' . htmlspecialchars($e->getMessage())];
        }
    }


    // Cambiar contraseña
    if (isset($_POST['update_password'])) {
        $current = $_POST['current_password'] ?? '';
        $new = $_POST['new_password'] ?? '';
        $confirm = $_POST['confirm_password'] ?? '';

        try {
            if ($new === '' || $confirm === '') {
                throw new RuntimeException('La nueva contraseña no puede estar vacía.');
            }
            if ($new !== $confirm) {
                throw new RuntimeException('La confirmación no coincide.');
            }
            if (strlen($new) < 8) {
                throw new RuntimeException('La nueva contraseña debe tener al menos 8 caracteres.');
            }

            ManageProfile::updatePassword($userId, $current, $new);
            $flash = ['success', 'Contraseña actualizada correctamente.'];
        } catch (Throwable $e) {
            $flash = ['danger', 'No se pudo actualizar la contraseña: ' . htmlspecialchars($e->getMessage())];
        }
    }

    // Función temporal para debugging - elimínala después de resolver el problema
    function debugPhotoUpload()
    {
        error_log("=== DEBUG PHOTO UPLOAD ===");
        error_log("FILES array: " . print_r($_FILES, true));
        error_log("POST array: " . print_r($_POST, true));

        if (isset($_FILES['photo'])) {
            $photo = $_FILES['photo'];
            error_log("Photo name: " . $photo['name']);
            error_log("Photo tmp_name: " . $photo['tmp_name']);
            error_log("Photo error: " . $photo['error']);
            error_log("Photo size: " . $photo['size']);
            error_log("File exists: " . (file_exists($photo['tmp_name']) ? 'YES' : 'NO'));
        }
    }
}
?>

<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <title>Editar Perfil - AVENTONES</title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="../../../public/assets/css/base.css">
    <link rel="stylesheet" href="../../../public/assets/css/profile.css">
</head>

<body>
    <!-- Header -->
    <div class="header">
        <img src="../../../public/assets/img/Icono.png" alt="Logo" class="logo">
        <h1 class="title">AVENTONES</h1>
    </div>

    <!-- Menu -->
    <div class="menu-container">
        <div class="menu">
            <nav class="left-menu">
                <a href="../rides/searchrides.php">Home</a>
                <a href="../rides/search_public_rides.php">Search</a>
                <a href="../bookings/bookings.php">Bookings</a>
            </nav>
            <form method="POST" class="right-menu">
                <button class="btn btn-danger" name="logout" value="1">Logout</button>
            </form>
        </div>
    </div>

    <div class="container">
        <?php if ($flash): ?>
            <div class="alert <?= htmlspecialchars($flash[0]) ?>"><?= htmlspecialchars($flash[1]) ?></div>
        <?php endif; ?>

        <div class="grid-2">
            <!-- Perfil -->
            <form class="card" method="POST" enctype="multipart/form-data">
                <h2>Datos del perfil</h2>
                <div style="display:flex; gap:16px; align-items:center; margin-bottom: 10px;">
                    <img id="avatarPreview" class="avatar" src="<?= htmlspecialchars($currentPhotoUrl) ?>"
                        onerror="this.onerror=null;this.src='../../../public/assets/img/avatar.png';" alt="Avatar">
                    <div>
                        <div class="field">
                            <label for="photo">Foto</label>
                            <input type="file" id="photo" name="photo" accept=".jpg,.jpeg,.png,.webp">
                        </div>
                        <small>Formatos: JPG, PNG o WEBP. Se guarda en <code>public/assets/img/users/</code></small>
                    </div>
                </div>

                <div class="form-row">
                    <div class="field">
                        <label for="first_name">Nombre</label>
                        <input type="text" id="first_name" name="first_name" required
                            value="<?= htmlspecialchars($userData['first_name'] ?? '') ?>">
                    </div>
                    <div class="field">
                        <label for="last_name">Apellido</label>
                        <input type="text" id="last_name" name="last_name" required
                            value="<?= htmlspecialchars($userData['last_name'] ?? '') ?>">
                    </div>

                    <div class="field">
                        <label for="national_id">Cédula</label>
                        <input type="text" id="national_id" name="national_id"
                            value="<?= htmlspecialchars($userData['national_id'] ?? '') ?>">
                    </div>
                    <div class="field">
                        <label for="birth_date">Fecha de nacimiento</label>
                        <input type="date" id="birth_date" name="birth_date"
                            value="<?= htmlspecialchars($birthForInput) ?>">
                    </div>

                    <div class="field">
                        <label for="email">Correo</label>
                        <input type="email" id="email" name="email" required
                            value="<?= htmlspecialchars($userData['email'] ?? '') ?>">
                    </div>
                    <div class="field">
                        <label for="phone">Teléfono</label>
                        <input type="text" id="phone" name="phone"
                            value="<?= htmlspecialchars($userData['phone'] ?? '') ?>">
                    </div>

                    <div class="field full actions">
                        <button type="reset" class="btn btn-secondary">Restablecer</button>
                        <button type="submit" class="btn btn-primary" name="update_profile" value="1">Guardar
                            cambios</button>
                    </div>
                </div>
            </form>

            <!-- Contraseña -->
            <form class="card" method="POST">
                <h2>Actualizar contraseña</h2>
                <div class="field">
                    <label for="current_password">Contraseña actual</label>
                    <input type="password" id="current_password" name="current_password" required>
                </div>
                <div class="field">
                    <label for="new_password">Nueva contraseña</label>
                    <input type="password" id="new_password" name="new_password" required>
                </div>
                <div class="field">
                    <label for="confirm_password">Confirmar nueva contraseña</label>
                    <input type="password" id="confirm_password" name="confirm_password" required>
                </div>
                <div class="actions">
                    <button type="submit" class="btn btn-primary" name="update_password" value="1">Cambiar
                        contraseña</button>
                </div>
            </form>
        </div>
    </div>

    <footer class="footer">
        <p>© <?= date('Y') ?> AVENTONES</p>
    </footer>

    <script>
        // Preview inmediata de foto nueva
        document.getElementById('photo')?.addEventListener('change', (e) => {
            const [file] = e.target.files || [];
            if (!file) return;
            const url = URL.createObjectURL(file);
            document.getElementById('avatarPreview').src = url;
        });
    </script>
</body>

</html>