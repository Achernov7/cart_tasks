<?php

use Lib\DB;

DB::execQuery("CREATE TABLE IF NOT EXISTS `groups` (
    id INT NOT NULL AUTO_INCREMENT PRIMARY KEY,
    id_parent INT Default 1,
    name varchar(255) NOT NULL,
    FOREIGN KEY (id_parent) REFERENCES `groups`(id) ON DELETE CASCADE
)");

DB::execQuery("CREATE TABLE IF NOT EXISTS `products` (
    id INT NOT NULL AUTO_INCREMENT PRIMARY KEY,
    id_group INT NOT NULL,
    name varchar(255) NOT NULL,
    FOREIGN KEY (id_group) REFERENCES `groups`(id) ON DELETE CASCADE
)");

// можно сначала проверять
DB::execQuery("INSERT INTO `groups`(name) VALUES('Все товары')"); 
DB::execQuery("INSERT INTO `groups`(id_parent, name) VALUES 
    (1, 'Телевизоры'), (2, 'ЖК Телевизоры'), 
    (2, 'Плазменные телевизоры'), 
    (4, 'Премиальные плазменные телевизоры'), 
    (4, 'Бюджетные плазменные телевизоры'), 
    (1, 'Мультимедиа'), (7, 'Домашние кинотеатры'), 
    (7, 'Магнитофоны')"
);
DB::execQuery("INSERT INTO products (id_group, name) VALUES
    (1, 'Градусник'), (1, 'Велотренажер'),
    (1, 'Розовый закат'), (2, 'Радуга 704-Д'), 
    (3, 'ЖК телевизор TOSHIBA 46'), 
    (3, 'ЖК телевизор SAMSUNG 43'), 
    (3, 'ЖК телевизор LG 41'), 
    (4,'Плазменный телевизор общий LG 42'),
    (5,'Плазменный телевизор premium LG 41'), 
    (5,'Плазменный телевизор premium LG 43'), 
    (5,'Плазменный телевизор premium LG 50'), 
    (6,'Плазменный телевизор бюджетный LG XP3445 41'), 
    (6,'Плазменный телевизор бюджетный LG 41 XP3445'),
    (7, 'магнитола'), 
    (8, 'Домашний кинотеатр премиум XPsad267'), 
    (8, 'Домашний кинотеатр Samsung'), 
    (8, 'Домашний кинотеатр LG')"
);




