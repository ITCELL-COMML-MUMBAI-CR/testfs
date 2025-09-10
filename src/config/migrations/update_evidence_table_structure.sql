-- Migration script to update evidence table structure
-- This script modifies the evidence table to store up to 3 file names in separate columns
-- instead of storing each file in a separate row

-- Step 1: Create a backup of the current evidence table
CREATE TABLE evidence_backup AS SELECT * FROM evidence;

-- Step 2: Drop the current evidence table
DROP TABLE evidence;

-- Step 3: Create the new evidence table structure
CREATE TABLE `evidence` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `complaint_id` varchar(20) NOT NULL,
  `file_name_1` varchar(255) DEFAULT NULL,
  `file_name_2` varchar(255) DEFAULT NULL,
  `file_name_3` varchar(255) DEFAULT NULL,
  `file_type_1` varchar(50) DEFAULT NULL,
  `file_type_2` varchar(50) DEFAULT NULL,
  `file_type_3` varchar(50) DEFAULT NULL,
  `file_path_1` varchar(500) DEFAULT NULL,
  `file_path_2` varchar(500) DEFAULT NULL,
  `file_path_3` varchar(500) DEFAULT NULL,
  `compressed_size_1` int(11) DEFAULT NULL,
  `compressed_size_2` int(11) DEFAULT NULL,
  `compressed_size_3` int(11) DEFAULT NULL,
  `uploaded_by_type` enum('customer','user') NOT NULL,
  `uploaded_by_id` varchar(50) NOT NULL,
  `uploaded_at` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  KEY `idx_complaint_id` (`complaint_id`),
  KEY `idx_uploaded_by` (`uploaded_by_type`, `uploaded_by_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Step 4: Migrate existing data from backup table
-- Group by complaint_id and consolidate files into the new structure
INSERT INTO evidence (
    complaint_id,
    file_name_1, file_name_2, file_name_3,
    file_type_1, file_type_2, file_type_3,
    file_path_1, file_path_2, file_path_3,
    compressed_size_1, compressed_size_2, compressed_size_3,
    uploaded_by_type, uploaded_by_id, uploaded_at
)
SELECT 
    complaint_id,
    MAX(CASE WHEN row_num = 1 THEN file_name END) as file_name_1,
    MAX(CASE WHEN row_num = 2 THEN file_name END) as file_name_2,
    MAX(CASE WHEN row_num = 3 THEN file_name END) as file_name_3,
    MAX(CASE WHEN row_num = 1 THEN file_type END) as file_type_1,
    MAX(CASE WHEN row_num = 2 THEN file_type END) as file_type_2,
    MAX(CASE WHEN row_num = 3 THEN file_type END) as file_type_3,
    MAX(CASE WHEN row_num = 1 THEN file_path END) as file_path_1,
    MAX(CASE WHEN row_num = 2 THEN file_path END) as file_path_2,
    MAX(CASE WHEN row_num = 3 THEN file_path END) as file_path_3,
    MAX(CASE WHEN row_num = 1 THEN compressed_size END) as compressed_size_1,
    MAX(CASE WHEN row_num = 2 THEN compressed_size END) as compressed_size_2,
    MAX(CASE WHEN row_num = 3 THEN compressed_size END) as compressed_size_3,
    uploaded_by_type,
    uploaded_by_id,
    MIN(uploaded_at) as uploaded_at
FROM (
    SELECT 
        *,
        ROW_NUMBER() OVER (PARTITION BY complaint_id ORDER BY uploaded_at) as row_num
    FROM evidence_backup
) ranked
GROUP BY complaint_id, uploaded_by_type, uploaded_by_id;

-- Step 5: Clean up backup table (uncomment the line below after verifying data migration)
-- DROP TABLE evidence_backup;

-- Step 6: Add any missing indexes for performance
CREATE INDEX idx_evidence_complaint_uploaded ON evidence(complaint_id, uploaded_by_type, uploaded_by_id);

-- Verification queries (run these to check the migration)
-- SELECT COUNT(*) as total_evidence_records FROM evidence;
-- SELECT COUNT(*) as total_backup_records FROM evidence_backup;
-- SELECT complaint_id, file_name_1, file_name_2, file_name_3 FROM evidence WHERE file_name_1 IS NOT NULL LIMIT 10;
