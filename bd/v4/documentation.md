### Documentation des tables de la base de données
### Schéma MLD
![Schéma MLD](https://cdn.discordapp.com/attachments/1292735811959394304/1316065627433340990/image.png?ex=675a59e0&is=67590860&hm=b127508a52274f68b45514b3c30deb16a705ce6df92f010e1eca6a9ca5f9ff8b&)

---

### **Tables**

#### **0. Compagny**
- **Description :** Cette table n'est pour l'instant pas implémenté.


#### **1. Teacher**

- **Description :** Cette table contient les informations relatives aux enseignants.
- **Colonnes :**
    - `Id_teacher` : Identifiant unique pour chaque enseignant (Clé primaire).
    - `Teacher_name` : Nom de l'enseignant.
    - `Teacher_firstname` : Prénom de l'enseignant.
    - `Maxi_number_trainees` : Nombre maximal de stagiaires que l'enseignant peut superviser.

---

#### **2. Student**

- **Description :** Cette table contient les informations relatives aux étudiants.
- **Colonnes :**
    - `Student_number` : Identifiant unique pour chaque étudiant (Clé primaire).
    - `Student_name` : Nom de l'étudiant (obligatoire).
    - `Student_firstname` : Prénom de l'étudiant (obligatoire).
    - `Formation` : Formation de l'étudiant.
    - `Class_group` : Groupe de classe de l'étudiant.

---

#### **3. Discipline**

- **Description :** Cette table contient les disciplines (matières) disponibles.
- **Colonnes :**
    - `Discipline_name` : Nom de la discipline (Clé primaire).

---

#### **4. User_connect**

- **Description :** Cette table contient les informations de connexion des utilisateurs.
- **Colonnes :**
    - `User_id` : Identifiant unique de l'utilisateur, peut être un `Teacher_id`(Clé primaire).
    - `User_pass` : Mot de passe de l'utilisateur.

---

#### **5. Role**

- **Description :** Cette table contient les rôles d'utilisateurs dans le système.
- **Colonnes :**
    - `Role_name` : Nom du rôle (Clé primaire).

---

#### **6. Distribution_criteria**

- **Description :** Cette table contient les critères de distribution des rôles ou autres entités.
- **Colonnes :**
    - `Name_criteria` : Nom du critère (Clé primaire).

---

#### **7. Addr_name**

- **Description :** Cette table contient les adresses.
- **Colonnes :**
    - `Address` : Adresse (Clé primaire).

---

#### **8. Address_type**

- **Description :** Cette table contient les types d'adresses (par exemple, "domicile", "bureaux").
- **Colonnes :**
    - `Type` : Type d'adresse (Clé primaire).

---

#### **9. Id_backup**

- **Description :** Cette table contient des informations de sauvegarde.
- **Colonnes :**
    - `Id_backup` : Identifiant unique pour chaque sauvegarde (Clé primaire).

---

#### **10. Internship**

- **Description :** Cette table contient les informations sur les stages.
- **Colonnes :**
    - `Internship_identifier` : Identifiant unique pour chaque stage (Clé primaire).
    - `Company_name` : Nom de l'entreprise où se déroule le stage (obligatoire).
    - `Keywords` : Mots-clés associés au stage.
    - `Start_date_internship` : Date de début du stage (obligatoire).
    - `Type` : Type de stage.
    - `End_date_internship` : Date de fin du stage (obligatoire).
    - `Internship_subject` : Sujet du stage (obligatoire).
    - `Address` : Adresse du lieu de stage (obligatoire).
    - `Student_number` : Numéro d'étudiant associé au stage (obligatoire).
    - `Relevance_score` : Score de pertinence du stage.
    - `Responsible_start_date` : Date de début de la responsabilité pour le stage.
    - `Responsible_end_date` : Date de fin de la responsabilité pour le stage.
    - `Id_teacher` : Identifiant de l'enseignant supervisant le stage (Clé étrangère vers `Teacher`).

---

#### **11. Department**

- **Description :** Cette table contient les départements d'enseignement.
- **Colonnes :**
    - `Department_name` : Nom du département (Clé primaire).
    - `Address` : Adresse du département (Clé étrangère vers `Addr_name`).

---

#### **12. Is_requested**

- **Description :** Cette table enregistre les demandes de stage par les enseignants pour les étudiants.
- **Colonnes :**
    - `Id_teacher` : Identifiant de l'enseignant (Clé primaire, clé étrangère vers `Teacher`).
    - `Student_number` : Numéro d'étudiant (Clé primaire, clé étrangère vers `Student`).

---

#### **13. Is_taught**

- **Description :** Cette table indique quelles disciplines sont enseignées par quel enseignant.
- **Colonnes :**
    - `Id_teacher` : Identifiant de l'enseignant (Clé primaire, clé étrangère vers `Teacher`).
    - `Discipline_name` : Nom de la discipline (Clé primaire, clé étrangère vers `Discipline`).

---

#### **14. Has_role**

- **Description :** Cette table assigne des rôles aux utilisateurs dans les départements.
- **Colonnes :**
    - `User_id` : Identifiant de l'utilisateur (Clé primaire, clé étrangère vers `User_connect`).
    - `Role_name` : Nom du rôle (Clé primaire, clé étrangère vers `Role`).
    - `Department_name` : Nom du département (Clé primaire, clé étrangère vers `Department`).

---

#### **15. Study_at**

- **Description :** Cette table associe les étudiants aux départements dans lesquels ils étudient.
- **Colonnes :**
    - `Student_number` : Numéro d'étudiant (Clé primaire, clé étrangère vers `Student`).
    - `Department_name` : Nom du département (Clé primaire, clé étrangère vers `Department`).

---

#### **16. Has_address**

- **Description :** Cette table assigne des adresses aux enseignants avec un type d'adresse.
- **Colonnes :**
    - `Id_teacher` : Identifiant de l'enseignant (Clé primaire, clé étrangère vers `Teacher`).
    - `Address` : Adresse (Clé primaire, clé étrangère vers `Addr_name`).
    - `Type` : Type d'adresse (Clé primaire, clé étrangère vers `Address_type`).

---

#### **17. Distance**

- **Description :** Cette table contient les informations sur les distances entre un enseignant et un stage.
- **Colonnes :**
    - `Id_teacher` : Identifiant de l'enseignant (Clé primaire, clé étrangère vers `Teacher`).
    - `Internship_identifier` : Identifiant du stage (Clé primaire, clé étrangère vers `Internship`).
    - `Distance` : Distance entre l'enseignant et le stage.

---

#### **18. Backup**

- **Description :** Cette table contient les informations de sauvegarde liées aux critères de distribution.
- **Colonnes :**
    - `User_id` : Identifiant de l'utilisateur (Clé primaire, clé étrangère vers `User_connect`).
    - `Name_criteria` : Nom du critère (Clé primaire, clé étrangère vers `Distribution_criteria`).
    - `Id_backup` : Identifiant de la sauvegarde (Clé primaire, clé étrangère vers `Id_backup`).
    - `Coef` : Coefficient associé à la sauvegarde.

---

### **Fonctions et Triggers**

#### **1. Fonction `check_is_requested_assignment`**

- **Description :** Vérifie si un stage a déjà été attribué à un enseignant avant qu'un enseignant puisse en faire la demande. Si le stage est déjà attribué, une exception est levée.
- **Syntaxe :**
  ```sql
  CREATE OR REPLACE FUNCTION check_is_requested_assignment()
  RETURNS TRIGGER AS $$
      BEGIN
          IF EXISTS (SELECT 1 FROM Internship WHERE Internship_identifier = NEW.Internship_identifier AND Id_teacher IS NOT NULL) THEN
              RAISE EXCEPTION 'This internship is already assigned and can no longer be requested.';
          END IF;
          RETURN NEW;
      END;
  $$ LANGUAGE plpgsql;
  ```
- **Déclencheur (Trigger) :**
    - **Nom :** `check_Is_requested_assignment`
    - **Événement :** Avant un `INSERT` dans la table `Is_requested`.
    - **Action :** Exécute la fonction `check_is_requested_assignment()`.

#### **2. Fonction `check_internship_assignment`**

- **Description :** Lors de la mise à jour d'un stage, si un enseignant est attribué à ce stage (`Id_teacher` non nul), cette fonction supprime les demandes de stage pour ce stage dans la table `Is_requested`.
- **Syntaxe :**
  ```sql
  CREATE OR REPLACE FUNCTION check_internship_assignment()
  RETURNS TRIGGER AS $$
      BEGIN
          IF NEW.Id_teacher IS NOT NULL THEN
              DELETE FROM Is_requested WHERE Internship_identifier = NEW.Internship_identifier;
          END IF;
          RETURN NEW;
      END;
  $$ LANGUAGE plpgsql;
  ```
- **Déclencheur (Trigger) :**
    - **Nom :** `check_internship_assignment`
    - **Événement :** Avant une `UPDATE` sur la table `Internship`.
    - **Action :** Exécute la fonction `check_internship_assignment()`.



