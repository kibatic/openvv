import { Viewer } from '@photo-sphere-viewer/core';
import { VirtualTourPlugin } from '@photo-sphere-viewer/virtual-tour-plugin';
import { MarkersPlugin } from '@photo-sphere-viewer/markers-plugin';

const container = document.querySelector('#viewer');

const nodes = JSON.parse(container.dataset.nodes);

const viewer = new Viewer({
    container: container,
    panorama: nodes[0].panorama,
    thumbnail: nodes[0].thumbnail,
    caption: nodes[0].caption,
    defaultYaw: nodes[0].defaultYaw,
    defaultPitch: nodes[0].defaultPitch,
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
            transition : false
        }],
        [MarkersPlugin, {
        }]
    ]
});

const virtualTour = viewer.getPlugin(VirtualTourPlugin);
const markersPlugin = viewer.getPlugin(MarkersPlugin);

virtualTour.setNodes(nodes);
const linkRotations = JSON.parse(container.dataset.linkRotations);
const mediaRotations = JSON.parse(container.dataset.mediaRotations);

virtualTour.addEventListener('node-changed', ({node, data}) => {
    if (data.fromNode) { // other data are available
        viewer.rotate(linkRotations[data.fromNode.id][node.id]);
    } else {
        viewer.rotate(mediaRotations[node.id]);
    }
});
