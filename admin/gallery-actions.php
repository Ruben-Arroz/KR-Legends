<?php
// ==========================================
// gallery-actions.php - Gallery Actions Handler
// ==========================================

ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);
ini_set('session.cookie_secure', 1);
ini_set('session.cookie_httponly', 1);
ini_set('session.use_strict_mode', 1);
session_name('KRSESSION');
session_start();

// Incluir conexão MySQLi
require_once __DIR__ . '/../../databaseconnect.php';

// Verificar permissão
if (!in_array($_SESSION['role'], ['ADM', 'SUPERADM'])) {
    http_response_code(403);
    echo json_encode(['success' => false, 'message' => 'Acesso negado']);
    exit;
}

// Definir header JSON
header('Content-Type: application/json');

// Processar ação
$action = $_POST['action'] ?? $_GET['action'] ?? '';

switch ($action) {
    case 'add':
        handleAddItem();
        break;
    case 'edit':
        handleEditItem();
        break;
    case 'delete':
        handleDeleteItem();
        break;
    case 'get_item':
        handleGetItem();
        break;
    default:
        // Se não há ação específica, verificar se é um formulário de adição/edição
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            if (isset($_POST['item_id'])) {
                handleEditItem();
            } else {
                handleAddItem();
            }
        } else {
            echo json_encode(['success' => false, 'message' => 'Ação inválida']);
        }
        break;
}

