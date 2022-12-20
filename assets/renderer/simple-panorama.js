import { Viewer } from '@photo-sphere-viewer/core';

const container = document.querySelector('#viewer');

const viewer = new Viewer({
    container: container,
    panorama: container.dataset.panorama,
    caption: container.dataset.caption,
    navbar: [
        'caption',
        'fullscreen',
    ],
    plugins: [
        [AutorotatePlugin, {
            autostartDelay: 30000,
            autostartOnIdle: false,
        }]
    ],
});
viewer.rotate(JSON.parse(container.dataset.initialPosition));
const autorotate = viewer.getPlugin(AutorotatePlugin);
autorotate.stop();
