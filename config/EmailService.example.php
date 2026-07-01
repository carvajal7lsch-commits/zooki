<?php

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

require_once __DIR__ . '/../vendor/autoload.php';

class EmailService {
    private $mail;
    
    public function __construct() {
        $this->mail = new PHPMailer(true);

        // Configuración del servidor SMTP
        // NOTA: Debes configurar estos valores según tu proveedor de correo
        $this->mail->isSMTP();
        $this->mail->Host = 'smtp.gmail.com'; // Cambiar según tu proveedor
        $this->mail->SMTPAuth = true;
        $this->mail->Username = 'TU_CORREO@gmail.com'; // Cambiar por tu correo
        $this->mail->Password = 'TU_CONTRASEÑA_DE_APLICACION'; // Cambiar por tu contraseña o app password
        $this->mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
        $this->mail->Port = 587;

        // Configuración general
        $this->mail->CharSet = 'UTF-8';
        $this->mail->setFrom('TU_CORREO@gmail.com', 'Zooki - Sistema Veterinario');
    }
    
    public function enviarCredencialesUsuario($email, $nombre, $documento, $password) {
        try {
            $this->mail->addAddress($email, $nombre);
            $this->mail->Subject = 'Bienvenido a Zooki - Tus credenciales de acceso';

            $this->mail->Body = $this->generarPlantillaCredenciales($nombre, $documento, $password);
            $this->mail->AltBody = "Hola $nombre,\n\nTus credenciales de acceso a Zooki son:\n\nDocumento: $documento\nContraseña: $password\n\nPor seguridad, te recomendamos cambiar tu contraseña en tu primer inicio de sesión.\n\nSaludos,\nEquipo de Zooki";

            $this->mail->send();
            return true;
        } catch (Exception $e) {
            error_log("Error al enviar correo: " . $this->mail->ErrorInfo);
            return false;
        }
    }

    public function enviarCorreoPersonalizado($email, $nombre, $asunto, $cuerpoHTML) {
        try {
            $this->mail->addAddress($email, $nombre);
            $this->mail->Subject = $asunto;
            $this->mail->Body = $cuerpoHTML;
            $this->mail->isHTML(true);
            $this->mail->send();
            return true;
        } catch (Exception $e) {
            error_log("Error al enviar correo: " . $this->mail->ErrorInfo);
            return false;
        }
    }

    public function limpiarDirecciones() {
        $this->mail->clearAddresses();
    }
    
    private function generarPlantillaCredenciales($nombre, $documento, $password) {
        return "
        <html>
        <head>
            <style>
                body { font-family: Arial, sans-serif; line-height: 1.6; color: #333; }
                .container { max-width: 600px; margin: 0 auto; padding: 20px; }
                .header { background: linear-gradient(135deg, #0052FF 0%, #003bbb 100%); color: white; padding: 30px; text-align: center; border-radius: 10px 10px 0 0; }
                .header h1 { margin: 0; font-size: 24px; }
                .content { background: #f9f9f9; padding: 30px; border-radius: 0 0 10px 10px; }
                .credentials { background: white; padding: 20px; border-left: 4px solid #0052FF; margin: 20px 0; border-radius: 5px; }
                .credentials p { margin: 10px 0; font-size: 16px; }
                .credentials strong { color: #0052FF; }
                .footer { text-align: center; margin-top: 20px; font-size: 12px; color: #666; }
            </style>
        </head>
        <body>
            <div class='container'>
                <div class='header'>
                    <h1>🐾 Bienvenido a Zooki</h1>
                </div>
                <div class='content'>
                    <p>Hola <strong>$nombre</strong>,</p>
                    <p>Tu cuenta ha sido creada exitosamente en el sistema veterinario Zooki. A continuación te presentamos tus credenciales de acceso:</p>
                    
                    <div class='credentials'>
                        <p><strong>📋 Documento:</strong> $documento</p>
                        <p><strong>🔑 Contraseña:</strong> $password</p>
                    </div>
                    
                    <p><strong>⚠️ Importante:</strong> Por seguridad, te recomendamos cambiar tu contraseña en tu primer inicio de sesión.</p>
                    
                    <p>Para acceder al sistema, visita el siguiente enlace:</p>
                    <p><a href='http://localhost/Zooki/public/index.php' style='color: #0052FF; text-decoration: none; font-weight: bold;'>Acceder a Zooki</a></p>
                    
                    <div class='footer'>
                        <p>Este es un correo automático, por favor no respondas.</p>
                        <p>© 2024 Zooki - Sistema Veterinario</p>
                    </div>
                </div>
            </div>
        </body>
        </html>
        ";
    }
}
