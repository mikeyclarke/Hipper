CREATE TABLE IF NOT EXISTS invite (
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
