<?php

require 'vendor/autoload.php';

use Shuchkin\SimpleXLSX;

// Configuración de conexión a la base de datos
$dbHost = 'localhost';
$dbUsername = 'root';
$dbPassword = '';
$dbName = 'adofimdb';

// Crear conexión a la base de datos
$conn = new mysqli($dbHost, $dbUsername, $dbPassword, $dbName);

// Verificar conexión
if ($conn->connect_error) {
    die("Conexión fallida: " . $conn->connect_error);
}

// Obtener datos JSON del cuerpo de la solicitud
$data = json_decode(file_get_contents('php://input'), true);

$urlArchivo = $data['urlarchivo'] ?? '';
$empresaId = $data['empresa_id'] ?? '';

// Mapeo de columnas Excel a nombres de columnas DB
$mapeoColumnas = [
    'Codigo' => 'CodigoPrestamo',
    'RNC/Cedula' => 'cliente_cedula',
    'Nombre del Cliente' => 'NombreCliente',
    'Direccion Actual' => 'DireccionActual',
    'Numero Telefono1' => 'NumeroTelefono1',
    'Numero Telefono2' => 'NumeroTelefono2',
    'Fecha Aprobacion' => 'FechaAprobacion',
    'Monto Aprobado' => 'MontoAprobado',
    'Fecha Vencimiento' => 'FechaVencimiento',
    'Monto Cuotas' => 'MontoCuotas',
    'Balance Atraso' => 'BalanceAtraso',
    'Balance Pendiente' => 'BalancePendiente',
    'Estatus' => 'Estatus',
    'Tipo' => 'Tipo',
    'Ultimo Pago' => 'UltimoPago',
    // Agregar más mapeos si es necesario
];

