CREATE TABLE users (
  id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  username VARCHAR(32) NOT NULL UNIQUE,
  email VARCHAR(190) NOT NULL UNIQUE,
  password_hash VARCHAR(255) NOT NULL,
  balance_kopecks BIGINT NOT NULL DEFAULT 1000000,
  created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  last_login_at TIMESTAMP NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE wallet_transactions (
  id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  user_id BIGINT UNSIGNED NOT NULL,
  type VARCHAR(40) NOT NULL,
  amount_kopecks BIGINT NOT NULL,
  balance_after_kopecks BIGINT NOT NULL,
  reference VARCHAR(100) NULL,
  metadata_json JSON NULL,
  created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  INDEX idx_wallet_user_created (user_id, created_at),
  INDEX idx_wallet_user_type_ref (user_id, type, reference),
  CONSTRAINT fk_wallet_user FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE user_game_states (
  user_id BIGINT UNSIGNED NOT NULL,
  game_key VARCHAR(64) NOT NULL,
  free_spins INT NOT NULL DEFAULT 0,
  storm_charge DECIMAL(6,2) NOT NULL DEFAULT 0,
  multiplier_map_json JSON NULL,
  updated_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (user_id, game_key),
  CONSTRAINT fk_game_state_user FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE game_rounds (
  id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  user_id BIGINT UNSIGNED NOT NULL,
  game_key VARCHAR(64) NOT NULL,
  mode VARCHAR(32) NOT NULL,
  bet_kopecks BIGINT NOT NULL,
  cost_kopecks BIGINT NOT NULL,
  win_kopecks BIGINT NOT NULL,
  balance_before_kopecks BIGINT NOT NULL,
  balance_after_kopecks BIGINT NOT NULL,
  result_json JSON NULL,
  created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  INDEX idx_round_user_created (user_id, created_at),
  INDEX idx_round_created (created_at),
  INDEX idx_round_game_created (game_key, created_at),
  CONSTRAINT fk_round_user FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE request_idempotency (
  user_id BIGINT UNSIGNED NOT NULL,
  scope VARCHAR(120) NOT NULL,
  request_id VARCHAR(80) NOT NULL,
  response_json JSON NOT NULL,
  created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (user_id, scope, request_id),
  INDEX idx_request_idempotency_created (created_at),
  CONSTRAINT fk_request_idempotency_user FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE schema_migrations (
  version VARCHAR(100) PRIMARY KEY,
  applied_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

INSERT INTO schema_migrations(version) VALUES ('20260913_001_query_indexes'),('20260913_002_request_idempotency');
