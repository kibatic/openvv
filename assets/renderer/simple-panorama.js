import { Viewer } from 'photo-sphere-viewer';

const container = document.querySelector('#viewer');

const viewer = new Viewer({
    container: container,
    panorama: container.dataset.panorama,
    caption: container.dataset.caption,
    navbar: [
        'autorotate',
        'caption',
        'fullscreen',
    ]
});
viewer.rotate(JSON.parse(container.dataset.initialPosition));
