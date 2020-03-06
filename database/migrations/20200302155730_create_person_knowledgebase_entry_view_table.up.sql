CREATE TABLE IF NOT EXISTS person_knowledgebase_entry_view (
    id                  UUID NOT NULL PRIMARY KEY,
    person_id           UUID NOT NULL references person(id),
    document_id         UUID DEFAULT NULL references document(id),
    topic_id            UUID DEFAULT NULL references topic(id),
    knowledgebase_id    UUID NOT NULL references knowledgebase(id),
    organization_id     UUID NOT NULL references organization(id),
    created             timestamp without time zone NOT NULL DEFAULT CURRENT_TIMESTAMP
);
