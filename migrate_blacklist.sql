-- Run this once on your dbinnsync database
ALTER TABLE users
  MODIFY COLUMN status ENUM('approved', 'rejected', 'blacklist') DEFAULT 'approved';
