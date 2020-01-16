DROP INDEX IF EXISTS section_search_index;
DROP TRIGGER IF EXISTS update_section_search_tokens ON section;
DROP FUNCTION IF EXISTS update_section_search_tokens();
ALTER TABLE section DROP COLUMN IF EXISTS search_tokens;
