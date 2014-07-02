alter table `kalturadw`.`dwh_dim_partners`
	ADD  `priority_group_id` INTEGER ,
	ADD  `work_group_id` INTEGER ,
	ADD `partner_group_type_id` SMALLINT default 1,
	ADD `partner_parent_id` INTEGER default null,
	ADD KEY `partner_parent_index`(`partner_parent_id`);
