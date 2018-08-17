const navigationTemplate = require('Twig/navigation.twig');

import * as Bottle from 'bottlejs';
import CKeditor from './Library/TextEditor/CKEditor/CKEditor';
import TextEditor from './Library/TextEditor/TextEditor';
import Template from './Library/Template/Template';
import { Navigation } from './UIControls/Navigation/Navigation';
import ApplicationBootstrap from './Bootstrap/ApplicationBootstrap';
import SignupBootstrap from './Bootstrap/SignupBootstrap';

const bottle = new Bottle();

bottle.factory('CKEditor', () => {
    return new CKeditor();
});

bottle.factory('navigationControl', (container) => {
    Navigation.template = container.template_navigation;
    return Navigation;
});

bottle.factory('textEditor', (container) => {
    return new TextEditor(container.CKEditor);
});

bottle.factory('template_navigation', () => {
    return new Template(navigationTemplate);
});

bottle.factory('applicationBootstrap', (container) => {
    const navigation = new container.navigationControl('.js-navigation-container');
    return new ApplicationBootstrap(navigation, container.textEditor);
});

bottle.factory('signupBootstrap', () => {
    return new SignupBootstrap();
});

bottle.factory('bootstrap', (container) => {
    const path: string = window.location.pathname;
    const routes: object = {
        '/': container.applicationBootstrap,
        '/sign-up': container.signupBootstrap,
    }

    if (routes[path]) {
        return routes[path];
    } else {
        throw new Error('no path found for bootstrapping');
    }
})

export default bottle.container;
