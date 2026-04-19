<?php
// Générer les hash corrects
$mdp_test = "test";
$mdp_admin = "admin123";

$hash_test = password_hash($mdp_test, PASSWORD_DEFAULT);
$hash_admin = password_hash($mdp_admin, PASSWORD_DEFAULT);

echo "<h2>HASH GÉNÉRÉS</h2>";
echo "<p><strong>Hash pour 'test' :</strong></p>";
echo "<code>" . $hash_test . "</code>";
echo "<br><br>";
echo "<p><strong>Hash pour 'admin123' :</strong></p>";
echo "<code>" . $hash_admin . "</code>";
echo "<br><br>";

// Vérifier que les hash fonctionnent
echo "<h2>VÉRIFICATION</h2>";
echo "<p>password_verify('test', hash_test) = " . (password_verify($mdp_test, $hash_test) ? "TRUE ✅" : "FALSE ❌") . "</p>";
echo "<p>password_verify('admin123', hash_admin) = " . (password_verify($mdp_admin, $hash_admin) ? "TRUE ✅" : "FALSE ❌") . "</p>";
?>