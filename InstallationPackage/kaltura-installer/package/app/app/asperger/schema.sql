CREATE TABLE customers
(
    id INTEGER NOT NULL PRIMARY KEY AUTOINCREMENT,
    name VARCHAR(128) NOT NULL,
    customer_tech_contact VARCHAR(128) NOT NULL,
    pm VARCHAR(128),
    am VARCHAR(128),
    ps_tech_contact VARCHAR(128),
    on_prem_version VARCHAR(128),
    notes VARCHAR(1024)
);

CREATE TABLE hosts
(
    id INTEGER NOT NULL PRIMARY KEY AUTOINCREMENT,
    customer_id INTEGER NOT NULL,
    hostname VARCHAR(255) NOT NULL,
    host_description VARCHAR(255) NOT NULL,
    distro_version_arch VARCHAR(128) NOT NULL,
    ssh_user VARCHAR(128) NOT NULL,
    ssh_passwd VARCHAR(128) NOT NULL,
    notes VARCHAR(1024)
);

CREATE TABLE vpn
(
    id INTEGER NOT NULL PRIMARY KEY AUTOINCREMENT,
    customer_id INTEGER NOT NULL,
    username VARCHAR(128) NOT NULL,
    passwd VARCHAR(128) NOT NULL,
    display_name VARCHAR(128) NOT NULL,
    gateway VARCHAR(255) NOT NULL,
    vpn_type VARCHAR(128) NOT NULL,
    notes VARCHAR(1024)
);

CREATE TABLE ui
(
    id INTEGER NOT NULL PRIMARY KEY AUTOINCREMENT,
    customer_id INTEGER NOT NULL,
    admin_console_url VARCHAR(256),
    admin_console_user VARCHAR(128) NOT NULL,
    admin_console_passwd VARCHAR(128) NOT NULL,
    kmc_url VARCHAR(256),
    kmc_user VARCHAR(128) NOT NULL,
    kmc_passwd VARCHAR(128) NOT NULL,
    kms_admin_url VARCHAR(256),
    kms_admin_user VARCHAR(128) NOT NULL,
    kms_admin_passwd VARCHAR(128) NOT NULL,
    notes VARCHAR(1024)
);

CREATE TABLE log
(
    id INTEGER NOT NULL PRIMARY KEY AUTOINCREMENT,
    action VARCHAR(256),
    create_time INTEGER,
    username VARCHAR(128) NOT NULL
);

