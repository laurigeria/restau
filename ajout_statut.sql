ALTER TABLE reservations
ADD COLUMN statut ENUM('en_attente', 'validee') NOT NULL DEFAULT 'en_attente' AFTER qr_code_path;
