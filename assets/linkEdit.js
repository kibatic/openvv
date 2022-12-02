import { Viewer } from 'photo-sphere-viewer';
import { MarkersPlugin } from 'photo-sphere-viewer/dist/plugins/markers';

const sourceContainer = document.querySelector('#sourceViewer');
const targetContainer = document.querySelector('#targetViewer');

const sourceViewer = new Viewer({
    container: sourceContainer,
    panorama: sourceContainer.dataset.panorama,
    plugins: [
        [MarkersPlugin, {
            markers: [
                {
                    id: 'old-marker',
                    circle: 20,
                    longitude: sourceContainer.dataset.longitude,
                    latitude: sourceContainer.dataset.latitude,
                    tooltip: 'Source image marker position'
                }
            ],
        }],
    ]
});
sourceViewer.rotate({longitude:sourceContainer.dataset.longitude, latitude:sourceContainer.dataset.latitude});

const targetViewer = new Viewer({
    container: targetContainer,
    panorama: targetContainer.dataset.panorama,
});
targetViewer.rotate({longitude:targetContainer.dataset.longitude, latitude:targetContainer.dataset.latitude});


const sourceMarkersPlugin = sourceViewer.getPlugin(MarkersPlugin);

sourceViewer.on('click', (e, data) => {
    sourceMarkersPlugin.clearMarkers();
    sourceMarkersPlugin.addMarker({
        id: 'new-marker',
        circle: 20,
        // x: data.textureX,
        // y: data.textureY,
        longitude: data.longitude,
        latitude: data.latitude,
        tooltip: 'Source image marker position'
    });
    document.querySelector('#edit_link_sourceLongitude').value = data.longitude;
    document.querySelector('#edit_link_sourceLatitude').value = data.latitude;
});

targetViewer.on('position-updated', (e, position) => {
    document.querySelector('#edit_link_targetLongitude').value = position.longitude;
    document.querySelector('#edit_link_targetLatitude').value = position.latitude;
});
