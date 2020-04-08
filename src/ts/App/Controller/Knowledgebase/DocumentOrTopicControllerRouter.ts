import ViewDocumentController from 'App/Controller/Document/ViewDocumentController';
import TopicController from 'App/Controller/Topic/TopicController';

export default class DocumentOrTopicControllerRouter {
    private readonly documentController: ViewDocumentController;
    private readonly topicController: TopicController;

    constructor(
        documentController: ViewDocumentController,
        topicController: TopicController
    ) {
        this.documentController = documentController;
        this.topicController = topicController;
    }

    public route(routeParameters: Map<string, string>): void {
        if (null !== document.querySelector('.js-knowledgebase-topic-header')) {
            this.topicController.start(routeParameters);
            return;
        }

        this.documentController.start(routeParameters);
    }
}
