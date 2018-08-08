const navigationTemplate = require('Twig/navigation.twig');

import * as Bottle from 'bottlejs';
import CKeditor from './CKEditor/CKEditor';
import TextEditor from './Library/TextEditor/TextEditor';
import Template from './Library/Template/Template';
import { Navigation } from './UIControls/Navigation/Navigation';
import { Render } from './UIControls/Behaviours/Render';
import { Events } from './UIControls/Behaviours/Events';

const bottle = new Bottle();

bottle.factory('CKEditor', () => {
    return new CKeditor();
});

bottle.factory('navigationControl', (container) => {
    Navigation.template = container.template_navigation;
    Navigation.Render = Render;
    Navigation.Events = Events;
    return Navigation;
});

bottle.factory('textEditor', (container) => {
    return new TextEditor(container.CKEditor);
});

bottle.factory('template_navigation', () => {
    return new Template(navigationTemplate);
});

export default bottle.container;
