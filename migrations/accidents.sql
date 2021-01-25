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

select * from drivers where accident_id = 95 and guilty = 1;
SELECT fio FROM drivers as d left join accidents as a on a.id = d.accident_id WHERE accident_id=95 AND guilty=1
SELECT * FROM accidents  WHERE id='95';

select number from cars where owner_id in (select id from drivers where accident_id = 95);


insert into gibdd.drivers(fio, drive_license, insurance, accident_id) values ('Зюбан Сергей Дмитриевич','333','234','58');
INSERT INTO gibdd.drivers (fio,drive_license, insurance) VALUES ('Зюбан Сергей Дмитриевич','234','345');
INSERT INTO gibdd.accidents (accident_date_time,accident_address,cause_accident,number_of_ref) VALUES ('2021-01-24T14:58:00', 'Ленина 1', 'Пьянка','2');
