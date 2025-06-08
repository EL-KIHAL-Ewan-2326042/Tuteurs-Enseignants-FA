DROP TABLE IF EXISTS backup;
DROP TABLE IF EXISTS Distance;
DROP TABLE IF EXISTS Has_address;
DROP TABLE IF EXISTS Study_at;
DROP TABLE IF EXISTS Has_role;
DROP TABLE IF EXISTS Is_taught;
DROP TABLE IF EXISTS Is_requested;
DROP TABLE IF EXISTS Department;
DROP TABLE IF EXISTS Internship;
DROP TABLE IF EXISTS Id_backup;
DROP TABLE IF EXISTS Address_type;
DROP TABLE IF EXISTS Addr_name;
DROP TABLE IF EXISTS Distribution_criteria;
DROP TABLE IF EXISTS Role;
DROP TABLE IF EXISTS User_connect;
DROP TABLE IF EXISTS Discipline;
DROP TABLE IF EXISTS Student;
DROP TABLE IF EXISTS Teacher;


-- Création des tables 

CREATE TABLE Teacher(
    Id_teacher TEXT,
    Teacher_mail TEXT,
    Teacher_name TEXT,
    Teacher_firstname TEXT,
    Maxi_number_intern INT,
    Maxi_number_apprentice INT,
    PRIMARY KEY(Id_teacher)

);

CREATE TABLE Student(
    Student_number TEXT,
    Student_mail TEXT,
    Student_name TEXT NOT NULL,
    Student_firstname TEXT NOT NULL,
    Formation TEXT,
    Class_group TEXT,
    PRIMARY KEY(Student_number)
);

CREATE TABLE Discipline(
    Discipline_name TEXT,
    PRIMARY KEY(Discipline_name)
);

CREATE TABLE User_connect(
    User_id TEXT,
    User_pass TEXT,
    PRIMARY KEY(User_id)
);

CREATE TABLE Role(
    Role_name TEXT,
    Role_full_name TEXT,
    PRIMARY KEY(Role_name)
);

CREATE TABLE Distribution_criteria(
    Name_criteria TEXT,
    Description TEXT NOT NULL,
    PRIMARY KEY(Name_criteria)
);

CREATE TABLE Addr_name(
    Address TEXT,
    PRIMARY KEY(Address)
);

CREATE TABLE Address_type(
    Type TEXT,
    Type_complet TEXT,
    PRIMARY KEY(Type)
);

CREATE TABLE Id_backup(
    Id_backup INT,
    PRIMARY KEY(Id_backup)
);

CREATE TABLE Internship(
    Internship_identifier TEXT,
    Company_name TEXT NOT NULL,
    Keywords TEXT[],
    Start_date_internship DATE NOT NULL,
    Type TEXT,
    End_date_internship DATE NOT NULL,
    Internship_subject TEXT NOT NULL,
    Address TEXT NOT NULL,
    Student_number TEXT NOT NULL,
    Relevance_score FLOAT,
    Id_teacher TEXT,
    PRIMARY KEY(Internship_identifier),
    FOREIGN KEY(Id_teacher) REFERENCES Teacher(Id_teacher),
    FOREIGN KEY(Address) REFERENCES Addr_name(Address),
    FOREIGN KEY(Student_number) REFERENCES Student(Student_number)
);

-- Création de la séquence pour les internships
CREATE SEQUENCE Internship_id_counter_seq
    INCREMENT 1
    START 1
    MINVALUE 1
    NO MAXVALUE
    CACHE 1;

CREATE TABLE Department(
    Department_name TEXT,
    Address TEXT NOT NULL,
    Department_full_name TEXT,
    PRIMARY KEY(Department_name),
    FOREIGN KEY(Address) REFERENCES Addr_name(Address)
);

CREATE TABLE Is_requested(
    Id_teacher TEXT,
    Internship_identifier TEXT,
    PRIMARY KEY(Id_teacher, Internship_identifier),
    FOREIGN KEY(Id_teacher) REFERENCES Teacher(Id_teacher),
    FOREIGN KEY(Internship_identifier) REFERENCES Internship(Internship_identifier)
);

CREATE TABLE Is_taught(
    Id_teacher TEXT,
    Discipline_name TEXT,
    PRIMARY KEY(Id_teacher, Discipline_name),
    FOREIGN KEY(Id_teacher) REFERENCES Teacher(Id_teacher),
    FOREIGN KEY(Discipline_name) REFERENCES Discipline(Discipline_name)
);

CREATE TABLE Has_role(
    User_id TEXT,
    Role_name TEXT NOT NULL,
    Department_name TEXT NOT NULL,
    PRIMARY KEY(User_id, Role_name, Department_name),
    FOREIGN KEY(User_id) REFERENCES User_connect(User_id),
    FOREIGN KEY(Role_name) REFERENCES Role(Role_name),
    FOREIGN KEY(Department_name) REFERENCES Department(Department_name)
);

CREATE TABLE Study_at(
    Student_number TEXT,
    Department_name TEXT,
    PRIMARY KEY(Student_number, Department_name),
    FOREIGN KEY(Student_number) REFERENCES Student(Student_number),
    FOREIGN KEY(Department_name) REFERENCES Department(Department_name)
);

CREATE TABLE Has_address(
    Id_teacher TEXT,
    Address TEXT,
    Type TEXT,
    PRIMARY KEY(Id_teacher, Address, Type),
    FOREIGN KEY(Id_teacher) REFERENCES Teacher(Id_teacher),
    FOREIGN KEY(Address) REFERENCES Addr_name(Address),
    FOREIGN KEY(Type) REFERENCES Address_type(Type)
);

CREATE TABLE Distance(
    Id_teacher TEXT,
    Internship_identifier TEXT,
    Distance INT,
    PRIMARY KEY(Id_teacher, Internship_identifier),
    FOREIGN KEY(Id_teacher) REFERENCES Teacher(Id_teacher),
    FOREIGN KEY(Internship_identifier) REFERENCES Internship(Internship_identifier)
);

CREATE TABLE backup (
    User_id TEXT,
    Name_criteria TEXT,
    Id_backup INT,
    Coef INT,
    Name_save TEXT,
    Is_checked BOOLEAN DEFAULT TRUE,
    PRIMARY KEY(User_id, Name_criteria, Id_backup),
    FOREIGN KEY(User_id) REFERENCES User_connect(User_id),
    FOREIGN KEY(Name_criteria) REFERENCES Distribution_criteria(Name_criteria),
    FOREIGN KEY(Id_backup) REFERENCES Id_backup(Id_backup)
);

-- Triggers

-- Retiré check_is_requested_assignment()

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

-- Trigger check_distance_assignment() supprimé

CREATE OR REPLACE FUNCTION create_addr_for_insert()
RETURNS TRIGGER AS $$
    BEGIN
        IF (SELECT Address FROM Addr_name WHERE Address = NEW.Address) IS NULL THEN
            INSERT INTO Addr_name (Address) VALUES (NEW.address);
        END IF;
        RETURN NEW;
    END;
$$ LANGUAGE plpgsql;

CREATE TRIGGER create_addr_for_insert_internship
    BEFORE INSERT ON Internship
    FOR EACH ROW
    EXECUTE FUNCTION create_addr_for_insert();


CREATE OR REPLACE FUNCTION update_backup_new_criteria()
RETURNS TRIGGER AS $$
    DECLARE
        user_id TEXT;
        id_backup integer;
    BEGIN
        FOR user_id IN SELECT User_connect.user_id FROM User_connect
            LOOP
            FOR id_backup IN SELECT Id_backup.id_backup FROM Id_backup
                LOOP
                INSERT INTO backup (user_id, name_Criteria, id_backup, coef, Is_checked) VALUES (user_id, NEW.name_criteria, id_backup, 1, False);
                END LOOP;
            END LOOP;
        RETURN NEW;
    END;
$$ LANGUAGE plpgsql;

CREATE TRIGGER update_backup_new_criteria
    AFTER INSERT ON Distribution_criteria
    FOR EACH ROW
    EXECUTE FUNCTION update_backup_new_criteria();


CREATE TRIGGER create_addr_for_insert_has_address
    BEFORE INSERT ON Has_address
    FOR EACH ROW
    EXECUTE FUNCTION create_addr_for_insert();


CREATE OR REPLACE FUNCTION create_discipline_for_insert()
RETURNS TRIGGER AS $$
    BEGIN
        IF (SELECT discipline_name FROM Discipline WHERE discipline_name = NEW.discipline_name) IS NULL THEN
            INSERT INTO Discipline (discipline_name) VALUES (NEW.discipline_name);
        END IF;
        RETURN NEW;
    END;
$$ LANGUAGE plpgsql;

CREATE TRIGGER create_discipline_for_insert_is_taught
    BEFORE INSERT ON Is_taught
    FOR EACH ROW
    EXECUTE FUNCTION create_discipline_for_insert();


CREATE OR REPLACE FUNCTION create_id_backup_for_insert()
RETURNS TRIGGER AS $$
    BEGIN
        IF (SELECT id_backup FROM id_backup WHERE id_backup.id_backup = NEW.id_backup) IS NULL THEN
            INSERT INTO id_backup (id_backup) VALUES (NEW.id_backup);
        END IF;
    RETURN NEW;
END;
$$ LANGUAGE plpgsql;

CREATE TRIGGER create_discipline_for_insert_is_taught
    BEFORE INSERT ON backup
    FOR EACH ROW
    EXECUTE FUNCTION create_id_backup_for_insert();

CREATE OR REPLACE FUNCTION update_backup_new_id_backup()
RETURNS TRIGGER AS $$
    DECLARE
        user_id TEXT;
        name_criteria TEXT;
    BEGIN
        FOR user_id IN SELECT user_connect.user_id FROM User_connect
            LOOP
            FOR name_criteria IN SELECT Distribution_criteria.name_criteria FROM Distribution_criteria
                LOOP
                INSERT INTO backup (user_id, name_Criteria, id_backup, coef) VALUES (user_id, name_criteria, NEW.id_backup, 1);
            END LOOP;
        END LOOP;
    RETURN NEW;
END;
$$ LANGUAGE plpgsql;

CREATE TRIGGER update_backup_new_id_backup
    AFTER INSERT ON Distribution_criteria
    FOR EACH ROW
    EXECUTE FUNCTION update_backup_new_id_backup();


CREATE OR REPLACE FUNCTION insert_backup()
RETURNS TRIGGER AS $$
    DECLARE
        name_criteria TEXT;
        id_backup integer;
    BEGIN
        FOR name_criteria IN SELECT Distribution_criteria.name_criteria FROM Distribution_criteria
            LOOP
            FOR id_backup IN SELECT Id_backup.id_backup FROM Id_backup
                LOOP
                INSERT INTO backup (user_id, name_Criteria, id_backup, coef) VALUES (NEW.user_id, name_criteria, id_backup, 1);
            END LOOP;
        END LOOP;
    RETURN NEW;
END;
$$ LANGUAGE plpgsql;

CREATE TRIGGER insert_backup
    AFTER INSERT ON user_connect
    FOR EACH ROW
    EXECUTE FUNCTION insert_backup();

-- Insertion des données

INSERT INTO Distribution_criteria VALUES ('A été responsable', 'Prendre en compte le fait que l''enseignant ait déjà travaillé avec l''élève');
INSERT INTO Distribution_criteria VALUES ('Distance', 'Prendre en compte la distance entre le lieu du stage et l''adresse renseignée la plus proche pour le responsable');
INSERT INTO Distribution_criteria VALUES ('Discipline', 'Prendre en compte la corrélation entre la matière enseignée par le responsable et le sujet du stage');
INSERT INTO Distribution_criteria VALUES ('Est demandé', 'Prendre en compte le fait que le responsable demande le stage');

INSERT INTO Teacher (id_teacher, teacher_mail, teacher_name, teacher_firstname, maxi_number_intern, maxi_number_apprentice) VALUES
('emilie.martin', 'emilie.martin@yopmail.com', 'Martin', 'Émilie', 3, 2),
('jean-luc.bernard', 'jean-luc.bernard@yopmail.com', 'Bernard', 'Jean-Luc', 6, 4),
('fatima.benali', 'fatima.benali@yopmail.com', 'Benali', 'Fatima Zohra', 9, 6),
('alexandre.dubois', 'alexandre.dubois@yopmail.com', 'Dubois', 'Alexandre', 6, 4),
('sofia.rossi', 'sofia.rossi@yopmail.com', 'Rossi', 'Sofia', 9, 6),
('laurent.moreau', 'laurent.moreau@yopmail.com', 'Moreau', 'Laurent', 3, 2),
('amina.kebir', 'amina.kebir@yopmail.com', 'Kébir', 'Amina', 9, 6),
('pierre-henri.blanc', 'pierre-henri.blanc@yopmail.com', 'Blanc', 'Pierre-Henri', 3, 2),
('clara.dupont', 'clara.dupont@yopmail.com', 'Dupont', 'Clara', 6, 4),
('youssef.el-mansouri', 'youssef.el-mansouri@yopmail.com', 'El-Mansouri', 'Youssef', 6, 4),
('lea.girard', 'lea.girard@yopmail.com', 'Girard', 'Léa', 3, 2),
('sergei.ivanov', 'sergei.ivanov@yopmail.com', 'Ivanov', 'Sergei', 6, 4),
('marie-laure.roux', 'marie-laure.roux@yopmail.com', 'Roux', 'Marie-Laure', 6, 4),
('mehdi.taha', 'mehdi.taha@yopmail.com', 'Taha', 'Mehdi', 6, 4),
('chloe.lefebvre', 'chloe.lefebvre@yopmail.com', 'Lefebvre', 'Chloé', 6, 4),
('marc.antoine', 'marc.antoine@yopmail.com', 'Antoine', 'Marc', 6, 4),
('nadia.cherif', 'nadia.cherif@yopmail.com', 'Cherif', 'Nadia', 6, 4),
('henri.delorme', 'henri.delorme@yopmail.com', 'Delorme', 'Henri', 6, 4),
('laura.costa', 'laura.costa@yopmail.com', 'Costa', 'Laura', 6, 4),
('francois-xavier.dupuis', 'francois-xavier.dupuis@yopmail.com', 'Dupuis', 'François-Xavier', 6, 4),
('samira.othmani', 'samira.othmani@yopmail.com', 'Othmani', 'Samira', 6, 4),
('philippe.marchand', 'philippe.marchand@yopmail.com', 'Marchand', 'Philippe', 6, 4),
('anais.petit', 'anais.petit@yopmail.com', 'Petit', 'Anaïs', 6, 4),
('lucas.royer', 'lucas.royer@yopmail.com', 'Royer', 'Lucas', 9, 6),
('helene.vidal', 'helene.vidal@yopmail.com', 'Vidal', 'Hélène', 6, 4);

