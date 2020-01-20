ALTER TABLE project ADD COLUMN IF NOT EXISTS search_tokens tsvector;

CREATE OR REPLACE FUNCTION update_project_search_tokens()
RETURNS TRIGGER AS $$
BEGIN
    NEW.search_tokens =
        setweight(to_tsvector('english', coalesce(new.name, '')), 'A') || ' ' ||
        setweight(to_tsvector('english', coalesce(new.description, '')), 'C');
    RETURN NEW;
END;
$$ language 'plpgsql';

DO $$
BEGIN
    IF NOT EXISTS (SELECT 1 FROM pg_trigger WHERE tgname = 'update_project_search_tokens') THEN
        CREATE TRIGGER update_project_search_tokens
        BEFORE INSERT OR UPDATE OF name, description
        ON project
        FOR EACH ROW EXECUTE PROCEDURE update_project_search_tokens();
    END IF;
END
$$;

CREATE INDEX IF NOT EXISTS project_search_index ON project USING GIN (search_tokens);
