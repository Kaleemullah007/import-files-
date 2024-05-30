<?php
require 'vendor/autoload.php';

use Shuchkin\SimpleXLSX;

// Configuración de conexión a la base de datos
$dbHost = 'localhost';
$dbUsername = 'root';
$dbPassword = '';
$dbName = 'adofimdb';

$conn = new mysqli($dbHost, $dbUsername, $dbPassword, $dbName);

if ($conn->connect_error) {
    die("Conexión fallida: " . $conn->connect_error);
}

$data = json_decode(file_get_contents('php://input'), true);

$urlArchivo = $data['urlarchivo'] ?? '';
$empresaId = $data['empresa_id'] ?? '';


// Mapeo de columnas Excel a nombres de columnas DB
$mapeoColumnas = [
    '  No. Prest.' => 'CodigoPrestamo',
    '  Cédula' => 'cliente_cedula',
    '  Nombre del Cliente' => 'NombreCliente',
    '  Dirección del Cliente' => 'DireccionActual',
    '  Teléfono' => 'NumeroTelefono1',
    '  Fecha Aper.' => 'FechaAprobacion',
    '  Fecha Venc.' => 'FechaVencimiento',
    '  Valor Cuota' => 'MontoCuotas',
    '  Status' => 'Estatus',
    '  Tipo Prest.' => 'Tipo',
    // Se asume que otros campos necesarios ya están definidos o no se requieren
    '   Código' => 'Codigo',
	'  Valor Solic.' => 'ValorSolicitado',
	'  Balance Act.' => 'BalanceActual',
	'         1 - 30' => '1-30',
	'        31 - 60' => '31-60',
	'        61 - 90' => '61-90',
	'      91 - 120' => '91-120',
	'     121 - Más' => '121-mas',

];

if (!empty($urlArchivo) && !empty($empresaId)) {
    $tempFile = tempnam(sys_get_temp_dir(), 'xlsx');
    $fileContent = file_get_contents($urlArchivo['tmp_name']);
    if ($fileContent === false) {
        die(json_encode(['error' => 'Error al descargar el archivo Excel.']));
    }
    file_put_contents($tempFile, $fileContent);

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
            $stmtClienteExistente->close();

            if ($count == 0) { // Si el cliente no existe, insertarlo
                $sqlInsertarCliente = "INSERT INTO clientes (cliente_cedula, NombreCliente) VALUES (?, ?)";
                $stmtInsertarCliente = $conn->prepare($sqlInsertarCliente);
                $stmtInsertarCliente->bind_param("ss", $mappedData['cliente_cedula'], $mappedData['NombreCliente']);
                $stmtInsertarCliente->execute();
                $stmtInsertarCliente->close();
            }

            // Proceso de inserción o actualización en datoscrediticios
            $stmtCheck = $conn->prepare("SELECT COUNT(*) FROM datoscrediticios WHERE CodigoPrestamo = ? AND empresa_id = ?");
            $stmtCheck->bind_param("si", $mappedData['CodigoPrestamo'], $empresaId);
            $stmtCheck->execute();
            $stmtCheck->bind_result($count);
            $stmtCheck->fetch();
            $stmtCheck->close();

            if ($count > 0) {
                // Actualizar los datos si ya existe el CodigoPrestamo
                $sqlUpdate = "UPDATE datoscrediticios SET 
                cliente_cedula = ?, NombreCliente = ?, DireccionActual = ?, NumeroTelefono1 = ?, 
                FechaAprobacion = ?, FechaVencimiento = ?, MontoCuotas = ?, 
                Estatus = ?, Tipo = ?, ValorSolicitado = ?, BalanceActual = ?, 
                `1-30` = ?, `31-60` = ?, `61-90` = ?, `91-120` = ?, `121-mas` = ?
            WHERE CodigoPrestamo = ? AND empresa_id = ?";
            $stmtUpdate = $conn->prepare($sqlUpdate);
            $stmtUpdate->bind_param(
                "ssssssssssssssssi",
                $mappedData['cliente_cedula'], $mappedData['NombreCliente'], $mappedData['DireccionActual'], 
                $mappedData['NumeroTelefono1'], $mappedData['FechaAprobacion'], 
                $mappedData['FechaVencimiento'], $mappedData['MontoCuotas'], 
                $mappedData['Estatus'], $mappedData['Tipo'], $mappedData['ValorSolicitado'], 
                $mappedData['BalanceActual'], $mappedData['1-30'], $mappedData['31-60'], 
                $mappedData['61-90'], $mappedData['91-120'], $mappedData['121-mas'],
                $mappedData['CodigoPrestamo'], $empresaId
            );
                $stmtUpdate->execute();
                $stmtUpdate->close();
            } else {
                // Insertar nuevo registro
                $sqlInsert = "INSERT INTO datoscrediticios (
                    CodigoPrestamo, cliente_cedula, NombreCliente, empresa_id, DireccionActual, 
                    NumeroTelefono1, FechaAprobacion, FechaVencimiento, MontoCuotas, 
                    Estatus, Tipo, ValorSolicitado, BalanceActual, `1-30`, `31-60`, `61-90`, `91-120`, `121-mas`
                ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";
                $stmtInsert = $conn->prepare($sqlInsert);
                $stmtInsert->bind_param(
                    "sssissssssssssssss",
                    $mappedData['CodigoPrestamo'], $mappedData['cliente_cedula'], $mappedData['NombreCliente'], 
                    $empresaId, $mappedData['DireccionActual'], $mappedData['NumeroTelefono1'], 
                    $mappedData['FechaAprobacion'], $mappedData['FechaVencimiento'], 
                    $mappedData['MontoCuotas'], $mappedData['Estatus'], $mappedData['Tipo'], 
                    $mappedData['ValorSolicitado'], $mappedData['BalanceActual'], $mappedData['1-30'], 
                    $mappedData['31-60'], $mappedData['61-90'], $mappedData['91-120'], $mappedData['121-mas']
                );
                $stmtInsert->execute();
                $stmtInsert->close();
            }
        }
        echo json_encode(['success' => 'Datos procesados correctamente.']);
    } else {
        echo json_encode(['error' => 'Error al leer el archivo Excel.']);
    }
    unlink($tempFile); // Eliminar el archivo temporal
} else {
    echo json_encode(['error' => 'Falta la URL del archivo o el ID de empresa.']);
}

$conn->close(); // Cerrar la conexión a la base de datos
?>