<?php
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Sanitize project name
    $projectName = preg_replace('/[^a-zA-Z0-9-_]/', '', str_replace(' ', '-', strtolower($_POST['projectName'])));


    // Check if project already exists and append a counter if it does
    $counter = 1;
    $originalName = $projectName;
    while (file_exists("projects/$projectName")) {
        $projectName = $originalName . '_' . $counter;
        $counter++;
    }

    // Create project folder
    mkdir("projects/$projectName");

    // Handle image upload
    if (isset($_FILES['images'])) {
        $uploadDir = "projects/$projectName/";
        foreach ($_FILES['images']['tmp_name'] as $key => $tmp_name) {
            $filename = $_FILES['images']['name'][$key];
            
            // Sanitize filename: Replace spaces with dashes and remove non-web-safe characters
            $filename = preg_replace('/[^a-zA-Z0-9-_\.]/', '', str_replace(' ', '-', strtolower($filename)));
            
            $targetFile = $uploadDir . $filename;
            move_uploaded_file($tmp_name, $targetFile);
        }
    }


    // Create JSON file for extra data
    // $json_data = json_encode(['hotspots' => []], JSON_PRETTY_PRINT);
    // file_put_contents("projects/$projectName/data.json", $json_data);

    // All operations successful, echo sanitized project name
    echo $projectName;

}
?>
