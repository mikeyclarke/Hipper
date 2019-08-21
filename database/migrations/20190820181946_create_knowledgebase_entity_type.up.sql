DO $$
BEGIN
    IF NOT EXISTS (SELECT 1 FROM pg_type WHERE typname = 'knowledgebase_entity') THEN
        CREATE TYPE knowledgebase_entity AS ENUM ('team', 'project');
    END IF;
END
$$;
