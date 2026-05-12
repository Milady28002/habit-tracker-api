# Habit Tracker - Backend API

## Description

Habit Tracker est une application web permettant à un utilisateur de gérer ses habitudes quotidiennes.

Cette partie backend a été développée avec **Symfony** sous la forme d’une **API REST sécurisée**. Elle permet :

- Authentification utilisateur par token
- Gestion des habitudes
- Création d’habitudes
- Modification d’habitudes
- Suppression d’habitudes
- Validation des habitudes par date
- Historique des validations
- Statistiques quotidiennes
- Sécurisation des routes API
- Gestion des données utilisateurs

Le backend est conçu pour fonctionner avec un frontend React qui consomme les endpoints de l’API.

---

## Stack technique

- Symfony
- PHP
- Doctrine ORM
- MySQL / MariaDB
- MongoDB
- Composer
- Docker
- Postman
- Nelmio CORS Bundle

---

## Fonctionnalités

### API REST Symfony

- création d’endpoints REST ;
- communication JSON avec le frontend React ;
- gestion des réponses HTTP et des erreurs.

### Authentification

- connexion utilisateur ;
- authentification par token ;
- sécurisation des routes API ;
- contrôle d’accès aux données personnelles ;
- vérification de l’utilisateur authentifié via le header :

```txt
X-AUTH-TOKEN
```

---

### Gestion des habitudes

- création d’une habitude
- récupération des habitudes
- modification d’une habitude
- suppression d’une habitude
- validation quotidienne d’une habitude
- gestion des habitudes par jour de la semaine

### Historique et statistiques

- suivi des validations quotidiennes
- historique des habitudes
- statistiques de complétion
- calcul du taux de réussite
- gestion des données de suivi avec MongoDB

### Base de données
- persistance des données principales avec Doctrine ORM
- stockage des habitudes et utilisateurs dans MySQL/MariaDB
- stockage des statistiques et validations dans MongoDB

### Autres fonctionnalités
- gestion du CORS avec NelmioCorsBundle
- tests des endpoints avec Postman
- optimisation des performances backend
- amélioration des temps de réponse API

---

## Structure du projet
config/
    - packages/
    - routes/
    - bundles.php
    - preload.php
    - routes.yaml
    - services.yaml

migrations/

public/

src/
    - Controller/
        - Api/
            - AuthController.php
            - HabitController.php
            - HistoryController.php
            - StatsController.php
            - UserController.php
        - LoginController.php

    - Entity/
        - Habit.php
        - User.php

    - Repository/
        - HabitRepository.php
        - UserRepository.php

    - Service/
        - HabitStatsService.php
        - MongoStatsService.php

    - Kernel.php

templates/
    - api/
    - base.html.twig

tests/
translations/
var/
vendor/

---

## Gestion des données

**MySQL / MariaDB**
La base relationnelle stocke :
- les utilisateurs
- les habitudes
- les jours associés aux habitudes

Doctrine ORM est utilisé pour gérer les entités et les repositories

**MongoDB**
MongoDB est utilisé pour stocker:
- les validations quotidiennes
- les statistiques
- l'historique utilisateur

Chaque document contient notamment :

- l’identifiant utilisateur
- l’identifiant de l’habitude
- la date
- le statut de validation

Ce choix permet de gérer plus facilement les statistiques temporelles et le suivi quotidien.

---


## Points techniques travaillés

Ce projet m’a permis de travailler :

- la création d’une API REST avec Symfony
- la structuration d’un backend en couches
- la gestion des entités et des repositories avec Doctrine
- l'utilisation conjointe de MySQL et MongoDB
- l’authentification par token
- la sécurisation des routes
- la communication avec un frontend React
- la résolution de problèmes techniques comme le CORS, les erreurs 404 ou la gestion des headers HTTP, l'optimisation des performances.backend.

---
## Evolutions possibles

- ajout d'un système d'inscription frontend
- statistiques avancées sur 7 ou 30 jours
- ajout de graphiques
- ajout de notifications
- ajout de tests automatisés


## 👩‍💻 Autrice
Projet réalisé par Sylvie
Formation Graduate Développeur Web Full Stack
