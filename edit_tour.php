<!DOCTYPE html>
<html>
<head>
    <title>Edit 360 Tour</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <script src="https://aframe.io/releases/1.2.0/aframe.min.js"></script>
    <style>
        .image-wrapper {
            position: relative;
            height: 400px;
        }
        a-scene {
            height: 100%;
            width: 100%;
        }
        .a-enter-vr {
            display: none !important;
        }
    </style>

    <script>

        const URL = "https://cvm-cadstudio.eu/360/"

    </script>
</head>
<body>

    <div class="container mt-5">
        <a href="index.php" class="btn btn-secondary mb-4">Back</a>
        <h1>Edit 360 Tour</h1>
        <div class="row">
            <?php
            $projectName = $_GET['project'] ?? '';
            $counter = 0;
            if (!empty($projectName)) {
                // Load JSON
                $jsonFilePath = "projects/$projectName/hotspots.json";

                if (file_exists($jsonFilePath)) {
                    $jsonContent = file_get_contents($jsonFilePath);
                    $hotspotsArray = json_decode($jsonContent, true);  // true to get an associative array

                    // Now $hotspotsArray contains the data from hotspots.json
                } else {
                    echo "JSON file does not exist.";
                }


                $images = glob("projects/$projectName/*.{jpg,png}", GLOB_BRACE);
                foreach ($images as $image) {
                    $html = <<<HTML
                    <div class="col-12 mb-4">
                        <div class="row">
                            <div class="col-md-3">
                                <h3>Szene $counter</h3>
                            </div>
                            <div class="col-md-3">
                                <div class="form-check">
                                    <input class="form-check-input" type="radio" name="startbild" id="startbildRadio-$counter">
                                    <label class="form-check-label" for="startbildRadio-$counter">Startbild</label>
                                </div>
                            </div>
                            <div class="col-md-3">
                                <input type="text" id="sceneName-$counter" class="form-control" placeholder="Szenen Name">
                            </div>
                            <div class="col-md-3">
                                <button id="cameraDirection-$counter" class="btn btn-primary camera-direction" onclick="saveCameraDirection(this.id)">Blickrichtung</button>
                            </div>
                        </div>
                        <div class="image-wrapper">
                            <a-scene embedded data-index="$counter" data-img="$image">
                                <a-sky src="$image"></a-sky>
                                <a-camera position="0 0 0" wasd-controls-enabled="false" look-controls="reverseMouseDrag: true">
                                    <a-sphere class="cursor-sphere" position="0 0 -10" color="#4CC3D9" log-intersection-point></a-sphere>
                                    <a-cone position="0 0 -10" color="yellow" height="2.5" radius-bottom="1" radius-top= "0.2" rotation="90 0 0"> </a-cone>
                                    <a-cursor></a-cursor>
                                </a-camera>
                            </a-scene>
                        </div>
                    </div>
                    HTML;
                    echo $html;
                    $counter++;
                }
            } else {
                echo '<div class="col-12">Dieses Projekt wurde nicht gefunden.</div>';
            }
            ?>
        </div>
        <button id="saveHotspots" class="btn btn-primary mt-4">Weiter</button>
    </div>

    

