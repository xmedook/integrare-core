<?php
/**
 * Template: Authentication
 * Route: /tienda/auth/
 * Views: login (default), registro, recuperar, reset
 */

$vista = sanitize_text_field( $_GET['vista'] ?? 'login' );
$reset_key   = sanitize_text_field( $_GET['key'] ?? '' );
$reset_login = sanitize_text_field( $_GET['login'] ?? '' );

$google_enabled    = get_option( 'integrare_google_oauth_enabled', '0' ) === '1';
$microsoft_enabled = get_option( 'integrare_microsoft_oauth_enabled', '0' ) === '1';
$any_oauth         = $google_enabled || $microsoft_enabled;

$microsoft_auth_url = $microsoft_enabled ? Integrare_Auth::get_microsoft_auth_url() : '';
$oauth_error        = ! empty( $_GET['oauth_error'] );
?>

<section class="int-auth-wrapper">

    <!-- ── Logo ──────────────────────────────── -->
    <div class="int-auth-logo">
        <img src="https://integrare.mx/wp-content/uploads/2025/11/integrare-solo.svg" alt="Integrare" height="32">
    </div>

    <div class="int-auth-card">

        <!-- ── Feedback message ──────────────────── -->
        <div class="int-auth-feedback<?php echo $oauth_error ? ' error' : ''; ?>" id="intAuthFeedback" style="<?php echo $oauth_error ? '' : 'display:none;'; ?>"><?php if ( $oauth_error ) echo 'Error al iniciar sesion. Intenta de nuevo.'; ?></div>

        <!-- ══════════════════════════════════════════
             LOGIN VIEW
             ══════════════════════════════════════════ -->
        <div class="int-auth-view <?php echo $vista === 'login' ? 'active' : ''; ?>" id="intViewLogin" data-view="login">
            <h2>Iniciar Sesión</h2>
            <p class="int-auth-subtitle">Accede a tu cuenta para ver precios y realizar pedidos</p>

            <form id="intLoginForm" autocomplete="on">
                <div class="int-auth-field">
                    <label for="intLoginEmail">Correo Electrónico</label>
                    <input type="email" id="intLoginEmail" name="email" placeholder="tu@correo.com" required autocomplete="email">
                </div>

                <div class="int-auth-field">
                    <label for="intLoginPassword">Contraseña</label>
                    <div class="int-auth-password-wrap">
                        <input type="password" id="intLoginPassword" name="password" placeholder="Tu contraseña" required autocomplete="current-password">
                        <button type="button" class="int-auth-eye" data-target="intLoginPassword" aria-label="Mostrar contraseña">
                            <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                <path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"/><circle cx="12" cy="12" r="3"/>
                            </svg>
                        </button>
                    </div>
                </div>

                <div class="int-auth-options">
                    <label class="int-auth-checkbox">
                        <input type="checkbox" name="remember" id="intLoginRemember">
                        <span>Recordar mi sesión</span>
                    </label>
                    <a href="#" class="int-auth-link" data-goto="recuperar">¿Olvidaste tu contraseña?</a>
                </div>

                <button type="submit" class="int-btn int-btn-primary int-btn-full int-auth-submit" id="intLoginBtn">
                    Iniciar Sesión
                </button>
            </form>

            <?php if ( $any_oauth ) : ?>
            <!-- OAuth Buttons -->
            <div class="int-auth-divider">
                <span>o continua con</span>
            </div>
            <div class="int-auth-oauth">
                <?php if ( $google_enabled ) : ?>
                <button type="button" class="int-auth-oauth-btn" id="intGoogleLoginBtn">
                    <svg width="18" height="18" viewBox="0 0 24 24">
                        <path d="M22.56 12.25c0-.78-.07-1.53-.2-2.25H12v4.26h5.92a5.06 5.06 0 01-2.2 3.32v2.77h3.57c2.08-1.92 3.28-4.74 3.28-8.1z" fill="#4285F4"/>
                        <path d="M12 23c2.97 0 5.46-.98 7.28-2.66l-3.57-2.77c-.98.66-2.23 1.06-3.71 1.06-2.86 0-5.29-1.93-6.16-4.53H2.18v2.84C3.99 20.53 7.7 23 12 23z" fill="#34A853"/>
                        <path d="M5.84 14.09c-.22-.66-.35-1.36-.35-2.09s.13-1.43.35-2.09V7.07H2.18C1.43 8.55 1 10.22 1 12s.43 3.45 1.18 4.93l2.85-2.22.81-.62z" fill="#FBBC05"/>
                        <path d="M12 5.38c1.62 0 3.06.56 4.21 1.64l3.15-3.15C17.45 2.09 14.97 1 12 1 7.7 1 3.99 3.47 2.18 7.07l3.66 2.84c.87-2.6 3.3-4.53 6.16-4.53z" fill="#EA4335"/>
                    </svg>
                    Google
                </button>
                <?php endif; ?>
                <?php if ( $microsoft_enabled && $microsoft_auth_url ) : ?>
                <a href="<?php echo esc_url( $microsoft_auth_url ); ?>" class="int-auth-oauth-btn">
                    <svg width="18" height="18" viewBox="0 0 23 23">
                        <rect x="1" y="1" width="10" height="10" fill="#F25022"/>
                        <rect x="12" y="1" width="10" height="10" fill="#7FBA00"/>
                        <rect x="1" y="12" width="10" height="10" fill="#00A4EF"/>
                        <rect x="12" y="12" width="10" height="10" fill="#FFB900"/>
                    </svg>
                    Microsoft
                </a>
                <?php endif; ?>
            </div>
            <?php endif; ?>

            <p class="int-auth-switch">
                ¿No tienes cuenta? <a href="#" class="int-auth-link" data-goto="registro">Regístrate aquí</a>
            </p>
        </div>

        <!-- ══════════════════════════════════════════
             REGISTER VIEW
             ══════════════════════════════════════════ -->
        <div class="int-auth-view <?php echo $vista === 'registro' ? 'active' : ''; ?>" id="intViewRegistro" data-view="registro">
            <h2>Crear Cuenta</h2>
            <p class="int-auth-subtitle">Regístrate para acceder a precios exclusivos</p>

            <form id="intRegisterForm" autocomplete="on">
                <!-- Honeypot anti-spam -->
                <div style="position:absolute;left:-9999px;" aria-hidden="true">
                    <input type="text" name="website_url" tabindex="-1" autocomplete="off">
                </div>

                <div class="int-auth-field-row">
                    <div class="int-auth-field">
                        <label for="intRegFirstName">Nombre</label>
                        <input type="text" id="intRegFirstName" name="first_name" placeholder="Tu nombre" required autocomplete="given-name">
                    </div>
                    <div class="int-auth-field">
                        <label for="intRegLastName">Apellido</label>
                        <input type="text" id="intRegLastName" name="last_name" placeholder="Tu apellido" required autocomplete="family-name">
                    </div>
                </div>

                <div class="int-auth-field">
                    <label for="intRegEmail">Correo Electrónico</label>
                    <input type="email" id="intRegEmail" name="email" placeholder="tu@correo.com" required autocomplete="email">
                </div>

                <div class="int-auth-field">
                    <label for="intRegPassword">Contraseña</label>
                    <div class="int-auth-password-wrap">
                        <input type="password" id="intRegPassword" name="password" placeholder="Mínimo 8 caracteres" required autocomplete="new-password">
                        <button type="button" class="int-auth-eye" data-target="intRegPassword" aria-label="Mostrar contraseña">
                            <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                <path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"/><circle cx="12" cy="12" r="3"/>
                            </svg>
                        </button>
                    </div>
                    <div class="int-auth-password-strength" id="intPasswordStrength">
                        <div class="int-auth-strength-bar"><div class="int-auth-strength-fill" id="intStrengthFill"></div></div>
                        <span class="int-auth-strength-text" id="intStrengthText"></span>
                    </div>
                </div>

                <div class="int-auth-field">
                    <label for="intRegPasswordConfirm">Repetir Contraseña</label>
                    <div class="int-auth-password-wrap">
                        <input type="password" id="intRegPasswordConfirm" name="password_confirm" placeholder="Confirma tu contraseña" required autocomplete="new-password">
                        <button type="button" class="int-auth-eye" data-target="intRegPasswordConfirm" aria-label="Mostrar contraseña">
                            <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                <path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"/><circle cx="12" cy="12" r="3"/>
                            </svg>
                        </button>
                    </div>
                </div>

                <button type="submit" class="int-btn int-btn-primary int-btn-full int-auth-submit" id="intRegisterBtn">
                    Crear Cuenta
                </button>
            </form>

            <?php if ( $any_oauth ) : ?>
            <!-- OAuth Buttons -->
            <div class="int-auth-divider">
                <span>o continua con</span>
            </div>
            <div class="int-auth-oauth">
                <?php if ( $google_enabled ) : ?>
                <button type="button" class="int-auth-oauth-btn" onclick="intGoogleLogin()">
                    <svg width="18" height="18" viewBox="0 0 24 24">
                        <path d="M22.56 12.25c0-.78-.07-1.53-.2-2.25H12v4.26h5.92a5.06 5.06 0 01-2.2 3.32v2.77h3.57c2.08-1.92 3.28-4.74 3.28-8.1z" fill="#4285F4"/>
                        <path d="M12 23c2.97 0 5.46-.98 7.28-2.66l-3.57-2.77c-.98.66-2.23 1.06-3.71 1.06-2.86 0-5.29-1.93-6.16-4.53H2.18v2.84C3.99 20.53 7.7 23 12 23z" fill="#34A853"/>
                        <path d="M5.84 14.09c-.22-.66-.35-1.36-.35-2.09s.13-1.43.35-2.09V7.07H2.18C1.43 8.55 1 10.22 1 12s.43 3.45 1.18 4.93l2.85-2.22.81-.62z" fill="#FBBC05"/>
                        <path d="M12 5.38c1.62 0 3.06.56 4.21 1.64l3.15-3.15C17.45 2.09 14.97 1 12 1 7.7 1 3.99 3.47 2.18 7.07l3.66 2.84c.87-2.6 3.3-4.53 6.16-4.53z" fill="#EA4335"/>
                    </svg>
                    Google
                </button>
                <?php endif; ?>
                <?php if ( $microsoft_enabled && $microsoft_auth_url ) : ?>
                <a href="<?php echo esc_url( $microsoft_auth_url ); ?>" class="int-auth-oauth-btn">
                    <svg width="18" height="18" viewBox="0 0 23 23">
                        <rect x="1" y="1" width="10" height="10" fill="#F25022"/>
                        <rect x="12" y="1" width="10" height="10" fill="#7FBA00"/>
                        <rect x="1" y="12" width="10" height="10" fill="#00A4EF"/>
                        <rect x="12" y="12" width="10" height="10" fill="#FFB900"/>
                    </svg>
                    Microsoft
                </a>
                <?php endif; ?>
            </div>
            <?php endif; ?>

            <p class="int-auth-switch">
                ¿Ya tienes cuenta? <a href="#" class="int-auth-link" data-goto="login">Inicia sesión</a>
            </p>
        </div>

        <!-- ══════════════════════════════════════════
             FORGOT PASSWORD VIEW
             ══════════════════════════════════════════ -->
        <div class="int-auth-view <?php echo $vista === 'recuperar' ? 'active' : ''; ?>" id="intViewRecuperar" data-view="recuperar">
            <h2>Recuperar Contraseña</h2>
            <p class="int-auth-subtitle">Te enviaremos un enlace para restablecer tu contraseña</p>

            <form id="intForgotForm" autocomplete="on">
                <div class="int-auth-field">
                    <label for="intForgotEmail">Correo Electrónico</label>
                    <input type="email" id="intForgotEmail" name="email" placeholder="tu@correo.com" required autocomplete="email">
                </div>

                <button type="submit" class="int-btn int-btn-primary int-btn-full int-auth-submit" id="intForgotBtn">
                    Enviar Enlace
                </button>
            </form>

            <p class="int-auth-switch">
                <a href="#" class="int-auth-link" data-goto="login">← Volver al Inicio de Sesión</a>
            </p>
        </div>

        <!-- ══════════════════════════════════════════
             RESET PASSWORD VIEW
             ══════════════════════════════════════════ -->
        <div class="int-auth-view <?php echo $vista === 'reset' ? 'active' : ''; ?>" id="intViewReset" data-view="reset">
            <h2>Nueva Contraseña</h2>
            <p class="int-auth-subtitle">Ingresa tu nueva contraseña</p>

            <form id="intResetForm" autocomplete="off">
                <input type="hidden" id="intResetLogin" value="<?php echo esc_attr( $reset_login ); ?>">
                <input type="hidden" id="intResetKey" value="<?php echo esc_attr( $reset_key ); ?>">

                <div class="int-auth-field">
                    <label for="intResetPassword">Nueva Contraseña</label>
                    <div class="int-auth-password-wrap">
                        <input type="password" id="intResetPassword" name="password" placeholder="Mínimo 8 caracteres" required autocomplete="new-password">
                        <button type="button" class="int-auth-eye" data-target="intResetPassword" aria-label="Mostrar contraseña">
                            <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                <path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"/><circle cx="12" cy="12" r="3"/>
                            </svg>
                        </button>
                    </div>
                </div>

                <div class="int-auth-field">
                    <label for="intResetPasswordConfirm">Confirmar Contraseña</label>
                    <div class="int-auth-password-wrap">
                        <input type="password" id="intResetPasswordConfirm" name="password_confirm" placeholder="Confirma tu contraseña" required autocomplete="new-password">
                        <button type="button" class="int-auth-eye" data-target="intResetPasswordConfirm" aria-label="Mostrar contraseña">
                            <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                <path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"/><circle cx="12" cy="12" r="3"/>
                            </svg>
                        </button>
                    </div>
                </div>

                <button type="submit" class="int-btn int-btn-primary int-btn-full int-auth-submit" id="intResetBtn">
                    Actualizar Contraseña
                </button>
            </form>
        </div>

    </div>
