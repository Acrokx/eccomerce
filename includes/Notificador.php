<?php
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

require '../vendor/autoload.php';

class Notificador {
    private $pdo;
    private $mailer;

    public function __construct($database_connection) {
        $this->pdo = $database_connection;
        $this->configurarMailer();
    }

    private function configurarMailer() {
        $this->mailer = new PHPMailer(true);

        try {
            // Configuración SMTP (ejemplo con Gmail)
            $this->mailer->isSMTP();
            $this->mailer->Host = 'smtp.gmail.com'; // Servidor SMTP
            $this->mailer->SMTPAuth = true;
            $this->mailer->Username = 'tu-email@gmail.com'; // Tu email
            $this->mailer->Password = 'tu-contraseña-app'; // Contraseña de aplicación
            $this->mailer->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
            $this->mailer->Port = 587;

            // Configuración del remitente
            $this->mailer->setFrom('noreply@organicos.com', 'Plataforma Orgánicos');
            $this->mailer->CharSet = 'UTF-8';

        } catch (Exception $e) {
            error_log("Error configurando mailer: ". $e->getMessage());
        }
    }

    // Notificación de pedido confirmado
    public function enviarConfirmacionPedido($pedido_id) {
        try {
            // Obtener datos del pedido
            $stmt = $this->pdo->prepare("
            SELECT p.*, u.nombre as cliente_nombre, u.email as cliente_email
            FROM pedidos p
            JOIN usuarios u ON p.cliente_id = u.id
            WHERE p.id = ?
            ");
            $stmt->execute([$pedido_id]);
            $pedido = $stmt->fetch(PDO::FETCH_ASSOC);

            if ($pedido) {
                // Obtener detalles del pedido
                $stmt = $this->pdo->prepare("
                SELECT pd.*, pr.nombre as producto_nombre
                FROM pedido_detalles pd
                JOIN productos pr ON pd.producto_id = pr.id
                WHERE pd.pedido_id = ?
                ");
                $stmt->execute([$pedido_id]);
                $detalles = $stmt->fetchAll(PDO::FETCH_ASSOC);

                // Preparar email
                $this->mailer->clearAddresses();
                $this->mailer->addAddress($pedido['cliente_email'], $pedido['cliente_nombre']);
                $this->mailer->Subject = 'Confirmación de Pedido #' . $pedido_id;

                // Crear contenido HTML
                $html = $this->plantillaPedidoConfirmado($pedido, $detalles);
                $this->mailer->isHTML(true);
                $this->mailer->Body = $html;

                // Enviar email
                if ($this->mailer->send()) {
                    // Registrar notificación enviada
                    $stmt = $this->pdo->prepare("
                    INSERT INTO notificaciones (usuario_id, tipo, mensaje, enviado, fecha_envio)
                    VALUES (?, 'email', ?, 1, NOW())
                    ");
                    $stmt->execute([$pedido['cliente_id'], 'Confirmación pedido #' . $pedido_id]);
                    return true;
                } else {
                    error_log("Error enviando email: ". $this->mailer->ErrorInfo);
                    return false;
                }
            }
        } catch (Exception $e) {
            error_log("Error en notificación: ". $e->getMessage());
            return false;
        }
    }

    private function plantillaPedidoConfirmado($pedido, $detalles) {
        $productos_html = '';
        foreach ($detalles as $detalle) {
            $productos_html .= "<li>{$detalle['producto_nombre']} (x{$detalle['cantidad']}) - $" . number_format($detalle['subtotal'], 2) . "</li>";
        }

        return "
        <html>
        <head>
            <style>
                body { font-family: Arial, sans-serif; background-color: #f4f4f4; }
                .container { max-width: 600px; margin: 0 auto; background-color: white; padding: 20px; border-radius: 8px; }
                .header { background-color: #27ae60; color: white; padding: 20px; text-align: center; border-radius: 8px 8px 0 0; }
                .content { padding: 20px; }
                .footer { background-color: #ecf0f1; padding: 10px; text-align: center; border-radius: 0 0 8px 8px; }
                .total { font-size: 18px; font-weight: bold; color: #27ae60; }
            </style>
        </head>
        <body>
            <div class='container'>
                <div class='header'>
                    <h1>¡Pedido Confirmado!</h1>
                </div>
                <div class='content'>
                    <p>Hola <strong>{$pedido['cliente_nombre']}</strong>,</p>
                    <p>Tu pedido ha sido confirmado exitosamente.</p>
                    <p><strong>Número de pedido:</strong> {$pedido['id']}</p>
                    <p><strong>Productos:</strong></p>
                    <ul>$productos_html</ul>
                    <p class='total'>Total: $" . number_format($pedido['total'], 2) . "</p>
                    <p>Recibirás actualizaciones sobre el estado de tu pedido.</p>
                </div>
                <div class='footer'>
                    <p>Plataforma Orgánicos - Productos frescos y saludables</p>
                </div>
            </div>
        </body>
        </html>
        ";
    }
}
?>