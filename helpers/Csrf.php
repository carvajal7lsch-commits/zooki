<?php
/**
 * CSRF Token Helper
 * Genera y valida tokens CSRF usando sesión de PHP.
 */
class Csrf {
    /**
     * Genera o recupera un token CSRF para un formulario.
     */
    public static function token(string $form = 'default'): string {
        if (session_status() === PHP_SESSION_NONE) session_start();
        $key = 'csrf_' . $form;
        if (empty($_SESSION[$key])) {
            $_SESSION[$key] = bin2hex(random_bytes(32));
        }
        return $_SESSION[$key];
    }

    /**
     * Imprime un input hidden con el token.
     */
    public static function field(string $form = 'default'): void {
        $token = self::token($form);
        echo '<input type="hidden" name="csrf_token" value="' . $token . '">' . PHP_EOL;
    }

    /**
     * Valida el token enviado por POST.
     */
    public static function validate(string $form = 'default'): bool {
        if (session_status() === PHP_SESSION_NONE) session_start();
        $key = 'csrf_' . $form;
        $token = $_POST['csrf_token'] ?? '';
        return !empty($token) && hash_equals($_SESSION[$key] ?? '', $token);
    }

    /**
     * Invalida (regenera) un token, útil tras submit exitoso.
     */
    public static function regenerate(string $form = 'default'): void {
        if (session_status() === PHP_SESSION_NONE) session_start();
        $_SESSION['csrf_' . $form] = bin2hex(random_bytes(32));
    }
}
