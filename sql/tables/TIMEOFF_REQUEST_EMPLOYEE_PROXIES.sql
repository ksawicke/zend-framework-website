--  Generate SQL 
--  Version:                   	V7R1M0 100423 
--  Generated on:              	09/02/16 15:10:38 
--  Relational Database:       	SWIFTDB 
--  Standards Option:          	DB2 for i 
CREATE TABLE HRTEST/TIMEOFF_REQUEST_EMPLOYEE_PROXIES FOR SYSTEM NAME TOEMPPRX ( 
	EMPLOYEE_NUMBER FOR COLUMN EMPNUM     VARCHAR(9) CCSID 37 DEFAULT NULL , 
	PROXY_EMPLOYEE_NUMBER FOR COLUMN PRXEMPNUM  VARCHAR(9) CCSID 37 DEFAULT NULL , 
	STATUS VARCHAR(1) ALLOCATE(1) CCSID 37 DEFAULT '0' )   
	  
	RCDFMT TOEMPPRX   ; 
  
GRANT ALTER , DELETE , INDEX , INSERT , REFERENCES , SELECT , UPDATE   
ON HRTEST/TIMEOFF_REQUEST_EMPLOYEE_PROXIES TO ALDONCMS WITH GRANT OPTION ; 
  
GRANT SELECT   
ON HRTEST/TIMEOFF_REQUEST_EMPLOYEE_PROXIES TO PUBLIC ; 
  
GRANT ALTER , DELETE , INDEX , INSERT , REFERENCES , SELECT , UPDATE   
ON HRTEST/TIMEOFF_REQUEST_EMPLOYEE_PROXIES TO S2KOBJOWNR WITH GRANT OPTION ; 
  
