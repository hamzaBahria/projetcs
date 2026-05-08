<!DOCTYPE html>
<html>
<head>
    <title>Confirmation d'email</title>
</head>
<body>
    <h1>Bonjour !</h1>
    <p>Cliquez sur le lien ci-dessous pour confirmer votre adresse email :</p>
    <a href="{{ $verificationUrl }}">Confirmer mon email</a>
    <p>Ce lien expire dans 60 minutes.</p>
</body>
</html>