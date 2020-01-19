DROP INDEX IF EXISTS person_search_index;
DROP TRIGGER IF EXISTS update_person_search_tokens ON person;
DROP FUNCTION IF EXISTS update_person_search_tokens();
ALTER TABLE person DROP COLUMN IF EXISTS search_tokens;
