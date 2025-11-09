<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <title>SmartRepair</title>
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
    <link rel="stylesheet" href="../../assets/css/style.css">
</head>

<body>

    <div class="FormCajaLogin">
        <div class="FormLogin">

            <form method="POST" id="frmlogin" class="grupo-entradas" action="login.php">
                <div class="Titulo">
                    <div class="Titulo11">
                        <h1>BIENVENIDO!</h1>
                        <h2>INICIAR SESIÓN</h2>
                    </div>

                    <div class="input-grupo">
                        <i class="fas fa-user icono"></i>
                        <input type="text" name="txtusuario" class="CajaTexto" placeholder="Usuario" autocomplete="off"
                            required>
                    </div>

                    <div class="input-grupo">
                        <i class="fas fa-lock icono"></i>
                        <input type="password" id="password" name="txtpassword" class="CajaTexto"
                            placeholder="Contraseña" autocomplete="off" required>
                        <!-- <i class="fas fa-eye toggle-password" id="togglePassword"></i> -->
                    </div>

                    <div class="recuperarContraseña">
                        <a href="recuperarContraseña.php">¿Olvidaste tu contraseña?</a>
                    </div>


                </div>

                <div>
                    <input type="submit" value="INGRESAR" class="BtnLogin" name="btningresar">
                </div>
            </form>
        </div>
    </div>

</body>

</html>