</section>

<!-- ── Auth JavaScript ──────────────────────────────────── -->
<script>
document.addEventListener('DOMContentLoaded', function() {
    var ajaxUrl = integrareData.ajaxUrl;
    var nonce   = integrareData.nonce;

    // ── View Switching ─────────────────────────────────
    document.querySelectorAll('[data-goto]').forEach(function(link) {
        link.addEventListener('click', function(e) {
            e.preventDefault();
            var target = this.getAttribute('data-goto');
            switchView(target);
        });
    });

    function switchView(viewName) {
        document.querySelectorAll('.int-auth-view').forEach(function(v) {
            v.classList.remove('active');
        });
        var el = document.querySelector('[data-view="' + viewName + '"]');
        if (el) el.classList.add('active');
        hideFeedback();
        // Update URL without reload
        var url = new URL(window.location);
        url.searchParams.set('vista', viewName);
        history.replaceState(null, '', url);
    }

    // ── Toggle Password Visibility ─────────────────────
    document.querySelectorAll('.int-auth-eye').forEach(function(btn) {
        btn.addEventListener('click', function() {
            var input = document.getElementById(this.getAttribute('data-target'));
            if (input) {
                var isPass = input.type === 'password';
                input.type = isPass ? 'text' : 'password';
                this.classList.toggle('active', isPass);
            }
        });
    });

    // ── Password Strength Indicator ────────────────────
    var regPassword = document.getElementById('intRegPassword');
    if (regPassword) {
        regPassword.addEventListener('input', function() {
            var val = this.value;
            var strength = 0;
            if (val.length >= 8) strength++;
            if (/[A-Z]/.test(val)) strength++;
            if (/[0-9]/.test(val)) strength++;
            if (/[^A-Za-z0-9]/.test(val)) strength++;

            var fill = document.getElementById('intStrengthFill');
            var text = document.getElementById('intStrengthText');
            var labels = ['', 'Débil', 'Regular', 'Buena', 'Fuerte'];
            var colors = ['', '#EF4444', '#F59E0B', '#3B82F6', '#22C55E'];
            var widths = ['0%', '25%', '50%', '75%', '100%'];

            if (val.length === 0) {
                fill.style.width = '0%';
                text.textContent = '';
                return;
            }

            fill.style.width = widths[strength];
            fill.style.background = colors[strength];
            text.textContent = labels[strength];
            text.style.color = colors[strength];
        });
    }

    // ── Feedback ───────────────────────────────────────
    function showFeedback(msg, isError) {
        var fb = document.getElementById('intAuthFeedback');
        fb.textContent = msg;
        fb.className = 'int-auth-feedback ' + (isError ? 'error' : 'success');
        fb.style.display = 'block';
        // Scroll to feedback
        fb.scrollIntoView({ behavior: 'smooth', block: 'nearest' });
    }

    function hideFeedback() {
        var fb = document.getElementById('intAuthFeedback');
        fb.style.display = 'none';
    }

    // ── AJAX Helper ───────────────────────────────────
    function authAjax(action, formData, btn, callback) {
        var originalText = btn.textContent;
        btn.disabled = true;
        btn.innerHTML = '<span class="int-spinner"></span> Procesando...';
        hideFeedback();

        var params = 'action=' + action + '&nonce=' + encodeURIComponent(nonce);
        for (var key in formData) {
            params += '&' + key + '=' + encodeURIComponent(formData[key]);
        }

        var xhr = new XMLHttpRequest();
        xhr.open('POST', ajaxUrl);
        xhr.setRequestHeader('Content-Type', 'application/x-www-form-urlencoded');
        xhr.onload = function() {
            btn.disabled = false;
            btn.textContent = originalText;
            try {
                var res = JSON.parse(xhr.responseText);
                if (res.success) {
                    showFeedback(res.data.message, false);
                    if (res.data.redirect) {
                        setTimeout(function() { window.location.href = res.data.redirect; }, 1000);
                    }
                    if (callback) callback(res.data);
                } else {
                    showFeedback(res.data.message || 'Error desconocido.', true);
                    // Highlight field with error
                    if (res.data.field) {
                        var errInput = document.querySelector('[name="' + res.data.field + '"]');
                        if (errInput) {
                            errInput.classList.add('int-auth-input-error');
                            errInput.addEventListener('input', function() { this.classList.remove('int-auth-input-error'); }, { once: true });
                        }
                    }
                }
            } catch (e) {
                showFeedback('Error de conexión. Intenta de nuevo.', true);
            }
        };
        xhr.onerror = function() {
            btn.disabled = false;
            btn.textContent = originalText;
            showFeedback('Error de conexión. Intenta de nuevo.', true);
        };
        xhr.send(params);
    }

    // ── Login Form ────────────────────────────────────
    var loginForm = document.getElementById('intLoginForm');
    if (loginForm) {
        loginForm.addEventListener('submit', function(e) {
            e.preventDefault();
            authAjax('integrare_login', {
                email: document.getElementById('intLoginEmail').value,
                password: document.getElementById('intLoginPassword').value,
                remember: document.getElementById('intLoginRemember').checked ? '1' : ''
            }, document.getElementById('intLoginBtn'));
        });
    }

    // ── Register Form ─────────────────────────────────
    var registerForm = document.getElementById('intRegisterForm');
    if (registerForm) {
        registerForm.addEventListener('submit', function(e) {
            e.preventDefault();
            var formEl = this;
            var honeypot = formEl.querySelector('[name="website_url"]');

            authAjax('integrare_register', {
                first_name: document.getElementById('intRegFirstName').value,
                last_name: document.getElementById('intRegLastName').value,
                email: document.getElementById('intRegEmail').value,
                password: document.getElementById('intRegPassword').value,
                password_confirm: document.getElementById('intRegPasswordConfirm').value,
                website_url: honeypot ? honeypot.value : ''
            }, document.getElementById('intRegisterBtn'));
        });
    }

    // ── Forgot Password Form ──────────────────────────
    var forgotForm = document.getElementById('intForgotForm');
    if (forgotForm) {
        forgotForm.addEventListener('submit', function(e) {
            e.preventDefault();
            authAjax('integrare_forgot_password', {
                email: document.getElementById('intForgotEmail').value
            }, document.getElementById('intForgotBtn'));
        });
    }

    // ── Reset Password Form ───────────────────────────
    var resetForm = document.getElementById('intResetForm');
    if (resetForm) {
        resetForm.addEventListener('submit', function(e) {
            e.preventDefault();
            authAjax('integrare_reset_password', {
                login: document.getElementById('intResetLogin').value,
                key: document.getElementById('intResetKey').value,
                password: document.getElementById('intResetPassword').value,
                password_confirm: document.getElementById('intResetPasswordConfirm').value
            }, document.getElementById('intResetBtn'), function(data) {
                if (data.redirect) {
                    setTimeout(function() { switchView('login'); }, 1500);
                }
            });
        });
    }


});

