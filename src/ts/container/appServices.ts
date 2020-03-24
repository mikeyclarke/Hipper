import * as Bottle from 'bottlejs';
import JoinOrganizationController from 'RouteControllers/app/Organization/JoinOrganizationController';
import LoginController from 'RouteControllers/app/Organization/LoginController';
import VerifyEmailAddressController from 'RouteControllers/app/Organization/VerifyEmailAddressController';
import CreateTeamController from 'RouteControllers/app/Team/CreateTeamController';
import CreateDocumentController from 'RouteControllers/app/Document/CreateDocumentController';
import CreateProjectController from 'RouteControllers/app/Project/CreateProjectController';
import CreateTopicController from 'RouteControllers/app/Topic/CreateTopicController';
import DocumentOrTopicControllerRouter from 'RouteControllers/app/Knowledgebase/DocumentOrTopicControllerRouter';
import EditDocumentController from 'RouteControllers/app/Document/EditDocumentController';
import SearchController from 'RouteControllers/app/SearchController';
import SearchResultsPaginator from 'Search/SearchResultsPaginator';
import TopicController from 'RouteControllers/app/Topic/TopicController';

export default function (bottle: Bottle): void {
    bottle.factory('searchResultsPaginator', (container) => {
        return new SearchResultsPaginator(
            container.httpClient,
        );
    });

    bottle.factory('loginController', (container) => {
        return new LoginController(
            container.httpClient,
        );
    });

    bottle.factory('createTeamController', (container) => {
        return new CreateTeamController(
            container.httpClient,
        );
    });

    bottle.factory('createDocumentController', (container) => {
        return new CreateDocumentController(
            container.httpClient,
            container.config.user_agent_profile,
        );
    });

    bottle.factory('createProjectController', (container) => {
        return new CreateProjectController(
            container.httpClient,
        );
    });

    bottle.factory('createTopicController', (container) => {
        return new CreateTopicController(
            container.httpClient,
        );
    });

    bottle.factory('editDocumentController', (container) => {
        return new EditDocumentController(
            container.httpClient,
            container.config.user_agent_profile,
        );
    });

    bottle.factory('searchController', (container) => {
        return new SearchController(
            container.searchResultsPaginator,
        );
    });

    bottle.factory('topicController', (container) => {
        return new TopicController(
            container.httpClient
        );
    });

    bottle.factory('documentOrTopicControllerRouter', (container) => {
        return new DocumentOrTopicControllerRouter(
            container.topicController
        );
    });

    bottle.factory('joinOrganizationController', (container) => {
        return new JoinOrganizationController(
            container.httpClient
        );
    });

    bottle.factory('verifyEmailAddressController', (container) => {
        return new VerifyEmailAddressController(
            container.httpClient
        );
    });
}
