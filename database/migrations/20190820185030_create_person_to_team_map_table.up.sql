CREATE TABLE IF NOT EXISTS person_to_team_map (
    id                  UUID NOT NULL PRIMARY KEY,
    person_id           UUID NOT NULL references person(id),
    team_id             UUID NOT NULL references team(id),
    created             timestamp without time zone NOT NULL DEFAULT CURRENT_TIMESTAMP
);
