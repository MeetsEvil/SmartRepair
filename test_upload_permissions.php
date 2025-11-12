<?php
/**
 * Script de prueba para verificar permisos de subida de imágenes
 * Ejecutar desde el navegador: http://localhost/tu-proyecto/test_upload_permissions.php
 */

echo "<h2>Prueba de Permisos - Sistema de Imágenes de Máquinas</h2>";
echo "<hr>";

// Verificar si la carpeta existe
$carpeta = 'imgMaquinas';
echo "<h3>1. Verificación de Carpeta</h3>";

if (file_exists($carpeta)) {
    echo "✅ La carpeta '$carpeta' existe<br>";
    
    // Verificar permisos
    if (is_writable($carpeta)) {
        echo "✅ La carpeta tiene permisos de escritura<br>";
    } else {
        echo "❌ La carpeta NO tiene permisos de escritura<br>";
        echo "<strong>Solución:</strong> Ejecuta: <code>chmod 755 $carpeta</code><br>";
    }
    
    if (is_readable($carpeta)) {
        echo "✅ La carpeta tiene permisos de lectura<br>";
    } else {
        echo "❌ La carpeta NO tiene permisos de lectura<br>";
    }
} else {
    echo "❌ La carpeta '$carpeta' NO existe<br>";
    echo "<strong>Creando carpeta...</strong><br>";
    
    if (mkdir($carpeta, 0755, true)) {
        echo "✅ Carpeta creada exitosamente<br>";
    } else {
        echo "❌ No se pudo crear la carpeta<br>";
    }
}

echo "<hr>";

// Verificar imagen por defecto
echo "<h3>2. Verificación de Imagen Por Defecto</h3>";
$imagen_defecto = $carpeta . '/no-maquina.png';

if (file_exists($imagen_defecto)) {
    echo "✅ La imagen por defecto existe: $imagen_defecto<br>";
    echo "Tamaño: " . round(filesize($imagen_defecto) / 1024, 2) . " KB<br>";
} else {
    echo "⚠️ La imagen por defecto NO existe: $imagen_defecto<br>";
    echo "<strong>Acción requerida:</strong> Coloca una imagen llamada 'no-maquina.png' en la carpeta '$carpeta'<br>";
}

echo "<hr>";

// Probar escritura
echo "<h3>3. Prueba de Escritura</h3>";
$archivo_prueba = $carpeta . '/test_write.txt';

if (file_put_contents($archivo_prueba, 'Prueba de escritura - ' . date('Y-m-d H:i:s'))) {
    echo "✅ Se puede escribir en la carpeta<br>";
    echo "Archivo de prueba creado: $archivo_prueba<br>";
    
    // Eliminar archivo de prueba
    if (unlink($archivo_prueba)) {
        echo "✅ Archivo de prueba eliminado correctamente<br>";
    }
} else {
    echo "❌ NO se puede escribir en la carpeta<br>";
    echo "<strong>Solución:</strong><br>";
    echo "- Linux/Mac: <code>chmod 755 $carpeta</code><br>";
    echo "- Windows: Dar permisos de escritura desde propiedades de la carpeta<br>";
}

echo "<hr>";

// Información del servidor
echo "<h3>4. Información del Servidor</h3>";
echo "Sistema Operativo: " . PHP_OS . "<br>";
echo "Versión PHP: " . PHP_VERSION . "<br>";
echo "Usuario del servidor: " . get_current_user() . "<br>";
echo "Límite de subida: " . ini_get('upload_max_filesize') . "<br>";
echo "Límite POST: " . ini_get('post_max_size') . "<br>";
echo "Directorio actual: " . getcwd() . "<br>";

echo "<hr>";

// Resumen
echo "<h3>5. Resumen</h3>";
$todo_ok = file_exists($carpeta) && is_writable($carpeta) && is_readable($carpeta);

if ($todo_ok) {
    echo "<div style='background: #d4edda; color: #155724; padding: 15px; border-radius: 5px;'>";
    echo "✅ <strong>Todo está configurado correctamente</strong><br>";
    echo "El sistema de subida de imágenes debería funcionar sin problemas.";
    echo "</div>";
} else {
    echo "<div style='background: #f8d7da; color: #721c24; padding: 15px; border-radius: 5px;'>";
    echo "❌ <strong>Hay problemas de configuración</strong><br>";
    echo "Revisa los puntos marcados con ❌ arriba y aplica las soluciones sugeridas.";
    echo "</div>";
}

echo "<hr>";
echo "<p><small>Puedes eliminar este archivo después de verificar que todo funciona correctamente.</small></p>";
?>
