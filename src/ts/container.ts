const navigationTemplate = require('Twig/navigation.twig');

import * as Bottle from 'bottlejs';
import CKeditor from './TextEditor/CKEditor/CKEditor';
import TextEditor from './TextEditor/TextEditor';
import Template from './Library/Template/Template';
import ApplicationBootstrap from './Bootstrap/ApplicationBootstrap';
import SignupBootstrap from './Bootstrap/SignupBootstrap';
import SignupController from './Signup/SignupController';
import SignupView from './Signup/SignupView';

const bottle = new Bottle();

bottle.factory('CKEditor', () => {
    return new CKeditor();
});

bottle.factory('textEditor', (container) => {
    return new TextEditor(container.CKEditor);
});

bottle.factory('template_navigation', () => {
    return new Template(navigationTemplate);
});

bottle.factory('applicationBootstrap', (container) => {
    return new ApplicationBootstrap(container.textEditor);
});

bottle.factory('signupView', () => {
    return new SignupView();
});

bottle.factory('signupController', (container) => {
    return new SignupController(container.signupView);
});

bottle.factory('signupBootstrap', (container) => {
    return new SignupBootstrap(container.signupController);
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
