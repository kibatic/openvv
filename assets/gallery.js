import { Viewer } from 'photo-sphere-viewer';
import { GalleryPlugin } from 'photo-sphere-viewer/dist/plugins/gallery';

const baseUrl = 'https://photo-sphere-viewer-data.netlify.app/assets/';
const container = document.querySelector('#viewer');

const viewer = new Viewer({
    container: container,
    panorama: container.dataset.panorama,
    caption: container.dataset.caption,
    loadingImg: baseUrl + 'loader.gif',
    touchmoveTwoFingers: true,
    mousewheelCtrlKey: true,

    plugins: [
        [GalleryPlugin, {
            visibleOnLoad: true,
            hideOnClick: false
        }],
    ],
});

const gallery = viewer.getPlugin(GalleryPlugin);
console.log(JSON.parse(container.dataset.items));
gallery.setItems(JSON.parse(container.dataset.items));
viewer.once('open-panel', () => {
    viewer.rotate({latitude:"0deg", longitude:"0deg"});
    console.log(`viewer is ready`);
});
