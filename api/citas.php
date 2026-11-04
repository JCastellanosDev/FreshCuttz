<?php
// Horarios ocupados de un barbero en una fecha: GET ?barbero=2&fecha=2026-11-05
header('Content-Type: application/json; charset=utf-8');

require_once __DIR__ . '/../includes/negocio.php';

$barbero = filter_input(INPUT_GET, 'barbero', FILTER_VALIDATE_INT);
$fecha = (string)filter_input(INPUT_GET, 'fecha');
$fechaValida = DateTime::createFromFormat('!Y-m-d', $fecha);

if (!$barbero || !isset(BARBEROS[$barbero]) || !$fechaValida || $fechaValida->format('Y-m-d') !== $fecha) {
    http_response_code(422);
    echo json_encode(['error' => 'Parámetros no válidos.'], JSON_UNESCAPED_UNICODE);
    exit;
}

try {
    $consulta = conexion()->prepare("SELECT TIME_FORMAT(hora, '%H:%i') FROM citas WHERE barbero_id = ? AND fecha = ? AND cancelada = 0");
    $consulta->execute([$barbero, $fecha]);
    echo json_encode(['ocupadas' => $consulta->fetchAll(PDO::FETCH_COLUMN)]);
} catch (PDOException $e) {
    error_log('FreshCuttz citas: ' . $e->getMessage());
    http_response_code(500);
    echo json_encode(['error' => 'No pudimos consultar la disponibilidad.'], JSON_UNESCAPED_UNICODE);
}