INSERT INTO Student (student_number, student_mail, student_name, student_firstname, formation, class_group) VALUES 
('m22000001', 'lucas.moreau@yopmail.com', 'MOREAU', 'Lucas', 'BUT Info', 'A1-1_An2'),
('b22000002', 'amelie.benoit@yopmail.com', 'BENOIT', 'Amélie', 'BUT Info', 'A1-2_An2'),
('b22000003', 'karim.bensaid@yopmail.com', 'BENSAÏD', 'Karim', 'BUT Info', 'B-1_An2'),
('a12000004', 'lea.costa@yopmail.com', 'COSTA', 'Léa', 'BUT Info', 'A1-1_An2'),
('f22000005', 'marco.ferrari@yopmail.com', 'FERRARI', 'Marco', 'BUT Info', 'A1-1_An2'),
('e21000006', 'fatima.elamrani@yopmail.com', 'EL AMRANI', 'Fatima', 'BUT Info', 'A1-1_An3'),
('m21000007', 'theo.martin@yopmail.com', 'MARTIN', 'Théo', 'BUT Info', 'A2-1_An3'),
('i21000008', 'sofia.ivanova@yopmail.com', 'IVANOVA', 'Sofia', 'BUT Info', 'A1-1_An3'),
('b21000009', 'mehdi.belkacem@yopmail.com', 'BELKACEM', 'Mehdi', 'BUT Info', 'B2-1_An3'),
('r21000010', 'clara.rousseau@yopmail.com', 'ROUSSEAU', 'Clara', 'BUT Info', 'A2-1_An3'),
('k23000011', 'youssef.khadiri@yopmail.com', 'KHADIRI', 'Youssef', 'BUT Info', 'A1-1_An2'),
('l23000012', 'camille.leroux@yopmail.com', 'LEROUX', 'Camille', 'BUT Info', 'B-1_An2'),
('n23000013', 'aicha.ndiaye@yopmail.com', 'N''DIAYE', 'Aïcha', 'BUT Info', 'B-1_An2'),
('l23000014', 'enzo.lombardi@yopmail.com', 'LOMBARDI', 'Enzo', 'BUT Info', 'A-2_An2'),
('v23000015', 'manon.vidal@yopmail.com', 'VIDAL', 'Manon', 'BUT Info', 'B-2_An2'),
('n22000016', 'pavel.novak@yopmail.com', 'NOVAK', 'Pavel', 'BUT Info', 'B-2_An2'),
('a12000017', 'lina.cherif@yopmail.com', 'CHERIF', 'Lina', 'BUT Info', 'A2-2_An2'),
('m22000018', 'hugo.marchand@yopmail.com', 'MARCHAND', 'Hugo', 'BUT Info', 'A1-2_An2'),
('b22000019', 'zeynep.demir@yopmail.com', 'DEMIR', 'Zeynep', 'BUT Info', 'A1-2_An2'),
('l21000020', 'antoine.lambert@yopmail.com', 'LAMBERT', 'Antoine', 'BUT Info', 'A2-2_An3'),
('s21000021', 'nora.schmidt@yopmail.com', 'SCHMIDT', 'Nora', 'BUT Info', 'A1-2_An3'),
('b21000022', 'mohamed.diallo@yopmail.com', 'DIALLO', 'Mohamed', 'BUT Info', 'B-2_An3'),
('p21000023', 'elodie.petit@yopmail.com', 'PETIT', 'Élodie', 'BUT Info', 'B-2_An3'),
('r23000024', 'diego.ramirez@yopmail.com', 'RAMIREZ', 'Diego', 'BUT Info', 'B-2_An2'),
('r23000025', 'oceane.roux@yopmail.com', 'ROUX', 'Océane', 'BUT Info', 'A1-1_An2'),
('h22000026', 'ali.hassan@yopmail.com', 'HASSAN', 'Ali', 'BUT Info', 'A1-2_An2'),
('g22000027', 'louane.girard@yopmail.com', 'GIRARD', 'Louane', 'BUT Info', 'A2-2_An2'),
('k22000028', 'viktor.kovacs@yopmail.com', 'KOVACS', 'Viktor', 'BUT Info', 'B-2_An2'),
('l21000029', 'ines.leclerc@yopmail.com', 'LECLERC', 'Inès', 'BUT Info', 'B-2_An3'),
('s21000030', 'rafael.silva@yopmail.com', 'SILVA', 'Rafael', 'BUT Info', 'A2-2_An3'),
('m21000031', 'chloe.meyer@yopmail.com', 'MEYER', 'Chloé', 'BUT Info', 'A1-2_An3'),
('k23000032', 'samir.khelifi@yopmail.com', 'KHELIFI', 'Samir', 'BUT Info', 'A2-1_An2'),
('m23000033', 'jade.morel@yopmail.com', 'MOREL', 'Jade', 'BUT Info', 'A1-2_An2'),
('n22000034', 'aleksander.nowak@yopmail.com', 'NOWAK', 'Aleksander', 'BUT Info', 'A2-1_An2'),
('b22000035', 'leonie.dubois@yopmail.com', 'DUBOIS', 'Léonie', 'BUT Info', 'B-2_An2'),
('e21000036', 'anas.elfassi@yopmail.com', 'EL FASSI', 'Anas', 'BUT Info', 'B-1_An3'),
('a11000037', 'eva.costa@yopmail.com', 'COSTA', 'Eva', 'BUT Info', 'A2-2_An3'),
('b21000038', 'rami.benahmed@yopmail.com', 'BEN AHMED', 'Rami', 'BUT Info', 'A1-2_An3'),
('s23000039', 'lola.sanchez@yopmail.com', 'SANCHEZ', 'Lola', 'BUT Info', 'A1-1_An2'),
('b23000040', 'tariq.bouaziz@yopmail.com', 'BOUAZIZ', 'Tariq', 'BUT Info', 'A1-2_An2'),
('a12000041', 'maelle.colin@yopmail.com', 'COLIN', 'Maëlle', 'BUT Info', 'A2-1_An2'),
('a22000042', 'hassan.almansoori@yopmail.com', 'AL-MANSOORI', 'Hassan', 'BUT Info', 'B-2_An2'),
('b20000043', 'alice.dupuis@yopmail.com', 'DUPUIS', 'Alice', 'BUT Info', 'B-1_An3'),
('t21000044', 'nabil.toumi@yopmail.com', 'TOUMI', 'Nabil', 'BUT Info', 'A2-1_An3'),
('l21000045', 'zoe.lambert@yopmail.com', 'LAMBERT', 'Zoé', 'BUT Info', 'A1-1_An3'),
('a13000046', 'matteo.conti@yopmail.com', 'CONTI', 'Matteo', 'BUT Info', 'A1-2_An2'),
('z23000047', 'amira.zidane@yopmail.com', 'ZIDANE', 'Amira', 'BUT Info', 'A2-2_An2'),
('w22000048', 'tom.weber@yopmail.com', 'WEBER', 'Tom', 'BUT Info', 'A2-2_An2'),
('n22000049', 'lila.nguyen@yopmail.com', 'NGUYEN', 'Lila', 'BUT Info', 'B-2_An2'),
('a21000050', 'bilal.akkad@yopmail.com', 'AKKAD', 'Bilal', 'BUT Info', 'B-2_An3'),
('g22000051', 'leo.garnier@yopmail.com', 'GARNIER', 'Léo', 'BUT Info', 'A1-1_An2'),
('e23000052', 'amina.elkhadir@yopmail.com', 'EL KHADIR', 'Amina', 'BUT Info', 'A1-1_An2'),
('s21000053', 'gabriel.silva@yopmail.com', 'SILVA', 'Gabriel', 'BUT Info', 'A1-1_An2'),
('b19000054', 'lina.boucher@yopmail.com', 'BOUCHER', 'Lina', 'BUT Info', 'A1-1_An2'),
('m23000055', 'youssef.marzouki@yopmail.com', 'MARZOUKI', 'Youssef', 'BUT Info', 'A1-1_An2'),
('n20000056', 'clara.ngo@yopmail.com', 'NGO', 'Clara', 'BUT Info', 'A1-1_An2'),
('c23000057', 'rafael.costa@yopmail.com', 'COSTA', 'Rafael', 'BUT Info', 'A1-1_An2'),
('k22000058', 'sofia.kovac@yopmail.com', 'KOVAČ', 'Sofia', 'BUT Info', 'A1-1_An2'),
('t23000059', 'malik.traore@yopmail.com', 'TRAORÉ', 'Malik', 'BUT Info', 'A1-1_An2'),
('l18000060', 'emma.lerclercq@yopmail.com', 'LERCLERCQ', 'Emma', 'BUT Info', 'A1-1_An2'),
('a23000061', 'hamza.azizi@yopmail.com', 'AZIZI', 'Hamza', 'BUT Info', 'A1-1_An2'),
('d22000062', 'lou.delattre@yopmail.com', 'DELATTRE', 'Lou', 'BUT Info', 'A1-1_An2'),
('p23000063', 'igor.popov@yopmail.com', 'POPOV', 'Igor', 'BUT Info', 'A1-1_An2'),
('m21000064', 'lena.moreau@yopmail.com', 'MOREAU', 'Léna', 'BUT Info', 'A1-1_An2'),
('s23000065', 'anouar.saidi@yopmail.com', 'SAÏDI', 'Anouar', 'BUT Info', 'A1-1_An2'),
('m19000066', 'zoe.marchal@yopmail.com', 'MARCHAL', 'Zoé', 'BUT Info', 'A1-1_An2'),
('b23000067', 'nadir.belhadi@yopmail.com', 'BELHADI', 'Nadir', 'BUT Info', 'A1-1_An2'),
('r22000068', 'camille.roussel@yopmail.com', 'ROUSSEL', 'Camille', 'BUT Info', 'A1-1_An2'),
('n23000069', 'aya.nakamura@yopmail.com', 'NAKAMURA', 'Aya', 'BUT Info', 'A1-1_An2'),
('r20000070', 'thibault.renault@yopmail.com', 'RENAULT', 'Thibault', 'BUT Info', 'A1-1_An2'),
('b23000071', 'soraya.benmohamed@yopmail.com', 'BEN MOHAMED', 'Soraya', 'BUT Info', 'A1-1_An2'),
('p22000072', 'hugo.perrin@yopmail.com', 'PERRIN', 'Hugo', 'BUT Info', 'A1-1_An2'),
('d23000073', 'ines.dubois@yopmail.com', 'DUBOIS', 'Inès', 'BUT Info', 'A1-1_An2'),
('v21000074', 'dmitri.volkov@yopmail.com', 'VOLKOV', 'Dmitri', 'BUT Info', 'A1-1_An2'),
('l23000075', 'elise.lambert@yopmail.com', 'LAMBERT', 'Élise', 'BUT Info', 'A1-1_An2'),
('e18000076', 'karim.elfassi@yopmail.com', 'EL FASSI', 'Karim', 'BUT Info', 'A1-1_An2'),
('m23000077', 'leonie.mercier@yopmail.com', 'MERCIER', 'Léonie', 'BUT Info', 'A1-1_An2'),
('h22000078', 'sami.haddad@yopmail.com', 'HADDAD', 'Sami', 'BUT Info', 'A1-1_An2'),
('c23000079', 'anais.costa@yopmail.com', 'COSTA', 'Anaïs', 'BUT Info', 'A1-1_An2'),
('r23000080', 'julien.roche@yopmail.com', 'ROCHE', 'Julien', 'BUT Info', 'A1-1_An2'),
('d22000081', 'fatou.diop@yopmail.com', 'DIOP', 'Fatou', 'BUT Info', 'A1-1_An2'),
('d23000082', 'noe.dumas@yopmail.com', 'DUMAS', 'Noé', 'BUT Info', 'A1-1_An2'),
('t20000083', 'rania.touil@yopmail.com', 'TOUIL', 'Rania', 'BUT Info', 'A1-1_An2'),
('n23000084', 'victor.nguyen@yopmail.com', 'NGUYEN', 'Victor', 'BUT Info', 'A1-1_An2'),
('c22000085', 'louna.chevalier@yopmail.com', 'CHEVALIER', 'Louna', 'BUT Info', 'A1-1_An2'),
('b23000086', 'idriss.bennis@yopmail.com', 'BENNIS', 'Idriss', 'BUT Info', 'A1-1_An2'),
('r21000087', 'ophelie.roy@yopmail.com', 'ROY', 'Ophélie', 'BUT Info', 'A1-1_An2'),
('z23000088', 'mehdi.zaoui@yopmail.com', 'ZAOUI', 'Mehdi', 'BUT Info', 'A1-1_An2'),
('b19000089', 'manel.benali@yopmail.com', 'BENALI', 'Manel', 'BUT Info', 'A1-1_An2'),
('d23000090', 'tom.dubreuil@yopmail.com', 'DUBREUIL', 'Tom', 'BUT Info', 'A1-1_An2'),
('h22000091', 'nour.haddadi@yopmail.com', 'HADDADI', 'Nour', 'BUT Info', 'A1-1_An2'),
('b23000092', 'maxime.blanc@yopmail.com', 'BLANC', 'Maxime', 'BUT Info', 'A1-1_An2'),
('k23000093', 'amira.khoury@yopmail.com', 'KHOURY', 'Amira', 'BUT Info', 'A1-1_An2'),
('l20000094', 'paul.lefevre@yopmail.com', 'LEFÈVRE', 'Paul', 'BUT Info', 'A1-1_An2'),
('a23000095', 'zara.akhtar@yopmail.com', 'AKHTAR', 'Zara', 'BUT Info', 'A1-1_An2'),
('g22000096', 'mathis.gauthier@yopmail.com', 'GAUTHIER', 'Mathis', 'BUT Info', 'A1-1_An2'),
('e23000097', 'salma.elamrani@yopmail.com', 'EL AMRANI', 'Salma', 'BUT Info', 'A1-1_An2'),
('m21000098', 'enzo.moretti@yopmail.com', 'MORETTI', 'Enzo', 'BUT Info', 'A1-1_An2'),
('d23000099', 'lila.dasilva@yopmail.com', 'DA SILVA', 'Lila', 'BUT Info', 'A1-1_An2'),
('j19000100', 'kevin.joubert@yopmail.com', 'JOUBERT', 'Kévin', 'BUT Info', 'A1-1_An2'),
('b23000101', 'adam.belhadj@yopmail.com', 'BELHADJ', 'Adam', 'BUT Info', 'A1-1_An2'),
('n23000102', 'lea.nguyen@yopmail.com', 'NGUYEN', 'Léa', 'BUT Info', 'A1-1_An2'),
('e23000103', 'mehdi.elyazidi@yopmail.com', 'EL YAZIDI', 'Mehdi', 'BUT Info', 'A1-1_An2'),
('c23000104', 'camille.costa@yopmail.com', 'COSTA', 'Camille', 'BUT Info', 'A1-1_An2'),
('b23000105', 'yassin.bouzid@yopmail.com', 'BOUZID', 'Yassin', 'BUT Info', 'A1-1_An2'),
('d23000106', 'clara.dubreuil@yopmail.com', 'DUBREUIL', 'Clara', 'BUT Info', 'A1-1_An2'),
('s23000107', 'ramiro.silva@yopmail.com', 'SILVA', 'Ramiro', 'BUT Info', 'A1-1_An2'),
('t23000108', 'amira.toumi@yopmail.com', 'TOUMI', 'Amira', 'BUT Info', 'A1-1_An2'),
('l23000109', 'hugo.lefort@yopmail.com', 'LEFORT', 'Hugo', 'BUT Info', 'A1-1_An2'),
('b23000110', 'zara.benali@yopmail.com', 'BENALI', 'Zara', 'BUT Info', 'A1-1_An2'),
('m23000111', 'enzo.moreau@yopmail.com', 'MOREAU', 'Enzo', 'BUT Info', 'A1-1_An2'),
('z23000112', 'fatima.zahra@yopmail.com', 'ZAHRA', 'Fatima', 'BUT Info', 'A1-1_An2'),
('d23000113', 'lucas.dacosta@yopmail.com', 'DA COSTA', 'Lucas', 'BUT Info', 'A1-1_An2'),
('t23000114', 'lina.traore@yopmail.com', 'TRAORÉ', 'Lina', 'BUT Info', 'A1-1_An2'),
('m23000115', 'idir.messaoudi@yopmail.com', 'MESSAOUDI', 'Idir', 'BUT Info', 'A1-1_An2'),
('m23000116', 'eloise.marchand@yopmail.com', 'MARCHAND', 'Éloïse', 'BUT Info', 'A1-1_An2'),
('a23000117', 'karim.azizi@yopmail.com', 'AZIZI', 'Karim', 'BUT Info', 'A1-1_An2'),
('p23000118', 'sofia.petrov@yopmail.com', 'PETROV', 'Sofia', 'BUT Info', 'A1-1_An2'),
('e23000119', 'nabil.elfassi@yopmail.com', 'EL FASSI', 'Nabil', 'BUT Info', 'A1-1_An2'),
('l23000120', 'manon.legall@yopmail.com', 'LE GALL', 'Manon', 'BUT Info', 'A1-1_An2'),
('k23000121', 'rayan.khelifi@yopmail.com', 'KHELIFI', 'Rayan', 'BUT Info', 'A1-1_An2'),
('b23000122', 'louna.barre@yopmail.com', 'BARRE', 'Louna', 'BUT Info', 'A1-1_An2'),
('n23000123', 'pavel.nowak@yopmail.com', 'NOWAK', 'Pavel', 'BUT Info', 'A1-1_An2'),
('d23000124', 'aicha.diallo@yopmail.com', 'DIALLO', 'Aïcha', 'BUT Info', 'A1-1_An2'),
('l23000125', 'theo.lambert@yopmail.com', 'LAMBERT', 'Théo', 'BUT Info', 'A1-1_An2'),
('a23000126', 'salma.akkad@yopmail.com', 'AKKAD', 'Salma', 'BUT Info', 'A1-1_An2'),
('r23000127', 'matteo.rossi@yopmail.com', 'ROSSI', 'Matteo', 'BUT Info', 'A1-1_An2'),
('b23000128', 'jade.benamor@yopmail.com', 'BEN AMOR', 'Jade', 'BUT Info', 'A1-1_An2'),
('k23000129', 'sami.khoury@yopmail.com', 'KHOURY', 'Sami', 'BUT Info', 'A1-1_An2'),
('p23000130', 'oceane.perrin@yopmail.com', 'PERRIN', 'Océane', 'BUT Info', 'A1-1_An2'),
('d23000131', 'ali.demir@yopmail.com', 'DEMIR', 'Ali', 'BUT Info', 'A1-1_An2'),
('m23000132', 'ines.marzouki@yopmail.com', 'MARZOUKI', 'Inès', 'BUT Info', 'A1-1_An2'),
('i23000133', 'viktor.ivanov@yopmail.com', 'IVANOV', 'Viktor', 'BUT Info', 'A1-1_An2'),
('a23000134', 'lila.akhtar@yopmail.com', 'AKHTAR', 'Lila', 'BUT Info', 'A1-1_An2'),
('s23000135', 'anis.saadi@yopmail.com', 'SAADI', 'Anis', 'BUT Info', 'A1-1_An2'),
('m23000136', 'eva.moretti@yopmail.com', 'MORETTI', 'Eva', 'BUT Info', 'A1-1_An2'),
('b23000137', 'kenza.bouaziz@yopmail.com', 'BOUAZIZ', 'Kenza', 'BUT Info', 'A1-1_An2'),
('r23000138', 'maxime.roux@yopmail.com', 'ROUX', 'Maxime', 'BUT Info', 'A1-1_An2'),
('e23000139', 'nora.elmansouri@yopmail.com', 'EL MANSOURI', 'Nora', 'BUT Info', 'A1-1_An2'),
('l23000140', 'diego.lopez@yopmail.com', 'LOPEZ', 'Diego', 'BUT Info', 'A1-1_An2'),
('b23000141', 'amina.bennis@yopmail.com', 'BENNIS', 'Amina', 'BUT Info', 'A1-1_An2'),
('f23000142', 'tomas.fernandes@yopmail.com', 'FERNANDES', 'Tomás', 'BUT Info', 'A1-1_An2'),
('c23000143', 'bilal.cherif@yopmail.com', 'CHERIF', 'Bilal', 'BUT Info', 'A1-1_An2'),
('g23000144', 'leonie.gauthier@yopmail.com', 'GAUTHIER', 'Léonie', 'BUT Info', 'A1-1_An2'),
('h23000145', 'youssef.haddad@yopmail.com', 'HADDAD', 'Youssef', 'BUT Info', 'A1-1_An2'),
('z23000146', 'zoe.zhang@yopmail.com', 'ZHANG', 'Zoé', 'BUT Info', 'A1-1_An2'),
('l23000147', 'rania.lecomte@yopmail.com', 'LECOMTE', 'Rania', 'BUT Info', 'A1-1_An2'),
('b23000148', 'hugo.boukherroub@yopmail.com', 'BOUKHERROUB', 'Hugo', 'BUT Info', 'A1-1_An2'),
('d23000149', 'amelie.dasilva@yopmail.com', 'DA SILVA', 'Amélie', 'BUT Info', 'A1-1_An2'),
('e23000150', 'nour.eldin@yopmail.com', 'EL DIN', 'Nour', 'BUT Info', 'A1-1_An2'),
('b23000151', 'marco.bianchi@yopmail.com', 'BIANCHI', 'Marco', 'BUT Info', 'A1-1_An2'),
('k23000152', 'lea.kovacs@yopmail.com', 'KOVÁCS', 'Léa', 'BUT Info', 'A1-1_An2'),
('d23000153', 'idrissa.diop@yopmail.com', 'DIOP', 'Idrissa', 'BUT Info', 'A1-1_An2'),
('b23000154', 'clara.benahmed@yopmail.com', 'BEN AHMED', 'Clara', 'BUT Info', 'A1-1_An2'),
('c23000155', 'antoine.costa@yopmail.com', 'COSTA', 'Antoine', 'BUT Info', 'A1-1_An2'),
('f23000156', 'fatou.fall@yopmail.com', 'FALL', 'Fatou', 'BUT Info', 'A1-1_An2'),
('m23000157', 'enzo.martin@yopmail.com', 'MARTIN', 'Enzo', 'BUT Info', 'A1-1_An2'),
('t23000158', 'soraya.touil@yopmail.com', 'TOUIL', 'Soraya', 'BUT Info', 'A1-1_An2'),
('l23000159', 'mathis.leroy@yopmail.com', 'LEROY', 'Mathis', 'BUT Info', 'A1-1_An2'),
('a23000160', 'lina.almaktoum@yopmail.com', 'AL-MAKTOUM', 'Lina', 'BUT Info', 'A1-1_An2'),
('z23000161', 'mehdi.zaidi@yopmail.com', 'ZAÏDI', 'Mehdi', 'BUT Info', 'A1-1_An2'),
('d23000162', 'elise.dubois@yopmail.com', 'DUBOIS', 'Élise', 'BUT Info', 'A1-1_An2'),
('s23000163', 'rafael.santos@yopmail.com', 'SANTOS', 'Rafael', 'BUT Info', 'A1-1_An2'),
('o23000164', 'manel.othmani@yopmail.com', 'OTHMANI', 'Manel', 'BUT Info', 'A1-1_An2'),
('g23000165', 'thibault.girard@yopmail.com', 'GIRARD', 'Thibault', 'BUT Info', 'A1-1_An2'),
('b23000166', 'aya.benyoussef@yopmail.com', 'BEN YOUSSEF', 'Aya', 'BUT Info', 'A1-1_An2'),
('m23000167', 'julien.morel@yopmail.com', 'MOREL', 'Julien', 'BUT Info', 'A1-1_An2'),
('l23000168', 'louna.lerclerc@yopmail.com', 'LERCLERC', 'Louna', 'BUT Info', 'A1-1_An2'),
('b23000169', 'karim.boukhemis@yopmail.com', 'BOUKHEMIS', 'Karim', 'BUT Info', 'A1-1_An2'),
('m23000170', 'sofia.mendes@yopmail.com', 'MENDES', 'Sofia', 'BUT Info', 'A1-1_An2'),
('e23000171', 'anouar.elfassi@yopmail.com', 'EL FASSI', 'Anouar', 'BUT Info', 'A1-1_An2'),
('r23000172', 'louane.roy@yopmail.com', 'ROY', 'Louane', 'BUT Info', 'A1-1_An2'),
('p23000173', 'viktor.popa@yopmail.com', 'POPA', 'Viktor', 'BUT Info', 'A1-1_An2'),
('b23000174', 'amira.belkacem@yopmail.com', 'BELKACEM', 'Amira', 'BUT Info', 'A1-1_An2'),
('b23000175', 'hugo.bensaid@yopmail.com', 'BEN SAÏD', 'Hugo', 'BUT Info', 'A1-1_An2'),
('t23000176', 'lea.traore@yopmail.com', 'TRAORÉ', 'Léa', 'BUT Info', 'A1-1_An2'),
('n23000177', 'samir.naceri@yopmail.com', 'NACERI', 'Samir', 'BUT Info', 'A1-1_An2'),
('h23000178', 'clara.haddadi@yopmail.com', 'HADDADI', 'Clara', 'BUT Info', 'A1-1_An2'),
('a23000179', 'youssef.amrani@yopmail.com', 'AMRANI', 'Youssef', 'BUT Info', 'A1-1_An2'),
('l23000180', 'eva.lombardi@yopmail.com', 'LOMBARDI', 'Eva', 'BUT Info', 'A1-1_An2'),
('b23000181', 'idris.bensaid@yopmail.com', 'BENSAÏD', 'Idris', 'BUT Info', 'A1-1_An2'),
('b23000182', 'lila.benkirane@yopmail.com', 'BENKIRANE', 'Lila', 'BUT Info', 'A1-1_An2'),
('l23000183', 'maxence.lambert@yopmail.com', 'LAMBERT', 'Maxence', 'BUT Info', 'A1-1_An2'),
('b23000184', 'nora.boucher@yopmail.com', 'BOUCHER', 'Nora', 'BUT Info', 'A1-1_An2'),
('t23000185', 'tariq.elmansouri@yopmail.com', 'EL MANSOURI', 'Tariq', 'BUT Info', 'A1-1_An2'),
('z23000186', 'zoe.dacosta@yopmail.com', 'DA COSTA', 'Zoé', 'BUT Info', 'A1-1_An2'),
('c23000187', 'matteo.conti@yopmail.com', 'CONTI', 'Matteo', 'BUT Info', 'A1-1_An2'),
('r23000188', 'amelie.roussel@yopmail.com', 'ROUSSEL', 'Amélie', 'BUT Info', 'A1-1_An2'),
('z23000189', 'karim.zidane@yopmail.com', 'ZIDANE', 'Karim', 'BUT Info', 'A1-1_An2'),
('m23000190', 'leo.mercier@yopmail.com', 'MERCIER', 'Léo', 'BUT Info', 'A1-1_An2'),
('b23000191', 'ines.benamor@yopmail.com', 'BEN AMOR', 'Inès', 'BUT Info', 'A1-1_An2'),
('o23000192', 'rafael.oliveira@yopmail.com', 'OLIVEIRA', 'Rafael', 'BUT Info', 'A1-1_An2'),
('k23000193', 'lina.khadra@yopmail.com', 'KHADRA', 'Lina', 'BUT Info', 'A1-1_An2'),
('d23000194', 'hugo.dasilva@yopmail.com', 'DA SILVA', 'Hugo', 'BUT Info', 'A1-1_An2'),
('e23000195', 'chloe.elhaddad@yopmail.com', 'EL HADDAD', 'Chloé', 'BUT Info', 'A1-1_An2'),
('b23000196', 'sami.benali@yopmail.com', 'BENALI', 'Sami', 'BUT Info', 'A1-1_An2'),
('m23000197', 'elise.marechal@yopmail.com', 'MARÉCHAL', 'Élise', 'BUT Info', 'A1-1_An2'),
('b23000198', 'anas.boukhari@yopmail.com', 'BOUKHARI', 'Anas', 'BUT Info', 'A1-1_An2'),
('m23000199', 'manon.leroux@yopmail.com', 'LE ROUX', 'Manon', 'BUT Info', 'A1-1_An2'),
('m23000200', 'clara.martin@yopmail.com', 'MARTIN', 'Clara', 'BUT Info', 'A1-1_An2'),
('b23000201', 'samir.benhaddou@yopmail.com', 'BENHADDOU', 'Samir', 'BUT Info', 'A1-1_An2'),
('n23000202', 'jade.nguyen@yopmail.com', 'NGUYEN', 'Jade', 'BUT Info', 'A1-1_An2'),
('s23000203', 'lucas.silva@yopmail.com', 'SILVA', 'Lucas', 'BUT Info', 'A1-1_An2'),
('f23000204', 'giulia.ferrari@yopmail.com', 'FERRARI', 'Giulia', 'BUT Info', 'A1-1_An2'),
('k23000205', 'adam.kowalski@yopmail.com', 'KOWALSKI', 'Adam', 'BUT Info', 'A1-1_An2'),
('c23000206', 'hugo.chevalier@yopmail.com', 'CHEVALIER', 'Hugo', 'BUT Info', 'A1-1_An2'),
('f23000207', 'sofia.fernandez@yopmail.com', 'FERNANDEZ', 'Sofia', 'BUT Info', 'A1-1_An2'),
('l23000208', 'nathan.levy@yopmail.com', 'LEVY', 'Nathan', 'BUT Info', 'A1-1_An2'),
('t23000209', 'aiko.tanaka@yopmail.com', 'TANAKA', 'Aiko', 'BUT Info', 'A1-1_An2'),
('m23000210', 'ines.moreau@yopmail.com', 'MOREAU', 'Inès', 'BUT Info', 'A1-1_An2'),
('r23000211', 'mateo.rodriguez@yopmail.com', 'RODRIGUEZ', 'Mateo', 'BUT Info', 'A1-1_An2'),
('z23000212', 'liying.zhou@yopmail.com', 'ZHOU', 'Liying', 'BUT Info', 'A1-1_An2'),
('a23000213', 'youssef.amrani@yopmail.com', 'AMRANI', 'Youssef', 'BUT Info', 'A1-1_An2'),
('d23000214', 'lea.dubois@yopmail.com', 'DUBOIS', 'Léa', 'BUT Info', 'A1-1_An2'),
('f23000215', 'mamadou.fofana@yopmail.com', 'FOFANA', 'Mamadou', 'BUT Info', 'A1-1_An2'),
('r23000216', 'matteo.ricci@yopmail.com', 'RICCI', 'Matteo', 'BUT Info', 'A1-1_An2'),
('b23000217', 'lina.bensalem@yopmail.com', 'BEN SALEM', 'Lina', 'BUT Info', 'A1-1_An2'),
('l23000218', 'arthur.lemoine@yopmail.com', 'LEMOINE', 'Arthur', 'BUT Info', 'A1-1_An2'),
('a23000219', 'amira.aitelhadj@yopmail.com', 'AÏT EL HADJ', 'Amira', 'BUT Info', 'A1-1_An2'),
('c23000220', 'ana.carvalho@yopmail.com', 'CARVALHO', 'Ana', 'BUT Info', 'A1-1_An2'),
('g23000221', 'thomas.gruber@yopmail.com', 'GRUBER', 'Thomas', 'BUT Info', 'A1-1_An2'),
('k23000222', 'aisha.khan@yopmail.com', 'KHAN', 'Aisha', 'BUT Info', 'A1-1_An2'),
('p23000223', 'louis.petit@yopmail.com', 'PETIT', 'Louis', 'BUT Info', 'A1-1_An2'),
('b23000224', 'chiara.bianchi@yopmail.com', 'BIANCHI', 'Chiara', 'BUT Info', 'A1-1_An2'),
('y23000225', 'elif.yilmaz@yopmail.com', 'YILMAZ', 'Elif', 'BUT Info', 'A1-1_An2'),
('g23000226', 'rohan.gupta@yopmail.com', 'GUPTA', 'Rohan', 'BUT Info', 'A1-1_An2'),
('m23000227', 'carla.moreno@yopmail.com', 'MORENO', 'Carla', 'BUT Info', 'A1-1_An2'),
('z23000228', 'rayan.ziani@yopmail.com', 'ZIANI', 'Rayan', 'BUT Info', 'A1-1_An2'),
('s23000229', 'marie.schneider@yopmail.com', 'SCHNEIDER', 'Marie', 'BUT Info', 'A1-1_An2'),
('s23000230', 'nabil.slimani@yopmail.com', 'SLIMANI', 'Nabil', 'BUT Info', 'A1-1_An2'),
('b23000231', 'emma.bourgeois@yopmail.com', 'BOURGEOIS', 'Emma', 'BUT Info', 'A1-1_An2'),
('m23000232', 'baptiste.meunier@yopmail.com', 'MEUNIER', 'Baptiste', 'BUT Info', 'A1-1_An2'),
('k23000233', 'minji.kim@yopmail.com', 'KIM', 'Minji', 'BUT Info', 'A1-1_An2'),
('a23000234', 'erik.andersson@yopmail.com', 'ANDERSSON', 'Erik', 'BUT Info', 'A1-1_An2'),
('p23000235', 'lucas.perreira@yopmail.com', 'PERREIRA', 'Lucas', 'BUT Info', 'A1-1_An2'),
('c23000236', 'manon.charpentier@yopmail.com', 'CHARPENTIER', 'Manon', 'BUT Info', 'A1-1_An2'),
('a23000237', 'amina.ahmad@yopmail.com', 'AHMAD', 'Amina', 'BUT Info', 'A1-1_An2'),
('d23000238', 'julien.dube@yopmail.com', 'DUBÉ', 'Julien', 'BUT Info', 'A1-1_An2'),
('l23000239', 'celia.lemoine@yopmail.com', 'LEMOINE', 'Célia', 'BUT Info', 'A1-1_An2'),
('b23000240', 'malek.benyoussef@yopmail.com', 'BEN YOUSSEF', 'Malek', 'BUT Info', 'A1-1_An2'),
('z23000241', 'wei.zhang@yopmail.com', 'ZHANG', 'Wei', 'BUT Info', 'A1-1_An2'),
('o23000242', 'sean.oconnor@yopmail.com', 'O CONNOR', 'Sean', 'BUT Info', 'A1-1_An2'),
('b23000243', 'theo.boulanger@yopmail.com', 'BOULANGER', 'Théo', 'BUT Info', 'A1-1_An2'),
('h23000244', 'nour.hadid@yopmail.com', 'HADID', 'Nour', 'BUT Info', 'A1-1_An2'),
('a23000245', 'alexei.ivanov@yopmail.com', 'IVANOV', 'Alexei', 'BUT Info', 'A1-1_An2'),
('l23000246', 'xinyi.liu@yopmail.com', 'LIU', 'Xinyi', 'BUT Info', 'A1-1_An2'),
('g23000247', 'lucia.gonzalez@yopmail.com', 'GONZALEZ', 'Lucia', 'BUT Info', 'A1-1_An2'),
('r23000248', 'ilies.rahmani@yopmail.com', 'RAHMANI', 'Ilies', 'BUT Info', 'A1-1_An2'),
('s23000249', 'pedro.santos@yopmail.com', 'SANTOS', 'Pedro', 'BUT Info', 'A1-1_An2'),
('b23000250', 'lina.bensalem@yopmail.com', 'BEN SALEM', 'Lina', 'BUT Info', 'A1-1_An2'),
('m23000251', 'lina.martin@yopmail.com', 'MARTIN', 'Lina', 'BUT Info', 'B-1_An3'),
('z21000252', 'mathis.zeroual@yopmail.com', 'ZEROUAL', 'Mathis', 'BUT Info', 'A1-1_An2'),
('m22000253', 'tom.moreau@yopmail.com', 'MOREAU', 'Tom', 'BUT Info', 'B-1_An3'),
('b21000254', 'ines.belkacem@yopmail.com', 'BELKACEM', 'Inès', 'BUT Info', 'B2-1_An2'),
('z23000255', 'sacha.zeroual@yopmail.com', 'ZEROUAL', 'Sacha', 'BUT Info', 'A2-1_An2'),
('b21000256', 'leo.bouaziz@yopmail.com', 'BOUAZIZ', 'Léo', 'BUT Info', 'B2-1_An2'),
('m23000257', 'ines.moreau@yopmail.com', 'MOREAU', 'Inès', 'BUT Info', 'A1-1_An3'),
('n22000258', 'youssef.ndiaye@yopmail.com', 'N''DIAYE', 'Youssef', 'BUT Info', 'A2-1_An3'),
('l23000259', 'clara.leroux@yopmail.com', 'LEROUX', 'Clara', 'BUT Info', 'A2-1_An3'),
('m23000260', 'sarah.martinez@yopmail.com', 'MARTINEZ', 'Sarah', 'BUT Info', 'A2-1_An2'),
('n22000261', 'lea.ndiaye@yopmail.com', 'N''DIAYE', 'Léa', 'BUT Info', 'B-1_An3'),
('d21000262', 'clara.dubois@yopmail.com', 'DUBOIS', 'Clara', 'BUT Info', 'B2-1_An2'),
('d22000263', 'rayan.durand@yopmail.com', 'DURAND', 'Rayan', 'BUT Info', 'B2-1_An3'),
('r23000264', 'ilian.roux@yopmail.com', 'ROUX', 'Ilian', 'BUT Info', 'A2-1_An3'),
('i22000265', 'nina.ivanova@yopmail.com', 'IVANOVA', 'Nina', 'BUT Info', 'B2-1_An3'),
('b22000266', 'ilian.boulet@yopmail.com', 'BOULET', 'Ilian', 'BUT Info', 'A1-2_An2'),
('a22000267', 'ilian.arnaud@yopmail.com', 'ARNAUD', 'Ilian', 'BUT Info', 'A1-2_An2'),
('l22000268', 'leo.lopez@yopmail.com', 'LOPEZ', 'Léo', 'BUT Info', 'A1-1_An3'),
('m23000269', 'camille.moreau@yopmail.com', 'MOREAU', 'Camille', 'BUT Info', 'A1-1_An2'),
('b22000270', 'aicha.blasco@yopmail.com', 'BLASCO', 'Aïcha', 'BUT Info', 'B2-1_An2'),
('b22000271', 'sarah.belkacem@yopmail.com', 'BELKACEM', 'Sarah', 'BUT Info', 'B2-1_An2'),
('d22000272', 'ines.dubois@yopmail.com', 'DUBOIS', 'Inès', 'BUT Info', 'A1-2_An2'),
('m23000273', 'rayan.martinez@yopmail.com', 'MARTINEZ', 'Rayan', 'BUT Info', 'B2-1_An3'),
('m22000274', 'mathis.moreau@yopmail.com', 'MOREAU', 'Mathis', 'BUT Info', 'B-1_An2'),
('z23000275', 'fatima.zeroual@yopmail.com', 'ZEROUAL', 'Fatima', 'BUT Info', 'A1-2_An3');


INSERT INTO Discipline VALUES ('Architecture des ordinateurs');
INSERT INTO Discipline VALUES ('Gestion de projet');
INSERT INTO Discipline VALUES ('Communication');
INSERT INTO Discipline VALUES ('Gestion de bases de données');
INSERT INTO Discipline VALUES ('Programmation orientée objet');
INSERT INTO Discipline VALUES ('Économie');
INSERT INTO Discipline VALUES ('Droit');
INSERT INTO Discipline VALUES ('Gestion financière');
INSERT INTO Discipline VALUES ('Marketing');
INSERT INTO Discipline VALUES ('Communication digitale');
INSERT INTO Discipline VALUES ('Vente et négociation');
INSERT INTO Discipline VALUES ('Anglais');
INSERT INTO Discipline VALUES ('Programmation web');

INSERT INTO User_connect (user_id, user_pass) VALUES
('emilie.martin', ''), 
('jean-luc.bernard', ''),
('fatima.benali', ''),
('alexandre.dubois', ''),
('sofia.rossi', ''),
('laurent.moreau', ''),
('amina.kebir', ''),
('pierre-henri.blanc', ''),
('clara.dupont', ''),
('youssef.el-mansouri', ''),
('lea.girard', ''),
('sergei.ivanov', ''),
('marie-laure.roux', ''),
('mehdi.taha', ''),
('chloe.lefebvre', ''),
('marc.antoine', ''),
('nadia.cherif', ''),
('henri.delorme', ''),
('laura.costa', ''),
('francois-xavier.dupuis', ''),
('samira.othmani', ''),
('philippe.marchand', ''),
('anais.petit', ''),
('lucas.royer', ''),
('helene.vidal', '');

INSERT INTO Role VALUES ('Enseignant', 'Enseignant');
INSERT INTO Role VALUES ('Admin_dep', 'Administrateur département');
INSERT INTO Role VALUES ('Admin_site', 'Administrateur du site');
INSERT INTO Role VALUES ('Etudiant', 'Etudiant');

INSERT INTO Address_type VALUES ('Domicile_1', 'Domicile principal');
INSERT INTO Address_type VALUES ('Domicile_2', 'Domicile secondaire');
INSERT INTO Address_type VALUES ('Travail_1', 'Bureau principal');
INSERT INTO Address_type VALUES ('Travail_2', 'Bureau secondaire');
INSERT INTO Address_type VALUES ('Batiment', 'Local d''entreprise');

INSERT INTO Addr_name (address) VALUES ('413 Avenue Gaston Berger 13100 Aix-en-Provence');