// ── Google Sign-In (GIS Popup Flow) ──────────────────
var intGoogleClient = null;

function intGoogleLogin() {
    if (!intGoogleClient) {
        if (typeof google === 'undefined' || !google.accounts) {
            alert('Error: Google Sign-In no se cargó. Recarga la página.');
            return;
        }
        intGoogleClient = google.accounts.oauth2.initCodeClient({
            client_id: integrareData.googleClientId,
            scope: 'openid email profile',
            ux_mode: 'popup',
            callback: function(response) {
                if (response.error) {
                    document.getElementById('intAuthFeedback').textContent = 'Error al iniciar sesión con Google.';
                    document.getElementById('intAuthFeedback').className = 'int-auth-feedback error';
                    document.getElementById('intAuthFeedback').style.display = 'block';
                    return;
                }
                // Send authorization code to server via AJAX POST
                var xhr = new XMLHttpRequest();
                xhr.open('POST', integrareData.ajaxUrl);
                xhr.setRequestHeader('Content-Type', 'application/x-www-form-urlencoded');
                xhr.onload = function() {
                    try {
                        var res = JSON.parse(xhr.responseText);
                        if (res.success && res.data.redirect) {
                            window.location.href = res.data.redirect;
                        } else {
                            document.getElementById('intAuthFeedback').textContent = (res.data && res.data.message) || 'Error de autenticación.';
                            document.getElementById('intAuthFeedback').className = 'int-auth-feedback error';
                            document.getElementById('intAuthFeedback').style.display = 'block';
                        }
                    } catch (e) {
                        document.getElementById('intAuthFeedback').textContent = 'Error de conexión.';
                        document.getElementById('intAuthFeedback').className = 'int-auth-feedback error';
                        document.getElementById('intAuthFeedback').style.display = 'block';
                    }
                };
                xhr.onerror = function() {
                    document.getElementById('intAuthFeedback').textContent = 'Error de conexión.';
                    document.getElementById('intAuthFeedback').className = 'int-auth-feedback error';
                    document.getElementById('intAuthFeedback').style.display = 'block';
                };
                xhr.send('action=integrare_google_login&nonce=' + encodeURIComponent(integrareData.nonce) + '&code=' + encodeURIComponent(response.code));
            }
        });
    }
    intGoogleClient.requestCode();
}

// Also attach to login view Google button
document.addEventListener('DOMContentLoaded', function() {
    var loginGoogleBtn = document.getElementById('intGoogleLoginBtn');
    if (loginGoogleBtn) {
        loginGoogleBtn.addEventListener('click', intGoogleLogin);
    }
});
</script>
