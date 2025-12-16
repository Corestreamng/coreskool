-- Fix admin password to 'admin123'
-- This script updates the admin user password in case the database was already installed with the wrong hash

UPDATE users 
SET password = '$2y$10$oY1NSNGLF22bzdDhCbdxUuYEcTKV.ucL/8jPS/ICJXFIghvvRBaCO'
WHERE email = 'admin@coreskool.coinswipe.xyz' AND role = 'admin';
