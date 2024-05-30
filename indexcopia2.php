<?php
require 'vendor/autoload.php';

use Shuchkin\SimpleXLSX;

$data = json_decode(file_get_contents('php://input'), true);

// echo "<pre>";
// // print_r($_FILES['urlarchivo']['tmp_name']);
// die();

$urlArchivo = $_FILES['urlarchivo'] ?? '';
$empresaId = $_REQUEST['empresa_id'] ?? '';

if (!empty($urlArchivo) && !empty($empresaId) && !empty($urlArchivo['tmp_name'])) {
    $tempFile = tempnam(sys_get_temp_dir(), 'xlsx');
    $fileContent = file_get_contents($urlArchivo['tmp_name']);
    if ($fileContent === false) {
        die(json_encode(['error' => 'Error al descargar el archivo Excel.']));
    }
    file_put_contents($tempFile, $fileContent);

    if ($xlsx = SimpleXLSX::parse($tempFile)) {
        $headers = $xlsx->rows()[0];

        // print_r($headers);
        // die();
        $formatoDetectado = detectarFormato($headers);
        // echo $formatoDetectado;
        // die();

        switch ($formatoDetectado) {
            case 'formatoA':
                $response = procesarFormatoA($empresaId, $urlArchivo);
                break;
            case 'formatoB':
                $response = procesarFormatoB($empresaId, $urlArchivo);
                break;
            default:
                $response = ['error' => 'Formato de archivo desconocido.'];
                break;
        }
        echo json_encode($response);
    } else {
        echo json_encode(['error' => 'Error al leer el archivo Excel.']);
    }
} else {
    echo json_encode(['error' => 'Falta la URL del archivo o el ID de empresa en indexcopia2.']);
}

function detectarFormato($headers) {
    $formatoA = ['Datos Personales', 'Información Demografica', 'Datos de la Cuenta'];
    $formatoB = ['NOMBR', 'CEDULA-RNC', 'DIRECCION'];

    if (count(array_intersect($formatoA, $headers)) == count($formatoA)) {
        return 'formatoA';
    } elseif (count(array_intersect($formatoB, $headers)) == count($formatoB)) {
        return 'formatoB';
    }
    elseif (count(array_intersect($formatoB, $headers)) == count($formatoB)) {
        return 'formatoB';
    }
    elseif (count(array_intersect($formatoB, $headers)) == count($formatoB)) {
        return 'formatoB';
    }


    return 'desconocido';
}

function procesarFormatoA($empresaId, $urlArchivo) {
    $url = 'http://localhost/fiver/apisubir/index.php'; // URL ajustada
    return enviarDatosPorCurl($url, $empresaId, $urlArchivo);
}

function procesarFormatoB($empresaId, $urlArchivo) {
    $url = 'http://localhost/fiver/apisubir/indexa2.php'; // URL ajustada
    return enviarDatosPorCurl($url, $empresaId, $urlArchivo);
}

function enviarDatosPorCurl($url, $empresaId, $urlArchivo) {
    // Creando el cuerpo de la solicitud como una cadena JSON
    $data = [
        'empresa_id' => $empresaId,
        'urlarchivo' => $urlArchivo
    ];
    // print_r($data);die();

    

    $curl = curl_init();
    
    curl_setopt_array($curl, array(
      CURLOPT_URL =>$url,
      CURLOPT_RETURNTRANSFER => true,
      CURLOPT_ENCODING => '',
      CURLOPT_MAXREDIRS => 10,
      CURLOPT_TIMEOUT => 0,
      CURLOPT_FOLLOWLOCATION => true,
      CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
      CURLOPT_CUSTOMREQUEST => 'POST',
      CURLOPT_POSTFIELDS => json_encode($data),
      CURLOPT_HTTPHEADER=>[
        'Content-Type: application/json',
        'Content-Length: ' . strlen(json_encode($data))
    ]
    ));
    
    $result = curl_exec($curl);
    $err = curl_error($curl);
    curl_close($curl);
    
    if ($err) {
        return ['error' => "cURL Error #:" . $err];
    } else {


        
        return json_decode($result, true); // Asumiendo que la respuesta es JSON
    }
}
?>
