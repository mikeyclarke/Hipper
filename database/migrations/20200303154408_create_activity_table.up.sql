CREATE TABLE IF NOT EXISTS activity (
    id                  UUID NOT NULL PRIMARY KEY,
    type                activity_type NOT NULL,
    storage             json DEFAULT NULL,
    document_id         UUID DEFAULT NULL references document(id),
    topic_id            UUID DEFAULT NULL references topic(id),
    team_id             UUID DEFAULT NULL references team(id),
    project_id          UUID DEFAULT NULL references project(id),
    organization_id     UUID NOT NULL references organization(id),
    actor_id            UUID NOT NULL references person(id),
    created             timestamp without time zone NOT NULL DEFAULT CURRENT_TIMESTAMP
);
