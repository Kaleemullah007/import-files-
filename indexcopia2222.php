<?php
require'vendor/autoload.php';

use Shuchkin\SimpleXLSX;

$data = json_decode(file_get_contents('php://input'), true);


$urlArchivo = $_FILES['urlarchivo'] ?? '';
$empresaId = $_REQUEST['empresa_id'] ?? '';



if (!empty($urlArchivo) &&!empty($empresaId) && !empty($urlArchivo['tmp_name'])) {
  $tempFile = tempnam(sys_get_temp_dir(), 'xlsx');
  $fileContent = file_get_contents($urlArchivo['tmp_name']);
  if ($fileContent === false) {
    die(json_encode(['error' => 'Error al descargar el archivo Excel.']));
  }
  file_put_contents($tempFile, $fileContent);

  if ($xlsx = SimpleXLSX::parse($tempFile)) {
    $formatoDetectado = detectarFormato($xlsx);
    // echo $formatoDetectado;
    // die();
    
    switch ($formatoDetectado) {
      case 'formatoA':
        $response = procesarFormatoA($empresaId, $urlArchivo);
        break;
      case 'formatoB':
        $response = procesarFormatoB($empresaId, $urlArchivo);
        break;
      case 'formatoC':
        $response = procesarFormatoC($empresaId, $urlArchivo);
        break;
      case 'formatoD':
        $response = procesarFormatoD($empresaId, $urlArchivo);
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

function detectarFormato($xlsx) {
  $formatos = [
    'formatoA' => ['Codigo', 'Nombre del Cliente', 'RNC/Cedula'],
    'formatoB' => ['   Código','  No. Prest.','  Nombre del Cliente'],
    'formatoC' => ['TIPO DE ENTIDAD', 'NOMBRE DEL CLIENTE', 'APELLIDOS'],
    'formatoD' => ['NOMBRE', 'CEDULA-RNC', 'DIRECCION']
  ];

  
  // echo "<pre>";
  // print_r($xlsx->rows() );
  // die();
  foreach ($xlsx->rows() as $row) {
    foreach ($formatos as $formato => $headers) {
      if (count(array_intersect($headers, $row)) == count($headers)) {
        return $formato;
      }
    }
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

function procesarFormatoC($empresaId, $urlArchivo) {
  $url = 'http://localhost/fiver/apisubir/indexc1.php'; // URL ajustada
  return enviarDatosPorCurl($url, $empresaId, $urlArchivo);
}

function procesarFormatoD($empresaId, $urlArchivo) {
  $url = 'http://localhost/fiver/apisubir/indexd.php'; // URL ajustada
  return enviarDatosPorCurl($url, $empresaId, $urlArchivo);
}

function enviarDatosPorCurl($url, $empresaId, $urlArchivo) {
  // Creando el cuerpo de la solicitud como una cadena JSON
  $data = [
    'empresa_id' => $empresaId,
    'urlarchivo' => $urlArchivo
];

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

      print_r($result);
      die();
        return json_decode($result, true); // Asumiendo que la respuesta es JSON
    }
}
?>