ALTER TABLE person ADD COLUMN IF NOT EXISTS search_tokens tsvector;

CREATE OR REPLACE FUNCTION update_person_search_tokens()
RETURNS TRIGGER AS $$
BEGIN
    NEW.search_tokens =
        setweight(to_tsvector('simple', coalesce(new.email_address, '')), 'A') || ' ' ||
        setweight(to_tsvector('simple', coalesce(new.name, '')), 'B') || ' ' ||
        setweight(to_tsvector('simple', coalesce(new.job_role_or_title, '')), 'C');
    RETURN NEW;
END;
$$ language 'plpgsql';

DO $$
BEGIN
    IF NOT EXISTS (SELECT 1 FROM pg_trigger WHERE tgname = 'update_person_search_tokens') THEN
        CREATE TRIGGER update_person_search_tokens
        BEFORE INSERT OR UPDATE OF name, email_address, job_role_or_title
        ON person
        FOR EACH ROW EXECUTE PROCEDURE update_person_search_tokens();
    END IF;
END
$$;

CREATE INDEX IF NOT EXISTS person_search_index ON person USING GIN (search_tokens);
