USE kalturadw;

DROP FUNCTION IF EXISTS `calc_time_shift`;

DELIMITER $$

CREATE FUNCTION `calc_time_shift`(date_id INT, hour_id INT, time_shift INT)
    RETURNS INT NO SQL
    BEGIN
    	DECLARE day_move INT;
	DECLARE time_shift_24 INT;
	
	IF SIGN(time_shift)>=0 THEN
		SET time_shift_24 = time_shift;
	ELSE
		SET time_shift_24 = 24+time_shift;
	END IF;
	
	IF hour_id < time_shift_24 THEN 
		IF SIGN(time_shift)<0 THEN SET day_move = 0;
		ELSE SET day_move = -1;
		END IF;
	ELSE 
	    	IF SIGN(time_shift)>=0 THEN SET day_move = 0;
		ELSE SET day_move = 1;
		END IF;
	END IF;
	
	RETURN date_id+day_move;
    END$$

DELIMITER ;