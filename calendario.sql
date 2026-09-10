-- Eseguire una sola volta in phpMyAdmin, sul database my_butacu5256.
CREATE TABLE appuntamenti (
  id INT NOT NULL AUTO_INCREMENT,
  cliente_id INT NULL,
  servizio_id INT NULL,
  titolo VARCHAR(160) NOT NULL,
  inizio DATETIME NOT NULL,
  fine DATETIME NOT NULL,
  note TEXT NULL,
  google_event_id VARCHAR(255) NULL,
  google_updated_at DATETIME NULL,
  updated_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (id),
  UNIQUE KEY appuntamenti_google_event_id (google_event_id),
  KEY appuntamenti_inizio (inizio),
  KEY appuntamenti_cliente (cliente_id),
  KEY appuntamenti_servizio (servizio_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
