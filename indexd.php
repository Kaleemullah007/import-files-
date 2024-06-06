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
    'NOMBRE' => 'Nombre',
    'CEDULA-RNC' => 'CedulaRNC',
    'DIRECCION' => 'Direccion',
    'TELEFONO 1' => 'Telefono1',
    'TELEFONO 2' => 'Telefono2',
    'NUMERO DE PRESTAMO' => 'NumeroPrestamo',
    'RELACION TIPO' => 'RelacionTipo',
    'FECHA APERTURA' => 'FechaApertura',
    'MONTO FORMALIZADO' => 'MontoFormalizado',
    'MONTO CUOTA' => 'MontoCuota',
    'ESTATUS' => 'Estatus',
    'BALANCE AL DIA' => 'BalanceAlDia',
    'MONTO EN ATRASO' => 'MontoEnAtraso',
    'FECHA VENCIMIENTO' => 'FechaVencimiento',
    'FECHA ULTIMO PAGO' => 'FechaUltimoPago',
    'TIPO DE PRESTAMO' => 'TipoPrestamo'
];

if (!empty($urlArchivo) && !empty($empresaId)) {
    $tempFile = tempnam(sys_get_temp_dir(), 'xlsx');
    $fileContent = file_get_contents($urlArchivo['tmp_name']);
    if ($fileContent === false) {
        die(json_encode(['error' => 'Error al descargar el archivo Excel.']));
    }
    file_put_contents($tempFile, $fileContent);
    if ($xlsx = SimpleXLSX::parse($tempFile)) {
        $headers = $xlsx->rows()[8]; // Novena fila contiene los encabezados
        foreach ($xlsx->rows() as $rowIndex => $row) {
            if ($rowIndex === 0 || $rowIndex == 8) continue; // Omitir las dos primeras filas (cabeceras)
            $mappedData = [];
            foreach ($headers as $index => $header) {
                if (isset($mapeoColumnas[$header])) {
                    $mappedData[$mapeoColumnas[$header]] = $row[$index] ?? null;
                }
            }
             // Verificar si el cliente ya existe en la base de datos
            $stmtClienteExistente = $conn->prepare("SELECT COUNT(*) FROM clientes WHERE cliente_cedula = ?");
            $stmtClienteExistente->bind_param("s", $mappedData['CedulaRNC']);
            $stmtClienteExistente->execute();
            $stmtClienteExistente->store_result();
            $stmtClienteExistente->bind_result($count);
            $stmtClienteExistente->fetch();
            $stmtClienteExistente->close();

            if ($count == 0) {
                // Si el cliente no existe, insertarlo
                $sqlInsertarCliente = "INSERT INTO clientes (cliente_cedula, NombreCliente) VALUES (?, ?)";
                $stmtInsertarCliente = $conn->prepare($sqlInsertarCliente);
                $stmtInsertarCliente->bind_param("ss", $mappedData['CedulaRNC'], $mappedData['Nombre']);
                $stmtInsertarCliente->execute();
                $stmtInsertarCliente->close();
            }
            // Proceso de inserción o actualización en la tabla correspondiente (datoscrediticios)
$stmtCheck = $conn->prepare("SELECT COUNT(*) FROM datoscrediticios WHERE CodigoPrestamo = ? AND empresa_id = ?");
$stmtCheck->bind_param("si", $mappedData['NumeroPrestamo'], $empresaId);
$stmtCheck->execute();
$stmtCheck->bind_result($count);
$stmtCheck->fetch();
$stmtCheck->close();

if ($count > 0) {
    // Actualizar los datos si ya existe el CodigoPrestamo
    $sqlUpdate = "UPDATE datoscrediticios SET 
                    cliente_cedula = ?, 
                    NombreCliente = ?, 
                    DireccionActual = ?, 
                    NumeroTelefono1 = ?, 
                    FechaAprobacion = ?, 
                    FechaVencimiento = ?, 
                    MontoCuotas = ?, 
                    Estatus = ?, 
                    Tipo = ?, 
                    ValorSolicitado = ?, 
                    BalanceActual = ?
                WHERE CodigoPrestamo = ? AND empresa_id = ?";
    $stmtUpdate = $conn->prepare($sqlUpdate);
    $stmtUpdate->bind_param( 
        "sssssssssssi", 
        $mappedData['CedulaRNC'], 
        $mappedData['Nombre'], 
        $mappedData['Direccion'], 
        $mappedData['Telefono1'], 
        $mappedData['FechaApertura'], 
        $mappedData['FechaVencimiento'], 
        $mappedData['MontoCuota'], 
        $mappedData['Estatus'], 
        $mappedData['RelacionTipo'], 
        $mappedData['MontoFormalizado'], 
        $mappedData['BalanceAlDia'],  
        $mappedData['NumeroPrestamo'], 
        $empresaId 
    );
    $stmtUpdate->execute();
    $stmtUpdate->close();
} else {
    // Insertar nuevo registro
    $sqlInsert = "INSERT INTO datoscrediticios (
                    CodigoPrestamo, 
                    cliente_cedula, 
                    NombreCliente, 
                    empresa_id, 
                    DireccionActual, 
                    NumeroTelefono1, 
                    FechaAprobacion, 
                    FechaVencimiento, 
                    MontoCuotas, 
                    Estatus, 
                    Tipo, 
                    ValorSolicitado, 
                    BalanceActual
                ) 
                VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";
    $stmtInsert = $conn->prepare($sqlInsert);
    $stmtInsert->bind_param( 
        "sssisssssssss", 
        $mappedData['NumeroPrestamo'], 
        $mappedData['CedulaRNC'], 
        $mappedData['Nombre'], 
        $empresaId, 
        $mappedData['Direccion'], 
        $mappedData['Telefono1'], 
        $mappedData['FechaApertura'], 
        $mappedData['FechaVencimiento'], 
        $mappedData['MontoCuota'], 
        $mappedData['Estatus'], 
        $mappedData['RelacionTipo'], 
        $mappedData['MontoFormalizado'], 
        $mappedData['BalanceAlDia']
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
