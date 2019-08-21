CREATE TABLE IF NOT EXISTS knowledgebase (
    id                  UUID NOT NULL PRIMARY KEY,
    entity              knowledgebase_entity,
    organization_id     UUID NOT NULL references organization(id),
    created             timestamp without time zone NOT NULL DEFAULT CURRENT_TIMESTAMP
);
