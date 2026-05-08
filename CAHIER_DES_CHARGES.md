# Cahier des Charges – Application Web avec Authentification

## 1. Présentation du Projet

### 1.1 Contexte
Développement d'une application web moderne intégrant un système d'authentification complet et sécurisé, réalisé dans le cadre d'un projet pédagogique.

### 1.2 Objectifs
- Concevoir une architecture web full-stack robuste
- Implémenter un système d'authentification sécurisé (Token-based)
- Intégrer des services tiers (OAuth2 Google, service email)
- Appliquer les bonnes pratiques de sécurité (hachage, validation, protection des routes)

### 1.3 Stack Technique

| Composant | Technologie choisie |
|---|---|
| Backend | Laravel (PHP) |
| Frontend | Angular (TypeScript) |
| Base de données | MySQL |
| Authentification API | Laravel Sanctum (token-based) |
| Hachage | Bcrypt |
| OAuth2 | Laravel Socialite (Google) |
| Email (dev) | Mailtrap / SMTP |

---

## 2. Spécifications Fonctionnelles

### 2.1 Module Inscription & Validation

| ID | Fonctionnalité | Description |
|---|---|---|
| F-01 | Formulaire d'inscription | Champs : nom, email, mot de passe, confirmation. Validation : email valide, mot de passe >= 8 caractères avec majuscule + chiffre |
| F-02 | Confirmation par email | Envoi d'un email avec lien signé. Compte marqué "inactif" tant que non confirmé |
| F-03 | Validation des champs | Messages d'erreur explicites en temps réel (côté frontend + backend) |

### 2.2 Module Authentification

| ID | Fonctionnalité | Description |
|---|---|---|
| F-04 | Connexion locale | Authentification par email + mot de passe. Vérification que le compte est activé |
| F-05 | Connexion OAuth2 Google | Bouton "Se connecter avec Google". Récupération des infos profil via Socialite |
| F-06 | Gestion de session | Token stocké côté client (localStorage). Intercepteur HTTP attachant le token à chaque requête |
| F-07 | Déconnexion | Révocation du token côté serveur + nettoyage côté client |

### 2.3 Module Profil Utilisateur

| ID | Fonctionnalité | Description |
|---|---|---|
| F-08 | Affichage du profil | Nom, email, avatar, date d'inscription |
| F-09 | Modification du profil | Mise à jour du nom et de l'email |
| F-10 | Avatar | Upload d'image (JPEG/PNG, max 2 Mo). Stockage local (storage Laravel) |

### 2.4 Module Sécurité & Récupération

| ID | Fonctionnalité | Description |
|---|---|---|
| F-11 | Mot de passe oublié | Saisie email -> envoi lien de réinitialisation (token à durée limitée : 60 min) |
| F-12 | Réinitialisation du mot de passe | Saisie nouveau mot de passe + confirmation après validation du token |
| F-13 | Changement de mot de passe | Authentifié : saisie ancien mot de passe -> nouveau mot de passe |

---

## 3. Spécifications Techniques

### 3.1 Architecture Backend (Laravel)

```
backend/
├── app/
│   ├── Http/
│   │   ├── Controllers/
│   │   │   └── Api/
│   │   │       ├── AuthController.php
│   │   │       ├── ProfileController.php
│   │   │       ├── PasswordController.php
│   │   │       ├── EmailVerificationController.php
│   │   │       └── SocialiteController.php
│   │   ├── Middleware/
│   │   │   └── ForceJsonResponse.php
│   │   └── Requests/
│   │       ├── RegisterRequest.php
│   │       ├── LoginRequest.php
│   │       ├── UpdateProfileRequest.php
│   │       └── PasswordChangeRequest.php
│   ├── Mail/
│   │   ├── VerifyEmail.php
│   │   └── ResetPassword.php
│   └── Models/
│       └── User.php
├── config/
│   ├── cors.php
│   ├── sanctum.php
│   └── socialite.php
├── database/
│   └── migrations/
│       ├── create_users_table.php
│       ├── create_password_resets_table.php
│       └── create_personal_access_tokens_table.php
└── routes/
    └── api.php
```

### 3.2 Architecture Frontend (Angular)

