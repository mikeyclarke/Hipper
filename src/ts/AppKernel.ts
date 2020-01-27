import appRoutes from 'routes/appRoutes';
import appServices from 'container/appServices';
import sharedServices from 'container/sharedServices';
import Kernel from 'Kernel';
import DocumentHeadConfigurationProvider from 'DocumentHeadConfigurationProvider';
import loadComponents from 'components/componentLoader';

const htmlHeadConfigVars = [
    { name: 'csrf_token', selector: '.js-csrf', parseAsJson: false },
    { name: 'user_agent_profile', selector: '.js-user-agent-profile', parseAsJson: true },
];

export default class AppKernel extends Kernel {
    protected onBeforeRouting(): void {
        loadComponents();
        this.bottle.container.timeZoneCookie.createOrUpdate();
    }

    protected getServices(): Function[] {
        return [sharedServices, appServices];
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
        return appRoutes;
    }
}
