import { Viewer } from '@photo-sphere-viewer/core';
import { MarkersPlugin } from '@photo-sphere-viewer/markers-plugin';

const container = document.querySelector('#viewer');

// get the panorama URL from the container's data attribute
const panorama = container.dataset.panorama;

const initialPosition = JSON.parse(container.dataset.initialPosition);

const viewer = new Viewer({
    container: container,
    panorama: panorama,
    defaultYaw: initialPosition.yaw,
    defaultPitch: initialPosition.pitch,

    plugins: [
        [MarkersPlugin, {
            markers: [
            ],
        }],
    ]
});
const markersPlugin = viewer.getPlugin(MarkersPlugin);

viewer.addEventListener('click', ({ data }) => {
    console.log(`${data.rightclick?'right ':''}clicked at longitude: ${data.yaw} latitude: ${data.pitch}`);
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
    document.querySelector('#link_sourceLongitude').value = data.yaw;
    document.querySelector('#link_sourceLatitude').value = data.pitch;
});

