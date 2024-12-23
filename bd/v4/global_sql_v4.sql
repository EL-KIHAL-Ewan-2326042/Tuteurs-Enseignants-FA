DROP TABLE IF EXISTS Backup;
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

CREATE TABLE Teacher(
    Id_teacher VARCHAR(10),
    Teacher_name VARCHAR(50),
    Teacher_firstname VARCHAR(50),
    Maxi_number_trainees INT,
    PRIMARY KEY(Id_teacher)
);

CREATE TABLE Student(
    Student_number VARCHAR(10),
    Student_name VARCHAR(50) NOT NULL,
    Student_firstname VARCHAR(50) NOT NULL,
    Formation VARCHAR(50),
    Class_group VARCHAR(50),
    PRIMARY KEY(Student_number)
);

CREATE TABLE Discipline(
    Discipline_name VARCHAR(50),
    PRIMARY KEY(Discipline_name)
);

CREATE TABLE User_connect(
    User_id VARCHAR(10),
    User_pass VARCHAR(100),
    PRIMARY KEY(User_id)
);

CREATE TABLE Role(
    Role_name VARCHAR(50),
    PRIMARY KEY(Role_name)
);

CREATE TABLE Distribution_criteria(
    Name_criteria VARCHAR(50),
    Description VARCHAR(500) NOT NULL,
    PRIMARY KEY(Name_criteria)
);

CREATE TABLE Addr_name(
    Address VARCHAR(100),
    PRIMARY KEY(Address)
);

CREATE TABLE Address_type(
    Type VARCHAR(50),
    PRIMARY KEY(Type)
);

CREATE TABLE Id_backup(
    Id_backup INT,
    PRIMARY KEY(Id_backup)
);

CREATE TABLE Internship(
    Internship_identifier VARCHAR(20),
    Company_name VARCHAR(50) NOT NULL,
    Keywords VARCHAR(200),
    Start_date_internship DATE NOT NULL,
    Type VARCHAR(50),
    End_date_internship DATE NOT NULL,
    Internship_subject VARCHAR(150) NOT NULL,
    Address VARCHAR(100) NOT NULL,
    Student_number VARCHAR(10) NOT NULL,
    Relevance_score FLOAT,
    Id_teacher VARCHAR(10),
    PRIMARY KEY(Internship_identifier),
    FOREIGN KEY(Id_teacher) REFERENCES Teacher(Id_teacher),
    FOREIGN KEY(Address) REFERENCES Addr_name(Address),
    FOREIGN KEY(Student_number) REFERENCES Student(Student_number)
);

CREATE TABLE Department(
    Department_name VARCHAR(50),
    Address VARCHAR(100) NOT NULL,
    PRIMARY KEY(Department_name),
    FOREIGN KEY(Address) REFERENCES Addr_name(Address)
);

CREATE TABLE Is_requested(
    Id_teacher VARCHAR(10),
    Internship_identifier VARCHAR(50),
    PRIMARY KEY(Id_teacher, Internship_identifier),
    FOREIGN KEY(Id_teacher) REFERENCES Teacher(Id_teacher),
    FOREIGN KEY(Internship_identifier) REFERENCES Internship(Internship_identifier)
);

CREATE TABLE Is_taught(
    Id_teacher VARCHAR(10),
    Discipline_name VARCHAR(50),
    PRIMARY KEY(Id_teacher, Discipline_name),
    FOREIGN KEY(Id_teacher) REFERENCES Teacher(Id_teacher),
    FOREIGN KEY(Discipline_name) REFERENCES Discipline(Discipline_name)
);

CREATE TABLE Has_role(
    User_id VARCHAR(10),
    Role_name VARCHAR(50) NOT NULL,
    Department_name VARCHAR(50) NOT NULL,
    PRIMARY KEY(User_id, Role_name),
    FOREIGN KEY(User_id) REFERENCES User_connect(User_id),
    FOREIGN KEY(Role_name) REFERENCES Role(Role_name)
);

CREATE TABLE Study_at(
    Student_number VARCHAR(10),
    Department_name VARCHAR(50),
    PRIMARY KEY(Student_number, Department_name),
    FOREIGN KEY(Student_number) REFERENCES Student(Student_number),
    FOREIGN KEY(Department_name) REFERENCES Department(Department_name)
);

CREATE TABLE Has_address(
    Id_teacher VARCHAR(10),
    Address VARCHAR(100),
    Type VARCHAR(50),
    PRIMARY KEY(Id_teacher, Address, Type),
    FOREIGN KEY(Id_teacher) REFERENCES Teacher(Id_teacher),
    FOREIGN KEY(Address) REFERENCES Addr_name(Address),
    FOREIGN KEY(Type) REFERENCES Address_type(Type)
);

CREATE TABLE Distance(
    Id_teacher VARCHAR(10),
    Internship_identifier VARCHAR(20),
    Distance INT,
    PRIMARY KEY(Id_teacher, Internship_identifier),
    FOREIGN KEY(Id_teacher) REFERENCES Teacher(Id_teacher),
    FOREIGN KEY(Internship_identifier) REFERENCES Internship(Internship_identifier)
);

CREATE TABLE Backup(
    User_id VARCHAR(10),
    Name_criteria VARCHAR(50),
    Id_backup INT,
    Coef INT,
    Name_save VARCHAR(100),
    Is_checked BOOLEAN DEFAULT TRUE,
    PRIMARY KEY(User_id, Name_criteria, Id_backup),
    FOREIGN KEY(User_id) REFERENCES User_connect(User_id),
    FOREIGN KEY(Name_criteria) REFERENCES Distribution_criteria(Name_criteria),
    FOREIGN KEY(Id_backup) REFERENCES Id_backup(Id_backup)
);

CREATE OR REPLACE FUNCTION check_is_requested_assignment()
RETURNS TRIGGER AS $$
    BEGIN
        IF EXISTS (SELECT 1 FROM Internship WHERE Internship_identifier = NEW.Internship_identifier AND Id_teacher IS NOT NULL) THEN
            RAISE EXCEPTION 'Un professeur est déjà assigné à ce stage';
        END IF;
        RETURN NEW;
    END;
$$ LANGUAGE plpgsql;

CREATE TRIGGER check_Is_requested_assignment
    BEFORE INSERT ON Is_requested
    FOR EACH ROW
    EXECUTE FUNCTION check_is_requested_assignment();


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
        FOR user_id IN SELECT user_connect.user_id FROM User_connect
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



INSERT INTO Distribution_criteria (Name_criteria, Description) VALUES ('A été responsable', 'Prendre en compte le fait que le prof ait déjà travaillé avec l élève');
INSERT INTO Distribution_criteria (Name_criteria, Description) VALUES ('Distance', 'Prendre en compte la distance entre le lieu du stage et l adresse renseigné la plus proche pour le responsable');
INSERT INTO Distribution_criteria (Name_criteria, Description) VALUES ('Cohérence', 'Prendre en compte la corrélation entre la matière enseigner par le responsable et le sujet du stage');
INSERT INTO Distribution_criteria (Name_criteria, Description) VALUES ('Est demandé', 'Prendre en compte le fait que le responsable demande le stage');

INSERT INTO Teacher (Id_teacher, Teacher_name, Teacher_firstname, Maxi_number_trainees) VALUES ('B22662146', 'CASES', 'Murphy', 3);
INSERT INTO Teacher (Id_teacher, Teacher_name, Teacher_firstname, Maxi_number_trainees) VALUES ('R14328249', 'ALVARADOS', 'Christen', 2);
INSERT INTO Teacher (Id_teacher, Teacher_name, Teacher_firstname, Maxi_number_trainees) VALUES ('G42185815', 'KOCHS', 'Barry', 4);
INSERT INTO Teacher (Id_teacher, Teacher_name, Teacher_firstname, Maxi_number_trainees) VALUES ('R32281327', 'DUDLEYS', 'Wylie', 5);
INSERT INTO Teacher (Id_teacher, Teacher_name, Teacher_firstname, Maxi_number_trainees) VALUES ('O75041198', 'SLATERS', 'Colleen', 1);
INSERT INTO Teacher (Id_teacher, Teacher_name, Teacher_firstname, Maxi_number_trainees) VALUES ('V73654623', 'MONROES', 'Linus', 2);
INSERT INTO Teacher (Id_teacher, Teacher_name, Teacher_firstname, Maxi_number_trainees) VALUES ('Z17235374', 'FISCHERS', 'Isabella', 4);
INSERT INTO Teacher (Id_teacher, Teacher_name, Teacher_firstname, Maxi_number_trainees) VALUES ('R84623671', 'MCCARTYS', 'Claudia', 3);
INSERT INTO Teacher (Id_teacher, Teacher_name, Teacher_firstname, Maxi_number_trainees) VALUES ('D78106598', 'CASTANEDAS', 'Beau', 5);
INSERT INTO Teacher (Id_teacher, Teacher_name, Teacher_firstname, Maxi_number_trainees) VALUES ('S85694088', 'DORSEYS', 'Colby', 2);
INSERT INTO Teacher (Id_teacher, Teacher_name, Teacher_firstname, Maxi_number_trainees) VALUES ('Y68664772', 'COTES', 'Juliet', 4);
INSERT INTO Teacher (Id_teacher, Teacher_name, Teacher_firstname, Maxi_number_trainees) VALUES ('Q66676064', 'MCPHERSONS', 'Perry', 3);
INSERT INTO Teacher (Id_teacher, Teacher_name, Teacher_firstname, Maxi_number_trainees) VALUES ('B10648624', 'HANEYS', 'Nolan', 5);
INSERT INTO Teacher (Id_teacher, Teacher_name, Teacher_firstname, Maxi_number_trainees) VALUES ('N26332417', 'MEYERSS', 'Mufutau', 1);
INSERT INTO Teacher (Id_teacher, Teacher_name, Teacher_firstname, Maxi_number_trainees) VALUES ('F42358144', 'AGUIRRES', 'Halee', 2);
INSERT INTO Teacher (Id_teacher, Teacher_name, Teacher_firstname, Maxi_number_trainees) VALUES ('I57332640', 'EMERSONS', 'Jenette', 4);
INSERT INTO Teacher (Id_teacher, Teacher_name, Teacher_firstname, Maxi_number_trainees) VALUES ('B51423637', 'CRUZS', 'Hayden', 3);
INSERT INTO Teacher (Id_teacher, Teacher_name, Teacher_firstname, Maxi_number_trainees) VALUES ('C45328794', 'LAWRENCES', 'Brittany', 5);
INSERT INTO Teacher (Id_teacher, Teacher_name, Teacher_firstname, Maxi_number_trainees) VALUES ('H48344613', 'IRWINS', 'Alisa', 2);
INSERT INTO Teacher (Id_teacher, Teacher_name, Teacher_firstname, Maxi_number_trainees) VALUES ('R41814241', 'MILESS', 'Aidan', 4);
INSERT INTO Teacher (Id_teacher, Teacher_name, Teacher_firstname, Maxi_number_trainees) VALUES ('A23456789', 'PARKER', 'Megan', 3);
INSERT INTO Teacher (Id_teacher, Teacher_name, Teacher_firstname, Maxi_number_trainees) VALUES ('B34567890', 'TURNER', 'John', 4);
INSERT INTO Teacher (Id_teacher, Teacher_name, Teacher_firstname, Maxi_number_trainees) VALUES ('C45678901', 'MARTIN', 'Sophia', 2);
INSERT INTO Teacher (Id_teacher, Teacher_name, Teacher_firstname, Maxi_number_trainees) VALUES ('D56789012', 'THOMPSON', 'Ethan', 5);
INSERT INTO Teacher (Id_teacher, Teacher_name, Teacher_firstname, Maxi_number_trainees) VALUES ('E67890123', 'ANDERSON', 'Olivia', 3);
-- insert de prof de TC
INSERT INTO Teacher (Id_teacher, Teacher_name, Teacher_firstname, Maxi_number_trainees) VALUES ('A12345678', 'BENNETTS', 'Olivia', 3);
INSERT INTO Teacher (Id_teacher, Teacher_name, Teacher_firstname, Maxi_number_trainees) VALUES ('B23456789', 'DAVISS', 'Michael', 4);
INSERT INTO Teacher (Id_teacher, Teacher_name, Teacher_firstname, Maxi_number_trainees) VALUES ('C34567890', 'THOMPSONS', 'Emma', 2);
INSERT INTO Teacher (Id_teacher, Teacher_name, Teacher_firstname, Maxi_number_trainees) VALUES ('D45678901', 'WALKERS', 'Ethan', 5);
INSERT INTO Teacher (Id_teacher, Teacher_name, Teacher_firstname, Maxi_number_trainees) VALUES ('E56789012', 'MILLERS', 'Sophia', 3);
-- insert de prof de GEA
INSERT INTO Teacher (Id_teacher, Teacher_name, Teacher_firstname, Maxi_number_trainees) VALUES ('F67890123', 'ROBERTSS', 'Lucas', 2);
INSERT INTO Teacher (Id_teacher, Teacher_name, Teacher_firstname, Maxi_number_trainees) VALUES ('G78901234', 'HALLS', 'Mia', 4);
INSERT INTO Teacher (Id_teacher, Teacher_name, Teacher_firstname, Maxi_number_trainees) VALUES ('H89012345', 'ALLENS', 'Aiden', 3);
INSERT INTO Teacher (Id_teacher, Teacher_name, Teacher_firstname, Maxi_number_trainees) VALUES ('I90123456', 'YOUNGS', 'Zoe', 1);
INSERT INTO Teacher (Id_teacher, Teacher_name, Teacher_firstname, Maxi_number_trainees) VALUES ('J01234567', 'WRIGHTS', 'Liam', 5);

