CREATE TABLE IF NOT EXISTS knowledgebase_route (
    id                  UUID NOT NULL PRIMARY KEY,
    url_id              text CHECK (LENGTH(url_id) <= 8) NOT NULL,
    route               text CHECK (LENGTH(route) <= 500) NOT NULL,
    is_canonical        boolean DEFAULT true,
    entity              knowledgebase_route_entity,
    organization_id     UUID NOT NULL references organization(id),
    knowledgebase_id    UUID NOT NULL references knowledgebase(id),
    section_id          UUID DEFAULT NULL references section(id),
    document_id         UUID DEFAULT NULL references document(id),
    created             timestamp without time zone NOT NULL DEFAULT CURRENT_TIMESTAMP,
    updated             timestamp without time zone NOT NULL DEFAULT CURRENT_TIMESTAMP
);
