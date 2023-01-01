import { Viewer } from '@photo-sphere-viewer/core';
import { GalleryPlugin } from '@photo-sphere-viewer/gallery-plugin';

const container = document.querySelector('#viewer');

const items = JSON.parse(container.dataset.items);

const viewer = new Viewer({
    container: container,
    panorama: items[0].panorama,
    caption: items[0].options.caption,
    defaultYaw: items[0].defaultYaw,
    defaultPitch: items[0].defaultPitch,
    navbar: [
        'zoom',
        'move',
        'download',
        'gallery',
        'caption',
        'fullscreen',
    ],
    plugins: [
        [GalleryPlugin, {
            visibleOnLoad: true,
            hideOnClick: false
        }]
    ],
});

const gallery = viewer.getPlugin(GalleryPlugin);
gallery.setItems(items);
