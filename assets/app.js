/*
 * Welcome to your app's main JavaScript file!
 *
 * We recommend including the built version of this JavaScript file
 * (and its CSS file) in your base layout (base.html.twig).
 */

const $ = require('jquery');
require('bootstrap');

import './styles/app.scss';

// any CSS you import will output into a single css file (app.css in this case)
import '@photo-sphere-viewer/core/index.scss';
import '@photo-sphere-viewer/gallery-plugin/index.scss';
import '@photo-sphere-viewer/virtual-tour-plugin/index.scss';
import '@photo-sphere-viewer/markers-plugin/index.scss';


// start the Stimulus application
import './bootstrap';
