-- =============================================
-- QUIDQUE STUDIO DATABASE SCHEMA
-- =============================================

-- Drop tables in reverse dependency order (for rebuilding)
DROP TABLE IF EXISTS message_attachments;
DROP TABLE IF EXISTS messages;
DROP TABLE IF EXISTS comments;
DROP TABLE IF EXISTS devlogs;
DROP TABLE IF EXISTS blog_post_tags;
DROP TABLE IF EXISTS blog_blocks;
DROP TABLE IF EXISTS blog_block_types;
DROP TABLE IF EXISTS blog_tags;
DROP TABLE IF EXISTS blog_posts;
DROP TABLE IF EXISTS blog_categories;
DROP TABLE IF EXISTS gallery_items;
DROP TABLE IF EXISTS project_blocks;
DROP TABLE IF EXISTS block_types;
DROP TABLE IF EXISTS project_tech_stack;
DROP TABLE IF EXISTS project_tags;
DROP TABLE IF EXISTS tech_stack;
DROP TABLE IF EXISTS tags;
DROP TABLE IF EXISTS projects;
DROP TABLE IF EXISTS media;
DROP TABLE IF EXISTS sessions;
DROP TABLE IF EXISTS auth_tokens;
DROP TABLE IF EXISTS users;

-- =============================================
-- USERS & AUTHENTICATION
-- =============================================

CREATE TABLE users (
    id              INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    email           VARCHAR(255) NOT NULL UNIQUE,
    username        VARCHAR(50) NOT NULL UNIQUE,
    is_admin        BOOLEAN DEFAULT FALSE,
    is_confirmed    BOOLEAN DEFAULT FALSE,
    settings        JSON DEFAULT ('{"anonymous": false, "theme": "dark"}'),
    created_at      DATETIME DEFAULT CURRENT_TIMESTAMP,
    updated_at      DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    
    INDEX idx_email (email),
    INDEX idx_username (username)
);

CREATE TABLE auth_tokens (
    id          INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    user_id     INT UNSIGNED NOT NULL,
    token       VARCHAR(64) NOT NULL,
    expires_at  DATETIME NOT NULL,
    used        BOOLEAN DEFAULT FALSE,
    created_at  DATETIME DEFAULT CURRENT_TIMESTAMP,
    
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    INDEX idx_token (token),
    INDEX idx_user_expires (user_id, expires_at)
);

CREATE TABLE sessions (
    id              VARCHAR(64) PRIMARY KEY,
    user_id         INT UNSIGNED NOT NULL,
    ip_address      VARCHAR(45),
    user_agent      VARCHAR(512),
    country         VARCHAR(100),
    city            VARCHAR(100),
    expires_at      DATETIME NOT NULL,
    created_at      DATETIME DEFAULT CURRENT_TIMESTAMP,
    last_active_at  DATETIME DEFAULT CURRENT_TIMESTAMP,
    
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    INDEX idx_user (user_id),
    INDEX idx_expires (expires_at)
);

-- =============================================
-- MEDIA LIBRARY
-- =============================================

CREATE TABLE media (
    id          INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    file_path   VARCHAR(500) NOT NULL,
    file_type   ENUM('image', 'video', 'audio') NOT NULL,
    mime_type   VARCHAR(100) NOT NULL,
    file_size   INT UNSIGNED NOT NULL,
    alt_text    VARCHAR(255),
    uploaded_by INT UNSIGNED,
    created_at  DATETIME DEFAULT CURRENT_TIMESTAMP,
    
    FOREIGN KEY (uploaded_by) REFERENCES users(id) ON DELETE SET NULL,
    INDEX idx_file_type (file_type)
);

-- =============================================
-- PROJECTS
-- =============================================

CREATE TABLE projects (
    id          INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    title       VARCHAR(200) NOT NULL,
    slug        VARCHAR(200) NOT NULL UNIQUE,
    description TEXT,
    status      ENUM('active', 'complete', 'on_hold', 'archived') DEFAULT 'active',
    is_featured BOOLEAN DEFAULT FALSE,
    settings    JSON DEFAULT ('{"devlog_enabled": false, "comments_enabled": false}'),
    created_at  DATETIME DEFAULT CURRENT_TIMESTAMP,
    updated_at  DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    
    INDEX idx_slug (slug),
    INDEX idx_status (status),
    INDEX idx_featured (is_featured)
);

