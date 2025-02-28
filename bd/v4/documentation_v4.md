### Documentation des tables de la base de données ###  
### Schéma MLD

![Image Schéma MLD](https://i.imgur.com/1Yhr68G.png)

---

### 1. **Tables**

#### 1.1 **Teacher**
- **Table** : `Teacher`
- **Description** : Contient les informations des enseignants.
- **Colonnes** :
  - `Id_teacher` (`VARCHAR(10)`) : Identifiant unique de l'enseignant (clé primaire)(ce numéro est nécessairement un `user_id` permettant à l'enseignant d'accéder au site).
  - `Teacher_name` (`VARCHAR(50)`) : Nom de l'enseignant.
  - `Teacher_firstname` (`VARCHAR(50)`) : Prénom de l'enseignant.
  - `Maxi_number_intern` (`INT`) : Nombre maximum d'alternant qu'un enseignant peut superviser.
  - `Maxi_number_apprentice` (`INT`) : Nombre maximum de stagiaires qu'un enseignant peut superviser.

#### 1.2 **Student**
- **Table** : `Student`
- **Description** : Contient les informations des étudiants.
- **Colonnes** :
  - `Student_number` (`VARCHAR(10)`) : Numéro d'identification unique de l'étudiant (clé primaire).
  - `Student_name` (`VARCHAR(50)`, NOT NULL) : Nom de l'étudiant.
  - `Student_firstname` (`VARCHAR(50)`, NOT NULL) : Prénom de l'étudiant.
  - `Formation` (`VARCHAR(50)`) : Formation de l'étudiant.
  - `Class_group` (`VARCHAR(50)`) : Groupe de classe de l'étudiant.

#### 1.3 **Discipline**
- **Table** : `Discipline`
- **Description** : Contient les disciplines enseignées par les enseignants.
- **Colonnes** :
  - `Discipline_name` (`VARCHAR(50)`) : Nom de la discipline (clé primaire).

#### 1.4 **User_connect**
- **Table** : `User_connect`
- **Description** : Contient les informations d'authentification des utilisateurs.
- **Colonnes** :
  - `User_id` (`VARCHAR(10)`) : Identifiant unique de l'utilisateur (clé primaire).
  - `User_pass` (`VARCHAR(100)`) : Mot de passe de l'utilisateur.

#### 1.5 **Role**
- **Table** : `Role`
- **Description** : Contient les rôles attribués aux utilisateurs.
- **Colonnes** :
  - `Role_name` (`VARCHAR(50)`) : Nom du rôle (clé primaire).

#### 1.6 **Distribution_criteria**
- **Table** : `Distribution_criteria`
- **Description** : Contient les critères utilisés pour la distribution des stages.
- **Colonnes** :
  - `Name_criteria` (`VARCHAR(50)`) : Nom du critère (clé primaire).
  - `Description` (`VARCHAR(500)`, NOT NULL) : Description du critère.

#### 1.7 **Addr_name**
- **Table** : `Addr_name`
- **Description** : Contient les adresses.
- **Colonnes** :
  - `Address` (`VARCHAR(100)`) : Adresse (clé primaire).

#### 1.8 **Address_type**
- **Table** : `Address_type`
- **Description** : Contient les types d'adresses.
- **Colonnes** :
  - `Type` (`VARCHAR(50)`) : Type de l'adresse (clé primaire).

#### 1.9 **Id_backup**
- **Table** : `Id_backup`
- **Description** : Contient des identifiants pour les sauvegardes.
- **Colonnes** :
  - `Id_backup` (`INT`) : Identifiant de sauvegarde (clé primaire).

#### 1.10 **Internship**
- **Table** : `Internship`
- **Description** : Contient les informations relatives aux stages.
- **Colonnes** :
  - `Internship_identifier` (`VARCHAR(20)`) : Identifiant unique du stage (clé primaire).
  - `Company_name` (`VARCHAR(50)`, NOT NULL) : Nom de l'entreprise où se passe le stage.
  - `Keywords` (`VARCHAR(200)`) : Mots-clés décrivant le stage.
  - `Start_date_internship` (`DATE`, NOT NULL) : Date de début du stage.
  - `Type` (`VARCHAR(50)`) : Type du stage.
  - `End_date_internship` (`DATE`, NOT NULL) : Date de fin du stage.
  - `Internship_subject` (`VARCHAR(150)`, NOT NULL) : Sujet détaillé du stage.
  - `Address` (`VARCHAR(100)`, NOT NULL) : Adresse du stage.
  - `Student_number` (`VARCHAR(10)`, NOT NULL) : Identifiant de l'étudiant en stage.
  - `Relevance_score` (`FLOAT`) : Score de pertinence du stage par rapport à l'enseignant.
  - `Id_teacher` (`VARCHAR(10)`) : Identifiant de l'enseignant responsable du stage.


#### 1.11 **Department**
- **Table** : `Department`
- **Description** : Contient les informations sur les départements.
- **Colonnes** :
  - `Department_name` (`VARCHAR(50)`) : Nom unique du département (clé primaire).
  - `Address` (`VARCHAR(100)`, NOT NULL) : Adresse associée au département.

#### 1.12 **Is_requested**
- **Table** : `Is_requested`
- **Description** : Relie les stages demandés à des enseignants spécifiques.
- **Colonnes** :
  - `Id_teacher` (`VARCHAR(10)`) : Identifiant de l'enseignant (clé étrangère).
  - `Internship_identifier` (`VARCHAR(50)`) : Identifiant du stage (clé étrangère).

#### 1.13 **Is_taught**
- **Table** : `Is_taught`
- **Description** : Associe les disciplines enseignées aux enseignants.
- **Colonnes** :
  - `Id_teacher` (`VARCHAR(10)`) : Identifiant de l'enseignant (clé étrangère).
  - `Discipline_name` (`VARCHAR(50)`) : Nom de la discipline (clé étrangère).

#### 1.14 **Has_role**
- **Table** : `Has_role`
- **Description** : Attribue des rôles spécifiques aux utilisateurs.
- **Colonnes** :
  - `User_id` (`VARCHAR(10)`) : Identifiant de l'utilisateur (clé étrangère).
  - `Role_name` (`VARCHAR(50)`, NOT NULL) : Nom du rôle (clé étrangère).
  - `Department_name` (`VARCHAR(50)`, NOT NULL) : Département associé au rôle.

#### 1.15 **Study_at**
- **Table** : `Study_at`
- **Description** : Associe les étudiants à leurs départements respectifs.
- **Colonnes** :
  - `Student_number` (`VARCHAR(10)`) : Identifiant de l'étudiant (clé étrangère).
  - `Department_name` (`VARCHAR(50)`) : Nom du département (clé étrangère).

#### 1.16 **Has_address**
- **Table** : `Has_address`
- **Description** : Associe des adresses et types d'adresses à un enseignant.
- **Colonnes** :
  - `Id_teacher` (`VARCHAR(10)`) : Identifiant de l'enseignant (clé étrangère).
  - `Address` (`VARCHAR(100)`) : Adresse associée (clé étrangère).
  - `Type` (`VARCHAR(50)`) : Type d'adresse (clé étrangère).

#### 1.17 **Distance**
- **Table** : `Distance`
- **Description** : Indique la distance entre un enseignant et un stage.
- **Colonnes** :
  - `Id_teacher` (`VARCHAR(10)`) : Identifiant de l'enseignant (clé étrangère).
  - `Internship_identifier` (`VARCHAR(20)`) : Identifiant du stage (clé étrangère).
  - `Distance` (`INT`) : Distance mesurée en kilomètres.

#### 1.18 **Backup**
- **Table** : `Backup`
- **Description** : Contient les coefficients et sauvegardes associés à des critères de distribution.
- **Colonnes** :
  - `User_id` (`VARCHAR(10)`) : Identifiant de l'utilisateur (clé étrangère).
  - `Name_criteria` (`VARCHAR(50)`) : Nom du critère (clé étrangère).
  - `Id_backup` (`INT`) : Identifiant de sauvegarde (clé étrangère).
  - `Coef` (`INT`) : Valeur du coefficient associé.
  - `Name_save` (`VARCHAR(100)`) : Nom de la sauvegarde.
  - `Is_checked` (`BOOLEAN`, défaut : `TRUE`) : Indique si le critère est activé.

---

### 2. **Triggers et Fonctions**

#### 2.1 **check_is_requested_assignment**
- **Fonction** : Vérifie qu’un stage n’a pas déjà un enseignant assigné avant d’ajouter une demande (`Is_requested`).
- **Trigger** :
  - Type : `BEFORE INSERT`
  - Table : `Is_requested`

```sql
CREATE OR REPLACE FUNCTION check_is_requested_assignment()
RETURNS TRIGGER AS $$
BEGIN
    IF EXISTS (
        SELECT 1 FROM Internship 
        WHERE Internship_identifier = NEW.Internship_identifier AND Id_teacher IS NOT NULL
    ) THEN
        RAISE EXCEPTION 'Un professeur est déjà assigné à ce stage';
    END IF;
    RETURN NEW;
END;
$$ LANGUAGE plpgsql;

CREATE TRIGGER check_Is_requested_assignment
    BEFORE INSERT ON Is_requested
    FOR EACH ROW
    EXECUTE FUNCTION check_is_requested_assignment();
```

#### 2.2 **check_internship_assignment**
- **Fonction** : Supprime les relations dans `Is_requested` et `Distance` lorsqu’un enseignant est assigné à un stage.
- **Trigger** :
  - Type : `BEFORE UPDATE OR INSERT`
  - Table : `Internship`

```sql
CREATE OR REPLACE FUNCTION check_internship_assignment()
RETURNS TRIGGER AS $$
BEGIN
    IF NEW.Id_teacher IS NOT NULL THEN
        DELETE FROM Is_requested WHERE Internship_identifier = NEW.Internship_identifier;
        DELETE FROM Distance WHERE Internship_identifier = NEW.Internship_identifier;
    END IF;
    RETURN NEW;
END;
$$ LANGUAGE plpgsql;

CREATE TRIGGER check_internship_assignment
    BEFORE UPDATE OR INSERT ON Internship
    FOR EACH ROW
    EXECUTE FUNCTION check_internship_assignment();
```

#### 2.3 **create_addr_for_insert**
- **Fonction** : Ajoute une adresse dans `Addr_name` si elle n’existe pas avant un `INSERT`.
- **Trigger** :
  - Type : `BEFORE INSERT`
  - Tables : `Internship`, `Has_address`

```sql
CREATE OR REPLACE FUNCTION create_addr_for_insert()
RETURNS TRIGGER AS $$
BEGIN
    IF (SELECT Address FROM Addr_name WHERE Address = NEW.Address) IS NULL THEN
        INSERT INTO Addr_name (Address) VALUES (NEW.Address);
    END IF;
    RETURN NEW;
END;
$$ LANGUAGE plpgsql;

CREATE TRIGGER create_addr_for_insert_internship
    BEFORE INSERT ON Internship
    FOR EACH ROW
    EXECUTE FUNCTION create_addr_for_insert();

CREATE TRIGGER create_addr_for_insert_has_address
    BEFORE INSERT ON Has_address
    FOR EACH ROW
    EXECUTE FUNCTION create_addr_for_insert();
```

---

#### 2.4 **check_distance_assignment**
- **Fonction** : Vérifie qu’un stage n’a pas déjà un enseignant assigné avant d’ajouter une distance à `Distance`.
- **Trigger** :
  - Type : `BEFORE INSERT`
  - Table : `Distance`

```sql
CREATE OR REPLACE FUNCTION check_distance_assignment()
RETURNS TRIGGER AS $$
BEGIN
    IF (SELECT Id_teacher FROM Internship WHERE Internship_identifier = NEW.Internship_identifier) IS NULL THEN
        RETURN NEW;
    ELSE
        RAISE EXCEPTION 'Un professeur est déjà assigné à ce stage';
    END IF;
END;
$$ LANGUAGE plpgsql;

CREATE TRIGGER check_distance_assignment
    BEFORE INSERT ON Distance
    FOR EACH ROW
    EXECUTE FUNCTION check_distance_assignment();
```

#### 2.5 **update_backup_new_criteria**
- **Fonction** : Met à jour `Backup` avec de nouveaux critères ajoutés à `Distribution_criteria`.
- **Trigger** :
  - Type : `AFTER INSERT`
  - Table : `Distribution_criteria`

```sql
CREATE OR REPLACE FUNCTION update_backup_new_criteria()
RETURNS TRIGGER AS $$
DECLARE
    user_id TEXT;
    id_backup INT;
BEGIN
    FOR user_id IN SELECT User_id FROM User_connect LOOP
        FOR id_backup IN SELECT Id_backup FROM Id_backup LOOP
            INSERT INTO Backup (User_id, Name_criteria, Id_backup, Coef, Is_checked)
            VALUES (user_id, NEW.Name_criteria, id_backup, 1, FALSE);
        END LOOP;
    END LOOP;
    RETURN NEW;
END;
$$ LANGUAGE plpgsql;

CREATE TRIGGER update_backup_new_criteria
    AFTER INSERT ON Distribution_criteria
    FOR EACH ROW
    EXECUTE FUNCTION update_backup_new_criteria();
```

#### 2.6 **create_discipline_for_insert**
- **Fonction** : Ajoute une discipline dans `Discipline` si elle n’existe pas avant un `INSERT`.
- **Trigger** :
  - Type : `BEFORE INSERT`
  - Table : `Is_taught`

```sql
CREATE OR REPLACE FUNCTION create_discipline_for_insert()
RETURNS TRIGGER AS $$
BEGIN
    IF (SELECT Discipline_name FROM Discipline WHERE Discipline_name = NEW.Discipline_name) IS NULL THEN
        INSERT INTO Discipline (Discipline_name) VALUES (NEW.Discipline_name);
    END IF;
    RETURN NEW;
END;
$$ LANGUAGE plpgsql;

CREATE TRIGGER create_discipline_for_insert_is_taught
    BEFORE INSERT ON Is_taught
    FOR EACH ROW
    EXECUTE FUNCTION create_discipline_for_insert();
```

#### 2.7 **create_id_backup_for_insert**
- **Fonction** : Ajoute un identifiant de sauvegarde à `Id_backup` si inexistant avant un `INSERT`.
- **Trigger** :
  - Type : `BEFORE INSERT`
  - Table : `Backup`

```sql
CREATE OR REPLACE FUNCTION create_id_backup_for_insert()
RETURNS TRIGGER AS $$
BEGIN
    IF (SELECT Id_backup FROM Id_backup WHERE Id_backup = NEW.Id_backup) IS NULL THEN
        INSERT INTO Id_backup (Id_backup) VALUES (NEW.Id_backup);
    END IF;
    RETURN NEW;
END;
$$ LANGUAGE plpgsql;

CREATE TRIGGER create_id_backup_for_insert_Backup
    BEFORE INSERT ON Backup
    FOR EACH ROW
    EXECUTE FUNCTION create_id_backup_for_insert();
```

---

