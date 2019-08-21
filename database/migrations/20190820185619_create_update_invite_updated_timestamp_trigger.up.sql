DO $$
BEGIN
    IF NOT EXISTS (SELECT 1 FROM pg_trigger WHERE tgname = 'update_invite_updated_timestamp') THEN
        CREATE TRIGGER update_invite_updated_timestamp
        BEFORE UPDATE
        ON invite
        FOR EACH ROW EXECUTE PROCEDURE update_updated_timestamp();
    END IF;
END
$$;