INSERT INTO Internship (internship_identifier, company_name, keywords, start_date_internship, type, end_date_internship, internship_subject, address, student_number, relevance_score, id_teacher) VALUES ('ALT' || '2024' || LPAD(NEXTVAL('Internship_id_counter_seq')::TEXT, 5, '0'), 'ImCheck Therapeutics', ARRAY['Gestion de projet', 'Anglais', 'Gestion de bases de données'], '2024-04-01', 'alternance', '2024-12-27', 'Développement d''une plateforme e-commerce', '46 Rue des Fours 13500 Martigues', 'm22000001', NULL, 'francois-xavier.dupuis');
INSERT INTO Internship (internship_identifier, company_name, keywords, start_date_internship, type, end_date_internship, internship_subject, address, student_number, relevance_score, id_teacher) VALUES ('ALT' || '2024' || LPAD(NEXTVAL('Internship_id_counter_seq')::TEXT, 5, '0'), 'Google', ARRAY['Économie', 'Programmation orientée objet'], '2024-04-01', 'alternance', '2025-02-23', 'Optimisation de requêtes base de données', '16 Boulevard Jean Jaurès 13600 La Ciotat', 'b22000002', NULL, 'sofia.rossi');
INSERT INTO Internship (internship_identifier, company_name, keywords, start_date_internship, type, end_date_internship, internship_subject, address, student_number, relevance_score, id_teacher) VALUES ('ALT' || '2025' || LPAD(NEXTVAL('Internship_id_counter_seq')::TEXT, 5, '0'), 'Capgemini', ARRAY['Droit'], '2025-04-01', 'alternance', '2025-12-23', 'Développement d''une plateforme e-commerce', '24 Impasse Vert et Ciel 13820 Le Rove', 'b22000003', NULL, 'jean-luc.bernard');
INSERT INTO Internship (internship_identifier, company_name, keywords, start_date_internship, type, end_date_internship, internship_subject, address, student_number, relevance_score, id_teacher) VALUES ('ALT' || '2025' || LPAD(NEXTVAL('Internship_id_counter_seq')::TEXT, 5, '0'), 'Stellantis', ARRAY['Gestion de bases de données', 'Vente et négociation', 'Architecture des ordinateurs'], '2025-04-01', 'alternance', '2025-12-18', 'Développement d''une plateforme e-commerce', '5 Impasse de La Palmeraie 13011 La-Penne-sur-Huveaune', 'a12000004', NULL, 'sofia.rossi');
INSERT INTO Internship (internship_identifier, company_name, keywords, start_date_internship, type, end_date_internship, internship_subject, address, student_number, relevance_score, id_teacher) VALUES ('ALT' || '2025' || LPAD(NEXTVAL('Internship_id_counter_seq')::TEXT, 5, '0'), 'Groupe La Poste', ARRAY['Programmation web'], '2025-04-01', 'alternance', '2025-08-16', 'Conception d''une application mobile de VTC', '6 Rue Louis Feuillée 13013 Marseille', 'f22000005', NULL, 'emilie.martin');
INSERT INTO Internship (internship_identifier, company_name, keywords, start_date_internship, type, end_date_internship, internship_subject, address, student_number, relevance_score, id_teacher) VALUES ('ALT' || '2025' || LPAD(NEXTVAL('Internship_id_counter_seq')::TEXT, 5, '0'), 'Airbus', ARRAY['Architecture des ordinateurs', 'Anglais'], '2025-04-01', 'alternance', '2025-12-15', 'Création d''un dashboard de visualisation de données', '4 Rue Pavillon 13001 Marseille', 'e21000006', NULL, 'nadia.cherif');
INSERT INTO Internship (internship_identifier, company_name, keywords, start_date_internship, type, end_date_internship, internship_subject, address, student_number, relevance_score, id_teacher) VALUES ('ALT' || '2023' || LPAD(NEXTVAL('Internship_id_counter_seq')::TEXT, 5, '0'), 'Thales', ARRAY['Communication', 'Marketing'], '2023-04-01', 'alternance', '2023-09-21', 'Audit de sécurité d''une application web', '50 Avenue Dauphiné 06100 Nice', 'm21000007', NULL, 'fatima.benali');
INSERT INTO Internship (internship_identifier, company_name, keywords, start_date_internship, type, end_date_internship, internship_subject, address, student_number, relevance_score, id_teacher) VALUES ('ALT' || '2024' || LPAD(NEXTVAL('Internship_id_counter_seq')::TEXT, 5, '0'), 'DEV-ID Marseille', ARRAY['Droit', 'Vente et négociation', 'Gestion de bases de données'], '2024-04-01', 'alternance', '2024-08-27', 'Audit de sécurité d''une application web', '7 Impasse Maria Mauban 13012 Marseille', 'i21000008', NULL, 'amina.kebir');
INSERT INTO Internship (internship_identifier, company_name, keywords, start_date_internship, type, end_date_internship, internship_subject, address, student_number, relevance_score, id_teacher) VALUES ('ALT' || '2026' || LPAD(NEXTVAL('Internship_id_counter_seq')::TEXT, 5, '0'), 'SFR', ARRAY['Programmation web'], '2026-04-01', 'alternance', '2026-11-05', 'Création d''un dashboard de visualisation de données', '31 Avenue de Savoie 06300 Nice', 'b21000009', NULL, 'youssef.el-mansouri');
INSERT INTO Internship (internship_identifier, company_name, keywords, start_date_internship, type, end_date_internship, internship_subject, address, student_number, relevance_score, id_teacher) VALUES ('ALT' || '2024' || LPAD(NEXTVAL('Internship_id_counter_seq')::TEXT, 5, '0'), 'Valeo', ARRAY['Vente et négociation'], '2024-04-01', 'alternance', '2025-02-21', 'Développement d''un chatbot intelligent', '50 Rue Bâtonnier Boutière 13626 Aix-en-Provence', 'r21000010', NULL, 'laurent.moreau');
INSERT INTO Internship (internship_identifier, company_name, keywords, start_date_internship, type, end_date_internship, internship_subject, address, student_number, relevance_score, id_teacher) VALUES ('ALT' || '2024' || LPAD(NEXTVAL('Internship_id_counter_seq')::TEXT, 5, '0'), 'Orange', ARRAY['Programmation web'], '2024-04-01', 'alternance', '2025-02-16', 'Développement d''une plateforme e-commerce', '32 Cours Aristide Briand 13500 Martigues', 'k23000011', NULL, 'marc.antoine');
INSERT INTO Internship (internship_identifier, company_name, keywords, start_date_internship, type, end_date_internship, internship_subject, address, student_number, relevance_score, id_teacher) VALUES ('ALT' || '2025' || LPAD(NEXTVAL('Internship_id_counter_seq')::TEXT, 5, '0'), 'Thales', ARRAY['Gestion de bases de données', 'Programmation web', 'Gestion financière'], '2025-04-01', 'alternance', '2025-07-04', 'Optimisation de requêtes base de données', '3 Avenue des Calanques 13260 Cassis', 'l23000012', NULL, 'philippe.marchand');
INSERT INTO Internship (internship_identifier, company_name, keywords, start_date_internship, type, end_date_internship, internship_subject, address, student_number, relevance_score, id_teacher) VALUES ('ALT' || '2025' || LPAD(NEXTVAL('Internship_id_counter_seq')::TEXT, 5, '0'), 'Safran', ARRAY['Programmation web'], '2025-04-01', 'alternance', '2025-11-12', 'Développement d''un chatbot intelligent', '50 Allée Alessandro Volta 13500 Martigues', 'n23000013', NULL, 'sergei.ivanov');
INSERT INTO Internship (internship_identifier, company_name, keywords, start_date_internship, type, end_date_internship, internship_subject, address, student_number, relevance_score, id_teacher) VALUES ('ALT' || '2026' || LPAD(NEXTVAL('Internship_id_counter_seq')::TEXT, 5, '0'), 'Free', ARRAY['Communication', 'Programmation orientée objet', 'Communication digitale'], '2026-04-01', 'alternance', '2026-12-10', 'Programmation d''un drone autonome', '43 Chemin de la Vallée du Pilon du Roi 13190 Plan-de-Cuques', 'l23000014', NULL, 'sergei.ivanov');
INSERT INTO Internship (internship_identifier, company_name, keywords, start_date_internship, type, end_date_internship, internship_subject, address, student_number, relevance_score, id_teacher) VALUES ('ALT' || '2026' || LPAD(NEXTVAL('Internship_id_counter_seq')::TEXT, 5, '0'), 'Schneider Electric', ARRAY['Vente et négociation', 'Programmation orientée objet'], '2026-04-01', 'alternance', '2026-07-18', 'Création d''un dashboard de visualisation de données', '13 Impasse des Tournesols 13300 Salon-de-Provence', 'v23000015', NULL, 'clara.dupont');
INSERT INTO Internship (internship_identifier, company_name, keywords, start_date_internship, type, end_date_internship, internship_subject, address, student_number, relevance_score, id_teacher) VALUES ('ALT' || '2024' || LPAD(NEXTVAL('Internship_id_counter_seq')::TEXT, 5, '0'), 'Remmedia', ARRAY['Programmation orientée objet', 'Programmation web', 'Gestion de projet'], '2024-04-01', 'alternance', '2024-11-16', 'Création d''un dashboard de visualisation de données', '6 Rue Alain Bajac 84120 Pertuis', 'n22000016', NULL, 'lea.girard');
INSERT INTO Internship (internship_identifier, company_name, keywords, start_date_internship, type, end_date_internship, internship_subject, address, student_number, relevance_score, id_teacher) VALUES ('ALT' || '2023' || LPAD(NEXTVAL('Internship_id_counter_seq')::TEXT, 5, '0'), 'Renault', ARRAY['Vente et négociation'], '2023-04-01', 'alternance', '2024-02-25', 'Développement d''une plateforme e-commerce', '15 Rue de la Camargue 13300 Salon-de-Provence', 'a12000017', NULL, 'chloe.lefebvre');
INSERT INTO Internship (internship_identifier, company_name, keywords, start_date_internship, type, end_date_internship, internship_subject, address, student_number, relevance_score, id_teacher) VALUES ('ALT' || '2025' || LPAD(NEXTVAL('Internship_id_counter_seq')::TEXT, 5, '0'), 'STMicroelectronics', ARRAY['Économie', 'Architecture des ordinateurs'], '2025-04-01', 'alternance', '2026-02-05', 'Automatisation des déploiements applicatifs', '15 Avenue Paul Julien 13100 Aix-en-Provence', 'm22000018', NULL, 'anais.petit');
INSERT INTO Internship (internship_identifier, company_name, keywords, start_date_internship, type, end_date_internship, internship_subject, address, student_number, relevance_score, id_teacher) VALUES ('ALT' || '2026' || LPAD(NEXTVAL('Internship_id_counter_seq')::TEXT, 5, '0'), 'Omniciel', ARRAY['Vente et négociation', 'Marketing'], '2026-04-01', 'alternance', '2026-12-19', 'Création d''un dashboard de visualisation de données', '8 Rue du Douard 13740 Le Rove', 'b22000019', NULL, 'fatima.benali');
INSERT INTO Internship (internship_identifier, company_name, keywords, start_date_internship, type, end_date_internship, internship_subject, address, student_number, relevance_score, id_teacher) VALUES ('ALT' || '2024' || LPAD(NEXTVAL('Internship_id_counter_seq')::TEXT, 5, '0'), 'Altran', ARRAY['Anglais', 'Gestion de projet', 'Économie'], '2024-04-01', 'alternance', '2024-07-29', 'Développement d''une plateforme e-commerce', '26 Avenue Jean Bart 13470 La-Penne-sur-Huveaune', 'l21000020', NULL, 'fatima.benali');
INSERT INTO Internship (internship_identifier, company_name, keywords, start_date_internship, type, end_date_internship, internship_subject, address, student_number, relevance_score, id_teacher) VALUES ('ALT' || '2023' || LPAD(NEXTVAL('Internship_id_counter_seq')::TEXT, 5, '0'), 'Valeo', ARRAY['Marketing', 'Programmation orientée objet'], '2023-04-01', 'alternance', '2024-02-17', 'Création d''un dashboard de visualisation de données', '45 Boulevard de l''Observatoire 06340 Nice', 's21000021', NULL, 'philippe.marchand');
INSERT INTO Internship (internship_identifier, company_name, keywords, start_date_internship, type, end_date_internship, internship_subject, address, student_number, relevance_score, id_teacher) VALUES ('ALT' || '2026' || LPAD(NEXTVAL('Internship_id_counter_seq')::TEXT, 5, '0'), 'DEV-ID Marseille', ARRAY['Vente et négociation', 'Marketing', 'Communication'], '2026-04-01', 'alternance', '2026-11-14', 'Développement d''une plateforme e-commerce', '21 Avenue des Aires 13120 Gardanne', 'b21000022', NULL, 'jean-luc.bernard');
INSERT INTO Internship (internship_identifier, company_name, keywords, start_date_internship, type, end_date_internship, internship_subject, address, student_number, relevance_score, id_teacher) VALUES ('ALT' || '2025' || LPAD(NEXTVAL('Internship_id_counter_seq')::TEXT, 5, '0'), 'Microsoft', ARRAY['Communication digitale', 'Droit', 'Vente et négociation'], '2025-04-01', 'alternance', '2026-03-26', 'Conception d''une application mobile de VTC', '38 Boulevard des Grands Cerisiers 13400 La-Penne-sur-Huveaune', 'p21000023', NULL, 'laura.costa');
INSERT INTO Internship (internship_identifier, company_name, keywords, start_date_internship, type, end_date_internship, internship_subject, address, student_number, relevance_score, id_teacher) VALUES ('ALT' || '2025' || LPAD(NEXTVAL('Internship_id_counter_seq')::TEXT, 5, '0'), 'SFR', ARRAY['Gestion de projet'], '2025-04-01', 'alternance', '2025-09-07', 'Conception d''une application mobile de VTC', '4 Avenue Antoine Camugli 13600 La Ciotat', 'r23000024', NULL, 'sofia.rossi');
INSERT INTO Internship (internship_identifier, company_name, keywords, start_date_internship, type, end_date_internship, internship_subject, address, student_number, relevance_score, id_teacher) VALUES ('ALT' || '2024' || LPAD(NEXTVAL('Internship_id_counter_seq')::TEXT, 5, '0'), 'Wooxo', ARRAY['Architecture des ordinateurs', 'Programmation web'], '2024-04-01', 'alternance', '2025-03-01', 'Développement d''une plateforme e-commerce', '13 Rue Kerguelen 13600 La Ciotat', 'r23000025', NULL, 'samira.othmani');
INSERT INTO Internship (internship_identifier, company_name, keywords, start_date_internship, type, end_date_internship, internship_subject, address, student_number, relevance_score, id_teacher) VALUES ('ALT' || '2024' || LPAD(NEXTVAL('Internship_id_counter_seq')::TEXT, 5, '0'), 'Ombrea', ARRAY['Programmation web', 'Droit', 'Programmation orientée objet'], '2024-04-01', 'alternance', '2024-12-09', 'Audit de sécurité d''une application web', '40 Vallée des Poiriers 13170 Le Rove', 'h22000026', NULL, 'mehdi.taha');
INSERT INTO Internship (internship_identifier, company_name, keywords, start_date_internship, type, end_date_internship, internship_subject, address, student_number, relevance_score, id_teacher) VALUES ('ALT' || '2023' || LPAD(NEXTVAL('Internship_id_counter_seq')::TEXT, 5, '0'), 'Groupe La Poste', ARRAY['Économie', 'Communication digitale', 'Gestion financière'], '2023-04-01', 'alternance', '2024-01-26', 'Configuration d''une infrastructure réseau sécurisée', '38 Impasse des Agasses 13770 Venelles', 'g22000027', NULL, 'alexandre.dubois');
INSERT INTO Internship (internship_identifier, company_name, keywords, start_date_internship, type, end_date_internship, internship_subject, address, student_number, relevance_score, id_teacher) VALUES ('ALT' || '2023' || LPAD(NEXTVAL('Internship_id_counter_seq')::TEXT, 5, '0'), 'Renault', ARRAY['Droit'], '2023-04-01', 'alternance', '2023-07-08', 'Développement d''une plateforme e-commerce', '41 Rue du Cimetière 13120 Gardanne', 'k22000028', NULL, 'marie-laure.roux');
INSERT INTO Internship (internship_identifier, company_name, keywords, start_date_internship, type, end_date_internship, internship_subject, address, student_number, relevance_score, id_teacher) VALUES ('ALT' || '2024' || LPAD(NEXTVAL('Internship_id_counter_seq')::TEXT, 5, '0'), 'Dassault Aviation', ARRAY['Communication digitale', 'Économie'], '2024-04-01', 'alternance', '2024-09-01', 'Pilotage d''un projet de refonte SI', '6 Rue du Musée 13001 Marseille', 'l21000029', NULL, 'philippe.marchand');
INSERT INTO Internship (internship_identifier, company_name, keywords, start_date_internship, type, end_date_internship, internship_subject, address, student_number, relevance_score, id_teacher) VALUES ('ALT' || '2026' || LPAD(NEXTVAL('Internship_id_counter_seq')::TEXT, 5, '0'), 'STMicroelectronics', ARRAY['Programmation web', 'Communication digitale'], '2026-04-01', 'alternance', '2027-02-28', 'Audit de sécurité d''une application web', '46 Rue des Roseaux 13920 Martigues', 's21000030', NULL, 'amina.kebir');
INSERT INTO Internship (internship_identifier, company_name, keywords, start_date_internship, type, end_date_internship, internship_subject, address, student_number, relevance_score, id_teacher) VALUES ('ALT' || '2023' || LPAD(NEXTVAL('Internship_id_counter_seq')::TEXT, 5, '0'), 'Groupe La Poste', ARRAY['Gestion de projet'], '2023-04-01', 'alternance', '2024-03-26', 'Programmation d''un drone autonome', '44 Allée Auguste Rodin 13470 Aubagne', 'm21000031', NULL, 'nadia.cherif');
INSERT INTO Internship (internship_identifier, company_name, keywords, start_date_internship, type, end_date_internship, internship_subject, address, student_number, relevance_score, id_teacher) VALUES ('ALT' || '2024' || LPAD(NEXTVAL('Internship_id_counter_seq')::TEXT, 5, '0'), 'NXP Semiconductors', ARRAY['Anglais', 'Vente et négociation'], '2024-04-01', 'alternance', '2024-09-10', 'Conception d''une application mobile de VTC', '44 Boulevard Joseph Roubaud 13380 Plan-de-Cuques', 'k23000032', NULL, 'fatima.benali');
INSERT INTO Internship (internship_identifier, company_name, keywords, start_date_internship, type, end_date_internship, internship_subject, address, student_number, relevance_score, id_teacher) VALUES ('ALT' || '2026' || LPAD(NEXTVAL('Internship_id_counter_seq')::TEXT, 5, '0'), 'ImCheck Therapeutics', ARRAY['Anglais', 'Vente et négociation'], '2026-04-01', 'alternance', '2026-11-19', 'Programmation d''un drone autonome', '23 Allée des Romarins 13821 La-Penne-sur-Huveaune', 'm23000033', NULL, 'lucas.royer');
INSERT INTO Internship (internship_identifier, company_name, keywords, start_date_internship, type, end_date_internship, internship_subject, address, student_number, relevance_score, id_teacher) VALUES ('ALT' || '2024' || LPAD(NEXTVAL('Internship_id_counter_seq')::TEXT, 5, '0'), 'Bouygues Telecom', ARRAY['Architecture des ordinateurs', 'Communication'], '2024-04-01', 'alternance', '2025-02-13', 'Création d''un dashboard de visualisation de données', '28 Avenue Henry Dunant 06106 Nice', 'n22000034', NULL, 'henri.delorme');
INSERT INTO Internship (internship_identifier, company_name, keywords, start_date_internship, type, end_date_internship, internship_subject, address, student_number, relevance_score, id_teacher) VALUES ('ALT' || '2026' || LPAD(NEXTVAL('Internship_id_counter_seq')::TEXT, 5, '0'), 'Bull', ARRAY['Marketing', 'Économie'], '2026-04-01', 'alternance', '2027-03-24', 'Pilotage d''un projet de refonte SI', '6 Rue du Cimetière 13120 Gardanne', 'b22000035', NULL, 'marc.antoine');
INSERT INTO Internship (internship_identifier, company_name, keywords, start_date_internship, type, end_date_internship, internship_subject, address, student_number, relevance_score, id_teacher) VALUES ('ALT' || '2026' || LPAD(NEXTVAL('Internship_id_counter_seq')::TEXT, 5, '0'), 'Netangels', ARRAY['Gestion financière', 'Programmation orientée objet', 'Vente et négociation'], '2026-04-01', 'alternance', '2026-09-20', 'Création d''un dashboard de visualisation de données', '30 Rue des Ortolans 13820 Le Rove', 'e21000036', NULL, 'marie-laure.roux');
INSERT INTO Internship (internship_identifier, company_name, keywords, start_date_internship, type, end_date_internship, internship_subject, address, student_number, relevance_score, id_teacher) VALUES ('ALT' || '2023' || LPAD(NEXTVAL('Internship_id_counter_seq')::TEXT, 5, '0'), 'Purjus', ARRAY['Architecture des ordinateurs', 'Gestion de bases de données'], '2023-04-01', 'alternance', '2023-08-14', 'Pilotage d''un projet de refonte SI', '26 Avenue Mendiguren 06300 Nice', 'a11000037', NULL, 'francois-xavier.dupuis');
INSERT INTO Internship (internship_identifier, company_name, keywords, start_date_internship, type, end_date_internship, internship_subject, address, student_number, relevance_score, id_teacher) VALUES ('ALT' || '2026' || LPAD(NEXTVAL('Internship_id_counter_seq')::TEXT, 5, '0'), 'Renault', ARRAY['Économie', 'Architecture des ordinateurs', 'Communication'], '2026-04-01', 'alternance', '2026-10-16', 'Audit de sécurité d''une application web', '8 Allée des Acacias 83470 Saint-Maximin-la-Sainte-Baume', 'b21000038', NULL, 'amina.kebir');
INSERT INTO Internship (internship_identifier, company_name, keywords, start_date_internship, type, end_date_internship, internship_subject, address, student_number, relevance_score, id_teacher) VALUES ('ALT' || '2026' || LPAD(NEXTVAL('Internship_id_counter_seq')::TEXT, 5, '0'), 'Safran', ARRAY['Gestion de bases de données', 'Économie', 'Gestion financière'], '2026-04-01', 'alternance', '2026-09-07', 'Conception d''une application mobile de VTC', '34 Rue Méry 13002 Marseille', 's23000039', NULL, 'sergei.ivanov');
INSERT INTO Internship (internship_identifier, company_name, keywords, start_date_internship, type, end_date_internship, internship_subject, address, student_number, relevance_score, id_teacher) VALUES ('ALT' || '2025' || LPAD(NEXTVAL('Internship_id_counter_seq')::TEXT, 5, '0'), 'Purjus', ARRAY['Anglais', 'Économie', 'Programmation web'], '2025-04-01', 'alternance', '2026-01-26', 'Conception d''une application mobile de VTC', '44 Impasse des Grillons 13740 Le Rove', 'b23000040', NULL, 'marc.antoine');
INSERT INTO Internship (internship_identifier, company_name, keywords, start_date_internship, type, end_date_internship, internship_subject, address, student_number, relevance_score, id_teacher) VALUES ('ALT' || '2023' || LPAD(NEXTVAL('Internship_id_counter_seq')::TEXT, 5, '0'), 'Omniciel', ARRAY['Programmation web', 'Marketing'], '2023-04-01', 'alternance', '2023-12-24', 'Développement d''une plateforme e-commerce', '25 Avenue Frédéric Mistral 13500 Martigues', 'a12000041', NULL, 'alexandre.dubois');
INSERT INTO Internship (internship_identifier, company_name, keywords, start_date_internship, type, end_date_internship, internship_subject, address, student_number, relevance_score, id_teacher) VALUES ('ALT' || '2026' || LPAD(NEXTVAL('Internship_id_counter_seq')::TEXT, 5, '0'), 'Dassault Systèmes', ARRAY['Gestion de bases de données', 'Économie', 'Anglais'], '2026-04-01', 'alternance', '2026-11-23', 'Développement d''une plateforme e-commerce', '47 Impasse de la Quille 13380 Plan-de-Cuques', 'a22000042', NULL, 'youssef.el-mansouri');
INSERT INTO Internship (internship_identifier, company_name, keywords, start_date_internship, type, end_date_internship, internship_subject, address, student_number, relevance_score, id_teacher) VALUES ('ALT' || '2023' || LPAD(NEXTVAL('Internship_id_counter_seq')::TEXT, 5, '0'), 'Renault', ARRAY['Gestion de projet', 'Anglais', 'Communication digitale'], '2023-04-01', 'alternance', '2023-08-17', 'Optimisation de requêtes base de données', '43 Rue Sinetis 13500 Martigues', 'b20000043', NULL, 'lea.girard');
INSERT INTO Internship (internship_identifier, company_name, keywords, start_date_internship, type, end_date_internship, internship_subject, address, student_number, relevance_score, id_teacher) VALUES ('ALT' || '2025' || LPAD(NEXTVAL('Internship_id_counter_seq')::TEXT, 5, '0'), 'IADYS', ARRAY['Gestion de bases de données'], '2025-04-01', 'alternance', '2025-08-01', 'Automatisation des déploiements applicatifs', '31 Boulevard Georges Clemenceau 13600 La Ciotat', 't21000044', NULL, 'nadia.cherif');
INSERT INTO Internship (internship_identifier, company_name, keywords, start_date_internship, type, end_date_internship, internship_subject, address, student_number, relevance_score, id_teacher) VALUES ('ALT' || '2026' || LPAD(NEXTVAL('Internship_id_counter_seq')::TEXT, 5, '0'), 'Capgemini', ARRAY['Communication digitale', 'Anglais', 'Gestion de projet'], '2026-04-01', 'alternance', '2027-02-24', 'Optimisation de requêtes base de données', '11 Impasse Aimée Mathieu 84120 Pertuis', 'l21000045', NULL, 'francois-xavier.dupuis');
INSERT INTO Internship (internship_identifier, company_name, keywords, start_date_internship, type, end_date_internship, internship_subject, address, student_number, relevance_score, id_teacher) VALUES ('ALT' || '2024' || LPAD(NEXTVAL('Internship_id_counter_seq')::TEXT, 5, '0'), 'Sopra Steria', ARRAY['Économie', 'Programmation orientée objet', 'Communication digitale'], '2024-04-01', 'alternance', '2024-12-04', 'Configuration d''une infrastructure réseau sécurisée', '32 Impasse des Vaudrans 13011 Marseille', 'a13000046', NULL, 'philippe.marchand');
INSERT INTO Internship (internship_identifier, company_name, keywords, start_date_internship, type, end_date_internship, internship_subject, address, student_number, relevance_score, id_teacher) VALUES ('ALT' || '2025' || LPAD(NEXTVAL('Internship_id_counter_seq')::TEXT, 5, '0'), 'Siemens', ARRAY['Architecture des ordinateurs'], '2025-04-01', 'alternance', '2025-09-12', 'Conception d''une application mobile de VTC', '4 Allée de Tanalia 13590 Gardanne', 'z23000047', NULL, 'henri.delorme');
INSERT INTO Internship (internship_identifier, company_name, keywords, start_date_internship, type, end_date_internship, internship_subject, address, student_number, relevance_score, id_teacher) VALUES ('ALT' || '2023' || LPAD(NEXTVAL('Internship_id_counter_seq')::TEXT, 5, '0'), 'Capgemini', ARRAY['Économie', 'Communication', 'Gestion de projet'], '2023-04-01', 'alternance', '2024-02-22', 'Audit de sécurité d''une application web', '21 Avenue Jean Macé 13500 Martigues', 'w22000048', NULL, 'anais.petit');
INSERT INTO Internship (internship_identifier, company_name, keywords, start_date_internship, type, end_date_internship, internship_subject, address, student_number, relevance_score, id_teacher) VALUES ('ALT' || '2023' || LPAD(NEXTVAL('Internship_id_counter_seq')::TEXT, 5, '0'), 'SFR', ARRAY['Droit', 'Programmation web', 'Économie'], '2023-04-01', 'alternance', '2023-11-29', 'Développement d''un chatbot intelligent', '24 Rue de Sévigné 13300 Salon-de-Provence', 'n22000049', NULL, 'helene.vidal');
INSERT INTO Internship (internship_identifier, company_name, keywords, start_date_internship, type, end_date_internship, internship_subject, address, student_number, relevance_score, id_teacher) VALUES ('ALT' || '2024' || LPAD(NEXTVAL('Internship_id_counter_seq')::TEXT, 5, '0'), 'IADYS', ARRAY['Programmation orientée objet'], '2024-04-01', 'alternance', '2024-11-16', 'Configuration d''une infrastructure réseau sécurisée', '38 Rue des Bartavelles 13190 Plan-de-Cuques', 'a21000050', NULL, 'clara.dupont');
INSERT INTO Internship (internship_identifier, company_name, keywords, start_date_internship, type, end_date_internship, internship_subject, address, student_number, relevance_score, id_teacher) VALUES ('ALT' || '2024' || LPAD(NEXTVAL('Internship_id_counter_seq')::TEXT, 5, '0'), 'Remmedia', ARRAY['Architecture des ordinateurs'], '2024-04-01', 'alternance', '2025-02-22', 'Optimisation de requêtes base de données', '11 Impasse des Romarins 13600 La Ciotat', 'g22000051', NULL, 'sofia.rossi');
INSERT INTO Internship (internship_identifier, company_name, keywords, start_date_internship, type, end_date_internship, internship_subject, address, student_number, relevance_score, id_teacher) VALUES ('ALT' || '2025' || LPAD(NEXTVAL('Internship_id_counter_seq')::TEXT, 5, '0'), 'Groupe La Poste', ARRAY['Communication', 'Anglais'], '2025-04-01', 'alternance', '2026-01-21', 'Audit de sécurité d''une application web', '50 Rue Victor Delacour 13600 La Ciotat', 'e23000052', NULL, 'marc.antoine');
INSERT INTO Internship (internship_identifier, company_name, keywords, start_date_internship, type, end_date_internship, internship_subject, address, student_number, relevance_score, id_teacher) VALUES ('ALT' || '2023' || LPAD(NEXTVAL('Internship_id_counter_seq')::TEXT, 5, '0'), 'Netangels', ARRAY['Marketing', 'Gestion financière'], '2023-04-01', 'alternance', '2023-11-24', 'Automatisation des déploiements applicatifs', '6 Boulevard Ange Delestrade 13380 Plan-de-Cuques', 's21000053', NULL, 'sofia.rossi');
INSERT INTO Internship (internship_identifier, company_name, keywords, start_date_internship, type, end_date_internship, internship_subject, address, student_number, relevance_score, id_teacher) VALUES ('ALT' || '2024' || LPAD(NEXTVAL('Internship_id_counter_seq')::TEXT, 5, '0'), 'Dassault Aviation', ARRAY['Anglais', 'Programmation orientée objet'], '2024-04-01', 'alternance', '2024-09-06', 'Développement d''une plateforme e-commerce', '34 Allée du Ferigoulo 13500 Martigues', 'b19000054', NULL, 'jean-luc.bernard');
INSERT INTO Internship (internship_identifier, company_name, keywords, start_date_internship, type, end_date_internship, internship_subject, address, student_number, relevance_score, id_teacher) VALUES ('ALT' || '2024' || LPAD(NEXTVAL('Internship_id_counter_seq')::TEXT, 5, '0'), 'Free', ARRAY['Économie'], '2024-04-01', 'alternance', '2024-09-08', 'Développement d''un chatbot intelligent', '36 Rue Emma et Philippe Tiranty 06000 Nice', 'm23000055', NULL, 'samira.othmani');
INSERT INTO Internship (internship_identifier, company_name, keywords, start_date_internship, type, end_date_internship, internship_subject, address, student_number, relevance_score, id_teacher) VALUES ('ALT' || '2026' || LPAD(NEXTVAL('Internship_id_counter_seq')::TEXT, 5, '0'), 'Wooxo', ARRAY['Communication digitale', 'Gestion de bases de données'], '2026-04-01', 'alternance', '2026-11-16', 'Audit de sécurité d''une application web', '26 Rue Saint-Exupéry 13180 Le Rove', 'n20000056', NULL, 'alexandre.dubois');
INSERT INTO Internship (internship_identifier, company_name, keywords, start_date_internship, type, end_date_internship, internship_subject, address, student_number, relevance_score, id_teacher) VALUES ('ALT' || '2023' || LPAD(NEXTVAL('Internship_id_counter_seq')::TEXT, 5, '0'), 'Ombrea', ARRAY['Communication digitale', 'Anglais'], '2023-04-01', 'alternance', '2024-01-18', 'Pilotage d''un projet de refonte SI', '47 Allée Jean Perrin 13270 Fos-sur-Mer', 'c23000057', NULL, 'fatima.benali');
INSERT INTO Internship (internship_identifier, company_name, keywords, start_date_internship, type, end_date_internship, internship_subject, address, student_number, relevance_score, id_teacher) VALUES ('ALT' || '2025' || LPAD(NEXTVAL('Internship_id_counter_seq')::TEXT, 5, '0'), 'Safran', ARRAY['Anglais', 'Gestion financière'], '2025-04-01', 'alternance', '2025-12-29', 'Programmation d''un drone autonome', '12 Boulevard Salicis 83200 Toulon', 'k22000058', NULL, 'amina.kebir');
INSERT INTO Internship (internship_identifier, company_name, keywords, start_date_internship, type, end_date_internship, internship_subject, address, student_number, relevance_score, id_teacher) VALUES ('ALT' || '2024' || LPAD(NEXTVAL('Internship_id_counter_seq')::TEXT, 5, '0'), 'Atos', ARRAY['Programmation web'], '2024-04-01', 'alternance', '2024-07-21', 'Configuration d''une infrastructure réseau sécurisée', '25 Rue des Remparts 84120 Pertuis', 't23000059', NULL, 'laura.costa');
INSERT INTO Internship (internship_identifier, company_name, keywords, start_date_internship, type, end_date_internship, internship_subject, address, student_number, relevance_score, id_teacher) VALUES ('ALT' || '2026' || LPAD(NEXTVAL('Internship_id_counter_seq')::TEXT, 5, '0'), 'Netangels', ARRAY['Gestion de projet', 'Anglais', 'Marketing'], '2026-04-01', 'alternance', '2027-03-23', 'Audit de sécurité d''une application web', '1 Cours Lafayette 83081 Toulon', 'l18000060', NULL, 'youssef.el-mansouri');
INSERT INTO Internship (internship_identifier, company_name, keywords, start_date_internship, type, end_date_internship, internship_subject, address, student_number, relevance_score, id_teacher) VALUES ('ALT' || '2023' || LPAD(NEXTVAL('Internship_id_counter_seq')::TEXT, 5, '0'), 'Alstom', ARRAY['Programmation orientée objet', 'Communication'], '2023-04-01', 'alternance', '2024-03-17', 'Création d''un dashboard de visualisation de données', '31 Impasse des Micocouliers 13300 Salon-de-Provence', 'a23000061', NULL, 'pierre-henri.blanc');
INSERT INTO Internship (internship_identifier, company_name, keywords, start_date_internship, type, end_date_internship, internship_subject, address, student_number, relevance_score, id_teacher) VALUES ('ALT' || '2024' || LPAD(NEXTVAL('Internship_id_counter_seq')::TEXT, 5, '0'), 'Omniciel', ARRAY['Communication', 'Droit', 'Communication digitale'], '2024-04-01', 'alternance', '2025-02-07', 'Programmation d''un drone autonome', '40 Quai aux Charbons 13002 Marseille', 'd22000062', NULL, 'samira.othmani');
INSERT INTO Internship (internship_identifier, company_name, keywords, start_date_internship, type, end_date_internship, internship_subject, address, student_number, relevance_score, id_teacher) VALUES ('ALT' || '2025' || LPAD(NEXTVAL('Internship_id_counter_seq')::TEXT, 5, '0'), 'Infineon Technologies', ARRAY['Droit'], '2025-04-01', 'alternance', '2025-12-15', 'Pilotage d''un projet de refonte SI', '27 Rue Peyresc 13626 Aix-en-Provence', 'p23000063', NULL, 'lucas.royer');
INSERT INTO Internship (internship_identifier, company_name, keywords, start_date_internship, type, end_date_internship, internship_subject, address, student_number, relevance_score, id_teacher) VALUES ('ALT' || '2023' || LPAD(NEXTVAL('Internship_id_counter_seq')::TEXT, 5, '0'), 'ImCheck Therapeutics', ARRAY['Anglais', 'Droit'], '2023-04-01', 'alternance', '2023-10-06', 'Audit de sécurité d''une application web', '11 Avenue Émile Bodin 13260 Cassis', 'm21000064', NULL, 'sofia.rossi');
INSERT INTO Internship (internship_identifier, company_name, keywords, start_date_internship, type, end_date_internship, internship_subject, address, student_number, relevance_score, id_teacher) VALUES ('ALT' || '2025' || LPAD(NEXTVAL('Internship_id_counter_seq')::TEXT, 5, '0'), 'Atos', ARRAY['Gestion de projet'], '2025-04-01', 'alternance', '2026-01-31', 'Audit de sécurité d''une application web', '38 Impasse Fontcouverte 13080 Gardanne', 's23000065', NULL, 'nadia.cherif');
INSERT INTO Internship (internship_identifier, company_name, keywords, start_date_internship, type, end_date_internship, internship_subject, address, student_number, relevance_score, id_teacher) VALUES ('ALT' || '2024' || LPAD(NEXTVAL('Internship_id_counter_seq')::TEXT, 5, '0'), 'Apple', ARRAY['Vente et négociation', 'Gestion de bases de données', 'Économie'], '2024-04-01', 'alternance', '2025-03-01', 'Configuration d''une infrastructure réseau sécurisée', '47 Cours Voltaire 13400 Aubagne', 'm19000066', NULL, 'sergei.ivanov');
INSERT INTO Internship (internship_identifier, company_name, keywords, start_date_internship, type, end_date_internship, internship_subject, address, student_number, relevance_score, id_teacher) VALUES ('ALT' || '2026' || LPAD(NEXTVAL('Internship_id_counter_seq')::TEXT, 5, '0'), 'Amazon Web Services', ARRAY['Architecture des ordinateurs'], '2026-04-01', 'alternance', '2026-07-30', 'Conception d''une application mobile de VTC', '23 Rue Colette Besson 13380 Plan-de-Cuques', 'b23000067', NULL, 'pierre-henri.blanc');
INSERT INTO Internship (internship_identifier, company_name, keywords, start_date_internship, type, end_date_internship, internship_subject, address, student_number, relevance_score, id_teacher) VALUES ('ALT' || '2024' || LPAD(NEXTVAL('Internship_id_counter_seq')::TEXT, 5, '0'), 'Capgemini', ARRAY['Gestion de projet', 'Droit'], '2024-04-01', 'alternance', '2024-07-04', 'Optimisation de requêtes base de données', '18 Cours Beaumond 13400 Aubagne', 'r22000068', NULL, 'amina.kebir');
INSERT INTO Internship (internship_identifier, company_name, keywords, start_date_internship, type, end_date_internship, internship_subject, address, student_number, relevance_score, id_teacher) VALUES ('ALT' || '2023' || LPAD(NEXTVAL('Internship_id_counter_seq')::TEXT, 5, '0'), 'IADYS', ARRAY['Vente et négociation', 'Programmation web'], '2023-04-01', 'alternance', '2024-01-31', 'Audit de sécurité d''une application web', '11 Allée du Parc 13770 Venelles', 'n23000069', NULL, 'clara.dupont');
INSERT INTO Internship (internship_identifier, company_name, keywords, start_date_internship, type, end_date_internship, internship_subject, address, student_number, relevance_score, id_teacher) VALUES ('ALT' || '2026' || LPAD(NEXTVAL('Internship_id_counter_seq')::TEXT, 5, '0'), 'Airbus', ARRAY['Anglais'], '2026-04-01', 'alternance', '2026-07-30', 'Création d''un dashboard de visualisation de données', '18 Avenue des Tamaris 13100 Aix-en-Provence', 'r20000070', NULL, 'mehdi.taha');
INSERT INTO Internship (internship_identifier, company_name, keywords, start_date_internship, type, end_date_internship, internship_subject, address, student_number, relevance_score, id_teacher) VALUES ('ALT' || '2025' || LPAD(NEXTVAL('Internship_id_counter_seq')::TEXT, 5, '0'), 'Airbus', ARRAY['Droit'], '2025-04-01', 'alternance', '2026-03-09', 'Développement d''un chatbot intelligent', '36 Allée de la Montagne 13530 Trets', 'b23000071', NULL, 'anais.petit');
INSERT INTO Internship (internship_identifier, company_name, keywords, start_date_internship, type, end_date_internship, internship_subject, address, student_number, relevance_score, id_teacher) VALUES ('ALT' || '2023' || LPAD(NEXTVAL('Internship_id_counter_seq')::TEXT, 5, '0'), 'Safran', ARRAY['Droit'], '2023-04-01', 'alternance', '2024-02-03', 'Développement d''une plateforme e-commerce', '32 Place Fabre d''Églantine 13013 Plan-de-Cuques', 'p22000072', NULL, 'laurent.moreau');
INSERT INTO Internship (internship_identifier, company_name, keywords, start_date_internship, type, end_date_internship, internship_subject, address, student_number, relevance_score, id_teacher) VALUES ('ALT' || '2025' || LPAD(NEXTVAL('Internship_id_counter_seq')::TEXT, 5, '0'), 'Microsoft', ARRAY['Communication'], '2025-04-01', 'alternance', '2026-03-13', 'Développement d''une plateforme e-commerce', '36 Avenue Ambroise Croizat 13110 Fos-sur-Mer', 'd23000073', NULL, 'mehdi.taha');
INSERT INTO Internship (internship_identifier, company_name, keywords, start_date_internship, type, end_date_internship, internship_subject, address, student_number, relevance_score, id_teacher) VALUES ('ALT' || '2025' || LPAD(NEXTVAL('Internship_id_counter_seq')::TEXT, 5, '0'), 'Safran', ARRAY['Communication digitale'], '2025-04-01', 'alternance', '2025-10-08', 'Développement d''un chatbot intelligent', '49 Rue Marx Dormoy 13110 Fos-sur-Mer', 'v21000074', NULL, 'mehdi.taha');
INSERT INTO Internship (internship_identifier, company_name, keywords, start_date_internship, type, end_date_internship, internship_subject, address, student_number, relevance_score, id_teacher) VALUES ('ALT' || '2025' || LPAD(NEXTVAL('Internship_id_counter_seq')::TEXT, 5, '0'), 'Microsoft', ARRAY['Gestion de bases de données', 'Communication digitale', 'Gestion financière'], '2025-04-01', 'alternance', '2025-12-14', 'Automatisation des déploiements applicatifs', '18 Quai Jean Charcot 13002 Marseille', 'l23000075', NULL, 'chloe.lefebvre');
INSERT INTO Internship (internship_identifier, company_name, keywords, start_date_internship, type, end_date_internship, internship_subject, address, student_number, relevance_score, id_teacher) VALUES ('ALT' || '2025' || LPAD(NEXTVAL('Internship_id_counter_seq')::TEXT, 5, '0'), 'Valeo', ARRAY['Anglais', 'Vente et négociation', 'Programmation orientée objet'], '2025-04-01', 'alternance', '2026-03-12', 'Conception d''une application mobile de VTC', '22 Avenue du 8 Mai 1945 13120 Gardanne', 'e18000076', NULL, 'alexandre.dubois');
INSERT INTO Internship (internship_identifier, company_name, keywords, start_date_internship, type, end_date_internship, internship_subject, address, student_number, relevance_score, id_teacher) VALUES ('ALT' || '2023' || LPAD(NEXTVAL('Internship_id_counter_seq')::TEXT, 5, '0'), 'Airbus', ARRAY['Gestion financière', 'Marketing', 'Gestion de bases de données'], '2023-04-01', 'alternance', '2023-12-25', 'Configuration d''une infrastructure réseau sécurisée', '19 Rue Georges Charpak 13013 Marseille', 'm23000077', NULL, 'francois-xavier.dupuis');
INSERT INTO Internship (internship_identifier, company_name, keywords, start_date_internship, type, end_date_internship, internship_subject, address, student_number, relevance_score, id_teacher) VALUES ('ALT' || '2026' || LPAD(NEXTVAL('Internship_id_counter_seq')::TEXT, 5, '0'), 'Infineon Technologies', ARRAY['Économie', 'Communication digitale'], '2026-04-01', 'alternance', '2026-08-16', 'Automatisation des déploiements applicatifs', '36 Allée de Mendez 13270 Fos-sur-Mer', 'h22000078', NULL, 'chloe.lefebvre');
INSERT INTO Internship (internship_identifier, company_name, keywords, start_date_internship, type, end_date_internship, internship_subject, address, student_number, relevance_score, id_teacher) VALUES ('ALT' || '2023' || LPAD(NEXTVAL('Internship_id_counter_seq')::TEXT, 5, '0'), 'STMicroelectronics', ARRAY['Programmation orientée objet'], '2023-04-01', 'alternance', '2023-10-15', 'Automatisation des déploiements applicatifs', '26 Allée de la Gardi 13530 Trets', 'c23000079', NULL, 'youssef.el-mansouri');
INSERT INTO Internship (internship_identifier, company_name, keywords, start_date_internship, type, end_date_internship, internship_subject, address, student_number, relevance_score, id_teacher) VALUES ('ALT' || '2024' || LPAD(NEXTVAL('Internship_id_counter_seq')::TEXT, 5, '0'), 'Vertical Expense', ARRAY['Programmation orientée objet', 'Vente et négociation', 'Économie'], '2024-04-01', 'alternance', '2025-02-15', 'Développement d''un chatbot intelligent', '30 Impasse du Colombier 13400 Aubagne', 'r23000080', NULL, 'anais.petit');
INSERT INTO Internship (internship_identifier, company_name, keywords, start_date_internship, type, end_date_internship, internship_subject, address, student_number, relevance_score, id_teacher) VALUES ('ALT' || '2024' || LPAD(NEXTVAL('Internship_id_counter_seq')::TEXT, 5, '0'), 'Schneider Electric', ARRAY['Économie', 'Architecture des ordinateurs'], '2024-04-01', 'alternance', '2025-02-09', 'Programmation d''un drone autonome', '12 Boulevard de l''Engrenier 13110 Fos-sur-Mer', 'd22000081', NULL, 'helene.vidal');
INSERT INTO Internship (internship_identifier, company_name, keywords, start_date_internship, type, end_date_internship, internship_subject, address, student_number, relevance_score, id_teacher) VALUES ('ALT' || '2025' || LPAD(NEXTVAL('Internship_id_counter_seq')::TEXT, 5, '0'), 'ImCheck Therapeutics', ARRAY['Programmation orientée objet'], '2025-04-01', 'alternance', '2025-10-28', 'Audit de sécurité d''une application web', '18 Rue de la Tour 84120 Pertuis', 'd23000082', NULL, 'amina.kebir');
INSERT INTO Internship (internship_identifier, company_name, keywords, start_date_internship, type, end_date_internship, internship_subject, address, student_number, relevance_score, id_teacher) VALUES ('ALT' || '2026' || LPAD(NEXTVAL('Internship_id_counter_seq')::TEXT, 5, '0'), 'Texas Instruments', ARRAY['Droit', 'Vente et négociation', 'Économie'], '2026-04-01', 'alternance', '2027-01-13', 'Création d''un dashboard de visualisation de données', '31 Rue des Fauvettes 13300 Salon-de-Provence', 't20000083', NULL, 'helene.vidal');
INSERT INTO Internship (internship_identifier, company_name, keywords, start_date_internship, type, end_date_internship, internship_subject, address, student_number, relevance_score, id_teacher) VALUES ('ALT' || '2026' || LPAD(NEXTVAL('Internship_id_counter_seq')::TEXT, 5, '0'), 'Atos', ARRAY['Gestion de projet', 'Architecture des ordinateurs', 'Gestion de bases de données'], '2026-04-01', 'alternance', '2026-09-29', 'Configuration d''une infrastructure réseau sécurisée', '39 Avenue Alphonse Juin 83041 Toulon', 'n23000084', NULL, 'henri.delorme');
INSERT INTO Internship (internship_identifier, company_name, keywords, start_date_internship, type, end_date_internship, internship_subject, address, student_number, relevance_score, id_teacher) VALUES ('ALT' || '2024' || LPAD(NEXTVAL('Internship_id_counter_seq')::TEXT, 5, '0'), 'STMicroelectronics', ARRAY['Gestion financière', 'Anglais'], '2024-04-01', 'alternance', '2025-03-06', 'Automatisation des déploiements applicatifs', '5 Rue Jean Roque 13500 Martigues', 'c22000085', NULL, 'lucas.royer');
INSERT INTO Internship (internship_identifier, company_name, keywords, start_date_internship, type, end_date_internship, internship_subject, address, student_number, relevance_score, id_teacher) VALUES ('ALT' || '2023' || LPAD(NEXTVAL('Internship_id_counter_seq')::TEXT, 5, '0'), 'STMicroelectronics', ARRAY['Communication digitale', 'Vente et négociation', 'Droit'], '2023-04-01', 'alternance', '2024-03-25', 'Programmation d''un drone autonome', '45 Allée des Griottes 13626 Aix-en-Provence', 'b23000086', NULL, 'fatima.benali');
INSERT INTO Internship (internship_identifier, company_name, keywords, start_date_internship, type, end_date_internship, internship_subject, address, student_number, relevance_score, id_teacher) VALUES ('ALT' || '2025' || LPAD(NEXTVAL('Internship_id_counter_seq')::TEXT, 5, '0'), 'Ombrea', ARRAY['Architecture des ordinateurs', 'Communication', 'Droit'], '2025-04-01', 'alternance', '2025-11-01', 'Configuration d''une infrastructure réseau sécurisée', '26 Esplanade Capitainerie 13600 La Ciotat', 'r21000087', NULL, 'henri.delorme');
INSERT INTO Internship (internship_identifier, company_name, keywords, start_date_internship, type, end_date_internship, internship_subject, address, student_number, relevance_score, id_teacher) VALUES ('ALT' || '2026' || LPAD(NEXTVAL('Internship_id_counter_seq')::TEXT, 5, '0'), 'Purjus', ARRAY['Marketing'], '2026-04-01', 'alternance', '2026-09-08', 'Configuration d''une infrastructure réseau sécurisée', '32 Boulevard de l''Oli 06340 Nice', 'z23000088', NULL, 'lucas.royer');
INSERT INTO Internship (internship_identifier, company_name, keywords, start_date_internship, type, end_date_internship, internship_subject, address, student_number, relevance_score, id_teacher) VALUES ('ALT' || '2024' || LPAD(NEXTVAL('Internship_id_counter_seq')::TEXT, 5, '0'), 'Safran', ARRAY['Programmation web', 'Économie', 'Communication'], '2024-04-01', 'alternance', '2025-03-11', 'Création d''un dashboard de visualisation de données', '3 Boulevard Glanum 13300 Salon-de-Provence', 'b19000089', NULL, 'marie-laure.roux');
INSERT INTO Internship (internship_identifier, company_name, keywords, start_date_internship, type, end_date_internship, internship_subject, address, student_number, relevance_score, id_teacher) VALUES ('ALT' || '2024' || LPAD(NEXTVAL('Internship_id_counter_seq')::TEXT, 5, '0'), 'ImCheck Therapeutics', ARRAY['Communication digitale'], '2024-04-01', 'alternance', '2024-09-22', 'Optimisation de requêtes base de données', '2 Rue Boris Vian 13400 La-Penne-sur-Huveaune', 'd23000090', NULL, 'lucas.royer');
INSERT INTO Internship (internship_identifier, company_name, keywords, start_date_internship, type, end_date_internship, internship_subject, address, student_number, relevance_score, id_teacher) VALUES ('ALT' || '2024' || LPAD(NEXTVAL('Internship_id_counter_seq')::TEXT, 5, '0'), 'Amazon Web Services', ARRAY['Communication digitale'], '2024-04-01', 'alternance', '2025-02-25', 'Automatisation des déploiements applicatifs', '49 Boulevard de l''Europe 13530 Trets', 'h22000091', NULL, 'emilie.martin');
INSERT INTO Internship (internship_identifier, company_name, keywords, start_date_internship, type, end_date_internship, internship_subject, address, student_number, relevance_score, id_teacher) VALUES ('ALT' || '2024' || LPAD(NEXTVAL('Internship_id_counter_seq')::TEXT, 5, '0'), 'Apple', ARRAY['Marketing'], '2024-04-01', 'alternance', '2024-09-17', 'Création d''un dashboard de visualisation de données', '15 Avenue du Golfe 13110 Fos-sur-Mer', 'b23000092', NULL, 'samira.othmani');
INSERT INTO Internship (internship_identifier, company_name, keywords, start_date_internship, type, end_date_internship, internship_subject, address, student_number, relevance_score, id_teacher) VALUES ('ALT' || '2026' || LPAD(NEXTVAL('Internship_id_counter_seq')::TEXT, 5, '0'), 'Capgemini', ARRAY['Marketing', 'Programmation web'], '2026-04-01', 'alternance', '2027-02-18', 'Audit de sécurité d''une application web', '45 Rue Raymond Filippi 13626 Aix-en-Provence', 'k23000093', NULL, 'helene.vidal');
INSERT INTO Internship (internship_identifier, company_name, keywords, start_date_internship, type, end_date_internship, internship_subject, address, student_number, relevance_score, id_teacher) VALUES ('ALT' || '2026' || LPAD(NEXTVAL('Internship_id_counter_seq')::TEXT, 5, '0'), 'ImCheck Therapeutics', ARRAY['Vente et négociation', 'Gestion financière', 'Programmation orientée objet'], '2026-04-01', 'alternance', '2027-03-19', 'Automatisation des déploiements applicatifs', '23 Boulevard du Bocage 13821 La-Penne-sur-Huveaune', 'l20000094', NULL, 'clara.dupont');
INSERT INTO Internship (internship_identifier, company_name, keywords, start_date_internship, type, end_date_internship, internship_subject, address, student_number, relevance_score, id_teacher) VALUES ('ALT' || '2023' || LPAD(NEXTVAL('Internship_id_counter_seq')::TEXT, 5, '0'), 'Crosscall', ARRAY['Économie', 'Gestion financière', 'Gestion de projet'], '2023-04-01', 'alternance', '2023-09-26', 'Optimisation de requêtes base de données', '4 Rue Alphonse Daudet 13820 Le Rove', 'a23000095', NULL, 'marie-laure.roux');
INSERT INTO Internship (internship_identifier, company_name, keywords, start_date_internship, type, end_date_internship, internship_subject, address, student_number, relevance_score, id_teacher) VALUES ('ALT' || '2026' || LPAD(NEXTVAL('Internship_id_counter_seq')::TEXT, 5, '0'), 'Serès Technologies', ARRAY['Gestion de projet'], '2026-04-01', 'alternance', '2026-09-27', 'Développement d''une plateforme e-commerce', '13 Avenue de la Liberté 13380 Plan-de-Cuques', 'g22000096', NULL, 'laura.costa');
INSERT INTO Internship (internship_identifier, company_name, keywords, start_date_internship, type, end_date_internship, internship_subject, address, student_number, relevance_score, id_teacher) VALUES ('ALT' || '2023' || LPAD(NEXTVAL('Internship_id_counter_seq')::TEXT, 5, '0'), 'Dassault Aviation', ARRAY['Gestion financière', 'Économie', 'Gestion de bases de données'], '2023-04-01', 'alternance', '2024-03-28', 'Audit de sécurité d''une application web', '45 Rue Gavaudan 13004 Marseille', 'e23000097', NULL, 'chloe.lefebvre');
INSERT INTO Internship (internship_identifier, company_name, keywords, start_date_internship, type, end_date_internship, internship_subject, address, student_number, relevance_score, id_teacher) VALUES ('ALT' || '2024' || LPAD(NEXTVAL('Internship_id_counter_seq')::TEXT, 5, '0'), 'DEV-ID Marseille', ARRAY['Gestion de bases de données'], '2024-04-01', 'alternance', '2024-10-10', 'Développement d''une plateforme e-commerce', '18 Rue Picot 83000 Toulon', 'm21000098', NULL, 'jean-luc.bernard');
INSERT INTO Internship (internship_identifier, company_name, keywords, start_date_internship, type, end_date_internship, internship_subject, address, student_number, relevance_score, id_teacher) VALUES ('ALT' || '2026' || LPAD(NEXTVAL('Internship_id_counter_seq')::TEXT, 5, '0'), 'Orange', ARRAY['Anglais', 'Programmation orientée objet', 'Programmation web'], '2026-04-01', 'alternance', '2027-03-17', 'Optimisation de requêtes base de données', '50 Boulevard Paul 13190 Plan-de-Cuques', 'd23000099', NULL, 'lucas.royer');
INSERT INTO Internship (internship_identifier, company_name, keywords, start_date_internship, type, end_date_internship, internship_subject, address, student_number, relevance_score, id_teacher) VALUES ('ALT' || '2023' || LPAD(NEXTVAL('Internship_id_counter_seq')::TEXT, 5, '0'), 'Netangels', ARRAY['Anglais', 'Droit'], '2023-04-01', 'alternance', '2023-09-09', 'Conception d''une application mobile de VTC', '5 Rue Jean Rostand 13090 Aix-en-Provence', 'j19000100', NULL, 'laura.costa');
INSERT INTO Internship (internship_identifier, company_name, keywords, start_date_internship, type, end_date_internship, internship_subject, address, student_number, relevance_score, id_teacher) VALUES ('STA' || '2025' || LPAD(NEXTVAL('Internship_id_counter_seq')::TEXT, 5, '0'), 'Renesas Electronics', ARRAY['Anglais'], '2025-04-01', 'stage', '2025-11-06', 'Audit de sécurité d''une application web', '22 Boulevard Louis Pasquet 13300 Salon-de-Provence', 'b23000101', NULL, 'marie-laure.roux');
INSERT INTO Internship (internship_identifier, company_name, keywords, start_date_internship, type, end_date_internship, internship_subject, address, student_number, relevance_score, id_teacher) VALUES ('STA' || '2023' || LPAD(NEXTVAL('Internship_id_counter_seq')::TEXT, 5, '0'), 'Capgemini', ARRAY['Marketing'], '2023-04-01', 'stage', '2024-01-17', 'Audit de sécurité d''une application web', '37 Avenue du Meunier 13260 Cassis', 'n23000102', NULL, 'marie-laure.roux');
INSERT INTO Internship (internship_identifier, company_name, keywords, start_date_internship, type, end_date_internship, internship_subject, address, student_number, relevance_score, id_teacher) VALUES ('STA' || '2026' || LPAD(NEXTVAL('Internship_id_counter_seq')::TEXT, 5, '0'), 'Microsoft', ARRAY['Gestion de projet', 'Communication digitale', 'Marketing'], '2026-04-01', 'stage', '2026-08-23', 'Création d''un dashboard de visualisation de données', '12 Rue des Genêts 84120 Pertuis', 'e23000103', NULL, 'francois-xavier.dupuis');
INSERT INTO Internship (internship_identifier, company_name, keywords, start_date_internship, type, end_date_internship, internship_subject, address, student_number, relevance_score, id_teacher) VALUES ('STA' || '2024' || LPAD(NEXTVAL('Internship_id_counter_seq')::TEXT, 5, '0'), 'Dassault Systèmes', ARRAY['Marketing', 'Gestion de bases de données'], '2024-04-01', 'stage', '2024-10-16', 'Configuration d''une infrastructure réseau sécurisée', '7 Allée du Verdon 13770 Venelles', 'c23000104', NULL, 'laura.costa');
INSERT INTO Internship (internship_identifier, company_name, keywords, start_date_internship, type, end_date_internship, internship_subject, address, student_number, relevance_score, id_teacher) VALUES ('STA' || '2023' || LPAD(NEXTVAL('Internship_id_counter_seq')::TEXT, 5, '0'), 'Infineon Technologies', ARRAY['Gestion de bases de données'], '2023-04-01', 'stage', '2023-12-03', 'Développement d''un chatbot intelligent', '49 Rue Francis Garnier 83000 Toulon', 'b23000105', NULL, 'lucas.royer');
INSERT INTO Internship (internship_identifier, company_name, keywords, start_date_internship, type, end_date_internship, internship_subject, address, student_number, relevance_score, id_teacher) VALUES ('STA' || '2024' || LPAD(NEXTVAL('Internship_id_counter_seq')::TEXT, 5, '0'), 'Serès Technologies', ARRAY['Programmation web'], '2024-04-01', 'stage', '2025-01-25', 'Optimisation de requêtes base de données', '43 Place du Polygone 83090 Toulon', 'd23000106', NULL, 'lea.girard');
INSERT INTO Internship (internship_identifier, company_name, keywords, start_date_internship, type, end_date_internship, internship_subject, address, student_number, relevance_score, id_teacher) VALUES ('STA' || '2024' || LPAD(NEXTVAL('Internship_id_counter_seq')::TEXT, 5, '0'), 'Dassault Aviation', ARRAY['Communication', 'Programmation web'], '2024-04-01', 'stage', '2024-08-05', 'Programmation d''un drone autonome', '18 Allée des Mimosas 13190 Allauch', 's23000107', NULL, 'chloe.lefebvre');
INSERT INTO Internship (internship_identifier, company_name, keywords, start_date_internship, type, end_date_internship, internship_subject, address, student_number, relevance_score, id_teacher) VALUES ('STA' || '2026' || LPAD(NEXTVAL('Internship_id_counter_seq')::TEXT, 5, '0'), 'Ombrea', ARRAY['Programmation orientée objet'], '2026-04-01', 'stage', '2027-02-22', 'Programmation d''un drone autonome', '45 Impasse de la Savane 13600 La Ciotat', 't23000108', NULL, 'francois-xavier.dupuis');
INSERT INTO Internship (internship_identifier, company_name, keywords, start_date_internship, type, end_date_internship, internship_subject, address, student_number, relevance_score, id_teacher) VALUES ('STA' || '2024' || LPAD(NEXTVAL('Internship_id_counter_seq')::TEXT, 5, '0'), 'Bull', ARRAY['Gestion de projet', 'Programmation web', 'Économie'], '2024-04-01', 'stage', '2025-03-20', 'Conception d''une application mobile de VTC', '48 Rue Jean d''Ormesson 13530 Trets', 'l23000109', NULL, 'marc.antoine');
INSERT INTO Internship (internship_identifier, company_name, keywords, start_date_internship, type, end_date_internship, internship_subject, address, student_number, relevance_score, id_teacher) VALUES ('STA' || '2026' || LPAD(NEXTVAL('Internship_id_counter_seq')::TEXT, 5, '0'), 'IADYS', ARRAY['Programmation orientée objet', 'Architecture des ordinateurs', 'Communication digitale'], '2026-04-01', 'stage', '2026-12-17', 'Audit de sécurité d''une application web', '45 Allée du Parc 13770 Venelles', 'b23000110', NULL, 'fatima.benali');
INSERT INTO Internship (internship_identifier, company_name, keywords, start_date_internship, type, end_date_internship, internship_subject, address, student_number, relevance_score, id_teacher) VALUES ('STA' || '2025' || LPAD(NEXTVAL('Internship_id_counter_seq')::TEXT, 5, '0'), 'Dassault Aviation', ARRAY['Économie', 'Communication digitale', 'Gestion de bases de données'], '2025-04-01', 'stage', '2025-10-23', 'Automatisation des déploiements applicatifs', '7 Allée de Mendez 13270 Fos-sur-Mer', 'm23000111', NULL, 'marc.antoine');
INSERT INTO Internship (internship_identifier, company_name, keywords, start_date_internship, type, end_date_internship, internship_subject, address, student_number, relevance_score, id_teacher) VALUES ('STA' || '2026' || LPAD(NEXTVAL('Internship_id_counter_seq')::TEXT, 5, '0'), 'Ombrea', ARRAY['Communication'], '2026-04-01', 'stage', '2027-02-11', 'Programmation d''un drone autonome', '38 Place Vivaux 13002 Marseille', 'z23000112', NULL, 'sergei.ivanov');
INSERT INTO Internship (internship_identifier, company_name, keywords, start_date_internship, type, end_date_internship, internship_subject, address, student_number, relevance_score, id_teacher) VALUES ('STA' || '2024' || LPAD(NEXTVAL('Internship_id_counter_seq')::TEXT, 5, '0'), 'Bouygues Telecom', ARRAY['Gestion de bases de données', 'Gestion financière', 'Économie'], '2024-04-01', 'stage', '2024-11-03', 'Automatisation des déploiements applicatifs', '10 Avenue Villermont 06000 Nice', 'd23000113', NULL, 'fatima.benali');
INSERT INTO Internship (internship_identifier, company_name, keywords, start_date_internship, type, end_date_internship, internship_subject, address, student_number, relevance_score, id_teacher) VALUES ('STA' || '2023' || LPAD(NEXTVAL('Internship_id_counter_seq')::TEXT, 5, '0'), 'Safran', ARRAY['Anglais', 'Communication'], '2023-04-01', 'stage', '2023-07-18', 'Création d''un dashboard de visualisation de données', '4 Impasse du Verger 13600 La Ciotat', 't23000114', NULL, 'clara.dupont');
INSERT INTO Internship (internship_identifier, company_name, keywords, start_date_internship, type, end_date_internship, internship_subject, address, student_number, relevance_score, id_teacher) VALUES ('STA' || '2024' || LPAD(NEXTVAL('Internship_id_counter_seq')::TEXT, 5, '0'), 'Google', ARRAY['Anglais', 'Architecture des ordinateurs'], '2024-04-01', 'stage', '2025-02-17', 'Automatisation des déploiements applicatifs', '14 Rue Samat Mikaelly 84120 Pertuis', 'm23000115', NULL, 'laura.costa');
INSERT INTO Internship (internship_identifier, company_name, keywords, start_date_internship, type, end_date_internship, internship_subject, address, student_number, relevance_score, id_teacher) VALUES ('STA' || '2025' || LPAD(NEXTVAL('Internship_id_counter_seq')::TEXT, 5, '0'), 'Pharm’Aging', ARRAY['Vente et négociation', 'Anglais', 'Gestion de projet'], '2025-04-01', 'stage', '2025-11-08', 'Optimisation de requêtes base de données', '42 Allée de Saint-Hippolyte 13770 Venelles', 'm23000116', NULL, 'philippe.marchand');
INSERT INTO Internship (internship_identifier, company_name, keywords, start_date_internship, type, end_date_internship, internship_subject, address, student_number, relevance_score, id_teacher) VALUES ('STA' || '2026' || LPAD(NEXTVAL('Internship_id_counter_seq')::TEXT, 5, '0'), 'Crosscall', ARRAY['Communication digitale', 'Marketing', 'Programmation web'], '2026-04-01', 'stage', '2026-11-30', 'Conception d''une application mobile de VTC', '16 Rue Arnould 13400 Aubagne', 'a23000117', NULL, 'marie-laure.roux');
INSERT INTO Internship (internship_identifier, company_name, keywords, start_date_internship, type, end_date_internship, internship_subject, address, student_number, relevance_score, id_teacher) VALUES ('STA' || '2024' || LPAD(NEXTVAL('Internship_id_counter_seq')::TEXT, 5, '0'), 'Omniciel', ARRAY['Gestion financière', 'Anglais'], '2024-04-01', 'stage', '2024-10-25', 'Conception d''une application mobile de VTC', '27 Allée de la Scierie 13400 Aubagne', 'p23000118', NULL, 'sofia.rossi');
INSERT INTO Internship (internship_identifier, company_name, keywords, start_date_internship, type, end_date_internship, internship_subject, address, student_number, relevance_score, id_teacher) VALUES ('STA' || '2026' || LPAD(NEXTVAL('Internship_id_counter_seq')::TEXT, 5, '0'), 'Vertical Expense', ARRAY['Vente et négociation', 'Architecture des ordinateurs', 'Communication'], '2026-04-01', 'stage', '2026-10-15', 'Configuration d''une infrastructure réseau sécurisée', '30 Boulevard Rey 83470 Saint-Maximin-la-Sainte-Baume', 'e23000119', NULL, 'pierre-henri.blanc');
INSERT INTO Internship (internship_identifier, company_name, keywords, start_date_internship, type, end_date_internship, internship_subject, address, student_number, relevance_score, id_teacher) VALUES ('STA' || '2024' || LPAD(NEXTVAL('Internship_id_counter_seq')::TEXT, 5, '0'), 'Apple', ARRAY['Anglais', 'Programmation web'], '2024-04-01', 'stage', '2024-07-04', 'Développement d''un chatbot intelligent', '40 Rue Gérard de Nerval 13400 La-Penne-sur-Huveaune', 'l23000120', NULL, 'emilie.martin');
INSERT INTO Internship (internship_identifier, company_name, keywords, start_date_internship, type, end_date_internship, internship_subject, address, student_number, relevance_score, id_teacher) VALUES ('STA' || '2024' || LPAD(NEXTVAL('Internship_id_counter_seq')::TEXT, 5, '0'), 'Infineon Technologies', ARRAY['Économie', 'Anglais', 'Programmation orientée objet'], '2024-04-01', 'stage', '2025-03-20', 'Automatisation des déploiements applicatifs', '38 Avenue Lazare Carnot 83097 Toulon', 'k23000121', NULL, 'laura.costa');
INSERT INTO Internship (internship_identifier, company_name, keywords, start_date_internship, type, end_date_internship, internship_subject, address, student_number, relevance_score, id_teacher) VALUES ('STA' || '2023' || LPAD(NEXTVAL('Internship_id_counter_seq')::TEXT, 5, '0'), 'Vertical Expense', ARRAY['Économie', 'Gestion financière', 'Marketing'], '2023-04-01', 'stage', '2023-10-10', 'Configuration d''une infrastructure réseau sécurisée', '50 Boulevard Fructidor 13013 Plan-de-Cuques', 'b23000122', NULL, 'mehdi.taha');
INSERT INTO Internship (internship_identifier, company_name, keywords, start_date_internship, type, end_date_internship, internship_subject, address, student_number, relevance_score, id_teacher) VALUES ('STA' || '2025' || LPAD(NEXTVAL('Internship_id_counter_seq')::TEXT, 5, '0'), 'Siemens', ARRAY['Programmation web'], '2025-04-01', 'stage', '2026-02-02', 'Conception d''une application mobile de VTC', '15 Boulevard Maritime 13500 Martigues', 'n23000123', NULL, 'fatima.benali');
INSERT INTO Internship (internship_identifier, company_name, keywords, start_date_internship, type, end_date_internship, internship_subject, address, student_number, relevance_score, id_teacher) VALUES ('STA' || '2024' || LPAD(NEXTVAL('Internship_id_counter_seq')::TEXT, 5, '0'), 'Orange', ARRAY['Marketing', 'Anglais'], '2024-04-01', 'stage', '2025-02-18', 'Automatisation des déploiements applicatifs', '42 Avenue Antoine Galante 06284 Nice', 'd23000124', NULL, 'marie-laure.roux');
INSERT INTO Internship (internship_identifier, company_name, keywords, start_date_internship, type, end_date_internship, internship_subject, address, student_number, relevance_score, id_teacher) VALUES ('STA' || '2026' || LPAD(NEXTVAL('Internship_id_counter_seq')::TEXT, 5, '0'), 'Apple', ARRAY['Marketing', 'Gestion de projet'], '2026-04-01', 'stage', '2026-11-05', 'Configuration d''une infrastructure réseau sécurisée', '48 Boulevard Tristan Corbière 13012 Marseille', 'l23000125', NULL, 'alexandre.dubois');
INSERT INTO Internship (internship_identifier, company_name, keywords, start_date_internship, type, end_date_internship, internship_subject, address, student_number, relevance_score, id_teacher) VALUES ('STA' || '2026' || LPAD(NEXTVAL('Internship_id_counter_seq')::TEXT, 5, '0'), 'Purjus', ARRAY['Vente et négociation', 'Programmation web'], '2026-04-01', 'stage', '2027-03-18', 'Programmation d''un drone autonome', '45 Place de l''Église 13420 Aubagne', 'a23000126', NULL, 'chloe.lefebvre');
INSERT INTO Internship (internship_identifier, company_name, keywords, start_date_internship, type, end_date_internship, internship_subject, address, student_number, relevance_score, id_teacher) VALUES ('STA' || '2024' || LPAD(NEXTVAL('Internship_id_counter_seq')::TEXT, 5, '0'), 'Altran', ARRAY['Programmation web', 'Programmation orientée objet', 'Architecture des ordinateurs'], '2024-04-01', 'stage', '2024-12-20', 'Optimisation de requêtes base de données', '2 Rue des Tamaris 13920 Martigues', 'r23000127', NULL, 'mehdi.taha');
INSERT INTO Internship (internship_identifier, company_name, keywords, start_date_internship, type, end_date_internship, internship_subject, address, student_number, relevance_score, id_teacher) VALUES ('STA' || '2023' || LPAD(NEXTVAL('Internship_id_counter_seq')::TEXT, 5, '0'), 'Altran', ARRAY['Communication', 'Gestion de bases de données', 'Architecture des ordinateurs'], '2023-04-01', 'stage', '2024-03-15', 'Programmation d''un drone autonome', '34 Avenue Pierre Mendès France 13008 Marseille', 'b23000128', NULL, 'francois-xavier.dupuis');
INSERT INTO Internship (internship_identifier, company_name, keywords, start_date_internship, type, end_date_internship, internship_subject, address, student_number, relevance_score, id_teacher) VALUES ('STA' || '2024' || LPAD(NEXTVAL('Internship_id_counter_seq')::TEXT, 5, '0'), 'Airbus', ARRAY['Programmation orientée objet', 'Anglais'], '2024-04-01', 'stage', '2024-11-02', 'Création d''un dashboard de visualisation de données', '24 Allée des Glycines 13400 Aubagne', 'k23000129', NULL, 'anais.petit');
INSERT INTO Internship (internship_identifier, company_name, keywords, start_date_internship, type, end_date_internship, internship_subject, address, student_number, relevance_score, id_teacher) VALUES ('STA' || '2025' || LPAD(NEXTVAL('Internship_id_counter_seq')::TEXT, 5, '0'), 'IBM', ARRAY['Marketing', 'Architecture des ordinateurs', 'Programmation orientée objet'], '2025-04-01', 'stage', '2025-07-26', 'Conception d''une application mobile de VTC', '3 Quai aux Charbons 13002 Marseille', 'p23000130', NULL, 'marc.antoine');
INSERT INTO Internship (internship_identifier, company_name, keywords, start_date_internship, type, end_date_internship, internship_subject, address, student_number, relevance_score, id_teacher) VALUES ('STA' || '2025' || LPAD(NEXTVAL('Internship_id_counter_seq')::TEXT, 5, '0'), 'Infineon Technologies', ARRAY['Gestion de projet'], '2025-04-01', 'stage', '2026-02-14', 'Pilotage d''un projet de refonte SI', '1 Rue Claude Chappe 13500 Martigues', 'd23000131', NULL, 'clara.dupont');
INSERT INTO Internship (internship_identifier, company_name, keywords, start_date_internship, type, end_date_internship, internship_subject, address, student_number, relevance_score, id_teacher) VALUES ('STA' || '2023' || LPAD(NEXTVAL('Internship_id_counter_seq')::TEXT, 5, '0'), 'Thales', ARRAY['Gestion de projet', 'Gestion financière'], '2023-04-01', 'stage', '2023-11-22', 'Conception d''une application mobile de VTC', '4 Rue Adolphe Bony 83097 Toulon', 'm23000132', NULL, 'laura.costa');
INSERT INTO Internship (internship_identifier, company_name, keywords, start_date_internship, type, end_date_internship, internship_subject, address, student_number, relevance_score, id_teacher) VALUES ('STA' || '2023' || LPAD(NEXTVAL('Internship_id_counter_seq')::TEXT, 5, '0'), 'Remmedia', ARRAY['Gestion de projet', 'Vente et négociation'], '2023-04-01', 'stage', '2024-03-02', 'Configuration d''une infrastructure réseau sécurisée', '9 Impasse La Remise 13400 Aubagne', 'i23000133', NULL, 'samira.othmani');
INSERT INTO Internship (internship_identifier, company_name, keywords, start_date_internship, type, end_date_internship, internship_subject, address, student_number, relevance_score, id_teacher) VALUES ('STA' || '2023' || LPAD(NEXTVAL('Internship_id_counter_seq')::TEXT, 5, '0'), 'Amazon Web Services', ARRAY['Gestion de bases de données', 'Gestion financière', 'Architecture des ordinateurs'], '2023-04-01', 'stage', '2023-08-31', 'Pilotage d''un projet de refonte SI', '41 Allée de Mendez 13270 Fos-sur-Mer', 'a23000134', NULL, 'henri.delorme');
INSERT INTO Internship (internship_identifier, company_name, keywords, start_date_internship, type, end_date_internship, internship_subject, address, student_number, relevance_score, id_teacher) VALUES ('STA' || '2024' || LPAD(NEXTVAL('Internship_id_counter_seq')::TEXT, 5, '0'), 'ImCheck Therapeutics', ARRAY['Droit'], '2024-04-01', 'stage', '2025-02-13', 'Création d''un dashboard de visualisation de données', '40 Impasse de la Roche Percée 13500 Martigues', 's23000135', NULL, 'philippe.marchand');
INSERT INTO Internship (internship_identifier, company_name, keywords, start_date_internship, type, end_date_internship, internship_subject, address, student_number, relevance_score, id_teacher) VALUES ('STA' || '2026' || LPAD(NEXTVAL('Internship_id_counter_seq')::TEXT, 5, '0'), 'Renault', ARRAY['Gestion financière'], '2026-04-01', 'stage', '2026-11-15', 'Automatisation des déploiements applicatifs', '23 Boulevard Voltaire 13821 La-Penne-sur-Huveaune', 'm23000136', NULL, 'philippe.marchand');
INSERT INTO Internship (internship_identifier, company_name, keywords, start_date_internship, type, end_date_internship, internship_subject, address, student_number, relevance_score, id_teacher) VALUES ('STA' || '2026' || LPAD(NEXTVAL('Internship_id_counter_seq')::TEXT, 5, '0'), 'Pharm’Aging', ARRAY['Programmation orientée objet', 'Architecture des ordinateurs', 'Vente et négociation'], '2026-04-01', 'stage', '2026-12-17', 'Développement d''un chatbot intelligent', '27 Impasse du Grand Cros 84120 Pertuis', 'b23000137', NULL, 'henri.delorme');
INSERT INTO Internship (internship_identifier, company_name, keywords, start_date_internship, type, end_date_internship, internship_subject, address, student_number, relevance_score, id_teacher) VALUES ('STA' || '2026' || LPAD(NEXTVAL('Internship_id_counter_seq')::TEXT, 5, '0'), 'TUITO', ARRAY['Marketing', 'Communication', 'Gestion financière'], '2026-04-01', 'stage', '2026-07-31', 'Programmation d''un drone autonome', '37 Boulevard des Tamaris 13400 Aubagne', 'r23000138', NULL, 'laurent.moreau');
INSERT INTO Internship (internship_identifier, company_name, keywords, start_date_internship, type, end_date_internship, internship_subject, address, student_number, relevance_score, id_teacher) VALUES ('STA' || '2026' || LPAD(NEXTVAL('Internship_id_counter_seq')::TEXT, 5, '0'), 'Netangels', ARRAY['Marketing', 'Architecture des ordinateurs'], '2026-04-01', 'stage', '2026-12-13', 'Conception d''une application mobile de VTC', '14 Avenue de Toulon 13120 Gardanne', 'e23000139', NULL, 'jean-luc.bernard');
INSERT INTO Internship (internship_identifier, company_name, keywords, start_date_internship, type, end_date_internship, internship_subject, address, student_number, relevance_score, id_teacher) VALUES ('STA' || '2026' || LPAD(NEXTVAL('Internship_id_counter_seq')::TEXT, 5, '0'), 'Purjus', ARRAY['Anglais'], '2026-04-01', 'stage', '2027-01-27', 'Conception d''une application mobile de VTC', '42 Rue des Tisserands 84120 Pertuis', 'l23000140', NULL, 'philippe.marchand');
INSERT INTO Internship (internship_identifier, company_name, keywords, start_date_internship, type, end_date_internship, internship_subject, address, student_number, relevance_score, id_teacher) VALUES ('STA' || '2025' || LPAD(NEXTVAL('Internship_id_counter_seq')::TEXT, 5, '0'), 'Serès Technologies', ARRAY['Programmation web'], '2025-04-01', 'stage', '2026-01-20', 'Développement d''un chatbot intelligent', '22 Rue Jules Bouilloud 13270 Fos-sur-Mer', 'b23000141', NULL, 'marc.antoine');
INSERT INTO Internship (internship_identifier, company_name, keywords, start_date_internship, type, end_date_internship, internship_subject, address, student_number, relevance_score, id_teacher) VALUES ('STA' || '2026' || LPAD(NEXTVAL('Internship_id_counter_seq')::TEXT, 5, '0'), 'Amazon Web Services', ARRAY['Programmation web'], '2026-04-01', 'stage', '2027-01-06', 'Création d''un dashboard de visualisation de données', '35 Impasse Mercure 13011 La-Penne-sur-Huveaune', 'f23000142', NULL, 'alexandre.dubois');
INSERT INTO Internship (internship_identifier, company_name, keywords, start_date_internship, type, end_date_internship, internship_subject, address, student_number, relevance_score, id_teacher) VALUES ('STA' || '2026' || LPAD(NEXTVAL('Internship_id_counter_seq')::TEXT, 5, '0'), 'Microsoft', ARRAY['Vente et négociation', 'Droit'], '2026-04-01', 'stage', '2027-01-16', 'Développement d''une plateforme e-commerce', '21 Impasse des Campanules 13180 Le Rove', 'c23000143', NULL, 'samira.othmani');
INSERT INTO Internship (internship_identifier, company_name, keywords, start_date_internship, type, end_date_internship, internship_subject, address, student_number, relevance_score, id_teacher) VALUES ('STA' || '2023' || LPAD(NEXTVAL('Internship_id_counter_seq')::TEXT, 5, '0'), 'Thales', ARRAY['Économie', 'Gestion financière'], '2023-04-01', 'stage', '2023-10-07', 'Création d''un dashboard de visualisation de données', '38 Rue Louis Lépine 13500 Martigues', 'g23000144', NULL, 'marc.antoine');
INSERT INTO Internship (internship_identifier, company_name, keywords, start_date_internship, type, end_date_internship, internship_subject, address, student_number, relevance_score, id_teacher) VALUES ('STA' || '2025' || LPAD(NEXTVAL('Internship_id_counter_seq')::TEXT, 5, '0'), 'Capgemini', ARRAY['Anglais', 'Programmation web'], '2025-04-01', 'stage', '2026-03-13', 'Création d''un dashboard de visualisation de données', '12 Allée Amaury de la Grange 13300 Salon-de-Provence', 'h23000145', NULL, 'helene.vidal');
INSERT INTO Internship (internship_identifier, company_name, keywords, start_date_internship, type, end_date_internship, internship_subject, address, student_number, relevance_score, id_teacher) VALUES ('STA' || '2026' || LPAD(NEXTVAL('Internship_id_counter_seq')::TEXT, 5, '0'), 'Renault', ARRAY['Programmation web'], '2026-04-01', 'stage', '2026-11-22', 'Développement d''une plateforme e-commerce', '5 Place du Château 13270 Fos-sur-Mer', 'z23000146', NULL, 'sofia.rossi');
INSERT INTO Internship (internship_identifier, company_name, keywords, start_date_internship, type, end_date_internship, internship_subject, address, student_number, relevance_score, id_teacher) VALUES ('STA' || '2024' || LPAD(NEXTVAL('Internship_id_counter_seq')::TEXT, 5, '0'), 'Vertical Expense', ARRAY['Marketing', 'Gestion financière', 'Gestion de bases de données'], '2024-04-01', 'stage', '2024-07-23', 'Configuration d''une infrastructure réseau sécurisée', '5 Rue de l''Égalité 13400 Aubagne', 'l23000147', NULL, 'marie-laure.roux');
INSERT INTO Internship (internship_identifier, company_name, keywords, start_date_internship, type, end_date_internship, internship_subject, address, student_number, relevance_score, id_teacher) VALUES ('STA' || '2025' || LPAD(NEXTVAL('Internship_id_counter_seq')::TEXT, 5, '0'), 'Free', ARRAY['Gestion de bases de données', 'Économie'], '2025-04-01', 'stage', '2026-03-10', 'Automatisation des déploiements applicatifs', '10 Boulevard de l''Observatoire 06300 Nice', 'b23000148', NULL, 'chloe.lefebvre');
INSERT INTO Internship (internship_identifier, company_name, keywords, start_date_internship, type, end_date_internship, internship_subject, address, student_number, relevance_score, id_teacher) VALUES ('STA' || '2025' || LPAD(NEXTVAL('Internship_id_counter_seq')::TEXT, 5, '0'), 'Texas Instruments', ARRAY['Gestion de projet', 'Architecture des ordinateurs'], '2025-04-01', 'stage', '2025-07-08', 'Configuration d''une infrastructure réseau sécurisée', '27 Impasse des Palmiers 13400 Aubagne', 'd23000149', NULL, 'sofia.rossi');
INSERT INTO Internship (internship_identifier, company_name, keywords, start_date_internship, type, end_date_internship, internship_subject, address, student_number, relevance_score, id_teacher) VALUES ('STA' || '2023' || LPAD(NEXTVAL('Internship_id_counter_seq')::TEXT, 5, '0'), 'Akka Technologies', ARRAY['Économie', 'Gestion financière', 'Marketing'], '2023-04-01', 'stage', '2024-02-02', 'Audit de sécurité d''une application web', '13 Allée des Jardins du Papillon 13770 Venelles', 'e23000150', NULL, 'emilie.martin');
INSERT INTO Internship (internship_identifier, company_name, keywords, start_date_internship, type, end_date_internship, internship_subject, address, student_number, relevance_score, id_teacher) VALUES ('STA' || '2025' || LPAD(NEXTVAL('Internship_id_counter_seq')::TEXT, 5, '0'), 'Infineon Technologies', ARRAY['Anglais'], '2025-04-01', 'stage', '2025-11-13', 'Programmation d''un drone autonome', '43 Boulevard du Béal 13821 La-Penne-sur-Huveaune', 'b23000151', NULL, 'youssef.el-mansouri');
INSERT INTO Internship (internship_identifier, company_name, keywords, start_date_internship, type, end_date_internship, internship_subject, address, student_number, relevance_score, id_teacher) VALUES ('STA' || '2024' || LPAD(NEXTVAL('Internship_id_counter_seq')::TEXT, 5, '0'), 'Schneider Electric', ARRAY['Communication digitale', 'Gestion de bases de données', 'Droit'], '2024-04-01', 'stage', '2024-09-30', 'Audit de sécurité d''une application web', '30 Rue Max Bacharetti 13270 Fos-sur-Mer', 'k23000152', NULL, 'fatima.benali');
INSERT INTO Internship (internship_identifier, company_name, keywords, start_date_internship, type, end_date_internship, internship_subject, address, student_number, relevance_score, id_teacher) VALUES ('STA' || '2023' || LPAD(NEXTVAL('Internship_id_counter_seq')::TEXT, 5, '0'), 'Renesas Electronics', ARRAY['Gestion de bases de données'], '2023-04-01', 'stage', '2023-11-18', 'Optimisation de requêtes base de données', '24 Rue du Maréchal Gallieni 13470 Cassis', 'd23000153', NULL, 'henri.delorme');
INSERT INTO Internship (internship_identifier, company_name, keywords, start_date_internship, type, end_date_internship, internship_subject, address, student_number, relevance_score, id_teacher) VALUES ('STA' || '2023' || LPAD(NEXTVAL('Internship_id_counter_seq')::TEXT, 5, '0'), 'Purjus', ARRAY['Vente et négociation', 'Gestion de bases de données', 'Marketing'], '2023-04-01', 'stage', '2023-08-02', 'Audit de sécurité d''une application web', '34 Avenue de la Bartavello 13470 Aubagne', 'b23000154', NULL, 'amina.kebir');
INSERT INTO Internship (internship_identifier, company_name, keywords, start_date_internship, type, end_date_internship, internship_subject, address, student_number, relevance_score, id_teacher) VALUES ('STA' || '2024' || LPAD(NEXTVAL('Internship_id_counter_seq')::TEXT, 5, '0'), 'Omniciel', ARRAY['Anglais', 'Programmation orientée objet', 'Programmation web'], '2024-04-01', 'stage', '2024-07-28', 'Audit de sécurité d''une application web', '20 Rue Saint-Roch 84120 Pertuis', 'c23000155', NULL, 'helene.vidal');
INSERT INTO Internship (internship_identifier, company_name, keywords, start_date_internship, type, end_date_internship, internship_subject, address, student_number, relevance_score, id_teacher) VALUES ('STA' || '2023' || LPAD(NEXTVAL('Internship_id_counter_seq')::TEXT, 5, '0'), 'Safran', ARRAY['Marketing'], '2023-04-01', 'stage', '2024-01-21', 'Pilotage d''un projet de refonte SI', '2 Rue Antoine Maurel 13300 Salon-de-Provence', 'f23000156', NULL, 'sergei.ivanov');
INSERT INTO Internship (internship_identifier, company_name, keywords, start_date_internship, type, end_date_internship, internship_subject, address, student_number, relevance_score, id_teacher) VALUES ('STA' || '2025' || LPAD(NEXTVAL('Internship_id_counter_seq')::TEXT, 5, '0'), 'Orange', ARRAY['Gestion de bases de données', 'Communication digitale'], '2025-04-01', 'stage', '2025-10-15', 'Pilotage d''un projet de refonte SI', '8 Allée des Romarins 13190 Allauch', 'm23000157', NULL, 'lucas.royer');
INSERT INTO Internship (internship_identifier, company_name, keywords, start_date_internship, type, end_date_internship, internship_subject, address, student_number, relevance_score, id_teacher) VALUES ('STA' || '2026' || LPAD(NEXTVAL('Internship_id_counter_seq')::TEXT, 5, '0'), 'Infineon Technologies', ARRAY['Communication digitale', 'Économie'], '2026-04-01', 'stage', '2027-01-10', 'Conception d''une application mobile de VTC', '19 Avenue Ambroise Croizat 13120 Gardanne', 't23000158', NULL, 'philippe.marchand');
INSERT INTO Internship (internship_identifier, company_name, keywords, start_date_internship, type, end_date_internship, internship_subject, address, student_number, relevance_score, id_teacher) VALUES ('STA' || '2024' || LPAD(NEXTVAL('Internship_id_counter_seq')::TEXT, 5, '0'), 'Serès Technologies', ARRAY['Économie', 'Communication', 'Vente et négociation'], '2024-04-01', 'stage', '2024-08-07', 'Automatisation des déploiements applicatifs', '31 Avenue des Fuchsias 13120 Gardanne', 'l23000159', NULL, 'mehdi.taha');
INSERT INTO Internship (internship_identifier, company_name, keywords, start_date_internship, type, end_date_internship, internship_subject, address, student_number, relevance_score, id_teacher) VALUES ('STA' || '2026' || LPAD(NEXTVAL('Internship_id_counter_seq')::TEXT, 5, '0'), 'TUITO', ARRAY['Architecture des ordinateurs', 'Droit'], '2026-04-01', 'stage', '2026-11-28', 'Optimisation de requêtes base de données', '10 Allée du Clos Siméon 13530 Trets', 'a23000160', NULL, 'lucas.royer');
INSERT INTO Internship (internship_identifier, company_name, keywords, start_date_internship, type, end_date_internship, internship_subject, address, student_number, relevance_score, id_teacher) VALUES ('STA' || '2026' || LPAD(NEXTVAL('Internship_id_counter_seq')::TEXT, 5, '0'), 'Stellantis', ARRAY['Communication digitale', 'Économie'], '2026-04-01', 'stage', '2026-07-17', 'Optimisation de requêtes base de données', '47 Impasse des Albizias 13400 La-Penne-sur-Huveaune', 'z23000161', NULL, 'sergei.ivanov');
INSERT INTO Internship (internship_identifier, company_name, keywords, start_date_internship, type, end_date_internship, internship_subject, address, student_number, relevance_score, id_teacher) VALUES ('STA' || '2025' || LPAD(NEXTVAL('Internship_id_counter_seq')::TEXT, 5, '0'), 'Sopra Steria', ARRAY['Programmation orientée objet'], '2025-04-01', 'stage', '2025-09-07', 'Création d''un dashboard de visualisation de données', '23 Rue Diderot 06000 Nice', 'd23000162', NULL, 'nadia.cherif');
INSERT INTO Internship (internship_identifier, company_name, keywords, start_date_internship, type, end_date_internship, internship_subject, address, student_number, relevance_score, id_teacher) VALUES ('STA' || '2025' || LPAD(NEXTVAL('Internship_id_counter_seq')::TEXT, 5, '0'), 'Crosscall', ARRAY['Communication', 'Architecture des ordinateurs', 'Gestion de bases de données'], '2025-04-01', 'stage', '2026-03-14', 'Automatisation des déploiements applicatifs', '1 Avenue de Béarn 06300 Nice', 's23000163', NULL, 'laura.costa');
INSERT INTO Internship (internship_identifier, company_name, keywords, start_date_internship, type, end_date_internship, internship_subject, address, student_number, relevance_score, id_teacher) VALUES ('STA' || '2023' || LPAD(NEXTVAL('Internship_id_counter_seq')::TEXT, 5, '0'), 'Crosscall', ARRAY['Anglais'], '2023-04-01', 'stage', '2023-12-27', 'Programmation d''un drone autonome', '22 Rue Bailli de Suffren 13380 Plan-de-Cuques', 'o23000164', NULL, 'alexandre.dubois');
INSERT INTO Internship (internship_identifier, company_name, keywords, start_date_internship, type, end_date_internship, internship_subject, address, student_number, relevance_score, id_teacher) VALUES ('STA' || '2025' || LPAD(NEXTVAL('Internship_id_counter_seq')::TEXT, 5, '0'), 'Airbus', ARRAY['Marketing', 'Communication digitale', 'Gestion de bases de données'], '2025-04-01', 'stage', '2025-07-15', 'Création d''un dashboard de visualisation de données', '18 Allée des Logis du Brunet 13600 La Ciotat', 'g23000165', NULL, 'chloe.lefebvre');
INSERT INTO Internship (internship_identifier, company_name, keywords, start_date_internship, type, end_date_internship, internship_subject, address, student_number, relevance_score, id_teacher) VALUES ('STA' || '2025' || LPAD(NEXTVAL('Internship_id_counter_seq')::TEXT, 5, '0'), 'Dassault Aviation', ARRAY['Communication digitale', 'Vente et négociation', 'Gestion financière'], '2025-04-01', 'stage', '2025-07-22', 'Audit de sécurité d''une application web', '14 Avenue Pierre Emmanuel 06100 Nice', 'b23000166', NULL, 'marie-laure.roux');
INSERT INTO Internship (internship_identifier, company_name, keywords, start_date_internship, type, end_date_internship, internship_subject, address, student_number, relevance_score, id_teacher) VALUES ('STA' || '2025' || LPAD(NEXTVAL('Internship_id_counter_seq')::TEXT, 5, '0'), 'Alstom', ARRAY['Droit', 'Vente et négociation'], '2025-04-01', 'stage', '2025-07-03', 'Audit de sécurité d''une application web', '12 Allée des Genêts 83470 Saint-Maximin-la-Sainte-Baume', 'm23000167', NULL, 'mehdi.taha');
INSERT INTO Internship (internship_identifier, company_name, keywords, start_date_internship, type, end_date_internship, internship_subject, address, student_number, relevance_score, id_teacher) VALUES ('STA' || '2023' || LPAD(NEXTVAL('Internship_id_counter_seq')::TEXT, 5, '0'), 'Altran', ARRAY['Anglais', 'Gestion financière', 'Communication'], '2023-04-01', 'stage', '2023-10-14', 'Automatisation des déploiements applicatifs', '31 Rue Fevareu 13011 La-Penne-sur-Huveaune', 'l23000168', NULL, 'fatima.benali');
INSERT INTO Internship (internship_identifier, company_name, keywords, start_date_internship, type, end_date_internship, internship_subject, address, student_number, relevance_score, id_teacher) VALUES ('STA' || '2025' || LPAD(NEXTVAL('Internship_id_counter_seq')::TEXT, 5, '0'), 'Altran', ARRAY['Communication'], '2025-04-01', 'stage', '2025-09-22', 'Configuration d''une infrastructure réseau sécurisée', '19 Allée François Auguste Berthon 83081 Toulon', 'b23000169', NULL, 'lucas.royer');
INSERT INTO Internship (internship_identifier, company_name, keywords, start_date_internship, type, end_date_internship, internship_subject, address, student_number, relevance_score, id_teacher) VALUES ('STA' || '2024' || LPAD(NEXTVAL('Internship_id_counter_seq')::TEXT, 5, '0'), 'Thales', ARRAY['Vente et négociation', 'Droit'], '2024-04-01', 'stage', '2025-01-04', 'Pilotage d''un projet de refonte SI', '31 Rue de La Ciotat 13260 Cassis', 'm23000170', NULL, 'nadia.cherif');
INSERT INTO Internship (internship_identifier, company_name, keywords, start_date_internship, type, end_date_internship, internship_subject, address, student_number, relevance_score, id_teacher) VALUES ('STA' || '2026' || LPAD(NEXTVAL('Internship_id_counter_seq')::TEXT, 5, '0'), 'Pharm’Aging', ARRAY['Programmation orientée objet'], '2026-04-01', 'stage', '2026-12-06', 'Pilotage d''un projet de refonte SI', '48 Avenue du Revestel 13260 Cassis', 'e23000171', NULL, 'amina.kebir');
INSERT INTO Internship (internship_identifier, company_name, keywords, start_date_internship, type, end_date_internship, internship_subject, address, student_number, relevance_score, id_teacher) VALUES ('STA' || '2025' || LPAD(NEXTVAL('Internship_id_counter_seq')::TEXT, 5, '0'), 'TUITO', ARRAY['Gestion de projet', 'Gestion de bases de données'], '2025-04-01', 'stage', '2025-11-06', 'Création d''un dashboard de visualisation de données', '50 Avenue de Saint-Menet 13011 La-Penne-sur-Huveaune', 'r23000172', NULL, 'jean-luc.bernard');
INSERT INTO Internship (internship_identifier, company_name, keywords, start_date_internship, type, end_date_internship, internship_subject, address, student_number, relevance_score, id_teacher) VALUES ('STA' || '2026' || LPAD(NEXTVAL('Internship_id_counter_seq')::TEXT, 5, '0'), 'Groupe La Poste', ARRAY['Programmation orientée objet'], '2026-04-01', 'stage', '2026-07-15', 'Configuration d''une infrastructure réseau sécurisée', '26 Rue du Vallon des Auffes 13007 Marseille', 'p23000173', NULL, 'sofia.rossi');
INSERT INTO Internship (internship_identifier, company_name, keywords, start_date_internship, type, end_date_internship, internship_subject, address, student_number, relevance_score, id_teacher) VALUES ('STA' || '2025' || LPAD(NEXTVAL('Internship_id_counter_seq')::TEXT, 5, '0'), 'Renesas Electronics', ARRAY['Programmation orientée objet', 'Économie'], '2025-04-01', 'stage', '2026-01-06', 'Développement d''un chatbot intelligent', '17 Impasse de la Colombière 13109 Gardanne', 'b23000174', NULL, 'jean-luc.bernard');
INSERT INTO Internship (internship_identifier, company_name, keywords, start_date_internship, type, end_date_internship, internship_subject, address, student_number, relevance_score, id_teacher) VALUES ('STA' || '2023' || LPAD(NEXTVAL('Internship_id_counter_seq')::TEXT, 5, '0'), 'TUITO', ARRAY['Communication'], '2023-04-01', 'stage', '2023-11-08', 'Développement d''un chatbot intelligent', '38 Allée Bernadette Cattanéo 13821 La-Penne-sur-Huveaune', 'b23000175', NULL, 'sofia.rossi');
INSERT INTO Internship (internship_identifier, company_name, keywords, start_date_internship, type, end_date_internship, internship_subject, address, student_number, relevance_score, id_teacher) VALUES ('STA' || '2023' || LPAD(NEXTVAL('Internship_id_counter_seq')::TEXT, 5, '0'), 'Sopra Steria', ARRAY['Programmation web', 'Marketing', 'Architecture des ordinateurs'], '2023-04-01', 'stage', '2023-10-18', 'Conception d''une application mobile de VTC', '44 Boulevard Saint-Jean 83470 Saint-Maximin-la-Sainte-Baume', 't23000176', NULL, 'lucas.royer');
INSERT INTO Internship (internship_identifier, company_name, keywords, start_date_internship, type, end_date_internship, internship_subject, address, student_number, relevance_score, id_teacher) VALUES ('STA' || '2026' || LPAD(NEXTVAL('Internship_id_counter_seq')::TEXT, 5, '0'), 'IBM', ARRAY['Droit', 'Programmation orientée objet'], '2026-04-01', 'stage', '2027-03-17', 'Conception d''une application mobile de VTC', '6 Impasse de l''Hermitage 13600 La Ciotat', 'n23000177', NULL, 'clara.dupont');
INSERT INTO Internship (internship_identifier, company_name, keywords, start_date_internship, type, end_date_internship, internship_subject, address, student_number, relevance_score, id_teacher) VALUES ('STA' || '2025' || LPAD(NEXTVAL('Internship_id_counter_seq')::TEXT, 5, '0'), 'Omniciel', ARRAY['Droit', 'Communication digitale', 'Architecture des ordinateurs'], '2025-04-01', 'stage', '2025-07-20', 'Développement d''une plateforme e-commerce', '10 Avenue Joliot Curie 13740 Le Rove', 'h23000178', NULL, 'youssef.el-mansouri');
INSERT INTO Internship (internship_identifier, company_name, keywords, start_date_internship, type, end_date_internship, internship_subject, address, student_number, relevance_score, id_teacher) VALUES ('STA' || '2026' || LPAD(NEXTVAL('Internship_id_counter_seq')::TEXT, 5, '0'), 'Apple', ARRAY['Communication digitale', 'Gestion de projet', 'Droit'], '2026-04-01', 'stage', '2026-09-12', 'Création d''un dashboard de visualisation de données', '49 Avenue Émile Bieckert 06000 Nice', 'a23000179', NULL, 'emilie.martin');
INSERT INTO Internship (internship_identifier, company_name, keywords, start_date_internship, type, end_date_internship, internship_subject, address, student_number, relevance_score, id_teacher) VALUES ('STA' || '2026' || LPAD(NEXTVAL('Internship_id_counter_seq')::TEXT, 5, '0'), 'Infineon Technologies', ARRAY['Programmation web'], '2026-04-01', 'stage', '2027-02-13', 'Configuration d''une infrastructure réseau sécurisée', '25 Rue de la Fille du Puisatier 13011 La-Penne-sur-Huveaune', 'l23000180', NULL, 'sergei.ivanov');
INSERT INTO Internship (internship_identifier, company_name, keywords, start_date_internship, type, end_date_internship, internship_subject, address, student_number, relevance_score, id_teacher) VALUES ('STA' || '2024' || LPAD(NEXTVAL('Internship_id_counter_seq')::TEXT, 5, '0'), 'Capgemini', ARRAY['Programmation orientée objet'], '2024-04-01', 'stage', '2025-02-10', 'Configuration d''une infrastructure réseau sécurisée', '43 Impasse des Jujubiers 13400 Aubagne', 'b23000181', NULL, 'clara.dupont');
INSERT INTO Internship (internship_identifier, company_name, keywords, start_date_internship, type, end_date_internship, internship_subject, address, student_number, relevance_score, id_teacher) VALUES ('STA' || '2026' || LPAD(NEXTVAL('Internship_id_counter_seq')::TEXT, 5, '0'), 'Thales', ARRAY['Marketing', 'Communication digitale', 'Architecture des ordinateurs'], '2026-04-01', 'stage', '2026-07-24', 'Conception d''une application mobile de VTC', '7 Quai Brescon 13500 Martigues', 'b23000182', NULL, 'anais.petit');
INSERT INTO Internship (internship_identifier, company_name, keywords, start_date_internship, type, end_date_internship, internship_subject, address, student_number, relevance_score, id_teacher) VALUES ('STA' || '2026' || LPAD(NEXTVAL('Internship_id_counter_seq')::TEXT, 5, '0'), 'Apple', ARRAY['Communication digitale', 'Gestion de bases de données', 'Droit'], '2026-04-01', 'stage', '2026-07-15', 'Pilotage d''un projet de refonte SI', '4 Boulevard Danielle Casanova 13014 Marseille', 'l23000183', NULL, 'youssef.el-mansouri');
INSERT INTO Internship (internship_identifier, company_name, keywords, start_date_internship, type, end_date_internship, internship_subject, address, student_number, relevance_score, id_teacher) VALUES ('STA' || '2024' || LPAD(NEXTVAL('Internship_id_counter_seq')::TEXT, 5, '0'), 'Capgemini', ARRAY['Architecture des ordinateurs'], '2024-04-01', 'stage', '2024-12-04', 'Création d''un dashboard de visualisation de données', '19 Impasse des Romains 13270 Fos-sur-Mer', 'b23000184', NULL, 'helene.vidal');
INSERT INTO Internship (internship_identifier, company_name, keywords, start_date_internship, type, end_date_internship, internship_subject, address, student_number, relevance_score, id_teacher) VALUES ('STA' || '2026' || LPAD(NEXTVAL('Internship_id_counter_seq')::TEXT, 5, '0'), 'Pharm’Aging', ARRAY['Vente et négociation', 'Communication'], '2026-04-01', 'stage', '2027-01-13', 'Développement d''une plateforme e-commerce', '15 Allée des Géraniums 13821 La-Penne-sur-Huveaune', 't23000185', NULL, 'pierre-henri.blanc');
INSERT INTO Internship (internship_identifier, company_name, keywords, start_date_internship, type, end_date_internship, internship_subject, address, student_number, relevance_score, id_teacher) VALUES ('STA' || '2026' || LPAD(NEXTVAL('Internship_id_counter_seq')::TEXT, 5, '0'), 'Omniciel', ARRAY['Vente et négociation'], '2026-04-01', 'stage', '2026-11-06', 'Développement d''un chatbot intelligent', '41 Boulevard Anatole France 13380 Allauch', 'z23000186', NULL, 'sofia.rossi');
INSERT INTO Internship (internship_identifier, company_name, keywords, start_date_internship, type, end_date_internship, internship_subject, address, student_number, relevance_score, id_teacher) VALUES ('STA' || '2023' || LPAD(NEXTVAL('Internship_id_counter_seq')::TEXT, 5, '0'), 'Thales', ARRAY['Communication'], '2023-04-01', 'stage', '2024-02-14', 'Optimisation de requêtes base de données', '19 Avenue de la Californie 06282 Nice', 'c23000187', NULL, 'anais.petit');
INSERT INTO Internship (internship_identifier, company_name, keywords, start_date_internship, type, end_date_internship, internship_subject, address, student_number, relevance_score, id_teacher) VALUES ('STA' || '2026' || LPAD(NEXTVAL('Internship_id_counter_seq')::TEXT, 5, '0'), 'Bouygues Telecom', ARRAY['Gestion de bases de données', 'Marketing'], '2026-04-01', 'stage', '2026-07-03', 'Conception d''une application mobile de VTC', '6 Allée du Clos Siméon 13530 Trets', 'r23000188', NULL, 'chloe.lefebvre');
INSERT INTO Internship (internship_identifier, company_name, keywords, start_date_internship, type, end_date_internship, internship_subject, address, student_number, relevance_score, id_teacher) VALUES ('STA' || '2024' || LPAD(NEXTVAL('Internship_id_counter_seq')::TEXT, 5, '0'), 'Netangels', ARRAY['Marketing', 'Gestion de projet'], '2024-04-01', 'stage', '2024-11-28', 'Automatisation des déploiements applicatifs', '38 Rue César Bossy 13300 Salon-de-Provence', 'z23000189', NULL, 'amina.kebir');
INSERT INTO Internship (internship_identifier, company_name, keywords, start_date_internship, type, end_date_internship, internship_subject, address, student_number, relevance_score, id_teacher) VALUES ('STA' || '2024' || LPAD(NEXTVAL('Internship_id_counter_seq')::TEXT, 5, '0'), 'Wooxo', ARRAY['Gestion financière'], '2024-04-01', 'stage', '2025-03-20', 'Développement d''une plateforme e-commerce', '34 Impasse Paul Arène 84120 Pertuis', 'm23000190', NULL, 'sofia.rossi');
INSERT INTO Internship (internship_identifier, company_name, keywords, start_date_internship, type, end_date_internship, internship_subject, address, student_number, relevance_score, id_teacher) VALUES ('STA' || '2023' || LPAD(NEXTVAL('Internship_id_counter_seq')::TEXT, 5, '0'), 'Dassault Systèmes', ARRAY['Gestion de bases de données', 'Économie', 'Programmation orientée objet'], '2023-04-01', 'stage', '2024-03-25', 'Programmation d''un drone autonome', '19 Avenue de Saint-Roch 83097 Toulon', 'b23000191', NULL, 'jean-luc.bernard');
INSERT INTO Internship (internship_identifier, company_name, keywords, start_date_internship, type, end_date_internship, internship_subject, address, student_number, relevance_score, id_teacher) VALUES ('STA' || '2025' || LPAD(NEXTVAL('Internship_id_counter_seq')::TEXT, 5, '0'), 'Purjus', ARRAY['Marketing', 'Vente et négociation'], '2025-04-01', 'stage', '2025-11-19', 'Conception d''une application mobile de VTC', '24 Avenue Émile Bodin 13600 Cassis', 'o23000192', NULL, 'jean-luc.bernard');
INSERT INTO Internship (internship_identifier, company_name, keywords, start_date_internship, type, end_date_internship, internship_subject, address, student_number, relevance_score, id_teacher) VALUES ('STA' || '2025' || LPAD(NEXTVAL('Internship_id_counter_seq')::TEXT, 5, '0'), 'Orange', ARRAY['Architecture des ordinateurs', 'Communication', 'Anglais'], '2025-04-01', 'stage', '2025-07-21', 'Audit de sécurité d''une application web', '19 Impasse Ravel 13013 Marseille', 'k23000193', NULL, 'samira.othmani');
INSERT INTO Internship (internship_identifier, company_name, keywords, start_date_internship, type, end_date_internship, internship_subject, address, student_number, relevance_score, id_teacher) VALUES ('STA' || '2024' || LPAD(NEXTVAL('Internship_id_counter_seq')::TEXT, 5, '0'), 'Serès Technologies', ARRAY['Droit', 'Gestion de bases de données'], '2024-04-01', 'stage', '2024-11-25', 'Configuration d''une infrastructure réseau sécurisée', '18 Allée de l''Armoise 13300 Salon-de-Provence', 'd23000194', NULL, 'clara.dupont');
INSERT INTO Internship (internship_identifier, company_name, keywords, start_date_internship, type, end_date_internship, internship_subject, address, student_number, relevance_score, id_teacher) VALUES ('STA' || '2024' || LPAD(NEXTVAL('Internship_id_counter_seq')::TEXT, 5, '0'), 'Altran', ARRAY['Gestion de projet', 'Programmation web', 'Marketing'], '2024-04-01', 'stage', '2024-12-03', 'Développement d''un chatbot intelligent', '26 Boulevard Bellevue 13011 La-Penne-sur-Huveaune', 'e23000195', NULL, 'youssef.el-mansouri');
INSERT INTO Internship (internship_identifier, company_name, keywords, start_date_internship, type, end_date_internship, internship_subject, address, student_number, relevance_score, id_teacher) VALUES ('STA' || '2025' || LPAD(NEXTVAL('Internship_id_counter_seq')::TEXT, 5, '0'), 'Safran', ARRAY['Gestion financière', 'Marketing', 'Économie'], '2025-04-01', 'stage', '2025-09-10', 'Optimisation de requêtes base de données', '10 Avenue Albert 1er 83470 Saint-Maximin-la-Sainte-Baume', 'b23000196', NULL, 'samira.othmani');
INSERT INTO Internship (internship_identifier, company_name, keywords, start_date_internship, type, end_date_internship, internship_subject, address, student_number, relevance_score, id_teacher) VALUES ('STA' || '2026' || LPAD(NEXTVAL('Internship_id_counter_seq')::TEXT, 5, '0'), 'Bull', ARRAY['Marketing'], '2026-04-01', 'stage', '2026-07-11', 'Programmation d''un drone autonome', '49 Avenue Mirabeau 13530 Trets', 'm23000197', NULL, 'helene.vidal');
INSERT INTO Internship (internship_identifier, company_name, keywords, start_date_internship, type, end_date_internship, internship_subject, address, student_number, relevance_score, id_teacher) VALUES ('STA' || '2025' || LPAD(NEXTVAL('Internship_id_counter_seq')::TEXT, 5, '0'), 'Infineon Technologies', ARRAY['Programmation web'], '2025-04-01', 'stage', '2025-11-09', 'Programmation d''un drone autonome', '7 Rue Robert de Roux 13013 Plan-de-Cuques', 'b23000198', NULL, 'youssef.el-mansouri');
INSERT INTO Internship (internship_identifier, company_name, keywords, start_date_internship, type, end_date_internship, internship_subject, address, student_number, relevance_score, id_teacher) VALUES ('STA' || '2023' || LPAD(NEXTVAL('Internship_id_counter_seq')::TEXT, 5, '0'), 'Serès Technologies', ARRAY['Architecture des ordinateurs', 'Gestion financière'], '2023-04-01', 'stage', '2023-09-07', 'Pilotage d''un projet de refonte SI', '42 Rue de l''Égalité 13400 Aubagne', 'm23000199', NULL, 'samira.othmani');
INSERT INTO Internship (internship_identifier, company_name, keywords, start_date_internship, type, end_date_internship, internship_subject, address, student_number, relevance_score, id_teacher) VALUES ('STA' || '2024' || LPAD(NEXTVAL('Internship_id_counter_seq')::TEXT, 5, '0'), 'Schneider Electric', ARRAY['Économie'], '2024-04-01', 'stage', '2024-09-18', 'Audit de sécurité d''une application web', '4 Avenue du Mistral 13600 La Ciotat', 'm23000200', NULL, 'samira.othmani');
INSERT INTO Internship (internship_identifier, company_name, keywords, start_date_internship, type, end_date_internship, internship_subject, address, student_number, relevance_score, id_teacher) VALUES ('STA' || '2026' || LPAD(NEXTVAL('Internship_id_counter_seq')::TEXT, 5, '0'), 'Atos', ARRAY['Programmation web'], '2026-04-01', 'stage', '2026-12-08', 'Création d''un dashboard de visualisation de données', '40 Quai Colbert 83097 Toulon', 'b23000201', NULL, 'anais.petit');
INSERT INTO Internship (internship_identifier, company_name, keywords, start_date_internship, type, end_date_internship, internship_subject, address, student_number, relevance_score, id_teacher) VALUES ('STA' || '2026' || LPAD(NEXTVAL('Internship_id_counter_seq')::TEXT, 5, '0'), 'IBM', ARRAY['Économie', 'Programmation web', 'Vente et négociation'], '2026-04-01', 'stage', '2026-12-24', 'Conception d''une application mobile de VTC', '30 Avenue Jean Giono 13190 Allauch', 'n23000202', NULL, 'amina.kebir');
INSERT INTO Internship (internship_identifier, company_name, keywords, start_date_internship, type, end_date_internship, internship_subject, address, student_number, relevance_score, id_teacher) VALUES ('STA' || '2025' || LPAD(NEXTVAL('Internship_id_counter_seq')::TEXT, 5, '0'), 'Wooxo', ARRAY['Économie', 'Vente et négociation'], '2025-04-01', 'stage', '2025-12-31', 'Développement d''un chatbot intelligent', '40 Allée du Montagnero 13190 Allauch', 's23000203', NULL, 'fatima.benali');
INSERT INTO Internship (internship_identifier, company_name, keywords, start_date_internship, type, end_date_internship, internship_subject, address, student_number, relevance_score, id_teacher) VALUES ('STA' || '2024' || LPAD(NEXTVAL('Internship_id_counter_seq')::TEXT, 5, '0'), 'Vertical Expense', ARRAY['Programmation orientée objet', 'Vente et négociation'], '2024-04-01', 'stage', '2024-12-09', 'Développement d''une plateforme e-commerce', '1 Allée Garaud Gustave 83196 Toulon', 'f23000204', NULL, 'francois-xavier.dupuis');
INSERT INTO Internship (internship_identifier, company_name, keywords, start_date_internship, type, end_date_internship, internship_subject, address, student_number, relevance_score, id_teacher) VALUES ('STA' || '2024' || LPAD(NEXTVAL('Internship_id_counter_seq')::TEXT, 5, '0'), 'Schneider Electric', ARRAY['Communication digitale'], '2024-04-01', 'stage', '2024-10-06', 'Développement d''une plateforme e-commerce', '19 Avenue Joliot Curie 13740 Le Rove', 'k23000205', NULL, 'alexandre.dubois');
INSERT INTO Internship (internship_identifier, company_name, keywords, start_date_internship, type, end_date_internship, internship_subject, address, student_number, relevance_score, id_teacher) VALUES ('STA' || '2026' || LPAD(NEXTVAL('Internship_id_counter_seq')::TEXT, 5, '0'), 'DEV-ID Marseille', ARRAY['Gestion de bases de données'], '2026-04-01', 'stage', '2026-11-20', 'Programmation d''un drone autonome', '19 Boulevard de la Libération 13001 Marseille', 'c23000206', NULL, 'sergei.ivanov');
INSERT INTO Internship (internship_identifier, company_name, keywords, start_date_internship, type, end_date_internship, internship_subject, address, student_number, relevance_score, id_teacher) VALUES ('STA' || '2025' || LPAD(NEXTVAL('Internship_id_counter_seq')::TEXT, 5, '0'), 'Schneider Electric', ARRAY['Communication digitale'], '2025-04-01', 'stage', '2025-08-28', 'Optimisation de requêtes base de données', '31 Allée de Mendez 13270 Fos-sur-Mer', 'f23000207', NULL, 'laura.costa');
INSERT INTO Internship (internship_identifier, company_name, keywords, start_date_internship, type, end_date_internship, internship_subject, address, student_number, relevance_score, id_teacher) VALUES ('STA' || '2025' || LPAD(NEXTVAL('Internship_id_counter_seq')::TEXT, 5, '0'), 'Thales', ARRAY['Programmation web'], '2025-04-01', 'stage', '2025-07-18', 'Audit de sécurité d''une application web', '36 Rue de la Verdière 13097 Aix-en-Provence', 'l23000208', NULL, 'marc.antoine');
INSERT INTO Internship (internship_identifier, company_name, keywords, start_date_internship, type, end_date_internship, internship_subject, address, student_number, relevance_score, id_teacher) VALUES ('STA' || '2025' || LPAD(NEXTVAL('Internship_id_counter_seq')::TEXT, 5, '0'), 'Atos', ARRAY['Économie'], '2025-04-01', 'stage', '2025-12-27', 'Conception d''une application mobile de VTC', '9 Avenue des Goums 13400 La-Penne-sur-Huveaune', 't23000209', NULL, 'clara.dupont');
INSERT INTO Internship (internship_identifier, company_name, keywords, start_date_internship, type, end_date_internship, internship_subject, address, student_number, relevance_score, id_teacher) VALUES ('STA' || '2025' || LPAD(NEXTVAL('Internship_id_counter_seq')::TEXT, 5, '0'), 'Capgemini', ARRAY['Programmation web'], '2025-04-01', 'stage', '2026-02-26', 'Création d''un dashboard de visualisation de données', '12 Rue du Professeur Robert Debré 13380 Plan-de-Cuques', 'm23000210', NULL, 'helene.vidal');
INSERT INTO Internship (internship_identifier, company_name, keywords, start_date_internship, type, end_date_internship, internship_subject, address, student_number, relevance_score, id_teacher) VALUES ('STA' || '2025' || LPAD(NEXTVAL('Internship_id_counter_seq')::TEXT, 5, '0'), 'Pharm’Aging', ARRAY['Anglais'], '2025-04-01', 'stage', '2025-08-19', 'Audit de sécurité d''une application web', '46 Impasse Pierrot 13400 La-Penne-sur-Huveaune', 'r23000211', NULL, 'nadia.cherif');
INSERT INTO Internship (internship_identifier, company_name, keywords, start_date_internship, type, end_date_internship, internship_subject, address, student_number, relevance_score, id_teacher) VALUES ('STA' || '2023' || LPAD(NEXTVAL('Internship_id_counter_seq')::TEXT, 5, '0'), 'Apple', ARRAY['Architecture des ordinateurs', 'Communication digitale', 'Marketing'], '2023-04-01', 'stage', '2023-10-21', 'Développement d''un chatbot intelligent', '23 Place des Aires 13500 Martigues', 'z23000212', NULL, 'fatima.benali');
INSERT INTO Internship (internship_identifier, company_name, keywords, start_date_internship, type, end_date_internship, internship_subject, address, student_number, relevance_score, id_teacher) VALUES ('STA' || '2024' || LPAD(NEXTVAL('Internship_id_counter_seq')::TEXT, 5, '0'), 'Serès Technologies', ARRAY['Gestion de bases de données', 'Gestion financière'], '2024-04-01', 'stage', '2025-03-17', 'Programmation d''un drone autonome', '43 Chemin de la Gaie Vallée 83190 Toulon', 'a23000213', NULL, 'sofia.rossi');
INSERT INTO Internship (internship_identifier, company_name, keywords, start_date_internship, type, end_date_internship, internship_subject, address, student_number, relevance_score, id_teacher) VALUES ('STA' || '2025' || LPAD(NEXTVAL('Internship_id_counter_seq')::TEXT, 5, '0'), 'DEV-ID Marseille', ARRAY['Gestion de bases de données'], '2025-04-01', 'stage', '2026-02-21', 'Développement d''une plateforme e-commerce', '27 Rue Jean Richepin 13400 Aubagne', 'd23000214', NULL, 'alexandre.dubois');
INSERT INTO Internship (internship_identifier, company_name, keywords, start_date_internship, type, end_date_internship, internship_subject, address, student_number, relevance_score, id_teacher) VALUES ('STA' || '2025' || LPAD(NEXTVAL('Internship_id_counter_seq')::TEXT, 5, '0'), 'Infineon Technologies', ARRAY['Gestion financière'], '2025-04-01', 'stage', '2026-01-20', 'Audit de sécurité d''une application web', '38 Avenue de l''Amiral Ganteaume 13260 Cassis', 'f23000215', NULL, 'nadia.cherif');
INSERT INTO Internship (internship_identifier, company_name, keywords, start_date_internship, type, end_date_internship, internship_subject, address, student_number, relevance_score, id_teacher) VALUES ('STA' || '2023' || LPAD(NEXTVAL('Internship_id_counter_seq')::TEXT, 5, '0'), 'Amazon Web Services', ARRAY['Communication', 'Droit'], '2023-04-01', 'stage', '2024-03-23', 'Pilotage d''un projet de refonte SI', '20 Allée du Bois Joli 83470 Saint-Maximin-la-Sainte-Baume', 'r23000216', NULL, 'jean-luc.bernard');
INSERT INTO Internship (internship_identifier, company_name, keywords, start_date_internship, type, end_date_internship, internship_subject, address, student_number, relevance_score, id_teacher) VALUES ('STA' || '2024' || LPAD(NEXTVAL('Internship_id_counter_seq')::TEXT, 5, '0'), 'Serès Technologies', ARRAY['Communication', 'Communication digitale', 'Droit'], '2024-04-01', 'stage', '2024-08-26', 'Développement d''une plateforme e-commerce', '9 Allée de la Farigoulette 13300 Salon-de-Provence', 'b23000217', NULL, 'fatima.benali');
INSERT INTO Internship (internship_identifier, company_name, keywords, start_date_internship, type, end_date_internship, internship_subject, address, student_number, relevance_score, id_teacher) VALUES ('STA' || '2024' || LPAD(NEXTVAL('Internship_id_counter_seq')::TEXT, 5, '0'), 'STMicroelectronics', ARRAY['Architecture des ordinateurs', 'Économie'], '2024-04-01', 'stage', '2024-09-07', 'Configuration d''une infrastructure réseau sécurisée', '5 Boulevard Bara 13013 Plan-de-Cuques', 'l23000218', NULL, 'amina.kebir');
INSERT INTO Internship (internship_identifier, company_name, keywords, start_date_internship, type, end_date_internship, internship_subject, address, student_number, relevance_score, id_teacher) VALUES ('STA' || '2023' || LPAD(NEXTVAL('Internship_id_counter_seq')::TEXT, 5, '0'), 'Akka Technologies', ARRAY['Programmation web', 'Programmation orientée objet'], '2023-04-01', 'stage', '2024-02-07', 'Configuration d''une infrastructure réseau sécurisée', '17 Allée des Sarcelles 13270 Fos-sur-Mer', 'a23000219', NULL, 'henri.delorme');
INSERT INTO Internship (internship_identifier, company_name, keywords, start_date_internship, type, end_date_internship, internship_subject, address, student_number, relevance_score, id_teacher) VALUES ('STA' || '2024' || LPAD(NEXTVAL('Internship_id_counter_seq')::TEXT, 5, '0'), 'Netangels', ARRAY['Communication digitale'], '2024-04-01', 'stage', '2024-07-18', 'Optimisation de requêtes base de données', '35 Rue des Hortensias 13120 Gardanne', 'c23000220', NULL, 'lucas.royer');
INSERT INTO Internship (internship_identifier, company_name, keywords, start_date_internship, type, end_date_internship, internship_subject, address, student_number, relevance_score, id_teacher) VALUES ('STA' || '2026' || LPAD(NEXTVAL('Internship_id_counter_seq')::TEXT, 5, '0'), 'Dassault Systèmes', ARRAY['Programmation web', 'Économie'], '2026-04-01', 'stage', '2026-08-05', 'Automatisation des déploiements applicatifs', '48 Avenue des Aires 13120 Gardanne', 'g23000221', NULL, 'lucas.royer');
INSERT INTO Internship (internship_identifier, company_name, keywords, start_date_internship, type, end_date_internship, internship_subject, address, student_number, relevance_score, id_teacher) VALUES ('STA' || '2023' || LPAD(NEXTVAL('Internship_id_counter_seq')::TEXT, 5, '0'), 'IBM', ARRAY['Architecture des ordinateurs'], '2023-04-01', 'stage', '2024-02-23', 'Automatisation des déploiements applicatifs', '34 Avenue Pablo Picasso 83160 Toulon', 'k23000222', NULL, 'youssef.el-mansouri');
INSERT INTO Internship (internship_identifier, company_name, keywords, start_date_internship, type, end_date_internship, internship_subject, address, student_number, relevance_score, id_teacher) VALUES ('STA' || '2025' || LPAD(NEXTVAL('Internship_id_counter_seq')::TEXT, 5, '0'), 'Alstom', ARRAY['Gestion de bases de données'], '2025-04-01', 'stage', '2026-02-09', 'Conception d''une application mobile de VTC', '6 Place de la Révolution 83470 Saint-Maximin-la-Sainte-Baume', 'p23000223', NULL, 'sergei.ivanov');
INSERT INTO Internship (internship_identifier, company_name, keywords, start_date_internship, type, end_date_internship, internship_subject, address, student_number, relevance_score, id_teacher) VALUES ('STA' || '2024' || LPAD(NEXTVAL('Internship_id_counter_seq')::TEXT, 5, '0'), 'Ombrea', ARRAY['Communication digitale', 'Gestion de projet', 'Économie'], '2024-04-01', 'stage', '2024-08-25', 'Conception d''une application mobile de VTC', '24 Avenue de la Marine 13600 La Ciotat', 'b23000224', NULL, 'sofia.rossi');
INSERT INTO Internship (internship_identifier, company_name, keywords, start_date_internship, type, end_date_internship, internship_subject, address, student_number, relevance_score, id_teacher) VALUES ('STA' || '2025' || LPAD(NEXTVAL('Internship_id_counter_seq')::TEXT, 5, '0'), 'Ombrea', ARRAY['Anglais', 'Vente et négociation', 'Programmation web'], '2025-04-01', 'stage', '2025-07-22', 'Configuration d''une infrastructure réseau sécurisée', '19 Allée des Oliviers 06106 Nice', 'y23000225', NULL, 'nadia.cherif');
INSERT INTO Internship (internship_identifier, company_name, keywords, start_date_internship, type, end_date_internship, internship_subject, address, student_number, relevance_score, id_teacher) VALUES ('STA' || '2023' || LPAD(NEXTVAL('Internship_id_counter_seq')::TEXT, 5, '0'), 'Atos', ARRAY['Économie'], '2023-04-01', 'stage', '2023-08-09', 'Automatisation des déploiements applicatifs', '17 Rue de la Palombière 13270 Fos-sur-Mer', 'g23000226', NULL, 'lea.girard');
INSERT INTO Internship (internship_identifier, company_name, keywords, start_date_internship, type, end_date_internship, internship_subject, address, student_number, relevance_score, id_teacher) VALUES ('STA' || '2025' || LPAD(NEXTVAL('Internship_id_counter_seq')::TEXT, 5, '0'), 'Stellantis', ARRAY['Gestion de bases de données', 'Anglais'], '2025-04-01', 'stage', '2025-11-28', 'Création d''un dashboard de visualisation de données', '32 Avenue Jean Jaurès 13530 Trets', 'm23000227', NULL, 'lucas.royer');
INSERT INTO Internship (internship_identifier, company_name, keywords, start_date_internship, type, end_date_internship, internship_subject, address, student_number, relevance_score, id_teacher) VALUES ('STA' || '2023' || LPAD(NEXTVAL('Internship_id_counter_seq')::TEXT, 5, '0'), 'DEV-ID Marseille', ARRAY['Gestion financière', 'Communication digitale', 'Gestion de projet'], '2023-04-01', 'stage', '2024-03-03', 'Automatisation des déploiements applicatifs', '40 Boulevard Louvois 83000 Toulon', 'z23000228', NULL, 'fatima.benali');
INSERT INTO Internship (internship_identifier, company_name, keywords, start_date_internship, type, end_date_internship, internship_subject, address, student_number, relevance_score, id_teacher) VALUES ('STA' || '2023' || LPAD(NEXTVAL('Internship_id_counter_seq')::TEXT, 5, '0'), 'Ombrea', ARRAY['Gestion financière'], '2023-04-01', 'stage', '2024-03-29', 'Programmation d''un drone autonome', '50 Allée de la Courtine 13270 Fos-sur-Mer', 's23000229', NULL, 'amina.kebir');
INSERT INTO Internship (internship_identifier, company_name, keywords, start_date_internship, type, end_date_internship, internship_subject, address, student_number, relevance_score, id_teacher) VALUES ('STA' || '2023' || LPAD(NEXTVAL('Internship_id_counter_seq')::TEXT, 5, '0'), 'Bull', ARRAY['Droit'], '2023-04-01', 'stage', '2023-11-13', 'Pilotage d''un projet de refonte SI', '31 Rue des Ventadourio 13300 Salon-de-Provence', 's23000230', NULL, 'anais.petit');
INSERT INTO Internship (internship_identifier, company_name, keywords, start_date_internship, type, end_date_internship, internship_subject, address, student_number, relevance_score, id_teacher) VALUES ('STA' || '2024' || LPAD(NEXTVAL('Internship_id_counter_seq')::TEXT, 5, '0'), 'SFR', ARRAY['Communication', 'Communication digitale', 'Gestion de bases de données'], '2024-04-01', 'stage', '2025-01-06', 'Audit de sécurité d''une application web', '43 Impasse du Littoral 13260 Cassis', 'b23000231', NULL, 'laurent.moreau');
INSERT INTO Internship (internship_identifier, company_name, keywords, start_date_internship, type, end_date_internship, internship_subject, address, student_number, relevance_score, id_teacher) VALUES ('STA' || '2025' || LPAD(NEXTVAL('Internship_id_counter_seq')::TEXT, 5, '0'), 'Vertical Expense', ARRAY['Communication digitale'], '2025-04-01', 'stage', '2026-02-19', 'Optimisation de requêtes base de données', '50 Rue Félicien David 13100 Aix-en-Provence', 'm23000232', NULL, 'anais.petit');
INSERT INTO Internship (internship_identifier, company_name, keywords, start_date_internship, type, end_date_internship, internship_subject, address, student_number, relevance_score, id_teacher) VALUES ('STA' || '2025' || LPAD(NEXTVAL('Internship_id_counter_seq')::TEXT, 5, '0'), 'Crosscall', ARRAY['Anglais', 'Programmation orientée objet', 'Gestion de projet'], '2025-04-01', 'stage', '2025-07-17', 'Optimisation de requêtes base de données', '2 Avenue de l''Amiral Ganteaume 13260 Cassis', 'k23000233', NULL, 'mehdi.taha');
INSERT INTO Internship (internship_identifier, company_name, keywords, start_date_internship, type, end_date_internship, internship_subject, address, student_number, relevance_score, id_teacher) VALUES ('STA' || '2026' || LPAD(NEXTVAL('Internship_id_counter_seq')::TEXT, 5, '0'), 'Sopra Steria', ARRAY['Architecture des ordinateurs', 'Vente et négociation', 'Gestion de bases de données'], '2026-04-01', 'stage', '2026-07-26', 'Développement d''une plateforme e-commerce', '41 Avenue des Carriers 13260 Cassis', 'a23000234', NULL, 'francois-xavier.dupuis');
INSERT INTO Internship (internship_identifier, company_name, keywords, start_date_internship, type, end_date_internship, internship_subject, address, student_number, relevance_score, id_teacher) VALUES ('STA' || '2024' || LPAD(NEXTVAL('Internship_id_counter_seq')::TEXT, 5, '0'), 'Microsoft', ARRAY['Économie', 'Programmation web'], '2024-04-01', 'stage', '2024-11-19', 'Programmation d''un drone autonome', '20 Rue des Pastourelles 13300 Salon-de-Provence', 'p23000235', NULL, 'francois-xavier.dupuis');
INSERT INTO Internship (internship_identifier, company_name, keywords, start_date_internship, type, end_date_internship, internship_subject, address, student_number, relevance_score, id_teacher) VALUES ('STA' || '2023' || LPAD(NEXTVAL('Internship_id_counter_seq')::TEXT, 5, '0'), 'Siemens', ARRAY['Vente et négociation', 'Marketing'], '2023-04-01', 'stage', '2023-08-17', 'Configuration d''une infrastructure réseau sécurisée', '12 Avenue du Général de Gaulle 13590 Gardanne', 'c23000236', NULL, 'laurent.moreau');
INSERT INTO Internship (internship_identifier, company_name, keywords, start_date_internship, type, end_date_internship, internship_subject, address, student_number, relevance_score, id_teacher) VALUES ('STA' || '2026' || LPAD(NEXTVAL('Internship_id_counter_seq')::TEXT, 5, '0'), 'Crosscall', ARRAY['Anglais', 'Communication'], '2026-04-01', 'stage', '2026-07-03', 'Développement d''un chatbot intelligent', '33 Boulevard du Commandant Nicolas 83000 Toulon', 'a23000237', NULL, 'alexandre.dubois');
INSERT INTO Internship (internship_identifier, company_name, keywords, start_date_internship, type, end_date_internship, internship_subject, address, student_number, relevance_score, id_teacher) VALUES ('STA' || '2024' || LPAD(NEXTVAL('Internship_id_counter_seq')::TEXT, 5, '0'), 'Bouygues Telecom', ARRAY['Programmation orientée objet'], '2024-04-01', 'stage', '2024-12-09', 'Configuration d''une infrastructure réseau sécurisée', '23 Rue Paul Cézanne 13380 Plan-de-Cuques', 'd23000238', NULL, 'lea.girard');
INSERT INTO Internship (internship_identifier, company_name, keywords, start_date_internship, type, end_date_internship, internship_subject, address, student_number, relevance_score, id_teacher) VALUES ('STA' || '2023' || LPAD(NEXTVAL('Internship_id_counter_seq')::TEXT, 5, '0'), 'ImCheck Therapeutics', ARRAY['Programmation orientée objet', 'Marketing', 'Programmation web'], '2023-04-01', 'stage', '2024-01-19', 'Programmation d''un drone autonome', '26 Rue Delille 06300 Nice', 'l23000239', NULL, 'nadia.cherif');
INSERT INTO Internship (internship_identifier, company_name, keywords, start_date_internship, type, end_date_internship, internship_subject, address, student_number, relevance_score, id_teacher) VALUES ('STA' || '2024' || LPAD(NEXTVAL('Internship_id_counter_seq')::TEXT, 5, '0'), 'Apple', ARRAY['Vente et négociation', 'Économie', 'Gestion de projet'], '2024-04-01', 'stage', '2024-10-27', 'Création d''un dashboard de visualisation de données', '27 Impasse Courtot 13530 Trets', 'b23000240', NULL, 'amina.kebir');
INSERT INTO Internship (internship_identifier, company_name, keywords, start_date_internship, type, end_date_internship, internship_subject, address, student_number, relevance_score, id_teacher) VALUES ('STA' || '2026' || LPAD(NEXTVAL('Internship_id_counter_seq')::TEXT, 5, '0'), 'Akka Technologies', ARRAY['Architecture des ordinateurs', 'Économie', 'Programmation web'], '2026-04-01', 'stage', '2026-11-30', 'Programmation d''un drone autonome', '18 Quai Commandant Rivière 83200 Toulon', 'z23000241', NULL, 'amina.kebir');
INSERT INTO Internship (internship_identifier, company_name, keywords, start_date_internship, type, end_date_internship, internship_subject, address, student_number, relevance_score, id_teacher) VALUES ('STA' || '2025' || LPAD(NEXTVAL('Internship_id_counter_seq')::TEXT, 5, '0'), 'Ombrea', ARRAY['Gestion de projet', 'Vente et négociation'], '2025-04-01', 'stage', '2025-09-18', 'Audit de sécurité d''une application web', '3 Rue Jean-Baptiste Michel 13380 Plan-de-Cuques', 'o23000242', NULL, 'helene.vidal');
INSERT INTO Internship (internship_identifier, company_name, keywords, start_date_internship, type, end_date_internship, internship_subject, address, student_number, relevance_score, id_teacher) VALUES ('STA' || '2024' || LPAD(NEXTVAL('Internship_id_counter_seq')::TEXT, 5, '0'), 'Bull', ARRAY['Vente et négociation', 'Architecture des ordinateurs', 'Programmation web'], '2024-04-01', 'stage', '2024-08-07', 'Optimisation de requêtes base de données', '12 Rue Maurice Ravel 13500 Martigues', 'b23000243', NULL, 'lucas.royer');
INSERT INTO Internship (internship_identifier, company_name, keywords, start_date_internship, type, end_date_internship, internship_subject, address, student_number, relevance_score, id_teacher) VALUES ('STA' || '2025' || LPAD(NEXTVAL('Internship_id_counter_seq')::TEXT, 5, '0'), 'SFR', ARRAY['Droit'], '2025-04-01', 'stage', '2026-03-20', 'Optimisation de requêtes base de données', '12 Place du Marché 13270 Fos-sur-Mer', 'h23000244', NULL, 'henri.delorme');
INSERT INTO Internship (internship_identifier, company_name, keywords, start_date_internship, type, end_date_internship, internship_subject, address, student_number, relevance_score, id_teacher) VALUES ('STA' || '2023' || LPAD(NEXTVAL('Internship_id_counter_seq')::TEXT, 5, '0'), 'Texas Instruments', ARRAY['Économie', 'Programmation web', 'Anglais'], '2023-04-01', 'stage', '2023-11-19', 'Configuration d''une infrastructure réseau sécurisée', '21 Rue Baubin 83470 Saint-Maximin-la-Sainte-Baume', 'a23000245', NULL, 'henri.delorme');
INSERT INTO Internship (internship_identifier, company_name, keywords, start_date_internship, type, end_date_internship, internship_subject, address, student_number, relevance_score, id_teacher) VALUES ('STA' || '2025' || LPAD(NEXTVAL('Internship_id_counter_seq')::TEXT, 5, '0'), 'Crosscall', ARRAY['Anglais', 'Gestion de bases de données', 'Économie'], '2025-04-01', 'stage', '2026-03-24', 'Audit de sécurité d''une application web', '24 Avenue des Ribas 13770 Venelles', 'l23000246', NULL, 'chloe.lefebvre');
INSERT INTO Internship (internship_identifier, company_name, keywords, start_date_internship, type, end_date_internship, internship_subject, address, student_number, relevance_score, id_teacher) VALUES ('STA' || '2023' || LPAD(NEXTVAL('Internship_id_counter_seq')::TEXT, 5, '0'), 'Groupe La Poste', ARRAY['Architecture des ordinateurs', 'Programmation orientée objet'], '2023-04-01', 'stage', '2024-01-03', 'Configuration d''une infrastructure réseau sécurisée', '31 Allée des Jardins du Papillon 13770 Venelles', 'g23000247', NULL, 'pierre-henri.blanc');
INSERT INTO Internship (internship_identifier, company_name, keywords, start_date_internship, type, end_date_internship, internship_subject, address, student_number, relevance_score, id_teacher) VALUES ('STA' || '2023' || LPAD(NEXTVAL('Internship_id_counter_seq')::TEXT, 5, '0'), 'Wooxo', ARRAY['Programmation orientée objet'], '2023-04-01', 'stage', '2023-12-27', 'Optimisation de requêtes base de données', '39 Quai Kléber 13500 Martigues', 'r23000248', NULL, 'philippe.marchand');
INSERT INTO Internship (internship_identifier, company_name, keywords, start_date_internship, type, end_date_internship, internship_subject, address, student_number, relevance_score, id_teacher) VALUES ('STA' || '2023' || LPAD(NEXTVAL('Internship_id_counter_seq')::TEXT, 5, '0'), 'Altran', ARRAY['Programmation orientée objet'], '2023-04-01', 'stage', '2023-08-21', 'Création d''un dashboard de visualisation de données', '48 Impasse de Courtrai 13012 Marseille', 's23000249', NULL, 'mehdi.taha');
INSERT INTO Internship (internship_identifier, company_name, keywords, start_date_internship, type, end_date_internship, internship_subject, address, student_number, relevance_score, id_teacher) VALUES ('STA' || '2023' || LPAD(NEXTVAL('Internship_id_counter_seq')::TEXT, 5, '0'), 'Akka Technologies', ARRAY['Économie', 'Gestion financière'], '2023-04-01', 'stage', '2023-12-23', 'Optimisation de requêtes base de données', '19 Rue des Arcades 13110 Fos-sur-Mer', 'b23000250', NULL, 'amina.kebir');
INSERT INTO Internship (internship_identifier, company_name, keywords, start_date_internship, type, end_date_internship, internship_subject, address, student_number, relevance_score) VALUES ('STA' || '2026' || LPAD(NEXTVAL('Internship_id_counter_seq')::TEXT, 5, '0'), 'Apple', ARRAY['Architecture des ordinateurs'], '2026-04-01', 'stage', '2026-10-13', 'Programmation d''un drone autonome', '1 Rue Victor Leydet 13100 Aix-en-Provence', 'm23000251', NULL);
INSERT INTO Internship (internship_identifier, company_name, keywords, start_date_internship, type, end_date_internship, internship_subject, address, student_number, relevance_score) VALUES ('STA' || '2025' || LPAD(NEXTVAL('Internship_id_counter_seq')::TEXT, 5, '0'), 'Sopra Steria', ARRAY['Programmation orientée objet'], '2025-04-01', 'stage', '2025-11-09', 'Pilotage d''un projet de refonte SI', '24 Impasse des Fauvettes 13400 Aubagne', 'z21000252', NULL);
INSERT INTO Internship (internship_identifier, company_name, keywords, start_date_internship, type, end_date_internship, internship_subject, address, student_number, relevance_score) VALUES ('STA' || '2026' || LPAD(NEXTVAL('Internship_id_counter_seq')::TEXT, 5, '0'), 'Crosscall', ARRAY['Programmation web'], '2026-04-01', 'stage', '2027-02-16', 'Programmation d''un drone autonome', '32 Rue des Moulins 84120 Pertuis', 'm22000253', NULL);
INSERT INTO Internship (internship_identifier, company_name, keywords, start_date_internship, type, end_date_internship, internship_subject, address, student_number, relevance_score) VALUES ('STA' || '2025' || LPAD(NEXTVAL('Internship_id_counter_seq')::TEXT, 5, '0'), 'Microsoft', ARRAY['Architecture des ordinateurs'], '2025-04-01', 'stage', '2025-12-27', 'Pilotage d''un projet de refonte SI', '44 Avenue Fernand Chauvin 13530 Trets', 'b21000254', NULL);
INSERT INTO Internship (internship_identifier, company_name, keywords, start_date_internship, type, end_date_internship, internship_subject, address, student_number, relevance_score) VALUES ('STA' || '2026' || LPAD(NEXTVAL('Internship_id_counter_seq')::TEXT, 5, '0'), 'Wooxo', ARRAY['Marketing', 'Programmation web', 'Architecture des ordinateurs'], '2026-04-01', 'stage', '2026-12-29', 'Développement d''un chatbot intelligent', '46 Allée des Platanes 13400 Aubagne', 'z23000255', NULL);
INSERT INTO Internship (internship_identifier, company_name, keywords, start_date_internship, type, end_date_internship, internship_subject, address, student_number, relevance_score) VALUES ('STA' || '2024' || LPAD(NEXTVAL('Internship_id_counter_seq')::TEXT, 5, '0'), 'Schneider Electric', ARRAY['Communication digitale'], '2024-04-01', 'stage', '2024-11-06', 'Développement d''une plateforme e-commerce', '8 Avenue du Sable d''Or 13270 Fos-sur-Mer', 'b21000256', NULL);
INSERT INTO Internship (internship_identifier, company_name, keywords, start_date_internship, type, end_date_internship, internship_subject, address, student_number, relevance_score) VALUES ('STA' || '2024' || LPAD(NEXTVAL('Internship_id_counter_seq')::TEXT, 5, '0'), 'Bouygues Telecom', ARRAY['Communication', 'Anglais', 'Droit'], '2024-04-01', 'stage', '2024-07-08', 'Audit de sécurité d''une application web', '35 Rue André Bailet 13380 Allauch', 'm23000257', NULL);
INSERT INTO Internship (internship_identifier, company_name, keywords, start_date_internship, type, end_date_internship, internship_subject, address, student_number, relevance_score) VALUES ('STA' || '2025' || LPAD(NEXTVAL('Internship_id_counter_seq')::TEXT, 5, '0'), 'IADYS', ARRAY['Gestion financière', 'Anglais'], '2025-04-01', 'stage', '2026-01-18', 'Conception d''une application mobile de VTC', '13 Avenue Charles de Gaulle 13500 Martigues', 'n22000258', NULL);
INSERT INTO Internship (internship_identifier, company_name, keywords, start_date_internship, type, end_date_internship, internship_subject, address, student_number, relevance_score) VALUES ('STA' || '2024' || LPAD(NEXTVAL('Internship_id_counter_seq')::TEXT, 5, '0'), 'Vertical Expense', ARRAY['Économie', 'Gestion financière'], '2024-04-01', 'stage', '2024-09-28', 'Optimisation de requêtes base de données', '21 Avenue des Albizzi 13260 Cassis', 'l23000259', NULL);
INSERT INTO Internship (internship_identifier, company_name, keywords, start_date_internship, type, end_date_internship, internship_subject, address, student_number, relevance_score) VALUES ('STA' || '2023' || LPAD(NEXTVAL('Internship_id_counter_seq')::TEXT, 5, '0'), 'IBM', ARRAY['Économie', 'Programmation web', 'Vente et négociation'], '2023-04-01', 'stage', '2023-12-22', 'Conception d''une application mobile de VTC', '8 Allée de Saint-Hippolyte 13770 Venelles', 'm23000260', NULL);
INSERT INTO Internship (internship_identifier, company_name, keywords, start_date_internship, type, end_date_internship, internship_subject, address, student_number, relevance_score) VALUES ('STA' || '2026' || LPAD(NEXTVAL('Internship_id_counter_seq')::TEXT, 5, '0'), 'IBM', ARRAY['Gestion de bases de données', 'Marketing', 'Communication'], '2026-04-01', 'stage', '2027-02-01', 'Optimisation de requêtes base de données', '10 Avenue du Pastre 13400 Aubagne', 'n22000261', NULL);
INSERT INTO Internship (internship_identifier, company_name, keywords, start_date_internship, type, end_date_internship, internship_subject, address, student_number, relevance_score) VALUES ('STA' || '2026' || LPAD(NEXTVAL('Internship_id_counter_seq')::TEXT, 5, '0'), 'Siemens', ARRAY['Droit', 'Programmation orientée objet'], '2026-04-01', 'stage', '2027-03-03', 'Développement d''une plateforme e-commerce', '13 Avenue de la Savoie 13180 Gignac-la-Nerthe', 'd21000262', NULL);
INSERT INTO Internship (internship_identifier, company_name, keywords, start_date_internship, type, end_date_internship, internship_subject, address, student_number, relevance_score) VALUES ('STA' || '2023' || LPAD(NEXTVAL('Internship_id_counter_seq')::TEXT, 5, '0'), 'Remmedia', ARRAY['Anglais', 'Communication digitale'], '2023-04-01', 'stage', '2024-01-05', 'Pilotage d''un projet de refonte SI', '23 Avenue de Bredasque 13090 Aix-en-Provence', 'd22000263', NULL);
INSERT INTO Internship (internship_identifier, company_name, keywords, start_date_internship, type, end_date_internship, internship_subject, address, student_number, relevance_score) VALUES ('STA' || '2025' || LPAD(NEXTVAL('Internship_id_counter_seq')::TEXT, 5, '0'), 'Omniciel', ARRAY['Gestion de projet', 'Programmation orientée objet'], '2025-04-01', 'stage', '2026-03-13', 'Configuration d''une infrastructure réseau sécurisée', '5 Rue Fernand Arata 13626 Aix-en-Provence', 'r23000264', NULL);
INSERT INTO Internship (internship_identifier, company_name, keywords, start_date_internship, type, end_date_internship, internship_subject, address, student_number, relevance_score) VALUES ('STA' || '2026' || LPAD(NEXTVAL('Internship_id_counter_seq')::TEXT, 5, '0'), 'IADYS', ARRAY['Marketing', 'Gestion de bases de données', 'Programmation orientée objet'], '2026-04-01', 'stage', '2026-10-20', 'Audit de sécurité d''une application web', '40 Avenue Julien Fabre 13300 Salon-de-Provence', 'i22000265', NULL);
INSERT INTO Internship (internship_identifier, company_name, keywords, start_date_internship, type, end_date_internship, internship_subject, address, student_number, relevance_score) VALUES ('STA' || '2023' || LPAD(NEXTVAL('Internship_id_counter_seq')::TEXT, 5, '0'), 'Alstom', ARRAY['Programmation web', 'Gestion de bases de données'], '2023-04-01', 'stage', '2024-03-19', 'Optimisation de requêtes base de données', '44 Avenue Ambroise Croizat 13120 Gardanne', 'b22000266', NULL);
INSERT INTO Internship (internship_identifier, company_name, keywords, start_date_internship, type, end_date_internship, internship_subject, address, student_number, relevance_score) VALUES ('STA' || '2026' || LPAD(NEXTVAL('Internship_id_counter_seq')::TEXT, 5, '0'), 'Airbus', ARRAY['Communication digitale', 'Gestion financière'], '2026-04-01', 'stage', '2026-07-19', 'Développement d''une plateforme e-commerce', '4 Boulevard d''Estienne d''Orves 13500 Martigues', 'a22000267', NULL);
INSERT INTO Internship (internship_identifier, company_name, keywords, start_date_internship, type, end_date_internship, internship_subject, address, student_number, relevance_score) VALUES ('STA' || '2026' || LPAD(NEXTVAL('Internship_id_counter_seq')::TEXT, 5, '0'), 'Safran', ARRAY['Anglais', 'Communication'], '2026-04-01', 'stage', '2026-08-11', 'Développement d''une plateforme e-commerce', '13 Impasse des Albatros 13110 Port-de-Bouc', 'l22000268', NULL);
INSERT INTO Internship (internship_identifier, company_name, keywords, start_date_internship, type, end_date_internship, internship_subject, address, student_number, relevance_score) VALUES ('STA' || '2026' || LPAD(NEXTVAL('Internship_id_counter_seq')::TEXT, 5, '0'), 'Pharm’Aging', ARRAY['Architecture des ordinateurs', 'Gestion de projet'], '2026-04-01', 'stage', '2026-09-16', 'Développement d''un chatbot intelligent', '15 Impasse du Cabot 13300 Salon-de-Provence', 'm23000269', NULL);
INSERT INTO Internship (internship_identifier, company_name, keywords, start_date_internship, type, end_date_internship, internship_subject, address, student_number, relevance_score) VALUES ('STA' || '2023' || LPAD(NEXTVAL('Internship_id_counter_seq')::TEXT, 5, '0'), 'Safran', ARRAY['Programmation orientée objet', 'Gestion financière', 'Gestion de bases de données'], '2023-04-01', 'stage', '2024-02-01', 'Configuration d''une infrastructure réseau sécurisée', '31 Rue Léon Jouve 13190 Allauch', 'b22000270', NULL);
INSERT INTO Internship (internship_identifier, company_name, keywords, start_date_internship, type, end_date_internship, internship_subject, address, student_number, relevance_score) VALUES ('STA' || '2024' || LPAD(NEXTVAL('Internship_id_counter_seq')::TEXT, 5, '0'), 'Renault', ARRAY['Communication'], '2024-04-01', 'stage', '2024-12-28', 'Audit de sécurité d''une application web', '17 Avenue des Oulivarello 13300 Salon-de-Provence', 'b22000271', NULL);
INSERT INTO Internship (internship_identifier, company_name, keywords, start_date_internship, type, end_date_internship, internship_subject, address, student_number, relevance_score) VALUES ('STA' || '2023' || LPAD(NEXTVAL('Internship_id_counter_seq')::TEXT, 5, '0'), 'Dassault Systèmes', ARRAY['Droit'], '2023-04-01', 'stage', '2023-07-27', 'Création d''un dashboard de visualisation de données', '13 Avenue de Verdun 13260 Cassis', 'd22000272', NULL);
INSERT INTO Internship (internship_identifier, company_name, keywords, start_date_internship, type, end_date_internship, internship_subject, address, student_number, relevance_score) VALUES ('STA' || '2025' || LPAD(NEXTVAL('Internship_id_counter_seq')::TEXT, 5, '0'), 'Dassault Aviation', ARRAY['Marketing', 'Programmation web'], '2025-04-01', 'stage', '2025-07-03', 'Configuration d''une infrastructure réseau sécurisée', '32 Avenue de la Viguerie 13260 Cassis', 'm23000273', NULL);
INSERT INTO Internship (internship_identifier, company_name, keywords, start_date_internship, type, end_date_internship, internship_subject, address, student_number, relevance_score) VALUES ('STA' || '2023' || LPAD(NEXTVAL('Internship_id_counter_seq')::TEXT, 5, '0'), 'NXP Semiconductors', ARRAY['Gestion de projet'], '2023-04-01', 'stage', '2023-10-26', 'Optimisation de requêtes base de données', '8 Avenue de Saint-Menet 13821 La-Penne-sur-Huveaune', 'm22000274', NULL);
INSERT INTO Internship (internship_identifier, company_name, keywords, start_date_internship, type, end_date_internship, internship_subject, address, student_number, relevance_score) VALUES ('STA' || '2023' || LPAD(NEXTVAL('Internship_id_counter_seq')::TEXT, 5, '0'), 'Safran', ARRAY['Vente et négociation', 'Programmation web', 'Gestion de bases de données'], '2023-04-01', 'stage', '2023-09-07', 'Conception d''une application mobile de VTC', '50 Allée Amaury 13300 Salon-de-Provence', 'z23000275', NULL);


