import { Viewer } from 'photo-sphere-viewer';
import { MarkersPlugin } from 'photo-sphere-viewer/dist/plugins/markers';

const container = document.querySelector('#viewer');

// get the panorama URL from the container's data attribute
const panorama = container.dataset.panorama;

const viewer = new Viewer({
    container: container,
    panorama: panorama,
});

viewer.rotate(JSON.parse(container.dataset.initialPosition));
viewer.on('position-updated', (e, position) => {
    document.querySelector('#media_edit_initialLongitude').value = position.longitude;
    document.querySelector('#media_edit_initialLatitude').value = position.latitude;
});
