<?php
header('Content-Type: application/json');
require 'config.php';

// 📌 Captura a requisição do Supabase
$dados = json_decode(file_get_contents("php://input"), true);

// 📌 Verifica se é um evento de confirmação de e-mail
if (!isset($dados['event']) || $dados['event'] !== 'user.confirmed') {
    echo json_encode(["error" => "Evento inválido"]);
    exit;
}

// 📌 Captura os dados do usuário confirmado
$user_id = $dados['user']['id'];
$email = $dados['user']['email'];

// 📌 Insere os dados do perfil na tabela profiles
$dadosPerfil = json_encode([
    "id" => $user_id,
    "email" => $email,
    "name" => $dados['user']['raw_user_meta_data']['name'] ?? '',
    "cpf_cnpj" => $dados['user']['raw_user_meta_data']['cpf_cnpj'] ?? '',
    "razao_social" => $dados['user']['raw_user_meta_data']['razao_social'] ?? null,
    "phone" => $dados['user']['raw_user_meta_data']['phone'] ?? '',
    "user_type" => $dados['user']['raw_user_meta_data']['user_type'] ?? 'cpf',
    "address" => $dados['user']['raw_user_meta_data']['address'] ?? '',
    "image_url" => $dados['user']['raw_user_meta_data']['image_url'] ?? ''
]);

$ch = curl_init("$BANK_URL/rest/v1/profiles");
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_HTTPHEADER, [
    "Content-Type: application/json",
    "apikey: $BANK_KEY",
    "Authorization: Bearer $BANK_KEY"
]);
curl_setopt($ch, CURLOPT_POST, true);
curl_setopt($ch, CURLOPT_POSTFIELDS, $dadosPerfil);

$response = curl_exec($ch);
curl_close($ch);

echo json_encode(["success" => "Perfil criado após confirmação de e-mail"]);
exit;
?>