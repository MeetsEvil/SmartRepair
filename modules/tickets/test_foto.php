<?php
require_once '../../config/db.php';

echo "<h2>Prueba de Visualización de Fotos</h2>";

// Obtener tickets con fotos
$query = "SELECT id_ticket, codigo_ticket, foto_url FROM tickets WHERE foto_url IS NOT NULL AND foto_url != ''";
$result = mysqli_query($conexion, $query);

if ($result && mysqli_num_rows($result) > 0) {
    echo "<p>Tickets con fotos encontrados:</p>";
    
    while ($row = mysqli_fetch_assoc($result)) {
        echo "<div style='border: 1px solid #ccc; padding: 15px; margin: 10px 0;'>";
        echo "<h3>Ticket: " . htmlspecialchars($row['codigo_ticket']) . "</h3>";
        echo "<p><strong>Ruta en BD:</strong> " . htmlspecialchars($row['foto_url']) . "</p>";
        
        $ruta_completa = "../../" . $row['foto_url'];
        echo "<p><strong>Ruta completa desde este archivo:</strong> " . htmlspecialchars($ruta_completa) . "</p>";
        
        // Verificar si el archivo existe
        if (file_exists($ruta_completa)) {
            echo "<p style='color: green;'><strong>✓ El archivo existe en el servidor</strong></p>";
            
            // Obtener información del archivo
            $file_size = filesize($ruta_completa);
            $file_type = mime_content_type($ruta_completa);
            echo "<p><strong>Tamaño:</strong> " . number_format($file_size / 1024, 2) . " KB</p>";
            echo "<p><strong>Tipo MIME:</strong> " . htmlspecialchars($file_type) . "</p>";
            
            // Intentar mostrar la imagen
            echo "<p><strong>Intentando mostrar imagen:</strong></p>";
            echo "<img src='" . htmlspecialchars($ruta_completa) . "' style='max-width: 300px; border: 2px solid #ddd;' alt='Foto del ticket' onerror='this.style.display=\"none\"; this.nextElementSibling.style.display=\"block\";'>";
            echo "<p style='display:none; color: red;'>✗ Error al cargar la imagen en el navegador</p>";
            
            // Mostrar URL completa para debug
            $url_absoluta = "http://" . $_SERVER['HTTP_HOST'] . dirname($_SERVER['PHP_SELF']) . "/" . $ruta_completa;
            echo "<p><strong>URL completa:</strong> <a href='" . htmlspecialchars($url_absoluta) . "' target='_blank'>" . htmlspecialchars($url_absoluta) . "</a></p>";
        } else {
            echo "<p style='color: red;'><strong>✗ El archivo NO existe en el servidor</strong></p>";
            echo "<p>Buscando en: " . realpath(dirname(__FILE__) . "/../../") . "/" . $row['foto_url'] . "</p>";
        }
        
        echo "</div>";
    }
} else {
    echo "<p>No se encontraron tickets con fotos.</p>";
}

echo "<hr>";
echo "<h3>Verificar estructura de carpetas:</h3>";
$upload_dir = "../../uploads/tickets/";
if (is_dir($upload_dir)) {
    echo "<p style='color: green;'>✓ La carpeta uploads/tickets/ existe</p>";
    
    $files = scandir($upload_dir);
    echo "<p><strong>Archivos en la carpeta:</strong></p>";
    echo "<ul>";
    foreach ($files as $file) {
        if ($file != '.' && $file != '..') {
            echo "<li>" . htmlspecialchars($file) . "</li>";
        }
    }
    echo "</ul>";
} else {
    echo "<p style='color: red;'>✗ La carpeta uploads/tickets/ NO existe</p>";
}
?>
