DROP INDEX IF EXISTS project_search_index;
DROP TRIGGER IF EXISTS update_project_search_tokens ON project;
DROP FUNCTION IF EXISTS update_project_search_tokens();
ALTER TABLE project DROP COLUMN IF EXISTS search_tokens;
