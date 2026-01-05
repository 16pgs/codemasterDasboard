<?php
// -------------------------------
// Configuration (à renseigner)
// - Crée une application sur https://discord.com/developers
// - Mets ici ton client_id et client_secret
// - Le redirect_uri doit être exactement celui configuré dans le portail Discord
// Exemple de redirect_uri : https://tonsite.com/dis.php
// -------------------------------
$clientId = '1457787688517374022';
$clientSecret = 'Kc3AWFxQxuDgHz_R2faaYi0nYNVNBjkb';
$redirectUri = 'http://tonsite.com/dis.php';

// 1) Récupère le code envoyé par Discord
$code = isset($_GET['code']) ? $_GET['code'] : null;
if (!$code) {
    die('Erreur : pas de code de connexion. Assure-toi que le redirect_uri est correct.');
}

// 2) Échange le code contre un access_token
$tokenUrl = "https://discord.com/api/oauth2/token";
$data = [
    'client_id' => $clientId,
    'client_secret' => $clientSecret,
    'grant_type' => 'authorization_code',
    'code' => $code,
    'redirect_uri' => $redirectUri
];

$ch = curl_init($tokenUrl);
curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query($data));
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_HTTPHEADER, array('Content-Type: application/x-www-form-urlencoded'));

// Exécute la requête et capture les erreurs + code HTTP
$raw = curl_exec($ch);
$curlErr = curl_error($ch);
$httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
curl_close($ch);

if ($curlErr) {
    die('Erreur cURL lors de la requête token : ' . htmlspecialchars($curlErr));
}

$response = json_decode($raw, true);
if ($httpCode < 200 || $httpCode >= 300) {
    die('Erreur lors de la requête token (HTTP ' . $httpCode . ') : ' . htmlspecialchars($raw));
}
if (isset($response['error'])) {
    die('Erreur token : ' . htmlspecialchars($response['error_description'] ?? $response['error']));
}
if (empty($response['access_token'])) {
    die('Erreur : aucun access_token reçu. Réponse brute : ' . htmlspecialchars($raw));
}
$access_token = $response['access_token'];

// 3) Utilise le token pour récupérer le profil utilisateur
$userUrl = "https://discord.com/api/users/@me";
$header = array("Authorization: Bearer $access_token");

$ch = curl_init($userUrl);
curl_setopt($ch, CURLOPT_HTTPHEADER, $header);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);

$userRaw = curl_exec($ch);
$curlErr = curl_error($ch);
$httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
curl_close($ch);

if ($curlErr) {
    die('Erreur cURL lors de la requête utilisateur : ' . htmlspecialchars($curlErr));
}
if ($httpCode < 200 || $httpCode >= 300) {
    die('Erreur lors de la récupération utilisateur (HTTP ' . $httpCode . ') : ' . htmlspecialchars($userRaw));
}

$user = json_decode($userRaw, true);

if (empty($user) || isset($user['code'])) {
    die('Impossible de récupérer les informations utilisateur. Réponse brute : ' . htmlspecialchars($userRaw));
}

// Affiche un exemple simple
echo "Salut " . htmlspecialchars($user['username']) . "#" . htmlspecialchars($user['discriminator']);
echo "<br><img src='https://cdn.discordapp.com/avatars/" . htmlspecialchars($user['id']) . "/" . htmlspecialchars($user['avatar']) . ".png' alt='avatar'>";
?>