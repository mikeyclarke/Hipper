DO $$
BEGIN
    IF NOT EXISTS (SELECT 1 FROM pg_type WHERE typname = 'file_usage') THEN
        CREATE TYPE file_usage AS ENUM ('profile_image', 'profile_image_thumbnail');
    END IF;
END
$$;
