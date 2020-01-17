ALTER TABLE person ADD COLUMN IF NOT EXISTS job_role_or_title text CHECK (LENGTH(job_role_or_title) <= 100) DEFAULT NULL;
