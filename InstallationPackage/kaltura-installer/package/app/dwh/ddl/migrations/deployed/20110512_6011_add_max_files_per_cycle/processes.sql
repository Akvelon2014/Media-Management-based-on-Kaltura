ALTER TABLE kalturadw_ds.processes ADD max_files_per_cycle INT(11);

UPDATE kalturadw_ds.processes
set max_files_per_cycle = case id when 1 then 20 when 2 then 50 when 4 then 50 when 5 then 50 when 6 then 1000 end; 
