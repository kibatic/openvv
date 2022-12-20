import { Viewer } from '@photo-sphere-viewer/core';
import { MarkersPlugin } from '@photo-sphere-viewer/markers-plugin';

const sourceContainer = document.querySelector('#sourceViewer');
const targetContainer = document.querySelector('#targetViewer');

const sourceViewer = new Viewer({
    container: sourceContainer,
    panorama: sourceContainer.dataset.panorama,
    defaultYaw: sourceContainer.dataset.yaw,
    defaultPitch: sourceContainer.dataset.pitch,
    plugins: [
        [MarkersPlugin, {
            markers: [
                {
                    id: 'old-marker',
                    circle: 20,
                    position: {
                        yaw: sourceContainer.dataset.yaw,
                        pitch: sourceContainer.dataset.pitch
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
    defaultYaw: targetContainer.dataset.yaw,
    defaultPitch: targetContainer.dataset.pitch,
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
    document.querySelector('#edit_link_sourceYaw').value = data.yaw;
    document.querySelector('#edit_link_sourcePitch').value = data.pitch;
});

targetViewer.addEventListener('position-updated', ({ position }) => {
    document.querySelector('#edit_link_targetYaw').value = position.yaw;
    document.querySelector('#edit_link_targetPitch').value = position.pitch;
});
