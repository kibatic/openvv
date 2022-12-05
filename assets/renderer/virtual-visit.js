import { Viewer } from 'photo-sphere-viewer';
import { VirtualTourPlugin } from 'photo-sphere-viewer/dist/plugins/virtual-tour';
import { MarkersPlugin } from 'photo-sphere-viewer/dist/plugins/markers';

const container = document.querySelector('#viewer');

// get the panorama URL from the container's data attribute
const panorama = container.dataset.panorama;

const viewer = new Viewer({
    container: container,
    panorama: panorama,
    navbar: [
        'autorotate',
        'caption',
        'fullscreen',
    ],
    plugins: [
        [VirtualTourPlugin, {
            positionMode: VirtualTourPlugin.MODE_MANUAL,
            renderMode  : VirtualTourPlugin.MODE_3D,
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

virtualTour.on('node-changed', (e, nodeId, data) => {
    if (data.fromNode) { // other data are available
        viewer.rotate(linkRotations[data.fromNode.id][nodeId]);
    } else {
        viewer.rotate(mediaRotations[nodeId]);
    }
});