-- etudiant en INFO à l'IUT d'aix en provence
INSERT INTO Student (Student_number,Student_name,Student_firstname,formation,Class_group) VALUES ('B82656814','Kerrs','Reese','BUT_INFO','B-1_An2');
INSERT INTO Student (Student_number,Student_name,Student_firstname,formation,Class_group) VALUES ('Y26472238','Mcculloughs','Jorden','BUT_INFO','B-2_An3');
INSERT INTO Student (Student_number,Student_name,Student_firstname,formation,Class_group) VALUES ('B14955698','Cranes','Lester','BUT_INFO','A2-2_An3');
INSERT INTO Student (Student_number,Student_name,Student_firstname,formation,Class_group) VALUES ('S47843997','Mendozas','Hiram','BUT_INFO','A2-2_An2');
INSERT INTO Student (Student_number,Student_name,Student_firstname,formation,Class_group) VALUES ('D83154177','Burnss','Kaseem','BUT_INFO','B-1_An2');
INSERT INTO Student (Student_number,Student_name,Student_firstname,formation,Class_group) VALUES ('D97153746','Hoppers','Ira','BUT_INFO','A2-2_An2');
INSERT INTO Student (Student_number,Student_name,Student_firstname,formation,Class_group) VALUES ('O18468102','Yatess','Price','BUT_INFO','A2-1_An2');
INSERT INTO Student (Student_number,Student_name,Student_firstname,formation,Class_group) VALUES ('Y51150412','Deleons','Quail','BUT_INFO','B-2_An2');
INSERT INTO Student (Student_number,Student_name,Student_firstname,formation,Class_group) VALUES ('U84466434','Dudleys','Xyla','BUT_INFO','B-2_An2');
INSERT INTO Student (Student_number,Student_name,Student_firstname,formation,Class_group) VALUES ('P43090772','Ruizs','Quail','BUT_INFO','A2-1_An2');
INSERT INTO Student (Student_number,Student_name,Student_firstname,formation,Class_group) VALUES ('H47232920','Hortons','Murphy','BUT_INFO','A1-2_An3');
INSERT INTO Student (Student_number,Student_name,Student_firstname,formation,Class_group) VALUES ('C35008429','Mosleys','Ferris','BUT_INFO','B-2_An3');
INSERT INTO Student (Student_number,Student_name,Student_firstname,formation,Class_group) VALUES ('U64274615','Days','Stacey','BUT_INFO','A2-2_An3');
INSERT INTO Student (Student_number,Student_name,Student_firstname,formation,Class_group) VALUES ('C67683232','Whitneys','Olivia','BUT_INFO','B-2_An2');
INSERT INTO Student (Student_number,Student_name,Student_firstname,formation,Class_group) VALUES ('K71754824','Cantus','Ray','BUT_INFO','A2-2_An3');
INSERT INTO Student (Student_number,Student_name,Student_firstname,formation,Class_group) VALUES ('S60552402','Grahams','Darryl','BUT_INFO','A2-1_An2');
INSERT INTO Student (Student_number,Student_name,Student_firstname,formation,Class_group) VALUES ('R15640225','Masseys','Russell','BUT_INFO','B-1_An2');
INSERT INTO Student (Student_number,Student_name,Student_firstname,formation,Class_group) VALUES ('Q49315273','Gilmores','Charde','BUT_INFO','A1-1_An2');
INSERT INTO Student (Student_number,Student_name,Student_firstname,formation,Class_group) VALUES ('Q44691862','Deleons','Alexander','BUT_INFO','B-2_An2');
INSERT INTO Student (Student_number,Student_name,Student_firstname,formation,Class_group) VALUES ('R27448536','Huffs','Teegan','BUT_INFO','A2-2_An3');
INSERT INTO Student (Student_number,Student_name,Student_firstname,formation,Class_group) VALUES ('F03019685','Scotts','Quinn','BUT_INFO','A2-1_An2');
INSERT INTO Student (Student_number,Student_name,Student_firstname,formation,Class_group) VALUES ('B13433362','Wests','Mollie','BUT_INFO','A2-1_An2');
INSERT INTO Student (Student_number,Student_name,Student_firstname,formation,Class_group) VALUES ('C70741722','Cobbs','Marah','BUT_INFO','B-1_An2');
INSERT INTO Student (Student_number,Student_name,Student_firstname,formation,Class_group) VALUES ('Z21392555','Baileys','Bree','BUT_INFO','A2-2_An3');
INSERT INTO Student (Student_number,Student_name,Student_firstname,formation,Class_group) VALUES ('D06749632','Vazquezs','Caleb','BUT_INFO','B-2_An3');
INSERT INTO Student (Student_number,Student_name,Student_firstname,formation,Class_group) VALUES ('S33551879','Mercados','Anika','BUT_INFO','A2-2_An3');
INSERT INTO Student (Student_number,Student_name,Student_firstname,formation,Class_group) VALUES ('W36736211','Bradleys','Cally','BUT_INFO','B-1_An2');
INSERT INTO Student (Student_number,Student_name,Student_firstname,formation,Class_group) VALUES ('R56931231','Humphreys','Mark','BUT_INFO','A2-2_An3');
INSERT INTO Student (Student_number,Student_name,Student_firstname,formation,Class_group) VALUES ('C25155368','Fullers','Olga','BUT_INFO','A1-1_An2');
INSERT INTO Student (Student_number,Student_name,Student_firstname,formation,Class_group) VALUES ('N65851779','Hatfields','Eleanor','BUT_INFO','A2-1_An2');
INSERT INTO Student (Student_number,Student_name,Student_firstname,formation,Class_group) VALUES ('B61337468','Fredericks','Sylvester','BUT_INFO','B-1_An2');
INSERT INTO Student (Student_number,Student_name,Student_firstname,formation,Class_group) VALUES ('X13183474','Potters','Odessa','BUT_INFO','A2-2_An3');
INSERT INTO Student (Student_number,Student_name,Student_firstname,formation,Class_group) VALUES ('V88128782','Mathewss','Halla','BUT_INFO','A2-2_An2');
INSERT INTO Student (Student_number,Student_name,Student_firstname,formation,Class_group) VALUES ('T37746743','Weavers','Dale','BUT_INFO','A1-2_An2');
INSERT INTO Student (Student_number,Student_name,Student_firstname,formation,Class_group) VALUES ('B62553911','Alvarados','Phillip','BUT_INFO','B-2_An2');
INSERT INTO Student (Student_number,Student_name,Student_firstname,formation,Class_group) VALUES ('L86338389','Sellerss','Xena','BUT_INFO','A1-1_An2');
INSERT INTO Student (Student_number,Student_name,Student_firstname,formation,Class_group) VALUES ('I69456267','Velazquezs','Ignatius','BUT_INFO','A1-2_An2');
INSERT INTO Student (Student_number,Student_name,Student_firstname,formation,Class_group) VALUES ('B40239339','Forbess','Alana','BUT_INFO','B-2_An2');
INSERT INTO Student (Student_number,Student_name,Student_firstname,formation,Class_group) VALUES ('H14863692','Terrys','Autumn','BUT_INFO','A2-2_An3');
INSERT INTO Student (Student_number,Student_name,Student_firstname,formation,Class_group) VALUES ('T74730069','Monroes','Jarrod','BUT_INFO','A2-2_An3');
INSERT INTO Student (Student_number,Student_name,Student_firstname,formation,Class_group) VALUES ('P15063542','Lancasters','Lee','BUT_INFO','A2-1_An2');
INSERT INTO Student (Student_number,Student_name,Student_firstname,formation,Class_group) VALUES ('M33382558','Macdonalds','Chaim','BUT_INFO','A2-1_An2');
INSERT INTO Student (Student_number,Student_name,Student_firstname,formation,Class_group) VALUES ('U60431135','Mosss','Ian','BUT_INFO','B-2_An2');
INSERT INTO Student (Student_number,Student_name,Student_firstname,formation,Class_group) VALUES ('M34783033','Rodgerss','Fulton','BUT_INFO','A1-1_An2');
INSERT INTO Student (Student_number,Student_name,Student_firstname,formation,Class_group) VALUES ('O64863218','Pruitts','Kaitlin','BUT_INFO','B-2_An3');
INSERT INTO Student (Student_number,Student_name,Student_firstname,formation,Class_group) VALUES ('V51296562','Burnss','Marny','BUT_INFO','A2-2_An3');
INSERT INTO Student (Student_number,Student_name,Student_firstname,formation,Class_group) VALUES ('X62847517','Keiths','Edward','BUT_INFO','A2-2_An3');
INSERT INTO Student (Student_number,Student_name,Student_firstname,formation,Class_group) VALUES ('J95442213','Strongs','Kieran','BUT_INFO','B-1_An2');
INSERT INTO Student (Student_number,Student_name,Student_firstname,formation,Class_group) VALUES ('J17865151','Mcleans','Leonard','BUT_INFO','A1-1_An2');
INSERT INTO Student (Student_number,Student_name,Student_firstname,formation,Class_group) VALUES ('E35962258','Navarros','Germane','BUT_INFO','A1-1_An2');
INSERT INTO Student (Student_number,Student_name,Student_firstname,formation,Class_group) VALUES ('D93211952','Manns','Helen','BUT_INFO','A2-2_An3');
INSERT INTO Student (Student_number,Student_name,Student_firstname,formation,Class_group) VALUES ('K11370670','Colemans','Nicholas','BUT_INFO','A2-2_An3');
INSERT INTO Student (Student_number,Student_name,Student_firstname,formation,Class_group) VALUES ('L61872638','Mcdaniels','Hanna','BUT_INFO','A2-2_An2');
INSERT INTO Student (Student_number,Student_name,Student_firstname,formation,Class_group) VALUES ('L10783379','Underwoods','Dalton','BUT_INFO','A2-2_An3');
INSERT INTO Student (Student_number,Student_name,Student_firstname,formation,Class_group) VALUES ('K14815933','Burkes','Coby','BUT_INFO','B-1_An2');
INSERT INTO Student (Student_number,Student_name,Student_firstname,formation,Class_group) VALUES ('L24677322','Pollards','Nehru','BUT_INFO','A2-2_An2');
INSERT INTO Student (Student_number,Student_name,Student_firstname,formation,Class_group) VALUES ('C90023858','Dales','Chase','BUT_INFO','B-1_An2');
INSERT INTO Student (Student_number,Student_name,Student_firstname,formation,Class_group) VALUES ('I40581417','Lyonss','Basia','BUT_INFO','B-2_An2');
INSERT INTO Student (Student_number,Student_name,Student_firstname,formation,Class_group) VALUES ('U44158779','Lowerys','Juliet','BUT_INFO','B-2_An2');
INSERT INTO Student (Student_number,Student_name,Student_firstname,formation,Class_group) VALUES ('U18373223','Lancasters','Hakeem','BUT_INFO','B-2_An2');
INSERT INTO Student (Student_number,Student_name,Student_firstname,formation,Class_group) VALUES ('Q61178516','Patricks','Emerald','BUT_INFO','A2-1_An2');
INSERT INTO Student (Student_number,Student_name,Student_firstname,formation,Class_group) VALUES ('X76731856','Pucketts','Buffy','BUT_INFO','B-1_An2');
INSERT INTO Student (Student_number,Student_name,Student_firstname,formation,Class_group) VALUES ('T97220228','Keys','Calvin','BUT_INFO','B-1_An2');
INSERT INTO Student (Student_number,Student_name,Student_firstname,formation,Class_group) VALUES ('G12824677','Dejesuss','Cameron','BUT_INFO','A1-1_An2');
INSERT INTO Student (Student_number,Student_name,Student_firstname,formation,Class_group) VALUES ('V62147234','Averys','Matthew','BUT_INFO','A2-1_An2');
INSERT INTO Student (Student_number,Student_name,Student_firstname,formation,Class_group) VALUES ('G23254613','Burriss','Chancellor','BUT_INFO','B-1_An2');
INSERT INTO Student (Student_number,Student_name,Student_firstname,formation,Class_group) VALUES ('Q78353565','Wattss','Derek','BUT_INFO','A1-2_An2');
INSERT INTO Student (Student_number,Student_name,Student_firstname,formation,Class_group) VALUES ('C86247631','Rochas','Brenden','BUT_INFO','A1-1_An2');
INSERT INTO Student (Student_number,Student_name,Student_firstname,formation,Class_group) VALUES ('L27312376','Ruizs','Emerald','BUT_INFO','A1-2_An3');
INSERT INTO Student (Student_number,Student_name,Student_firstname,formation,Class_group) VALUES ('P36571511','Bryants','Sylvester','BUT_INFO','A1-2_An2');
INSERT INTO Student (Student_number,Student_name,Student_firstname,formation,Class_group) VALUES ('P08567312','Wilcoxs','Michael','BUT_INFO','A2-2_An2');
INSERT INTO Student (Student_number,Student_name,Student_firstname,formation,Class_group) VALUES ('N63215569','Larsons','Haley','BUT_INFO','B-1_An2');
INSERT INTO Student (Student_number,Student_name,Student_firstname,formation,Class_group) VALUES ('F97127967','Buckleys','Quon','BUT_INFO','B-1_An2');
INSERT INTO Student (Student_number,Student_name,Student_firstname,formation,Class_group) VALUES ('M87422896','Kemps','Jin','BUT_INFO','B-2_An2');
INSERT INTO Student (Student_number,Student_name,Student_firstname,formation,Class_group) VALUES ('E61327716','Palmers','Scott','BUT_INFO','A2-1_An2');
INSERT INTO Student (Student_number,Student_name,Student_firstname,formation,Class_group) VALUES ('E89368273','Bankss','Dante','BUT_INFO','A2-1_An2');
INSERT INTO Student (Student_number,Student_name,Student_firstname,formation,Class_group) VALUES ('B66537879','Pottss','Melanie','BUT_INFO','A2-2_An2');
INSERT INTO Student (Student_number,Student_name,Student_firstname,formation,Class_group) VALUES ('R47255376','Hughess','Zane','BUT_INFO','A2-1_An2');
INSERT INTO Student (Student_number,Student_name,Student_firstname,formation,Class_group) VALUES ('N38268628','Englishs','Urielle','BUT_INFO','B-2_An3');
INSERT INTO Student (Student_number,Student_name,Student_firstname,formation,Class_group) VALUES ('O14132859','Sanderss','Ciaran','BUT_INFO','A2-1_An2');
INSERT INTO Student (Student_number,Student_name,Student_firstname,formation,Class_group) VALUES ('J93570177','Harriss','Melanie','BUT_INFO','A2-2_An2');
INSERT INTO Student (Student_number,Student_name,Student_firstname,formation,Class_group) VALUES ('M04459427','Pachecos','Lamar','BUT_INFO','A1-2_An2');
INSERT INTO Student (Student_number,Student_name,Student_firstname,formation,Class_group) VALUES ('I07746111','Conways','Dennis','BUT_INFO','A2-1_An2');
INSERT INTO Student (Student_number,Student_name,Student_firstname,formation,Class_group) VALUES ('P08664347','Sandovals','Grady','BUT_INFO','A1-2_An3');
INSERT INTO Student (Student_number,Student_name,Student_firstname,formation,Class_group) VALUES ('X77543350','Tanners','Debra','BUT_INFO','B-2_An3');
INSERT INTO Student (Student_number,Student_name,Student_firstname,formation,Class_group) VALUES ('L65151712','Brennans','Dacey','BUT_INFO','B-2_An3');
INSERT INTO Student (Student_number,Student_name,Student_firstname,formation,Class_group) VALUES ('C69818318','Stevensons','Acton','BUT_INFO','A1-2_An3');
INSERT INTO Student (Student_number,Student_name,Student_firstname,formation,Class_group) VALUES ('O53891790','Rodriquezs','Matthew','BUT_INFO','A1-1_An2');
INSERT INTO Student (Student_number,Student_name,Student_firstname,formation,Class_group) VALUES ('P81524434','Stephensons','Sawyer','BUT_INFO','A1-2_An3');
INSERT INTO Student (Student_number,Student_name,Student_firstname,formation,Class_group) VALUES ('V36767537','Prestons','Jemima','BUT_INFO','A2-1_An2');
INSERT INTO Student (Student_number,Student_name,Student_firstname,formation,Class_group) VALUES ('X75842440','Prices','Alexis','BUT_INFO','A1-2_An3');
INSERT INTO Student (Student_number,Student_name,Student_firstname,formation,Class_group) VALUES ('I89553120','Johnss','Felicia','BUT_INFO','A2-1_An2');
INSERT INTO Student (Student_number,Student_name,Student_firstname,formation,Class_group) VALUES ('T73738333','Nunezs','Craig','BUT_INFO','A2-2_An2');
INSERT INTO Student (Student_number,Student_name,Student_firstname,formation,Class_group) VALUES ('M82251745','Potters','Giacomo','BUT_INFO','B-1_An2');
INSERT INTO Student (Student_number,Student_name,Student_firstname,formation,Class_group) VALUES ('R18634641','Gatess','Declan','BUT_INFO','B-1_An2');
INSERT INTO Student (Student_number,Student_name,Student_firstname,formation,Class_group) VALUES ('P48118413','Stevensons','Evelyn','BUT_INFO','A2-2_An3');
INSERT INTO Student (Student_number,Student_name,Student_firstname,formation,Class_group) VALUES ('U36743854','Schneiders','Britanni','BUT_INFO','A2-1_An2');
INSERT INTO Student (Student_number,Student_name,Student_firstname,formation,Class_group) VALUES ('D75827545','Halls','Fatima','BUT_INFO','A1-2_An2');
INSERT INTO Student (Student_number,Student_name,Student_firstname,formation,Class_group) VALUES ('D21640336','Christians','Lucian','BUT_INFO','B-1_An2');
INSERT INTO Student (Student_number,Student_name,Student_firstname,formation,Class_group) VALUES ('M05227831','Todds','Kessie','BUT_INFO','B-1_An2');
INSERT INTO Student (Student_number,Student_name,Student_firstname,formation,Class_group) VALUES ('V77414394','Barreras','Xenos','BUT_INFO','A1-1_An2');
INSERT INTO Student (Student_number,Student_name,Student_firstname,formation,Class_group) VALUES ('Y67152665','Sniders','Lacey','BUT_INFO','B-2_An2');
INSERT INTO Student (Student_number,Student_name,Student_firstname,formation,Class_group) VALUES ('E67351791','Hopkinss','Seth','BUT_INFO','A1-1_An2');
INSERT INTO Student (Student_number,Student_name,Student_firstname,formation,Class_group) VALUES ('E27192381','Johnsons','Sylvia','BUT_INFO','B-2_An3');
INSERT INTO Student (Student_number,Student_name,Student_firstname,formation,Class_group) VALUES ('F55522863','Mosess','Daryl','BUT_INFO','A1-2_An2');
INSERT INTO Student (Student_number,Student_name,Student_firstname,formation,Class_group) VALUES ('F48626191','Batess','Donna','BUT_INFO','B-2_An3');
INSERT INTO Student (Student_number,Student_name,Student_firstname,formation,Class_group) VALUES ('T91338854','Colons','Gareth','BUT_INFO','A1-1_An2');
INSERT INTO Student (Student_number,Student_name,Student_firstname,formation,Class_group) VALUES ('L64795769','Richardsons','Kelsey','BUT_INFO','A2-2_An3');
INSERT INTO Student (Student_number,Student_name,Student_firstname,formation,Class_group) VALUES ('M22758723','Pruitts','Medge','BUT_INFO','A1-2_An2');
INSERT INTO Student (Student_number,Student_name,Student_firstname,formation,Class_group) VALUES ('P04312112','Haleys','Ivan','BUT_INFO','B-2_An2');
INSERT INTO Student (Student_number,Student_name,Student_firstname,formation,Class_group) VALUES ('H22564973','Burtons','Garth','BUT_INFO','A2-2_An2');
INSERT INTO Student (Student_number,Student_name,Student_firstname,formation,Class_group) VALUES ('H51561432','Wallaces','Chase','BUT_INFO','A2-1_An2');
INSERT INTO Student (Student_number,Student_name,Student_firstname,formation,Class_group) VALUES ('E73591777','Cotes','Jennifer','BUT_INFO','A1-1_An2');
INSERT INTO Student (Student_number,Student_name,Student_firstname,formation,Class_group) VALUES ('X87316377','Mcclains','Yeo','BUT_INFO','B-1_An2');
INSERT INTO Student (Student_number,Student_name,Student_firstname,formation,Class_group) VALUES ('I47154666','Millss','Ian','BUT_INFO','A2-2_An3');


