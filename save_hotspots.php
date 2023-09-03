<?php
    header('Content-Type: application/json');

    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        $json = file_get_contents('php://input');
        $data = json_decode($json, true);

        $hotspotsJSON = $data['hotspots'];
        $projectName = $data['project'];

        $filePath = "projects/$projectName/hotspots.json";

        if (file_put_contents($filePath, $hotspotsJSON)) {
            echo json_encode(['success' => true]);
        } else {
            echo json_encode(['success' => false]);
        }
    } else {
        echo json_encode(['success' => false]);
    }
?>