<script>

    // Global variables

    // Init scenes array with each scene being an object
    let scenes = [
        <?php
            $counter = 0;
            $scenes = [];
            foreach ($images as $imageURL) {
                $scene = [
                    'index' => $counter,
                    'startScene' => false,
                    'title' => '',
                    'image_url' => $imageURL,
                    'hotspots' => [],
                    'camera' => [
                        'rotation' => [
                            'x' => 0,
                            'y' => 0,
                            'z' => 0
                        ]
                    ]
                ];                
                $scenes[] = json_encode($scene);
                $counter++;
            }
            echo implode(',', $scenes);
        ?>
    ];



    

    // Initialize the application
    function init() {
        setupEventListeners();
        autoSelectFirstRadioButton();
    }

    // Setup event listeners
    function setupEventListeners() {
        document.getElementById('saveHotspots').addEventListener('click', saveHotspots);
        document.querySelectorAll('a-scene').forEach(scene => {
            scene.addEventListener('mouseover', setActiveScene);
            scene.addEventListener('mouseout', removeActiveScene);
        });
        window.addEventListener("keydown", handleKeyDown);
    }

    function saveCameraDirection(buttonId) {
        // Extract the index number from the button's id
        const index = buttonId.split('-')[1];
        
        // Find the corresponding <a-scene> element by its data-index attribute
        const activeScene = document.querySelector(`a-scene[data-index="${index}"]`);
        
        if (activeScene) {
            // Get the <a-camera> element within the active scene
            const camera = activeScene.querySelector('a-camera');
            
            if (camera) {
                // Get the camera's rotation
                const rotation = camera.getAttribute('rotation');
                
                // Save this rotation into your scene array object
                scenes[index].camera.rotation = rotation;
                
                console.log(`Saved rotation for scene ${index}:`, rotation);

                // Find the button by its id
                const buttonElement = document.getElementById(buttonId);
                
                // Change the button's background color to green
                if (buttonElement) {
                    buttonElement.style.backgroundColor = "green";
                }
            }
        }
    }


    // Save hotspots
    function saveHotspots() {
        // find start scene and update titles
        updateSceneInputs();
        const scenesJSON = JSON.stringify(scenes);
        fetch('save_hotspots.php', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify({ hotspots: scenesJSON, project: '<?php echo $projectName; ?>' })
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                window.location.href = 'preview.php?project=<?php echo $projectName; ?>';
            } else {
                alert('Failed to save hotspots.');
            }
        })
        .catch(error => console.error('Error:', error));
    }

    // Set active scene
    function setActiveScene() {
        this.setAttribute('data-active', 'true');
    }

    // Remove active scene
    function removeActiveScene() {
        this.removeAttribute('data-active');
    }

    // Handle keydown events
    function handleKeyDown(e) {
        if (e.keyCode === 86) { // 86 == "v" key
            setHotSpot();
        } else if (e.key === "x") {
            deleteSphere();
        }
    }

    function updateSceneInputs() {
        scenes.forEach(scene => {
            const index = scene.index;
            //  Fetch the radio button and label content by their IDs
            const startSceneRadio = document.getElementById(`startbildRadio-${index}`);
            const sceneTitle = document.getElementById(`sceneName-${index}`);

            // Get the values
            const startScene = startSceneRadio ? startSceneRadio.checked : false; // true or false based on whether the radio button is checked
            const title = sceneTitle ? sceneTitle.value : ''; // Get the value of the label (assuming it's an input field)

            scene.startScene = startScene;
            scene.title = title;

            // remove dom elements from array (spheres)
            scene.hotspots.forEach(hotspot => {
                hotspot.element = null
            });

        });

    }

   function setHotSpot() {
        const activeScene = document.querySelector('a-scene[data-active="true"]');
        if (!activeScene) return;

        // Get scene attributes
        const sceneIndex = activeScene.getAttribute('data-index');
        // const sceneImage = activeScene.getAttribute('data-img');

        // Get the movable sphere within the active scene
        const sphere = activeScene.querySelector('.cursor-sphere');
        if (!sphere) return;

        // Initialize Three.js objects
        const position = new THREE.Vector3();
        const quaternion = new THREE.Quaternion();
        const euler = new THREE.Euler();

        // Get world position and rotation
        sphere.object3D.getWorldPosition(position);
        sphere.object3D.getWorldQuaternion(quaternion);

        // Convert quaternion to Euler angles
        euler.setFromQuaternion(quaternion, 'XYZ');

        // Convert Euler angles to degrees
        const rotationInDegrees = {
            x: THREE.MathUtils.radToDeg(euler.x),
            y: THREE.MathUtils.radToDeg(euler.y),
            z: THREE.MathUtils.radToDeg(euler.z)
        };

        // Create and append a new sphere to the active scene
        const newSphere = createNewSphere(position);
        activeScene.appendChild(newSphere);

        const newCone = createNewCone(position, rotationInDegrees);
        activeScene.appendChild(newCone);

        // Prompt for next scene text
        const nextScene = prompt("Zu welcher Szene Linken?:");

        // Save hotspot data
        saveHotspotData(sceneIndex, position, rotationInDegrees, nextScene, newSphere);
    }

    function createNewSphere(position) {
        const newSphere = document.createElement('a-sphere');
        newSphere.setAttribute('position', position);
        newSphere.setAttribute('color', '#FF0000'); // Red color
        return newSphere;
    }

    function createNewCone(position, rotation = null) {
        const newCone = document.createElement('a-cone');
        newCone.setAttribute('position', position);
        newCone.setAttribute('color', 'black');
        newCone.setAttribute('radius-bottom', '0.5');
        newCone.setAttribute('radius-top', '0.1');
        newCone.setAttribute('height', '2');
        // newCone.setAttribute('wireframe', 'true');

        if( rotation ) {
            console.log("HAS ROTATION")
            console.log(rotation)
            const r = {
                x: rotation.x,
                y: rotation.y,
                z: rotation.z 
            }
            newCone.setAttribute('rotation', r);
        }
        return newCone;
    }


    function saveHotspotData(sceneIndex, position, rotationInDegrees, nextScene, newSphere) {
        scenes[sceneIndex].
            hotspots.push({
                position: {
                    x: position.x,
                    y: position.y,
                    z: position.z
                },
                rotation: rotationInDegrees,
                nextScene: nextScene,
                element: newSphere // Save the DOM element
            });
    }

    function deleteSphere() {
        // Find the active scene
        const activeScene = document.querySelector('a-scene[data-active="true"]');

        if (!activeScene) return;

        // Get the cursor sphere within the active scene
        const cameraSphere = activeScene.querySelector('.cursor-sphere');
        // Get the position of the camera's child sphere
        const cameraSpherePosition = new THREE.Vector3();
        cameraSphere.object3D.getWorldPosition(cameraSpherePosition);

        // Check for collision with each hotspot
        scenes.forEach(scene => {
            scene.hotspots.forEach((hotspot, index) => {
        
                const hotspotPosition = new THREE.Vector3(hotspot.position.x, hotspot.position.y, hotspot.position.z);
                
                // Calculate the distance between the camera's child sphere and the hotspot
                const distance = cameraSpherePosition.distanceTo(hotspotPosition);
                
                // Check for collision (assuming both spheres have a radius of 1)
                if (distance < 2) {
                    
                    // Remove the sphere from the DOM
                    hotspot.element.remove();

                    // Remove the hotspot from the array
                    hotspots.splice(index, 1);

                    }
                
            });
        });
        
        
    }



   // Auto-select the first radio button
   function autoSelectFirstRadioButton() {
        document.querySelector('input[name="startbild"]').checked = true;
    }

    // Initialize the application
    init();




    // Wait for all a-scenes to be loaded
    document.addEventListener('DOMContentLoaded', function() {
        console.log("DOM is loaded");
        // Get all a-scene elements
        const scenes = document.querySelectorAll('a-scene');
        let loadedScenes = 0;



        // Attach loaded event to each scene
        // scenes.forEach((scene) => {
        //     console.log("forEach");
        //     scene.addEventListener('loaded', function() {
        //         loadedScenes++;
        //         console.log(loadedScenes);
        //         if (loadedScenes === scenes.length) {
        //             allScenesLoaded();
        //         }
        //     });
        // });

        // var scene = document.querySelector('a-scene');

        // console.log(scene);
        
        // scene.addEventListener('hasLoaded', function() {
        //     console.log('loaded');
            
        // });

        allScenesLoaded();
    });

    function allScenesLoaded() {

    // Convert PHP array to JavaScript object
        var hotspots = <?php echo json_encode($hotspotsArray); ?>;
        console.log(hotspots);
        if (hotspots) {
            scenes = hotspots;

            scenes.forEach(scene => {
                const index = scene.index;
                document.getElementById("sceneName-" + index).value = scene.title;
                if(scene.startScene) {
                    console.log("start scene is: " + index);
                    document.getElementById("startbildRadio-" + index).checked = true;
                }

                if(scene.hotspots.length > 0) {
                    console.log("Scene " + index + " has Hotspots");
                    scene.hotspots.forEach(hotspot => {
                        console.log(hotspot);
                        // Create and append a new sphere to the active scene
                        // hotspot.element = createNewSphere(hotspot.position, hotspot.rotation);
                        hotspot.element = createNewSphere(hotspot.position);
                        const newCone = createNewCone(hotspot.position, hotspot.rotation);

                        // Get the active scene by its data-index attribute
                        const activeScene = document.querySelector(`a-scene[data-index="${index}"]`);
                        // const activeScene = document.querySelector(`a-scene[data-index="1"]`);

            
                        // index is scene.index

                        activeScene.appendChild(hotspot.element);
                        // Append the hotspot.element to the active scene
                        if (activeScene && hotspot.element) {
                            activeScene.appendChild(hotspot.element);
                            activeScene.appendChild(newCone);
                        }
                        
                    });
                    
                }
                
            });
        }
    }

</script>

<style>
    .a-enter-vr {
  display: none !important;
}

</style>

</body>
</html>







