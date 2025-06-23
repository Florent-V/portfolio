
-- Suppression des anciens utilisateurs avec les mêmes emails (évite les doublons)
DELETE FROM user WHERE email IN ('user@omega.com', 'admin@omega.com', 'superadmin@omega.com');

-- Ajout des utilisateurs avec rôles
INSERT INTO user (email, password, roles, user_name, first_name, last_name, is_verified) VALUES
('user@omega.com',  '$2y$13$iUb2JqiZ3CnQGwOAJpJjIebgCe5Aata1XXW88D6ZvyjWdXoBUIY.6', '["ROLE_USER"]', 'user', 'user', 'user', 1),
('admin@omega.com', '$2y$13$iUb2JqiZ3CnQGwOAJpJjIebgCe5Aata1XXW88D6ZvyjWdXoBUIY.6', '["ROLE_USER", "ROLE_ADMIN"]', 'admin', 'admin', 'admin', 1),
('superadmin@omega.com', '$2y$13$iUb2JqiZ3CnQGwOAJpJjIebgCe5Aata1XXW88D6ZvyjWdXoBUIY.6', '["ROLE_USER", "ROLE_ADMIN", "ROLE_SUPER_ADMIN"]', 'superadmin', 'superadmin', 'superadmin', 1);

INSERT INTO user (email, password, roles, user_name, first_name, last_name, is_verified) VALUES
('test@omega.com',  '$2y$13$iUb2JqiZ3CnQGwOAJpJjIebgCe5Aata1XXW88D6ZvyjWdXoBUIY.6', '["ROLE_USER"]', 'user', 'user', 'user', 0);
