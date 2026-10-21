<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Auto-detect the base URL so the app works no matter what folder it's
// installed in (e.g. htdocs/rental-system or htdocs/campus-nest).
$__root    = str_replace('\\', '/', dirname(__DIR__));
$__docroot = str_replace('\\', '/', rtrim($_SERVER['DOCUMENT_ROOT'] ?? '', '/'));
define('BASE_URL', str_replace($__docroot, '', $__root));

define('SITE_NAME', 'CampusNest');
define('POSTING_FEE_AMOUNT', 300); // KES — fee a landlord pays admin to list a house
define('ADMIN_MPESA_TILL', '174379'); // demo till number students see for the posting-fee flow

function base_url($path = '') {
    return BASE_URL . '/' . ltrim($path, '/');
}

function redirect($path) {
    header('Location: ' . base_url($path));
    exit;
}

function sanitize($str) {
    return htmlspecialchars(trim($str ?? ''), ENT_QUOTES, 'UTF-8');
}

function is_logged_in() {
    return isset($_SESSION['user_id']);
}

function current_role() {
    return $_SESSION['role'] ?? null;
}

function require_login() {
    if (!is_logged_in()) {
        redirect('auth/login.php');
    }
}

function require_role($role) {
    require_login();
    if (current_role() !== $role) {
        redirect('index.php');
    }
    if (($role === 'landlord' || $role === 'student') && ($_SESSION['status'] ?? '') !== 'approved') {
        // let them through to a "pending approval" notice on their own dashboard
    }
}

function generate_code() {
    return strval(random_int(100000, 999999));
}

function flash($type, $message) {
    $_SESSION['flash'][] = ['type' => $type, 'message' => $message];
}

function get_flashes() {
    $flashes = $_SESSION['flash'] ?? [];
    unset($_SESSION['flash']);
    return $flashes;
}

function room_type_label($type) {
    $map = [
        'bedsitter'    => 'Bedsitter',
        'single'       => 'Single Room',
        'one_bedroom'  => 'One Bedroom',
        'two_bedroom'  => 'Two Bedroom',
        'hostel'       => 'Hostel Room',
    ];
    return $map[$type] ?? $type;
}

/**
 * Handles a single uploaded image (jpg/png/webp, <=5MB) and moves it into
 * assets/uploads/$subfolder. Returns the relative path to store in the DB,
 * or null if no file was uploaded / it failed validation.
 */
function handle_image_upload($fileInputName, $subfolder) {
    if (empty($_FILES[$fileInputName]['name']) || $_FILES[$fileInputName]['error'] === UPLOAD_ERR_NO_FILE) {
        return null;
    }
    $file = $_FILES[$fileInputName];
    if ($file['error'] !== UPLOAD_ERR_OK) return null;

    $allowed = ['jpg' => 'image/jpeg', 'jpeg' => 'image/jpeg', 'png' => 'image/png', 'webp' => 'image/webp'];
    $ext = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
    if (!isset($allowed[$ext]) || $file['size'] > 5 * 1024 * 1024) return null;

    $destDir = __DIR__ . '/../assets/uploads/' . $subfolder;
    if (!is_dir($destDir)) mkdir($destDir, 0755, true);

    $filename = uniqid($subfolder . '_', true) . '.' . $ext;
    if (!move_uploaded_file($file['tmp_name'], $destDir . '/' . $filename)) return null;

    return 'assets/uploads/' . $subfolder . '/' . $filename;
}

function star_rating($rating) {
    $rating = round($rating);
    return str_repeat('★', $rating) . str_repeat('☆', 5 - $rating);
}
