<?php
declare(strict_types=1);

namespace Hipper\FrontEnd\App\Middleware\Knowledgebase;

use Hipper\Knowledgebase\Exception\UnsupportedKnowledgebaseContentTypeException;
use Hipper\Person\PersonKnowledgebaseEntryViewCreator;
use Symfony\Component\HttpFoundation\Request;

class KnowledgebaseEntryViewMiddleware
{
    private PersonKnowledgebaseEntryViewCreator $entryViewCreator;

    public function __construct(
        PersonKnowledgebaseEntryViewCreator $entryViewCreator
    ) {
        $this->entryViewCreator = $entryViewCreator;
    }

    public function after(Request $request): void
    {
        $currentUser = $request->attributes->get('current_user');
        $entityType = $request->attributes->get('entity_type');

        switch ($entityType) {
            case 'document':
                $entry = $request->attributes->get('document');
                break;
            case 'topic':
                $entry = $request->attributes->get('topic');
                break;
            default:
                throw new UnsupportedKnowledgebaseContentTypeException;
        }

        if (null === $entry) {
            return;
        }

        $this->entryViewCreator->create($currentUser, $entry);
    }
}
