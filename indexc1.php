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
    'TIPO DE ENTIDAD' => 'TipoEntidad',
    'NOMBRE DEL CLIENTE' => 'NombreCliente',
    'APELLIDOS' => 'Apellidos',
    'CEDULA O RNC' => 'CedulaRNC',
    'SEXO' => 'Sexo',
    'ESTADO CIVIL' => 'EstadoCivil',
    'OCUPACION' => 'Ocupacion',
    'CODIGO DE CLIENTE' => 'CodigoCliente',
    'FECHA DE NACIMIENTO' => 'FechaNacimiento',
    'NACIONALIDAD' => 'Nacionalidad',
    'DIRECCION' => 'Direccion',
    'SECTOR' => 'Sector',
    'CALLE/NUMERO' => 'CalleNumero',
    'MUNICIPIO' => 'Municipio',
    'CIUDAD' => 'Ciudad',
    'PROVINCIA' => 'Provincia',
    'País' => 'Pais',
    'Dir_Referencia' => 'DirReferencia',
    'TELEFONO1' => 'Telefono1',
    'TELEFONO2' => 'Telefono2',
    'EMPRESA DONDE TRABAJA' => 'EmpresaTrabajo',
    'CARGO' => 'Cargo',
    'DIRECCION EMPRESA' => 'DireccionEmpresa',
    'CALLE/NUMERO EMPRESA' => 'CalleNumeroEmpresa',
    'MUNICIPIO EMPRESA' => 'MunicipioEmpresa',
    'CIUDAD EMPRESA' => 'CiudadEmpresa',
    'PROVINCIA EMPRESA' => 'ProvinciaEmpresa',
    'País EMPRESA' => 'PaisEmpresa',
    'Dir_Referencia EMPRESA' => 'DirReferenciaEmpresa',
    'SALARIO MENSUAL EMPRESA' => 'SalarioMensualEmpresa',
    'MONEDA SALARIO' => 'MonedaSalario',
    'RELACIÓN TIPO' => 'RelacionTipo',
    'FECHA APERTURA' => 'FechaApertura',
    'FECHA VENCIMIENTO' => 'FechaVencimiento',
    'FECHA ULTIMO PAGO' => 'FechaUltimoPago',
    'NUMERO CUENTA' => 'NumeroCuenta',
    'ESTATUS' => 'Estatus',
    'TIPO DE PRESTAMO' => 'TipoPrestamo',
    'MONEDA' => 'Moneda',
    'CREDITO APROBADO' => 'CreditoAprobado',
    'BALANCE AL CORTE' => 'BalanceCorte',
    'MONTO ADEUDADO' => 'MontoAdeudado',
    'PAGO MANDATORIO O CUOTA' => 'PagoMandatorioCuota',
    'MONTO ULTIMO PAGO' => 'MontoUltimoPago',
    'TOTAL DE ATRASO' => 'TotalAtraso',
    'TASA DE INTERES' => 'TasaInteres',
    'FORMA DE PAGO' => 'FormaPago',
    'CANTIDAD DE CUOTAS' => 'CantidadCuotas',
    'ATRASO 1 A 30 DIAS' => 'Atraso1a30Dias',
    'ATRASO 31 A 60 DIAS' => 'Atraso31a60Dias',
    'ATRASO 61 A 90 DIAS' => 'Atraso61a90Dias',
    'ATRASO 91 A 120 DIAS' => 'Atraso91a120Dias',
    'ATRASO 121 A 150 DIAS' => 'Atraso121a150Dias',
    'ATRASO 151 A 180 DIAS' => 'Atraso151a180Dias',
    'ATRASO 181 DIAS O MAS' => 'Atraso181OMas',
];

if (!empty($urlArchivo) && !empty($empresaId)) {
    $tempFile = tempnam(sys_get_temp_dir(), 'xlsx');
    $fileContent = file_get_contents($urlArchivo['tmp_name']);
    if ($fileContent === false) {
        die(json_encode(['error' => 'Error al descargar el archivo Excel.']));
    }
    file_put_contents($tempFile, $fileContent);
    if ($xlsx = SimpleXLSX::parse($tempFile)) {
        $headers = $xlsx->rows()[1]; // Segunda fila contiene los encabezados
        foreach ($xlsx->rows() as $rowIndex => $row) {
            if ($rowIndex === 0 || $rowIndex === 1) continue; // Omitir las dos primeras filas (cabeceras)
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
                $stmtInsertarCliente->bind_param("ss", $mappedData['CedulaRNC'], $mappedData['NombreCliente']);
                $stmtInsertarCliente->execute();
                $stmtInsertarCliente->close();
            }
			// Proceso de inserción o actualización en la tabla correspondiente (datoscrediticios)
$stmtCheck = $conn->prepare("SELECT COUNT(*) FROM datoscrediticios WHERE CodigoPrestamo = ? AND empresa_id = ?");
$stmtCheck->bind_param("si", $mappedData['CodigoCliente'], $empresaId);
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
                    BalanceActual = ?, 
                    `1-30` = ?, 
                    `31-60` = ?, 
                    `61-90` = ?, 
                    `91-120` = ?, 
                    `121-mas` = ? 
                WHERE CodigoPrestamo = ? AND empresa_id = ?";
    $stmtUpdate = $conn->prepare($sqlUpdate);
    $stmtUpdate->bind_param( 
        "sssssssssssssssssi", 
        $mappedData['CedulaRNC'], 
        $mappedData['NombreCliente'], 
        $mappedData['Direccion'], 
        $mappedData['Telefono1'], 
        $mappedData['CodigoCliente'], 
        $mappedData['FechaApertura'], 
        $mappedData['FechaVencimiento'],
        $mappedData['NumeroCuenta'], 
        $mappedData['Estatus'], 
        $mappedData['TipoPrestamo'], 
        $mappedData['CreditoAprobado'],  
        $mappedData['BalanceCorte'], 
        $mappedData['Atraso1a30Dias'], 
        $mappedData['Atraso31a60Dias'], 
        $mappedData['Atraso61a90Dias'], 
        $mappedData['Atraso91a120Dias'], 
        $mappedData['Atraso121a150Dias'], 
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
                    BalanceActual, 
                    `1-30`, 
                    `31-60`, 
                    `61-90`, 
                    `91-120`, 
                    `121-mas`
                   
                ) 
                VALUES (?, ?, ?, ?, ?, ?, ?, ?,?,?,?,?,?,?,?,?,?,?)";
    $stmtInsert = $conn->prepare($sqlInsert);
    $stmtInsert->bind_param( 
        "sssissssssssssssss", 
        $mappedData['CodigoCliente'], 
        $mappedData['CedulaRNC'], 
        $mappedData['NombreCliente'], 
        $empresaId, 
        $mappedData['Direccion'], 
        $mappedData['Telefono1'], 
        $mappedData['FechaApertura'], 
        $mappedData['FechaVencimiento'],
        $mappedData['NumeroCuenta'], 
        $mappedData['Estatus'], 
        $mappedData['TipoPrestamo'], 
        $mappedData['CreditoAprobado'],
        $mappedData['BalanceCorte'], 
        $mappedData['Atraso1a30Dias'], 
        $mappedData['Atraso31a60Dias'], 
        $mappedData['Atraso61a90Dias'], 
        $mappedData['Atraso91a120Dias'], 
        $mappedData['Atraso121a150Dias']
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
