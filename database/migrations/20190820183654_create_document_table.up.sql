CREATE TABLE IF NOT EXISTS document (
    id                  UUID NOT NULL PRIMARY KEY,
    name                text CHECK (LENGTH(name) <= 150) NOT NULL,
    description         text CHECK (LENGTH(description) <= 300) DEFAULT NULL,
    deduced_description text CHECK (LENGTH(deduced_description) <= 300) DEFAULT NULL,
    content             json DEFAULT NULL,
    url_slug            text CHECK (LENGTH(url_slug) <= 100) NOT NULL,
    url_id              text CHECK (LENGTH(url_id) <= 8) NOT NULL UNIQUE,
    knowledgebase_id    UUID NOT NULL references knowledgebase(id),
    organization_id     UUID NOT NULL references organization(id),
    section_id          UUID DEFAULT NULL references section(id),
    created_by          UUID NOT NULL references person(id),
    last_updated_by     UUID NOT NULL references person(id),
    created             timestamp without time zone NOT NULL DEFAULT CURRENT_TIMESTAMP,
    updated             timestamp without time zone NOT NULL DEFAULT CURRENT_TIMESTAMP
);
