<?php
// backend/login.php
session_start();
require_once 'conexao.php';

// Define URL de falha padrão
$url_falha = 'view/index.php?erro=1';

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $email = $_POST['email'] ?? '';
    $senha = $_POST['password'] ?? ''; 

    // --- 1. VERIFICAÇÃO LOCAL (HARDCODED) ---
    // Bypass para garantir acesso imediato aos usuários de teste
    
    // Caso: Cliente Teste
    if ($email === 'cliente@smartflow.com' && $senha === 'cliente123') {
        $_SESSION['loggedin'] = true;
        $_SESSION['id'] = 1; // ID fictício
        $_SESSION['email'] = $email;
        $_SESSION['papel'] = 'cliente';
        
        header("location: view/menu_suco.php");
        exit;
    }

    // Caso: Admin Teste
    if ($email === 'admin@smartflow.com' && $senha === 'admin123') {
        $_SESSION['loggedin'] = true;
        $_SESSION['id'] = 2; // ID fictício
        $_SESSION['email'] = $email;
        $_SESSION['papel'] = 'administrador';
        
        header("location: view/telaPDASH.php");
        exit;
    }

    // --- 2. VERIFICAÇÃO NO BANCO DE DADOS (FALLBACK) ---
    // Se não for um dos usuários de teste acima, tenta buscar no banco
    $email_db = $conn->real_escape_string($email);
    
    $sql = "SELECT id, email, senha_hash, papel FROM usuarios WHERE email = ?";
    
    if ($stmt = $conn->prepare($sql)) {
        $stmt->bind_param("s", $email_db);
        $stmt->execute();
        $res = $stmt->get_result();

        if ($res->num_rows === 1) {
            $user = $res->fetch_assoc();
            
            // Verifica senha (suporta tanto Hash quanto Texto Puro para compatibilidade)
            if (password_verify($senha, $user['senha_hash']) || $senha === $user['senha_hash']) {
                $_SESSION['loggedin'] = true;
                $_SESSION['id'] = $user['id'];
                $_SESSION['email'] = $user['email'];
                $_SESSION['papel'] = $user['papel'];
                
                if ($user['papel'] === 'administrador') {
                    header("location: view/telaPDASH.php"); 
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

// Se chegou aqui, falhou
header("location: " . $url_falha);
exit;
?>