INSERT INTO Department (department_name, address, department_full_name) VALUES 
('IUT_INFO_AIX', '413 Avenue Gaston Berger 13100 Aix-en-Provence', 'IUT Informatique Aix'),
('IUT_GEA_AIX', '413 Avenue Gaston Berger 13100 Aix-en-Provence', 'IUT GEA Aix'),
('IUT_TC_AIX', '413 Avenue Gaston Berger 13100 Aix-en-Provence', 'IUT TC Aix');

INSERT INTO Is_taught VALUES ('emilie.martin', 'Architecture des ordinateurs');
INSERT INTO Is_taught VALUES ('jean-luc.bernard', 'Gestion de projet');
INSERT INTO Is_taught VALUES ('fatima.benali', 'Communication');
INSERT INTO Is_taught VALUES ('alexandre.dubois', 'Gestion de bases de données');
INSERT INTO Is_taught VALUES ('sofia.rossi', 'Programmation orientée objet');
INSERT INTO Is_taught VALUES ('laurent.moreau', 'Économie');
INSERT INTO Is_taught VALUES ('amina.kebir', 'Droit');
INSERT INTO Is_taught VALUES ('pierre-henri.blanc', 'Gestion financière');
INSERT INTO Is_taught VALUES ('clara.dupont', 'Marketing');
INSERT INTO Is_taught VALUES ('youssef.el-mansouri', 'Communication digitale');
INSERT INTO Is_taught VALUES ('lea.girard', 'Vente et négociation');
INSERT INTO Is_taught VALUES ('sergei.ivanov', 'Anglais');
INSERT INTO Is_taught VALUES ('marie-laure.roux', 'Programmation web');
INSERT INTO Is_taught VALUES ('mehdi.taha', 'Architecture des ordinateurs');
INSERT INTO Is_taught VALUES ('chloe.lefebvre', 'Gestion de projet');
INSERT INTO Is_taught VALUES ('marc.antoine', 'Communication');
INSERT INTO Is_taught VALUES ('nadia.cherif', 'Gestion de bases de données');
INSERT INTO Is_taught VALUES ('henri.delorme', 'Programmation orientée objet');
INSERT INTO Is_taught VALUES ('laura.costa', 'Économie');
INSERT INTO Is_taught VALUES ('francois-xavier.dupuis', 'Droit');
INSERT INTO Is_taught VALUES ('samira.othmani', 'Gestion financière');
INSERT INTO Is_taught VALUES ('philippe.marchand', 'Marketing');
INSERT INTO Is_taught VALUES ('anais.petit', 'Communication digitale');
INSERT INTO Is_taught VALUES ('lucas.royer', 'Vente et négociation');
INSERT INTO Is_taught VALUES ('helene.vidal', 'Anglais');

