<?php
// backend/login.php
session_start();
require_once 'conexao.php';

// Se falhar, volta para a tela de login na pasta view
$url_falha = 'view/index.php?erro=1';

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $email = $_POST['email'] ?? '';
    $senha = $_POST['password'] ?? ''; 

    // --- 1. VERIFICAÇÃO LOCAL (Hardcoded para Teste) ---
    if ($email === 'cliente@smartflow.com' && $senha === 'cliente123') {
        $_SESSION['loggedin'] = true;
        $_SESSION['papel'] = 'cliente';
        header("location: view/menu_suco.php");
        exit;
    }
    if ($email === 'admin@smartflow.com' && $senha === 'admin123') {
        $_SESSION['loggedin'] = true;
        $_SESSION['papel'] = 'administrador';
        header("location: view/home_admin.php");
        exit;
    }

    // --- 2. VERIFICAÇÃO NO BANCO DE DADOS (Fallback) ---
    $email_db = $conn->real_escape_string($email);
    $sql = "SELECT id, email, senha_hash, papel FROM usuarios WHERE email = ?";
    
    if ($stmt = $conn->prepare($sql)) {
        $stmt->bind_param("s", $email_db);
        $stmt->execute();
        $res = $stmt->get_result();

        if ($res->num_rows === 1) {
            $user = $res->fetch_assoc();
            
            // Aceita Hash OU Texto Puro (para desenvolvimento)
            if (password_verify($senha, $user['senha_hash']) || $senha === $user['senha_hash']) {
                $_SESSION['loggedin'] = true;
                $_SESSION['id'] = $user['id'];
                $_SESSION['email'] = $user['email'];
                $_SESSION['papel'] = $user['papel'];
                
                if ($user['papel'] === 'administrador') {
                    header("location: view/home_admin.php"); 
                } else {
                    header("location: view/menu_suco.php"); 
                }
                exit; 
            } else {
                $url_falha = 'view/index.php?erro=senha';
            }
        } else {
            $url_falha = 'view/index.php?erro=usuario';
        }
        $stmt->close();
    }
    $conn->close();
}
header("location: " . $url_falha);
exit;