DROP INDEX IF EXISTS topic_search_index;
DROP TRIGGER IF EXISTS update_topic_search_tokens ON topic;
DROP FUNCTION IF EXISTS update_topic_search_tokens();
ALTER TABLE topic DROP COLUMN IF EXISTS search_tokens;
