CREATE TABLE IF NOT EXISTS document_revision (
    id                  UUID NOT NULL PRIMARY KEY,
    name                text CHECK (LENGTH(name) <= 150) NOT NULL,
    description         text CHECK (LENGTH(description) <= 300) DEFAULT NULL,
    deduced_description text CHECK (LENGTH(deduced_description) <= 300) DEFAULT NULL,
    content             json DEFAULT NULL,
    knowledgebase_id    UUID NOT NULL references knowledgebase(id),
    organization_id     UUID NOT NULL references organization(id),
    document_id         UUID NOT NULL references document(id),
    created_by          UUID NOT NULL references person(id),
    created             timestamp without time zone NOT NULL DEFAULT CURRENT_TIMESTAMP,
    updated             timestamp without time zone NOT NULL DEFAULT CURRENT_TIMESTAMP
);
