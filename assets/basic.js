import { Viewer } from 'photo-sphere-viewer';

const viewer = new Viewer({
    container: document.querySelector('#viewer'),
    panorama: '/pano/sphere-test.jpg'
});