```
frontend/
├── src/
│   ├── app/
│   │   ├── components/
│   │   │   ├── login/
│   │   │   ├── register/
│   │   │   ├── verify-email/
│   │   │   ├── profile/
│   │   │   ├── forgot-password/
│   │   │   ├── reset-password/
│   │   │   ├── change-password/
│   │   │   ├── dashboard/
│   │   │   └── navbar/
│   │   ├── services/
│   │   │   ├── auth.service.ts
│   │   │   ├── user.service.ts
│   │   │   └── password.service.ts
│   │   ├── guards/
│   │   │   ├── auth.guard.ts
│   │   │   └── guest.guard.ts
│   │   ├── interceptors/
│   │   │   └── auth.interceptor.ts
│   │   ├── models/
│   │   │   └── user.model.ts
│   │   └── app.routes.ts
│   └── environments/
│       ├── environment.ts
│       └── environment.prod.ts
```

### 3.3 Schéma de la Base de Données

**Table : `users`**

| Colonne | Type | Contraintes |
|---|---|---|
| id | BIGINT UNSIGNED | PK, AUTO_INCREMENT |
| name | VARCHAR(255) | NOT NULL |
| email | VARCHAR(255) | UNIQUE, NOT NULL |
| password | VARCHAR(255) | NULLABLE (nullable pour OAuth) |
| avatar | VARCHAR(255) | NULLABLE |
| google_id | VARCHAR(255) | NULLABLE, UNIQUE |
| email_verified_at | TIMESTAMP | NULLABLE |
| remember_token | VARCHAR(100) | NULLABLE |
| created_at | TIMESTAMP | NULLABLE |
| updated_at | TIMESTAMP | NULLABLE |

**Table : `password_reset_tokens`**

| Colonne | Type | Contraintes |
|---|---|---|
| email | VARCHAR(255) | PRIMARY |
| token | VARCHAR(255) | NOT NULL |
| created_at | TIMESTAMP | NULLABLE |

**Table : `personal_access_tokens`** (Sanctum)

| Colonne | Type | Contraintes |
|---|---|---|
| id | BIGINT UNSIGNED | PK, AUTO_INCREMENT |
| tokenable_type | VARCHAR(255) | NOT NULL |
| tokenable_id | BIGINT UNSIGNED | NOT NULL |
| name | VARCHAR(255) | NOT NULL |
| token | VARCHAR(64) | UNIQUE, NOT NULL |
| abilities | TEXT | NULLABLE |
| last_used_at | TIMESTAMP | NULLABLE |
| expires_at | TIMESTAMP | NULLABLE |
| created_at | TIMESTAMP | NULLABLE |
| updated_at | TIMESTAMP | NULLABLE |

---

## 4. Flux d'Authentification

### 4.1 Inscription

```
[Client] -> POST /api/register
           -> [Laravel] -> Valide les champs
                        -> Hash le mot de passe (Bcrypt)
                        -> Crée l'utilisateur (inactif)
                        -> Génère une URL signée
                        -> Envoie un email de vérification
           -> Réponse : "Vérifiez votre email"
```

### 4.2 Vérification Email

```
[Client] -> GET /api/email/verify/{id}/{hash}
           -> [Laravel] -> Vérifie la signature
                        -> Active le compte (email_verified_at = now())
                        -> Redirige vers la page de confirmation
```

### 4.3 Connexion Locale

```
[Client] -> POST /api/login
           -> [Laravel] -> Vérifie les identifiants
                        -> Vérifie si le compte est activé
                        -> Crée un token Sanctum
           -> Réponse : { user, token }
```

### 4.4 Connexion Google (OAuth2)

```
[Client] -> Redirection vers /api/auth/google
           -> [Laravel] -> Redirige vers Google
           -> [Google] -> Page de consentement
           -> Callback vers /api/auth/google/callback
           -> [Laravel] -> Récupère les infos Google
                        -> Crée ou connecte l'utilisateur
                        -> Crée un token Sanctum
                        -> Redirige vers le frontend avec le token
```

### 4.5 Mot de passe oublié

```
[Client] -> POST /api/password/forgot
           -> [Laravel] -> Vérifie l'email
                        -> Génère un token (60 min)
                        -> Envoie un email avec lien + token

[Client] -> Clique sur le lien -> Page reset-password?token=xxx
         -> POST /api/password/reset
           -> [Laravel] -> Valide token + email
                        -> Met à jour le mot de passe
           -> Réponse : succès
```

### 4.6 Protection des Routes

