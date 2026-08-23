ALTER TABLE gallery_images
  ADD COLUMN member_id BIGINT UNSIGNED NULL AFTER id,
  MODIFY COLUMN status ENUM('pending','active','inactive') NOT NULL DEFAULT 'active',
  ADD CONSTRAINT fk_gallery_member FOREIGN KEY (member_id) REFERENCES members(id) ON DELETE SET NULL;
