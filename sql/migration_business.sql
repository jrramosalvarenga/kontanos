-- Add business profile type support
ALTER TABLE provider_profiles
    ADD COLUMN IF NOT EXISTS profile_type VARCHAR(20) NOT NULL DEFAULT 'personal',
    ADD COLUMN IF NOT EXISTS business_name VARCHAR(200);
