ALTER TABLE project ADD COLUMN IF NOT EXISTS lead_id UUID DEFAULT NULL references person(id);
