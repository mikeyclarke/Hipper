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

CREATE TABLE knowledgebase (
    id                  UUID NOT NULL PRIMARY KEY,
    organization_id     UUID NOT NULL references organization(id),
    created             timestamp without time zone NOT NULL DEFAULT CURRENT_TIMESTAMP
);

CREATE TABLE chapter (
    id                  UUID NOT NULL PRIMARY KEY,
    name                text CHECK (LENGTH(name) <= 100) NOT NULL,
    description         text CHECK (LENGTH(description) <= 300) DEFAULT NULL,
    url_id              text CHECK (LENGTH(url_id) <= 100) NOT NULL,
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
    url_id              text CHECK (LENGTH(url_id) <= 150) NOT NULL,
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

CREATE TABLE team_metadata (
    id                  UUID NOT NULL PRIMARY KEY,
    team_id             UUID DEFAULT NULL references team(id),
    email_address       text CHECK (LENGTH(email_address) <= 255) DEFAULT NULL,
    slack_channel_name  text CHECK (LENGTH(slack_channel_name) <= 21) DEFAULT NULL,
    created             timestamp without time zone NOT NULL DEFAULT CURRENT_TIMESTAMP,
    updated             timestamp without time zone NOT NULL DEFAULT CURRENT_TIMESTAMP
);

CREATE TRIGGER update_team_metadata_updated_timestamp
BEFORE UPDATE
ON team_metadata
FOR EACH ROW EXECUTE PROCEDURE update_updated_timestamp();

CREATE TABLE person_to_team_map (
    id                  UUID NOT NULL PRIMARY KEY,
    person_id           UUID NOT NULL references person(id),
    team_id             UUID NOT NULL references team(id),
    created             timestamp without time zone NOT NULL DEFAULT CURRENT_TIMESTAMP
);

CREATE TABLE person_metadata (
    id                  UUID NOT NULL PRIMARY KEY,
    person_id           UUID DEFAULT NULL references person(id),
    twitter_screen_name text CHECK (LENGTH(twitter_screen_name) <= 15) DEFAULT NULL,
    github_username     text CHECK (LENGTH(github_username) <= 39) DEFAULT NULL,
    created             timestamp without time zone NOT NULL DEFAULT CURRENT_TIMESTAMP,
    updated             timestamp without time zone NOT NULL DEFAULT CURRENT_TIMESTAMP
);

CREATE TRIGGER update_person_metadata_updated_timestamp
BEFORE UPDATE
ON person_metadata
FOR EACH ROW EXECUTE PROCEDURE update_updated_timestamp();

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
