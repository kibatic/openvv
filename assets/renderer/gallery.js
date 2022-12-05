import { Viewer } from 'photo-sphere-viewer';
import { GalleryPlugin } from 'photo-sphere-viewer/dist/plugins/gallery';

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
    ],
});

const gallery = viewer.getPlugin(GalleryPlugin);
gallery.setItems(JSON.parse(container.dataset.items));
viewer.once('open-panel', () => {
    console
});
