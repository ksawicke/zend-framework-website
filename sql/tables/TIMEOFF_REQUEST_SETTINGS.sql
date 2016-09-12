--  Generate SQL 
--  Version:                   	V7R1M0 100423 
--  Generated on:              	09/02/16 15:13:07 
--  Relational Database:       	SWIFTDB 
--  Standards Option:          	DB2 for i 
CREATE TABLE HRTEST/TIMEOFF_REQUEST_SETTINGS FOR SYSTEM NAME TOSETTING ( 
	IDENTITY_ID FOR COLUMN IDENTITYID INTEGER GENERATED ALWAYS AS IDENTITY ( 
	START WITH 1500 INCREMENT BY 1 
	MINVALUE 1500 NO MAXVALUE 
	NO CYCLE NO ORDER 
	CACHE 20 ) 
	, 
	SYSTEM_KEY FOR COLUMN SYSKEY     VARCHAR(32) CCSID 37 NOT NULL DEFAULT '' , 
	SYSTEM_VALUE FOR COLUMN SYSVAL     VARCHAR(32000) CCSID 37 NOT NULL DEFAULT '' , 
	CREATE_USER FOR COLUMN CTCRTUSR   VARCHAR(18) ALLOCATE(18) CCSID 37 NOT NULL DEFAULT USER , 
	CREATE_TIMESTAMP FOR COLUMN CTCRTTIM   TIMESTAMP DEFAULT CURRENT_TIMESTAMP , 
	UPDATE_USER FOR COLUMN CTUPDUSR   VARCHAR(18) ALLOCATE(18) CCSID 37 NOT NULL DEFAULT USER , 
	UPDATE_TIMESTAMP FOR COLUMN CTUPDTIM   TIMESTAMP GENERATED ALWAYS FOR EACH ROW ON UPDATE AS ROW CHANGE TIMESTAMP NOT NULL , 
	CONSTRAINT HRTEST/Q_HRTEST_TOSETTING_IDENTITYID_00001 PRIMARY KEY( IDENTITY_ID ) )   
	  
	RCDFMT SPDSETT1   ; 
  
GRANT ALTER , DELETE , INDEX , INSERT , REFERENCES , SELECT , UPDATE   
ON HRTEST/TIMEOFF_REQUEST_SETTINGS TO ALDONCMS WITH GRANT OPTION ; 
  
GRANT SELECT   
ON HRTEST/TIMEOFF_REQUEST_SETTINGS TO PUBLIC ; 
  
GRANT ALTER , DELETE , INDEX , INSERT , REFERENCES , SELECT , UPDATE   
ON HRTEST/TIMEOFF_REQUEST_SETTINGS TO S2KOBJOWNR WITH GRANT OPTION ; 
  