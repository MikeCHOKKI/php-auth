-- ==========================================
-- Initialisation de la base de données php-auth
-- PostgreSQL 15
-- ==========================================

-- Extensions
CREATE EXTENSION IF NOT EXISTS "uuid-ossp";

-- ==========================================
-- Table: users
-- ==========================================
CREATE TABLE IF NOT EXISTS users (
    id UUID PRIMARY KEY DEFAULT uuid_generate_v4(),
    email VARCHAR(255) NOT NULL UNIQUE,
    password_hash VARCHAR(255) NOT NULL,
    created_at TIMESTAMP WITH TIME ZONE DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP WITH TIME ZONE DEFAULT CURRENT_TIMESTAMP
);

COMMENT ON TABLE users IS 'Utilisateurs du système';
COMMENT ON COLUMN users.password_hash IS 'Hash Argon2id du mot de passe';

-- ==========================================
-- Table: roles
-- ==========================================
CREATE TABLE IF NOT EXISTS roles (
    id UUID PRIMARY KEY DEFAULT uuid_generate_v4(),
    name VARCHAR(100) NOT NULL UNIQUE,
    description TEXT,
    created_at TIMESTAMP WITH TIME ZONE DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP WITH TIME ZONE DEFAULT CURRENT_TIMESTAMP
);

COMMENT ON TABLE roles IS 'Rôles pour RBAC';

-- ==========================================
-- Table: permissions
-- ==========================================
CREATE TABLE IF NOT EXISTS permissions (
    id UUID PRIMARY KEY DEFAULT uuid_generate_v4(),
    resource VARCHAR(100) NOT NULL,
    action VARCHAR(100) NOT NULL,
    description VARCHAR(255),
    created_at TIMESTAMP WITH TIME ZONE DEFAULT CURRENT_TIMESTAMP,
    UNIQUE (resource, action)
);

COMMENT ON TABLE permissions IS 'Permissions granulaires (resource:action)';

-- ==========================================
-- Table: user_roles (many-to-many)
-- ==========================================
CREATE TABLE IF NOT EXISTS user_roles (
    user_id UUID NOT NULL REFERENCES users(id) ON DELETE CASCADE,
    role_id UUID NOT NULL REFERENCES roles(id) ON DELETE CASCADE,
    assigned_at TIMESTAMP WITH TIME ZONE DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (user_id, role_id)
);

COMMENT ON TABLE user_roles IS 'Association users ↔ roles';

-- ==========================================
-- Table: role_permissions (many-to-many)
-- ==========================================
CREATE TABLE IF NOT EXISTS role_permissions (
    role_id UUID NOT NULL REFERENCES roles(id) ON DELETE CASCADE,
    permission_id UUID NOT NULL REFERENCES permissions(id) ON DELETE CASCADE,
    granted_at TIMESTAMP WITH TIME ZONE DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (role_id, permission_id)
);

COMMENT ON TABLE role_permissions IS 'Association roles ↔ permissions';

-- ==========================================
-- Table: refresh_tokens
-- ==========================================
CREATE TABLE IF NOT EXISTS refresh_tokens (
    id UUID PRIMARY KEY DEFAULT uuid_generate_v4(),
    user_id UUID NOT NULL REFERENCES users(id) ON DELETE CASCADE,
    token_hash VARCHAR(255) NOT NULL UNIQUE,
    expires_at TIMESTAMP WITH TIME ZONE NOT NULL,
    created_at TIMESTAMP WITH TIME ZONE DEFAULT CURRENT_TIMESTAMP,
    replaced_by UUID REFERENCES refresh_tokens(id) ON DELETE SET NULL,
    revoked_at TIMESTAMP WITH TIME ZONE,
    is_revoked BOOLEAN DEFAULT FALSE
);

CREATE INDEX idx_refresh_tokens_user_id ON refresh_tokens(user_id);
CREATE INDEX idx_refresh_tokens_token_hash ON refresh_tokens(token_hash);
CREATE INDEX idx_refresh_tokens_expires_at ON refresh_tokens(expires_at);

COMMENT ON TABLE refresh_tokens IS 'Refresh tokens avec rotation';
COMMENT ON COLUMN refresh_tokens.token_hash IS 'SHA-256 du token (pas le token en clair)';
COMMENT ON COLUMN refresh_tokens.replaced_by IS 'ID du nouveau token après rotation';

-- ==========================================
-- Indexes pour performance
-- ==========================================
CREATE INDEX idx_users_email ON users(email);
CREATE INDEX idx_roles_name ON roles(name);
CREATE INDEX idx_permissions_resource_action ON permissions(resource, action);

-- ==========================================
-- Trigger: updated_at automatique
-- ==========================================
CREATE OR REPLACE FUNCTION update_updated_at_column()
RETURNS TRIGGER AS $$
BEGIN
    NEW.updated_at = CURRENT_TIMESTAMP;
    RETURN NEW;
END;
$$ language 'plpgsql';

CREATE TRIGGER update_users_updated_at BEFORE UPDATE ON users
    FOR EACH ROW EXECUTE FUNCTION update_updated_at_column();

CREATE TRIGGER update_roles_updated_at BEFORE UPDATE ON roles
    FOR EACH ROW EXECUTE FUNCTION update_updated_at_column();

-- ==========================================
-- Données initiales
-- ==========================================

-- Rôles par défaut
INSERT INTO roles (name, description) VALUES
    ('admin', 'Administrateur système - accès complet'),
    ('user', 'Utilisateur standard - accès limité')
ON CONFLICT (name) DO NOTHING;

-- Permissions par défaut
INSERT INTO permissions (resource, action, description) VALUES
    ('user', 'read', 'Lire les informations utilisateur'),
    ('user', 'create', 'Créer un utilisateur'),
    ('user', 'update', 'Modifier un utilisateur'),
    ('user', 'delete', 'Supprimer un utilisateur'),
    ('role', 'read', 'Lire les rôles'),
    ('role', 'create', 'Créer un rôle'),
    ('role', 'update', 'Modifier un rôle'),
    ('role', 'delete', 'Supprimer un rôle'),
    ('permission', 'read', 'Lire les permissions'),
    ('permission', 'grant', 'Accorder une permission'),
    ('permission', 'revoke', 'Révoquer une permission')
ON CONFLICT (resource, action) DO NOTHING;

-- Attribution des permissions au rôle admin
INSERT INTO role_permissions (role_id, permission_id)
SELECT r.id, p.id
FROM roles r, permissions p
WHERE r.name = 'admin'
ON CONFLICT DO NOTHING;

-- Attribution des permissions de base au rôle user
INSERT INTO role_permissions (role_id, permission_id)
SELECT r.id, p.id
FROM roles r, permissions p
WHERE r.name = 'user' AND p.resource = 'user' AND p.action = 'read'
ON CONFLICT DO NOTHING;
