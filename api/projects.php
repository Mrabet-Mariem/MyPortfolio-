<?php
header('Content-Type: application/json; charset=utf-8');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET');

require_once '../config/db.php';

if ($_SERVER['REQUEST_METHOD'] !== 'GET') {
    http_response_code(405);
    echo json_encode(['success' => false, 'message' => 'Method not allowed']);
    exit;
}

$sql = "SELECT id, title_en, title_fr, title_ar, title_tr,
               desc_en, desc_fr, desc_ar, desc_tr,
               tech, github_url, live_url, image
        FROM projects
        ORDER BY created_at DESC";

$result = $conn->query($sql);

if (!$result) {
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'message' => 'Query failed: ' . $conn->error
    ]);
    exit;
}

$projects = [];
while ($row = $result->fetch_assoc()) {
    // Cast id to int
    $row['id'] = (int)$row['id'];
    $projects[] = $row;
}

echo json_encode([
    'success'  => true,
    'count'    => count($projects),
    'projects' => $projects
]);

$conn->close();
?>
