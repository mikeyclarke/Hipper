CREATE TABLE organization (
    id          UUID NOT NULL PRIMARY KEY,
    name        varchar(255) NOT NULL,
    created     timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
    updated     timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP
);

CREATE TYPE organization_roles AS ENUM ('owner', 'admin', 'member');

CREATE TABLE person (
    id                      UUID NOT NULL PRIMARY KEY,
    name                    varchar(255) NOT NULL,
    email_address           varchar(255) NOT NULL,
    password                varchar(150) NOT NULL,
    role                    organization_roles DEFAULT 'member',
    email_address_verified  boolean DEFAULT false,
    organization_id         UUID NOT NULL references organization(id),
    created                 timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
    updated                 timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP
);

CREATE OR REPLACE FUNCTION update_updated_timestamp()
RETURNS TRIGGER AS $$
BEGIN
    NEW.updated = now();
    RETURN NEW;
END;
$$ language 'plpgsql';

CREATE TRIGGER update_organization_updated_timestamp
BEFORE UPDATE
ON organization
FOR EACH ROW EXECUTE PROCEDURE update_updated_timestamp();

CREATE TRIGGER update_person_updated_timestamp
BEFORE UPDATE
ON person
FOR EACH ROW EXECUTE PROCEDURE update_updated_timestamp();
