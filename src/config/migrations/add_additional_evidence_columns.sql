-- Migration to add columns for 2 additional evidence files
-- These will be used when additional information is requested

ALTER TABLE `evidence` ADD COLUMN `additional_file_name_1` varchar(255) DEFAULT NULL AFTER `compressed_size_3`;
ALTER TABLE `evidence` ADD COLUMN `additional_file_name_2` varchar(255) DEFAULT NULL AFTER `additional_file_name_1`;
ALTER TABLE `evidence` ADD COLUMN `additional_file_type_1` varchar(50) DEFAULT NULL AFTER `additional_file_name_2`;
ALTER TABLE `evidence` ADD COLUMN `additional_file_type_2` varchar(50) DEFAULT NULL AFTER `additional_file_type_1`;
ALTER TABLE `evidence` ADD COLUMN `additional_file_path_1` varchar(500) DEFAULT NULL AFTER `additional_file_type_2`;
ALTER TABLE `evidence` ADD COLUMN `additional_file_path_2` varchar(500) DEFAULT NULL AFTER `additional_file_path_1`;
ALTER TABLE `evidence` ADD COLUMN `additional_compressed_size_1` int(11) DEFAULT NULL AFTER `additional_file_path_2`;
ALTER TABLE `evidence` ADD COLUMN `additional_compressed_size_2` int(11) DEFAULT NULL AFTER `additional_compressed_size_1`;
ALTER TABLE `evidence` ADD COLUMN `additional_files_uploaded_at` timestamp NULL DEFAULT NULL AFTER `additional_compressed_size_2`;

-- Add comment to clarify the purpose
ALTER TABLE `evidence` COMMENT = 'Evidence table with support for 3 initial files + 2 additional files when info is requested';