function handleAddItem()
{
    global $mysqli;

    try {
        // Validar dados obrigatórios
        if (empty($_POST['title'])) {
            throw new Exception('O título é obrigatório');
        }

        if (empty($_POST['category'])) {
            throw new Exception('A categoria é obrigatória');
        }

        if (strlen($_POST['title']) > 150) {
            throw new Exception('O título não pode exceder 150 caracteres');
        }

        if (!empty($_POST['description']) && strlen($_POST['description']) > 500) {
            throw new Exception('A descrição não pode exceder 500 caracteres');
        }

        if (!empty($_POST['alt']) && strlen($_POST['alt']) > 255) {
            throw new Exception('O texto alternativo não pode exceder 255 caracteres');
        }

        // Validar categoria
        $allowed_categories = ['carros', 'pistas', 'corridas', 'eventos'];
        if (!in_array($_POST['category'], $allowed_categories)) {
            throw new Exception('Categoria inválida');
        }

        // Validar ficheiro
        if (!isset($_FILES['file']) || $_FILES['file']['error'] !== UPLOAD_ERR_OK) {
            throw new Exception('Erro no upload do ficheiro');
        }

        $file = $_FILES['file'];

        // Validar tamanho (35MB)
        $maxSize = 35 * 1024 * 1024;
        if ($file['size'] > $maxSize) {
            throw new Exception('O ficheiro excede o limite de 35MB');
        }

        // Validar tipo de ficheiro
        $allowedTypes = ['image/jpeg', 'image/jpg', 'image/png', 'image/gif', 'image/webp', 'video/mp4', 'video/webm'];
        $finfo = finfo_open(FILEINFO_MIME_TYPE);
        $mimeType = finfo_file($finfo, $file['tmp_name']);
        finfo_close($finfo);

        if (!in_array($mimeType, $allowedTypes)) {
            throw new Exception('Tipo de ficheiro não suportado');
        }

        // Inserir na base de dados
        $stmt = $mysqli->prepare("
            INSERT INTO KR_GALERIA (GL_TITLE, GL_DESCRIPTION, GL_CATEGORY, GL_ALT, GL_IS_PUBLIC, GL_USER_ID) 
            VALUES (?, ?, ?, ?, ?, ?)
        ");

        $title = trim($_POST['title']);
        $description = trim($_POST['description'] ?? '');
        $category = $_POST['category'];
        $alt = trim($_POST['alt'] ?? '');
        $isPublic = isset($_POST['is_public']) ? 1 : 0;
        $userId = $_SESSION['id'];

        $stmt->bind_param('ssssii', $title, $description, $category, $alt, $isPublic, $userId);

        if (!$stmt->execute()) {
            throw new Exception('Erro ao inserir na base de dados: ' . $stmt->error);
        }

        $itemId = $mysqli->insert_id;
        $stmt->close();

        // Determinar extensão do ficheiro
        $extension = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
        if (empty($extension)) {
            // Se não conseguir obter extensão do nome, usar pelo tipo MIME
            $mime_to_ext = [
                'image/jpeg' => 'jpg',
                'image/jpg' => 'jpg',
                'image/png' => 'png',
                'image/gif' => 'gif',
                'image/webp' => 'webp',
                'video/mp4' => 'mp4',
                'video/webm' => 'webm'
            ];
            $extension = $mime_to_ext[$mimeType] ?? 'jpg';
        }

        // Criar nome do ficheiro
        $fileName = "item_" . $itemId . "." . $extension;
        $uploadDir = __DIR__ . "/../Imagens/Galeria/";
        $uploadPath = $uploadDir . $fileName;

        // Criar diretório se não existir
        if (!is_dir($uploadDir)) {
            mkdir($uploadDir, 0755, true);
        }

        // Mover ficheiro
        if (!move_uploaded_file($file['tmp_name'], $uploadPath)) {
            // Se falhou o upload, eliminar da base de dados
            $mysqli->query("DELETE FROM KR_GALERIA WHERE GL_ID = $itemId");
            throw new Exception('Erro ao guardar ficheiro');
        }

        echo json_encode(['success' => true, 'message' => 'Item adicionado com sucesso', 'item_id' => $itemId]);

    } catch (Exception $e) {
        echo json_encode(['success' => false, 'message' => $e->getMessage()]);
    }
}

function handleEditItem()
{
    global $mysqli;

    try {
        // Validar dados obrigatórios
        if (empty($_POST['item_id'])) {
            throw new Exception('ID do item é obrigatório');
        }

        if (empty($_POST['title'])) {
            throw new Exception('O título é obrigatório');
        }

        if (empty($_POST['category'])) {
            throw new Exception('A categoria é obrigatória');
        }

        if (strlen($_POST['title']) > 150) {
            throw new Exception('O título não pode exceder 150 caracteres');
        }

        if (!empty($_POST['description']) && strlen($_POST['description']) > 500) {
            throw new Exception('A descrição não pode exceder 500 caracteres');
        }

        if (!empty($_POST['alt']) && strlen($_POST['alt']) > 255) {
            throw new Exception('O texto alternativo não pode exceder 255 caracteres');
        }

        $itemId = intval($_POST['item_id']);

        // Validar categoria
        $allowed_categories = ['carros', 'pistas', 'corridas', 'eventos'];
        if (!in_array($_POST['category'], $allowed_categories)) {
            throw new Exception('Categoria inválida');
        }

        // Verificar se o item existe
        $checkStmt = $mysqli->prepare("SELECT GL_ID FROM KR_GALERIA WHERE GL_ID = ?");
        $checkStmt->bind_param('i', $itemId);
        $checkStmt->execute();
        $result = $checkStmt->get_result();

        if ($result->num_rows === 0) {
            throw new Exception('Item não encontrado');
        }
        $checkStmt->close();

        // Atualizar dados na base de dados
        $stmt = $mysqli->prepare("
            UPDATE KR_GALERIA 
            SET GL_TITLE = ?, GL_DESCRIPTION = ?, GL_CATEGORY = ?, GL_ALT = ?, GL_IS_PUBLIC = ?, GL_UPDATED_AT = NOW()
            WHERE GL_ID = ?
        ");

        $title = trim($_POST['title']);
        $description = trim($_POST['description'] ?? '');
        $category = $_POST['category'];
        $alt = trim($_POST['alt'] ?? '');
        $isPublic = isset($_POST['is_public']) ? 1 : 0;

        $stmt->bind_param('ssssii', $title, $description, $category, $alt, $isPublic, $itemId);

        if (!$stmt->execute()) {
            throw new Exception('Erro ao atualizar na base de dados: ' . $stmt->error);
        }
        $stmt->close();

        // Processar novo ficheiro se foi enviado
        if (isset($_FILES['file']) && $_FILES['file']['error'] === UPLOAD_ERR_OK) {
            $file = $_FILES['file'];

            // Validar tamanho (35MB)
            $maxSize = 35 * 1024 * 1024;
            if ($file['size'] > $maxSize) {
                throw new Exception('O ficheiro excede o limite de 35MB');
            }

            // Validar tipo de ficheiro
            $allowedTypes = ['image/jpeg', 'image/jpg', 'image/png', 'image/gif', 'image/webp', 'video/mp4', 'video/webm'];
            $finfo = finfo_open(FILEINFO_MIME_TYPE);
            $mimeType = finfo_file($finfo, $file['tmp_name']);
            finfo_close($finfo);

            if (!in_array($mimeType, $allowedTypes)) {
                throw new Exception('Tipo de ficheiro não suportado');
            }

            // Eliminar ficheiro antigo
            $galleryDir = __DIR__ . "/../Imagens/Galeria/";
            $extensions = ['jpg', 'jpeg', 'png', 'gif', 'webp', 'mp4', 'webm'];

            foreach ($extensions as $ext) {
                $oldFile = $galleryDir . "item_" . $itemId . "." . $ext;
                if (file_exists($oldFile)) {
                    unlink($oldFile);
                }
            }

            // Determinar extensão do novo ficheiro
            $extension = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
            if (empty($extension)) {
                $mime_to_ext = [
                    'image/jpeg' => 'jpg',
                    'image/jpg' => 'jpg',
                    'image/png' => 'png',
                    'image/gif' => 'gif',
                    'image/webp' => 'webp',
                    'video/mp4' => 'mp4',
                    'video/webm' => 'webm'
                ];
                $extension = $mime_to_ext[$mimeType] ?? 'jpg';
            }

            // Criar nome do ficheiro
            $fileName = "item_" . $itemId . "." . $extension;
            $uploadPath = $galleryDir . $fileName;

            // Mover novo ficheiro
            if (!move_uploaded_file($file['tmp_name'], $uploadPath)) {
                throw new Exception('Erro ao guardar novo ficheiro');
            }
        }

        echo json_encode(['success' => true, 'message' => 'Item atualizado com sucesso']);

    } catch (Exception $e) {
        echo json_encode(['success' => false, 'message' => $e->getMessage()]);
    }
}

function handleDeleteItem()
{
    global $mysqli;

    try {
        if (empty($_POST['id'])) {
            throw new Exception('ID do item é obrigatório');
        }

        $itemId = intval($_POST['id']);

        // Verificar se o item existe
        $checkStmt = $mysqli->prepare("SELECT GL_ID FROM KR_GALERIA WHERE GL_ID = ?");
        $checkStmt->bind_param('i', $itemId);
        $checkStmt->execute();
        $result = $checkStmt->get_result();

        if ($result->num_rows === 0) {
            throw new Exception('Item não encontrado');
        }
        $checkStmt->close();

        // Eliminar ficheiro
        $galleryDir = __DIR__ . "/../Imagens/Galeria/";
        $extensions = ['jpg', 'jpeg', 'png', 'gif', 'webp', 'mp4', 'webm'];

        foreach ($extensions as $ext) {
            $filePath = $galleryDir . "item_" . $itemId . "." . $ext;
            if (file_exists($filePath)) {
                unlink($filePath);
                break;
            }
        }

        // Eliminar da base de dados
        $stmt = $mysqli->prepare("DELETE FROM KR_GALERIA WHERE GL_ID = ?");
        $stmt->bind_param('i', $itemId);

        if (!$stmt->execute()) {
            throw new Exception('Erro ao eliminar da base de dados: ' . $stmt->error);
        }
        $stmt->close();

        echo json_encode(['success' => true, 'message' => 'Item eliminado com sucesso']);

    } catch (Exception $e) {
        echo json_encode(['success' => false, 'message' => $e->getMessage()]);
    }
}

function handleGetItem()
{
    global $mysqli;

    try {
        if (empty($_GET['id'])) {
            throw new Exception('ID do item é obrigatório');
        }

        $itemId = intval($_GET['id']);

        $stmt = $mysqli->prepare("
            SELECT GL_ID, GL_TITLE, GL_DESCRIPTION, GL_CATEGORY, GL_ALT, GL_IS_PUBLIC 
            FROM KR_GALERIA 
            WHERE GL_ID = ?
        ");
        $stmt->bind_param('i', $itemId);
        $stmt->execute();
        $result = $stmt->get_result();

        if ($result->num_rows === 0) {
            throw new Exception('Item não encontrado');
        }

        $item = $result->fetch_assoc();
        $stmt->close();

        echo json_encode(['success' => true, 'item' => $item]);

    } catch (Exception $e) {
        echo json_encode(['success' => false, 'message' => $e->getMessage()]);
    }
}