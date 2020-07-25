DO $$
BEGIN
    IF EXISTS (SELECT 1 FROM information_schema.columns WHERE table_name = 'team' and column_name='url_id') THEN
        ALTER TABLE team RENAME COLUMN url_id TO url_slug;
    END IF;
END
$$;
