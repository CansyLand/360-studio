<?php

session_start();

// Check if user is logged in
if (!isset($_SESSION['loggedin'])) {
    header('Location: login.php');
    exit;
}

// Scan the "projects" directory for project folders
$project_dirs = array_filter(glob('projects/*'), 'is_dir');


// Function to get the first image in a directory
function get_first_image($dir) {
    $files = array_diff(scandir($dir), array('..', '.'));
    foreach ($files as $file) {
        $ext = pathinfo($file, PATHINFO_EXTENSION);
        if (in_array($ext, array('jpg', 'jpeg', 'png', 'gif'))) {
            return $file;
        }
    }
    return null;
}

?>
<!DOCTYPE html>
<html>
<head>
    <title>Projektübersicht</title>
    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body>

<div class="container mt-5">
    <div class="row">
        <div class="col-12 text-center mb-4">
            <h1>Project Overview</h1>
        </div>
        <div class="col-12 text-center mb-4">
            <a href="create_tour.php" class="btn btn-primary">Neue Tour +</a>
        </div>


        <?php foreach ($project_dirs as $dir): ?>
            <div class="col-md-4">
                <div class="card">
                    <!-- Display the first image as a preview -->
                    <?php 
                    $first_image = get_first_image($dir); 
                    #var_dump($first_image);  // Debug output
                    #var_dump($dir);  // Debug output
                    ?>
                    <?php if ($first_image): ?>
                        <img src="<?php echo $dir . '/' . $first_image; ?>" class="card-img-top" alt="...">
                    <?php else: ?>
                        <div class="card-img-top bg-secondary text-white text-center py-4">
                            No Image
                        </div>
                    <?php endif; ?>
                    <div class="card-body">
                        <h5 class="card-title"><?php echo basename($dir); ?></h5>
                        <div class="d-flex">
                            <a href="preview.php?project=<?php echo basename($dir); ?>" class="btn btn-primary mr-2">ansehen</a>
                            <a href="edit_tour.php?project=<?php echo basename($dir); ?>" class="btn btn-secondary">bearbeiten</a>
                        </div>
                    </div>

                </div>
            </div>
        <?php endforeach; ?>


    </div>
</div>

</body>
</html>

