<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

include 'config/database.php';

$error = '';

// 🔁 Redirection si déjà connecté
if (isset($_SESSION['user_id'])) {
    if ($_SESSION['role'] == 'admin') {
        header("Location: admin/dashboard.php");
        exit;
    } elseif ($_SESSION['role'] == 'commercial') {
        header("Location: commercial/mes-commandes.php");
        exit;
    } elseif ($_SESSION['role'] == 'production') {
        header("Location: production/commandes-en-attente.php");
        exit;
    } elseif ($_SESSION['role'] == 'magasin') {
        header("Location: magasin/dashboard.php");
        exit;
    }
}

// 🔐 Traitement login
if ($_SERVER['REQUEST_METHOD'] == 'POST') {

    $email = trim($_POST['email'] ?? '');
    $mdp   = trim($_POST['mdp'] ?? '');

    if (empty($email) || empty($mdp)) {
        $error = "Email et mot de passe requis";
    } else {

        $sql = "SELECT * FROM utilisateurs WHERE email = ?";
        $stmt = $conn->prepare($sql);

        if (!$stmt) {
            die("Erreur SQL : " . $conn->error);
        }

        $stmt->bind_param("s", $email);
        $stmt->execute();
        $result = $stmt->get_result();

        if ($result && $result->num_rows > 0) {

            $user = $result->fetch_assoc();

            if (
                $user &&
                isset($user['mot_de_passe']) &&
                password_verify($mdp, $user['mot_de_passe'])
            ) {

                // ✅ Création session
                $_SESSION['user_id'] = $user['id'];
                $_SESSION['nom']     = $user['nom'];
                $_SESSION['email']   = $user['email'];
                $_SESSION['role']    = $user['role'];

                // 🔁 Redirection selon rôle
                if ($user['role'] == 'admin') {
                    header("Location: admin/dashboard.php");
                    exit;
                } elseif ($user['role'] == 'commercial') {
                    header("Location: commercial/mes-commandes.php");
                    exit;
                } elseif ($user['role'] == 'production') {
                    header("Location: production/commandes-en-attente.php");
                    exit;
                } elseif ($user['role'] == 'magasin') {
                    header("Location: magasin/dashboard.php");
                    exit;
                } else {
                    $error = "Rôle inconnu";
                }

            } else {
                $error = "Mot de passe incorrect";
            }

        } else {
            $error = "Email incorrect";
        }
    }
}
?>

<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <title>Connexion - Gestion Fabrication</title>

    <style>
        :root {
            --primary: #667eea;
            --danger: #f44336;
        }

        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            min-height: 100vh;
            display: flex;
            justify-content: center;
            align-items: center;
            flex-direction: column;
        }

        .login-container {
            background: white;
            padding: 40px;
            border-radius: 10px;
            box-shadow: 0 15px 40px rgba(0,0,0,0.2);
            max-width: 400px;
            width: 100%;
            margin-bottom: 120px;
        }

        h1 {
            color: var(--primary);
            margin-bottom: 30px;
            text-align: center;
            font-size: 26px;
        }

        .form-group {
            margin-bottom: 20px;
        }

        label {
            display: block;
            margin-bottom: 8px;
            font-weight: bold;
            color: #333;
        }

        input {
            width: 100%;
            padding: 12px;
            border: 1px solid #ddd;
            border-radius: 5px;
            font-size: 14px;
        }

        input:focus {
            outline: none;
            border-color: var(--primary);
            box-shadow: 0 0 6px rgba(102, 126, 234, 0.3);
        }

        .login-btn {
            width: 100%;
            padding: 12px;
            background: var(--primary);
            color: white;
            border: none;
            border-radius: 5px;
            font-weight: bold;
            cursor: pointer;
            margin-top: 20px;
        }

        .login-btn:hover {
            background: #5568d3;
        }

        .error {
            background: #ffebee;
            color: #c62828;
            padding: 12px;
            border-radius: 5px;
            margin-bottom: 20px;
            border: 1px solid var(--danger);
        }

        .logo-container {
            text-align: center;
            margin-bottom: 15px;
        }

        .logo-gif {
            width: 100px;
        }

        .footer-text {
            color: white;
            font-size: 13px;
            opacity: 0.9;
            margin-top: 20px;
        }
    </style>
</head>

<body>

<div class="login-container">

    <div class="logo-container">
        <img src="assets/images/chicken.gif" alt="Logo" class="logo-gif">
    </div>

    <h1>Gestion Fabrication</h1>

    <?php if (!empty($error)): ?>
        <div class="error">
            ❌ <?php echo htmlspecialchars($error); ?>
        </div>
    <?php endif; ?>

    <form method="POST">
        <div class="form-group">
            <label>Email</label>
            <input type="email" name="email" required>
        </div>

        <div class="form-group">
            <label>Mot de passe</label>
            <input type="password" name="mdp" required>
        </div>

        <button type="submit" class="login-btn">
            🔓 Se connecter
        </button>
    </form>
</div>

<div class="footer-text">
    © <?php echo date("Y"); ?> - Application développée par 
    <strong>Omar Jbeli</strong>
</div>

</body>
</html>