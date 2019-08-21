CREATE TABLE IF NOT EXISTS person (
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
