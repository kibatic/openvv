import { Viewer } from 'photo-sphere-viewer';
import { MarkersPlugin } from 'photo-sphere-viewer/dist/plugins/markers';

const container = document.querySelector('#viewer');

// get the panorama URL from the container's data attribute
const panorama = container.dataset.panorama;

const viewer = new Viewer({
    container: container,
    panorama: panorama,
    plugins: [
        [MarkersPlugin, {
            markers: [
            ],
        }],
    ]
});
const markersPlugin = viewer.getPlugin(MarkersPlugin);
viewer.rotate(JSON.parse(container.dataset.initialPosition));

viewer.on('click', (e, data) => {
    console.log(`${data.rightclick?'right ':''}clicked at longitude: ${data.longitude} latitude: ${data.latitude}`);
    console.log(data);
    markersPlugin.clearMarkers();
    markersPlugin.addMarker({
        id: 'new-marker',
        circle: 20,
        // x: data.textureX,
        // y: data.textureY,
        longitude: data.longitude,
        latitude: data.latitude,
        tooltip: 'circle marker'
    });
    document.querySelector('#link_sourceLongitude').value = data.longitude;
    document.querySelector('#link_sourceLatitude').value = data.latitude;
});

