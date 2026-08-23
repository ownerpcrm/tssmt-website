ALTER TABLE donations
  MODIFY COLUMN member_id BIGINT UNSIGNED NULL,
  ADD COLUMN donor_name VARCHAR(150) NULL AFTER member_id,
  ADD COLUMN donor_mobile VARCHAR(20) NULL AFTER donor_name,
  ADD COLUMN donor_email VARCHAR(190) NULL AFTER donor_mobile,
  ADD COLUMN donor_address TEXT NULL AFTER donor_email,
  ADD COLUMN donor_state VARCHAR(100) NULL AFTER donor_address,
  ADD COLUMN donor_city VARCHAR(100) NULL AFTER donor_state,
  ADD COLUMN donor_pan VARCHAR(20) NULL AFTER donor_city,
  ADD COLUMN donor_aadhaar VARCHAR(20) NULL AFTER donor_pan;