INSERT INTO Has_role (user_id, role_name, department_name) VALUES
('emilie.martin', 'Enseignant', 'IUT_INFO_AIX'),
('jean-luc.bernard', 'Enseignant', 'IUT_INFO_AIX'),
('fatima.benali', 'Enseignant', 'IUT_INFO_AIX'),
('alexandre.dubois', 'Enseignant', 'IUT_INFO_AIX'),
('sofia.rossi', 'Enseignant', 'IUT_INFO_AIX'),
('laurent.moreau', 'Enseignant', 'IUT_INFO_AIX'),
('amina.kebir', 'Enseignant', 'IUT_INFO_AIX'),
('pierre-henri.blanc', 'Enseignant', 'IUT_INFO_AIX'),
('clara.dupont', 'Enseignant', 'IUT_INFO_AIX'),
('youssef.el-mansouri', 'Enseignant', 'IUT_INFO_AIX'),
('lea.girard', 'Enseignant', 'IUT_INFO_AIX'),
('sergei.ivanov', 'Enseignant', 'IUT_INFO_AIX'),
('marie-laure.roux', 'Enseignant', 'IUT_INFO_AIX'),
('mehdi.taha', 'Enseignant', 'IUT_INFO_AIX'),
('chloe.lefebvre', 'Enseignant', 'IUT_INFO_AIX'),
('marc.antoine', 'Enseignant', 'IUT_INFO_AIX'),
('nadia.cherif', 'Enseignant', 'IUT_INFO_AIX'),
('henri.delorme', 'Enseignant', 'IUT_INFO_AIX'),
('laura.costa', 'Enseignant', 'IUT_INFO_AIX'),
('francois-xavier.dupuis', 'Enseignant', 'IUT_INFO_AIX'),
('samira.othmani', 'Enseignant', 'IUT_INFO_AIX'),
('philippe.marchand', 'Enseignant', 'IUT_INFO_AIX'),
('anais.petit', 'Enseignant', 'IUT_INFO_AIX'),
('lucas.royer', 'Enseignant', 'IUT_INFO_AIX'),
('helene.vidal', 'Enseignant', 'IUT_INFO_AIX'),
('emilie.martin', 'Admin_dep', 'IUT_INFO_AIX');


