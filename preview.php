<?php
    $projectName = isset($_GET['project']) ? $_GET['project'] : '';
    $startSceneIndex = 0;
    $initCameraPitch = 0;
    $initCameraYaw = 0;



    // Load hotspots from folder
    if (!empty($projectName)) {
        $jsonFile = "projects/$projectName/hotspots.json";
        if (file_exists($jsonFile)) {
            $jsonContent = file_get_contents($jsonFile);
            $scenes = json_decode($jsonContent, true);
            $return = findStartSceneIndex($scenes);
            $startSceneIndex = $return[0];
            $initCameraPitch = $return[1];
            $initCameraYaw = $return[2];
        }
    }


    function findStartSceneIndex($scenes) {
        foreach ($scenes as $index => $scene) {
            if (isset($scene['startScene']) && $scene['startScene'] === true) {
                $rotation = $scene['camera']['rotation'];
                $initCameraPitch = $rotation['x'];
                $initCameraYaw = $rotation['y'];
                return [$index, $initCameraPitch, $initCameraYaw];
            }
        }
        return 0; // Return 0 if no startScene is found to be true
    }

    
?>

<!DOCTYPE html>
<html>
  <head>
    <meta charset="utf-8">
    <title>360&deg; Image Gallery</title>
    <meta name="description" content="360&deg; Image Gallery - A-Frame">
    <script src="a-frame/aframe.min.js"></script>
    <script src="a-frame/aframe-event-set-component.min.js"></script>
    <script src="a-frame/aframe-layout-component.min.js"></script>
    <script src="a-frame/aframe-template-component.min.js"></script>
    <script src="a-frame/aframe-proxy-event-component.min.js"></script>

    
    <!-- Image link template to be reused. -->
    <script id="link" type="text/html">


      <a-entity class="link"
        geometry="primitive: plane; height: 1; width: 1"
        material="shader: flat; src: ${thumb}; transparent: true"
        event-set__mouseenter="scale: 1.2 1.2 1"
        event-set__mouseleave="scale: 1 1 1"
        event-set__click="_target: #image-360; _delay: 300; material.src: ${src}"
        proxy-event="event: click; to: #image-360; as: fade"
        sound="on: click; src: #click-sound"></a-entity>
    </script>

    <script>
      AFRAME.registerComponent('look-at-camera', {
        tick: function () {
          this.el.object3D.lookAt(this.el.sceneEl.camera.el.object3D.position);
        }
      });
    </script>
  </head>
  <body>
    <a-scene>
      <a-assets>
        <?php
          // Create 360 image reference
          foreach ($scenes as $scene) {
            $index = $scene['index'];
            $image_url = $scene['image_url'];
            echo '<img id="scene-' . $index . '" crossorigin="anonymous" src="' . $image_url . '">';
          }
        ?>
        <img id="hotspot-thumb" crossorigin="anonymous" src="a-frame/hotspot-2.png">
        <!-- <img id="city" crossorigin="anonymous" src="https://cdn.aframe.io/360-image-gallery-boilerplate/img/city.jpg">
        <img id="city-thumb" crossorigin="anonymous" src="https://cdn.aframe.io/360-image-gallery-boilerplate/img/thumb-city.jpg">
        <img id="sechelt-thumb" crossorigin="anonymous" src="https://cdn.aframe.io/360-image-gallery-boilerplate/img/thumb-sechelt.jpg"> -->
        <audio id="click-sound" crossorigin="anonymous" src="https://cdn.aframe.io/360-image-gallery-boilerplate/audio/click.ogg"></audio>
        <!-- <img id="cubes" crossorigin="anonymous" src="https://cdn.aframe.io/360-image-gallery-boilerplate/img/cubes.jpg">
        <img id="sechelt" crossorigin="anonymous" src="https://cdn.aframe.io/360-image-gallery-boilerplate/img/sechelt.jpg"> -->

      </a-assets>

      <!-- 360-degree image. -->
      <a-sky id="image-360" radius="60" src="#scene-<?= $startSceneIndex ?>" sky-listener
             animation__fade="property: components.material.material.color; type: color; from: #FFF; to: #000; dur: 300; startEvents: fade"
             animation__fadeback="property: components.material.material.color; type: color; from: #000; to: #FFF; dur: 300; startEvents: animationcomplete__fade"></a-sky>

      <!-- Image links. -->
      <a-entity id="links" > <!-- layout="type: line; margin: 1.5" position="0 -1 -4" -->
        <?php
            // Create 360 image reference
            foreach ($scenes as $scene) {
              $index = $scene['index'];
              $image_url = $scene['image_url'];
              $class = 'hotspot-scene-' . $index;
              $hotspots = $scene['hotspots'];

              foreach ($hotspots as $hotspot) {
                $p = $hotspot['position'];
                $r = $hotspot['rotation'];

                echo '<a-entity
                        class="' . $class . '"
                        template="src: #link" 
                        data-src="#scene-' . $hotspot['nextScene'] . '" 
                        data-thumb="#hotspot-thumb" 
                        position="' . $p['x'] . ' ' . $p['y'] . ' ' . $p['z'] . '" 
                        look-at-camera
                        material="transparent: true"
                        onclick="updateHotspotVisibility(this.getAttribute(`data-src`))"
                      ></a-entity>';
              }
            }
              
        ?>
        <!-- rotation="' . $r['x'] . ' ' . $r['y'] . ' ' . $r['z'] .'" -->
        <!-- <a-entity template="src: #link" data-src="#cubes" data-thumb="#cubes-thumb"></a-entity>
        <a-entity template="src: #link" data-src="#city" data-thumb="#city-thumb"></a-entity>
        <a-entity template="src: #link" data-src="#sechelt" data-thumb="#sechelt-thumb"></a-entity> -->
      </a-entity>

      <!-- Camera + cursor. -->
      <a-entity camera id="camera" look-controls="reverseMouseDrag: true;" rotation="0 185 0"> <!-- position="0 0 0" -->
        <a-cursor
          id="cursor"
          animation__click="property: scale; startEvents: click; from: 0.1 0.1 0.1; to: 1 1 1; dur: 150"
          animation__fusing="property: fusing; startEvents: fusing; from: 1 1 1; to: 0.1 0.1 0.1; dur: 1500"
          event-set__mouseenter="_event: mouseenter; color: springgreen"
          event-set__mouseleave="_event: mouseleave; color: black"
          raycaster="objects: .link"></a-cursor>
      </a-entity>
    </a-scene>
  </body>

  <script>

    function updateHotspotVisibility(dataSrc) {
        // dataSrc has format: #scene-2
        // Split the string by '-' and take the last element to get the number
        const nextSceneNumber = dataSrc.split('-').pop();

        console.log("Scene: " + nextSceneNumber);

        const allHotspots = document.querySelectorAll('[class^="hotspot-scene-"]');
        allHotspots.forEach(hotspot => {
          hotspot.setAttribute('visible', 'false');
        });

        const matchingHotspots = document.querySelectorAll(`.hotspot-scene-${nextSceneNumber}`);
        matchingHotspots.forEach(hotspot => {
          hotspot.setAttribute('visible', 'true');
        });

      // Rotate camera after scene change
      // setTimeout(function() {
      //     // setCameraRotation(-90, -90);
      // }, 300);  // 150 milliseconds delay

      }
    
      // // Keep just scene 0 hotspots on init
      updateHotspotVisibility("scene-<?= $startSceneIndex ?>");

      function setCameraRotation(pitch, yaw) {
        const camera = document.querySelector('a-entity[camera]');
        camera.components['look-controls'].pitchObject.rotation.set(THREE.Math.degToRad(pitch),0,0);
        camera.components['look-controls'].yawObject.rotation.set(0,THREE.Math.degToRad(yaw),0);
      }
      
      // Wait for A-Frame to be completly loaded
      document.addEventListener('DOMContentLoaded', function() {
        var scene = document.querySelector('a-scene');
        
        scene.addEventListener('loaded', function() {
          setCameraRotation(<?= $initCameraPitch ?>, <?= $initCameraYaw ?>);
        });
      });


  </script>
</html>