-- Project tags (web, systems, embedded, game, tool, other)
CREATE TABLE tags (
    id      INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    name    VARCHAR(50) NOT NULL,
    slug    VARCHAR(50) NOT NULL UNIQUE
);

-- Tech stack with tiers (1=core, 2=framework, 3=library, 4=tool)
CREATE TABLE tech_stack (
    id      INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    name    VARCHAR(100) NOT NULL,
    slug    VARCHAR(100) NOT NULL UNIQUE,
    tier    TINYINT UNSIGNED DEFAULT 1,
    
    INDEX idx_tier (tier)
);

-- Project block types (link, download, audio, video, embed, gallery, etc.)
CREATE TABLE block_types (
    id      INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    name    VARCHAR(100) NOT NULL,
    slug    VARCHAR(100) NOT NULL UNIQUE,
    schema  JSON
);

-- Junction: projects <-> tags (max 2 per project, enforced in code)
CREATE TABLE project_tags (
    project_id  INT UNSIGNED NOT NULL,
    tag_id      INT UNSIGNED NOT NULL,
    
    PRIMARY KEY (project_id, tag_id),
    FOREIGN KEY (project_id) REFERENCES projects(id) ON DELETE CASCADE,
    FOREIGN KEY (tag_id) REFERENCES tags(id) ON DELETE CASCADE
);

-- Junction: projects <-> tech_stack
CREATE TABLE project_tech_stack (
    project_id  INT UNSIGNED NOT NULL,
    tech_id     INT UNSIGNED NOT NULL,
    
    PRIMARY KEY (project_id, tech_id),
    FOREIGN KEY (project_id) REFERENCES projects(id) ON DELETE CASCADE,
    FOREIGN KEY (tech_id) REFERENCES tech_stack(id) ON DELETE CASCADE
);

-- Modular content blocks for projects
CREATE TABLE project_blocks (
    id              INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    project_id      INT UNSIGNED NOT NULL,
    block_type_id   INT UNSIGNED NOT NULL,
    data            JSON,
    sort_order      INT UNSIGNED DEFAULT 0,
    
    FOREIGN KEY (project_id) REFERENCES projects(id) ON DELETE CASCADE,
    FOREIGN KEY (block_type_id) REFERENCES block_types(id) ON DELETE RESTRICT,
    INDEX idx_project_order (project_id, sort_order)
);

-- Gallery items (for gallery-type blocks)
CREATE TABLE gallery_items (
    id          INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    block_id    INT UNSIGNED NOT NULL,
    media_id    INT UNSIGNED NOT NULL,
    sort_order  INT UNSIGNED DEFAULT 0,
    
    FOREIGN KEY (block_id) REFERENCES project_blocks(id) ON DELETE CASCADE,
    FOREIGN KEY (media_id) REFERENCES media(id) ON DELETE CASCADE,
    INDEX idx_block_order (block_id, sort_order)
);

-- =============================================
-- BLOG
-- =============================================

CREATE TABLE blog_categories (
    id      INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    name    VARCHAR(100) NOT NULL,
    slug    VARCHAR(100) NOT NULL UNIQUE
);

CREATE TABLE blog_posts (
    id              INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    title           VARCHAR(300) NOT NULL,
    slug            VARCHAR(300) NOT NULL UNIQUE,
    author_id       INT UNSIGNED NOT NULL,
    category_id     INT UNSIGNED,
    status          ENUM('draft', 'published') DEFAULT 'draft',
    published_at    DATETIME,
    created_at      DATETIME DEFAULT CURRENT_TIMESTAMP,
    updated_at      DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    
    FOREIGN KEY (author_id) REFERENCES users(id) ON DELETE RESTRICT,
    FOREIGN KEY (category_id) REFERENCES blog_categories(id) ON DELETE SET NULL,
    INDEX idx_slug (slug),
    INDEX idx_status_date (status, published_at)
);

CREATE TABLE blog_tags (
    id      INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    name    VARCHAR(100) NOT NULL,
    slug    VARCHAR(100) NOT NULL UNIQUE
);

CREATE TABLE blog_post_tags (
    post_id INT UNSIGNED NOT NULL,
    tag_id  INT UNSIGNED NOT NULL,
    
    PRIMARY KEY (post_id, tag_id),
    FOREIGN KEY (post_id) REFERENCES blog_posts(id) ON DELETE CASCADE,
    FOREIGN KEY (tag_id) REFERENCES blog_tags(id) ON DELETE CASCADE
);

