<?php
// Afficher les erreurs
error_reporting(E_ALL);
ini_set('display_errors', 1);

$message = '';
$message_type = '';

// Si le formulaire est soumis
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    include 'config/database.php';
    
    $nom = trim($_POST['nom'] ?? '');
    $email = trim($_POST['email'] ?? '');
    $role = $_POST['role'] ?? '';
    $mdp = $_POST['mdp'] ?? '';
    $mdp_confirm = $_POST['mdp_confirm'] ?? '';

    // Validations
    $erreurs = [];

    if (empty($nom)) {
        $erreurs[] = "Le nom est requis";
    } elseif (strlen($nom) < 3) {
        $erreurs[] = "Le nom doit faire au moins 3 caractères";
    }

    if (empty($email)) {
        $erreurs[] = "L'email est requis";
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $erreurs[] = "L'email n'est pas valide";
    }

    if (empty($role)) {
        $erreurs[] = "Le rôle est requis";
    }

    if (empty($mdp)) {
        $erreurs[] = "Le mot de passe est requis";
    } elseif (strlen($mdp) < 6) {
        $erreurs[] = "Le mot de passe doit faire au moins 6 caractères";
    }

    if ($mdp !== $mdp_confirm) {
        $erreurs[] = "Les mots de passe ne correspondent pas";
    }

    if (empty($erreurs)) {
        // Vérifier que l'email n'existe pas déjà
        $sql_check = "SELECT id FROM utilisateurs WHERE email = ?";
        $stmt_check = $conn->prepare($sql_check);
        $stmt_check->bind_param("s", $email);
        $stmt_check->execute();
        $result_check = $stmt_check->get_result();

        if ($result_check->num_rows > 0) {
            $message = "❌ Cet email est déjà utilisé";
            $message_type = "error";
        } else {
            // Hasher le mot de passe
            $mdp_hash = password_hash($mdp, PASSWORD_DEFAULT);

            // Insérer le nouvel utilisateur
            $sql_insert = "INSERT INTO utilisateurs (nom, email, role, mdp) VALUES (?, ?, ?, ?)";
            $stmt_insert = $conn->prepare($sql_insert);
            $stmt_insert->bind_param("ssss", $nom, $email, $role, $mdp_hash);

            if ($stmt_insert->execute()) {
                $message = "✅ Inscription réussie ! Vous pouvez maintenant vous connecter.";
                $message_type = "success";
                // Rediriger après 2 secondes
                header("Refresh: 2; url=index.php");
            } else {
                $message = "❌ Erreur : " . $stmt_insert->error;
                $message_type = "error";
            }
            $stmt_insert->close();
        }
        $stmt_check->close();
    } else {
        $message = "❌ " . implode("<br>", $erreurs);
        $message_type = "error";
    }

    $conn->close();
}
?>
<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <title>Inscription - Gestion Fabrication</title>
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body { 
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            min-height: 100vh;
            display: flex;
            justify-content: center;
            align-items: center;
            padding: 20px;
        }
        .inscription-container {
            background: white;
            padding: 40px;
            border-radius: 8px;
            box-shadow: 0 10px 25px rgba(0,0,0,0.2);
            width: 100%;
            max-width: 450px;
        }
        h1 { 
            color: #333; 
            margin-bottom: 10px; 
            text-align: center;
            font-size: 28px;
        }
        .subtitle {
            color: #999;
            text-align: center;
            margin-bottom: 30px;
            font-size: 14px;
        }
        .form-group {
            margin-bottom: 20px;
        }
        label {
            display: block;
            margin-bottom: 8px;
            color: #555;
            font-weight: 500;
        }
        input, select {
            width: 100%;
            padding: 12px;
            border: 1px solid #ddd;
            border-radius: 4px;
            font-size: 14px;
            font-family: Arial, sans-serif;
        }
        input:focus, select:focus {
            outline: none;
            border-color: #667eea;
            box-shadow: 0 0 5px rgba(102, 126, 234, 0.1);
        }
        .required { color: #f44336; }
        button {
            width: 100%;
            padding: 12px;
            background: #4CAF50;
            color: white;
            border: none;
            border-radius: 4px;
            font-size: 16px;
            font-weight: bold;
            cursor: pointer;
            margin-top: 10px;
            transition: all 0.3s;
        }
        button:hover {
            background: #45a049;
            transform: translateY(-2px);
            box-shadow: 0 4px 8px rgba(76, 175, 80, 0.3);
        }
        .message {
            padding: 15px;
            border-radius: 4px;
            margin-bottom: 20px;
            font-weight: bold;
        }
        .message.success {
            background: #e8f5e9;
            color: #2e7d32;
            border: 1px solid #4caf50;
        }
        .message.error {
            background: #ffebee;
            color: #c62828;
            border: 1px solid #f44336;
        }
        .login-link {
            text-align: center;
            margin-top: 20px;
            color: #666;
            font-size: 14px;
        }
        .login-link a {
            color: #667eea;
            text-decoration: none;
            font-weight: bold;
        }
        .login-link a:hover {
            text-decoration: underline;
        }
        .password-requirements {
            background: #f9f9f9;
            padding: 12px;
            border-radius: 4px;
            margin-top: 10px;
            font-size: 12px;
            color: #666;
            border-left: 3px solid #667eea;
        }
        .password-requirements li {
            margin-left: 20px;
            margin-bottom: 5px;
        }
    </style>
</head>
<body>
    <div class="inscription-container">
        <h1>📝 Inscription</h1>
        <p class="subtitle">Créez votre compte pour commencer</p>

        <?php if ($message): ?>
            <div class="message <?php echo $message_type; ?>">
                <?php echo $message; ?>
            </div>
        <?php endif; ?>

        <form method="POST" action="">
            <div class="form-group">
                <label>Nom Complet <span class="required">*</span></label>
                <input type="text" name="nom" placeholder="ex: Ahmed Ben Ali" required>
            </div>

            <div class="form-group">
                <label>Email <span class="required">*</span></label>
                <input type="email" name="email" placeholder="ex: ahmed@exemple.com" required>
            </div>

            <div class="form-group">
                <label>Rôle <span class="required">*</span></label>
                <select name="role" required>
                    <option value="">-- Sélectionnez un rôle --</option>
                    <option value="commercial">Commercial</option>
                    <option value="production">Production</option>
                </select>
            </div>

            <div class="form-group">
                <label>Mot de Passe <span class="required">*</span></label>
                <input type="password" name="mdp" placeholder="Minimum 6 caractères" required>
                <div class="password-requirements">
                    <strong>Exigences :</strong>
                    <ul>
                        <li>Au moins 6 caractères</li>
                        <li>À conserver en lieu sûr</li>
                    </ul>
                </div>
            </div>

            <div class="form-group">
                <label>Confirmer le Mot de Passe <span class="required">*</span></label>
                <input type="password" name="mdp_confirm" placeholder="Confirmer le mot de passe" required>
            </div>

            <button type="submit">✅ S'inscrire</button>
        </form>

        <div class="login-link">
            Vous avez déjà un compte ? <a href="index.php">Se connecter</a>
        </div>
    </div>
</body>
</html>