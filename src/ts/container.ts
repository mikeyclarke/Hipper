import * as Bottle from 'bottlejs';
import { CKeditor } from './TextEditor/CKEditor/CKEditor';
import { TextEditor } from './TextEditor/TextEditor';
import { SignupForm } from './onboarding/SignupForm/SignupForm';
import { IndexController } from './RouteControllers/IndexController';
import { SignupController } from './RouteControllers/SignupController';
import { IController } from 'RouteControllers/IController';
import { ElementCache } from 'hleo/ElementCache/ElementCache';
import { EventDelegator } from 'hleo/EventDelegator/EventDelegator';

const bottle = new Bottle();

interface Iroute {
    [key: string]: IController;
}

bottle.factory('indexController', (container) => {
    return new IndexController(container.textEditor);
});

bottle.factory('signupController', (container) => {
    return new SignupController(container.signupForm);
});

bottle.factory('CKEditor', () => {
    return new CKeditor();
});

bottle.factory('textEditor', (container) => {
    return new TextEditor(container.CKEditor);
});

bottle.factory('signupForm', () => {
    const signupFormElementCache = new ElementCache('.js-signup-form', SignupForm.elements);
    const signupFormEventDelegator = new EventDelegator(signupFormElementCache.get('form'));
    return new SignupForm(signupFormEventDelegator, signupFormElementCache);
});

bottle.factory('bootstrap', (container) => {
    const path: string = window.location.pathname;
    const routes: Iroute = {
        '/': container.indexController,
        '/sign-up': container.signupController,
    };

    if (routes[path]) {
        return routes[path];
    } else {
        throw new Error('no path found for bootstrapping');
    }
});

export { bottle };
