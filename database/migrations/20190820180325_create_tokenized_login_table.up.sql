CREATE TABLE IF NOT EXISTS tokenized_login (
    id                  UUID NOT NULL PRIMARY KEY,
    person_id           UUID NOT NULL references person(id),
    token               text CHECK (LENGTH(token) <= 32) NOT NULL,
    expires             timestamp without time zone NOT NULL,
    created             timestamp without time zone NOT NULL DEFAULT CURRENT_TIMESTAMP,
    updated             timestamp without time zone NOT NULL DEFAULT CURRENT_TIMESTAMP
);
