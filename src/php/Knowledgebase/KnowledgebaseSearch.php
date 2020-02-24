<?php
declare(strict_types=1);

namespace Hipper\Knowledgebase;

use Hipper\Organization\OrganizationModel;
use Hipper\Project\ProjectModel;
use Hipper\Search\SearchResultsPaginatorFactory;
use Hipper\Team\TeamModel;
use Hipper\Topic\TopicAncestory;

class KnowledgebaseSearch
{
    private KnowledgebaseRepository $knowledgebaseRepository;
    private KnowledgebaseSearchRepository $knowledgebaseSearchRepository;
    private KnowledgebaseSearchResultsFormatter $knowledgebaseSearchResultsFormatter;
    private SearchResultsPaginatorFactory $searchResultsPaginatorFactory;
    private TopicAncestory $topicAncestory;

    public function __construct(
        KnowledgebaseRepository $knowledgebaseRepository,
        KnowledgebaseSearchRepository $knowledgebaseSearchRepository,
        KnowledgebaseSearchResultsFormatter $knowledgebaseSearchResultsFormatter,
        SearchResultsPaginatorFactory $searchResultsPaginatorFactory,
        TopicAncestory $topicAncestory
    ) {
        $this->knowledgebaseRepository = $knowledgebaseRepository;
        $this->knowledgebaseSearchRepository = $knowledgebaseSearchRepository;
        $this->knowledgebaseSearchResultsFormatter = $knowledgebaseSearchResultsFormatter;
        $this->searchResultsPaginatorFactory = $searchResultsPaginatorFactory;
        $this->topicAncestory = $topicAncestory;
    }

    public function search(
        string $searchQuery,
        string $displayTimeZone,
        OrganizationModel $organization,
        int $numberOfPages = 1,
        int $startFromPage = 1
    ): array {
        $searchResultsPaginator = $this->searchResultsPaginatorFactory->create($numberOfPages, $startFromPage);
        $limit = $searchResultsPaginator->getLimit();
        $offset = $searchResultsPaginator->getOffset();

        $results = $this->knowledgebaseSearchRepository->getResults(
            $searchQuery,
            $organization->getId(),
            $limit,
            $offset
        );
        $moreResults = $searchResultsPaginator->hasMoreResults($results);
        $filteredResults = $searchResultsPaginator->filterResults($results);

        $parentTopicIds = $this->getParentTopicIdsFromResults($filteredResults);
        $ancestory = $this->topicAncestory->getAncestorNamesForTopicIds(
            $parentTopicIds,
            $organization
        );

        $knowledgebaseIds = $this->getKnowledgebaseIdsFromResults($filteredResults);
        $knowledgebasesResult = $this->knowledgebaseRepository->getKnowledgebaseOwnersForIds(
            $knowledgebaseIds,
            $organization->getId()
        );

        $knowledgebaseOwners = [];
        foreach ($knowledgebasesResult as $row) {
            if ($row['entity'] === 'team') {
                $knowledgebaseOwners[$row['knowledgebase_id']] = TeamModel::createFromArray($row);
            }

            if ($row['entity'] === 'project') {
                $knowledgebaseOwners[$row['knowledgebase_id']] = ProjectModel::createFromArray($row);
            }
        }

        $formatted = $this->knowledgebaseSearchResultsFormatter->format(
            $organization,
            $knowledgebaseOwners,
            $ancestory,
            $displayTimeZone,
            $filteredResults
        );

        return [$formatted, $moreResults];
    }

    public function searchWithinKnowledgebase(
        string $searchQuery,
        string $displayTimeZone,
        OrganizationModel $organization,
        KnowledgebaseOwnerModelInterface $knowledgebaseOwner,
        int $numberOfPages = 1,
        int $startFromPage = 1
    ): array {
        $searchResultsPaginator = $this->searchResultsPaginatorFactory->create($numberOfPages, $startFromPage);
        $limit = $searchResultsPaginator->getLimit();
        $offset = $searchResultsPaginator->getOffset();

        $results = $this->knowledgebaseSearchRepository->getResultsInKnowledgebase(
            $searchQuery,
            $organization->getId(),
            $knowledgebaseOwner->getKnowledgebaseId(),
            $limit,
            $offset
        );
        $moreResults = $searchResultsPaginator->hasMoreResults($results);
        $filteredResults = $searchResultsPaginator->filterResults($results);

        $parentTopicIds = $this->getParentTopicIdsFromResults($filteredResults);
        $ancestory = $this->topicAncestory->getAncestorNamesForTopicIds(
            $parentTopicIds,
            $organization
        );

        $knowledgebaseOwners = [];
        $knowledgebaseOwners[$knowledgebaseOwner->getKnowledgebaseId()] = $knowledgebaseOwner;

        $formatted = $this->knowledgebaseSearchResultsFormatter->format(
            $organization,
            $knowledgebaseOwners,
            $ancestory,
            $displayTimeZone,
            $filteredResults
        );
        return [$formatted, $moreResults];
    }

    private function getKnowledgebaseIdsFromResults(array $results): array
    {
        $knowledgebaseIds = array_map(
            function ($result) {
                return $result['knowledgebase_id'];
            },
            $results
        );
        return array_unique($knowledgebaseIds);
    }

    private function getParentTopicIdsFromResults(array $results): array
    {
        $topicIds = [];
        foreach ($results as $result) {
            if (null !== $result['parent_topic_id'] && !in_array($result['parent_topic_id'], $topicIds)) {
                $topicIds[] = $result['parent_topic_id'];
            }
        }
        return $topicIds;
    }
}
