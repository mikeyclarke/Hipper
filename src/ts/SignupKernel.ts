import signupRoutes from 'routes/signupRoutes';
import signupServices from 'container/signupServices';
import sharedServices from 'container/sharedServices';
import Kernel from 'Kernel';
import DocumentHeadConfigurationProvider from 'DocumentHeadConfigurationProvider';
import loadComponents from 'components/componentLoader';

const defaultConfig = {
    service_worker_url: './service-worker.js',
    service_worker_scope: '/sign-up',
};
const htmlHeadConfigVars = [
    { name: 'csrf_token', selector: '.js-csrf', parseAsJson: false },
    { name: 'asset_base_url', selector: '.js-asset-base-url', parseAsJson: false },
];

export default class SignupKernel extends Kernel {
    protected onBeforeRouting(): void {
        loadComponents();
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

        return [defaultConfig, config];
    }

    protected getRoutes(): Function {
        return signupRoutes;
    }
}