```
Requête -> AuthInterceptor -> Attache "Authorization: Bearer <token>"
         -> [Laravel] -> Middleware auth:sanctum
                     -> Vérifie le token dans la table personal_access_tokens
                     -> Si valide -> passe la requête
                     -> Si invalide -> 401 Unauthorized
         -> AuthInterceptor -> Capture 401 -> Déconnexion automatique
```

---

## 5. Contraintes Techniques & Sécurité

| # | Règle | Détail |
|---|---|---|
| S-01 | Hachage | Tout mot de passe haché avec Bcrypt (coût 10+) |
| S-02 | Stockage tokens | Tokens stockés dans localStorage côté client, jamais dans les URL |
| S-03 | Fichier .env | Toutes les clés secrètes (DB, Google OAuth, mail, APP_KEY) dans le fichier `.env`, jamais commité |
| S-04 | Validation | Toutes les entrées validées côté serveur (Form Requests Laravel) |
| S-05 | CORS | Seule l'origine Angular autorisée (http://localhost:4200) |
| S-06 | Upload avatar | Types MIME autorisés : image/jpeg, image/png. Taille max : 2 Mo |
| S-07 | Expiration token reset | 60 minutes |
| S-08 | Protection XSS | Angular échappe automatiquement les templates. Laravel échappe les sorties. |
| S-09 | CSRF | Les tokens Sanctum sont liés à l'utilisateur, pas de vulnérabilité CSRF pour les API token-based |

---

## 6. API Endpoints – Récapitulatif

### Routes publiques (sans authentification)

| Méthode | URL | Contrôleur | Description |
|---|---|---|---|
| POST | `/api/register` | AuthController@register | Inscription |
| GET | `/api/email/verify/{id}/{hash}` | EmailVerificationController@verify | Confirmation email |
| POST | `/api/login` | AuthController@login | Connexion |
| POST | `/api/password/forgot` | PasswordController@sendResetLink | Mot de passe oublié |
| POST | `/api/password/reset` | PasswordController@reset | Réinitialisation mot de passe |
| GET | `/api/auth/google` | SocialiteController@redirect | Redirection vers Google OAuth |
| GET | `/api/auth/google/callback` | SocialiteController@callback | Callback Google OAuth |

### Routes protégées (middleware auth:sanctum)

| Méthode | URL | Contrôleur | Description |
|---|---|---|---|
| POST | `/api/logout` | AuthController@logout | Déconnexion |
| GET | `/api/user` | ProfileController@show | Voir profil |
| PUT | `/api/user/update` | ProfileController@update | Modifier profil |
| POST | `/api/user/avatar` | ProfileController@uploadAvatar | Upload avatar |
| PUT | `/api/password/change` | PasswordController@change | Changer mot de passe |

---

## 7. Format des Réponses API

### Succès

```json
{
  "success": true,
  "message": "Inscription réussie. Vérifiez votre email.",
  "data": { ... }
}
```

### Erreur de validation

```json
{
  "success": false,
  "message": "Erreur de validation.",
  "errors": {
    "email": ["L'email est déjà utilisé."],
    "password": ["Le mot de passe doit contenir au moins 8 caractères."]
  }
}
```

### Erreur générique

```json
{
  "success": false,
  "message": "Email ou mot de passe incorrect."
}
```

---

## 8. Plan de Test

| ID | Test | Résultat attendu |
|---|---|---|
| T-01 | Inscription avec email invalide | Erreur : "Email invalide" |
| T-02 | Inscription avec mot de passe faible (6 car.) | Erreur : "Min 8 caractères" |
| T-03 | Inscription valide | Email de confirmation reçu |
| T-04 | Cliquer sur le lien de confirmation | Compte activé, message succès |
| T-05 | Connexion avec email non vérifié | Bloqué : "Veuillez vérifier votre email" |
| T-06 | Connexion avec mauvais mot de passe | Erreur : "Identifiants incorrects" |
| T-07 | Connexion valide | Token reçu, redirection dashboard |
| T-08 | Accès route protégée sans token | 401 Unauthorized |
| T-09 | Accès route protégée avec token valide | Données retournées |
| T-10 | Mise à jour du profil | Données modifiées |
| T-11 | Upload avatar valide | Image stockée, URL retournée |
| T-12 | Upload fichier non-image (ex: .exe) | Erreur : "Type de fichier non autorisé" |
| T-13 | Mot de passe oublié (email existant) | Email de réinitialisation reçu |
| T-14 | Réinitialisation avec token valide | Mot de passe changé, connexion possible |
| T-15 | Réinitialisation avec token expiré | Erreur : "Token invalide ou expiré" |
| T-16 | Changement de mot de passe (ancien correct) | Mot de passe mis à jour |
| T-17 | Changement avec ancien mot de passe faux | Erreur : "Ancien mot de passe incorrect" |
| T-18 | Connexion Google OAuth | Compte créé/connecté, token reçu |
| T-19 | Déconnexion | Token révoqué, accès refusé après |

