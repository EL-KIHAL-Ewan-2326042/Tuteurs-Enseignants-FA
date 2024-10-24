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
    Distance_minute INT,
    Relevance_score INT,
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