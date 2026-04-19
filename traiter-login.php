<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);

session_start();
include 'config/database.php';

if (!$conn) {
    die("❌ ERREUR : Impossible de se connecter");
}

$email = $_POST['email'] ?? '';
$mdp = $_POST['mdp'] ?? '';

if (empty($email) || empty($mdp)) {
    header("Location: index.php?error=Email et mot de passe requis");
    exit;
}

try {
    $sql = "SELECT * FROM utilisateurs WHERE email = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("s", $email);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows == 0) {
        header("Location: index.php?error=Email ou mot de passe incorrect");
        exit;
    }

    $user = $result->fetch_assoc();
    
    // Vérifier le mot de passe
    if (isset($user['mdp']) && !empty($user['mdp'])) {
        if (!password_verify($mdp, $user['mdp'])) {
            header("Location: index.php?error=Email ou mot de passe incorrect");
            exit;
        }
    }

    // Créer la session
    $_SESSION['user_id'] = $user['id'];
    $_SESSION['nom'] = $user['nom'];
    $_SESSION['email'] = $user['email'];
    $_SESSION['role'] = $user['role'];

    // Rediriger selon le rôle
    if ($user['role'] == 'admin') {
    header("Location: admin/dashboard.php");

} else if ($user['role'] == 'commercial') {
    header("Location: commercial/mes-commandes.php");

} else if ($user['role'] == 'production') {
    header("Location: production/commandes-en-attente.php");

} else if ($user['role'] == 'magasin') {
    header("Location: magasin/dashboard.php");

} else {
    header("Location: index.php?error=Rôle inconnu");
}


    $stmt->close();
    $conn->close();
    exit;

} catch (Exception $e) {
    die("❌ Erreur : " . $e->getMessage());
}
?>