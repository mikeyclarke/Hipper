import SectionController from 'RouteControllers/app/Section/SectionController';

export default class DocumentOrSectionControllerRouter {
    private readonly sectionController: SectionController;

    constructor(
        sectionController: SectionController
    ) {
        this.sectionController = sectionController;
    }

    public route(routeParameters: Map<string, string>): void {
        if (null !== document.querySelector('.js-knowledgebase-section-header')) {
            this.sectionController.start(routeParameters);
        }
    }
}