INSERT INTO Student (Student_number,Student_name,Student_firstname,formation,Class_group) VALUES ('N74214375','Mcclains','Kimberly','BUT_INFO','A1-2_An3');
INSERT INTO Student (Student_number,Student_name,Student_firstname,formation,Class_group) VALUES ('Y75773561','Fullers','Deirdre','BUT_INFO','A1-1_An2');
INSERT INTO Student (Student_number,Student_name,Student_firstname,formation,Class_group) VALUES ('N97352362','Nguyens','Francis','BUT_INFO','A2-2_An2');
INSERT INTO Student (Student_number,Student_name,Student_firstname,formation,Class_group) VALUES ('O25165661','Wallss','Benjamin','BUT_INFO','A2-2_An3');
INSERT INTO Student (Student_number,Student_name,Student_firstname,formation,Class_group) VALUES ('J81275836','Thompsons','Daquan','BUT_INFO','A2-1_An2');
-- etudiant en TC à l'IUT d'aix en provence
INSERT INTO Student (Student_number, Student_name, Student_firstname, formation, Class_group) VALUES ('S12345678', 'Smith', 'John', null, 'A_An1');
INSERT INTO Student (Student_number, Student_name, Student_firstname, formation, Class_group) VALUES ('S22345679', 'Doe', 'Jane', null, 'B_An1');
INSERT INTO Student (Student_number, Student_name, Student_firstname, formation, Class_group) VALUES ('S32345680', 'Brown', 'Charlie', null, 'C_An2');
INSERT INTO Student (Student_number, Student_name, Student_firstname, formation, Class_group) VALUES ('S42345681', 'Taylor', 'Sam', null, 'A_An3');
INSERT INTO Student (Student_number, Student_name, Student_firstname, formation, Class_group) VALUES ('S52345682', 'Johnson', 'Chris', null, 'B_An2');
INSERT INTO Student (Student_number, Student_name, Student_firstname, formation, Class_group) VALUES ('S62345683', 'Lee', 'Alex', null, 'C_An1');
INSERT INTO Student (Student_number, Student_name, Student_firstname, formation, Class_group) VALUES ('S72345684', 'Garcia', 'Maria', null, 'A_An2');
INSERT INTO Student (Student_number, Student_name, Student_firstname, formation, Class_group) VALUES ('S82345685', 'Martinez', 'Carlos', null, 'B_An3');
INSERT INTO Student (Student_number, Student_name, Student_firstname, formation, Class_group) VALUES ('S92345686', 'Rodriguez', 'Sofia', null, 'C_An3');
INSERT INTO Student (Student_number, Student_name, Student_firstname, formation, Class_group) VALUES ('S03345687', 'Davis', 'Emily', null, 'A_An1');
INSERT INTO Student (Student_number, Student_name, Student_firstname, formation, Class_group) VALUES ('S13345688', 'Wilson', 'Olivia', null, 'B_An1');
INSERT INTO Student (Student_number, Student_name, Student_firstname, formation, Class_group) VALUES ('S23345689', 'Lopez', 'Miguel', null, 'C_An2');
INSERT INTO Student (Student_number, Student_name, Student_firstname, formation, Class_group) VALUES ('S33345690', 'Gonzalez', 'Luna', null, 'A_An3');
INSERT INTO Student (Student_number, Student_name, Student_firstname, formation, Class_group) VALUES ('S43345691', 'Nguyen', 'Kevin', null, 'B_An2');
INSERT INTO Student (Student_number, Student_name, Student_firstname, formation, Class_group) VALUES ('S53345692', 'Harris', 'Ava', null, 'C_An1');
INSERT INTO Student (Student_number, Student_name, Student_firstname, formation, Class_group) VALUES ('S63345693', 'Clark', 'Ethan', null, 'A_An2');
INSERT INTO Student (Student_number, Student_name, Student_firstname, formation, Class_group) VALUES ('S73345694', 'Lewis', 'Sophia', null, 'B_An3');
INSERT INTO Student (Student_number, Student_name, Student_firstname, formation, Class_group) VALUES ('S83345695', 'Walker', 'Daniel', null, 'C_An3');
INSERT INTO Student (Student_number, Student_name, Student_firstname, formation, Class_group) VALUES ('S93345696', 'King', 'Mason', null, 'A_An1');
INSERT INTO Student (Student_number, Student_name, Student_firstname, formation, Class_group) VALUES ('S03345697', 'Hall', 'Emma', null, 'B_An2');
-- etudiant en GEA à l'IUT d'aix en provence
INSERT INTO Student (Student_number, Student_name, Student_firstname, formation, Class_group) VALUES ('S14345678', 'Adams', 'Sarah', NULL, 'A_An1');
INSERT INTO Student (Student_number, Student_name, Student_firstname, formation, Class_group) VALUES ('S24345679', 'Baker', 'James', NULL, 'B_An2');
INSERT INTO Student (Student_number, Student_name, Student_firstname, formation, Class_group) VALUES ('S34345680', 'Carter', 'Liam', NULL, 'C_An3');
INSERT INTO Student (Student_number, Student_name, Student_firstname, formation, Class_group) VALUES ('S44345681', 'Davis', 'Noah', NULL, 'A_An2');
INSERT INTO Student (Student_number, Student_name, Student_firstname, formation, Class_group) VALUES ('S54345682', 'Evans', 'Olivia', NULL, 'B_An3');
INSERT INTO Student (Student_number, Student_name, Student_firstname, formation, Class_group) VALUES ('S64345683', 'Fisher', 'Isabella', NULL, 'C_An1');
INSERT INTO Student (Student_number, Student_name, Student_firstname, formation, Class_group) VALUES ('S74345684', 'Garcia', 'Mason', NULL, 'A_An1');
INSERT INTO Student (Student_number, Student_name, Student_firstname, formation, Class_group) VALUES ('S84345685', 'Harris', 'Lucas', NULL, 'B_An2');
INSERT INTO Student (Student_number, Student_name, Student_firstname, formation, Class_group) VALUES ('S94345686', 'Ivy', 'Sophia', NULL, 'C_An3');
INSERT INTO Student (Student_number, Student_name, Student_firstname, formation, Class_group) VALUES ('S05345687', 'Jones', 'Emily', NULL, 'A_An2');
INSERT INTO Student (Student_number, Student_name, Student_firstname, formation, Class_group) VALUES ('S15345688', 'King', 'Alexander', NULL, 'B_An3');
INSERT INTO Student (Student_number, Student_name, Student_firstname, formation, Class_group) VALUES ('S25345689', 'Lopez', 'Ella', NULL, 'C_An1');
INSERT INTO Student (Student_number, Student_name, Student_firstname, formation, Class_group) VALUES ('S35345690', 'Martinez', 'Benjamin', NULL, 'A_An1');
INSERT INTO Student (Student_number, Student_name, Student_firstname, formation, Class_group) VALUES ('S45345691', 'Nelson', 'Zoe', NULL, 'B_An2');
INSERT INTO Student (Student_number, Student_name, Student_firstname, formation, Class_group) VALUES ('S55345692', 'Owens', 'Lily', NULL, 'C_An3');
INSERT INTO Student (Student_number, Student_name, Student_firstname, formation, Class_group) VALUES ('S65345693', 'Parker', 'Elijah', NULL, 'A_An2');
INSERT INTO Student (Student_number, Student_name, Student_firstname, formation, Class_group) VALUES ('S75345694', 'Quinn', 'Ava', NULL, 'B_An3');
INSERT INTO Student (Student_number, Student_name, Student_firstname, formation, Class_group) VALUES ('S85345695', 'Roberts', 'James', NULL, 'C_An1');
INSERT INTO Student (Student_number, Student_name, Student_firstname, formation, Class_group) VALUES ('S95345696', 'Stewart', 'Ethan', NULL, 'A_An1');
INSERT INTO Student (Student_number, Student_name, Student_firstname, formation, Class_group) VALUES ('S06345697', 'Turner', 'Mia', NULL, 'B_An2');

-- Disciplines pour le BUT INFO
INSERT INTO Discipline (Discipline_name) VALUES ('Droit_du_numerique');
INSERT INTO Discipline (Discipline_name) VALUES ('Architecture_des_ordinateurs');
INSERT INTO Discipline (Discipline_name) VALUES ('Gestion_de_projet');
INSERT INTO Discipline (Discipline_name) VALUES ('Communication');
INSERT INTO Discipline (Discipline_name) VALUES ('Gestion_de_BD');
INSERT INTO Discipline (Discipline_name) VALUES ('POO');

-- Disciplines pour le BUT GEA
INSERT INTO Discipline (Discipline_name) VALUES ('Économie');
INSERT INTO Discipline (Discipline_name) VALUES ('Droit');
INSERT INTO Discipline (Discipline_name) VALUES ('Gestion financière');

-- Disciplines pour le BUT TC
INSERT INTO Discipline (Discipline_name) VALUES ('Marketing');
INSERT INTO Discipline (Discipline_name) VALUES ('Communication digitale');
INSERT INTO Discipline (Discipline_name) VALUES ('Vente et négociation');

INSERT INTO User_connect (User_id, User_pass) VALUES ('B22662146', '$2y$10$DNpKk6g77ufGETaD7A4FQua4ZrO9HwHl1J4qAwUgL1XwdpKPAXMRu'); -- mdp :
INSERT INTO User_connect (User_id, User_pass) VALUES ('R14328249', '$2y$10$TvPQhHaaEB1aTJ/FxvEo3O9GaFoIx2vOeZoyE5ahmKnhwSds6ZkiK');
INSERT INTO User_connect (User_id, User_pass) VALUES ('G42185815', '$2y$10$WG0pe2kKpQiEP0xYbfH/GuT.pr9eBvmzwdOgP.QUr34308q./ZM6a');
INSERT INTO User_connect (User_id, User_pass) VALUES ('R32281327', '$2y$10$V2YG65gZXZjCoPpxEpANtuFxtmyDPi9Qxe2D1rthC129SbeSuMG8.');
INSERT INTO User_connect (User_id, User_pass) VALUES ('O75041198', '$2y$10$YVWvia1C.bm1EDV5u/4yI.kb6pkwJM0DcGNmvDQ64Qs71rCRB/7ye');
INSERT INTO User_connect (User_id, User_pass) VALUES ('V73654623', '$2y$10$4GvAufarNFnQYAesWMWaPOcDNn3a1nZNuTy/OL/eoX9y7PpfbjEWO');
INSERT INTO User_connect (User_id, User_pass) VALUES ('Z17235374', '$2y$10$ulYwK4OEEbxpqusPRlXZXum4royyp5FUC/rRIWpdtjBzgNqMSBjuC');
INSERT INTO User_connect (User_id, User_pass) VALUES ('R84623671', '$2y$10$9.y/9zxukaG8yKJ7JOEK4OAXupNCZEnXGYCzXpd1au4coMY4j.iyC');
INSERT INTO User_connect (User_id, User_pass) VALUES ('D78106598', '$2y$10$iv4ZU3DVYRY6QQnIIN7VVusjbCmIZkVk82fqedfcuX23m9qDvvP8a');
INSERT INTO User_connect (User_id, User_pass) VALUES ('S85694088', '$2y$10$SwkcJmkKmvAGsuflQLacee6./yEHLpeHaqhnS3wUpSshKS/F11EMi');
INSERT INTO User_connect (User_id, User_pass) VALUES ('Y68664772', '$2y$10$CqU0sotf9LnlAlhT.Dbd0eb3tlqIRA5aH/ETRtH5vqVTq/K/V4qhW');
INSERT INTO User_connect (User_id, User_pass) VALUES ('Q66676064', '$2y$10$UFlM4t4JwnaRjVfWraUoo.XvaNk2LlIWR/jheXOFnfMa0QvMc/5fy');
INSERT INTO User_connect (User_id, User_pass) VALUES ('B10648624', '$2y$10$tdF/FDwEhV3lZ6DKa.Vesu98kBdIfPLZOyPxifs9k8C5i8ByMsiie');
INSERT INTO User_connect (User_id, User_pass) VALUES ('N26332417', '$2y$10$FdJ5wEicQxaX3d05.lLKS.P1Lujrdq/m79NGiXyGEc.KgMXt7Gnly');
INSERT INTO User_connect (User_id, User_pass) VALUES ('F42358144', '$2y$10$J9Bl5I2xx33d9qrawJDqluIULAW/NUZ.fEccpgSKTvFQwXdPlr4q2');
INSERT INTO User_connect (User_id, User_pass) VALUES ('I57332640', '$2y$10$G47oWpYdhJnN1OW.r1NYS.GdWq/jkrCMK2l5EhcUjs8Vk0HbRlw1O');
INSERT INTO User_connect (User_id, User_pass) VALUES ('B51423637', '$2y$10$WAFksdTEA2UkFuzwc02OqemZ1bardfPqc4yZM6Osiq6hfQdjbkv7G');
INSERT INTO User_connect (User_id, User_pass) VALUES ('C45328794', '$2y$10$d1t0mAYts3uDMQCHQ8InQeH.YZjDALitHrBxcyUqbVfi.ygbfn8dm');
INSERT INTO User_connect (User_id, User_pass) VALUES ('H48344613', '$2y$10$DqXmxG1udGp82W05ShrQc.FVp81iW4IEIKSwosD6fNXM0zGF0uMGu');
INSERT INTO User_connect (User_id, User_pass) VALUES ('R41814241', '$2y$10$UogEny9ZpSiEZ7FoFbLxmeW.FSmtTGFZ0rGNdvJkdf8H8zFidGAuC');
-- insert pour les prof de TC et GEA
INSERT INTO User_connect (User_id, User_pass) VALUES ('A12345678', '$2y$10$Px3DIuPvY5UCPv8201EAOORdbch5tUUVOS8DyYsFeMYAM6WJIHo4G');
INSERT INTO User_connect (User_id, User_pass) VALUES ('B23456789', '$2y$10$XMfBq27RP1Mv3B6jOW9LbO/6Y9ngozLp0U6S9W0wBp3aRI7PjJFs2');
INSERT INTO User_connect (User_id, User_pass) VALUES ('C34567890', '$2y$10$Eb41ZbnjMKqAVnJBDXqFSe0MyDMU0NNX0R0XNoX7IgW57/K.0Bo2G');
INSERT INTO User_connect (User_id, User_pass) VALUES ('D45678901', '$2y$10$LqX4MIWZcOanXbPAXrG5zu9F/E.pvP9kYV1RVZqq2MewhihV8BRZ2');
INSERT INTO User_connect (User_id, User_pass) VALUES ('E56789012', '$2y$10$8A9M7wF2DXR9wbpdM1RLpO5FViBtT5By0lqbxNiyUXKLmkroBhYJi');
INSERT INTO User_connect (User_id, User_pass) VALUES ('F67890123', '$2y$10$4BCm.CNTLl3wMMGkDyaT2OqJ0ur62Lk3b7vvRvF/CkxplJrBLfpxq');
INSERT INTO User_connect (User_id, User_pass) VALUES ('G78901234', '$2y$10$BMAaFUsO/1xgKiM4A7vg6ON9THfb9Hbfg9ZQKni6sC/3vqeBPJdi6');
INSERT INTO User_connect (User_id, User_pass) VALUES ('H89012345', '$2y$10$vbTkNDWh6Kr2SHcGH4yADONH8NKhM6VHZSkGCR3VoBr/LlbWGLwxi');
INSERT INTO User_connect (User_id, User_pass) VALUES ('I90123456', '$2y$10$ISrrFWkUhbb2b3y7/9NdHeEDJNOFqsV58CGuTqLp1Fg3clAho.U/q');
INSERT INTO User_connect (User_id, User_pass) VALUES ('J01234567', '$2y$10$QmPv1aWspu0UIz3F5uzgZeH2AwZfTS1XgKjv.XFXFQz06a/QwmWQW');

