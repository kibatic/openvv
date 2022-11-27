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
            renderMode  : VirtualTourPlugin.MODE_3D
        }],

        [MarkersPlugin, {
        }]
    ]
});

const virtualTour = viewer.getPlugin(VirtualTourPlugin);
const markersPlugin = viewer.getPlugin(MarkersPlugin);

virtualTour.setNodes([
    {
        id: '1',
        panorama: panorama,
        thumbnail: panorama,
        name: 'One',
        links: [
            // la ligne suivante crée un lien sur le node 2
            // c'est le lien qui vient du node 2, il est orienté vers la longitude et latitude
            // et le focuse de la fenêtre est vers 100, 100 du node 1
            { nodeId: '2', x: 100, y: 1000, latitude: '45deg', longitude: '45deg' }
        ],
        panoData: { poseHeading: 318 }
    },
    {
        id: '2',
        panorama: '/pano/sphere-test.jpg',
        thumbnail: '/pano/sphere-test.jpg',
        name: 'Two',
        links: [
            { nodeId: '1', latitude: 0, longitude: 0 }
        ],
        panoData: { poseHeading: 318 }
    }
]);
