<?php
// backend/login.php
session_start();
require_once 'conexao.php';

// Se falhar, volta para a tela de login na pasta view
$url_falha = 'view/index.php?erro=1';

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $email = $conn->real_escape_string($_POST['email'] ?? '');
    $senha = $_POST['password'] ?? ''; 

    // --- 1. VERIFICAÇÃO LOCAL (HARDCODED) ---
    // Bypass para garantir acesso imediato aos usuários de teste
    
    // Caso: Cliente Teste
    if ($email === 'cliente@smartflow.com' && $senha === 'cliente123') {
        $_SESSION['loggedin'] = true;
        $_SESSION['id'] = 1;
        $_SESSION['email'] = $email;
        $_SESSION['papel'] = 'cliente';
        header("location: view/menu_suco.php");
        exit;
    }

    // Caso: Admin Teste
    if ($email === 'admin@smartflow.com' && $senha === 'admin123') {
        $_SESSION['loggedin'] = true;
        $_SESSION['id'] = 2;
        $_SESSION['email'] = $email;
        $_SESSION['papel'] = 'administrador';
        
        // MUDANÇA AQUI: Vai para o Menu Intermediário
        header("location: view/home_admin.php");
        exit;
    }

    // --- 2. VERIFICAÇÃO NO BANCO DE DADOS ---
    $sql = "SELECT id, email, senha_hash, papel FROM usuarios WHERE email = ?";
    
    if ($stmt = $conn->prepare($sql)) {
        $stmt->bind_param("s", $email);
        $stmt->execute();
        $res = $stmt->get_result();

        if ($res->num_rows === 1) {
            $user = $res->fetch_assoc();
            
            // Aceita Hash ou Texto Puro
            if (password_verify($senha, $user['senha_hash']) || $senha === $user['senha_hash']) {
                $_SESSION['loggedin'] = true;
                $_SESSION['id'] = $user['id'];
                $_SESSION['email'] = $user['email'];
                $_SESSION['papel'] = $user['papel'];
                
                if ($user['papel'] === 'administrador') {
                    // MUDANÇA AQUI TAMBÉM
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
?>