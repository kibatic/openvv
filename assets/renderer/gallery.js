import { Viewer } from '@photo-sphere-viewer/core';
import { GalleryPlugin } from '@photo-sphere-viewer/gallery-plugin';
import { AutorotatePlugin } from '@photo-sphere-viewer/autorotate-plugin';

const container = document.querySelector('#viewer');

const viewer = new Viewer({
    container: container,
    panorama: container.dataset.panorama,
    caption: container.dataset.caption,
    navbar: [
        'autorotate',
        'caption',
        'fullscreen',
    ],
    plugins: [
        [GalleryPlugin, {
            visibleOnLoad: true,
            hideOnClick: false
        }],
        [AutorotatePlugin, {
            autostartDelay: 30000,
            autostartOnIdle: false,
        }]
    ],
});

const gallery = viewer.getPlugin(GalleryPlugin);
gallery.setItems(JSON.parse(container.dataset.items));
const autorotate = viewer.getPlugin(AutorotatePlugin);
autorotate.stop();
