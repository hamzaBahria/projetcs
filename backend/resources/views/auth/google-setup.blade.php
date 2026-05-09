<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Finaliser votre inscription</title>
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body {
            font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif;
            -webkit-font-smoothing: antialiased;
            color: #333;
        }
        .auth-container {
            display: flex;
            justify-content: center;
            align-items: center;
            min-height: 100vh;
            background: #f5f7fa;
        }
        .auth-card {
            background: white;
            padding: 2.5rem;
            border-radius: 12px;
            box-shadow: 0 4px 6px rgba(0,0,0,0.1);
            width: 100%;
            max-width: 450px;
            text-align: center;
        }
        .icon { font-size: 3rem; margin-bottom: 1rem; }
        .icon-warning { color: #d97706; }
        h1 { margin: 0 0 0.75rem; font-size: 1.5rem; }
        p { color: #666; margin-bottom: 0.5rem; line-height: 1.5; }
        .alert-box {
            background: #fef2f2;
            color: #dc2626;
            padding: 0.75rem;
            border-radius: 8px;
            margin-bottom: 1rem;
            font-size: 0.9rem;
            text-align: left;
            display: none;
        }
        .info-box {
            background: #eef2ff;
            color: #4f46e5;
            padding: 0.75rem;
            border-radius: 8px;
            margin: 1rem 0;
            font-size: 0.85rem;
            text-align: left;
            line-height: 1.5;
        }
        .form-group {
            margin-bottom: 1rem;
            text-align: left;
        }
        .form-group label {
            display: block;
            margin-bottom: 0.5rem;
            font-weight: 500;
        }
        .form-group input {
            width: 100%;
            padding: 0.75rem;
            border: 1px solid #ddd;
            border-radius: 8px;
            font-size: 1rem;
            box-sizing: border-box;
        }
        .form-group input:focus {
            outline: none;
            border-color: #4f46e5;
        }
        .btn-primary {
            display: inline-block;
            width: 100%;
            margin-top: 0.5rem;
            padding: 0.75rem 2rem;
            background: #4f46e5;
            color: white;
            border: none;
            border-radius: 8px;
            font-size: 1rem;
            cursor: pointer;
            text-decoration: none;
        }
        .btn-primary:hover { background: #4338ca; }
        .btn-primary:disabled { opacity: 0.6; cursor: not-allowed; }
        .btn-link {
            display: inline-block;
            margin-top: 1.5rem;
            color: #4f46e5;
            text-decoration: none;
            font-size: 0.9rem;
        }
        .btn-link:hover { text-decoration: underline; }
        .mt-2 { margin-top: 1rem; }
        .divider {
            text-align: center;
            margin: 1.5rem 0;
            border-top: 1px solid #eee;
        }
        .divider span {
            background: white;
            padding: 0 1rem;
            color: #999;
            position: relative;
            top: -0.7rem;
        }
        .success-box {
            display: none;
        }
        .success-box .icon-success { color: #16a34a; }
        .success-box p { color: #666; }
        .hidden { display: none; }
    </style>
</head>
<body>
    <div class="auth-container">
        <div class="auth-card">
            <div id="alertPage">
                <div class="icon icon-warning">&#9993;</div>
                <h1>Vérifiez votre email</h1>
                <p>Un email de confirmation a été envoyé à <strong>{{ $email }}</strong>.</p>
                <p>Cliquez sur le lien dans l'email pour activer votre compte.</p>

                <div class="info-box">
                    Vous pouvez fermer cette page et revenir plus tard. Un lien de vérification vous a été envoyé.
                </div>

                <div class="divider"><span>ou</span></div>

                <div class="form-group">
                    <label for="password">Mot de passe</label>
                    <input type="password" id="password" placeholder="Créez un mot de passe" minlength="8">
                </div>
                <div class="form-group">
                    <label for="password_confirmation">Confirmer le mot de passe</label>
                    <input type="password" id="password_confirmation" placeholder="Confirmez le mot de passe" minlength="8">
                </div>

                <div id="errorBox" class="alert-box"></div>

                <button id="submitBtn" class="btn-primary" onclick="setPassword()">
                    Enregistrer le mot de passe
                </button>

                <div class="mt-2">
                    <a href="{{ config('app.frontend_url') }}/login" class="btn-link">Aller à la connexion</a>
                </div>
            </div>

            <div id="successPage" class="success-box">
                <div class="icon icon-success">&#10004;</div>
                <h1>Mot de passe enregistré !</h1>
                <p>Votre mot de passe a été créé avec succès.</p>
                <p>Vérifiez votre boîte email et cliquez sur le lien de confirmation pour activer votre compte.</p>
                <a href="{{ config('app.frontend_url') }}/login" class="btn-primary">Se connecter</a>
            </div>
        </div>
    </div>

    <script>
        function setPassword() {
            const btn = document.getElementById('submitBtn');
            const errorBox = document.getElementById('errorBox');
            const password = document.getElementById('password').value;
            const confirmation = document.getElementById('password_confirmation').value;

            errorBox.style.display = 'none';

            if (password.length < 8) {
                errorBox.textContent = 'Le mot de passe doit contenir au moins 8 caractères.';
                errorBox.style.display = 'block';
                return;
            }

            if (password !== confirmation) {
                errorBox.textContent = 'Les mots de passe ne correspondent pas.';
                errorBox.style.display = 'block';
                return;
            }

            btn.disabled = true;
            btn.textContent = 'Enregistrement...';

            fetch('/api/auth/google/set-password', {
                method: 'POST',
                headers: { 'Content-Type': 'application/json', 'Accept': 'application/json' },
                body: JSON.stringify({
                    email: '{{ $email }}',
                    password: password,
                    password_confirmation: confirmation,
                }),
            })
            .then(res => res.json().then(data => ({ ok: res.ok, data })))
            .then(({ ok, data }) => {
                if (!ok) {
                    const msg = data.errors?.password?.[0] || data.message || 'Une erreur est survenue.';
                    errorBox.textContent = msg;
                    errorBox.style.display = 'block';
                    return;
                }
                document.getElementById('alertPage').style.display = 'none';
                document.getElementById('successPage').style.display = 'block';
            })
            .catch(() => {
                errorBox.textContent = 'Erreur de connexion. Veuillez réessayer.';
                errorBox.style.display = 'block';
            })
            .finally(() => {
                btn.disabled = false;
                btn.textContent = 'Enregistrer le mot de passe';
            });
        }
    </script>
</body>
</html>