INSERT INTO mysql.user 
( HOST, USER, PASSWORD, select_priv, insert_priv,update_priv, delete_priv,  create_priv, drop_priv, reload_priv, shutdown_priv, process_priv,  file_priv, grant_priv, references_priv, index_priv, alter_priv, show_db_priv, super_priv, create_tmp_table_priv, lock_tables_priv, execute_priv, repl_slave_priv, repl_client_priv, create_view_priv, show_view_priv, create_routine_priv, alter_routine_priv, create_user_priv, event_priv, trigger_priv ) 
VALUES ( '%', 'etl', PASSWORD('etl'), 'Y', 'Y', 'Y', 'Y', 'Y', 'Y', 'Y', 'Y', 'Y', 'Y', 'Y', 'Y', 'Y', 'Y', 'Y', 'Y', 'Y', 'Y', 'Y', 'Y', 'Y', 'Y', 'Y', 'Y', 'Y', 'Y', 'Y', 'Y' );
FLUSH PRIVILEGES;