INSERT INTO Role (Role_name) VALUES ('Professeur');
INSERT INTO Role (Role_name) VALUES ('Admin_dep');
INSERT INTO Role (Role_name) VALUES ('Super_admin');

INSERT INTO Address_type (Type) VALUES ('Domicile_1');
INSERT INTO Address_type (Type) VALUES ('Domicile_2');
INSERT INTO Address_type (Type) VALUES ('Travail_1');
INSERT INTO Address_type (Type) VALUES ('Travail_2');
INSERT INTO Address_type (Type) VALUES ('Batiment');

INSERT INTO Addr_name (Address) VALUES ('Lunel');
INSERT INTO Addr_name (Address) VALUES ('Boulogne-sur-Mer');
INSERT INTO Addr_name (Address) VALUES ('Brive-la-Gaillarde');
INSERT INTO Addr_name (Address) VALUES ('Orléans');
INSERT INTO Addr_name (Address) VALUES ('Cambrai');
INSERT INTO Addr_name (Address) VALUES ('La Rochelle');
INSERT INTO Addr_name (Address) VALUES ('Vandoeuvre-lès-Nancy');
INSERT INTO Addr_name (Address) VALUES ('Auxerre');
INSERT INTO Addr_name (Address) VALUES ('Le Puy-en-Velay');
INSERT INTO Addr_name (Address) VALUES ('Saint-Lô');
INSERT INTO Addr_name (Address) VALUES ('Beauvais');
INSERT INTO Addr_name (Address) VALUES ('Montpellier');
INSERT INTO Addr_name (Address) VALUES ('Lille');
INSERT INTO Addr_name (Address) VALUES ('Ajaccio');
INSERT INTO Addr_name (Address) VALUES ('Sarreguemines');
INSERT INTO Addr_name (Address) VALUES ('Chalon-sur-Saône');
INSERT INTO Addr_name (Address) VALUES ('Fréjus');
INSERT INTO Addr_name (Address) VALUES ('413 Av. Gaston Berger, 13100 Aix-en-Provence');
INSERT INTO Addr_name (Address) VALUES ('Albi');
INSERT INTO Addr_name (Address) VALUES ('Saint-Étienne-du-Rouvray');
INSERT INTO Addr_name (Address) VALUES ('Agen');
INSERT INTO Addr_name (Address) VALUES ('Perpignan');
INSERT INTO Addr_name (Address) VALUES ('Dreux');
INSERT INTO Addr_name (Address) VALUES ('Évreux');
INSERT INTO Addr_name (Address) VALUES ('Angoulême');
INSERT INTO Addr_name (Address) VALUES ('Paris');
INSERT INTO Addr_name (Address) VALUES ('Aulnay-sous-Bois');
INSERT INTO Addr_name (Address) VALUES ('Périgueux');
INSERT INTO Addr_name (Address) VALUES ('Villenave-d_Ornon');
INSERT INTO Addr_name (Address) VALUES ('Dijon');
INSERT INTO Addr_name (Address) VALUES ('Marcq-en-Baroeul');
INSERT INTO Addr_name (Address) VALUES ('Colmar');
INSERT INTO Addr_name (Address) VALUES ('Aix-en-Provence');
INSERT INTO Addr_name (Address) VALUES ('Marseille');
INSERT INTO Addr_name (Address) VALUES ('Nice');
INSERT INTO Addr_name (Address) VALUES ('Toulon');
INSERT INTO Addr_name (Address) VALUES ('Antibes');
INSERT INTO Addr_name (Address) VALUES ('Avignon');
INSERT INTO Addr_name (Address) VALUES ('Cannes');
INSERT INTO Addr_name (Address) VALUES ('Gap');
INSERT INTO Addr_name (Address) VALUES ('La Ciotat');
INSERT INTO Addr_name (Address) VALUES ('Martigues');
INSERT INTO Addr_name (Address) VALUES ('Digne-les-Bains');
INSERT INTO Addr_name (Address) VALUES ('Saint-Tropez');
INSERT INTO Addr_name (Address) VALUES ('Lyon');
INSERT INTO Addr_name (Address) VALUES ('Toulouse');
INSERT INTO Addr_name (Address) VALUES ('Grenoble');
INSERT INTO Addr_name (Address) VALUES ('Bordeaux');
INSERT INTO Addr_name (Address) VALUES ('Le Havre');
INSERT INTO Addr_name (Address) VALUES ('Vélizy-Villacoublay');
INSERT INTO Addr_name (Address) VALUES ('Clichy');
INSERT INTO Addr_name (Address) VALUES ('Boulogne-Billancourt');
INSERT INTO Addr_name (Address) VALUES ('Strasbourg');
INSERT INTO Addr_name (Address) VALUES ('Nantes');
INSERT INTO Addr_name (Address) VALUES ('Amiens');
INSERT INTO Addr_name (Address) VALUES ('Coudoux');
INSERT INTO Addr_name (Address) VALUES ('Auriol');
INSERT INTO Addr_name (Address) VALUES ('Sausset-les-Pins');
INSERT INTO Addr_name (Address) VALUES ('Carnoux-en-Provence');
INSERT INTO Addr_name (Address) VALUES ('Gréasque');
INSERT INTO Addr_name (Address) VALUES ('Le Rove');
INSERT INTO Addr_name (Address) VALUES ('Calas');
INSERT INTO Addr_name (Address) VALUES ('Saint-Victoret');
INSERT INTO Addr_name (Address) VALUES ('Marseille 13e');
INSERT INTO Addr_name (Address) VALUES ('Marseille 14e');
INSERT INTO Addr_name (Address) VALUES ('Marseille 15e');
INSERT INTO Addr_name (Address) VALUES ('Marseille 16e');
INSERT INTO Addr_name (Address) VALUES ('La Roque-d_Anthéron');
INSERT INTO Addr_name (Address) VALUES ('Lambesc');
INSERT INTO Addr_name (Address) VALUES ('La Destrousse');
INSERT INTO Addr_name (Address) VALUES ('Les Pennes-Mirabeau');
INSERT INTO Addr_name (Address) VALUES ('Roquefort-la-Bédoule');
INSERT INTO Addr_name (Address) VALUES ('Le Tholonet');
INSERT INTO Addr_name (Address) VALUES ('Saint-Estève-Janson');
INSERT INTO Addr_name (Address) VALUES ('Salon-de-Provence');
INSERT INTO Addr_name (Address) VALUES ('Cassis');
INSERT INTO Addr_name (Address) VALUES ('Aubagne');
INSERT INTO Addr_name (Address) VALUES ('Pertuis');
INSERT INTO Addr_name (Address) VALUES ('Puyricard');
INSERT INTO Addr_name (Address) VALUES ('Venelles');
INSERT INTO Addr_name (Address) VALUES ('Rognac');
INSERT INTO Addr_name (Address) VALUES ('Mimet');
INSERT INTO Addr_name (Address) VALUES ('Trets');
INSERT INTO Addr_name (Address) VALUES ('Gardanne');
INSERT INTO Addr_name (Address) VALUES ('Rousset');
INSERT INTO Addr_name (Address) VALUES ('Berre-l_Étang');
INSERT INTO Addr_name (Address) VALUES ('Allauch');
INSERT INTO Addr_name (Address) VALUES ('Le Puy-Sainte-Réparade');
INSERT INTO Addr_name (Address) VALUES ('Saint-Maximin-la-Sainte-Baume');
INSERT INTO Addr_name (Address) VALUES ('L_Estaque');
INSERT INTO Addr_name (Address) VALUES ('Fuveau');
INSERT INTO Addr_name (Address) VALUES ('Bouc-Bel-Air');
INSERT INTO Addr_name (Address) VALUES ('Plan-de-Cuques');
INSERT INTO Addr_name (Address) VALUES ('Gémenos');
INSERT INTO Addr_name (Address) VALUES ('Châteauneuf-le-Rouge');
INSERT INTO Addr_name (Address) VALUES ('Ceyreste');
INSERT INTO Addr_name (Address) VALUES ('Simiane-Collongue');
INSERT INTO Addr_name (Address) VALUES ('Peynier');
INSERT INTO Addr_name (Address) VALUES ('La Barben');
INSERT INTO Addr_name (Address) VALUES ('Cassie');

