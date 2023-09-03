<!DOCTYPE html>
<html>
<head>
    <title>Neue 360° Tour</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body>

<div class="container mt-5">
    <a href="index.php" class="btn btn-secondary mb-4">zurück</a>
    <h1>Neue 360° Tour</h1>

    <form id="tourForm" action="create_tour_handler.php" method="post" enctype="multipart/form-data">
        <div class="mb-3">
            <label for="projectName" class="form-label">Projekt Name</label>
            <input type="text" class="form-label" id="projectName" name="projectName" required>
        </div>

        <div class="mb-3" id="imageUpload" style="display:none;">
            <label for="images" class="form-label">Upload Bilder</label>
            <input type="file" class="form-control" id="images" name="images[]" multiple>
        </div>

        <button type="submit" class="btn btn-primary">weiter</button>
    </form>

    <!-- A-Frame preview will go here -->

    <!-- <button id="deleteProject" class="btn btn-danger mt-4" style="display:none;">Delete Project</button> -->
</div>

<script>
    // Enable image upload once project name is entered
    document.getElementById('projectName').addEventListener('input', function() {
        document.getElementById('imageUpload').style.display = 'block';
    });

    // Enable delete button once project is created
    // This is just a placeholder; you'll need to set this based on your actual project creation logic
    // document.getElementById('deleteProject').style.display = 'block';
</script>

<script>
    // Enable image upload once project name is entered
    document.getElementById('projectName').addEventListener('input', function() {
        document.getElementById('imageUpload').style.display = 'block';
    });

    // Handle form submission with progress indicator
    document.getElementById('tourForm').addEventListener('submit', function(event) {
        event.preventDefault();

        // Show progress indicator (you can customize this part)
        document.body.innerHTML += '<div id="progress">Uploading...</div>';

        // Create FormData object and append form data
        const formData = new FormData(event.target);

        // Create XMLHttpRequest and handle progress
        const xhr = new XMLHttpRequest();
        xhr.open('POST', 'create_tour_handler.php', true);

        xhr.upload.addEventListener('progress', function(event) {
            if (event.lengthComputable) {
                const percentComplete = (event.loaded / event.total) * 100;
                document.getElementById('progress').textContent = 'Uploading... ' + percentComplete.toFixed(2) + '%';
            }
        });

        xhr.addEventListener('load', function() {
            if (xhr.status === 200) {
                // Get sanitized project name from response (you'll need to send this from your PHP script)
                const sanitizedProjectName = xhr.responseText;  // Assume the PHP script returns the sanitized name

                // Redirect or handle successful upload
                window.location.href = 'edit_tour.php?project=' + sanitizedProjectName;
            } else {
                // Handle error
                alert('An error occurred');
            }
        });


        // Send the request
        xhr.send(formData);
    });
</script>

</body>
</html>
