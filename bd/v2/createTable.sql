CREATE TABLE Enseignant(
   Id_enseignant VARCHAR(10),
   Adresse VARCHAR(100) NOT NULL,
   Nom_prof VARCHAR(50),
   Prenom_prof VARCHAR(50),
   Nombre_max_stagiaire INT,
   PRIMARY KEY(Id_enseignant)
);

CREATE TABLE Eleve(
   Num_eleve VARCHAR(10),
   Nom_eleve VARCHAR(50) NOT NULL,
   Prenom_eleve VARCHAR(50) NOT NULL,
   Formation VARCHAR(50) NOT NULL,
   Groupe VARCHAR(50) NOT NULL,
   PRIMARY KEY(Num_eleve)
);

CREATE TABLE Stage(
   Id_stage VARCHAR(50),
   Nom_entreprise VARCHAR(50) NOT NULL,
   Adresse_entreprise VARCHAR(100) NOT NULL,
   Mots_cles VARCHAR(200),
   Date_debut DATE NOT NULL,
   Type VARCHAR(50),
   Date_fin DATE NOT NULL,
   Sujet_stage VARCHAR(150) NOT NULL,
   Num_eleve VARCHAR(10) NOT NULL,
   PRIMARY KEY(Id_stage),
   FOREIGN KEY(Num_eleve) REFERENCES Eleve(Num_eleve)
);

CREATE TABLE Departement(
   Nom_departement VARCHAR(50),
   PRIMARY KEY(Nom_departement)
);

CREATE TABLE Discipline(
   Nom_discipline VARCHAR(50),
   PRIMARY KEY(Nom_discipline)
);

CREATE TABLE Utilisateur(
   Id_user VARCHAR(10),
   Mdp_user VARCHAR(100),
   PRIMARY KEY(Id_user)
);

CREATE TABLE Role(
   Nom_role VARCHAR(50),
   PRIMARY KEY(Nom_role)
);

CREATE TABLE Citere_rapartiteur(
   Nom_critere VARCHAR(50),
   PRIMARY KEY(Nom_critere)
);

CREATE TABLE Est_responsable(
   Id_enseignant VARCHAR(10),
   Num_eleve VARCHAR(10),
   Distance_minute INT NOT NULL,
   Score_pertinence INT NOT NULL,
   Date_debut_resp DATE NOT NULL,
   Date_fin_resp DATE NOT NULL,
   PRIMARY KEY(Id_enseignant, Num_eleve),
   FOREIGN KEY(Id_enseignant) REFERENCES Enseignant(Id_enseignant),
   FOREIGN KEY(Num_eleve) REFERENCES Eleve(Num_eleve)
);

CREATE TABLE Est_demande(
   Id_enseignant VARCHAR(10),
   Num_eleve VARCHAR(10),
   PRIMARY KEY(Id_enseignant, Num_eleve),
   FOREIGN KEY(Id_enseignant) REFERENCES Enseignant(Id_enseignant),
   FOREIGN KEY(Num_eleve) REFERENCES Eleve(Num_eleve)
);

CREATE TABLE Enseigne_a(
   Id_enseignant VARCHAR(10),
   Nom_departement VARCHAR(50),
   PRIMARY KEY(Id_enseignant, Nom_departement),
   FOREIGN KEY(Id_enseignant) REFERENCES Enseignant(Id_enseignant),
   FOREIGN KEY(Nom_departement) REFERENCES Departement(Nom_departement)
);

CREATE TABLE Est_enseigne(
   Id_enseignant VARCHAR(10),
   Nom_discipline VARCHAR(50),
   PRIMARY KEY(Id_enseignant, Nom_discipline),
   FOREIGN KEY(Id_enseignant) REFERENCES Enseignant(Id_enseignant),
   FOREIGN KEY(Nom_discipline) REFERENCES Discipline(Nom_discipline)
);

CREATE TABLE a_role(
   Id_user VARCHAR(10),
   Nom_role VARCHAR(50),
   PRIMARY KEY(Id_user, Nom_role),
   FOREIGN KEY(Id_user) REFERENCES Utilisateur(Id_user),
   FOREIGN KEY(Nom_role) REFERENCES Role(Nom_role)
);

CREATE TABLE Etudie_a(
   Num_eleve VARCHAR(10),
   Nom_departement VARCHAR(50),
   PRIMARY KEY(Num_eleve, Nom_departement),
   FOREIGN KEY(Num_eleve) REFERENCES Eleve(Num_eleve),
   FOREIGN KEY(Nom_departement) REFERENCES Departement(Nom_departement)
);
