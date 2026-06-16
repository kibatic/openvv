import { Viewer } from '@photo-sphere-viewer/core';
import { MarkersPlugin } from '@photo-sphere-viewer/markers-plugin';

const sourceContainer = document.querySelector('#sourceViewer');
const targetContainer = document.querySelector('#targetViewer');

// Construit une position {yaw, pitch} depuis les data-attributes, ou null si
// l'une des valeurs est absente (lien dont la source n'est pas encore placée).
function readPosition(container) {
    const { yaw, pitch } = container.dataset;
    if (yaw === undefined || yaw === '' || pitch === undefined || pitch === '') {
        return null;
    }
    return { yaw, pitch };
}

const sourcePosition = readPosition(sourceContainer);
const targetPosition = readPosition(targetContainer);

// Marqueur indiquant la position du lien sur l'image source (si déjà définie).
const sourceMarkers = sourcePosition
    ? [{
        id: 'old-marker',
        circle: 20,
        position: sourcePosition,
        tooltip: 'Source image marker position',
    }]
    : [];

const sourceViewer = new Viewer({
    container: sourceContainer,
    plugins: [
        [MarkersPlugin, { markers: sourceMarkers }],
    ],
});

// On impose la position au chargement : sans l'option `position`, Photo Sphere
// Viewer applique les métadonnées XMP/GPano de l'image (souvent 0,0) et écrase
// l'orientation voulue. Voir mediaEdit.js pour le détail.
sourceViewer.setPanorama(sourceContainer.dataset.panorama, sourcePosition ? { position: sourcePosition } : {});

const targetViewer = new Viewer({
    container: targetContainer,
});

const sourceMarkersPlugin = sourceViewer.getPlugin(MarkersPlugin);

sourceViewer.addEventListener('click', ({ data }) => {
    sourceMarkersPlugin.clearMarkers();
    sourceMarkersPlugin.addMarker({
        id: 'new-marker',
        circle: 20,
        position: {
            yaw: data.yaw,
            pitch: data.pitch
        },
        tooltip: 'Source image marker position'
    });
    document.querySelector('#edit_link_sourceYaw').value = data.yaw;
    document.querySelector('#edit_link_sourcePitch').value = data.pitch;
});

// Même logique que la source : on impose la position cible et on n'écoute les
// déplacements qu'une fois le panorama chargé et positionné.
targetViewer.setPanorama(targetContainer.dataset.panorama, { position: targetPosition }).then(() => {
    targetViewer.addEventListener('position-updated', ({ position }) => {
        document.querySelector('#edit_link_targetYaw').value = position.yaw;
        document.querySelector('#edit_link_targetPitch').value = position.pitch;
    });
});
