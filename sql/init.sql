CREATE OR REPLACE FUNCTION update_updated_timestamp()
RETURNS TRIGGER AS $$
BEGIN
    NEW.updated = now();
    RETURN NEW;
END;
$$ language 'plpgsql';

CREATE TABLE organization (
    id                      UUID NOT NULL PRIMARY KEY,
    name                    text CHECK (LENGTH(name) <= 50) NOT NULL,
    subdomain               text CHECK (LENGTH(subdomain) <= 63) DEFAULT NULL,
    approved_email_domain_signup_allowed boolean DEFAULT false,
    approved_email_domains  jsonb DEFAULT NULL,
    created                 timestamp without time zone NOT NULL DEFAULT CURRENT_TIMESTAMP,
    updated                 timestamp without time zone NOT NULL DEFAULT CURRENT_TIMESTAMP
);

CREATE TRIGGER update_organization_updated_timestamp
BEFORE UPDATE
ON organization
FOR EACH ROW EXECUTE PROCEDURE update_updated_timestamp();

CREATE TABLE person (
    id                      UUID NOT NULL PRIMARY KEY,
    name                    text CHECK (LENGTH(name) <= 100) NOT NULL,
    abbreviated_name        text CHECK (LENGTH(abbreviated_name) <= 50),
    email_address           text CHECK (LENGTH(email_address) <= 255) NOT NULL,
    password                text NOT NULL,
    email_address_verified  boolean DEFAULT false,
    onboarding_completed    boolean DEFAULT false,
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

CREATE TABLE tokenized_login (
    id                  UUID NOT NULL PRIMARY KEY,
    person_id           UUID NOT NULL references person(id),
    token               text CHECK (LENGTH(token) <= 32) NOT NULL,
    expires             timestamp without time zone NOT NULL,
    created             timestamp without time zone NOT NULL DEFAULT CURRENT_TIMESTAMP,
    updated             timestamp without time zone NOT NULL DEFAULT CURRENT_TIMESTAMP
);

CREATE TRIGGER update_tokenized_login_updated_timestamp
BEFORE UPDATE
ON tokenized_login
FOR EACH ROW EXECUTE PROCEDURE update_updated_timestamp();

CREATE TYPE knowledgebase_entity AS ENUM ('team', 'project');

CREATE TABLE knowledgebase (
    id                  UUID NOT NULL PRIMARY KEY,
    entity              knowledgebase_entity,
    organization_id     UUID NOT NULL references organization(id),
    created             timestamp without time zone NOT NULL DEFAULT CURRENT_TIMESTAMP
);

CREATE TABLE section (
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

CREATE TRIGGER update_section_updated_timestamp
BEFORE UPDATE
ON section
FOR EACH ROW EXECUTE PROCEDURE update_updated_timestamp();

CREATE TABLE document (
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

CREATE TRIGGER update_document_updated_timestamp
BEFORE UPDATE
ON document
FOR EACH ROW EXECUTE PROCEDURE update_updated_timestamp();

CREATE TABLE document_revision (
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

CREATE TRIGGER update_document_revision_updated_timestamp
BEFORE UPDATE
ON document_revision
FOR EACH ROW EXECUTE PROCEDURE update_updated_timestamp();

CREATE TYPE knowledgebase_route_entity AS ENUM ('document', 'section');

CREATE TABLE knowledgebase_route (
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

CREATE TRIGGER update_knowledgebase_route_updated_timestamp
BEFORE UPDATE
ON knowledgebase_route
FOR EACH ROW EXECUTE PROCEDURE update_updated_timestamp();

CREATE TABLE team (
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

CREATE TRIGGER update_team_updated_timestamp
BEFORE UPDATE
ON team
FOR EACH ROW EXECUTE PROCEDURE update_updated_timestamp();

CREATE TABLE person_to_team_map (
    id                  UUID NOT NULL PRIMARY KEY,
    person_id           UUID NOT NULL references person(id),
    team_id             UUID NOT NULL references team(id),
    created             timestamp without time zone NOT NULL DEFAULT CURRENT_TIMESTAMP
);

CREATE TABLE project (
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

CREATE TRIGGER update_project_updated_timestamp
BEFORE UPDATE
ON project
FOR EACH ROW EXECUTE PROCEDURE update_updated_timestamp();

CREATE TABLE person_to_project_map (
    id                  UUID NOT NULL PRIMARY KEY,
    person_id           UUID NOT NULL references person(id),
    project_id          UUID NOT NULL references project(id),
    created             timestamp without time zone NOT NULL DEFAULT CURRENT_TIMESTAMP
);

CREATE TABLE invite (
    id                  UUID NOT NULL PRIMARY KEY,
    email_address       text CHECK (LENGTH(email_address) <= 255) NOT NULL,
    invited_by          UUID DEFAULT NULL references person(id),
    organization_id     UUID NOT NULL references organization(id),
    token               text CHECK (LENGTH(token) <= 32) DEFAULT NULL,
    sent                boolean DEFAULT false,
    expires             timestamp without time zone NOT NULL,
    created             timestamp without time zone NOT NULL DEFAULT CURRENT_TIMESTAMP,
    updated             timestamp without time zone NOT NULL DEFAULT CURRENT_TIMESTAMP
);

CREATE TRIGGER update_invite_updated_timestamp
BEFORE UPDATE
ON invite
FOR EACH ROW EXECUTE PROCEDURE update_updated_timestamp();
