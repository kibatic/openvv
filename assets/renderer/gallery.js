import { Viewer } from '@photo-sphere-viewer/core';
import { GalleryPlugin } from '@photo-sphere-viewer/gallery-plugin';
import { AutorotatePlugin } from '@photo-sphere-viewer/autorotate-plugin';

const container = document.querySelector('#viewer');

const items = JSON.parse(container.dataset.items);

const viewer = new Viewer({
    container: container,
    panorama: items[0].panorama,
    thumbnail: items[0].thumbnail,
    caption: items[0].caption,
    defaultYaw: items[0].defaultYaw,
    defaultPitch: items[0].defaultPitch,
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
gallery.setItems(items);
const autorotate = viewer.getPlugin(AutorotatePlugin);
autorotate.stop();
