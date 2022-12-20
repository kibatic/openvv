import { Viewer } from '@photo-sphere-viewer/core';
import { VirtualTourPlugin } from '@photo-sphere-viewer/virtual-tour-plugin';
import { MarkersPlugin } from '@photo-sphere-viewer/markers-plugin';

const container = document.querySelector('#viewer');

// get the panorama URL from the container's data attribute
const panorama = container.dataset.panorama;

const viewer = new Viewer({
    container: container,
    panorama: panorama,
    // navbar: [
    //     'autorotate',
    //     'caption',
    //     'fullscreen',
    // ],
    plugins: [
        [VirtualTourPlugin, {
            positionMode: 'manual',
            renderMode  : '3d',
            transition : false
        }],

        [MarkersPlugin, {
        }]
    ]
});

const virtualTour = viewer.getPlugin(VirtualTourPlugin);
const markersPlugin = viewer.getPlugin(MarkersPlugin);

virtualTour.setNodes(JSON.parse(container.dataset.nodes));
const linkRotations = JSON.parse(container.dataset.linkRotations);
const mediaRotations = JSON.parse(container.dataset.mediaRotations);

virtualTour.addEventListener('node-changed', ({node, data}) => {
    if (data.fromNode) { // other data are available
        viewer.rotate(linkRotations[data.fromNode.id][node.id]);
    } else {
        viewer.rotate(mediaRotations[node.id]);
    }
});
