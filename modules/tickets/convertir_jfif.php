<?php
require_once '../../config/db.php';

echo "<h2>Convertir Imágenes JFIF a JPG</h2>";

// Obtener tickets con fotos JFIF
$query = "SELECT id_ticket, codigo_ticket, foto_url FROM tickets WHERE foto_url LIKE '%.jfif'";
$result = mysqli_query($conexion, $query);

if ($result && mysqli_num_rows($result) > 0) {
    echo "<p>Convirtiendo imágenes JFIF a JPG...</p>";
    
    while ($row = mysqli_fetch_assoc($result)) {
        $ruta_vieja = "../../" . $row['foto_url'];
        
        if (file_exists($ruta_vieja)) {
            // Crear nueva ruta con extensión .jpg
            $ruta_nueva = str_replace('.jfif', '.jpg', $ruta_vieja);
            $foto_url_nueva = str_replace('.jfif', '.jpg', $row['foto_url']);
            
            // Cargar imagen JFIF
            $imagen = @imagecreatefromjpeg($ruta_vieja);
            
            if ($imagen !== false) {
                // Guardar como JPG
                if (imagejpeg($imagen, $ruta_nueva, 90)) {
                    // Actualizar base de datos
                    $id_ticket = $row['id_ticket'];
                    $update_query = "UPDATE tickets SET foto_url = '$foto_url_nueva' WHERE id_ticket = $id_ticket";
                    
                    if (mysqli_query($conexion, $update_query)) {
                        echo "<p style='color: green;'>✓ Convertido: " . htmlspecialchars($row['codigo_ticket']) . "</p>";
                        
                        // Eliminar archivo JFIF viejo
                        @unlink($ruta_vieja);
                    } else {
                        echo "<p style='color: red;'>✗ Error al actualizar BD: " . htmlspecialchars($row['codigo_ticket']) . "</p>";
                    }
                }
                imagedestroy($imagen);
            } else {
                echo "<p style='color: red;'>✗ No se pudo cargar la imagen: " . htmlspecialchars($row['codigo_ticket']) . "</p>";
            }
        } else {
            echo "<p style='color: orange;'>⚠ Archivo no encontrado: " . htmlspecialchars($row['codigo_ticket']) . "</p>";
        }
    }
    
    echo "<hr>";
    echo "<p><strong>Conversión completada. <a href='test_foto.php'>Ver resultado</a></strong></p>";
} else {
    echo "<p>No se encontraron imágenes JFIF para convertir.</p>";
}
?>
