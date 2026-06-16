import { Viewer } from '@photo-sphere-viewer/core';
import { GalleryPlugin } from '@photo-sphere-viewer/gallery-plugin';

const container = document.querySelector('#viewer');

const items = JSON.parse(container.dataset.items);

// On impose la position de chaque item via l'option `position` (que le plugin
// galerie transmet à setPanorama) pour éviter que les métadonnées XMP/GPano des
// images (souvent 0,0) écrasent l'orientation enregistrée. Voir mediaEdit.js.
items.forEach((item) => {
    item.options = {
        ...item.options,
        position: { yaw: item.defaultYaw, pitch: item.defaultPitch },
    };
});

const viewer = new Viewer({
    container: container,
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

// Chargement du premier panorama avec sa légende et sa position.
viewer.setPanorama(items[0].panorama, items[0].options);
