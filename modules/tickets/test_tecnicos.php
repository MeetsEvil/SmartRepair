<?php
// Archivo temporal para verificar técnicos en la base de datos
require_once '../../config/db.php';

echo "<h2>Verificación de Técnicos y Administradores</h2>";

$query = "SELECT u.id_usuario, u.nombre, u.apellido, u.usuario, r.nombre_rol, u.estado
          FROM usuarios u
          INNER JOIN roles r ON u.id_rol = r.id_rol
          ORDER BY r.nombre_rol, u.nombre";

$result = mysqli_query($conexion, $query);

if ($result) {
    echo "<table border='1' cellpadding='10'>";
    echo "<tr><th>ID</th><th>Nombre</th><th>Usuario</th><th>Rol</th><th>Estado</th></tr>";
    
    while ($row = mysqli_fetch_assoc($result)) {
        echo "<tr>";
        echo "<td>" . $row['id_usuario'] . "</td>";
        echo "<td>" . $row['nombre'] . " " . $row['apellido'] . "</td>";
        echo "<td>" . $row['usuario'] . "</td>";
        echo "<td>" . $row['nombre_rol'] . "</td>";
        echo "<td>" . $row['estado'] . "</td>";
        echo "</tr>";
    }
    
    echo "</table>";
} else {
    echo "Error: " . mysqli_error($conexion);
}

echo "<br><br>";
echo "<h3>Técnicos y Administradores Activos (los que deberían aparecer en el select):</h3>";

$query2 = "SELECT u.id_usuario, CONCAT(u.nombre, ' ', u.apellido) as nombre_completo, r.nombre_rol
           FROM usuarios u
           INNER JOIN roles r ON u.id_rol = r.id_rol
           WHERE u.estado = 'Activo' AND (r.nombre_rol = 'Técnico' OR r.nombre_rol = 'Administrador')
           ORDER BY r.nombre_rol, u.nombre, u.apellido";

$result2 = mysqli_query($conexion, $query2);

if ($result2) {
    echo "<table border='1' cellpadding='10'>";
    echo "<tr><th>ID</th><th>Nombre Completo</th><th>Rol</th></tr>";
    
    $count = 0;
    while ($row = mysqli_fetch_assoc($result2)) {
        echo "<tr>";
        echo "<td>" . $row['id_usuario'] . "</td>";
        echo "<td>" . $row['nombre_completo'] . "</td>";
        echo "<td>" . $row['nombre_rol'] . "</td>";
        echo "</tr>";
        $count++;
    }
    
    echo "</table>";
    echo "<p><strong>Total: $count usuarios</strong></p>";
} else {
    echo "Error: " . mysqli_error($conexion);
}
?>
