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
    defaultPitch: initialPosition.pitch
});

viewer.addEventListener('position-updated', ({ position }) => {
    document.querySelector('#media_edit_initialLongitude').value = position.yaw;
    document.querySelector('#media_edit_initialLatitude').value = position.pitch;
});
