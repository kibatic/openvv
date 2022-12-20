import { Viewer } from '@photo-sphere-viewer/core';
import { MarkersPlugin } from '@photo-sphere-viewer/markers-plugin';

const sourceContainer = document.querySelector('#sourceViewer');
const targetContainer = document.querySelector('#targetViewer');

const sourceViewer = new Viewer({
    container: sourceContainer,
    panorama: sourceContainer.dataset.panorama,
    defaultYaw: sourceContainer.dataset.longitude,
    defaultPitch: sourceContainer.dataset.latitude,
    plugins: [
        [MarkersPlugin, {
            markers: [
                {
                    id: 'old-marker',
                    circle: 20,
                    position: {
                        yaw: sourceContainer.dataset.longitude,
                        pitch: sourceContainer.dataset.latitude
                    },
                    tooltip: 'Source image marker position'
                }
            ],
        }],
    ]
});

const targetViewer = new Viewer({
    container: targetContainer,
    panorama: targetContainer.dataset.panorama,
    defaultYaw: targetContainer.dataset.longitude,
    defaultPitch: targetContainer.dataset.latitude,
});

const sourceMarkersPlugin = sourceViewer.getPlugin(MarkersPlugin);

sourceViewer.addEventListener('click', ({ data }) => {
    sourceMarkersPlugin.clearMarkers();
    sourceMarkersPlugin.addMarker({
        id: 'new-marker',
        circle: 20,
        position: {
            yaw: data.yaw,
            pitch: data.pitch
        },
        tooltip: 'Source image marker position'
    });
    document.querySelector('#edit_link_sourceLongitude').value = data.yaw;
    document.querySelector('#edit_link_sourceLatitude').value = data.pitch;
});

targetViewer.addEventListener('position-updated', ({ position }) => {
    document.querySelector('#edit_link_targetLongitude').value = position.yaw;
    document.querySelector('#edit_link_targetLatitude').value = position.pitch;
});
