CREATE TABLE IF NOT EXISTS person_to_project_map (
    id                  UUID NOT NULL PRIMARY KEY,
    person_id           UUID NOT NULL references person(id),
    project_id          UUID NOT NULL references project(id),
    created             timestamp without time zone NOT NULL DEFAULT CURRENT_TIMESTAMP
);