---

## 9. Plan de Réalisation (Sprints)

### Sprint 1 – Scaffolding
- Installation Laravel + Angular
- Configuration MySQL, CORS, .env
- Installation Sanctum, Socialite

### Sprint 2 – Base de données & Modèle User
- Migration users (avatar, google_id)
- Migration password_reset_tokens (Laravel default)
- Migration personal_access_tokens (Sanctum)
- Configuration du modèle User (HasApiTokens, fillable, casts)

### Sprint 3 – Inscription & Vérification Email (Backend)
- AuthController@register (validation, hash, création user)
- EmailVerificationController@verify (URL signée)
- Mailable VerifyEmail

### Sprint 4 – Connexion, Déconnexion, Profil (Backend)
- AuthController@login (vérification + token)
- AuthController@logout (révocation token)
- ProfileController (show, update, uploadAvatar)

### Sprint 5 – Mot de passe oublié & Changement (Backend)
- PasswordController@sendResetLink
- PasswordController@reset
- PasswordController@change
- Mailable ResetPassword

### Sprint 6 – Google OAuth (Backend)
- SocialiteController (redirect, callback)
- Configuration des credentials Google

### Sprint 7 – Services & Intercepteurs Angular
- AuthService, UserService, PasswordService
- AuthInterceptor (Bearer token + 401 handling)
- AuthGuard, GuestGuard

### Sprint 8 – Pages Auth Angular (Register, Login)
- RegisterComponent (formulaire + validation)
- LoginComponent (email/password + bouton Google)
- VerifyEmailComponent

### Sprint 9 – Pages Profil Angular
- ProfileComponent (affichage + édition)
- Upload avatar avec preview

### Sprint 10 – Pages Password Angular
- ForgotPasswordComponent
- ResetPasswordComponent
- ChangePasswordComponent

### Sprint 11 – Routing & Navigation
- Configuration des routes avec guards
- Navbar (liens conditionnels selon état auth)
- DashboardComponent (page protégée)

### Sprint 12 – Finition & Sécurité
- Tests de tous les flux complets
- Vérification sécurité (.gitignore, .env, validation)
- README.md (instructions d'installation)

---

## 10. Livrables

| Livrable | Description |
|---|---|
| Code source | Dépôt GitHub organisé (backend/ + frontend/) + README.md |
| Cahier des charges | Ce document |
| Schéma DB | Inclus dans la documentation (section 3.3) |
| Démo | Application fonctionnelle en local |

---

## 11. Critères d'Évaluation

| Critère | Points | Détail |
|---|---|---|
| Sécurité | 6 pts | Hachage Bcrypt, protection routes (middleware auth:sanctum), gestion des tokens, validation des entrées |
| Fonctionnalités | 8 pts | Toutes les fonctionnalités implémentées (inscription, email, login, OAuth, profil, avatar, password reset/change) |
| Qualité du code | 3 pts | Architecture MVC, code propre, gestion d'erreurs, respect PSR (Laravel) |
| UI/UX | 3 pts | Ergonomie des formulaires, feedback utilisateur (loading, erreurs, succès), navigation fluide |
| **Total** | **20 pts** | |

### Bonus (Optionnel)

| Fonctionnalité | Points |
|---|---|
| Authentification 2FA (TOTP) | +2 pts |
| Déploiement (Render, Railway ou Vercel) | +1 pt |
| Gestion des rôles (Utilisateur vs Administrateur) | +1 pt |

---

## 12. Rappels Importants

- **Aucun mot de passe ne doit être stocké en clair** dans la base de données (Bcrypt obligatoire)
- **Le fichier `.env` ne doit jamais être commité** (ajouter à `.gitignore`)
- **Toutes les clés secrètes** (APP_KEY, DB credentials, Google OAuth, Mail) doivent être dans le `.env`
- **Les tokens d'accès** ne doivent jamais apparaître dans les URLs ou les logs
- **Valider toutes les entrées** utilisateur côté serveur (ne jamais faire confiance au client)