// Verificar que se han proporcionado URL del archivo y ID de empresa
if (!empty($urlArchivo) && !empty($empresaId)) {
    // Descargar archivo Excel
    $tempFile = tempnam(sys_get_temp_dir(), 'xlsx');
    $fileContent = file_get_contents($urlArchivo);
    if ($fileContent === false) {
        die(json_encode(['error' => 'Error al descargar el archivo Excel.']));
    }
    file_put_contents($tempFile, $fileContent);

    // Procesar archivo Excel
    if ($xlsx = SimpleXLSX::parse($tempFile)) {
        $headers = $xlsx->rows()[0];
        foreach ($xlsx->rows() as $rowIndex => $row) {
            if ($rowIndex === 0) continue; // Omitir la cabecera

            $mappedData = [];
            foreach ($headers as $index => $header) {
                if (isset($mapeoColumnas[$header])) {
                    $mappedData[$mapeoColumnas[$header]] = $row[$index] ?? null;
                }
            }
            // Verificar si el cliente ya existe en la base de datos
            $stmtClienteExistente = $conn->prepare("SELECT COUNT(*) FROM clientes WHERE cliente_cedula = ?");
            $stmtClienteExistente->bind_param("s", $mappedData['cliente_cedula']);
            $stmtClienteExistente->execute();
            $stmtClienteExistente->store_result();
            $stmtClienteExistente->bind_result($count);
            $stmtClienteExistente->fetch();

            if ($count == 0) { // Si el cliente no existe, insertarlo
                $sqlInsertarCliente = "INSERT INTO clientes (NombreCliente, cliente_cedula) VALUES (?, ?)";
                $stmtInsertarCliente = $conn->prepare($sqlInsertarCliente);
                if ($stmtInsertarCliente) {
                    $stmtInsertarCliente->bind_param("ss", $mappedData['NombreCliente'], $mappedData['cliente_cedula']);
                    $stmtInsertarCliente->execute();
                    $stmtInsertarCliente->close();
                } else {
                    echo "Error preparando la inserción del cliente: " . $conn->error;
                }
            }

            $stmtClienteExistente->close();
            // Insertar o actualizar registros en la base de datos
            $stmtCheck = $conn->prepare("SELECT COUNT(*) AS count FROM datoscrediticios WHERE CodigoPrestamo = ? AND empresa_id = ?");
            $stmtCheck->bind_param("si", $mappedData['CodigoPrestamo'], $empresaId);
            $stmtCheck->execute();
            $result = $stmtCheck->get_result();
            $row = $result->fetch_assoc();
            $stmtCheck->close();

            if ($row['count'] > 0) {
                // Actualizar los datos si ya existe el CodigoPrestamo
                $sqlUpdate = "UPDATE datoscrediticios SET 
    cliente_cedula = ?, NombreCliente = ?, DireccionActual = ?, NumeroTelefono1 = ?, 
    NumeroTelefono2 = ?, FechaAprobacion = ?, MontoAprobado = ?, 
    FechaVencimiento = ?, MontoCuotas = ?, BalanceAtraso = ?, 
    BalancePendiente = ?, Estatus = ?, Tipo = ?, UltimoPago = ?
WHERE CodigoPrestamo = ? AND empresa_id = ?";
                if (!$stmtUpdate = $conn->prepare($sqlUpdate)) {
                    // Manejar el error aquí, por ejemplo:
                    die("Error preparando la consulta: " . $conn->error);
                }

                $stmtUpdate->bind_param(
                    "sssssssssssssssi",
                    $mappedData['cliente_cedula'],
                    $mappedData['NombreCliente'], // Incluye el nuevo campo aquí
                    $mappedData['DireccionActual'],
                    $mappedData['NumeroTelefono1'],
                    $mappedData['NumeroTelefono2'],
                    $mappedData['FechaAprobacion'],
                    $mappedData['MontoAprobado'],
                    $mappedData['FechaVencimiento'],
                    $mappedData['MontoCuotas'],
                    $mappedData['BalanceAtraso'],
                    $mappedData['BalancePendiente'],
                    $mappedData['Estatus'],
                    $mappedData['Tipo'],
                    $mappedData['UltimoPago'],
                    $mappedData['CodigoPrestamo'],
                    $empresaId
                );

                $stmtUpdate->execute();
                $stmtUpdate->close();
            } else {
                // Insertar nuevo registro
                $sqlInsert = "INSERT INTO datoscrediticios (
                                CodigoPrestamo, cliente_cedula, NombreCliente, empresa_id, DireccionActual, 
                                NumeroTelefono1, NumeroTelefono2, FechaAprobacion, MontoAprobado, 
                                FechaVencimiento, MontoCuotas, BalanceAtraso, BalancePendiente, 
                                Estatus, Tipo, UltimoPago
                            ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";

                // Preparar la consulta.
                $stmtInsert = $conn->prepare($sqlInsert);

                // Verificar si la preparación fue exitosa.
                if ($stmtInsert === false) {
                    die("Error preparando la consulta: " . $conn->error);
                }

                $stmtInsert->bind_param(
                    "sssissssssssssss",
                    $mappedData['CodigoPrestamo'],
                    $mappedData['cliente_cedula'],
                    $mappedData['NombreCliente'], // Asegúrate de agregar esta variable
                    $empresaId,
                    $mappedData['DireccionActual'],
                    $mappedData['NumeroTelefono1'],
                    $mappedData['NumeroTelefono2'],
                    $mappedData['FechaAprobacion'],
                    $mappedData['MontoAprobado'],
                    $mappedData['FechaVencimiento'],
                    $mappedData['MontoCuotas'],
                    $mappedData['BalanceAtraso'],
                    $mappedData['BalancePendiente'],
                    $mappedData['Estatus'],
                    $mappedData['Tipo'],
                    $mappedData['UltimoPago']
                );

                $stmtInsert->execute();
                $stmtInsert->close();
            }
        }
        echo json_encode(['success' => 'Datos procesados correctamente.']);
    } else {
        echo json_encode(['error' => 'Error al leer el archivo Excel.']);
    }
} else {
    echo json_encode(['error' => 'Falta la URL del archivo o el ID de empresa en index.']);
}

// Cerrar la conexión a la base de datos
$conn->close();
