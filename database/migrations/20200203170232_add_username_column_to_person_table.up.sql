ALTER TABLE person ADD COLUMN IF NOT EXISTS username text CHECK (LENGTH(username) <= 110) NOT NULL;
