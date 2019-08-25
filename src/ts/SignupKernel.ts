import { signupRoutes } from 'routes/signupRoutes';
import { signupServices } from 'container/signupServices';
import { sharedServices } from 'container/sharedServices';
import { Kernel } from 'Kernel';
import { DocumentHeadConfigurationProvider } from 'DocumentHeadConfigurationProvider';

const htmlHeadConfigVars = [
    { name: 'csrf_token', selector: '.js-csrf', parseAsJson: false },
];

export class SignupKernel extends Kernel {
    constructor() {
        super();
    }

    protected getServices(): Function[] {
        return [sharedServices, signupServices];
    }

    protected getConfigs(): object[] {
        const provider = new DocumentHeadConfigurationProvider();
        const config: Record<string, any> = {};

        htmlHeadConfigVars.forEach((configVar) => {
            let result = provider.getValue(configVar.selector);
            if (configVar.parseAsJson && null !== result) {
                result = JSON.parse(result);
            }
            config[configVar.name] = result;
        });

        return [config];
    }

    protected getRoutes(): Function {
        return signupRoutes;
    }
}
