USE form2;

INSERT INTO users (name, email, password, created_at, updated_at)
VALUES ('Administrador Forms Hub', 'admin@formshub.local', '$2y$12$DRJ8tB3P6T3E20hzX4XcRuYHNnXwMmOJOJM/48UuT86AobMAkO.Qq', NOW(), NOW())
ON DUPLICATE KEY UPDATE
    name = VALUES(name),
    password = VALUES(password),
    updated_at = NOW();

INSERT INTO campaigns (id, name, token, type, status, title, description, thank_you_message, published_at, created_at, updated_at)
VALUES
    (1, 'Levantamiento de onboarding', 'ONBOARD26', 'form', 'published', 'Campaña de onboarding interno', 'Recopila información de colaboradores nuevos con preguntas mixtas, carga de documentos y datos de contacto.', 'Recibimos tu información. Pronto el equipo se pondrá en contacto contigo.', NOW(), NOW(), NOW()),
    (2, 'Circular de beneficios 2026', 'BENEF26', 'info', 'published', 'Actualización de beneficios corporativos', 'Página informativa para compartir cambios de beneficios internos con apoyo visual y texto estructurado.', NULL, NOW(), NOW(), NOW())
ON DUPLICATE KEY UPDATE
    name = VALUES(name),
    type = VALUES(type),
    status = VALUES(status),
    title = VALUES(title),
    description = VALUES(description),
    thank_you_message = VALUES(thank_you_message),
    published_at = VALUES(published_at),
    updated_at = NOW();

DELETE FROM campaign_blocks WHERE campaign_id IN (1, 2);
DELETE FROM question_options WHERE question_id IN (SELECT id FROM questions WHERE campaign_id = 1);
DELETE FROM questions WHERE campaign_id = 1;

INSERT INTO campaign_blocks (campaign_id, type, title, content, position, created_at, updated_at)
VALUES
    (1, 'text', 'Antes de empezar', 'Este formulario nos permite centralizar antecedentes de ingreso, preferencias y documentos básicos en una sola experiencia.', 0, NOW(), NOW()),
    (1, 'text', 'Tiempo estimado', 'Completarlo toma menos de 5 minutos. Ten a mano tu documento de identificación y datos de contacto.', 1, NOW(), NOW()),
    (2, 'text', 'Seguro complementario', 'A partir de mayo se amplía la cobertura para consultas y exámenes preventivos, incluyendo nuevas clínicas en convenio.', 0, NOW(), NOW()),
    (2, 'text', 'Trabajo flexible', 'Se formaliza la modalidad híbrida con dos jornadas remotas por semana para los equipos administrativos.', 1, NOW(), NOW());

INSERT INTO questions (id, campaign_id, label, help_text, type, is_required, placeholder, position, settings, created_at, updated_at)
VALUES
    (101, 1, 'Nombre completo', 'Escribe tu nombre tal como aparece en tus documentos.', 'text', 1, NULL, 0, NULL, NOW(), NOW()),
    (102, 1, 'Correo personal', 'Usaremos este correo para seguimiento.', 'email', 1, 'nombre@dominio.com', 1, NULL, NOW(), NOW()),
    (103, 1, 'Años de experiencia', 'Ingresa un número aproximado.', 'number', 0, '3', 2, NULL, NOW(), NOW()),
    (104, 1, 'Fecha de ingreso', NULL, 'date', 1, NULL, 3, NULL, NOW(), NOW()),
    (105, 1, 'Modalidad de trabajo', NULL, 'single_choice', 1, NULL, 4, NULL, NOW(), NOW()),
    (106, 1, 'Herramientas que utilizas', NULL, 'multiple_choice', 0, NULL, 5, NULL, NOW(), NOW()),
    (107, 1, '¿Necesitas equipo computacional?', NULL, 'true_false', 1, NULL, 6, NULL, NOW(), NOW()),
    (108, 1, 'Adjunta tu documento de identificación', 'Acepta PDF, Word o imágenes.', 'file', 0, NULL, 7, JSON_OBJECT('max_size_kb', 10240), NOW(), NOW())
ON DUPLICATE KEY UPDATE
    label = VALUES(label),
    help_text = VALUES(help_text),
    type = VALUES(type),
    is_required = VALUES(is_required),
    placeholder = VALUES(placeholder),
    position = VALUES(position),
    settings = VALUES(settings),
    updated_at = NOW();

INSERT INTO question_options (question_id, label, value, position, created_at, updated_at)
VALUES
    (105, 'Presencial', 'presencial', 0, NOW(), NOW()),
    (105, 'Híbrida', 'hibrida', 1, NOW(), NOW()),
    (105, 'Remota', 'remota', 2, NOW(), NOW()),
    (106, 'Excel', 'excel', 0, NOW(), NOW()),
    (106, 'ERP', 'erp', 1, NOW(), NOW()),
    (106, 'CRM', 'crm', 2, NOW(), NOW()),
    (106, 'Power BI', 'power_bi', 3, NOW(), NOW()),
    (107, 'Verdadero', 'true', 0, NOW(), NOW()),
    (107, 'Falso', 'false', 1, NOW(), NOW());

ALTER TABLE campaigns AUTO_INCREMENT = 3;
