<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);

include 'config/database.php';

echo "<h2>TEST DE CONNEXION</h2>";

// Test 1 : Vérifier la connexion à la base
echo "<h3>✅ Test 1 : Connexion à la base</h3>";
if ($conn->connect_error) {
    die("❌ Erreur de connexion: " . $conn->connect_error);
}
echo "✅ Connexion OK<br><br>";

// Test 2 : Vérifier les utilisateurs en base
echo "<h3>✅ Test 2 : Utilisateurs en base</h3>";
$sql = "SELECT id, nom, email, role, mdp FROM utilisateurs";
$result = $conn->query($sql);

echo "<table border='1' cellpadding='10'>";
echo "<tr><th>ID</th><th>Nom</th><th>Email</th><th>Rôle</th><th>Mdp (premiers 20 car)</th><th>Mdp Vide?</th></tr>";

while ($row = $result->fetch_assoc()) {
    $mdp_preview = !empty($row['mdp']) ? substr($row['mdp'], 0, 20) . "..." : "VIDE";
    $mdp_empty = empty($row['mdp']) ? "✅ OUI (VIDE)" : "❌ NON (REMPLI)";
    
    echo "<tr>";
    echo "<td>" . $row['id'] . "</td>";
    echo "<td>" . htmlspecialchars($row['nom']) . "</td>";
    echo "<td>" . htmlspecialchars($row['email']) . "</td>";
    echo "<td>" . $row['role'] . "</td>";
    echo "<td>" . $mdp_preview . "</td>";
    echo "<td>" . $mdp_empty . "</td>";
    echo "</tr>";
}
echo "</table>";

echo "<br><br>";

// Test 3 : Tester password_verify avec les NOUVEAUX hash
echo "<h3>✅ Test 3 : Vérifier password_verify (NOUVEAUX HASH)</h3>";

$mdp_test = "test";
$hash_test = '$2y$10$JDlJVE5z6rVq3j7K8lM9NuO0P1Q2R3S4T5U6V7W8X9Y0Z1A2B3C4D5';

$result_verify = password_verify($mdp_test, $hash_test);

echo "Mot de passe testé : <strong>test</strong><br>";
echo "Hash : <strong>" . $hash_test . "</strong><br>";
echo "password_verify() retourne : <strong>" . ($result_verify ? "TRUE ✅" : "FALSE ❌") . "</strong><br>";

echo "<br>";

$mdp_admin = "admin123";
$hash_admin = '$2y$10$KEmL9U6w7x8Y9z0A1B2C3d4E5F6G7H8I9J0K1L2M3N4O5P6Q7R8S9T0';

$result_admin_verify = password_verify($mdp_admin, $hash_admin);

echo "Mot de passe testé : <strong>admin123</strong><br>";
echo "Hash : <strong>" . $hash_admin . "</strong><br>";
echo "password_verify() retourne : <strong>" . ($result_admin_verify ? "TRUE ✅" : "FALSE ❌") . "</strong><br>";

echo "<br><br>";

// Test 4 : Vérifier un utilisateur spécifique
echo "<h3>✅ Test 4 : Vérifier l'admin</h3>";

$email_test = "admin@exemple.com";
$sql_admin = "SELECT * FROM utilisateurs WHERE email = ?";
$stmt_admin = $conn->prepare($sql_admin);
$stmt_admin->bind_param("s", $email_test);
$stmt_admin->execute();
$result_admin = $stmt_admin->get_result();

if ($result_admin->num_rows > 0) {
    $admin = $result_admin->fetch_assoc();
    echo "✅ Admin trouvé<br>";
    echo "Email : " . htmlspecialchars($admin['email']) . "<br>";
    echo "Rôle : " . $admin['role'] . "<br>";
    echo "Mdp en base : " . (empty($admin['mdp']) ? "VIDE ❌" : "REMPLI ✅") . "<br>";
    echo "Mdp (premiers 30 car) : " . substr($admin['mdp'], 0, 30) . "<br>";
    
    echo "<br>";
    echo "Test password_verify avec 'admin123' : <strong>" . (password_verify("admin123", $admin['mdp']) ? "TRUE ✅" : "FALSE ❌") . "</strong><br>";
    echo "Test password_verify avec 'test' : <strong>" . (password_verify("test", $admin['mdp']) ? "TRUE ✅" : "FALSE ❌") . "</strong><br>";
} else {
    echo "❌ Admin non trouvé<br>";
}

$stmt_admin->close();

echo "<br><br>";

// Test 5 : Vérifier un utilisateur commercial
echo "<h3>✅ Test 5 : Vérifier Omar (Commercial)</h3>";

$email_test = "Omar.Jbeli@cocorico-tn.com";
$sql_omar = "SELECT * FROM utilisateurs WHERE email = ?";
$stmt_omar = $conn->prepare($sql_omar);
$stmt_omar->bind_param("s", $email_test);
$stmt_omar->execute();
$result_omar = $stmt_omar->get_result();

if ($result_omar->num_rows > 0) {
    $omar = $result_omar->fetch_assoc();
    echo "✅ Omar trouvé<br>";
    echo "Email : " . htmlspecialchars($omar['email']) . "<br>";
    echo "Rôle : " . $omar['role'] . "<br>";
    echo "Mdp en base : " . (empty($omar['mdp']) ? "VIDE ❌" : "REMPLI ✅") . "<br>";
    echo "Mdp (premiers 30 car) : " . substr($omar['mdp'], 0, 30) . "<br>";
    
    echo "<br>";
    echo "Test password_verify avec 'test' : <strong>" . (password_verify("test", $omar['mdp']) ? "TRUE ✅" : "FALSE ❌") . "</strong><br>";
    echo "Test password_verify avec 'admin123' : <strong>" . (password_verify("admin123", $omar['mdp']) ? "TRUE ✅" : "FALSE ❌") . "</strong><br>";
} else {
    echo "❌ Omar non trouvé<br>";
}

$stmt_omar->close();
$conn->close();
?>