-- Blog block types (heading, text, image, code, link, project_link)
CREATE TABLE blog_block_types (
    id      INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    name    VARCHAR(100) NOT NULL,
    slug    VARCHAR(100) NOT NULL UNIQUE,
    schema  JSON
);

-- Blog content blocks
CREATE TABLE blog_blocks (
    id              INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    post_id         INT UNSIGNED NOT NULL,
    block_type_id   INT UNSIGNED NOT NULL,
    data            JSON,
    sort_order      INT UNSIGNED DEFAULT 0,
    
    FOREIGN KEY (post_id) REFERENCES blog_posts(id) ON DELETE CASCADE,
    FOREIGN KEY (block_type_id) REFERENCES blog_block_types(id) ON DELETE RESTRICT,
    INDEX idx_post_order (post_id, sort_order)
);

-- =============================================
-- DEVLOGS
-- =============================================

CREATE TABLE devlogs (
    id          INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    project_id  INT UNSIGNED NOT NULL,
    title       VARCHAR(200) NOT NULL,
    slug        VARCHAR(200) NOT NULL,
    content     TEXT,
    created_at  DATETIME DEFAULT CURRENT_TIMESTAMP,
    
    FOREIGN KEY (project_id) REFERENCES projects(id) ON DELETE CASCADE,
    UNIQUE KEY unique_project_slug (project_id, slug),
    INDEX idx_project_date (project_id, created_at)
);

-- =============================================
-- COMMENTS
-- =============================================

CREATE TABLE comments (
    id          INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    project_id  INT UNSIGNED NOT NULL,
    user_id     INT UNSIGNED NOT NULL,
    parent_id   INT UNSIGNED,
    content     VARCHAR(2000) NOT NULL,
    is_edited   BOOLEAN DEFAULT FALSE,
    deleted_by  ENUM('user', 'admin'),
    created_at  DATETIME DEFAULT CURRENT_TIMESTAMP,
    updated_at  DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    
    FOREIGN KEY (project_id) REFERENCES projects(id) ON DELETE CASCADE,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    FOREIGN KEY (parent_id) REFERENCES comments(id) ON DELETE CASCADE,
    INDEX idx_project_date (project_id, created_at)
);

-- =============================================
-- MESSAGES (User <-> Admin DM)
-- =============================================

CREATE TABLE messages (
    id              INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    user_id         INT UNSIGNED NOT NULL,
    is_from_admin   BOOLEAN DEFAULT FALSE,
    content         TEXT,
    image_path      VARCHAR(500),
    created_at      DATETIME DEFAULT CURRENT_TIMESTAMP,
    
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    INDEX idx_user_date (user_id, created_at)
);

-- =============================================
-- SEED DATA: Default block types
-- =============================================

-- Project block types
INSERT INTO block_types (name, slug, schema) VALUES
('Link', 'link', '{"fields": ["url", "label"]}'),
('Download', 'download', '{"fields": ["file_path", "label", "file_size"]}'),
('Audio Embed', 'audio', '{"fields": ["url", "label"]}'),
('Video Embed', 'video', '{"fields": ["url", "label"]}'),
('Embedded Interactive', 'embed', '{"fields": ["embed_code", "label"]}'),
('Gallery', 'gallery', '{"fields": []}');

-- Blog block types
INSERT INTO blog_block_types (name, slug, schema) VALUES
('Heading', 'heading', '{"fields": ["text", "level"]}'),
('Text', 'text', '{"fields": ["content"]}'),
('Image', 'image', '{"fields": ["media_id", "caption"]}'),
('Code', 'code', '{"fields": ["content", "language"]}'),
('Link', 'link', '{"fields": ["url", "label"]}'),
('Project Link', 'project_link', '{"fields": ["project_id"]}');

-- Default tags
INSERT INTO tags (name, slug) VALUES
('Web', 'web'),
('Systems', 'systems'),
('Embedded', 'embedded'),
('Game', 'game'),
('Tool', 'tool'),
('Other', 'other');

-- Default tech stack tiers example
INSERT INTO tech_stack (name, slug, tier) VALUES
('PHP', 'php', 1),
('Rust', 'rust', 1),
('Go', 'go', 1),
('MariaDB', 'mariadb', 1),
('PostgreSQL', 'postgresql', 1),
('HTMX', 'htmx', 3),
('Tailwind CSS', 'tailwind', 3);

-- Default blog categories
INSERT INTO blog_categories (name, slug) VALUES
('Announcement', 'announcement'),
('Tutorial', 'tutorial'),
('Personal', 'personal'),
('Update', 'update');