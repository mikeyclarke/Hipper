CREATE TABLE organization (
    id          varchar(15) NOT NULL primary key,
    name        varchar(255) NOT NULL,
    created     timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
    updated     timestamp
);

CREATE TYPE organization_roles AS ENUM ('owner', 'admin', 'member');

CREATE TABLE person (
    id                  varchar(15) NOT NULL primary key,
    name                varchar(255) NOT NULL,
    role                organization_roles DEFAULT 'member',
    emailVerified       boolean DEFAULT false,
    organizationId      varchar(15) NOT NULL references organization(id),
    created             timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
    updated             timestamp
);
