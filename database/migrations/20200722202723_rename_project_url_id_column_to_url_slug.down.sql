DO $$
BEGIN
    IF EXISTS (SELECT 1 FROM information_schema.columns WHERE table_name = 'project' and column_name='url_slug') THEN
        ALTER TABLE project RENAME COLUMN url_slug TO url_id;
    END IF;
END
$$;
