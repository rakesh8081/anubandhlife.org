<?php
// auth.php — ties participant IDs to the browser session that created them,
// so reports can't be fetched by guessing sequential IDs.

function mindlab_session_start() {
    if (session_status() === PHP_SESSION_NONE) {
        $lifetime = 86400; // 24 hours: long enough to finish a test and download the report
        ini_set('session.gc_maxlifetime', (string)$lifetime);
        session_name('MINDLAB');
        session_set_cookie_params([
            'lifetime' => $lifetime,
            'path'     => '/',
            'secure'   => !empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off',
            'httponly' => true,
            'samesite' => 'Lax',
        ]);
        session_start();
    }
}

function mindlab_remember_participant($id) {
    mindlab_session_start();
    $ids = $_SESSION['mindlab_participants'] ?? [];
    $ids[] = (int)$id;
    $_SESSION['mindlab_participants'] = array_values(array_unique($ids));
}

function mindlab_owned_participants() {
    mindlab_session_start();
    return $_SESSION['mindlab_participants'] ?? [];
}

function mindlab_owns_participant($id) {
    mindlab_session_start();
    return in_array((int)$id, $_SESSION['mindlab_participants'] ?? [], true);
}

// Optional researcher access: define MINDLAB_ADMIN_KEY in db_config.php (server only)
// and add &key=... to an export_pdf.php URL to open any participant's report.
function mindlab_is_admin($key) {
    return defined('MINDLAB_ADMIN_KEY') && MINDLAB_ADMIN_KEY !== ''
        && is_string($key) && hash_equals(MINDLAB_ADMIN_KEY, $key);
}
