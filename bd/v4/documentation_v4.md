### Documentation des tables de la base de données
### Schéma MLD
![Schéma MLD](https://cdn.discordapp.com/attachments/1292735811959394304/1316351413726482484/image.png?ex=6761fb89&is=6760aa09&hm=70c67724b6d8150d8df5fad7747cda8f7d475c84a558642d0dc854254e0fbaef&)
La table company n'a pas été implémenter.

---

### 1. **Tables**

#### 1.1 **Teacher**
- **Table** : `Teacher`
- **Description** : Contient les informations des enseignants.
- **Colonnes** :
  - `Id_teacher` : Identifiant unique de l'enseignant (clé primaire) (ce numéro est nécessairement un `user_id` permettant à l'enseignant d'accéder au site).
  - `Teacher_name` : Nom de l'enseignant.
  - `Teacher_firstname` : Prénom de l'enseignant.
  - `Maxi_number_trainees` : Nombre maximum de stagiaires qu'un enseignant peut superviser.

#### 1.2 **Student**
- **Table** : `Student`
- **Description** : Contient les informations des étudiants.
- **Colonnes** :
  - `Student_number` : Numéro d'identification unique de l'étudiant (clé primaire).
  - `Student_name` : Nom de l'étudiant.
  - `Student_firstname` : Prénom de l'étudiant.
  - `Formation` : Formation de l'étudiant.
  - `Class_group` : Groupe de classe de l'étudiant.

#### 1.3 **Discipline**
- **Table** : `Discipline`
- **Description** : Contient les disciplines enseignées par les enseignants.
- **Colonnes** :
  - `Discipline_name` : Nom de la discipline (clé primaire).

#### 1.4 **User_connect**
- **Table** : `User_connect`
- **Description** : Contient les informations d'authentification des utilisateurs.
- **Colonnes** :
  - `User_id` : Identifiant unique de l'utilisateur (clé primaire) (peut être un `Id_teacher`).
  - `User_pass` : Mot de passe de l'utilisateur.

#### 1.5 **Role**
- **Table** : `Role`
- **Description** : Contient les rôles attribués aux utilisateurs.
- **Colonnes** :
  - `Role_name` : Nom du rôle (clé primaire).

#### 1.6 **Distribution_criteria**
- **Table** : `Distribution_criteria`
- **Description** : Contient les critères utilisés pour la distribution des stages (définit les critères disponible pour l'utilisateur).
- **Colonnes** :
  - `Name_criteria` : Nom du critère (clé primaire).

#### 1.7 **Addr_name**
- **Table** : `Addr_name`
- **Description** : Contient les adresses.
- **Colonnes** :
  - `Address` : Adresse (clé primaire).

#### 1.8 **Address_type**
- **Table** : `Address_type`
- **Description** : Contient les types d'adresses.
- **Colonnes** :
  - `Type` : Type de l'adresse (clé primaire).

#### 1.9 **Id_backup**
- **Table** : `Id_backup`
- **Description** : Contient des identifiants pour les sauvegardes (le nombre de sauvegarde maximum par utilisateur est géré par cette table).
- **Colonnes** :
  - `Id_backup` : Identifiant de sauvegarde (clé primaire).

#### 1.10 **Internship**
- **Table** : `Internship`
- **Description** : Contient les informations relatives aux stages.
- **Colonnes** :
  - `Internship_identifier` : Identifiant unique du stage (clé primaire).
  - `Company_name` : Nom de l'entreprise dans lequel se passe le stage.
  - `Keywords` : Mots-clés décrivant le stage.
  - `Start_date_internship` : Date de début du stage.
  - `End_date_internship` : Date de fin du stage.
  - `Internship_subject` : Sujet détaillé du stage.
  - `Address` : Adresse du stage.
  - `Student_number` : Identifiant de l'étudiant en stage (clé étrangère vers `Student`).
  - `Relevance_score` : Score de pertinence du stage par rapport à l'enseignant attribué.
  - `Id_teacher` : Identifiant de l'enseignant responsable du stage (clé étrangère vers `Teacher`).

#### 1.11 **Department**
- **Table** : `Department`
- **Description** : Contient les départements où les différents personnels et étudiants peuvent être affectés.
- **Colonnes** :
  - `Department_name` : Nom du département (clé primaire).
  - `Address` : Adresse du département (clé étrangère vers `Addr_name`).

#### 1.12 **Is_requested**
- **Table** : `Is_requested`
- **Description** : Indique les stages demandé par des enseignants.
- **Colonnes** :
  - `Id_teacher` : Identifiant de l'enseignant (clé étrangère vers `Teacher`).
  - `Internship_identifier` : Identifiant du stage (clé étrangère vers `Internship`).

#### 1.13 **Is_taught**
- **Table** : `Is_taught`
- **Description** : Contient les enseignements effectués par les enseignants dans chaque discipline.
- **Colonnes** :
  - `Id_teacher` : Identifiant de l'enseignant (clé étrangère vers `Teacher`).
  - `Discipline_name` : Nom de la discipline (clé étrangère vers `Discipline`).

#### 1.14 **Has_role**
- **Table** : `Has_role`
- **Description** : Contient les rôles des utilisateurs dans chaque département.
- **Colonnes** :
  - `User_id` : Identifiant de l'utilisateur (clé étrangère vers `User_connect`).
  - `Role_name` : Nom du rôle (clé étrangère vers `Role`).
  - `Department_name` : Nom du département (clé étrangère vers `Department`).

#### 1.15 **Study_at**
- **Table** : `Study_at`
- **Description** : Contient les affectations des étudiants aux départements.
- **Colonnes** :
  - `Student_number` : Identifiant de l'étudiant (clé étrangère vers `Student`).
  - `Department_name` : Nom du département (clé étrangère vers `Department`).

#### 1.16 **Has_address**
- **Table** : `Has_address`
- **Description** : Contient les adresses associées aux enseignants.
- **Colonnes** :
  - `Id_teacher` : Identifiant de l'enseignant (clé étrangère vers `Teacher`).
  - `Address` : Adresse de l'enseignant (clé étrangère vers `Addr_name`).
  - `Type` : Type d'adresse (clé étrangère vers `Address_type`).

#### 1.17 **Distance**
- **Table** : `Distance`
- **Description** : Contient les distances entre enseignants et stages.
- **Colonnes** :
  - `Id_teacher` : Identifiant de l'enseignant (clé étrangère vers `Teacher`).
  - `Internship_identifier` : Identifiant du stage (clé étrangère vers `Internship`).
  - `Distance` : Distance entre l'enseignant et le stage.

#### 1.18 **Backup**
- **Table** : `Backup`
- **Description** : Contient des informations sur les critères de sauvegarde pour les utilisateurs.
- **Colonnes** :
  - `User_id` : Identifiant de l'utilisateur (clé étrangère vers `User_connect`).
  - `Name_criteria` : Critère de sauvegarde (clé étrangère vers `Distribution_criteria`).
  - `Id_backup` : Identifiant de sauvegarde (clé étrangère vers `Id_backup`).
  - `Coef` : Coefficient associé au critère.
  - `Is_checked` : Statut de vérification de la sauvegarde.

---

### 2. **Triggers et Fonctions**

#### 2.1 **Trigger `check_is_requested_assignment`**
- **Objectif** : Empêcher l'ajout d'une demande de stage si un enseignant est déjà assigné à un stage.
- **Fonction** : Vérifie si un enseignant est déjà assigné à un stage. Si c'est le cas, une exception est levée.
- **Exécution** : Avant toute insertion dans la table `Is_requested`.

#### 2.2 **Trigger `check_internship_assignment`**
- **Objectif** : Supprimer les demandes de stage et les distances associées lorsqu'un enseignant est assigné à un stage.
- **Fonction** : Si un enseignant est assigné à un stage, les demandes de stage et distances associées sont supprimées.
- **Exécution** : Avant toute mise à jour ou insertion dans la table `Internship`.

#### 2.3 **Trigger `check_distance_assignment`**
- **Objectif** : Empêcher l'ajout d'une distance si un enseignant est déjà assigné au stage.
- **Fonction** : Vérifie si un enseignant est déjà assigné au stage. Si oui, l'ajout de la distance est empêché.
- **Exécution** : Avant toute insertion dans la table `Distance`.

#### 2.4 **Trigger `insert_backup`**
- **Objectif** : Insérer automatiquement des entrées de sauvegarde pour chaque nouvel utilisateur connecté.
- **Fonction** : Lors de l'insertion d'un utilisateur, des lignes sont ajoutées à la table `Backup` pour chaque critère de distribution et chaque identifiant de sauvegarde.
- **Exécution** : Après l'insertion dans la table `User_connect`.

#### 2.5 **Trigger `create_addr_for_insert_internship`**
- **Objectif** : Créer automatiquement une nouvelle entrée dans la table `Addr_name` si l'adresse du stage n'existe pas déjà.
- **Fonction** : Vérifie si l'adresse existe, et l'insère dans la table `Addr_name` si ce n'est pas le cas.
- **Exécution** : Avant l'insertion dans la table `Internship`.

---
