import { Viewer } from 'photo-sphere-viewer';
import { VirtualTourPlugin } from 'photo-sphere-viewer/dist/plugins/virtual-tour';
import { MarkersPlugin } from 'photo-sphere-viewer/dist/plugins/markers';

const container = document.querySelector('#viewer');

// get the panorama URL from the container's data attribute
const panorama = container.dataset.panorama;

const viewer = new Viewer({
    container: container,
    panorama: panorama,
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

console.log(JSON.parse(container.dataset.nodes));
virtualTour.setNodes(JSON.parse(container.dataset.nodes));
const rotations = JSON.parse(container.dataset.rotations);

virtualTour.on('node-changed', (e, nodeId, data) => {
    console.log(`Current node is ${nodeId}`);
    console.log(data);
    if (data.fromNode) { // other data are available
        console.log(`Previous node was ${data.fromNode.id}`);
        console.log('rotation to : '+rotations[data.fromNode.id][nodeId])
        viewer.rotate(rotations[data.fromNode.id][nodeId]);
    }
});