INSERT INTO Internship (Internship_identifier, Company_name, Address, keywords, Start_date_internship, type, End_date_internship, Internship_subject, Student_number) VALUES ('NV22432349XN','Qwant','Aix-en-Provence','Architecture_des_ordinateurs Gestion_de_projet','2025-05-11','alternance','2026-11-22','Developpement_de_jeux_video','B82656814');
INSERT INTO Internship (Internship_identifier, Company_name, Address, keywords, Start_date_internship, type, End_date_internship, Internship_subject, Student_number) VALUES ('FO60456314IK','Unowhy','Marseille','Architecture_des_ordinateurs Gestion_de_projet','2026-11-21','Internship','2026-12-21','Systemes_informatiques_embarques','Y26472238');
INSERT INTO Internship (Internship_identifier, Company_name, Address, keywords, Start_date_internship, type, End_date_internship, Internship_subject, Student_number) VALUES ('VK73845811RP','Bull','Marseille 13e','Gestion_de_projet Communication','2025-12-16','alternance','2026-06-21','Big_data_et_visualisation','B14955698');
INSERT INTO Internship (Internship_identifier, Company_name, Address, keywords, Start_date_internship, type, End_date_internship, Internship_subject, Student_number) VALUES ('YJ11570210YV','1Kubator','Aubagne','BD POO','2025-07-27','Internship','2026-05-21','Developpement_de_solution_devops','S47843997');
INSERT INTO Internship (Internship_identifier, Company_name, Address, keywords, Start_date_internship, type, End_date_internship, Internship_subject, Student_number) VALUES ('AR75067342MC','Quantmetry','La Ciotat','Gestion_de_projet Communication','2025-02-05','alternance','2026-04-03','Securite_informatique_et_tests_de_penetration','D83154177');
INSERT INTO Internship (Internship_identifier, Company_name, Address, keywords, Start_date_internship, type, End_date_internship, Internship_subject, Student_number) VALUES ('TM05282265IJ','OVH','Rognac','BD POO','2025-09-03','Internship','2026-08-30','Developpement_de_solution_devops','D97153746');
INSERT INTO Internship (Internship_identifier, Company_name, Address, keywords, Start_date_internship, type, End_date_internship, Internship_subject, Student_number) VALUES ('IY68488176WZ','Studio_Azzurro','Le Puy-Sainte-Réparade','Gestion_de_projet Communication','2025-04-17','alternance','2026-09-01','Developpement_de_plugins_PHP','O18468102');
INSERT INTO Internship (Internship_identifier, Company_name, Address, keywords, Start_date_internship, type, End_date_internship, Internship_subject, Student_number) VALUES ('UF46874472WY','Quantmetry','Les Pennes-Mirabeau','Architecture_des_ordinateurs Gestion_de_projet','2023-11-16','Internship','2025-06-30','IoT_et_technologies_connectees','Y51150412');
INSERT INTO Internship (Internship_identifier, Company_name, Address, keywords, Start_date_internship, type, End_date_internship, Internship_subject, Student_number) VALUES ('LP97488083AF','Sopra_Steria','Gardanne','BD POO','2026-01-14','alternance','2026-12-07','Developpement_d_API_restful','U84466434');
INSERT INTO Internship (Internship_identifier, Company_name, Address, keywords, Start_date_internship, type, End_date_internship, Internship_subject, Student_number) VALUES ('SP88568031FA','1Kubator','Cassie','Gestion_de_projet Communication','2025-10-10','Internship','2025-12-28','Cloud_computing_et_services_web','P43090772');
INSERT INTO Internship (Internship_identifier, Company_name, Address, keywords, Start_date_internship, type, End_date_internship, Internship_subject, Student_number) VALUES ('CL14652869OJ','Cdiscount','Pertuis','Architecture_des_ordinateurs Gestion_de_projet','2025-04-14','alternance','2026-01-23','Gestion_de_projet_Agile','H47232920');
INSERT INTO Internship (Internship_identifier, Company_name, Address, keywords, Start_date_internship, type, End_date_internship, Internship_subject, Student_number) VALUES ('TY09846524WT','iDems','Gémenos','BD POO','2025-03-10','Internship','2025-04-29','Intelligence_artificielle','C35008429');
INSERT INTO Internship (Internship_identifier, Company_name, Address, keywords, Start_date_internship, type, End_date_internship, Internship_subject, Student_number) VALUES ('NF72097659LK','Devoteam','Saint-Maximin-la-Sainte-Baume','Communication BD','2025-09-11','alternance','2026-06-21','Application_web_en_temps_reel','U64274615');
INSERT INTO Internship (Internship_identifier, Company_name, Address, keywords, Start_date_internship, type, End_date_internship, Internship_subject, Student_number) VALUES ('GE85072652YN','OVH','Roquefort-la-Bédoule','Gestion_de_projet Communication','2025-05-19','Internship','2026-04-25','Developpement_de_plugins_PHP','C67683232');
INSERT INTO Internship (Internship_identifier, Company_name, Address, keywords, Start_date_internship, type, End_date_internship, Internship_subject, Student_number) VALUES ('CL81638837DL','GFI_Informatique','Aix-en-Provence','Communication BD','2025-08-05','alternance','2026-12-08','Automatisation_de_processus_avec_RPA','K71754824');
INSERT INTO Internship (Internship_identifier, Company_name, Address, keywords, Start_date_internship, type, End_date_internship, Internship_subject, Student_number) VALUES ('JR69230420VL','Bull','Lambesc','Architecture_des_ordinateurs Gestion_de_projet','2026-02-11','Internship','2026-11-26','Developpement_d_API_restful','S60552402');
INSERT INTO Internship (Internship_identifier, Company_name, Address, keywords, Start_date_internship, type, End_date_internship, Internship_subject, Student_number) VALUES ('KL52238788XC','Linagora','Plan-de-Cuques','BD POO','2025-06-27','alternance','2026-08-19','E_commerce_et_marketing_numerique','R15640225');
INSERT INTO Internship (Internship_identifier, Company_name, Address, keywords, Start_date_internship, type, End_date_internship, Internship_subject, Student_number) VALUES ('ON05862680OQ','Talend','Bouc-Bel-Air','BD POO','2025-08-19','Internship','2026-01-01','Securite_informatique_et_tests_de_penetration','Q49315273');
INSERT INTO Internship (Internship_identifier, Company_name, Address, keywords, Start_date_internship, type, End_date_internship, Internship_subject, Student_number) VALUES ('UC62205346ZN','Talend','L_Estaque','BD POO','2025-09-28','alternance','2026-11-19','Cloud_computing_et_services_web','Q44691862');
INSERT INTO Internship (Internship_identifier, Company_name, Address, keywords, Start_date_internship, type, End_date_internship, Internship_subject, Student_number) VALUES ('J98751234PQ', 'Capgemini', 'Saint-Victoret', 'BD POO', '2025-03-15', 'Internship', '2025-12-15', 'Cloud_computing_et_services_web', 'R27448536');
INSERT INTO Internship (Internship_identifier, Company_name, Address, keywords, Start_date_internship, type, End_date_internship, Internship_subject, Student_number) VALUES ('J76284934LM', 'Atos', 'Carnoux-en-Provence', 'Architecture_des_ordinateurs', '2025-05-04', 'alternance', '2026-02-25', 'Securite_informatique_et_tests_de_penetration', 'F03019685');
INSERT INTO Internship (Internship_identifier, Company_name, Address, keywords, Start_date_internship, type, End_date_internship, Internship_subject, Student_number) VALUES ('L68349123GH', 'Orange', 'Les Pennes-Mirabeau', 'Gestion_de_projet Communication', '2025-09-01', 'Internship', '2026-03-01', 'Automatisation_de_processus_avec_RPA', 'B13433362');
INSERT INTO Internship (Internship_identifier, Company_name, Address, keywords, Start_date_internship, type, End_date_internship, Internship_subject, Student_number) VALUES ('Q98743120ER', 'Dassault Systems', 'Lambesc', 'Gestion_de_projet', '2025-10-15', 'alternance', '2026-07-15', 'Developpement_d_API_restful', 'C70741722');
INSERT INTO Internship (Internship_identifier, Company_name, Address, keywords, Start_date_internship, type, End_date_internship, Internship_subject, Student_number) VALUES ('R93412459KL', 'Sopra Steria', 'Gémenos', 'BD POO', '2025-06-10', 'Internship', '2026-01-01', 'Developpement_de_jeux_video', 'Z21392555');
INSERT INTO Internship (Internship_identifier, Company_name, Address, keywords, Start_date_internship, type, End_date_internship, Internship_subject, Student_number) VALUES ('T56789230FW', 'Ubisoft', 'Saint-Estève-Janson', 'Gestion_de_projet', '2025-08-10', 'alternance', '2026-11-22', 'Developpement_de_plugins_PHP', 'D06749632');
INSERT INTO Internship (Internship_identifier, Company_name, Address, keywords, Start_date_internship, type, End_date_internship, Internship_subject, Student_number) VALUES ('P86549720SX', 'Thales', 'Saint-Maximin-la-Sainte-Baume', 'Communication BD', '2025-05-12', 'Internship', '2025-12-01', 'Developpement_d_API_restful', 'S33551879');
INSERT INTO Internship (Internship_identifier, Company_name, Address, keywords, Start_date_internship, type, End_date_internship, Internship_subject, Student_number) VALUES ('S47839207DX', 'Accenture', 'Ceyreste', 'BD POO', '2025-11-22', 'alternance', '2026-07-10', 'Cloud_computing_et_services_web', 'W36736211');
INSERT INTO Internship (Internship_identifier, Company_name, Address, keywords, Start_date_internship, type, End_date_internship, Internship_subject, Student_number) VALUES ('V08756321AB', 'Schneider Electric', 'Aubagne', 'Architecture_des_ordinateurs', '2025-06-15', 'Internship', '2026-02-25', 'Securite_informatique_et_tests_de_penetration', 'R56931231');
INSERT INTO Internship (Internship_identifier, Company_name, Address, keywords, Start_date_internship, type, End_date_internship, Internship_subject, Student_number) VALUES ('M10765430BC', 'Capgemini', 'Venelles', 'BD POO', '2025-09-05', 'alternance', '2026-08-20', 'IoT_et_technologies_connectees', 'C25155368');
INSERT INTO Internship (Internship_identifier, Company_name, Address, keywords, Start_date_internship, type, End_date_internship, Internship_subject, Student_number) VALUES ('K89172340YT', 'Airbus', 'Trets', 'Gestion_de_projet Communication', '2025-08-20', 'Internship', '2026-06-10', 'Developpement_de_jeux_video', 'N65851779');
INSERT INTO Internship (Internship_identifier, Company_name, Address, keywords, Start_date_internship, type, End_date_internship, Internship_subject, Student_number) VALUES ('N54803910QF', 'Altran', 'Aix-en-Provence', 'Gestion_de_projet Communication', '2025-03-23', 'alternance', '2026-11-10', 'Securite_informatique_et_tests_de_penetration', 'B61337468');
INSERT INTO Internship (Internship_identifier, Company_name, Address, keywords, Start_date_internship, type, End_date_internship, Internship_subject, Student_number) VALUES ('J23985641PD', 'Dassault Systems', 'Aubagne', 'BD POO', '2025-07-12', 'Internship', '2026-01-23', 'Cloud_computing_et_services_web', 'X13183474');
INSERT INTO Internship (Internship_identifier, Company_name, Address, keywords, Start_date_internship, type, End_date_internship, Internship_subject, Student_number) VALUES ('L26401903GH', 'Siemens', 'Aix-en-Provence', 'Architecture_des_ordinateurs', '2025-09-18', 'alternance', '2026-03-15', 'Automatisation_de_processus_avec_RPA', 'V88128782');
INSERT INTO Internship (Internship_identifier, Company_name, Address, keywords, Start_date_internship, type, End_date_internship, Internship_subject, Student_number) VALUES ('F83647391EK', 'Oracle', 'Ceyreste', 'BD POO', '2025-05-30', 'Internship', '2026-03-20', 'IoT_et_technologies_connectees', 'T37746743');
INSERT INTO Internship (Internship_identifier, Company_name, Address, keywords, Start_date_internship, type, End_date_internship, Internship_subject, Student_number) VALUES ('H86593472DQ', 'Atos', 'Saint-Victoret', 'Gestion_de_projet Communication', '2025-12-01', 'alternance', '2026-07-05', 'Developpement_d_API_restful', 'B62553911');
INSERT INTO Internship (Internship_identifier, Company_name, Address, keywords, Start_date_internship, type, End_date_internship, Internship_subject, Student_number) VALUES ('T95714638ZR', 'Groupe Renault', 'Les Pennes-Mirabeau', 'Gestion_de_projet', '2025-06-05', 'Internship', '2026-05-01', 'Cloud_computing_et_services_web', 'I69456267');
INSERT INTO Internship (Internship_identifier, Company_name, Address, keywords, Start_date_internship, type, End_date_internship, Internship_subject, Student_number) VALUES ('S09372856DR', 'Eramet', 'Aubagne', 'BD POO', '2025-07-17', 'alternance', '2026-12-01', 'Automatisation_de_processus_avec_RPA', 'B40239339');
INSERT INTO Internship (Internship_identifier, Company_name, Address, keywords, Start_date_internship, type, End_date_internship, Internship_subject, Student_number) VALUES ('K91763894PQ', 'Schneider Electric', 'Auriol', 'Architecture_des_ordinateurs', '2025-08-20', 'Internship', '2026-05-12', 'IoT_et_technologies_connectees', 'H14863692');
INSERT INTO Internship (Internship_identifier, Company_name, Address, keywords, Start_date_internship, type, End_date_internship, Internship_subject, Student_number) VALUES ('F28647309PY', 'Orange', 'Cassis', 'BD POO', '2025-10-25', 'alternance', '2026-06-05', 'Developpement_de_jeux_video', 'T74730069');
INSERT INTO Internship (Internship_identifier, Company_name, Address, keywords, Start_date_internship, type, End_date_internship, Internship_subject, Student_number) VALUES ('J37692847LN', 'Safran', 'Saint-Estève-Janson', 'Gestion_de_projet', '2025-11-08', 'Internship', '2026-05-15', 'Developpement_d_API_restful', 'P15063542');
INSERT INTO Internship (Internship_identifier, Company_name, Address, keywords, Start_date_internship, type, End_date_internship, Internship_subject, Student_number) VALUES ('NV22132345YU','Capgemini','Marseille','BD POO','2025-03-01','alternance','2026-02-28','Développement_d’application_mobile','M33382558');
INSERT INTO Internship (Internship_identifier, Company_name, Address, keywords, Start_date_internship, type, End_date_internship, Internship_subject, Student_number) VALUES ('FO56423897HJ','Altran','Pertuis','Architecture_des_ordinateurs_Gestion_de_projet','2025-04-12','Internship','2026-04-11','Sécurité_informatique','U60431135');
INSERT INTO Internship (Internship_identifier, Company_name, Address, keywords, Start_date_internship, type, End_date_internship, Internship_subject, Student_number) VALUES ('VK22398012QR','Atos','Carnoux-en-Provence','BD_POO','2025-05-05','alternance','2026-03-10','Cloud_computing','M34783033');
INSERT INTO Internship (Internship_identifier, Company_name, Address, keywords, Start_date_internship, type, End_date_internship, Internship_subject, Student_number) VALUES ('YJ11254862BC','Dassault_Systèmes','Aubagne','Gestion_de_projet_Communication','2025-06-10','Internship','2026-04-15','Développement_de_solutions_DevOps','O64863218');
INSERT INTO Internship (Internship_identifier, Company_name, Address, keywords, Start_date_internship, type, End_date_internship, Internship_subject, Student_number) VALUES ('AR74893621LV','Sopra_Steria','Cassis','Architecture_des_ordinateurs_Gestion_de_projet','2025-02-20','alternance','2026-01-15','Sécurité_informatique_et_tests_de_pénétration','V51296562');
INSERT INTO Internship (Internship_identifier, Company_name, Address, keywords, Start_date_internship, type, End_date_internship, Internship_subject, Student_number) VALUES ('TM23458976YH','Orange','Marseille_13e','BD_POO','2025-07-01','Internship','2026-07-10','Intelligence_Artificielle','X62847517');
INSERT INTO Internship (Internship_identifier, Company_name, Address, keywords, Start_date_internship, type, End_date_internship, Internship_subject, Student_number) VALUES ('IY92248165DF','Accenture','La_Ciotat','Gestion_de_projet_Communication','2025-08-12','alternance','2026-10-10','Automatisation_de_processus_avec_RPA','J95442213');
INSERT INTO Internship (Internship_identifier, Company_name, Address, keywords, Start_date_internship, type, End_date_internship, Internship_subject, Student_number) VALUES ('UF23578495WD','Talan','Peynier','BD_POO','2025-09-01','Internship','2026-06-20','Développement_d’application_web','J17865151');
INSERT INTO Internship (Internship_identifier, Company_name, Address, keywords, Start_date_internship, type, End_date_internship, Internship_subject, Student_number) VALUES ('LP73810298GH','Ubisoft','Simiane-Collongue','Architecture_des_ordinateurs_Gestion_de_projet','2025-04-10','alternance','2026-12-15','Développement_de_jeux_vidéo','E35962258');
INSERT INTO Internship (Internship_identifier, Company_name, Address, keywords, Start_date_internship, type, End_date_internship, Internship_subject, Student_number) VALUES ('SP55509832YN','Bouygues_Telecom','Marseille_16e','Gestion_de_projet_Communication','2025-11-03','Internship','2026-06-10','Big_Data_et_Visualisation','D93211952');
INSERT INTO Internship (Internship_identifier, Company_name, Address, keywords, Start_date_internship, type, End_date_internship, Internship_subject, Student_number) VALUES ('CL12548976GD','Airbus','Aubagne','BD_POO','2026-01-01','alternance','2026-10-05','E-commerce_et_marketing_numérique','K11370670');
INSERT INTO Internship (Internship_identifier, Company_name, Address, keywords, Start_date_internship, type, End_date_internship, Internship_subject, Student_number) VALUES ('TY23054890ZS','Thales','Saint-Estève-Janson','Architecture_des_ordinateurs_Gestion_de_projet','2025-10-15','Internship','2026-09-25','Développement_de_solutions_DevOps','L61872638');
INSERT INTO Internship (Internship_identifier, Company_name, Address, keywords, Start_date_internship, type, End_date_internship, Internship_subject, Student_number) VALUES ('NF67389421JI','La_Poste','Ceyreste','BD_POO','2026-03-05','alternance','2026-12-20','Développement_d’API_Restful','L10783379');
INSERT INTO Internship (Internship_identifier, Company_name, Address, keywords, Start_date_internship, type, End_date_internship, Internship_subject, Student_number) VALUES ('GE90758214LK','SFR','Aubagne','Gestion_de_projet_Communication','2025-06-20','Internship','2026-05-30','Automatisation_des_processus_avec_RPA','K14815933');
INSERT INTO Internship (Internship_identifier, Company_name, Address, keywords, Start_date_internship, type, End_date_internship, Internship_subject, Student_number) VALUES ('CL89573210FD','Nokia','Marseille_14e','BD_POO','2025-05-25','alternance','2026-02-10','Cloud_Computing_et_Services_Web','L24677322');
INSERT INTO Internship (Internship_identifier, Company_name, Address, keywords, Start_date_internship, type, End_date_internship, Internship_subject, Student_number) VALUES ('JR67214839RT','L’Oréal','Le_Tholonet','Architecture_des_ordinateurs_Gestion_de_projet','2025-07-10','Internship','2026-05-20','Sécurité_Informatique_et_Tests_de_Pénétration','C90023858');
INSERT INTO Internship (Internship_identifier, Company_name, Address, keywords, Start_date_internship, type, End_date_internship, Internship_subject, Student_number) VALUES ('KL25561894RW','SAP','Saint-Victoret','BD_POO','2025-08-19','alternance','2026-03-21','Développement_d’application_mobile','I40581417');
INSERT INTO Internship (Internship_identifier, Company_name, Address, keywords, Start_date_internship, type, End_date_internship, Internship_subject, Student_number) VALUES ('ON74623598JY','Microsoft','Venelles','Gestion_de_projet_Communication','2025-09-07','Internship','2026-06-25','IoT_et_Technologies_Connectées','U44158779');
INSERT INTO Internship (Internship_identifier, Company_name, Address, keywords, Start_date_internship, type, End_date_internship, Internship_subject, Student_number) VALUES ('UC44619035ZL','Renault','Pertuis','BD_POO','2025-11-20','alternance','2026-09-30','Cloud_Computing_et_Services_Web','U18373223');
INSERT INTO Internship (Internship_identifier, Company_name, Address, keywords, Start_date_internship, type, End_date_internship, Internship_subject, Student_number) VALUES ('BD12345678XP','Capgemini','Gardanne','Gestion_de_projet Communication','2025-06-01','alternance','2026-06-01','Developpement_d_API_restful','Q61178516');
INSERT INTO Internship (Internship_identifier, Company_name, Address, keywords, Start_date_internship, type, End_date_internship, Internship_subject, Student_number) VALUES ('JK98765432LA','Atos','Lambesc','BD POO','2025-07-01','Internship','2026-06-30','Cloud_computing_et_services_web','X76731856');
INSERT INTO Internship (Internship_identifier, Company_name, Address, keywords, Start_date_internship, type, End_date_internship, Internship_subject, Student_number) VALUES ('GH12399876WI','Sopra Steria','La Roque-d_Anthéron','Gestion_de_projet Communication','2025-08-15','Internship','2026-08-14','Big_data_et_visualisation','T97220228');
INSERT INTO Internship (Internship_identifier, Company_name, Address, keywords, Start_date_internship, type, End_date_internship, Internship_subject, Student_number) VALUES ('MN34578612DZ','Orange','Marseille','Architecture_des_ordinateurs Gestion_de_projet','2025-04-20','alternance','2026-04-20','Developpement_de_plugins_PHP','G12824677');
INSERT INTO Internship (Internship_identifier, Company_name, Address, keywords, Start_date_internship, type, End_date_internship, Internship_subject, Student_number) VALUES ('JK12457896WR','Altran','Aubagne','BD POO','2025-11-01','alternance','2026-11-01','Securite_informatique_et_tests_de_penetration','V62147234');
INSERT INTO Internship (Internship_identifier, Company_name, Address, keywords, Start_date_internship, type, End_date_internship, Internship_subject, Student_number) VALUES ('TL56473891AX','Accenture','Venelles','Cloud_computing_et_services_web','2025-06-10','Internship','2026-06-10','Developpement_de_solution_devops','G23254613');
INSERT INTO Internship (Internship_identifier, Company_name, Address, keywords, Start_date_internship, type, End_date_internship, Internship_subject, Student_number) VALUES ('QW24681012EF','IBM','Saint-Estève-Janson','Gestion_de_projet Communication','2025-07-15','alternance','2026-07-15','IoT_et_technologies_connectees','Q78353565');
INSERT INTO Internship (Internship_identifier, Company_name, Address, keywords, Start_date_internship, type, End_date_internship, Internship_subject, Student_number) VALUES ('WQ13579135KD','Dassault Systèmes','Les Pennes-Mirabeau','BD POO','2025-05-01','Internship','2026-04-30','Developpement_d_API_restful','C86247631');
INSERT INTO Internship (Internship_identifier, Company_name, Address, keywords, Start_date_internship, type, End_date_internship, Internship_subject, Student_number) VALUES ('MN78652341QA','Thales','Rousset','Gestion_de_projet Communication','2025-09-10','alternance','2026-09-10','Automatisation_de_processus_avec_RPA','L27312376');
INSERT INTO Internship (Internship_identifier, Company_name, Address, keywords, Start_date_internship, type, End_date_internship, Internship_subject, Student_number) VALUES ('YZ13592468PE','Capgemini','Cassis','BD POO','2025-03-01','Internship','2026-03-01','Cloud_computing_et_services_web','P36571511');
INSERT INTO Internship (Internship_identifier, Company_name, Address, keywords, Start_date_internship, type, End_date_internship, Internship_subject, Student_number) VALUES ('FV56482397DW','Accenture','Plan-de-Cuques','Gestion_de_projet Communication','2025-08-01','alternance','2026-07-31','Developpement_de_plugins_PHP','P08567312');
INSERT INTO Internship (Internship_identifier, Company_name, Address, keywords, Start_date_internship, type, End_date_internship, Internship_subject, Student_number) VALUES ('AB34567123XN','Orange','Marseille 13e','BD POO','2025-04-05','Internship','2026-04-05','Securite_informatique_et_tests_de_penetration','N63215569');
INSERT INTO Internship (Internship_identifier, Company_name, Address, keywords, Start_date_internship, type, End_date_internship, Internship_subject, Student_number) VALUES ('GH98765432PA','Altran','Saint-Victoret','Gestion_de_projet Communication','2025-06-01','alternance','2026-06-01','IoT_et_technologies_connectees','F97127967');
INSERT INTO Internship (Internship_identifier, Company_name, Address, keywords, Start_date_internship, type, End_date_internship, Internship_subject, Student_number) VALUES ('JK98765432OL','Dassault Systèmes','Ceyreste','BD POO','2025-12-01','Internship','2026-12-01','Developpement_de_solution_devops','M87422896');
INSERT INTO Internship (Internship_identifier, Company_name, Address, keywords, Start_date_internship, type, End_date_internship, Internship_subject, Student_number) VALUES ('KL10293847SF','Accenture','Auriol','Gestion_de_projet Communication','2025-06-20','alternance','2026-06-20','Big_data_et_visualisation','E61327716');
INSERT INTO Internship (Internship_identifier, Company_name, Address, keywords, Start_date_internship, type, End_date_internship, Internship_subject, Student_number) VALUES ('PA13457689DQ','Thales','Gréasque','Cloud_computing_et_services_web','2025-09-01','Internship','2026-09-01','Developpement_d_API_restful','E89368273');
INSERT INTO Internship (Internship_identifier, Company_name, Address, keywords, Start_date_internship, type, End_date_internship, Internship_subject, Student_number) VALUES ('LN94728510HK','Orange','Salon-de-Provence','Gestion_de_projet Communication','2025-11-01','Internship','2026-10-31','Automatisation_de_processus_avec_RPA','B66537879');
INSERT INTO Internship (Internship_identifier, Company_name, Address, keywords, Start_date_internship, type, End_date_internship, Internship_subject, Student_number) VALUES ('VJ75364209LT','Capgemini','Saint-Maximin-la-Sainte-Baume','BD POO','2025-08-25','alternance','2026-08-25','Securite_informatique_et_tests_de_penetration','R47255376');
INSERT INTO Internship (Internship_identifier, Company_name, Address, keywords, Start_date_internship, type, End_date_internship, Internship_subject, Student_number) VALUES ('XZ41562378UJ','Dassault Systèmes','Marseille 16e','Gestion_de_projet Communication','2025-10-15','alternance','2026-10-15','Cloud_computing_et_services_web','N38268628');
INSERT INTO Internship (Internship_identifier, Company_name, Address, keywords, Start_date_internship, type, End_date_internship, Internship_subject, Student_number) VALUES ('PQ73645980RC','Altran','Le Rove','BD POO','2025-07-01','Internship','2026-06-30','Developpement_de_solution_devops','O14132859');
INSERT INTO Internship (Internship_identifier, Company_name, Address, keywords, Start_date_internship, type, End_date_internship, Internship_subject, Student_number) VALUES ('JU62873458IW','Atos','La Roque-d_Anthéron','Gestion_de_projet Communication','2025-11-10','Internship','2026-11-10','IoT_et_technologies_connectees','J93570177');
INSERT INTO Internship (Internship_identifier, Company_name, Address, keywords, Start_date_internship, type, End_date_internship, Internship_subject, Student_number) VALUES ('LP23648714SK','Thales','Mimet','BD POO','2025-05-05','alternance','2026-05-05','Developpement_d_API_restful','M04459427');
INSERT INTO Internship (Internship_identifier, Company_name, Address, keywords, Start_date_internship, type, End_date_internship, Internship_subject, Student_number) VALUES ('XJ23589764QL','Capgemini','Lambesc','Gestion_de_projet Communication','2025-12-10','alternance','2026-12-10','Cloud_computing_et_services_web','I07746111');
INSERT INTO Internship (Internship_identifier, Company_name, Address, keywords, Start_date_internship, type, End_date_internship, Internship_subject, Student_number) VALUES ('NV58674109DF','Orange','Le Tholonet','BD POO','2025-10-05','Internship','2026-10-05','Securite_informatique_et_tests_de_penetration','P08664347');
INSERT INTO Internship (Internship_identifier, Company_name, Address, keywords, Start_date_internship, type, End_date_internship, Internship_subject, Student_number) VALUES ('UJ43658790EK','Accenture','Trets','Gestion_de_projet Communication','2025-03-01','alternance','2026-03-01','Big_data_et_visualisation','X77543350');
INSERT INTO Internship (Internship_identifier, Company_name, Address, keywords, Start_date_internship, type, End_date_internship, Internship_subject, Student_number) VALUES ('RW85964231IT','Altran','Fuveau','BD POO','2025-05-01','Internship','2026-05-01','Developpement_de_plugins_PHP','L65151712');
INSERT INTO Internship (Internship_identifier, Company_name, Address, keywords, Start_date_internship, type, End_date_internship, Internship_subject, Student_number) VALUES ('ZX54327865LN','Atos','Marseille 14e','Gestion_de_projet Communication','2025-11-05','alternance','2026-11-05','IoT_et_technologies_connectees','C69818318');
INSERT INTO Internship (Internship_identifier, Company_name, Address, keywords, Start_date_internship, type, End_date_internship, Internship_subject, Student_number) VALUES ('SM24596830QT','Thales','Rognac','BD POO','2025-09-15','Internship','2026-09-15','Developpement_d_API_restful','O53891790');
INSERT INTO Internship (Internship_identifier, Company_name, Address, keywords, Start_date_internship, type, End_date_internship, Internship_subject, Student_number) VALUES ('PK46357980DG','Orange','Pertuis','Gestion_de_projet Communication','2025-12-01','alternance','2026-12-01','Automatisation_de_processus_avec_RPA','P81524434');
INSERT INTO Internship (Internship_identifier, Company_name, Address, keywords, Start_date_internship, type, End_date_internship, Internship_subject, Student_number) VALUES ('NV22345678XN','Alstom','Gémenos','Architecture_des_ordinateurs Gestion_de_projet','2025-06-01','alternance','2026-06-01','Developpement_de_jeux_video','V36767537');
INSERT INTO Internship (Internship_identifier, Company_name, Address, keywords, Start_date_internship, type, End_date_internship, Internship_subject, Student_number) VALUES ('FO60498754IK','Ubisoft','Mimet','Architecture_des_ordinateurs Gestion_de_projet','2026-02-15','Internship','2026-02-15','Systemes_informatiques_embarques','X75842440');
INSERT INTO Internship (Internship_identifier, Company_name, Address, keywords, Start_date_internship, type, End_date_internship, Internship_subject, Student_number) VALUES ('VK73894512RP','Capgemini','Aix-en-Provence','Gestion_de_projet Communication','2025-07-01','alternance','2026-12-01','Big_data_et_visualisation','I89553120');
INSERT INTO Internship (Internship_identifier, Company_name, Address, keywords, Start_date_internship, type, End_date_internship, Internship_subject, Student_number) VALUES ('YJ11576932YV','Sopra Steria','Trets','BD POO','2025-08-01','Internship','2026-08-01','Developpement_de_solution_devops','T73738333');
INSERT INTO Internship (Internship_identifier, Company_name, Address, keywords, Start_date_internship, type, End_date_internship, Internship_subject, Student_number) VALUES ('AR75043245MC','L’Oréal','Lambesc','Gestion_de_projet Communication','2025-03-01','alternance','2026-03-01','Securite_informatique_et_tests_de_penetration','M82251745');
INSERT INTO Internship (Internship_identifier, Company_name, Address, keywords, Start_date_internship, type, End_date_internship, Internship_subject, Student_number) VALUES ('TM05283659IJ','Orange','Puyricard','BD POO','2025-09-15','Internship','2026-09-15','Developpement_de_solution_devops','R18634641');
INSERT INTO Internship (Internship_identifier, Company_name, Address, keywords, Start_date_internship, type, End_date_internship, Internship_subject, Student_number) VALUES ('IY68481357WZ','Dassault Systèmes','La Ciotat','Gestion_de_projet Communication','2025-06-01','alternance','2026-06-01','Developpement_de_plugins_PHP','P48118413');
INSERT INTO Internship (Internship_identifier, Company_name, Address, keywords, Start_date_internship, type, End_date_internship, Internship_subject, Student_number) VALUES ('UF46871265WY','Thales','Bouc-Bel-Air','Architecture_des_ordinateurs Gestion_de_projet','2025-02-01','Internship','2025-12-01','IoT_et_technologies_connectees','U36743854');
INSERT INTO Internship (Internship_identifier, Company_name, Address, keywords, Start_date_internship, type, End_date_internship, Internship_subject, Student_number) VALUES ('LP97439803AF','Bouygues','Lyon','BD POO','2026-01-01','alternance','2026-06-01','Developpement_d_API_restful','D75827545');
INSERT INTO Internship (Internship_identifier, Company_name, Address, keywords, Start_date_internship, type, End_date_internship, Internship_subject, Student_number) VALUES ('SP88565892FA','Fnac Darty','Paris','Gestion_de_projet Communication','2025-10-15','Internship','2025-12-15','Cloud_computing_et_services_web','D21640336');
INSERT INTO Internship (Internship_identifier, Company_name, Address, keywords, Start_date_internship, type, End_date_internship, Internship_subject, Student_number) VALUES ('CL14652485OJ','Criteo','Paris','Architecture_des_ordinateurs Gestion_de_projet','2025-05-01','alternance','2026-05-01','Gestion_de_projet_Agile','M05227831');
INSERT INTO Internship (Internship_identifier, Company_name, Address, keywords, Start_date_internship, type, End_date_internship, Internship_subject, Student_number) VALUES ('TY09848723WT','Atos','Grenoble','BD POO','2025-03-01','Internship','2025-04-01','Intelligence_artificielle','V77414394');
INSERT INTO Internship (Internship_identifier, Company_name, Address, keywords, Start_date_internship, type, End_date_internship, Internship_subject, Student_number) VALUES ('NF72091247LK','Sage','Lille','Communication BD','2025-08-01','alternance','2026-01-01','Application_web_en_temps_reel','Y67152665');
INSERT INTO Internship (Internship_identifier, Company_name, Address, keywords, Start_date_internship, type, End_date_internship, Internship_subject, Student_number) VALUES ('GE85079843YN','Accenture','Paris','Gestion_de_projet Communication','2025-07-15','Internship','2026-01-15','Developpement_de_plugins_PHP','E67351791');
INSERT INTO Internship (Internship_identifier, Company_name, Address, keywords, Start_date_internship, type, End_date_internship, Internship_subject, Student_number) VALUES ('CL81638894DL','Devoteam','Nantes','Communication BD','2025-08-01','alternance','2026-06-01','Automatisation_de_processus_avec_RPA','F55522863');
INSERT INTO Internship (Internship_identifier, Company_name, Address, keywords, Start_date_internship, type, End_date_internship, Internship_subject, Student_number) VALUES ('JR69237692VL','Schneider Electric','Grenoble','Architecture_des_ordinateurs Gestion_de_projet','2026-01-01','Internship','2026-11-01','Developpement_d_API_restful','T91338854');
INSERT INTO Internship (Internship_identifier, Company_name, Address, keywords, Start_date_internship, type, End_date_internship, Internship_subject, Student_number) VALUES ('KL52238988XC','Saint-Gobain','Amiens','BD POO','2025-07-15','alternance','2026-06-15','E_commerce_et_marketing_numerique','L64795769');
INSERT INTO Internship (Internship_identifier, Company_name, Address, keywords, Start_date_internship, type, End_date_internship, Internship_subject, Student_number) VALUES ('ON05862213OQ','Bouygues Telecom','Paris','BD POO','2025-08-01','Internship','2026-01-01','Securite_informatique_et_tests_de_penetration','P04312112');
INSERT INTO Internship (Internship_identifier, Company_name, Address, keywords, Start_date_internship, type, End_date_internship, Internship_subject, Student_number) VALUES ('UC62205476ZN','Vinci','Lyon','BD POO','2025-09-01','alternance','2026-06-01','Cloud_computing_et_services_web','H22564973');
INSERT INTO Internship (Internship_identifier, Company_name, Address, keywords, Start_date_internship, type, End_date_internship, Internship_subject, Student_number) VALUES ('NV88321072UK','Capgemini','Paris','Architecture_des_ordinateurs Gestion_de_projet','2025-06-10','alternance','2026-06-10','Développement_Logiciel','H51561432');
INSERT INTO Internship (Internship_identifier, Company_name, Address, keywords, Start_date_internship, type, End_date_internship, Internship_subject, Student_number) VALUES ('AL34357629SJ','Atos','Lyon','BD POO','2025-03-15','internship','2025-09-01','Systèmes_Embarqués','E73591777');
INSERT INTO Internship (Internship_identifier, Company_name, Address, keywords, Start_date_internship, type, End_date_internship, Internship_subject, Student_number) VALUES ('QU28352697MX','Orange','Lille','Gestion_de_projet Communication','2025-07-01','internship','2026-02-28','Cloud_computing','X87316377');
INSERT INTO Internship (Internship_identifier, Company_name, Address, keywords, Start_date_internship, type, End_date_internship, Internship_subject, Student_number) VALUES ('PI94561724BW','Sopra_Steria','Toulouse','BD POO','2025-05-22','alternance','2026-03-15','DevOps_et_Automatisation','I47154666');

