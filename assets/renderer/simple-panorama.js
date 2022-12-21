import { Viewer } from '@photo-sphere-viewer/core';
import { AutorotatePlugin } from '@photo-sphere-viewer/autorotate-plugin';

const container = document.querySelector('#viewer');

const initialPosition = JSON.parse(container.dataset.initialPosition);

const viewer = new Viewer({
    container: container,
    panorama: container.dataset.panorama,
    caption: container.dataset.caption,
    defaultYaw: initialPosition.yaw,
    defaultPitch: initialPosition.pitch,
    navbar: [
        'autorotate',
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
const autorotate = viewer.getPlugin(AutorotatePlugin);
autorotate.stop();
