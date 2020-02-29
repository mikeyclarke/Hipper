ALTER TABLE organization ADD COLUMN IF NOT EXISTS knowledgebase_id UUID DEFAULT NULL references knowledgebase(id);
