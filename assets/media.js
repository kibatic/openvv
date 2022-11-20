import { Viewer } from 'photo-sphere-viewer';

const container = document.querySelector('#viewer');

// get the panorama URL from the container's data attribute
const panorama = container.dataset.panorama;

const viewer = new Viewer({
    container: container,
    panorama: panorama,
});
