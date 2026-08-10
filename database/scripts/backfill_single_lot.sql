-- One-off backfill: inventory checked in before the chaos-storage picking
-- feature existed wasn't grouped into daily lots (each Rapid Intake session
-- got its own random-suffixed lot, even across the same day). This collapses
-- everything still physically in the warehouse into a single lot so it can be
-- treated as one box going forward, matching the app's own default format
-- (see App\Filament\Resources\CardInventories\Pages\RapidIntake::mount()).
--
-- Scope is deliberately narrow: status IN ('in_stock', 'allocated') AND
-- picked_at IS NULL. picked_at is a brand-new column, so EVERY existing row
-- currently has picked_at IS NULL — including cards sold or dispatched long
-- ago. Without the status filter, those would wrongly get relabelled into
-- today's lot even though they're no longer in any box.

-- 1. Preview affected rows before running the update.
SELECT status, count(*) AS n
FROM card_inventory
WHERE status IN ('in_stock', 'allocated')
  AND picked_at IS NULL
GROUP BY status;

-- 2. The actual backfill — wrapped in a transaction so you can inspect
--    the row count Postgres reports and roll back if it looks wrong.
BEGIN;

UPDATE card_inventory
SET acquisition_lot = 'LOT-' || to_char(current_date, 'YYYY-MM-DD')
WHERE status IN ('in_stock', 'allocated')
  AND picked_at IS NULL;

-- Check the result, then either:
COMMIT;
-- or, if it looks wrong:
-- ROLLBACK;
