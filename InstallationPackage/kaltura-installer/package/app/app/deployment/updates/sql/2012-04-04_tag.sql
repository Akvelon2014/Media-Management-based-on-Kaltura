
# This is a fix for InnoDB in MySQL >= 4.1.x
# It "suspends judgement" for fkey relationships until are tables are set.
SET FOREIGN_KEY_CHECKS = 0;

#-----------------------------------------------------------------------------
#-- tag
#-----------------------------------------------------------------------------


CREATE TABLE `tag`
(
	`id` INTEGER  NOT NULL AUTO_INCREMENT,
	`tag` VARCHAR(32)  NOT NULL,
	`partner_id` INTEGER  NOT NULL,
	`object_type` INTEGER  NOT NULL,
	`instance_count` INTEGER default 1 NOT NULL,
	`created_at` DATETIME,
	PRIMARY KEY (`id`),
	KEY `partner_tag`(`partner_id`),
	KEY `partner_object_tag`(`partner_id`, `object_type`)
)ENGINE=InnoDB;

# This restores the fkey checks, after having unset them earlier
SET FOREIGN_KEY_CHECKS = 1;
