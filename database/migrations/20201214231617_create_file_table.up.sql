CREATE TABLE IF NOT EXISTS file (
    id                  UUID NOT NULL PRIMARY KEY,
    content_hash        text CHECK (LENGTH(content_hash) <= 32) DEFAULT NULL,
    storage_path        text CHECK (LENGTH(storage_path) <= 150) NOT NULL,
    file_type           file_type NOT NULL,
    mime_type           text CHECK (LENGTH(mime_type) <= 50) NOT NULL,
    usage               file_usage NOT NULL,
    bytes               integer NOT NULL,
    height              smallint DEFAULT NULL,
    width               smallint DEFAULT NULL,
    organization_id     UUID NOT NULL references organization(id),
    creator_id          UUID NOT NULL references person(id),
    created             timestamp without time zone NOT NULL DEFAULT CURRENT_TIMESTAMP,
    updated             timestamp without time zone NOT NULL DEFAULT CURRENT_TIMESTAMP
);
