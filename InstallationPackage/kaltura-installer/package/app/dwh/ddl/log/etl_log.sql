CREATE TABLE kalturalog.`etl_log` (                         
           `ID_JOB` INT(11) DEFAULT NULL,                 
           `JOBNAME` VARCHAR(255) DEFAULT NULL,           
           `STATUS` VARCHAR(15) DEFAULT NULL,             
           `LINES_READ` BIGINT(20) DEFAULT NULL,          
           `LINES_WRITTEN` BIGINT(20) DEFAULT NULL,       
           `LINES_UPDATED` BIGINT(20) DEFAULT NULL,       
           `LINES_INPUT` BIGINT(20) DEFAULT NULL,         
           `LINES_OUTPUT` BIGINT(20) DEFAULT NULL,        
           `ERRORS` BIGINT(20) DEFAULT NULL,              
           `STARTDATE` DATETIME DEFAULT NULL,             
           `ENDDATE` DATETIME DEFAULT NULL,               
           `LOGDATE` DATETIME DEFAULT NULL,               
           `DEPDATE` DATETIME DEFAULT NULL,               
           `REPLAYDATE` DATETIME DEFAULT NULL,            
           `LOG_FIELD` MEDIUMTEXT,                        
           KEY `etl_log_name_date` (`JOBNAME`,`LOGDATE`)  
         ) ENGINE=MYISAM DEFAULT CHARACTER SET utf8 COLLATE utf8_general_ci