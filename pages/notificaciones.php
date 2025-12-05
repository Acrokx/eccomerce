<?php
// Funciones de notificación automática

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

require '../vendor/autoload.php';

// Función para enviar email con PHPMailer
function enviar_email($destinatario, $asunto, $mensaje_html, $mensaje_texto = '') {
    $mail = new PHPMailer(true);

    try {
        // Configuración del servidor
        $mail->isSMTP();
        $mail->Host = 'smtp.gmail.com'; // Cambiar según tu proveedor
        $mail->SMTPAuth = true;
        $mail->Username = 'tuemail@gmail.com'; // Cambiar
        $mail->Password = 'tucontraseña'; // Cambiar
        $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
        $mail->Port = 587;

        // Remitente
        $mail->setFrom('noreply@ecommerceorganico.com', 'Ecommerce Orgánico');

        // Destinatario
        $mail->addAddress($destinatario);

        // Contenido
        $mail->isHTML(true);
        $mail->Subject = $asunto;
        $mail->Body = $mensaje_html;
        if (!empty($mensaje_texto)) {
            $mail->AltBody = $mensaje_texto;
        }

        $mail->send();

        // Log de envío exitoso
        $log = date('Y-m-d H:i:s') . " - Email enviado exitosamente a: $destinatario\nAsunto: $asunto\n\n";
        file_put_contents('email_log.txt', $log, FILE_APPEND);

        return true;
    } catch (Exception $e) {
        // Log de error
        $log = date('Y-m-d H:i:s') . " - Error al enviar email a: $destinatario\nError: {$mail->ErrorInfo}\n\n";
        file_put_contents('email_log.txt', $log, FILE_APPEND);

        return false;
    }
}

// Notificación de pedido realizado al cliente
function notificar_pedido_realizado_cliente($cliente_email, $cliente_nombre, $pedido_id, $total) {
    $asunto = "Confirmación de Pedido - Ecommerce Orgánico";

    $mensaje = "
    <html>
    <head>
        <title>Confirmación de Pedido</title>
        <style>
            body { font-family: Arial, sans-serif; }
            .header { background-color: #27ae60; color: white; padding: 20px; text-align: center; }
            .content { padding: 20px; }
            .footer { background-color: #ecf0f1; padding: 10px; text-align: center; font-size: 12px; }
        </style>
    </head>
    <body>
        <div class='header'>
            <h1>¡Pedido Confirmado!</h1>
        </div>
        <div class='content'>
            <p>Hola <strong>$cliente_nombre</strong>,</p>
            <p>Tu pedido ha sido confirmado exitosamente.</p>
            <p><strong>Número de pedido:</strong> $pedido_id</p>
            <p><strong>Total:</strong> $$total</p>
            <p>Recibirás actualizaciones sobre el estado de tu pedido.</p>
            <p>Gracias por comprar productos orgánicos con nosotros.</p>
        </div>
        <div class='footer'>
            <p>Ecommerce Orgánico - Productos frescos y saludables</p>
        </div>
    </body>
    </html>
    ";

    return enviar_email($cliente_email, $asunto, $mensaje);
}

// Notificación de nuevo pedido al agricultor
function notificar_nuevo_pedido_agricultor($agricultor_email, $agricultor_nombre, $cliente_nombre, $productos_pedidos) {
    $asunto = "Nuevo Pedido Recibido - Ecommerce Orgánico";

    $productos_lista = "";
    foreach ($productos_pedidos as $producto) {
        $productos_lista .= "<li>{$producto['nombre']} - Cantidad: {$producto['cantidad']}</li>";
    }

    $mensaje = "
    <html>
    <head>
        <title>Nuevo Pedido</title>
        <style>
            body { font-family: Arial, sans-serif; }
            .header { background-color: #3498db; color: white; padding: 20px; text-align: center; }
            .content { padding: 20px; }
            .footer { background-color: #ecf0f1; padding: 10px; text-align: center; font-size: 12px; }
        </style>
    </head>
    <body>
        <div class='header'>
            <h1>Nuevo Pedido Recibido</h1>
        </div>
        <div class='content'>
            <p>Hola <strong>$agricultor_nombre</strong>,</p>
            <p>Has recibido un nuevo pedido de <strong>$cliente_nombre</strong>.</p>
            <p><strong>Productos pedidos:</strong></p>
            <ul>$productos_lista</ul>
            <p>Por favor, prepara los productos para la entrega.</p>
        </div>
        <div class='footer'>
            <p>Ecommerce Orgánico - Conectando agricultores con consumidores</p>
        </div>
    </body>
    </html>
    ";

    return enviar_email($agricultor_email, $asunto, $mensaje);
}

// Notificación de cambio de estado del pedido
function notificar_cambio_estado_pedido($cliente_email, $cliente_nombre, $pedido_id, $nuevo_estado) {
    $asunto = "Actualización de Estado del Pedido - Ecommerce Orgánico";

    $mensaje = "
    <html>
    <head>
        <title>Actualización de Pedido</title>
        <style>
            body { font-family: Arial, sans-serif; }
            .header { background-color: #f39c12; color: white; padding: 20px; text-align: center; }
            .content { padding: 20px; }
            .footer { background-color: #ecf0f1; padding: 10px; text-align: center; font-size: 12px; }
        </style>
    </head>
    <body>
        <div class='header'>
            <h1>Estado del Pedido Actualizado</h1>
        </div>
        <div class='content'>
            <p>Hola <strong>$cliente_nombre</strong>,</p>
            <p>El estado de tu pedido #$pedido_id ha cambiado a: <strong>$nuevo_estado</strong></p>
            <p>Si tienes alguna pregunta, no dudes en contactarnos.</p>
        </div>
        <div class='footer'>
            <p>Ecommerce Orgánico - Tu satisfacción es nuestra prioridad</p>
        </div>
    </body>
    </html>
    ";

    return enviar_email($cliente_email, $asunto, $mensaje);
}

// Notificación de baja calificación (opcional)
function notificar_baja_calificacion($agricultor_email, $agricultor_nombre, $producto_nombre, $calificacion) {
    if ($calificacion <= 2) {
        $asunto = "Calificación Baja Recibida - Ecommerce Orgánico";

        $mensaje = "
        <html>
        <head>
            <title>Calificación Baja</title>
            <style>
                body { font-family: Arial, sans-serif; }
                .header { background-color: #e74c3c; color: white; padding: 20px; text-align: center; }
                .content { padding: 20px; }
                .footer { background-color: #ecf0f1; padding: 10px; text-align: center; font-size: 12px; }
            </style>
        </head>
        <body>
            <div class='header'>
                <h1>Calificación Baja Recibida</h1>
            </div>
            <div class='content'>
                <p>Hola <strong>$agricultor_nombre</strong>,</p>
                <p>Tu producto '$producto_nombre' recibió una calificación baja ($calificacion estrellas).</p>
                <p>Considera mejorar la calidad o el servicio para futuras ventas.</p>
            </div>
            <div class='footer'>
                <p>Ecommerce Orgánico - Mejora continua</p>
            </div>
        </body>
        </html>
        ";

        return enviar_email($agricultor_email, $asunto, $mensaje);
    }
    return true;
}
?>