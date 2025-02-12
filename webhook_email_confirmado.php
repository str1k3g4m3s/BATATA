<?php
header('Content-Type: application/json');
$BANK_URL = "https://hjcepiivzfqsenyfesez.supabase.co";
$BANK_KEY = "eyJhbGciOiJIUzI1NiIsInR5cCI6IkpXVCJ9.eyJpc3MiOiJzdXBhYmFzZSIsInJlZiI6ImhqY2VwaWl2emZxc2VueWZlc2V6Iiwicm9sZSI6InNlcnZpY2Vfcm9sZSIsImlhdCI6MTczODU1MjAxNSwiZXhwIjoyMDU0MTI4MDE1fQ.cQWyg3AS6I5j0SU28RsC5s4GXHTN5u0Shufvqqs-pf0";
// URL da API do Supabase (endpoint para obter dados da tabela 'profiles')
$url = "$BANK_URL/rest/v1/profiles";

// Cabeçalhos para autenticação
$headers = [
    "Content-Type: application/json",
    "apikey: $BANK_KEY",
    "Authorization: Bearer $BANK_KEY"
];

// 📌 Captura a requisição do Supabase
$dados = json_decode(file_get_contents("php://input"), true);

// 📌 Verifica se é um evento de confirmação de e-mail
if (!isset($dados['event']) || $dados['event'] !== 'user.confirmed') {
    echo json_encode(["error" => "Evento inválido"]);
    exit;
}

// 📌 Captura os dados do usuário confirmado
$user_id = $dados['user']['id'] ?? null;
$email = $dados['user']['email'] ?? null;
$meta_data = $dados['user']['raw_user_meta_data'] ?? [];

// 📌 Valida se os dados são válidos antes de continuar
if (!$user_id || !$email) {
    echo json_encode(["error" => "Dados do usuário ausentes"]);
    exit;
}

// 📌 Insere os dados do perfil na tabela profiles
$dadosPerfil = json_encode([
    "id" => $user_id,
    "email" => $email,
    "name" => $meta_data['name'] ?? '',
    "cpf_cnpj" => $meta_data['cpf_cnpj'] ?? '',
    "razao_social" => $meta_data['razao_social'] ?? null,
    "phone" => $meta_data['phone'] ?? '',
    "user_type" => $meta_data['user_type'] ?? 'cpf',
    "address" => $meta_data['address'] ?? '',
    "image_url" => $meta_data['image_url'] ?? ''
]);

// 📌 Envia os dados para a tabela profiles
$ch = curl_init("$BANK_URL/rest/v1/profiles");
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_HTTPHEADER, [
    "Content-Type: application/json",
    "apikey: $BANK_KEY",
    "Authorization: Bearer $BANK_KEY",
    "Prefer: resolution=merge-duplicates" // Garante que não haverá duplicatas
]);
curl_setopt($ch, CURLOPT_POST, true);
curl_setopt($ch, CURLOPT_POSTFIELDS, $dadosPerfil);

$response = curl_exec($ch);
$http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
curl_close($ch);

// 📌 Verifica se a inserção foi bem-sucedida
if ($http_code !== 201 && $http_code !== 200) {
    echo json_encode(["error" => "Erro ao salvar perfil", "response" => $response]);
    exit;
}

echo json_encode(["success" => "Perfil criado após confirmação de e-mail"]);
exit;
?>
