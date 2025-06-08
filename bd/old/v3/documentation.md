### Documentation des tables de la base de données
### Schéma MLD
![Schéma MLD](https://i.imgur.com/bXoBDWg.png)
#### Table : `Teacher`
Cette table contient les informations relatives aux enseignants.

- **Id_teacher** : Identifiant de l'enseignant (VARCHAR(10)) - Clé primaire.
- **Teacher_name** : Nom de l'enseignant (VARCHAR(50)).
- **Teacher_firstname** : Prénom de l'enseignant (VARCHAR(50)).
- **Maxi_number_trainees** : Nombre maximal de stagiaires que l'enseignant peut superviser (INT).

#### Table : `Student`
Cette table contient les informations relatives aux étudiants.

- **Student_number** : Numéro de l'étudiant (VARCHAR(10)) - Clé primaire.
- **Student_name** : Nom de l'étudiant (VARCHAR(50), NOT NULL).
- **Student_firstname** : Prénom de l'étudiant (VARCHAR(50), NOT NULL).
- **Formation** : Formation suivie par l'étudiant (VARCHAR(50), NOT NULL).
- **Class_group** : Groupe de classe de l'étudiant (VARCHAR(50), NOT NULL).

#### Table : `Discipline`
Cette table répertorie les différentes disciplines enseignées.

- **Discipline_name** : Nom de la discipline (VARCHAR(50)) - Clé primaire.

#### Table : `User_connect`
Cette table contient les informations de connexion des utilisateurs.

- **User_id** : Identifiant de l'utilisateur (peut correspondre avec un **Id_teacher**) (VARCHAR(10)) - Clé primaire.
- **User_pass** : Mot de passe de l'utilisateur (VARCHAR(100)).

#### Table : `Role`
Cette table stocke les différents rôles possibles (Enseignant, Admin, Super_admin,...).

- **Role_name** : Nom du rôle (VARCHAR(50)) - Clé primaire.

#### Table : `Critere_repartiteur`
Cette table contient les différents critères utilisables dans la répartition des tuteurs.

- **Name_criteria** : Nom du critère (VARCHAR(50)) - Clé primaire.

#### Table : `Addr_name`
Cette table stocke toute les différentes adresses.

- **Address** : Adresse complète (VARCHAR(100)) - Clé primaire.

#### Table : `Address_type`
Cette table contient les types d'adresses utilisé pour les enseigants (Domicile_1, Domicile_2,...).

- **Type** : Type d'adresse (VARCHAR(50)) - Clé primaire.

#### Table : `Coef`
Cette table stocke les coefficients utilisés dans les critères. ***???***

- **Coef** : Valeur du coefficient (DECIMAL(6,2)) - Clé primaire.

#### Table : `Internship`
Cette table contient les informations relatives aux stages.

- **Internship_identifier** : Identifiant unique du stage (VARCHAR(50)) - Clé primaire. ***serial ???***
- **Company_name** : Nom de l'entreprise où se déroule le stage (VARCHAR(50), NOT NULL).
- **keywords** : Mots-clés liés au stage pour la recherche par correspondance (VARCHAR(200)).
- **Start_date_internship** : Date de début du stage (DATE, NOT NULL).
- **Type** : Type de stage (VARCHAR(50)). ***ajout d'une contraint ou triggers ???***
- **End_date_internship** : Date de fin du stage (DATE, NOT NULL).
- **Internship_subject** : Sujet du stage (VARCHAR(150), NOT NULL).
- **Address** : Adresse de l'entreprise (VARCHAR(100), NOT NULL) - Référence à `Addr_name`.
- **Student_number** : Numéro de l'étudiant qui réalise le stage (VARCHAR(10), NOT NULL) - Référence à `Student`.

#### Table : `Department`
Cette table contient les informations relatives aux départements.

- **Department_name** : Nom du département (VARCHAR(50)) - Clé primaire.
- **Address** : Adresse du département (VARCHAR(100), NOT NULL) - Référence à `Addr_name`.

#### Table : `Is_responsible`
Cette table relie un enseignant responsable à un étudiant.

- **Id_teacher** : Identifiant de l'enseignant (VARCHAR(10)) - Clé primaire.
- **Student_number** : Numéro de l'étudiant (VARCHAR(10)) - Clé primaire.
- **Distance_minute** : Distance (en minutes) entre l'enseignant et l'étudiant (INT, NOT NULL).
- **Relevance_score** : Score de pertinence (INT, NOT NULL).
- **Responsible_start_date** : Date de début de la responsabilité (DATE, NOT NULL).
- **Responsible_end_date** : Date de fin de la responsabilité (DATE, NOT NULL).

#### Table : `Is_requested`
Cette table enregistre les demandes des enseignants pour avoir un étudiant spécifique.

- **Id_teacher** : Identifiant de l'enseignant (VARCHAR(10)) - Clé primaire.
- **Student_number** : Numéro de l'étudiant (VARCHAR(10)) - Clé primaire.

#### Table : `Teaches`
Cette table relie un enseignant à un département où il enseigne.

- **Id_teacher** : Identifiant de l'enseignant (VARCHAR(10)) - Clé primaire.
- **Department_name** : Nom du département (VARCHAR(50)) - Clé primaire.

#### Table : `Is_taught`
Cette table relie un enseignant à une discipline enseignée.

- **Id_teacher** : Identifiant de l'enseignant (VARCHAR(10)) - Clé primaire.
- **Discipline_name** : Nom de la discipline (VARCHAR(50)) - Clé primaire.

#### Table : `Has_role`
Cette table relie un utilisateur à un rôle et à un département.

- **User_id** : Identifiant de l'utilisateur (VARCHAR(10)) - Clé primaire.
- **Role_name** : Nom du rôle (VARCHAR(50)) - Clé primaire.
- **Role_department** : Département auquel le rôle est associé (VARCHAR(50)).

#### Table : `Study_at`
Cette table relie un étudiant à un département.

- **Student_number** : Numéro de l'étudiant (VARCHAR(10)) - Clé primaire.
- **Department_name** : Nom du département (VARCHAR(50)) - Clé primaire.

#### Table : `Has_address`
Cette table associe une adresse à un enseignant et à un type d'adresse.

- **Id_teacher** : Identifiant de l'enseignant (VARCHAR(10)) - Clé primaire.
- **Address** : Adresse de l'enseignant (VARCHAR(100)) - Clé primaire.
- **Type** : Type de l'adresse (VARCHAR(50)) - Clé primaire.

#### Table : `Backup`
Cette table associe un utilisateur à un critère et à un coefficient dans le cadre des sauvegardes.

- **User_id** : Identifiant de l'utilisateur (VARCHAR(10)) - Clé primaire.
- **Name_criteria** : Nom du critère (VARCHAR(50)) - Clé primaire.
- **Coef** : Coefficient associé (DECIMAL(6,2)) - Clé primaire.
- **Num_backup** : Numéro de la sauvegarde (VARCHAR(50), NOT NULL).