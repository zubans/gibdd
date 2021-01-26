CREATE DATABASE gibdd CHARACTER SET utf8 COLLATE utf8_general_ci;

create table gibdd.accidents
    (
        id int not null auto_increment,
        accident_date_time DATE,
        cause_accident varchar(500),
        number_of_ref varchar(30),
        accident_address varchar(500),

        PRIMARY KEY(id),
        name_of_culprit varchar(100))
        COLLATE='utf8_general_ci'
        ENGINE=InnoDB
        AUTO_INCREMENT=20;

create table gibdd.drivers
    (
        id int not null auto_increment,
        fio varchar(50),
        drive_license varchar(50),
        insurance varchar(50),
        accident_id varchar(30),
        home_address varchar(100),
        work_with_address varchar(100),
        damages varchar(500),
        guilty bool,
        car_id int,

        PRIMARY KEY(id)
    )
    COLLATE='utf8_general_ci'
    ENGINE=InnoDB
    AUTO_INCREMENT=20;

create table gibdd.cars
    (
        id int not null auto_increment,
        mark varchar(50),
        owner_id int,
        number varchar(50),
        color varchar(50),

        PRIMARY KEY(id)
)
    COLLATE='utf8_general_ci'
    ENGINE=InnoDB
    AUTO_INCREMENT=20;


create table gibdd.users
    (
        id int not null auto_increment,
        login varchar(50),
        password varchar(100),
        PRIMARY KEY(id)
)
    COLLATE='utf8_general_ci'
    ENGINE=InnoDB
    AUTO_INCREMENT=20;
