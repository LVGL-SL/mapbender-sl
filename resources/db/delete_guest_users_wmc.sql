-- SQL Script to delete database stored WMCs of non logged in users on demand.
DELETE FROM mapbender.mb_user_wmc WHERE fkey_user_id=2;
