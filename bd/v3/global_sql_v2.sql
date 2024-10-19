DROP TABLE IF EXISTS Backup;
DROP TABLE IF EXISTS Has_address;
DROP TABLE IF EXISTS Study_at;
DROP TABLE IF EXISTS Has_role;
DROP TABLE IF EXISTS Is_taught;
DROP TABLE IF EXISTS Teaches;
DROP TABLE IF EXISTS Is_requested;
DROP TABLE IF EXISTS Is_responsible;
DROP TABLE IF EXISTS Department;
DROP TABLE IF EXISTS Internship;
DROP TABLE IF EXISTS Addr_name;
DROP TABLE IF EXISTS Coef;
DROP TABLE IF EXISTS Address_type;
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
   Formation VARCHAR(50) NOT NULL,
   Class_group VARCHAR(50) NOT NULL,
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


CREATE TABLE Internship(
   Internship_identifier VARCHAR(50),
   Company_name VARCHAR(50) NOT NULL,
   keywords VARCHAR(200),
   Start_date_internship DATE NOT NULL,
   Type VARCHAR(50),
   End_date_internship DATE NOT NULL,
   Internship_subject VARCHAR(150) NOT NULL,
   Address VARCHAR(100) NOT NULL,
   Student_number VARCHAR(10) NOT NULL,
   PRIMARY KEY(Internship_identifier),
   FOREIGN KEY(Address) REFERENCES Addr_name(Address),
   FOREIGN KEY(Student_number) REFERENCES Student(Student_number)
);

CREATE TABLE Department(
   Department_name VARCHAR(50),
   Address VARCHAR(100) NOT NULL,
   PRIMARY KEY(Department_name),
   FOREIGN KEY(Address) REFERENCES Addr_name(Address)
);

CREATE TABLE Is_responsible(
   Id_teacher VARCHAR(10),
   Student_number VARCHAR(10),
   Distance_minute INT NOT NULL,
   Relevance_score INT NOT NULL,
   Responsible_start_date DATE NOT NULL,
   Responsible_end_date DATE NOT NULL,
   PRIMARY KEY(Id_teacher, Student_number),
   FOREIGN KEY(Id_teacher) REFERENCES Teacher(Id_teacher),
   FOREIGN KEY(Student_number) REFERENCES Student(Student_number)
);

CREATE TABLE Is_requested(
   Id_teacher VARCHAR(10),
   Student_number VARCHAR(10),
   PRIMARY KEY(Id_teacher, Student_number),
   FOREIGN KEY(Id_teacher) REFERENCES Teacher(Id_teacher),
   FOREIGN KEY(Student_number) REFERENCES Student(Student_number)
);

