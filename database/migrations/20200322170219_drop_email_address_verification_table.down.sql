CREATE TABLE IF NOT EXISTS email_address_verification (
    id                      UUID NOT NULL PRIMARY KEY,
    person_id               UUID NOT NULL references person(id),
    verification_phrase     text CHECK (LENGTH(verification_phrase) <= 50) NOT NULL,
    expires                 timestamp without time zone NOT NULL,
    created                 timestamp without time zone NOT NULL DEFAULT CURRENT_TIMESTAMP
);
