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
const targetViewer = new Viewer({
    container: targetContainer,
    panorama: targetContainer.dataset.panorama,
    plugins: [
        [MarkersPlugin, {
            markers: [
                {
                    id: 'old-marker',
                    circle: 20,
                    longitude: targetContainer.dataset.longitude,
                    latitude: targetContainer.dataset.latitude,
                    tooltip: 'Target image orientation'
                }
            ],
        }],
    ]
});

const sourceMarkersPlugin = sourceViewer.getPlugin(MarkersPlugin);

sourceViewer.on('click', (e, data) => {
    console.log(`${data.rightclick?'right ':''}clicked at longitude: ${data.longitude} latitude: ${data.latitude}`);
    console.log(data);
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
    document.querySelector('#edit_link_sourceTextureX').value = data.textureX;
    document.querySelector('#edit_link_sourceTextureY').value = data.textureY;
});

const targetMarkersPlugin = targetViewer.getPlugin(MarkersPlugin);

targetViewer.on('click', (e, data) => {
    console.log(`${data.rightclick?'right ':''}clicked at longitude: ${data.longitude} latitude: ${data.latitude}`);
    console.log(data);
    targetMarkersPlugin.clearMarkers();
    targetMarkersPlugin.addMarker({
        id: 'new-marker',
        circle: 20,
        // x: data.textureX,
        // y: data.textureY,
        longitude: data.longitude,
        latitude: data.latitude,
        tooltip: 'Target image orientation'
    });
    document.querySelector('#edit_link_targetLongitude').value = data.longitude;
    document.querySelector('#edit_link_targetLatitude').value = data.latitude;
});


