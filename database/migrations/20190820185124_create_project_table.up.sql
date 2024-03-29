CREATE TABLE IF NOT EXISTS project (
    id                  UUID NOT NULL PRIMARY KEY,
    name                text CHECK (LENGTH(name) <= 100) NOT NULL,
    description         text CHECK (LENGTH(description) <= 300) DEFAULT NULL,
    url_id              text CHECK (LENGTH(url_id) <= 100) NOT NULL,
    lead_id             UUID DEFAULT NULL references person(id),
    knowledgebase_id    UUID DEFAULT NULL references knowledgebase(id),
    organization_id     UUID NOT NULL references organization(id),
    created             timestamp without time zone NOT NULL DEFAULT CURRENT_TIMESTAMP,
    updated             timestamp without time zone NOT NULL DEFAULT CURRENT_TIMESTAMP
);
