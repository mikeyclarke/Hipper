import * as Bottle from 'bottlejs';
import JoinByInvitationController from 'App/Controller/Organization/Join/JoinByInvitationController';
import JoinOrganizationController from 'App/Controller/Organization/Join/JoinOrganizationController';
import LoginController from 'App/Controller/Organization/LoginController';
import VerifyEmailAddressController from 'App/Controller/Organization/Join/VerifyEmailAddressController';
import CreateTeamController from 'App/Controller/Team/CreateTeamController';
import CreateDocumentController from 'App/Controller/Document/CreateDocumentController';
import CreateProjectController from 'App/Controller/Project/CreateProjectController';
import CreateTopicController from 'App/Controller/Topic/CreateTopicController';
import DocumentOrTopicControllerRouter from 'App/Controller/Knowledgebase/DocumentOrTopicControllerRouter';
import EditDocumentController from 'App/Controller/Document/EditDocumentController';
import SearchController from 'App/Controller/SearchController';
import SearchResultsPaginator from 'Search/SearchResultsPaginator';
import TopicController from 'App/Controller/Topic/TopicController';

export default function (bottle: Bottle): void {
    bottle.factory('searchResultsPaginator', (container) => {
        return new SearchResultsPaginator(
            container.httpClient,
        );
    });

    bottle.factory('loginController', (container) => {
        return new LoginController(
            container.formSubmitHelper
        );
    });

    bottle.factory('createTeamController', (container) => {
        return new CreateTeamController(
            container.formSubmitHelper
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
            container.formSubmitHelper
        );
    });

    bottle.factory('createTopicController', (container) => {
        return new CreateTopicController(
            container.formSubmitHelper
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

    bottle.factory('joinByInvitationController', (container) => {
        return new JoinByInvitationController(
            container.formSubmitHelper
        );
    });

    bottle.factory('joinOrganizationController', (container) => {
        return new JoinOrganizationController(
            container.formSubmitHelper
        );
    });

    bottle.factory('verifyEmailAddressController', (container) => {
        return new VerifyEmailAddressController(
            container.formSubmitHelper
        );
    });
}