-------------------
INSERT INTO Department (Department_name, Address) VALUES ('IUT_INFO_AIX', '413 Av. Gaston Berger, 13100 Aix-en-Provence');
INSERT INTO Department (Department_name, Address) VALUES ('IUT_GEA_AIX', '413 Av. Gaston Berger, 13100 Aix-en-Provence');
INSERT INTO Department (Department_name, Address) VALUES ('IUT_TC_AIX', '413 Av. Gaston Berger, 13100 Aix-en-Provence');

INSERT INTO Is_taught (Id_teacher, Discipline_name) VALUES ('R14328249', 'Droit_du_numerique');
INSERT INTO Is_taught (Id_teacher, Discipline_name) VALUES ('G42185815', 'Gestion_de_projet');
INSERT INTO Is_taught (Id_teacher, Discipline_name) VALUES ('R32281327', 'Communication');
INSERT INTO Is_taught (Id_teacher, Discipline_name) VALUES ('O75041198', 'Gestion_de_projet');
INSERT INTO Is_taught (Id_teacher, Discipline_name) VALUES ('V73654623', 'Gestion_de_BD');
INSERT INTO Is_taught (Id_teacher, Discipline_name) VALUES ('Z17235374', 'Gestion_de_projet');
INSERT INTO Is_taught (Id_teacher, Discipline_name) VALUES ('R84623671', 'Communication');
INSERT INTO Is_taught (Id_teacher, Discipline_name) VALUES ('D78106598', 'Gestion_de_BD');
INSERT INTO Is_taught (Id_teacher, Discipline_name) VALUES ('S85694088', 'Communication');
INSERT INTO Is_taught (Id_teacher, Discipline_name) VALUES ('Y68664772', 'Droit_du_numerique');
INSERT INTO Is_taught (Id_teacher, Discipline_name) VALUES ('Q66676064', 'Architecture_des_ordinateurs');
INSERT INTO Is_taught (Id_teacher, Discipline_name) VALUES ('B10648624', 'Gestion_de_BD');
INSERT INTO Is_taught (Id_teacher, Discipline_name) VALUES ('N26332417', 'Droit_du_numerique');
INSERT INTO Is_taught (Id_teacher, Discipline_name) VALUES ('F42358144', 'Gestion_de_BD');
INSERT INTO Is_taught (Id_teacher, Discipline_name) VALUES ('I57332640', 'Communication');
INSERT INTO Is_taught (Id_teacher, Discipline_name) VALUES ('B51423637', 'Communication');
INSERT INTO Is_taught (Id_teacher, Discipline_name) VALUES ('C45328794', 'Gestion_de_BD');
INSERT INTO Is_taught (Id_teacher, Discipline_name) VALUES ('H48344613', 'POO');
INSERT INTO Is_taught (Id_teacher, Discipline_name) VALUES ('R41814241', 'POO');
INSERT INTO Is_taught (Id_teacher, Discipline_name) VALUES ('B22662146', 'Architecture_des_ordinateurs');
INSERT INTO Is_taught (Id_teacher, Discipline_name) VALUES ('A23456789', 'Droit_du_numerique');
INSERT INTO Is_taught (Id_teacher, Discipline_name) VALUES ('B34567890', 'Gestion_de_projet');
INSERT INTO Is_taught (Id_teacher, Discipline_name) VALUES ('C45678901', 'Gestion_de_BD');
INSERT INTO Is_taught (Id_teacher, Discipline_name) VALUES ('D56789012', 'Communication');
INSERT INTO Is_taught (Id_teacher, Discipline_name) VALUES ('E67890123', 'POO');
-- Association des enseignants aux disciplines pour GEA
INSERT INTO Is_taught (Id_teacher, Discipline_name) VALUES ('F67890123', 'Communication');
INSERT INTO Is_taught (Id_teacher, Discipline_name) VALUES ('G78901234', 'Gestion_de_BD');
INSERT INTO Is_taught (Id_teacher, Discipline_name) VALUES ('H89012345', 'Droit');
INSERT INTO Is_taught (Id_teacher, Discipline_name) VALUES ('I90123456', 'Économie');
INSERT INTO Is_taught (Id_teacher, Discipline_name) VALUES ('J01234567', 'Gestion financière');
-- Association des enseignants aux disciplines pour TC
INSERT INTO Is_taught (Id_teacher, Discipline_name) VALUES ('A12345678', 'Marketing');
INSERT INTO Is_taught (Id_teacher, Discipline_name) VALUES ('B23456789', 'Communication digitale');
INSERT INTO Is_taught (Id_teacher, Discipline_name) VALUES ('C34567890', 'Vente et négociation');
INSERT INTO Is_taught (Id_teacher, Discipline_name) VALUES ('D45678901', 'Économie');
INSERT INTO Is_taught (Id_teacher, Discipline_name) VALUES ('E56789012', 'Droit');

