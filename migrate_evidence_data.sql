-- Migration script to transform evidence data from old structure to new structure
-- This script migrates data from evidence_old.sql structure to evidence_new.sql structure

-- First, ensure the new evidence table exists (run evidence_new.sql first)
-- Then run this migration script

-- Insert data from old structure to new structure
INSERT INTO evidence (
    complaint_id,
    file_name_1,
    file_name_2,
    file_name_3,
    file_type_1,
    file_type_2,
    file_type_3,
    file_path_1,
    file_path_2,
    file_path_3,
    uploaded_by_type,
    uploaded_by_id,
    uploaded_at
)
SELECT 
    complaint_id,
    image_1 as file_name_1,
    image_2 as file_name_2,
    image_3 as file_name_3,
    -- Extract file type from file name (everything after the last dot)
    CASE 
        WHEN image_1 IS NOT NULL THEN 
            UPPER(SUBSTRING(image_1, LOCATE('.', image_1) + 1))
        ELSE NULL 
    END as file_type_1,
    CASE 
        WHEN image_2 IS NOT NULL THEN 
            UPPER(SUBSTRING(image_2, LOCATE('.', image_2) + 1))
        ELSE NULL 
    END as file_type_2,
    CASE 
        WHEN image_3 IS NOT NULL THEN 
            UPPER(SUBSTRING(image_3, LOCATE('.', image_3) + 1))
        ELSE NULL 
    END as file_type_3,
    -- Construct file paths (assuming files are in uploads/evidence/ directory)
    CASE 
        WHEN image_1 IS NOT NULL THEN 
            CONCAT('uploads/evidence/', image_1)
        ELSE NULL 
    END as file_path_1,
    CASE 
        WHEN image_2 IS NOT NULL THEN 
            CONCAT('uploads/evidence/', image_2)
        ELSE NULL 
    END as file_path_2,
    CASE 
        WHEN image_3 IS NOT NULL THEN 
            CONCAT('uploads/evidence/', image_3)
        ELSE NULL 
    END as file_path_3,
    -- Set default values for new required columns
    'customer' as uploaded_by_type,  -- Assuming customers uploaded the original files
    'migration' as uploaded_by_id,   -- Placeholder for migration process
    uploaded_at
FROM evidence_old  -- Assuming you rename the old table to evidence_old
WHERE complaint_id IS NOT NULL;

-- Optional: Add comments to track migration
UPDATE evidence 
SET uploaded_by_id = CONCAT('migrated_', id)
WHERE uploaded_by_id = 'migration';

-- Display migration summary
SELECT 
    COUNT(*) as total_records_migrated,
    COUNT(file_name_1) as files_with_image_1,
    COUNT(file_name_2) as files_with_image_2,
    COUNT(file_name_3) as files_with_image_3
FROM evidence 
WHERE uploaded_by_id LIKE 'migrated_%';
