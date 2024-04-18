<?php

function getFileName() {
    $mes_actual = date('n');
    date_default_timezone_set('UTC');
    $hoy = date("H:i:s");
    echo "Hola Panda Consulta y almacenamiento de archivo '$hoy'.\n";
    return "respuesta_$mes_actual.json";
}


function guardarRespuestaEnArchivo($url) {
    date_default_timezone_set('UTC');
    $hoy = date("H:i:s");
    echo "Hola Panda consulta Servicio Web'$hoy'.\n";
    $curl = curl_init();
    curl_setopt_array($curl, array(
        CURLOPT_URL => $url,
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_ENCODING => '',
        CURLOPT_MAXREDIRS => 10,
        CURLOPT_TIMEOUT => 0,
        CURLOPT_FOLLOWLOCATION => true,
        CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
        CURLOPT_CUSTOMREQUEST => 'GET',
        CURLOPT_HTTPHEADER => array(
            'Authorization: Basic ODAzOldlbGxjb21lMjE='
        ),
    ));

    $response = curl_exec($curl);

    if(curl_errno($curl)){
        echo 'Error: ' . curl_error($curl);
        return false; 
    }

    curl_close($curl);
    $hoy = date("H:i:s");
    $file_name = getFileName();
    file_put_contents($file_name, $response);
    echo "La respuesta se ha almacenado en el archivo '$file_name' en formato JSON '$hoy'.\n";

    return true;
}

$url = 'http://181.188.219.28:8015/Aseguradora/getListado?anio=2024&mes=' . date('n');
$response = guardarRespuestaEnArchivo($url);

if ($response !== false) {
    $file_json = getFileName();
    $response = file_get_contents($file_json);

    $array = json_decode($response, TRUE);

    $total = count($array[0]["losItems"]);

    $pdo = new PDO("mysql:host=50.87.151.158:3306;dbname=olandseg_olaplus", "olandseg_ola", "UJW)(]s82*hg");
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    $stmt = $pdo->query("SELECT COUNT(*) FROM `Facturas`");
    $current_total = $stmt->fetchColumn();

    $new_records_count = $total - $current_total;

    if ($new_records_count > 0) {
        $items = $array[0]["losItems"];
        for ($i = $current_total; $i < $total ; $i++) {
            $current_item = $items[$i - $current_total]; 

            $sql = "INSERT INTO `Facturas` (`LineNum`, `LocalS`, `FacturaS`, `OrigenS`, `FechaS`, `NombreClienteS`, `Cedula`, `E_Mail`, `Telefono`, `CodigoS`, `PreuniS`, `NombreArS`, `CantidadS`, `FechaNac`, `MarcaS`, `GrupoS`, `AsesorS`, `OptometraS`, `Direccion`, `Ciudad`, `CodlocalS`, `Neto`, `NetoComision`, `AñoS`, `MesS`, `DiaS`, `ProvinciaS`, `Olaplus`, `ItemCode`, `ItemName`, `ComisionS`, `ComisionOK`)
            VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";
            $stmt = $pdo->prepare($sql);

            $stmt->execute([
                $current_item['LineNum'],
                $current_item['LocalS'],
                $current_item['FacturaS'],
                $current_item['OrigenS'],
                $current_item['FechaS'],
                $current_item['NombreClienteS'],
                $current_item['Cedula'],
                $current_item['E_Mail'],
                $current_item['Telefono'],
                $current_item['CodigoS'],
                $current_item['PreuniS'],
                $current_item['NombreArS'],
                $current_item['CantidadS'],
                $current_item['FechaNac'],
                $current_item['MarcaS'],
                $current_item['GrupoS'],
                $current_item['AsesorS'],
                $current_item['OptometraS'],
                $current_item['Direccion'],
                $current_item['Ciudad'],
                $current_item['CodlocalS'],
                $current_item['Neto'],
                $current_item['NetoComision'],
                $current_item['AñoS'],
                $current_item['MesS'],
                $current_item['DiaS'],
                $current_item['ProvinciaS'],
                $current_item['Olaplus'],
                $current_item['ItemCode'],
                $current_item['ItemName'],
                $current_item['ComisionS'],
                $current_item['ComisionOK']
            ]);

            echo "Se ha insertado un nuevo registro en la base de datos.\n";
        }
    } else {
        echo "No hay nuevos registros para insertar.\n";
    }
} else {
    echo "No se pudo obtener la respuesta.\n";
}
?>
