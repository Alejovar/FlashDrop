<?php
// inc/auth.php — protección del panel admin.
require_once __DIR__ . '/helpers.php';

function af_admin_logged(): bool {
    af_session_start();
    return !empty($_SESSION['admin_id']);
}

function af_require_admin_page(): void {
    if (!af_admin_logged()) {
        header('Location: login.php');
        exit;
    }
}

function af_require_admin_api(): void {
    if (!af_admin_logged()) {
        af_json(['ok' => false, 'error' => 'No autorizado'], 401);
    }
}

function af_login_throttled(PDO $db): bool {
    $stmt = $db->prepare(
        'SELECT COUNT(*) c FROM login_attempts
         WHERE ip = ? AND attempted_at > (NOW() - INTERVAL ' . (int)ADMIN_ATTEMPT_WINDOW_MIN . ' MINUTE)'
    );
    $stmt->execute([af_client_ip_bin()]);
    return (int)$stmt->fetch()['c'] >= ADMIN_MAX_ATTEMPTS;
}

function af_register_failed_login(PDO $db): void {
    $db->prepare('INSERT INTO login_attempts (ip) VALUES (?)')->execute([af_client_ip_bin()]);
}
