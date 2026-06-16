import { Viewer } from '@photo-sphere-viewer/core';

const container = document.querySelector('#viewer');

const initialPosition = JSON.parse(container.dataset.initialPosition);

const viewer = new Viewer({
    container: container,
    caption: container.dataset.caption,
    navbar: [
        'zoom',
        'move',
        'download',
        'caption',
        'fullscreen',
    ]
});

// On impose la position au chargement : sans l'option `position`, Photo Sphere
// Viewer applique les métadonnées XMP/GPano de l'image (souvent 0,0) et écrase
// l'orientation enregistrée. Voir mediaEdit.js pour le détail.
viewer.setPanorama(container.dataset.panorama, { position: initialPosition });
