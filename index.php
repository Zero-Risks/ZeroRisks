<!DOCTYPE html>
<html>

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.1.0/css/all.min.css">
    <link rel="stylesheet" href="/public/assets/css/login.css">
    <title>Iniciar sesión</title>
</head>

<body>
    <div class="container">

        <div class="login-box">
            <div class="logo">
                <img src="/public/assets/img/logo.png" alt="Logo" class="logo-image">
            </div>
            <h1 class="title">¡Bienvenido!</h1>
            <form action="/controllers/validar_login.php" method="post">

                <div class="error-message" id="error-message">
                    <?php
    session_start();
    if (isset($_SESSION['error_message'])) {
        echo '<p class="error">' . $_SESSION['error_message'] . '</p>';
        unset($_SESSION['error_message']); // Limpia el mensaje de error después de mostrarlo
    }
    ?>
                </div>
                <div class="input-container">
                    <label for="email" class="label">Correo electrónico:</label>
                    <input type="email" id="email" name="email" placeholder="Ingresa tu correo electrónico" required>
                </div>
                <div class="input-container">
                    <label for="password" class="label">Contraseña:</label>
                    <div class="password-container">
                        <input type="Password" id="Password" name="contrasena" placeholder="Ingresa tu contraseña"
                            required>
                        <button type="button" id="togglePassword" class="toggle-password">
                            <i class="fas fa-eye"></i>
                        </button>
                    </div>
                </div>
                <button type="submit" class="login-button">Iniciar sesión</button>
            </form>
            <p class="signup-link">Si olvidastes tu contraseña comunicate con un administrador</p>
        </div>
    </div>
    <script>
        const togglePassword = document.getElementById('togglePassword');
        const passwordInput = document.getElementById('Password');

        togglePassword.addEventListener('click', () => {
            const type = passwordInput.getAttribute('type') === 'Password' ? 'text' : 'Password';
            passwordInput.setAttribute('type', type);
            togglePassword.querySelector('i').classList.toggle('fa-eye-slash');
        });
    </script>
    <script>
        // Obtén la referencia al elemento de mensaje de error
        const errorMessage = document.getElementById('error-message');

        // Función para ocultar el mensaje de error después de 5 segundos (5000 milisegundos)
        setTimeout(function () {
            errorMessage.style.display = 'none';
        }, 6000); // Cambia 5000 a la cantidad de milisegundos que desees para ajustar el tiempo de visualización
    </script>
</body>

</html>