INSERT INTO Study_at (student_number, department_name) VALUES
('m22000001', 'IUT_INFO_AIX'),
('b22000002', 'IUT_INFO_AIX'),
('b22000003', 'IUT_INFO_AIX'),
('f22000005', 'IUT_INFO_AIX'),
('m21000007', 'IUT_INFO_AIX'),
('b21000009', 'IUT_INFO_AIX'),
('k23000011', 'IUT_INFO_AIX'),
('l23000014', 'IUT_INFO_AIX'),
('n22000016', 'IUT_INFO_AIX'),
('m22000018', 'IUT_INFO_AIX'),
('l21000020', 'IUT_INFO_AIX'),
('p21000023', 'IUT_INFO_AIX'),
('h22000026', 'IUT_INFO_AIX'),
('k22000028', 'IUT_INFO_AIX'),
('s21000030', 'IUT_INFO_AIX'),
('m23000033', 'IUT_INFO_AIX'),
('b22000035', 'IUT_INFO_AIX'),
('a11000037', 'IUT_INFO_AIX'),
('b23000040', 'IUT_INFO_AIX'),
('a22000042', 'IUT_INFO_AIX'),
('t21000044', 'IUT_INFO_AIX'),
('z23000047', 'IUT_INFO_AIX'),
('n22000049', 'IUT_INFO_AIX'),
('a12000004', 'IUT_INFO_AIX'),
('i21000008', 'IUT_INFO_AIX'),
('l23000012', 'IUT_INFO_AIX'),
('a12000017', 'IUT_INFO_AIX'),
('s21000021', 'IUT_INFO_AIX'),
('r23000024', 'IUT_INFO_AIX'),
('g22000027', 'IUT_INFO_AIX'),
('m21000031', 'IUT_INFO_AIX'),
('n22000034', 'IUT_INFO_AIX'),
('b21000038', 'IUT_INFO_AIX'),
('a12000041', 'IUT_INFO_AIX'),
('l21000045', 'IUT_INFO_AIX'),
('w22000048', 'IUT_INFO_AIX'),
('e21000006', 'IUT_INFO_AIX'),
('r21000010', 'IUT_INFO_AIX'),
('n23000013', 'IUT_INFO_AIX'),
('v23000015', 'IUT_INFO_AIX'),
('b22000019', 'IUT_INFO_AIX'),
('b21000022', 'IUT_INFO_AIX'),
('r23000025', 'IUT_INFO_AIX'),
('l21000029', 'IUT_INFO_AIX'),
('k23000032', 'IUT_INFO_AIX'),
('e21000036', 'IUT_INFO_AIX'),
('s23000039', 'IUT_INFO_AIX'),
('b20000043', 'IUT_INFO_AIX'),
('a13000046', 'IUT_INFO_AIX'),
('a21000050', 'IUT_INFO_AIX'),
('g22000051', 'IUT_INFO_AIX'),
('e23000052', 'IUT_INFO_AIX'),
('s21000053', 'IUT_INFO_AIX'),
('b19000054', 'IUT_INFO_AIX'),
('m23000055', 'IUT_INFO_AIX'),
('n20000056', 'IUT_INFO_AIX'),
('c23000057', 'IUT_INFO_AIX'),
('k22000058', 'IUT_INFO_AIX'),
('t23000059', 'IUT_INFO_AIX'),
('l18000060', 'IUT_INFO_AIX'),
('a23000061', 'IUT_INFO_AIX'),
('d22000062', 'IUT_INFO_AIX'),
('p23000063', 'IUT_INFO_AIX'),
('m21000064', 'IUT_INFO_AIX'),
('s23000065', 'IUT_INFO_AIX'),
('m19000066', 'IUT_INFO_AIX'),
('b23000067', 'IUT_INFO_AIX'),
('r22000068', 'IUT_INFO_AIX'),
('n23000069', 'IUT_INFO_AIX'),
('r20000070', 'IUT_INFO_AIX'),
('b23000071', 'IUT_INFO_AIX'),
('p22000072', 'IUT_INFO_AIX'),
('d23000073', 'IUT_INFO_AIX'),
('v21000074', 'IUT_INFO_AIX'),
('l23000075', 'IUT_INFO_AIX'),
('e18000076', 'IUT_INFO_AIX'),
('m23000077', 'IUT_INFO_AIX'),
('h22000078', 'IUT_INFO_AIX'),
('c23000079', 'IUT_INFO_AIX'),
('r23000080', 'IUT_INFO_AIX'),
('d22000081', 'IUT_INFO_AIX'),
('d23000082', 'IUT_INFO_AIX'),
('t20000083', 'IUT_INFO_AIX'),
('n23000084', 'IUT_INFO_AIX'),
('c22000085', 'IUT_INFO_AIX'),
('b23000086', 'IUT_INFO_AIX'),
('r21000087', 'IUT_INFO_AIX'),
('z23000088', 'IUT_INFO_AIX'),
('b19000089', 'IUT_INFO_AIX'),
('d23000090', 'IUT_INFO_AIX'),
('h22000091', 'IUT_INFO_AIX'),
('b23000092', 'IUT_INFO_AIX'),
('k23000093', 'IUT_INFO_AIX'),
('l20000094', 'IUT_INFO_AIX'),
('a23000095', 'IUT_INFO_AIX'),
('g22000096', 'IUT_INFO_AIX'),
('e23000097', 'IUT_INFO_AIX'),
('m21000098', 'IUT_INFO_AIX'),
('d23000099', 'IUT_INFO_AIX'),
('j19000100', 'IUT_INFO_AIX'),
('b23000101', 'IUT_INFO_AIX'),
('n23000102', 'IUT_INFO_AIX'),
('e23000103', 'IUT_INFO_AIX'),
('c23000104', 'IUT_INFO_AIX'),
('b23000105', 'IUT_INFO_AIX'),
('d23000106', 'IUT_INFO_AIX'),
('s23000107', 'IUT_INFO_AIX'),
('t23000108', 'IUT_INFO_AIX'),
('l23000109', 'IUT_INFO_AIX'),
('b23000110', 'IUT_INFO_AIX'),
('m23000111', 'IUT_INFO_AIX'),
('z23000112', 'IUT_INFO_AIX'),
('d23000113', 'IUT_INFO_AIX'),
('t23000114', 'IUT_INFO_AIX'),
('m23000115', 'IUT_INFO_AIX'),
('m23000116', 'IUT_INFO_AIX'),
('a23000117', 'IUT_INFO_AIX'),
('p23000118', 'IUT_INFO_AIX'),
('e23000119', 'IUT_INFO_AIX'),
('l23000120', 'IUT_INFO_AIX'),
('k23000121', 'IUT_INFO_AIX'),
('b23000122', 'IUT_INFO_AIX'),
('n23000123', 'IUT_INFO_AIX'),
('d23000124', 'IUT_INFO_AIX'),
('l23000125', 'IUT_INFO_AIX'),
('a23000126', 'IUT_INFO_AIX'),
('r23000127', 'IUT_INFO_AIX'),
('b23000128', 'IUT_INFO_AIX'),
('k23000129', 'IUT_INFO_AIX'),
('p23000130', 'IUT_INFO_AIX'),
('d23000131', 'IUT_INFO_AIX'),
('m23000132', 'IUT_INFO_AIX'),
('i23000133', 'IUT_INFO_AIX'),
('a23000134', 'IUT_INFO_AIX'),
('s23000135', 'IUT_INFO_AIX'),
('m23000136', 'IUT_INFO_AIX'),
('b23000137', 'IUT_INFO_AIX'),
('r23000138', 'IUT_INFO_AIX'),
('e23000139', 'IUT_INFO_AIX'),
('l23000140', 'IUT_INFO_AIX'),
('b23000141', 'IUT_INFO_AIX'),
('f23000142', 'IUT_INFO_AIX'),
('c23000143', 'IUT_INFO_AIX'),
('g23000144', 'IUT_INFO_AIX'),
('h23000145', 'IUT_INFO_AIX'),
('z23000146', 'IUT_INFO_AIX'),
('l23000147', 'IUT_INFO_AIX'),
('b23000148', 'IUT_INFO_AIX'),
('d23000149', 'IUT_INFO_AIX'),
('e23000150', 'IUT_INFO_AIX'),
('b23000151', 'IUT_INFO_AIX'),
('k23000152', 'IUT_INFO_AIX'),
('d23000153', 'IUT_INFO_AIX'),
('b23000154', 'IUT_INFO_AIX'),
('c23000155', 'IUT_INFO_AIX'),
('f23000156', 'IUT_INFO_AIX'),
('m23000157', 'IUT_INFO_AIX'),
('t23000158', 'IUT_INFO_AIX'),
('l23000159', 'IUT_INFO_AIX'),
('a23000160', 'IUT_INFO_AIX'),
('z23000161', 'IUT_INFO_AIX'),
('d23000162', 'IUT_INFO_AIX'),
('s23000163', 'IUT_INFO_AIX'),
('o23000164', 'IUT_INFO_AIX'),
('g23000165', 'IUT_INFO_AIX'),
('b23000166', 'IUT_INFO_AIX'),
('m23000167', 'IUT_INFO_AIX'),
('l23000168', 'IUT_INFO_AIX'),
('b23000169', 'IUT_INFO_AIX'),
('m23000170', 'IUT_INFO_AIX'),
('e23000171', 'IUT_INFO_AIX'),
('r23000172', 'IUT_INFO_AIX'),
('p23000173', 'IUT_INFO_AIX'),
('b23000174', 'IUT_INFO_AIX'),
('b23000175', 'IUT_INFO_AIX'),
('t23000176', 'IUT_INFO_AIX'),
('n23000177', 'IUT_INFO_AIX'),
('h23000178', 'IUT_INFO_AIX'),
('a23000179', 'IUT_INFO_AIX'),
('l23000180', 'IUT_INFO_AIX'),
('b23000181', 'IUT_INFO_AIX'),
('b23000182', 'IUT_INFO_AIX'),
('l23000183', 'IUT_INFO_AIX'),
('b23000184', 'IUT_INFO_AIX'),
('t23000185', 'IUT_INFO_AIX'),
('z23000186', 'IUT_INFO_AIX'),
('c23000187', 'IUT_INFO_AIX'),
('r23000188', 'IUT_INFO_AIX'),
('z23000189', 'IUT_INFO_AIX'),
('m23000190', 'IUT_INFO_AIX'),
('b23000191', 'IUT_INFO_AIX'),
('o23000192', 'IUT_INFO_AIX'),
('k23000193', 'IUT_INFO_AIX'),
('d23000194', 'IUT_INFO_AIX'),
('e23000195', 'IUT_INFO_AIX'),
('b23000196', 'IUT_INFO_AIX'),
('m23000197', 'IUT_INFO_AIX'),
('b23000198', 'IUT_INFO_AIX'),
('m23000199', 'IUT_INFO_AIX'),
('m23000200', 'IUT_INFO_AIX'),
('b23000201', 'IUT_INFO_AIX'),
('n23000202', 'IUT_INFO_AIX'),
('s23000203', 'IUT_INFO_AIX'),
('f23000204', 'IUT_INFO_AIX'),
('k23000205', 'IUT_INFO_AIX'),
('c23000206', 'IUT_INFO_AIX'),
('f23000207', 'IUT_INFO_AIX'),
('l23000208', 'IUT_INFO_AIX'),
('t23000209', 'IUT_INFO_AIX'),
('m23000210', 'IUT_INFO_AIX'),
('r23000211', 'IUT_INFO_AIX'),
('z23000212', 'IUT_INFO_AIX'),
('a23000213', 'IUT_INFO_AIX'),
('d23000214', 'IUT_INFO_AIX'),
('f23000215', 'IUT_INFO_AIX'),
('r23000216', 'IUT_INFO_AIX'),
('b23000217', 'IUT_INFO_AIX'),
('l23000218', 'IUT_INFO_AIX'),
('a23000219', 'IUT_INFO_AIX'),
('c23000220', 'IUT_INFO_AIX'),
('g23000221', 'IUT_INFO_AIX'),
('k23000222', 'IUT_INFO_AIX'),
('p23000223', 'IUT_INFO_AIX'),
('b23000224', 'IUT_INFO_AIX'),
('y23000225', 'IUT_INFO_AIX'),
('g23000226', 'IUT_INFO_AIX'),
('m23000227', 'IUT_INFO_AIX'),
('z23000228', 'IUT_INFO_AIX'),
('s23000229', 'IUT_INFO_AIX'),
('s23000230', 'IUT_INFO_AIX'),
('b23000231', 'IUT_INFO_AIX'),
('m23000232', 'IUT_INFO_AIX'),
('k23000233', 'IUT_INFO_AIX'),
('a23000234', 'IUT_INFO_AIX'),
('p23000235', 'IUT_INFO_AIX'),
('c23000236', 'IUT_INFO_AIX'),
('a23000237', 'IUT_INFO_AIX'),
('d23000238', 'IUT_INFO_AIX'),
('l23000239', 'IUT_INFO_AIX'),
('b23000240', 'IUT_INFO_AIX'),
('z23000241', 'IUT_INFO_AIX'),
('o23000242', 'IUT_INFO_AIX'),
('b23000243', 'IUT_INFO_AIX'),
('h23000244', 'IUT_INFO_AIX'),
('a23000245', 'IUT_INFO_AIX'),
('l23000246', 'IUT_INFO_AIX'),
('g23000247', 'IUT_INFO_AIX'),
('r23000248', 'IUT_INFO_AIX'),
('s23000249', 'IUT_INFO_AIX'),
('b23000250', 'IUT_INFO_AIX'),
('m23000251', 'IUT_INFO_AIX'),
('z21000252', 'IUT_INFO_AIX'),
('m22000253', 'IUT_INFO_AIX'),
('b21000254', 'IUT_INFO_AIX'),
('z23000255', 'IUT_INFO_AIX'),
('b21000256', 'IUT_INFO_AIX'),
('m23000257', 'IUT_INFO_AIX'),
('n22000258', 'IUT_INFO_AIX'),
('l23000259', 'IUT_INFO_AIX'),
('m23000260', 'IUT_INFO_AIX'),
('n22000261', 'IUT_INFO_AIX'),
('d21000262', 'IUT_INFO_AIX'),
('d22000263', 'IUT_INFO_AIX'),
('r23000264', 'IUT_INFO_AIX'),
('i22000265', 'IUT_INFO_AIX'),
('b22000266', 'IUT_INFO_AIX'),
('a22000267', 'IUT_INFO_AIX'),
('l22000268', 'IUT_INFO_AIX'),
('m23000269', 'IUT_INFO_AIX'),
('b22000270', 'IUT_INFO_AIX'),
('b22000271', 'IUT_INFO_AIX'),
('d22000272', 'IUT_INFO_AIX'),
('m23000273', 'IUT_INFO_AIX'),
('m22000274', 'IUT_INFO_AIX'),
('z23000275', 'IUT_INFO_AIX');


