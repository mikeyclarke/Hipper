import * as Bottle from 'bottlejs';
import CKeditor from './TextEditor/CKEditor/CKEditor';
import TextEditor from './TextEditor/TextEditor';
import ApplicationBootstrap from './Bootstrap/ApplicationBootstrap';
import SignupBootstrap from './Bootstrap/SignupBootstrap';
import SignupForm from './onboarding/SignupForm/SignupForm';

const bottle = new Bottle();

bottle.factory('CKEditor', () => {
    return new CKeditor();
});

bottle.factory('textEditor', (container) => {
    return new TextEditor(container.CKEditor);
});

bottle.factory('applicationBootstrap', (container) => {
    return new ApplicationBootstrap(container.textEditor);
});

bottle.factory('signupForm', () => {
    return new SignupForm();
});

bottle.factory('signupBootstrap', (container) => {
    return new SignupBootstrap(container.signupForm);
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
