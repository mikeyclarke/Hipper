CREATE TABLE IF NOT EXISTS section (
    id                  UUID NOT NULL PRIMARY KEY,
    name                text CHECK (LENGTH(name) <= 100) NOT NULL,
    description         text CHECK (LENGTH(description) <= 300) DEFAULT NULL,
    url_slug            text CHECK (LENGTH(url_slug) <= 100) NOT NULL,
    url_id              text CHECK (LENGTH(url_id) <= 8) NOT NULL UNIQUE,
    parent_section_id   UUID DEFAULT NULL references section(id),
    knowledgebase_id    UUID NOT NULL references knowledgebase(id),
    organization_id     UUID NOT NULL references organization(id),
    created             timestamp without time zone NOT NULL DEFAULT CURRENT_TIMESTAMP,
    updated             timestamp without time zone NOT NULL DEFAULT CURRENT_TIMESTAMP
);
