import { Viewer } from '@photo-sphere-viewer/core';

const container = document.querySelector('#viewer');

// URL du panorama et position initiale enregistrée, lues sur le conteneur.
const panorama = container.dataset.panorama;
const initialPosition = JSON.parse(container.dataset.initialPosition);

const viewer = new Viewer({
    container: container,
});

// On charge le panorama en imposant explicitement la position initiale.
// Sans l'option `position`, Photo Sphere Viewer applique les métadonnées
// XMP/GPano de l'image (InitialViewHeading/Pitch, souvent à 0) APRÈS le
// chargement, ce qui écrasait la position enregistrée pour ce média.
// On n'attache l'écoute des déplacements qu'une fois le panorama chargé et
// positionné, pour ne pas capter d'événements transitoires.
viewer.setPanorama(panorama, { position: initialPosition }).then(() => {
    viewer.addEventListener('position-updated', ({ position }) => {
        document.querySelector('#media_edit_initialYaw').value = position.yaw;
        document.querySelector('#media_edit_initialPitch').value = position.pitch;
    });
});
