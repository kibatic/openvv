import { Viewer } from '@photo-sphere-viewer/core';

const container = document.querySelector('#viewer');

const initialPosition = JSON.parse(container.dataset.initialPosition);

const viewer = new Viewer({
    container: container,
    panorama: container.dataset.panorama,
    caption: container.dataset.caption,
    defaultYaw: initialPosition.yaw,
    defaultPitch: initialPosition.pitch,
    navbar: [
        'zoom',
        'move',
        'download',
        'caption',
        'fullscreen',
    ]
});
