CREATE OR REPLACE FUNCTION update_updated_timestamp()
RETURNS TRIGGER AS $$
BEGIN
    NEW.updated = now();
    RETURN NEW;
END;
$$ language 'plpgsql';

CREATE TABLE organization (
    id          UUID NOT NULL PRIMARY KEY,
    name        text CHECK (LENGTH(name) <= 50) NOT NULL,
    created     timestamp without time zone NOT NULL DEFAULT CURRENT_TIMESTAMP,
    updated     timestamp without time zone NOT NULL DEFAULT CURRENT_TIMESTAMP
);

CREATE TRIGGER update_organization_updated_timestamp
BEFORE UPDATE
ON organization
FOR EACH ROW EXECUTE PROCEDURE update_updated_timestamp();

CREATE TYPE organization_roles AS ENUM ('owner', 'admin', 'member');

CREATE TABLE person (
    id                      UUID NOT NULL PRIMARY KEY,
    name                    text CHECK (LENGTH(name) <= 100) NOT NULL,
    email_address           text CHECK (LENGTH(email_address) <= 255) NOT NULL,
    password                text CHECK (LENGTH(password) <= 150) NOT NULL,
    role                    organization_roles DEFAULT 'member',
    email_address_verified  boolean DEFAULT false,
    organization_id         UUID NOT NULL references organization(id),
    created                 timestamp without time zone NOT NULL DEFAULT CURRENT_TIMESTAMP,
    updated                 timestamp without time zone NOT NULL DEFAULT CURRENT_TIMESTAMP
);

CREATE TRIGGER update_person_updated_timestamp
BEFORE UPDATE
ON person
FOR EACH ROW EXECUTE PROCEDURE update_updated_timestamp();

CREATE TABLE email_address_verification (
    id                      UUID NOT NULL PRIMARY KEY,
    person_id               UUID NOT NULL references person(id),
    verification_phrase     text CHECK (LENGTH(verification_phrase) <= 50) NOT NULL,
    expires                 timestamp without time zone NOT NULL,
    created                 timestamp without time zone NOT NULL DEFAULT CURRENT_TIMESTAMP
);

CREATE TABLE knowledgebase (
    id                  UUID NOT NULL PRIMARY KEY,
    organization_id     UUID NOT NULL references organization(id),
    created             timestamp without time zone NOT NULL DEFAULT CURRENT_TIMESTAMP,
    updated             timestamp without time zone NOT NULL DEFAULT CURRENT_TIMESTAMP
);

CREATE TRIGGER update_knowledgebase_updated_timestamp
BEFORE UPDATE
ON knowledgebase
FOR EACH ROW EXECUTE PROCEDURE update_updated_timestamp();

CREATE TABLE chapter (
    id                  UUID NOT NULL PRIMARY KEY,
    name                text CHECK (LENGTH(name) <= 100) NOT NULL,
    description         text CHECK (LENGTH(description) <= 300) DEFAULT NULL,
    url_path            text CHECK (LENGTH(url_path) <= 100) NOT NULL,
    knowledgebase_id    UUID NOT NULL references knowledgebase(id),
    created             timestamp without time zone NOT NULL DEFAULT CURRENT_TIMESTAMP,
    updated             timestamp without time zone NOT NULL DEFAULT CURRENT_TIMESTAMP
);

CREATE TRIGGER update_chapter_updated_timestamp
BEFORE UPDATE
ON chapter
FOR EACH ROW EXECUTE PROCEDURE update_updated_timestamp();

CREATE TABLE document (
    id                  UUID NOT NULL PRIMARY KEY,
    name                text CHECK (LENGTH(name) <= 150) NOT NULL,
    description         text CHECK (LENGTH(description) <= 300) DEFAULT NULL,
    html                text DEFAULT NULL,
    url_path            text CHECK (LENGTH(url_path) <= 150) NOT NULL,
    chapter_id          UUID NOT NULL references chapter(id),
    knowledgebase_id    UUID NOT NULL references knowledgebase(id)
    created             timestamp without time zone NOT NULL DEFAULT CURRENT_TIMESTAMP,
    updated             timestamp without time zone NOT NULL DEFAULT CURRENT_TIMESTAMP
);

CREATE TRIGGER update_document_updated_timestamp
BEFORE UPDATE
ON document
FOR EACH ROW EXECUTE PROCEDURE update_updated_timestamp();

CREATE TABLE team (
    id                  UUID NOT NULL PRIMARY KEY,
    name                text CHECK (LENGTH(name) <= 100) NOT NULL,
    description         text CHECK (LENGTH(description) <= 300) DEFAULT NULL,
    url_path            text CHECK (LENGTH(url_path) <= 100) NOT NULL,
    lead_id             UUID DEFAULT NULL references person(id),
    knowledgebase_id    UUID DEFAULT NULL references knowledgebase(id),
    organization_id     UUID NOT NULL references organization(id),
    created             timestamp without time zone NOT NULL DEFAULT CURRENT_TIMESTAMP,
    updated             timestamp without time zone NOT NULL DEFAULT CURRENT_TIMESTAMP
);

CREATE TRIGGER update_team_updated_timestamp
BEFORE UPDATE
ON team
FOR EACH ROW EXECUTE PROCEDURE update_updated_timestamp();
