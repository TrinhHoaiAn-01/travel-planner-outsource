
-- pipeline hoạt động: tạo database → tạo chat sessions → tạo chat messages → tạo chat attachments

CREATE DATABASE IF NOT EXISTS ai_chatbot
    CHARACTER SET utf8mb4
    COLLATE utf8mb4_unicode_ci;

USE ai_chatbot;


-- 1. lưu các phiên trò chuyện
CREATE TABLE IF NOT EXISTS chat_sessions (
    session_id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    user_id VARCHAR(50) NOT NULL,
    channel_id VARCHAR(50) NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,

    INDEX idx_session_user_channel (
        user_id,
        channel_id,
        session_id
    )
);


-- 2. lưu tin nhắn của người dùng và AI
CREATE TABLE IF NOT EXISTS chat_messages (
    message_id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    session_id BIGINT UNSIGNED NOT NULL,
    role ENUM('user', 'assistant', 'system') NOT NULL,
    content LONGTEXT NOT NULL,
    metadata JSON DEFAULT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,

    CONSTRAINT fk_message_session
        FOREIGN KEY (session_id)
        REFERENCES chat_sessions(session_id)
        ON DELETE CASCADE,

    INDEX idx_message_session_id (
        session_id,
        message_id
    )
);


-- 3. lưu thông tin tệp đính kèm của từng tin nhắn
CREATE TABLE IF NOT EXISTS chat_attachments (
    attachment_id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    message_id BIGINT UNSIGNED NOT NULL,
    filename VARCHAR(255) NOT NULL,
    content_type VARCHAR(100) DEFAULT NULL,
    media_type ENUM(
        'image',
        'audio',
        'video',
        'document',
        'other'
    ) NOT NULL DEFAULT 'other',
    size_bytes BIGINT UNSIGNED DEFAULT NULL,
    storage_path TEXT DEFAULT NULL,
    source_url TEXT DEFAULT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,

    CONSTRAINT fk_attachment_message
        FOREIGN KEY (message_id)
        REFERENCES chat_messages(message_id)
        ON DELETE CASCADE,

    INDEX idx_attachment_message_id (
        message_id
    ),
    INDEX idx_attachment_media_type (
        media_type
    )
);
