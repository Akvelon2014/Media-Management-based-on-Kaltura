update storage_profile set delivery_http_base_url = '@DELIVERY_HTTP_BASE_URL@' , delivery_rmp_base_url = '@DELIVERY_RTMP_BASE_URL@', delivery_iis_base_url = '@DELIVERY_ISS_BASE_URL@' where id = 0;

update kuser set email = '@BATCH_KUSER_MAIL@' where id = 101;
update kuser set email = '@TEMPLATE_PARTNER_MAIL@' where id = 100;
update kuser set email = '@TEMPLATE_KUSER_MAIL@' where id = 99;
update kuser set email = '@SYSTEM_USER_ADMIN_EMAIL@', puser_id = '@SYSTEM_USER_ADMIN_EMAIL@' where id = 102;



update user_login_data set login_email = '@BATCH_KUSER_MAIL@' where id = 2;
update user_login_data set login_email = '@TEMPLATE_PARTNER_MAIL@', salt = '@TEMPLATE_ADMIN_KUSER_SALT@', sha1_password = '@TEMPLATE_ADMIN_KUSER_SHA1@' where id = 1;
update user_login_data set login_email = '@ADMIN_CONSOLE_KUSER_MAIL@', salt = '@ADMIN_CONSOLE_KUSER_SALT@', sha1_password = '@ADMIN_CONSOLE_KUSER_SHA1@' where id = 3;
update user_login_data set login_email = '@SYSTEM_USER_ADMIN_EMAIL@', salt = '@SYSTEM_USER_ADMIN_SALT@' , sha1_password = '@SYSTEM_USER_ADMIN_SHA1@' where id = 4;


update partner set admin_email = '@TEMPLATE_PARTNER_MAIL@' where id = 99;
update partner set admin_email = '@ADMIN_CONSOLE_ADMIN_MAIL@' where id = -2;
update partner set admin_email = '@BATCH_ADMIN_MAIL@' where id = -1;