CREATE TABLE Teaches(
   Id_teacher VARCHAR(10),
   Department_name VARCHAR(50),
   PRIMARY KEY(Id_teacher, Department_name),
   FOREIGN KEY(Id_teacher) REFERENCES Teacher(Id_teacher),
   FOREIGN KEY(Department_name) REFERENCES Department(Department_name)
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
   Role_name VARCHAR(50),
   Role_department VARCHAR(50),
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

CREATE TABLE Backup(
   User_id VARCHAR(10),
   Name_criteria VARCHAR(50),
   Coef DECIMAL(6,2),
   Num_backup VARCHAR(50) NOT NULL,
   PRIMARY KEY(User_id, Name_criteria, Coef),
   FOREIGN KEY(User_id) REFERENCES User_connect(User_id),
   FOREIGN KEY(Name_criteria) REFERENCES Distribution_criteria(Name_criteria)
);



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

INSERT INTO Discipline (Discipline_name) VALUES ('Droit_du_numerique');
INSERT INTO Discipline (Discipline_name) VALUES ('Architecture_des_ordinateurs');
INSERT INTO Discipline (Discipline_name) VALUES ('Gestion_de_projet');
INSERT INTO Discipline (Discipline_name) VALUES ('Communication');
INSERT INTO Discipline (Discipline_name) VALUES ('Gestion_de_BD');
INSERT INTO Discipline (Discipline_name) VALUES ('POO');

INSERT INTO User_connect (User_id, User_pass) VALUES ('B22662146', '$2y$10$DNpKk6g77ufGETaD7A4FQua4ZrO9HwHl1J4qAwUgL1XwdpKPAXMRu');
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
INSERT INTO User_connect (User_id, User_pass) VALUES ('R41814241', '$2y$10$Px3DIuPvY5UCPv8201EAOORdbch5tUUVOS8DyYsFeMYAM6WJIHo4G');

INSERT INTO Role (Role_name) VALUES ('Teacher');
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

INSERT INTO Internship (Internship_identifier, Company_name, Address, keywords, Start_date_internship, type, End_date_internship, Internship_subject, Student_number) VALUES ('NV22432349XN','Qwant','Brive-la-Gaillarde','Architecture_des_ordinateurs Gestion_de_projet','2024-05-11','alternance','2024-11-22','Developpement_de_jeux_video','B82656814');
INSERT INTO Internship (Internship_identifier, Company_name, Address, keywords, Start_date_internship, type, End_date_internship, Internship_subject, Student_number) VALUES ('FO60456314IK','Unowhy','Albi','Architecture_des_ordinateurs Gestion_de_projet','2025-11-21','Internship','2025-12-21','Systemes_informatiques_embarques','Y26472238');
INSERT INTO Internship (Internship_identifier, Company_name, Address, keywords, Start_date_internship, type, End_date_internship, Internship_subject, Student_number) VALUES ('VK73845811RP','Bull','Saint-Étienne-du-Rouvray','Gestion_de_projet Communication','2023-12-16','alternance','2024-06-21','Big_data_et_visualisation','B14955698');
INSERT INTO Internship (Internship_identifier, Company_name, Address, keywords, Start_date_internship, type, End_date_internship, Internship_subject, Student_number) VALUES ('YJ11570210YV','1Kubator','Agen','BD POO','2024-07-27','Internship','2025-05-21','Developpement_de_solution_devops','S47843997');
INSERT INTO Internship (Internship_identifier, Company_name, Address, keywords, Start_date_internship, type, End_date_internship, Internship_subject, Student_number) VALUES ('AR75067342MC','Quantmetry','Perpignan','Gestion_de_projet Communication','2024-02-05','alternance','2025-04-03','Securite_informatique_et_tests_de_penetration','D83154177');
INSERT INTO Internship (Internship_identifier, Company_name, Address, keywords, Start_date_internship, type, End_date_internship, Internship_subject, Student_number) VALUES ('TM05282265IJ','OVH','Dreux','BD POO','2024-09-03','Internship','2025-08-30','Developpement_de_solution_devops','D97153746');
INSERT INTO Internship (Internship_identifier, Company_name, Address, keywords, Start_date_internship, type, End_date_internship, Internship_subject, Student_number) VALUES ('IY68488176WZ','Studio_Azzurro','Évreux','Gestion_de_projet Communication','2024-04-17','alternance','2025-09-01','Developpement_de_plugins_PHP','O18468102');
INSERT INTO Internship (Internship_identifier, Company_name, Address, keywords, Start_date_internship, type, End_date_internship, Internship_subject, Student_number) VALUES ('UF46874472WY','Quantmetry','Angoulême','Architecture_des_ordinateurs Gestion_de_projet','2023-11-16','Internship','2024-06-30','IoT_et_technologies_connectees','Y51150412');
INSERT INTO Internship (Internship_identifier, Company_name, Address, keywords, Start_date_internship, type, End_date_internship, Internship_subject, Student_number) VALUES ('LP97488083AF','Sopra_Steria','Paris','BD POO','2025-01-14','alternance','2025-12-07','Developpement_d_API_restful','U84466434');
INSERT INTO Internship (Internship_identifier, Company_name, Address, keywords, Start_date_internship, type, End_date_internship, Internship_subject, Student_number) VALUES ('SP88568031FA','1Kubator','La Rochelle','Gestion_de_projet Communication','2024-10-10','Internship','2024-12-28','Cloud_computing_et_services_web','P43090772');
INSERT INTO Internship (Internship_identifier, Company_name, Address, keywords, Start_date_internship, type, End_date_internship, Internship_subject, Student_number) VALUES ('CL14652869OJ','Cdiscount','Albi','Architecture_des_ordinateurs Gestion_de_projet','2023-04-14','alternance','2025-01-23','Gestion_de_projet_Agile','H47232920'); 
INSERT INTO Internship (Internship_identifier, Company_name, Address, keywords, Start_date_internship, type, End_date_internship, Internship_subject, Student_number) VALUES ('TY09846524WT','iDems','Aulnay-sous-Bois','BD POO','2024-03-10','Internship','2024-04-29','Intelligence_artificielle','C35008429');
INSERT INTO Internship (Internship_identifier, Company_name, Address, keywords, Start_date_internship, type, End_date_internship, Internship_subject, Student_number) VALUES ('NF72097659LK','Devoteam','Périgueux','Communication BD','2023-09-11','alternance','2025-06-21','Application_web_en_temps_reel','U64274615');
INSERT INTO Internship (Internship_identifier, Company_name, Address, keywords, Start_date_internship, type, End_date_internship, Internship_subject, Student_number) VALUES ('GE85072652YN','OVH','Villenave-d_Ornon','Gestion_de_projet Communication','2024-05-19','Internship','2025-04-25','Developpement_de_plugins_PHP','C67683232');
INSERT INTO Internship (Internship_identifier, Company_name, Address, keywords, Start_date_internship, type, End_date_internship, Internship_subject, Student_number) VALUES ('CL81638837DL','GFI_Informatique','Dijon','Communication BD','2024-08-05','alternance','2023-12-08','Automatisation_de_processus_avec_RPA','K71754824');
INSERT INTO Internship (Internship_identifier, Company_name, Address, keywords, Start_date_internship, type, End_date_internship, Internship_subject, Student_number) VALUES ('JR69230420VL','Bull','Vandoeuvre-lès-Nancy','Architecture_des_ordinateurs Gestion_de_projet','2025-02-11','Internship','2023-11-26','Developpement_d_API_restful','S60552402');
INSERT INTO Internship (Internship_identifier, Company_name, Address, keywords, Start_date_internship, type, End_date_internship, Internship_subject, Student_number) VALUES ('KL52238788XC','Linagora','Marcq-en-Baroeul','BD POO','2023-06-27','alternance','2023-08-19','E_commerce_et_marketing_numerique','R15640225');
INSERT INTO Internship (Internship_identifier, Company_name, Address, keywords, Start_date_internship, type, End_date_internship, Internship_subject, Student_number) VALUES ('ON05862680OQ','Talend','Brive-la-Gaillarde','BD POO','2024-08-19','Internship','2025-01-01','Securite_informatique_et_tests_de_penetration','Q49315273');
INSERT INTO Internship (Internship_identifier, Company_name, Address, keywords, Start_date_internship, type, End_date_internship, Internship_subject, Student_number) VALUES ('UC62205346ZN','Talend','Colmar','BD POO','2024-09-28','alternance','2024-11-19','Cloud_computing_et_services_web','Q44691862');

INSERT INTO Department (Department_name, Address) VALUES ('IUT_INFO_AIX', '413 Av. Gaston Berger, 13100 Aix-en-Provence');

INSERT INTO Teaches (Id_teacher, Department_name) VALUES ('R14328249', 'IUT_INFO_AIX');
INSERT INTO Teaches (Id_teacher, Department_name) VALUES ('G42185815', 'IUT_INFO_AIX');
INSERT INTO Teaches (Id_teacher, Department_name) VALUES ('R32281327', 'IUT_INFO_AIX');
INSERT INTO Teaches (Id_teacher, Department_name) VALUES ('O75041198', 'IUT_INFO_AIX');
INSERT INTO Teaches (Id_teacher, Department_name) VALUES ('V73654623', 'IUT_INFO_AIX');
INSERT INTO Teaches (Id_teacher, Department_name) VALUES ('Z17235374', 'IUT_INFO_AIX');
INSERT INTO Teaches (Id_teacher, Department_name) VALUES ('R84623671', 'IUT_INFO_AIX');
INSERT INTO Teaches (Id_teacher, Department_name) VALUES ('D78106598', 'IUT_INFO_AIX');
INSERT INTO Teaches (Id_teacher, Department_name) VALUES ('S85694088', 'IUT_INFO_AIX');
INSERT INTO Teaches (Id_teacher, Department_name) VALUES ('Y68664772', 'IUT_INFO_AIX');
INSERT INTO Teaches (Id_teacher, Department_name) VALUES ('Q66676064', 'IUT_INFO_AIX');
INSERT INTO Teaches (Id_teacher, Department_name) VALUES ('B10648624', 'IUT_INFO_AIX');
INSERT INTO Teaches (Id_teacher, Department_name) VALUES ('N26332417', 'IUT_INFO_AIX');
INSERT INTO Teaches (Id_teacher, Department_name) VALUES ('F42358144', 'IUT_INFO_AIX');
INSERT INTO Teaches (Id_teacher, Department_name) VALUES ('I57332640', 'IUT_INFO_AIX');
INSERT INTO Teaches (Id_teacher, Department_name) VALUES ('B51423637', 'IUT_INFO_AIX');
INSERT INTO Teaches (Id_teacher, Department_name) VALUES ('C45328794', 'IUT_INFO_AIX');
INSERT INTO Teaches (Id_teacher, Department_name) VALUES ('H48344613', 'IUT_INFO_AIX');
INSERT INTO Teaches (Id_teacher, Department_name) VALUES ('R41814241', 'IUT_INFO_AIX');
INSERT INTO Teaches (Id_teacher, Department_name) VALUES ('B22662146', 'IUT_INFO_AIX');

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

INSERT INTO Has_role (User_id, Role_name) VALUES ('B22662146', 'Teacher');
INSERT INTO Has_role (User_id, Role_name) VALUES ('R14328249', 'Teacher');
INSERT INTO Has_role (User_id, Role_name) VALUES ('G42185815', 'Teacher');
INSERT INTO Has_role (User_id, Role_name) VALUES ('R32281327', 'Teacher');
INSERT INTO Has_role (User_id, Role_name) VALUES ('O75041198', 'Teacher');
INSERT INTO Has_role (User_id, Role_name) VALUES ('V73654623', 'Teacher');
INSERT INTO Has_role (User_id, Role_name) VALUES ('Z17235374', 'Teacher');
INSERT INTO Has_role (User_id, Role_name) VALUES ('R84623671', 'Teacher');
INSERT INTO Has_role (User_id, Role_name) VALUES ('D78106598', 'Teacher');
INSERT INTO Has_role (User_id, Role_name) VALUES ('S85694088', 'Teacher');
INSERT INTO Has_role (User_id, Role_name) VALUES ('Y68664772', 'Teacher');
INSERT INTO Has_role (User_id, Role_name) VALUES ('Q66676064', 'Teacher');
INSERT INTO Has_role (User_id, Role_name) VALUES ('B10648624', 'Teacher');
INSERT INTO Has_role (User_id, Role_name) VALUES ('N26332417', 'Teacher');
INSERT INTO Has_role (User_id, Role_name) VALUES ('F42358144', 'Teacher');
INSERT INTO Has_role (User_id, Role_name) VALUES ('I57332640', 'Teacher');
INSERT INTO Has_role (User_id, Role_name) VALUES ('B51423637', 'Teacher');
INSERT INTO Has_role (User_id, Role_name) VALUES ('C45328794', 'Teacher');
INSERT INTO Has_role (User_id, Role_name) VALUES ('H48344613', 'Teacher');
INSERT INTO Has_role (User_id, Role_name) VALUES ('R41814241', 'Teacher');
INSERT INTO Has_role (User_id, Role_name, role_department) VALUES ('B22662146', 'Admin_dep','IUT_INFO_AIX');

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

INSERT INTO Distribution_criteria (Name_criteria) VALUES ('A été responsable');
INSERT INTO Distribution_criteria (Name_criteria) VALUES ('Distance');
INSERT INTO Distribution_criteria (Name_criteria) VALUES ('Cohérence');

INSERT INTO Backup (user_id, name_criteria, coef, num_backup) VALUES ('B22662146','A été responsable', 1,1);
INSERT INTO Backup (user_id, name_criteria, coef, num_backup) VALUES ('B22662146','Distance', 1,1);
INSERT INTO Backup (user_id, name_criteria, coef, num_backup) VALUES ('B22662146','Cohérence', 1,1);