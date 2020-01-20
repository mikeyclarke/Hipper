DROP INDEX IF EXISTS team_search_index;
DROP TRIGGER IF EXISTS update_team_search_tokens ON team;
DROP FUNCTION IF EXISTS update_team_search_tokens();
ALTER TABLE team DROP COLUMN IF EXISTS search_tokens;
