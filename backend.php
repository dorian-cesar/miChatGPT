<?php
// Configuración de la base de datos
$servername = "localhost";
$username = "tu_usuario";
$password = "tu_contraseña";
$dbname = "tu_base_de_datos";

// Crear la conexión
$conn = new mysqli($servername, $username, $password, $dbname);

// Verificar la conexión
if ($conn->connect_error) {
    die("Conexión fallida: " . $conn->connect_error);
}

// Obtener la consulta del usuario
$query = $_POST['query'];

// Información sobre la estructura de la base de datos
$db_structure = "Estructura de la base de datos:
- Tabla 'usuarios': id (INT), nombre (VARCHAR), email (VARCHAR)
- Tabla 'pedidos': id (INT), usuario_id (INT), producto (VARCHAR), cantidad (INT)
- Tabla 'productos': id (INT), nombre (VARCHAR), precio (DECIMAL)
Relaciones:
- 'usuarios' y 'pedidos': 'usuarios.id' = 'pedidos.usuario_id'
";

// Llamar a la API de OpenAI para interpretar la consulta
$api_key = 'TU_API_KEY';
$url = 'https://api.openai.com/v1/engines/davinci-codex/completions';

$data = array(
    'prompt' => $db_structure . "\nConvierte esto a SQL: " . $query,
    'max_tokens' => 100
);

$options = array(
    'http' => array(
        'header'  => "Content-type: application/json\r\nAuthorization: Bearer $api_key\r\n",
        'method'  => 'POST',
        'content' => json_encode($data)
    )
);

$context  = stream_context_create($options);
$response = file_get_contents($url, false, $context);
$response_data = json_decode($response, true);
$sql_query = $response_data['choices'][0]['text'];

// Ejecutar la consulta en la base de datos
$result = $conn->query($sql_query);

if ($result->num_rows > 0) {
    // Mostrar los resultados
    $output = "<table class='table table-bordered'><thead><tr>";
    $columns = array_keys($result->fetch_assoc());
    foreach ($columns as $column) {
        $output .= "<th>$column</th>";
    }
    $output .= "</tr></thead><tbody>";

    // Reiniciar el puntero de resultado y recorrer filas
    $result->data_seek(0);
    while ($row = $result->fetch_assoc()) {
        $output .= "<tr>";
        foreach ($row as $data) {
            $output .= "<td>$data</td>";
        }
        $output .= "</tr>";
    }
    $output .= "</tbody></table>";
} else {
    $output = "No se encontraron resultados.";
}

$conn->close();

echo $output;
?>
