<?php
header('Content-Type: application/json');

$uploadDir = 'client_pictures';
$allowedTypes = ['image/png', 'image/jpeg', 'image/jpg'];
$maxFileSize = 5 * 1024 * 1024; // 5 MB

// Создаем каталог, если он не существует
if (!file_exists($uploadDir)) {
    mkdir($uploadDir, 0777, true);
}

$response = ['success' => false, 'message' => '', 'files' => []];

if (isset($_FILES['photo-upload'])) {
    $files = $_FILES['photo-upload'];
    $fileCount = count($files['name']);
    
    for ($i = 0; $i < $fileCount; $i++) {
        $fileName = $files['name'][$i];
        $fileType = $files['type'][$i];
        $fileSize = $files['size'][$i];
        $fileTmpName = $files['tmp_name'][$i];
        $fileError = $files['error'][$i];

        // Проверка ошибок загрузки
        if ($fileError !== UPLOAD_ERR_OK) {
            $response['message'] = "Error uploading file: $fileName";
            echo json_encode($response);
            exit;
        }

        // Проверка типа файла
        if (!in_array($fileType, $allowedTypes)) {
            $response['message'] = "Invalid file type for: $fileName";
            echo json_encode($response);
            exit;
        }

        // Проверка размера файла
        if ($fileSize > $maxFileSize) {
            $response['message'] = "File too large: $fileName";
            echo json_encode($response);
            exit;
        }

        // Очистка имени файла
        $fileName = preg_replace("/[^a-zA-Z0-9\._-]/", "", $fileName);
        $filePath = $uploadDir . DIRECTORY_SEPARATOR . $fileName;

        // Проверка на существование файла
        $baseName = pathinfo($fileName, PATHINFO_FILENAME);
        $extension = pathinfo($fileName, PATHINFO_EXTENSION);
        $counter = 1;
        while (file_exists($filePath)) {
            $fileName = $baseName . "_$counter." . $extension;
            $filePath = $uploadDir . DIRECTORY_SEPARATOR . $fileName;
            $counter++;
        }

        // Перемещаем файл в каталог
        if (move_uploaded_file($fileTmpName, $filePath)) {
            $response['files'][] = $fileName;
        } else {
            $response['message'] = "Failed to save file: $fileName";
            echo json_encode($response);
            exit;
        }
    }

    $response['success'] = true;
    $response['message'] = 'Files uploaded successfully !!!';
} else {
    $response['message'] = 'No files uploaded';
}

echo json_encode($response);
?>