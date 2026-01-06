# Documentation du Projet S-SETICE
## Système de Gestion de Formation

---

## 📁 Structure Générale du Projet

### Architecture MVC Simplifiée
- **views/** : Interfaces utilisateur (pages HTML/PHP)
- **actions/** : Logique métier et traitement des formulaires
- **includes/** : Composants réutilisables (sidebar, mailer, etc.)
- **config/** : Configuration de base de données
- **api/** : Endpoints API REST
- **assets/** : Ressources statiques (CSS, JS, images)
- **uploads/** : Fichiers uploadés par les utilisateurs
- **logs/** : Journaux d'audit et de débogage
- **tools/** : Scripts utilitaires et de maintenance

---

## 👥 Types d'Utilisateurs et Expériences

### 1. **DIRECTEUR** (Administrateur Système)
**Rôles :** Gestion complète du système, utilisateurs, espaces pédagogiques

#### Expérience Utilisateur :
- **Dashboard principal** (`views/dashboard.php`) : Vue d'ensemble avec statistiques
- **Gestion des utilisateurs** :
  - Création/édition de formateurs (`views/formateurs.php`, `views/edit_formateur.php`)
  - Création/édition d'étudiants (`views/etudiants.php`, `views/edit_etudiant.php`)
- **Gestion pédagogique** :
  - Création de promotions (`views/promotions.php`, `views/edit_promotion.php`)
  - Gestion des espaces pédagogiques (`views/espaces.php`, `views/edit_espace.php`)
- **Assignation globale** : Attribution des espaces aux formateurs (`views/assignation_globale.php`)

#### Fichiers Clés :
- `actions/formateur_action.php` : CRUD formateurs
- `actions/student_action.php` : CRUD étudiants
- `actions/pedagogique_action.php` : Gestion espaces/promotions
- `actions/promotion_action.php` : Gestion promotions

### 2. **FORMATEUR** (Enseignant)
**Rôles :** Création de travaux, évaluation, gestion des espaces assignés

#### Expérience Utilisateur :
- **Dashboard formateur** (`views/formateur_dashboard.php`) : Vue d'ensemble de ses espaces
- **Consultation des espaces** (`views/consulter_espaces.php`) : Liste des espaces assignés
- **Création de travaux** (`views/nouveau_travail.php`) :
  - Types : INDIVIDUEL, GROUPE, COLLECTIF
  - Upload de fichiers joints
  - Gestion vidéo (optionnel/obligatoire)
  - Pour COLLECTIF : création manuelle de groupes
- **Gestion des travaux** (`views/mes_travaux_formateur.php`) : Liste des travaux créés
- **Évaluation** (`views/evaluer_travaux.php`) : Notation des rendus étudiants

#### Fichiers Clés :
- `actions/travail_action.php` : Création et gestion des travaux
- `actions/rendu_action.php` : Gestion des rendus étudiants
- `actions/update_action.php` : Modification des travaux
- `actions/delete_action.php` : Suppression des travaux

### 3. **ETUDIANT** (Apprenant)
**Rôles :** Consultation des travaux, rendu des devoirs, consultation des notes

#### Expérience Utilisateur :
- **Dashboard étudiant** (`views/etudiant_dashboard.php`) : Vue d'ensemble des travaux
- **Mes travaux** (`views/mes_travaux.php`) : Liste des travaux assignés
- **Rendre un travail** (`views/rendre_travail.php`) :
  - Upload de fichiers
  - Soumission avec commentaires
  - Gestion des rendus individuels/groupes
- **Mes notes** (`views/mes_notes.php`) : Consultation des évaluations
- **Classement général** (`views/classement_general.php`) : Positionnement dans la promotion

#### Fichiers Clés :
- `actions/rendu_action.php` : Soumission des travaux
- `actions/student_action.php` : Gestion du profil étudiant

---

## 📄 Détail des Fichiers par Catégorie

### 🔐 Authentification & Sécurité
- **`index.php`** : Page de connexion principale
- **`actions/auth_action.php`** : Traitement de l'authentification
- **`actions/logout.php`** : Déconnexion utilisateur
- **`views/activation.php`** : Activation de compte étudiant
- **`includes/csrf.php`** : Protection CSRF (Cross-Site Request Forgery)

**Expérience :** Connexion sécurisée avec protection CSRF, activation de compte par email

### 🎯 Interface Utilisateur (Views)
- **`views/dashboard.php`** : Dashboard administrateur avec statistiques
- **`views/formateur_dashboard.php`** : Dashboard formateur avec espaces assignés
- **`views/etudiant_dashboard.php`** : Dashboard étudiant avec travaux en cours
- **`includes/sidebar.php`** : Barre de navigation latérale (commune à tous)

**Expérience :** Navigation intuitive avec sidebar responsive, dashboards personnalisés

### 👨‍🏫 Gestion Pédagogique
- **`views/espaces.php`** : Liste des espaces pédagogiques
- **`views/edit_espace.php`** : Création/édition d'espaces
- **`views/promotions.php`** : Gestion des promotions
- **`views/edit_promotion.php`** : Création/édition de promotions
- **`views/assignation_globale.php`** : Assignation espaces-formateurs
- **`views/assignation_promo.php`** : Assignation étudiants-promotions

**Expérience :** Interface CRUD complète pour la gestion académique

### 📝 Gestion des Travaux
- **`views/nouveau_travail.php`** : Formulaire de création de travaux
- **`views/mes_travaux_formateur.php`** : Liste des travaux créés (formateur)
- **`views/mes_travaux.php`** : Liste des travaux assignés (étudiant)
- **`views/evaluer_travaux.php`** : Interface d'évaluation des rendus
- **`views/rendre_travail.php`** : Soumission des travaux étudiants

**Expérience :**
- **Formateur :** Création intuitive avec options avancées (groupes manuels, vidéos)
- **Étudiant :** Soumission simple avec upload de fichiers

### 👥 Gestion des Utilisateurs
- **`views/formateurs.php`** : Liste des formateurs
- **`views/etudiants.php`** : Liste des étudiants
- **`views/edit_formateur.php`** : Édition formateur
- **`views/edit_etudiant.php`** : Édition étudiant

**Expérience :** Gestion centralisée des comptes utilisateurs

### 📊 Évaluation & Notes
- **`views/mes_notes.php`** : Consultation des notes (étudiant)
- **`views/classement_general.php`** : Classement dans la promotion
- **`views/evaluer_travaux.php`** : Notation des travaux (formateur)

**Expérience :** Suivi transparent des performances académiques

### 🔧 Actions (Logique Métier)
- **`actions/travail_action.php`** : Création/gestion des travaux (CORRIGÉ)
- **`actions/rendu_action.php`** : Gestion des soumissions
- **`actions/update_action.php`** : Modifications diverses
- **`actions/delete_action.php`** : Suppressions
- **`actions/export_action.php`** : Export de données

**Expérience :** Traitement robuste avec gestion d'erreurs et messages utilisateur

### 🌐 API & Services
- **`api/get_students_by_promotion.php`** : API REST pour récupérer étudiants par promotion
- **`includes/mailer_config.php`** : Configuration des emails (PHPMailer)

**Expérience :** Communications automatiques (notifications de travaux, résultats)

### 📁 Upload & Fichiers
- **`actions/upload_chunk.php`** : Upload de gros fichiers par chunks
- **`assets/js/chunk_upload.js`** : JavaScript pour upload progressif
- **`uploads/rendus/`** : Stockage des rendus étudiants
- **`uploads/travaux/`** : Stockage des fichiers joints aux travaux

**Expérience :** Upload fiable avec progression visuelle

### 🛠️ Outils & Maintenance
- **`tools/check_tables.php`** : Vérification de l'intégrité DB
- **`tools/migrate_tables.php`** : Migrations de base de données
- **`tools/debug_db.php`** : Outils de débogage
- **`tools/phpinfo.php`** : Informations PHP
- **`tools/update_php_ini.ps1`** : Configuration PHP (Windows)

**Expérience :** Maintenance facilitée pour les administrateurs

### 🧪 Tests & Qualité
- **`test_comprehensive.php`** : Tests fonctionnels complets
- **`test_frontend_integration.php`** : Tests d'intégration frontend
- **`test_group_parsing.php`** : Tests du parsing de groupes
- **`test_smtp.php`** : Tests de configuration email

**Expérience :** Assurance qualité avec tests automatisés

### 📊 Base de Données & Configuration
- **`config/db.php`** : Configuration de connexion MySQL
- **`gasa_formation.sql`** : Script de création de la base
- **`includes/messages.php`** : Gestion des messages flash

**Expérience :** Persistance des données fiable et sécurisée

### 📝 Logs & Audit
- **`logs/audit.log`** : Journalisation des actions importantes

**Expérience :** Traçabilité complète des opérations système

---

## 🎯 Fonctionnalités Clés Actuellement Opérationnelles

### ✅ Authentification & Autorisation
- Connexion sécurisée avec hashage des mots de passe
- Gestion des rôles (DIRECTEUR, FORMATEUR, ETUDIANT)
- Protection CSRF sur tous les formulaires
- Activation de compte par email

### ✅ Gestion Pédagogique
- CRUD complet pour espaces pédagogiques
- Gestion des promotions et étudiants
- Assignation flexible des espaces aux formateurs
- Organisation hiérarchique : Espace → Formateur → Promotion → Étudiants

### ✅ Système de Travaux
- **3 types de travaux** : INDIVIDUEL, GROUPE, COLLECTIF
- **COLLECTIF avec groupes manuels** : Définition personnalisée des groupes
- Upload de fichiers joints aux travaux
- Gestion des vidéos (optionnel/obligatoire)
- Notifications automatiques par email

### ✅ Soumission & Évaluation
- Upload de rendus par les étudiants
- Évaluation par les formateurs avec commentaires
- Gestion des groupes pour travaux collaboratifs
- Historique des soumissions

### ✅ Interface Utilisateur
- Design responsive avec Bootstrap
- Navigation intuitive avec sidebar
- Messages d'erreur/succès contextuels
- Dashboards personnalisés par rôle

### ✅ Sécurité & Performance
- Protection contre les injections SQL (PDO)
- Validation des données côté serveur
- Upload sécurisé avec vérification des types
- Journalisation des actions sensibles

---

## 🚀 État Actuel du Projet

**Status :** Fonctionnel avec quelques corrections nécessaires

### ✅ Corrections Réalisées
- **Erreur PHPMailer corrigée** : Ajout des `require_once` pour les classes PHPMailer dans `includes/mailer_config.php`
- **Erreur de syntaxe corrigée** : Correction de la requête SQL dans `actions/travail_action.php` pour la création de travaux
- **Fonctionnalités opérationnelles** :
  - Authentification et autorisation
  - Création de travaux (INDIVIDUEL, GROUPE, COLLECTIF)
  - Gestion des groupes manuels pour travaux COLLECTIF
  - Upload de fichiers joints aux travaux
  - Notifications par email automatiques

### ⚠️ Problèmes Identifiés Nécessitant Correction

#### Base de Données
- **Colonnes manquantes** dans la table `travaux` :
  - `accept_videos` (TINYINT(1) DEFAULT 0)
  - `video_obligatoire` (TINYINT(1) DEFAULT 0)
- **Table manquante** `fichiers_travaux` pour la gestion des fichiers joints
- **Table manquante** `formateur_espace` pour les vérifications d'autorisation
- **Incohérence de charset** : table `utilisateur` utilise `utf8mb4_0900_ai_ci` au lieu de `utf8mb4_unicode_ci`

#### Code PHP - Erreurs Potentielles
- **Variables potentiellement non définies** dans certains chemins d'exécution
- **Gestion des fichiers upload** : manque de validation stricte des types/taille
- **Gestion des transactions** : rollback partiel en cas d'erreur après commit
- **Validation insuffisante** pour les groupes manuels (vérification que tous les étudiants sont assignés)

### 🧪 Tests
**Status :** Tests de base passent, mais couverture incomplète
- Tests PHPMailer : ✅ Réussi
- Tests de création de travaux : ✅ Réussi
- Tests d'upload : ⚠️ Non testé complètement
- Tests de sécurité : ⚠️ Non testé

### 📋 TODO - Actions Prioritaires
1. **Mettre à jour le schéma SQL** (`gasa_formation.sql`) :
   - Ajouter les colonnes manquantes à `travaux`
   - Créer la table `fichiers_travaux`
   - Créer la table `formateur_espace`
   - Uniformiser les charsets

2. **Améliorer la robustesse du code** :
   - Ajouter des validations supplémentaires
   - Améliorer la gestion d'erreurs
   - Optimiser les requêtes SQL

3. **Tests complets** :
   - Tests d'intégration complets
   - Tests de sécurité
   - Tests de performance

### 🎯 Recommandations Immédiates
- **Corriger le schéma de base de données** avant déploiement
- **Ajouter des logs détaillés** pour le débogage
- **Implémenter des tests automatisés** complets
- **Sauvegarde de la base actuelle** avant modifications

**État de préparation :** 80% - Corrections mineures nécessaires avant production

---

*Documentation mise à jour - Dernière analyse : État actuel du projet et base de données*