INSERT INTO Has_address (id_teacher, address, type) VALUES
('emilie.martin', '12 Rue des Cordeliers 13100 Aix-en-Provence', 'Domicile_1'),
('emilie.martin', '39 Avenue des Chartreux 13004 Marseille 4e Arrondissement', 'Travail_1'),
('jean-luc.bernard', '45 Avenue Victor Hugo 84320 Entraigues-sur-la-Sorgue', 'Domicile_1'),
('jean-luc.bernard', '8 Impasse des Peupliers 13008 Marseille', 'Domicile_2'),
('fatima.benali', '7 Rue de la Molle 13100 Aix-en-Provence', 'Domicile_1'),
('alexandre.dubois', '880 Rte de Mimet 13120 Gardanne', 'Travail_1'),
('alexandre.dubois', '49 Lotissement Les Mûriers 13530 Trets', 'Batiment'),
('sofia.rossi', '22 Boulevard Gambetta 06000 Nice', 'Domicile_1'),
('sofia.rossi', '54 Boulevard de Plombières 13014 Marseille 14e Arrondissement', 'Domicile_2'),
('laurent.moreau', '18 Chemin des Bastides 13820 Ensuès-la-Redonne', 'Domicile_1'),
('amina.kebir', '9 Rue Boulegon 13100 Aix-en-Provence', 'Domicile_1'),
('amina.kebir', 'Lycée Cézanne 13100 Aix-en-Provence', 'Travail_1'),
('pierre-henri.blanc', '14 Route de Nice 06740 Châteauneuf-Grasse', 'Domicile_1'),
('pierre-henri.blanc', '5 Rue Rifle-Rafle 13090 Aix-en-Provence', 'Domicile_2'),
('clara.dupont', 'IUT Aix-Marseille 13090 Aix-en-Provence', 'Travail_1'),
('youssef.el-mansouri', '27 Quai des Belges 13001 Marseille', 'Domicile_1'),
('youssef.el-mansouri', 'Technopôle de l''Arbois 13545 Aix-en-Provence', 'Travail_2'),
('lea.girard', '7 Impasse du Tournepierre 13500 Martigues', 'Domicile_1'),
('sergei.ivanov', '33 Traverse de la Montre 13012 Marseille', 'Domicile_1'),
('marie-laure.roux', '7 Allée des Cèdres 13620 Carry-le-Rouet', 'Domicile_1'),
('marie-laure.roux', 'École polytechnique universitaire de Marseille 13009 Marseille', 'Travail_1'),
('mehdi.taha', '19 Rue de la République 13001 Marseille', 'Domicile_1'),
('chloe.lefebvre', '4 Cours Mirabeau 13100 Aix-en-Provence', 'Domicile_1'),
('chloe.lefebvre', '12 Rue des Écoles 84240 La Bastide-des-Jourdans', 'Domicile_2'),
('chloe.lefebvre', '55 Boulevard des Libérateurs 13011 Marseille', 'Travail_1'),
('chloe.lefebvre', '63 Chemin du Merlançon 13400 La Coueste', 'Batiment'),
('marc.antoine', 'Lycée Vauvenargues 13100 Aix-en-Provence', 'Travail_1'),
('nadia.cherif', '22 Avenue Félix Ziem 13500 Martigues', 'Domicile_1'),
('henri.delorme', '620 Rue du Barry 05130 Tallard', 'Domicile_1'),
('henri.delorme', '21 Rue Mignet 13100 Aix-en-Provence', 'Domicile_2'),
('laura.costa', '128 Avenue Jean Jaurès 13700 Marignane', 'Domicile_1'),
('francois-xavier.dupuis', '66 Allée de la Bouissonado 13500 Martigues', 'Travail_1'),
('samira.othmani', '10 Rue de la Paix 13240 Septèmes-les-Vallons', 'Domicile_1'),
('philippe.marchand', '1 Boulevard Jean Jaurès 13400 Aubagne', 'Domicile_1'),
('anais.petit', '7 Traverse de l''Étang 13500 Martigues', 'Domicile_1'),
('lucas.royer', '4 Avenue Gaston Berger 13100 Aix-en-Provence', 'Domicile_1'),
('lucas.royer', '3 Avenue Robert Schuman 13100 Aix-en-Provence', 'Travail_2'),
('helene.vidal', '18 Chemin des Sources 13880 Velaux', 'Domicile_1');
