<?php
session_start();
require_once(__DIR__ . '/../includes/csrf.php');

// Endpoint minimal pour réception de chunks
// Attendu : POST fields: fileId, chunkIndex, totalChunks, filename
// Fichier chunk en input name 'chunk'

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['error' => 'Method not allowed']);
    exit;
}

$csrf = '';
// prefer header X-CSRF-Token
if (!empty($_SERVER['HTTP_X_CSRF_TOKEN'])) $csrf = $_SERVER['HTTP_X_CSRF_TOKEN'];
if (empty($csrf) && !empty($_POST['csrf_token'])) $csrf = $_POST['csrf_token'];

if (!verifyCsrfToken($csrf)) {
    http_response_code(403);
    echo json_encode(['error' => 'Invalid CSRF']);
    exit;
}

$fileId = preg_replace('/[^a-zA-Z0-9_\-\.]/', '_', ($_POST['fileId'] ?? ''));
$chunkIndex = intval($_POST['chunkIndex'] ?? 0);
$totalChunks = intval($_POST['totalChunks'] ?? 1);
$filename = basename($_POST['filename'] ?? 'upload.bin');

$tmpDir = __DIR__ . '/../uploads/tmp';
if (!is_dir($tmpDir)) mkdir($tmpDir, 0755, true);

// Save incoming chunk
if (!empty($_FILES['chunk']) && $_FILES['chunk']['error'] === UPLOAD_ERR_OK) {
    $partPath = $tmpDir . '/' . $fileId . '.part.' . $chunkIndex;
    if (!move_uploaded_file($_FILES['chunk']['tmp_name'], $partPath)) {
        http_response_code(500);
        echo json_encode(['error' => 'Failed to store chunk']);
        exit;
    }
} else {
    // maybe sent as raw body
    $data = file_get_contents('php://input');
    if ($data === false || $data === '') {
        http_response_code(400);
        echo json_encode(['error' => 'No chunk data']);
        exit;
    }
    $partPath = $tmpDir . '/' . $fileId . '.part.' . $chunkIndex;
    if (file_put_contents($partPath, $data) === false) {
        http_response_code(500);
        echo json_encode(['error' => 'Failed to store raw chunk']);
        exit;
    }
}

// If this was the last chunk, assemble
if ($chunkIndex === $totalChunks - 1) {
    $safeName = preg_replace('/[^a-zA-Z0-9_\-\.]/', '_', $filename);
    $finalTmp = $tmpDir . '/' . $fileId . '__' . $safeName;
    $out = fopen($finalTmp, 'ab');
    if (!$out) {
        http_response_code(500);
        echo json_encode(['error' => 'Cannot open final file']);
        exit;
    }
    for ($i = 0; $i < $totalChunks; $i++) {
        $p = $tmpDir . '/' . $fileId . '.part.' . $i;
        if (!file_exists($p)) {
            // missing part -> abort
            fclose($out);
            http_response_code(500);
            echo json_encode(['error' => 'Missing chunk ' . $i]);
            exit;
        }
        $in = fopen($p, 'rb');
        if ($in) {
            while (!feof($in)) {
                $buf = fread($in, 1048576);
                if ($buf !== false) fwrite($out, $buf);
            }
            fclose($in);
            // remove part
            @unlink($p);
        }
    }
    fclose($out);

    // Return the tmp path relative to web root
    $webPath = 'uploads/tmp/' . basename($finalTmp);
    header('Content-Type: application/json');
    echo json_encode(['success' => true, 'tmp_path' => $webPath, 'original_name' => $filename]);
    exit;
} else {
    header('Content-Type: application/json');
    echo json_encode(['success' => true, 'chunk' => $chunkIndex]);
    exit;
}

?>
