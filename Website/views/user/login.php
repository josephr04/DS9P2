<?php
session_start();


?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Acceso — NewWays</title>

    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=DM+Sans:wght@300;400;500;600&family=DM+Serif+Display&display=swap" rel="stylesheet">
    <link rel="icon" type="image/png" href="../../assets/newwayslogo.png">

    <style>
        :root {
            --nw-blue:       #0c4ed4;
            --nw-blue-dark:  #0a3fb0;
            --nw-blue-light: #eff6ff;
            --nw-text:       #0f172a;
            --nw-muted:      #64748b;
            --nw-border:     #e2e8f0;
            --nw-surface:    #ffffff;
            --nw-bg:         #f4f7fb;
        }

        *, *::before, *::after { box-sizing: border-box; }

        body {
            font-family: 'DM Sans', sans-serif;
            background: var(--nw-bg);
            color: var(--nw-text);
            min-height: 100vh;
            display: flex;
            flex-direction: column;
        }

        .login-shell {
            flex: 1;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 48px 16px;
        }

        .login-card {
            background: var(--nw-surface);
            border: 1px solid var(--nw-border);
            border-radius: 16px;
            box-shadow: 0 8px 24px rgba(12,78,212,0.07);
            padding: 40px 36px;
            width: 100%;
            max-width: 440px;
            animation: fadeUp 0.4s ease both;
        }

        @keyframes fadeUp {
            from { opacity: 0; transform: translateY(16px); }
            to   { opacity: 1; transform: translateY(0); }
        }

        .card-eyebrow {
            font-size: 0.75rem;
            font-weight: 600;
            letter-spacing: 1.2px;
            text-transform: uppercase;
            color: var(--nw-blue);
            margin-bottom: 4px;
        }
        .card-title {
            font-family: 'DM Serif Display', serif;
            font-size: 1.75rem;
            margin-bottom: 6px;
            line-height: 1.2;
        }
        .card-subtitle {
            font-size: 0.88rem;
            color: var(--nw-muted);
            margin-bottom: 0;
        }

        .nav-pills .nav-link {
            color: var(--nw-muted);
            font-size: 0.88rem;
            transition: all 0.2s;
        }
        .nav-pills .nav-link.active {
            background: var(--nw-surface);
            color: var(--nw-blue);
            box-shadow: 0 2px 6px rgba(12,78,212,0.12);
            font-weight: 600;
        }

        .form-label {
            font-size: 0.82rem;
            font-weight: 500;
            color: #475569;
            margin-bottom: 6px;
        }

        .input-group-text {
            background: var(--nw-blue-light);
            border: 1px solid var(--nw-border);
            border-right: none;
            color: var(--nw-blue);
            border-radius: 8px 0 0 8px;
        }

        .form-control {
            border: 1px solid var(--nw-border);
            border-radius: 0 8px 8px 0;
            font-size: 0.95rem;
            padding: 10px 14px;
        }
        .form-control:focus {
            border-color: var(--nw-blue);
            box-shadow: 0 0 0 3px rgba(12,78,212,0.12);
        }
        .input-group:focus-within .input-group-text {
            border-color: var(--nw-blue);
        }

        .input-group.has-eye .form-control {
            border-radius: 0;
            border-right: none;
        }
        .btn-eye {
            background: var(--nw-blue-light);
            border: 1px solid var(--nw-border);
            border-left: none;
            border-radius: 0 8px 8px 0;
            color: var(--nw-blue);
            padding: 0 14px;
            cursor: pointer;
            transition: background 0.2s;
        }
        .btn-eye:hover { background: #dbeafe; }
        .input-group.has-eye:focus-within .btn-eye { border-color: var(--nw-blue); }

        .btn-login {
            background: var(--nw-blue);
            color: #fff;
            border: none;
            border-radius: 8px;
            padding: 11px;
            font-size: 0.95rem;
            font-weight: 600;
            width: 100%;
            transition: background 0.2s, transform 0.1s;
        }
        .btn-login:hover  { background: var(--nw-blue-dark); color: #fff; }
        .btn-login:active { transform: scale(0.99); }
        .btn-login:disabled { opacity: 0.7; cursor: not-allowed; }

        .alert-box {
            background: #fef2f2;
            border: 1px solid #fecaca;
            color: #b91c1c;
            border-radius: 8px;
            font-size: 0.875rem;
            padding: 10px 14px;
            display: none;
        }
        .alert-box.success {
            background: #f0fdf4;
            border-color: #bbf7d0;
            color: #166534;
        }

        .nw-footer {
            text-align: center;
            padding: 20px;
            font-size: 0.8rem;
            color: var(--nw-muted);
            border-top: 1px solid var(--nw-border);
            margin-top: auto;
        }
    </style>
</head>
<body>

<div class="login-shell">
    <div class="login-card">

        <!-- Tabs -->
        <ul class="nav nav-pills nav-justified mb-4" id="authTabs" role="tablist"
            style="background: var(--nw-blue-light); padding: 4px; border-radius: 8px;">
            <li class="nav-item">
                <button class="nav-link active py-2" id="login-tab"
                    data-bs-toggle="tab" data-bs-target="#login-panel" type="button">
                    Iniciar Sesión
                </button>
            </li>
            <li class="nav-item">
                <button class="nav-link py-2" id="register-tab"
                    data-bs-toggle="tab" data-bs-target="#register-panel" type="button">
                    Registrarse
                </button>
            </li>
        </ul>

        <div class="tab-content">

            <!-- PANEL LOGIN -->
            <div class="tab-pane fade show active" id="login-panel">
                <p class="card-eyebrow">Portal de aspirantes</p>
                <h1 class="card-title">Bienvenido de nuevo</h1>
                <p class="card-subtitle mb-4">Ingresa tus credenciales para continuar</p>

                <div class="alert-box mb-3" id="login-alert">
                    <i class="fa-solid fa-circle-exclamation me-2"></i>
                    <span id="login-msg">Correo o contraseña incorrectos.</span>
                </div>

                <form id="login-form">
                    <div class="mb-3">
                        <label class="form-label">Correo electrónico</label>
                        <div class="input-group">
                            <span class="input-group-text"><i class="fa-regular fa-envelope"></i></span>
                            <input type="email" class="form-control" name="email"
                                placeholder="nombre@ejemplo.com" required autocomplete="email">
                        </div>
                    </div>

                    <div class="mb-3">
                        <label class="form-label">Contraseña</label>
                        <div class="input-group has-eye">
                            <span class="input-group-text"><i class="fa-solid fa-lock"></i></span>
                            <input type="password" class="form-control pw-field" name="password"
                                placeholder="••••••••" required autocomplete="current-password">
                            <button class="btn-eye toggle-pw" type="button">
                                <i class="fa-regular fa-eye"></i>
                            </button>
                        </div>
                    </div>

                    <div class="form-check mb-4">
                        <input class="form-check-input" type="checkbox" id="remember" name="remember">
                        <label class="form-check-label" for="remember" style="font-size:.85rem; color:var(--nw-muted);">
                            Recordarme en este dispositivo
                        </label>
                    </div>

                    <button type="submit" class="btn-login" id="login-btn">
                        Iniciar sesión &nbsp;<i class="fa-solid fa-arrow-right"></i>
                    </button>
                </form>
            </div>

            <!-- PANEL REGISTRO -->
            <div class="tab-pane fade" id="register-panel">
                <p class="card-eyebrow">Portal de aspirantes</p>
                <h1 class="card-title">Crea tu cuenta</h1>
                <p class="card-subtitle mb-4">Completa el formulario para registrarte</p>

                <div class="alert-box mb-3" id="register-alert">
                    <i class="fa-solid fa-circle-exclamation me-2"></i>
                    <span id="register-msg">Error al registrar.</span>
                </div>

                <form id="register-form">
                    <div class="mb-3">
                        <label class="form-label">Nombre de usuario</label>
                        <div class="input-group">
                            <span class="input-group-text"><i class="fa-regular fa-user"></i></span>
                            <input type="text" class="form-control" name="username"
                                placeholder="ej. juan_perez" required autocomplete="username">
                        </div>
                    </div>

                    <div class="mb-3">
                        <label class="form-label">Correo electrónico</label>
                        <div class="input-group">
                            <span class="input-group-text"><i class="fa-regular fa-envelope"></i></span>
                            <input type="email" class="form-control" name="email"
                                placeholder="nombre@ejemplo.com" required autocomplete="email">
                        </div>
                    </div>

                    <div class="mb-3">
                        <label class="form-label">Contraseña</label>
                        <div class="input-group has-eye">
                            <span class="input-group-text"><i class="fa-solid fa-lock"></i></span>
                            <input type="password" class="form-control pw-field" id="reg-pass" name="password"
                                placeholder="Mínimo 6 caracteres" required minlength="6">
                            <button class="btn-eye toggle-pw" type="button">
                                <i class="fa-regular fa-eye"></i>
                            </button>
                        </div>
                    </div>

                    <div class="mb-4">
                        <label class="form-label">Confirmar contraseña</label>
                        <div class="input-group has-eye">
                            <span class="input-group-text"><i class="fa-solid fa-shield-halved"></i></span>
                            <input type="password" class="form-control pw-field" id="reg-confirm" name="confirm_password"
                                placeholder="Repite tu contraseña" required>
                            <button class="btn-eye toggle-pw" type="button">
                                <i class="fa-regular fa-eye"></i>
                            </button>
                        </div>
                    </div>

                    <button type="submit" class="btn-login" id="register-btn">
                        Registrarse &nbsp;<i class="fa-solid fa-user-plus"></i>
                    </button>
                </form>
            </div>

        </div>
    </div>
</div>

<footer class="nw-footer">
    &copy; <?php echo date('Y'); ?> NewWays &mdash; Todos los derechos reservados
</footer>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>

<script>
document.addEventListener('DOMContentLoaded', function() {
    
    // 1. Mostrar/Ocultar contraseña
    document.querySelectorAll('.toggle-pw').forEach(button => {
        button.addEventListener('click', function() {
            const input = this.closest('.input-group').querySelector('.pw-field');
            const icon = this.querySelector('i');
            
            if (input.type === 'password') {
                input.type = 'text';
                icon.classList.replace('fa-eye', 'fa-eye-slash');
            } else {
                input.type = 'password';
                icon.classList.replace('fa-eye-slash', 'fa-eye');
            }
        });
    });

    // RUTA DEL CONTROLADOR
    const controllerUrl = '../../controller/loginController.php'; 

    // 2. Enviar LOGIN mediante AJAX
    const loginForm = document.getElementById('login-form');
    const loginAlert = document.getElementById('login-alert');
    const loginMsg = document.getElementById('login-msg');

    loginForm.addEventListener('submit', function(e) {
        e.preventDefault(); 
        
        const formData = new FormData(loginForm);
        formData.append('action', 'login'); 

        fetch(controllerUrl, {
            method: 'POST',
            body: formData
        })
        .then(res => {
            if (!res.ok) throw new Error("Respuesta del servidor no exitosa");
            return res.json();
        })
        .then(data => {
            if (data.success) {
                loginAlert.className = "alert-box success mb-3";
                loginAlert.style.display = "block";
                loginMsg.textContent = data.message;
                
                setTimeout(() => {
                    window.location.href = data.redirect;
                }, 1200);
            } else {
                loginAlert.className = "alert-box mb-3";
                loginAlert.style.display = "block";
                loginMsg.textContent = data.message;
            }
        })
        .catch(err => {
            console.error("Error en la petición:", err);
            loginAlert.className = "alert-box mb-3";
            loginAlert.style.display = "block";
            loginMsg.textContent = "Error al conectar con el servidor o procesar la solicitud.";
        });
    });

    // 3. Enviar REGISTRO mediante AJAX
    const registerForm = document.getElementById('register-form');
    const registerAlert = document.getElementById('register-alert');
    const registerMsg = document.getElementById('register-msg');

    registerForm.addEventListener('submit', function(e) {
        e.preventDefault();

        const formData = new FormData(registerForm);
        formData.append('action', 'register');

        fetch(controllerUrl, {
            method: 'POST',
            body: formData
        })
        .then(res => {
            if (!res.ok) throw new Error("Respuesta del servidor no exitosa");
            return res.json();
        })
        .then(data => {
            if (data.success) {
                registerAlert.className = "alert-box success mb-3";
                registerAlert.style.display = "block";
                registerMsg.textContent = data.message;
                registerForm.reset();
                
                setTimeout(() => {
                    const loginTab = new bootstrap.Tab(document.getElementById('login-tab'));
                    loginTab.show();
                    registerAlert.style.display = "none";
                }, 2000);
            } else {
                registerAlert.className = "alert-box mb-3";
                registerAlert.style.display = "block";
                registerMsg.textContent = data.message;
            }
        })
        .catch(err => {
            console.error("Error en la petición:", err);
            registerAlert.className = "alert-box mb-3";
            registerAlert.style.display = "block";
            registerMsg.textContent = "Error al conectar con el servidor.";
        });
    });
});
</script>
</body>
</html>