import { Viewer } from '@photo-sphere-viewer/core';
import { MarkersPlugin } from '@photo-sphere-viewer/markers-plugin';

const container = document.querySelector('#viewer');

// get the panorama URL from the container's data attribute
const panorama = container.dataset.panorama;

const initialPosition = JSON.parse(container.dataset.initialPosition);

const viewer = new Viewer({
    container: container,
    plugins: [
        [MarkersPlugin, {
            markers: [
            ],
        }],
    ]
});

// On impose la position au chargement : sans l'option `position`, Photo Sphere
// Viewer applique les métadonnées XMP/GPano de l'image (souvent 0,0) et écrase
// l'orientation enregistrée. Voir mediaEdit.js pour le détail.
viewer.setPanorama(panorama, { position: initialPosition });

const markersPlugin = viewer.getPlugin(MarkersPlugin);

viewer.addEventListener('click', ({ data }) => {
    console.log(`${data.rightclick?'right ':''}clicked at yaw: ${data.yaw} pitch: ${data.pitch}`);
    console.log(data);
    markersPlugin.clearMarkers();
    markersPlugin.addMarker({
        id: 'new-marker',
        circle: 20,
        position: {
            yaw: data.yaw,
            pitch: data.pitch
        },
        tooltip: 'circle marker'
    });
    document.querySelector('#link_sourceYaw').value = data.yaw;
    document.querySelector('#link_sourcePitch').value = data.pitch;
});

