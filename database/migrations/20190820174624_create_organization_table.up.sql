CREATE TABLE IF NOT EXISTS organization (
    id                      UUID NOT NULL PRIMARY KEY,
    name                    text CHECK (LENGTH(name) <= 50) NOT NULL,
    subdomain               text CHECK (LENGTH(subdomain) <= 63) DEFAULT NULL,
    approved_email_domain_signup_allowed boolean DEFAULT false,
    approved_email_domains  jsonb DEFAULT NULL,
    created                 timestamp without time zone NOT NULL DEFAULT CURRENT_TIMESTAMP,
    updated                 timestamp without time zone NOT NULL DEFAULT CURRENT_TIMESTAMP
);
