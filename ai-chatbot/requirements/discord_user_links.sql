CREATE TABLE IF NOT EXISTS discord_user_links (
    discord_user_id VARCHAR(32) NOT NULL,
    travel_user_id BIGINT UNSIGNED NOT NULL,
    is_active TINYINT(1) NOT NULL DEFAULT 0,
    verified_by VARCHAR(32) DEFAULT NULL,
    verified_at TIMESTAMP NULL DEFAULT NULL,
    created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (discord_user_id),
    UNIQUE KEY unique_travel_user_link (travel_user_id),
    CONSTRAINT fk_discord_user_links_travel_user
        FOREIGN KEY (travel_user_id) REFERENCES users (id)
        ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
