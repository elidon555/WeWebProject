CREATE TABLE `product` (
`id` int(10) NOT NULL AUTO_INCREMENT,
`name` varchar(255) NOT NULL,
`price` varchar(255) DEFAULT NULL,
`category` varchar(255) DEFAULT NULL,
`manufacturer` varchar(255) DEFAULT NULL,
`expire` date DEFAULT NULL,
PRIMARY KEY (`id`),
UNIQUE KEY `p_name` (`name`),
UNIQUE KEY `p_id` (`id`),
KEY `product_name` (`name`)
) ENGINE=InnoDB


INSERT INTO weweb.product (id, name, price, category, manufacturer, expire) VALUES (1, 'Axe-Africa', '250', 'Deodorant', 'Elka', '2022-03-03');
INSERT INTO weweb.product (id, name, price, category, manufacturer, expire) VALUES (2, 'Axe-Albania', '300', 'Deodorant', 'Elka', '2022-03-03');
INSERT INTO weweb.product (id, name, price, category, manufacturer, expire) VALUES (3, 'Axe-Azerbaxhan', '300', 'Deodorant', 'Elka', '2022-03-03');
INSERT INTO weweb.product (id, name, price, category, manufacturer, expire) VALUES (4, 'Axe-Andorra', '400', 'Deodorant', 'Elka', '2022-03-03');
INSERT INTO weweb.product (id, name, price, category, manufacturer, expire) VALUES (5, 'Axe-Ankora', '150', 'Deodorant', 'Elka', '2022-03-03');
INSERT INTO weweb.product (id, name, price, category, manufacturer, expire) VALUES (6, 'Axe-Arctic', '500', 'Deodorant', 'Elka', '2022-03-03');
INSERT INTO weweb.product (id, name, price, category, manufacturer, expire) VALUES (7, 'Primo-Pasta', '300', 'Ushqim', 'Elka', '2022-02-20');
INSERT INTO weweb.product (id, name, price, category, manufacturer, expire) VALUES (8, 'AlDente-Pasta', '400', 'Ushqim', 'Elka', '2022-02-20');
INSERT INTO weweb.product (id, name, price, category, manufacturer, expire) VALUES (9, 'Oriz-Gold', '150', 'Ushqim', 'Elka', '2022-02-20');
INSERT INTO weweb.product (id, name, price, category, manufacturer, expire) VALUES (10, 'Oriz-Silver', '500', 'Ushqim', 'Elka', '2022-02-20');
INSERT INTO weweb.product (id, name, price, category, manufacturer, expire) VALUES (11, 'Mouse_Acer', '1000', 'Paisje', 'Acer', '2024-02-20');
INSERT INTO weweb.product (id, name, price, category, manufacturer, expire) VALUES (12, 'Furce-Dhembesh', '500', 'Dentare', 'Colgate', '2023-02-20');
INSERT INTO weweb.product (id, name, price, category, manufacturer, expire) VALUES (13, 'Tapet-Blu', '5500', 'Tapet', 'Anoria', '2025-02-20');
INSERT INTO weweb.product (id, name, price, category, manufacturer, expire) VALUES (14, 'Allure', '11100', 'Parfum', 'Channel', '2023-02-20');