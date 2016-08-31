--  Generate SQL 
--  Version:                   	V7R1M0 100423 
--  Generated on:              	08/31/16 14:17:26 
--  Relational Database:       	SWIFTDB 
--  Standards Option:          	DB2 for i 
CREATE TABLE SAWIK2/TIMEOFF_REQUEST_ENTRIES_ARCHIVE FOR SYSTEM NAME TOREQARC ( 
	ENTRY_ARCHIVE_ID FOR COLUMN ENTARCID   INTEGER GENERATED ALWAYS AS IDENTITY ( 
	START WITH 1 INCREMENT BY 1 
	NO MINVALUE NO MAXVALUE 
	NO CYCLE NO ORDER 
	CACHE 20 ) 
	, 
	REQUEST_ID FOR COLUMN REQUESTID  INTEGER NOT NULL , 
	ENTRY_ID FOR COLUMN ENTRYID    INTEGER NOT NULL , 
	REQUEST_DATA FOR COLUMN REQDATA    VARCHAR(32000) CCSID 37 DEFAULT NULL )   
	  
	RCDFMT TOREQARC   ; 
  
LABEL ON COLUMN SAWIK2/TIMEOFF_REQUEST_ENTRIES_ARCHIVE 
( REQUEST_DATA TEXT IS 'REQUEST_DATA' ) ; 
  
GRANT ALTER , DELETE , INDEX , INSERT , REFERENCES , SELECT , UPDATE   
ON SAWIK2/TIMEOFF_REQUEST_ENTRIES_ARCHIVE TO QPGMR WITH GRANT OPTION ;
