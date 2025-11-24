<?php
require_once __DIR__ . '/../../config/database.php';


if (isset($_SESSION['id_usuario'])) {
    // Ajusta la ruta si es necesaria, según tu estructura de carpetas
    header("Location: ../../index.php"); 
    exit();
}

$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = $_POST['username']; // El name del input debe ser 'username'
    $password = $_POST['password'];

    if (empty($username) || empty($password)) {
        $error = 'Por favor, completa ambos campos.';
    } else {
        // Consulta SQL incluyendo la moneda
        $stmt = $conexion->prepare("SELECT id_usuario, nombre_completo, password, id_rol, moneda FROM usuarios WHERE username = ? AND activo = 1");
        $stmt->bind_param("s", $username);
        $stmt->execute();
        $resultado = $stmt->get_result();

        if ($resultado->num_rows === 1) {
            $usuario = $resultado->fetch_assoc();
            
            if (password_verify($password, $usuario['password'])) {
                // Iniciar sesión
                $_SESSION['id_usuario'] = $usuario['id_usuario'];
                $_SESSION['nombre_usuario'] = $usuario['nombre_completo'];
                $_SESSION['id_rol'] = $usuario['id_rol'];
                $_SESSION['moneda_usuario'] = $usuario['moneda'];

                // Cargar permisos
                $stmt_permisos = $conexion->prepare("SELECT p.nombre_permiso FROM rol_permiso rp JOIN permisos p ON rp.id_permiso = p.id_permiso WHERE rp.id_rol = ?");
                $stmt_permisos->bind_param("i", $usuario['id_rol']);
                $stmt_permisos->execute();
                $resultado_permisos = $stmt_permisos->get_result();
                $permisos = [];
                while ($fila = $resultado_permisos->fetch_assoc()) {
                    $permisos[] = $fila['nombre_permiso'];
                }
                $_SESSION['permisos'] = $permisos;
                $stmt_permisos->close();
                
                // Redirección exitosa
                header("Location: ../../index.php"); // Ajusta esta ruta a tu panel principal
                exit();

            } else {
                $error = 'Contraseña incorrecta.';
            }
        } else {
            $error = 'Usuario no encontrado o inactivo.';
        }
        $stmt->close();
    }
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login - GestiónPro</title>
    
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet"/>
    <link href="https://fonts.googleapis.com/css?family=Roboto:300,400,500,700&display=swap" rel="stylesheet"/>
    <link href="https://cdnjs.cloudflare.com/ajax/libs/mdb-ui-kit/6.4.2/mdb.min.css" rel="stylesheet"/>
    
    <style>
        .divider:after,
        .divider:before {
            content: "";
            flex: 1;
            height: 1px;
            background: #eee;
        }
        .h-custom {
            height: calc(100% - 73px);
        }
        @media (max-width: 450px) {
            .h-custom {
                height: 100%;
            }
        }
        .password-container {
            position: relative;
        }
        .password-toggle {
            position: absolute;
            right: 10px;
            top: 50%;
            transform: translateY(-50%);
            cursor: pointer;
            color: #6c757d;
            z-index: 10;
        }
        /* Ajuste para que el icono no se superponga con el label flotante si usas MDB */
        .form-outline .password-toggle {
            top: 50%;
        }
    </style>
</head>
<body>
<section class="vh-100">
  <div class="container-fluid h-custom">
    <div class="row d-flex justify-content-center align-items-center h-100">
      <div class="col-md-9 col-lg-6 col-xl-5">
        <img src="../img/logo.png"
          class="img-fluid" alt="Sample image">
      </div>
      <div class="col-md-8 col-lg-6 col-xl-4 offset-xl-1">
        
        <form id="loginForm" method="POST" action="login.php">
          
          <div class="d-flex flex-row align-items-center justify-content-center justify-content-lg-start mb-4">
            <p class="lead fw-normal mb-0 me-3">Iniciar Sesión</p>
          </div>

          <?php if ($error): ?>
            <div class="alert alert-danger" role="alert">
                <i class="fas fa-exclamation-circle me-2"></i> <?php echo $error; ?>
            </div>
          <?php endif; ?>

          <div class="form-outline mb-4">
            <input type="text" id="form3Example3" name="username" class="form-control form-control-lg"
              placeholder="Ingresa tu usuario" required />
            <label class="form-label" for="form3Example3">Usuario</label>
          </div>

          <div class="form-outline mb-3 password-container">
            <input type="password" id="form3Example4" name="password" class="form-control form-control-lg"
              placeholder="Ingresa tu contraseña" required />
            <i class="fas fa-eye password-toggle" id="togglePassword"></i>
            <label class="form-label" for="form3Example4">Contraseña</label>
          </div>

          <div class="d-flex justify-content-between align-items-center">
            <div class="form-check mb-0">
              <input class="form-check-input me-2" type="checkbox" value="" id="form2Example3" />
              <label class="form-check-label" for="form2Example3">
                Recordarme
              </label>
            </div>
           
          </div>

          <div class="text-center text-lg-start mt-4 pt-2">
            <button type="submit" class="btn btn-primary btn-lg"
              style="padding-left: 2.5rem; padding-right: 2.5rem;">Ingresar</button>
          </div>

        </form>
      </div>
    </div>
  </div>
  
  <div class="d-flex flex-column flex-md-row text-center text-md-start justify-content-between py-4 px-4 px-xl-5 bg-primary">
    <div class="text-white mb-3 mb-md-0">
      Copyright © 2025. <b>TechFusion Data</b>. All rights reserved.
    </div>
    <div>
      <a href="https://www.facebook.com/TechFusionData" class="text-white me-4">
        <i class="fab fa-facebook-f"></i>
      </a>
      
    </div>
    </div>
</section>

<script type="text/javascript" src="https://cdnjs.cloudflare.com/ajax/libs/mdb-ui-kit/6.4.2/mdb.min.js"></script>

<script>
    document.getElementById('togglePassword').addEventListener('click', function () {
      const passwordInput = document.getElementById('form3Example4');
      const icon = this;
      if (passwordInput.type === 'password') {
        passwordInput.type = 'text';
        icon.classList.remove('fa-eye');
        icon.classList.add('fa-eye-slash');
      } else {
        passwordInput.type = 'password';
        icon.classList.remove('fa-eye-slash');
        icon.classList.add('fa-eye');
      }
    });
</script>

</body>
</html>