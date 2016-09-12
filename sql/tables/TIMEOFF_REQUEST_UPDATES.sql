--  Generate SQL 
--  Version:                   	V7R1M0 100423 
--  Generated on:              	09/02/16 15:14:07 
--  Relational Database:       	SWIFTDB 
--  Standards Option:          	DB2 for i 
CREATE TABLE HRTEST/TIMEOFF_REQUEST_UPDATES FOR SYSTEM NAME TOREQUPD ( 
	TIMEOFF_REQUEST_UPDATE_ID FOR COLUMN TOREQUPID  INTEGER GENERATED ALWAYS AS IDENTITY ( 
	START WITH 1 INCREMENT BY 1 
	NO MINVALUE NO MAXVALUE 
	NO CYCLE NO ORDER 
	CACHE 20 ) 
	, 
	REQUEST_ID FOR COLUMN TOREQID    INTEGER NOT NULL , 
	CREATE_USER FOR COLUMN CRTEMPID   VARCHAR(9) CCSID 37 NOT NULL , 
	CREATE_TIMESTAMP FOR COLUMN CRTTS      TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP , 
	UPDATE_DETAIL FOR COLUMN UPDDETAIL  VARCHAR(32000) CCSID 37 DEFAULT NULL )   
	  
	RCDFMT TOREQUPD   ; 
  
GRANT ALTER , DELETE , INDEX , INSERT , REFERENCES , SELECT , UPDATE   
ON HRTEST/TIMEOFF_REQUEST_UPDATES TO ALDONCMS WITH GRANT OPTION ; 
  
GRANT SELECT   
ON HRTEST/TIMEOFF_REQUEST_UPDATES TO PUBLIC ; 
  
GRANT ALTER , DELETE , INDEX , INSERT , REFERENCES , SELECT , UPDATE   
ON HRTEST/TIMEOFF_REQUEST_UPDATES TO S2KOBJOWNR WITH GRANT OPTION ; 
  