INSERT INTO Has_role (User_id, Role_name, Department_name) VALUES ('B22662146', 'Professeur', 'IUT_INFO_AIX');
INSERT INTO Has_role (User_id, Role_name, Department_name) VALUES ('R14328249', 'Professeur', 'IUT_INFO_AIX');
INSERT INTO Has_role (User_id, Role_name, Department_name) VALUES ('G42185815', 'Professeur', 'IUT_INFO_AIX');
INSERT INTO Has_role (User_id, Role_name, Department_name) VALUES ('R32281327', 'Professeur', 'IUT_INFO_AIX');
INSERT INTO Has_role (User_id, Role_name, Department_name) VALUES ('O75041198', 'Professeur', 'IUT_INFO_AIX');
INSERT INTO Has_role (User_id, Role_name, Department_name) VALUES ('V73654623', 'Professeur', 'IUT_INFO_AIX');
INSERT INTO Has_role (User_id, Role_name, Department_name) VALUES ('Z17235374', 'Professeur', 'IUT_INFO_AIX');
INSERT INTO Has_role (User_id, Role_name, Department_name) VALUES ('R84623671', 'Professeur', 'IUT_INFO_AIX');
INSERT INTO Has_role (User_id, Role_name, Department_name) VALUES ('D78106598', 'Professeur', 'IUT_INFO_AIX');
INSERT INTO Has_role (User_id, Role_name, Department_name) VALUES ('S85694088', 'Professeur', 'IUT_INFO_AIX');
INSERT INTO Has_role (User_id, Role_name, Department_name) VALUES ('Y68664772', 'Professeur', 'IUT_INFO_AIX');
INSERT INTO Has_role (User_id, Role_name, Department_name) VALUES ('Q66676064', 'Professeur', 'IUT_INFO_AIX');
INSERT INTO Has_role (User_id, Role_name, Department_name) VALUES ('B10648624', 'Professeur', 'IUT_INFO_AIX');
INSERT INTO Has_role (User_id, Role_name, Department_name) VALUES ('N26332417', 'Professeur', 'IUT_INFO_AIX');
INSERT INTO Has_role (User_id, Role_name, Department_name) VALUES ('F42358144', 'Professeur', 'IUT_INFO_AIX');
INSERT INTO Has_role (User_id, Role_name, Department_name) VALUES ('I57332640', 'Professeur', 'IUT_INFO_AIX');
INSERT INTO Has_role (User_id, Role_name, Department_name) VALUES ('B51423637', 'Professeur', 'IUT_INFO_AIX');
INSERT INTO Has_role (User_id, Role_name, Department_name) VALUES ('C45328794', 'Professeur', 'IUT_INFO_AIX');
INSERT INTO Has_role (User_id, Role_name, Department_name) VALUES ('H48344613', 'Professeur', 'IUT_INFO_AIX');
INSERT INTO Has_role (User_id, Role_name, Department_name) VALUES ('R41814241', 'Professeur', 'IUT_INFO_AIX');
INSERT INTO Has_role (User_id, Role_name, Department_name) VALUES ('B22662146', 'Admin_dep','IUT_INFO_AIX');
-- insert pour prof de TC et GEA
INSERT INTO Has_role (User_id, Role_name, Department_name) VALUES ('A12345678', 'Professeur', 'IUT_TC_AIX');
INSERT INTO Has_role (User_id, Role_name, Department_name) VALUES ('B23456789', 'Professeur', 'IUT_TC_AIX');
INSERT INTO Has_role (User_id, Role_name, Department_name) VALUES ('C34567890', 'Professeur', 'IUT_TC_AIX');
INSERT INTO Has_role (User_id, Role_name, Department_name) VALUES ('D45678901', 'Professeur', 'IUT_TC_AIX');
INSERT INTO Has_role (User_id, Role_name, Department_name) VALUES ('E56789012', 'Professeur', 'IUT_TC_AIX');
INSERT INTO Has_role (User_id, Role_name, Department_name) VALUES ('F67890123', 'Professeur', 'IUT_GEA_AIX');
INSERT INTO Has_role (User_id, Role_name, Department_name) VALUES ('G78901234', 'Professeur', 'IUT_GEA_AIX');
INSERT INTO Has_role (User_id, Role_name, Department_name) VALUES ('H89012345', 'Professeur', 'IUT_GEA_AIX');
INSERT INTO Has_role (User_id, Role_name, Department_name) VALUES ('I90123456', 'Professeur', 'IUT_GEA_AIX');
INSERT INTO Has_role (User_id, Role_name, Department_name) VALUES ('J01234567', 'Professeur', 'IUT_GEA_AIX');
INSERT INTO Has_role (User_id, Role_name, Department_name) VALUES ('J01234567', 'Admin_dep', 'IUT_TC_AIX');
INSERT INTO Has_role (User_id, Role_name, Department_name) VALUES ('A12345678', 'Admin_dep','IUT_GEA_AIX');

