ALTER TABLE document ADD COLUMN IF NOT EXISTS content_plain text DEFAULT null;
ALTER TABLE document ADD COLUMN IF NOT EXISTS search_tokens tsvector;

CREATE OR REPLACE FUNCTION update_document_search_tokens()
RETURNS TRIGGER AS $$
BEGIN
    NEW.search_tokens =
        setweight(to_tsvector('english', coalesce(new.name, '')), 'A') || ' ' ||
        setweight(to_tsvector('english', coalesce(new.description, '')), 'C') || ' ' ||
        setweight(to_tsvector('english', coalesce(new.content_plain, '')), 'D');
    RETURN NEW;
END;
$$ language 'plpgsql';

DO $$
BEGIN
    IF NOT EXISTS (SELECT 1 FROM pg_trigger WHERE tgname = 'update_document_search_tokens') THEN
        CREATE TRIGGER update_document_search_tokens
        BEFORE INSERT OR UPDATE OF name, description, content_plain
        ON document
        FOR EACH ROW EXECUTE PROCEDURE update_document_search_tokens();
    END IF;
END
$$;

CREATE INDEX IF NOT EXISTS document_search_index ON document USING GIN (search_tokens);
