DO $$
BEGIN
    IF NOT EXISTS (SELECT 1 FROM pg_type WHERE typname = 'knowledgebase_route_entity') THEN
        CREATE TYPE knowledgebase_route_entity AS ENUM ('document', 'section');
    END IF;
END
$$;