INSERT INTO Study_at (Student_number, Department_name) VALUES ('B82656814', 'IUT_INFO_AIX');
INSERT INTO Study_at (Student_number, Department_name) VALUES ('Y26472238', 'IUT_INFO_AIX');
INSERT INTO Study_at (Student_number, Department_name) VALUES ('B14955698', 'IUT_INFO_AIX');
INSERT INTO Study_at (Student_number, Department_name) VALUES ('S47843997', 'IUT_INFO_AIX');
INSERT INTO Study_at (Student_number, Department_name) VALUES ('D83154177', 'IUT_INFO_AIX');
INSERT INTO Study_at (Student_number, Department_name) VALUES ('D97153746', 'IUT_INFO_AIX');
INSERT INTO Study_at (Student_number, Department_name) VALUES ('O18468102', 'IUT_INFO_AIX');
INSERT INTO Study_at (Student_number, Department_name) VALUES ('Y51150412', 'IUT_INFO_AIX');
INSERT INTO Study_at (Student_number, Department_name) VALUES ('U84466434', 'IUT_INFO_AIX');
INSERT INTO Study_at (Student_number, Department_name) VALUES ('P43090772', 'IUT_INFO_AIX');
INSERT INTO Study_at (Student_number, Department_name) VALUES ('H47232920', 'IUT_INFO_AIX');
INSERT INTO Study_at (Student_number, Department_name) VALUES ('C35008429', 'IUT_INFO_AIX');
INSERT INTO Study_at (Student_number, Department_name) VALUES ('U64274615', 'IUT_INFO_AIX');
INSERT INTO Study_at (Student_number, Department_name) VALUES ('C67683232', 'IUT_INFO_AIX');
INSERT INTO Study_at (Student_number, Department_name) VALUES ('K71754824', 'IUT_INFO_AIX');
INSERT INTO Study_at (Student_number, Department_name) VALUES ('S60552402', 'IUT_INFO_AIX');
INSERT INTO Study_at (Student_number, Department_name) VALUES ('R15640225', 'IUT_INFO_AIX');
INSERT INTO Study_at (Student_number, Department_name) VALUES ('Q49315273', 'IUT_INFO_AIX');
INSERT INTO Study_at (Student_number, Department_name) VALUES ('Q44691862', 'IUT_INFO_AIX');
INSERT INTO Study_at (Student_number, Department_name) VALUES ('R27448536', 'IUT_INFO_AIX');
INSERT INTO Study_at (Student_number, Department_name) VALUES ('F03019685', 'IUT_INFO_AIX');
INSERT INTO Study_at (Student_number, Department_name) VALUES ('B13433362', 'IUT_INFO_AIX');
INSERT INTO Study_at (Student_number, Department_name) VALUES ('C70741722', 'IUT_INFO_AIX');
INSERT INTO Study_at (Student_number, Department_name) VALUES ('Z21392555', 'IUT_INFO_AIX');
INSERT INTO Study_at (Student_number, Department_name) VALUES ('D06749632', 'IUT_INFO_AIX');
INSERT INTO Study_at (Student_number, Department_name) VALUES ('S33551879', 'IUT_INFO_AIX');
INSERT INTO Study_at (Student_number, Department_name) VALUES ('W36736211', 'IUT_INFO_AIX');
INSERT INTO Study_at (Student_number, Department_name) VALUES ('R56931231', 'IUT_INFO_AIX');
INSERT INTO Study_at (Student_number, Department_name) VALUES ('C25155368', 'IUT_INFO_AIX');
INSERT INTO Study_at (Student_number, Department_name) VALUES ('N65851779', 'IUT_INFO_AIX');
INSERT INTO Study_at (Student_number, Department_name) VALUES ('B61337468', 'IUT_INFO_AIX');
INSERT INTO Study_at (Student_number, Department_name) VALUES ('X13183474', 'IUT_INFO_AIX');
INSERT INTO Study_at (Student_number, Department_name) VALUES ('V88128782', 'IUT_INFO_AIX');
INSERT INTO Study_at (Student_number, Department_name) VALUES ('T37746743', 'IUT_INFO_AIX');
INSERT INTO Study_at (Student_number, Department_name) VALUES ('B62553911', 'IUT_INFO_AIX');
INSERT INTO Study_at (Student_number, Department_name) VALUES ('L86338389', 'IUT_INFO_AIX');
INSERT INTO Study_at (Student_number, Department_name) VALUES ('I69456267', 'IUT_INFO_AIX');
INSERT INTO Study_at (Student_number, Department_name) VALUES ('B40239339', 'IUT_INFO_AIX');
INSERT INTO Study_at (Student_number, Department_name) VALUES ('H14863692', 'IUT_INFO_AIX');
INSERT INTO Study_at (Student_number, Department_name) VALUES ('T74730069', 'IUT_INFO_AIX');
INSERT INTO Study_at (Student_number, Department_name) VALUES ('P15063542', 'IUT_INFO_AIX');
INSERT INTO Study_at (Student_number, Department_name) VALUES ('M33382558', 'IUT_INFO_AIX');
INSERT INTO Study_at (Student_number, Department_name) VALUES ('U60431135', 'IUT_INFO_AIX');
INSERT INTO Study_at (Student_number, Department_name) VALUES ('M34783033', 'IUT_INFO_AIX');
INSERT INTO Study_at (Student_number, Department_name) VALUES ('O64863218', 'IUT_INFO_AIX');
INSERT INTO Study_at (Student_number, Department_name) VALUES ('V51296562', 'IUT_INFO_AIX');
INSERT INTO Study_at (Student_number, Department_name) VALUES ('X62847517', 'IUT_INFO_AIX');
INSERT INTO Study_at (Student_number, Department_name) VALUES ('J95442213', 'IUT_INFO_AIX');
INSERT INTO Study_at (Student_number, Department_name) VALUES ('J17865151', 'IUT_INFO_AIX');
INSERT INTO Study_at (Student_number, Department_name) VALUES ('E35962258', 'IUT_INFO_AIX');
INSERT INTO Study_at (Student_number, Department_name) VALUES ('D93211952', 'IUT_INFO_AIX');
INSERT INTO Study_at (Student_number, Department_name) VALUES ('K11370670', 'IUT_INFO_AIX');
INSERT INTO Study_at (Student_number, Department_name) VALUES ('L61872638', 'IUT_INFO_AIX');
INSERT INTO Study_at (Student_number, Department_name) VALUES ('L10783379', 'IUT_INFO_AIX');
INSERT INTO Study_at (Student_number, Department_name) VALUES ('K14815933', 'IUT_INFO_AIX');
INSERT INTO Study_at (Student_number, Department_name) VALUES ('L24677322', 'IUT_INFO_AIX');
INSERT INTO Study_at (Student_number, Department_name) VALUES ('C90023858', 'IUT_INFO_AIX');
INSERT INTO Study_at (Student_number, Department_name) VALUES ('I40581417', 'IUT_INFO_AIX');
INSERT INTO Study_at (Student_number, Department_name) VALUES ('U44158779', 'IUT_INFO_AIX');
INSERT INTO Study_at (Student_number, Department_name) VALUES ('U18373223', 'IUT_INFO_AIX');
INSERT INTO Study_at (Student_number, Department_name) VALUES ('Q61178516', 'IUT_INFO_AIX');
INSERT INTO Study_at (Student_number, Department_name) VALUES ('X76731856', 'IUT_INFO_AIX');
INSERT INTO Study_at (Student_number, Department_name) VALUES ('T97220228', 'IUT_INFO_AIX');
INSERT INTO Study_at (Student_number, Department_name) VALUES ('G12824677', 'IUT_INFO_AIX');
INSERT INTO Study_at (Student_number, Department_name) VALUES ('V62147234', 'IUT_INFO_AIX');
INSERT INTO Study_at (Student_number, Department_name) VALUES ('G23254613', 'IUT_INFO_AIX');
INSERT INTO Study_at (Student_number, Department_name) VALUES ('Q78353565', 'IUT_INFO_AIX');
INSERT INTO Study_at (Student_number, Department_name) VALUES ('C86247631', 'IUT_INFO_AIX');
INSERT INTO Study_at (Student_number, Department_name) VALUES ('L27312376', 'IUT_INFO_AIX');
INSERT INTO Study_at (Student_number, Department_name) VALUES ('P36571511', 'IUT_INFO_AIX');
INSERT INTO Study_at (Student_number, Department_name) VALUES ('P08567312', 'IUT_INFO_AIX');
INSERT INTO Study_at (Student_number, Department_name) VALUES ('N63215569', 'IUT_INFO_AIX');
INSERT INTO Study_at (Student_number, Department_name) VALUES ('F97127967', 'IUT_INFO_AIX');
INSERT INTO Study_at (Student_number, Department_name) VALUES ('M87422896', 'IUT_INFO_AIX');
INSERT INTO Study_at (Student_number, Department_name) VALUES ('E61327716', 'IUT_INFO_AIX');
INSERT INTO Study_at (Student_number, Department_name) VALUES ('E89368273', 'IUT_INFO_AIX');
INSERT INTO Study_at (Student_number, Department_name) VALUES ('B66537879', 'IUT_INFO_AIX');
INSERT INTO Study_at (Student_number, Department_name) VALUES ('R47255376', 'IUT_INFO_AIX');
INSERT INTO Study_at (Student_number, Department_name) VALUES ('N38268628', 'IUT_INFO_AIX');
INSERT INTO Study_at (Student_number, Department_name) VALUES ('O14132859', 'IUT_INFO_AIX');
INSERT INTO Study_at (Student_number, Department_name) VALUES ('J93570177', 'IUT_INFO_AIX');
INSERT INTO Study_at (Student_number, Department_name) VALUES ('M04459427', 'IUT_INFO_AIX');
INSERT INTO Study_at (Student_number, Department_name) VALUES ('I07746111', 'IUT_INFO_AIX');
INSERT INTO Study_at (Student_number, Department_name) VALUES ('P08664347', 'IUT_INFO_AIX');
INSERT INTO Study_at (Student_number, Department_name) VALUES ('X77543350', 'IUT_INFO_AIX');
INSERT INTO Study_at (Student_number, Department_name) VALUES ('L65151712', 'IUT_INFO_AIX');
INSERT INTO Study_at (Student_number, Department_name) VALUES ('C69818318', 'IUT_INFO_AIX');
INSERT INTO Study_at (Student_number, Department_name) VALUES ('O53891790', 'IUT_INFO_AIX');
INSERT INTO Study_at (Student_number, Department_name) VALUES ('P81524434', 'IUT_INFO_AIX');
INSERT INTO Study_at (Student_number, Department_name) VALUES ('V36767537', 'IUT_INFO_AIX');
INSERT INTO Study_at (Student_number, Department_name) VALUES ('X75842440', 'IUT_INFO_AIX');
INSERT INTO Study_at (Student_number, Department_name) VALUES ('I89553120', 'IUT_INFO_AIX');
INSERT INTO Study_at (Student_number, Department_name) VALUES ('T73738333', 'IUT_INFO_AIX');
INSERT INTO Study_at (Student_number, Department_name) VALUES ('M82251745', 'IUT_INFO_AIX');
INSERT INTO Study_at (Student_number, Department_name) VALUES ('R18634641', 'IUT_INFO_AIX');
INSERT INTO Study_at (Student_number, Department_name) VALUES ('P48118413', 'IUT_INFO_AIX');
INSERT INTO Study_at (Student_number, Department_name) VALUES ('U36743854', 'IUT_INFO_AIX');
INSERT INTO Study_at (Student_number, Department_name) VALUES ('D75827545', 'IUT_INFO_AIX');
INSERT INTO Study_at (Student_number, Department_name) VALUES ('D21640336', 'IUT_INFO_AIX');
INSERT INTO Study_at (Student_number, Department_name) VALUES ('M05227831', 'IUT_INFO_AIX');
INSERT INTO Study_at (Student_number, Department_name) VALUES ('V77414394', 'IUT_INFO_AIX');
INSERT INTO Study_at (Student_number, Department_name) VALUES ('Y67152665', 'IUT_INFO_AIX');
INSERT INTO Study_at (Student_number, Department_name) VALUES ('E67351791', 'IUT_INFO_AIX');
INSERT INTO Study_at (Student_number, Department_name) VALUES ('E27192381', 'IUT_INFO_AIX');
INSERT INTO Study_at (Student_number, Department_name) VALUES ('F55522863', 'IUT_INFO_AIX');
INSERT INTO Study_at (Student_number, Department_name) VALUES ('F48626191', 'IUT_INFO_AIX');
INSERT INTO Study_at (Student_number, Department_name) VALUES ('T91338854', 'IUT_INFO_AIX');
INSERT INTO Study_at (Student_number, Department_name) VALUES ('L64795769', 'IUT_INFO_AIX');
INSERT INTO Study_at (Student_number, Department_name) VALUES ('M22758723', 'IUT_INFO_AIX');
INSERT INTO Study_at (Student_number, Department_name) VALUES ('P04312112', 'IUT_INFO_AIX');
INSERT INTO Study_at (Student_number, Department_name) VALUES ('H22564973', 'IUT_INFO_AIX');
INSERT INTO Study_at (Student_number, Department_name) VALUES ('H51561432', 'IUT_INFO_AIX');
INSERT INTO Study_at (Student_number, Department_name) VALUES ('E73591777', 'IUT_INFO_AIX');
INSERT INTO Study_at (Student_number, Department_name) VALUES ('X87316377', 'IUT_INFO_AIX');
INSERT INTO Study_at (Student_number, Department_name) VALUES ('I47154666', 'IUT_INFO_AIX');
INSERT INTO Study_at (Student_number, Department_name) VALUES ('N74214375', 'IUT_INFO_AIX');
INSERT INTO Study_at (Student_number, Department_name) VALUES ('Y75773561', 'IUT_INFO_AIX');
INSERT INTO Study_at (Student_number, Department_name) VALUES ('N97352362', 'IUT_INFO_AIX');
INSERT INTO Study_at (Student_number, Department_name) VALUES ('O25165661', 'IUT_INFO_AIX');
INSERT INTO Study_at (Student_number, Department_name) VALUES ('J81275836', 'IUT_INFO_AIX');

-- insert de relation pour des etudiant en TC
INSERT INTO Study_at (Student_number, Department_name) VALUES ('S12345678', 'IUT_TC_AIX');
INSERT INTO Study_at (Student_number, Department_name) VALUES ('S22345679', 'IUT_TC_AIX');
INSERT INTO Study_at (Student_number, Department_name) VALUES ('S32345680', 'IUT_TC_AIX');
INSERT INTO Study_at (Student_number, Department_name) VALUES ('S42345681', 'IUT_TC_AIX');
INSERT INTO Study_at (Student_number, Department_name) VALUES ('S52345682', 'IUT_TC_AIX');
INSERT INTO Study_at (Student_number, Department_name) VALUES ('S62345683', 'IUT_TC_AIX');
INSERT INTO Study_at (Student_number, Department_name) VALUES ('S72345684', 'IUT_TC_AIX');
INSERT INTO Study_at (Student_number, Department_name) VALUES ('S82345685', 'IUT_TC_AIX');
INSERT INTO Study_at (Student_number, Department_name) VALUES ('S92345686', 'IUT_TC_AIX');
INSERT INTO Study_at (Student_number, Department_name) VALUES ('S03345687', 'IUT_TC_AIX');
INSERT INTO Study_at (Student_number, Department_name) VALUES ('S13345688', 'IUT_TC_AIX');
INSERT INTO Study_at (Student_number, Department_name) VALUES ('S23345689', 'IUT_TC_AIX');
INSERT INTO Study_at (Student_number, Department_name) VALUES ('S33345690', 'IUT_TC_AIX');
INSERT INTO Study_at (Student_number, Department_name) VALUES ('S43345691', 'IUT_TC_AIX');
INSERT INTO Study_at (Student_number, Department_name) VALUES ('S53345692', 'IUT_TC_AIX');
INSERT INTO Study_at (Student_number, Department_name) VALUES ('S63345693', 'IUT_TC_AIX');
INSERT INTO Study_at (Student_number, Department_name) VALUES ('S73345694', 'IUT_TC_AIX');
INSERT INTO Study_at (Student_number, Department_name) VALUES ('S83345695', 'IUT_TC_AIX');
INSERT INTO Study_at (Student_number, Department_name) VALUES ('S93345696', 'IUT_TC_AIX');
INSERT INTO Study_at (Student_number, Department_name) VALUES ('S03345697', 'IUT_TC_AIX');

-- insert de relation pour des etudiant en GMP
INSERT INTO Study_at (Student_number, Department_name) VALUES ('S14345678', 'IUT_GEA_AIX');
INSERT INTO Study_at (Student_number, Department_name) VALUES ('S24345679', 'IUT_GEA_AIX');
INSERT INTO Study_at (Student_number, Department_name) VALUES ('S34345680', 'IUT_GEA_AIX');
INSERT INTO Study_at (Student_number, Department_name) VALUES ('S44345681', 'IUT_GEA_AIX');
INSERT INTO Study_at (Student_number, Department_name) VALUES ('S54345682', 'IUT_GEA_AIX');
INSERT INTO Study_at (Student_number, Department_name) VALUES ('S64345683', 'IUT_GEA_AIX');
INSERT INTO Study_at (Student_number, Department_name) VALUES ('S74345684', 'IUT_GEA_AIX');
INSERT INTO Study_at (Student_number, Department_name) VALUES ('S84345685', 'IUT_GEA_AIX');
INSERT INTO Study_at (Student_number, Department_name) VALUES ('S94345686', 'IUT_GEA_AIX');
INSERT INTO Study_at (Student_number, Department_name) VALUES ('S05345687', 'IUT_GEA_AIX');
INSERT INTO Study_at (Student_number, Department_name) VALUES ('S15345688', 'IUT_GEA_AIX');
INSERT INTO Study_at (Student_number, Department_name) VALUES ('S25345689', 'IUT_GEA_AIX');
INSERT INTO Study_at (Student_number, Department_name) VALUES ('S35345690', 'IUT_GEA_AIX');
INSERT INTO Study_at (Student_number, Department_name) VALUES ('S45345691', 'IUT_GEA_AIX');
INSERT INTO Study_at (Student_number, Department_name) VALUES ('S55345692', 'IUT_GEA_AIX');
INSERT INTO Study_at (Student_number, Department_name) VALUES ('S65345693', 'IUT_GEA_AIX');
INSERT INTO Study_at (Student_number, Department_name) VALUES ('S75345694', 'IUT_GEA_AIX');
INSERT INTO Study_at (Student_number, Department_name) VALUES ('S85345695', 'IUT_GEA_AIX');
INSERT INTO Study_at (Student_number, Department_name) VALUES ('S95345696', 'IUT_GEA_AIX');
INSERT INTO Study_at (Student_number, Department_name) VALUES ('S06345697', 'IUT_GEA_AIX');

-- Ajout des étudiants dans Study_at avec un département NULL
INSERT INTO Has_address (Id_teacher, Address, Type) VALUES ('B22662146', 'Lunel', 'Domicile_1');
INSERT INTO Has_address (Id_teacher, Address, Type) VALUES ('R14328249', 'Boulogne-sur-Mer', 'Domicile_1');
INSERT INTO Has_address (Id_teacher, Address, Type) VALUES ('G42185815', 'Orléans', 'Domicile_1');
INSERT INTO Has_address (Id_teacher, Address, Type) VALUES ('R32281327', 'Cambrai', 'Domicile_1');
INSERT INTO Has_address (Id_teacher, Address, Type) VALUES ('O75041198', 'La Rochelle', 'Domicile_1');
INSERT INTO Has_address (Id_teacher, Address, Type) VALUES ('V73654623', 'Vandoeuvre-lès-Nancy', 'Domicile_1');
INSERT INTO Has_address (Id_teacher, Address, Type) VALUES ('Z17235374', 'Le Puy-en-Velay', 'Domicile_1');
INSERT INTO Has_address (Id_teacher, Address, Type) VALUES ('R84623671', 'Auxerre', 'Domicile_1');
INSERT INTO Has_address (Id_teacher, Address, Type) VALUES ('D78106598', 'Orléans', 'Domicile_1');
INSERT INTO Has_address (Id_teacher, Address, Type) VALUES ('S85694088', 'Le Puy-en-Velay', 'Domicile_1');
INSERT INTO Has_address (Id_teacher, Address, Type) VALUES ('Y68664772', 'Orléans', 'Domicile_1');
INSERT INTO Has_address (Id_teacher, Address, Type) VALUES ('Q66676064', 'Saint-Lô', 'Domicile_1');
INSERT INTO Has_address (Id_teacher, Address, Type) VALUES ('B10648624', 'Beauvais', 'Domicile_1');
INSERT INTO Has_address (Id_teacher, Address, Type) VALUES ('N26332417', 'Montpellier', 'Domicile_1');
INSERT INTO Has_address (Id_teacher, Address, Type) VALUES ('F42358144', 'Lille', 'Domicile_1');
INSERT INTO Has_address (Id_teacher, Address, Type) VALUES ('I57332640', 'La Rochelle', 'Domicile_1');
INSERT INTO Has_address (Id_teacher, Address, Type) VALUES ('B51423637', 'Ajaccio', 'Domicile_1');
INSERT INTO Has_address (Id_teacher, Address, Type) VALUES ('C45328794', 'Sarreguemines', 'Domicile_1');
INSERT INTO Has_address (Id_teacher, Address, Type) VALUES ('H48344613', 'Chalon-sur-Saône', 'Domicile_1');
INSERT INTO Has_address (Id_teacher, Address, Type) VALUES ('R41814241', 'Fréjus', 'Domicile_1');
INSERT INTO Has_address (Id_teacher, Address, Type) VALUES ('A23456789', 'Lunel', 'Domicile_1');
INSERT INTO Has_address (Id_teacher, Address, Type) VALUES ('B34567890', 'Boulogne-sur-Mer', 'Domicile_1');
INSERT INTO Has_address (Id_teacher, Address, Type) VALUES ('C45678901', 'Orléans', 'Domicile_1');
INSERT INTO Has_address (Id_teacher, Address, Type) VALUES ('D56789012', 'Cambrai', 'Domicile_1');
INSERT INTO Has_address (Id_teacher, Address, Type) VALUES ('E67890123', 'La Rochelle', 'Domicile_1');




