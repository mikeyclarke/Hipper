import TopicController from 'RouteControllers/app/Topic/TopicController';

export default class DocumentOrTopicControllerRouter {
    private readonly topicController: TopicController;

    constructor(
        topicController: TopicController
    ) {
        this.topicController = topicController;
    }

    public route(routeParameters: Map<string, string>): void {
        if (null !== document.querySelector('.js-knowledgebase-topic-header')) {
            this.topicController.start(routeParameters);
        }
    }
}
