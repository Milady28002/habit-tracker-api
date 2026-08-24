INSERT INTO `user` (email, roles, password, api_token)
VALUES (
    'test@habittracker.fr',
    '["ROLE_USER"]',
    '$2y$13$motdepassehacheexemple',
    '123456aAAAAaaaaaaaaaaaaaaaaaa'
);

INSERT INTO habit (title, done, days, owner_id)
VALUES
(
    'Boire de l''eau',
    0,
    '["lundi","mardi","mercredi","jeudi","vendredi","samedi","dimanche"]',
    1
),
(
    'Méditer 10 min',
    0,
    '["lundi","mercredi","vendredi"]',
    1
),
(
    'Faire du sport',
    1,
    '["mardi","jeudi","samedi"]',
    1
);