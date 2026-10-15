-- ============================================================
-- Pesez Farm — Soft-Delete Migration
-- Run this file once against your pesez_farm_db database.
-- Adds a nullable `deleted_at` TIMESTAMP column to the four
-- operational log tables. NULL = active, non-NULL = archived.
-- ============================================================

USE pesez_farm_db;

ALTER TABLE egg_production
    ADD COLUMN IF NOT EXISTS deleted_at TIMESTAMP NULL DEFAULT NULL;

ALTER TABLE health_records
    ADD COLUMN IF NOT EXISTS deleted_at TIMESTAMP NULL DEFAULT NULL;

ALTER TABLE mortality
    ADD COLUMN IF NOT EXISTS deleted_at TIMESTAMP NULL DEFAULT NULL;

ALTER TABLE feed_usage
    ADD COLUMN IF NOT EXISTS deleted_at TIMESTAMP NULL DEFAULT NULL;

-- Optional: index for fast filtering on active records
CREATE INDEX IF NOT EXISTS idx_ep_deleted   ON egg_production  (deleted_at);
CREATE INDEX IF NOT EXISTS idx_hr_deleted   ON health_records  (deleted_at);
CREATE INDEX IF NOT EXISTS idx_mort_deleted ON mortality       (deleted_at);
CREATE INDEX IF NOT EXISTS idx_fu_deleted   ON feed_usage      (deleted_at);
