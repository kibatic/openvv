import { Viewer } from '@photo-sphere-viewer/core';
import { VirtualTourPlugin } from '@photo-sphere-viewer/virtual-tour-plugin';
import { MarkersPlugin } from '@photo-sphere-viewer/markers-plugin';

const container = document.querySelector('#viewer');

const nodes = JSON.parse(container.dataset.nodes);

const viewer = new Viewer({
    container: container,
    navbar: [
        'zoom',
        'move',
        'download',
        'caption',
        'fullscreen',
    ],
    plugins: [
        [VirtualTourPlugin, {
            positionMode: 'manual',
            renderMode  : '3d', // '3d' or 'markers'
            // Depuis PSV 5, les transitions se configurent via `transitionOptions`
            // ('transition' n'existe plus). effect 'none' + rotation false =
            // changement de nœud instantané, sans animation.
            transitionOptions: {
                effect: 'none',
                rotation: false,
            },
        }],
        [MarkersPlugin, {
        }]
    ]
});

const virtualTour = viewer.getPlugin(VirtualTourPlugin);
const markersPlugin = viewer.getPlugin(MarkersPlugin);

const linkRotations = JSON.parse(container.dataset.linkRotations);
const mediaRotations = JSON.parse(container.dataset.mediaRotations);

// On impose l'orientation à chaque changement de nœud. Le listener est attaché
// AVANT setNodes() pour ne pas manquer le node-changed du nœud initial (chargé
// automatiquement par le plugin).
virtualTour.addEventListener('node-changed', ({node, data}) => {
    if (data.fromNode) { // other data are available
        viewer.rotate(linkRotations[data.fromNode.id][node.id]);
    } else {
        viewer.rotate(mediaRotations[node.id]);
    }
});

virtualTour.setNodes(nodes);
