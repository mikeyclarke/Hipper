import * as Bottle from 'bottlejs';
import { CKeditor } from './TextEditor/CKEditor/CKEditor';
import { TextEditor } from './TextEditor/TextEditor';
import { SignupForm } from './onboarding/SignupForm/SignupForm';
import { IndexController } from './RouteControllers/IndexController';
import { SignupController } from './RouteControllers/SignupController';

const bottle = new Bottle();

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
    return new SignupForm();
});

bottle.factory('bootstrap', (container) => {
    const path: string = window.location.pathname;
    const routes: object = {
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
