DROP INDEX IF EXISTS document_search_index;
DROP TRIGGER IF EXISTS update_document_search_tokens ON document;
DROP FUNCTION IF EXISTS update_document_search_tokens();
ALTER TABLE document DROP COLUMN IF EXISTS search_tokens;
ALTER TABLE document DROP COLUMN IF EXISTS content_plain;
