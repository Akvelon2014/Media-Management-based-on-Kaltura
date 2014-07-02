ALTER TABLE kalturadw.dwh_billing 
	CHANGE storage_gb storage_gb DECIMAL (19,4) NOT NULL,
	CHANGE bandwidth_gb bandwidth_gb DECIMAL (19,4) NOT NULL,
	CHANGE livestreaming_gb livestreaming_gb DECIMAL (19, 4) NOT NULL;
