--  Generate SQL 
--  Version:                   	V7R1M0 100423 
--  Generated on:              	09/02/16 15:15:57 
--  Relational Database:       	SWIFTDB 
--  Standards Option:          	DB2 for i 
SET PATH *LIBL ; 
  
CREATE FUNCTION HRTEST/TIMEOFF_GET_EMPLOYEE_DATA ( 
	IN_EMPLOYER_NUMBER VARCHAR(3) , 
	IN_EMPLOYEE_NUMBER VARCHAR(9) , 
	IN_INCLUDE_UNAPPROVED VARCHAR(1) ) 
	RETURNS TABLE ( 
	EMPLOYER_NUMBER CHAR(3) , 
	EMPLOYEE_NUMBER CHAR(9) , 
	EMPLOYEE_NAME CHAR(75) , 
	EMPLOYEE_COMMON_NAME CHAR(75) , 
	EMPLOYEE_LAST_NAME CHAR(75) , 
	EMPLOYEE_DESCRIPTION CHAR(100) , 
	EMPLOYEE_DESCRIPTION_ALT CHAR(100) , 
	EMAIL_ADDRESS CHAR(75) , 
	LEVEL_1 CHAR(5) , 
	LEVEL_2 CHAR(5) , 
	LEVEL_3 CHAR(5) , 
	LEVEL_4 CHAR(5) , 
	POSITION_CODE CHAR(6) , 
	POSITION_TITLE CHAR(30) , 
	EMPLOYEE_HIRE_DATE CHAR(10) , 
	SALARY_TYPE CHAR(1) , 
	MANAGER_EMPLOYEE_NUMBER CHAR(9) , 
	MANAGER_NAME CHAR(75) , 
	MANAGER_COMMON_NAME CHAR(75) , 
	MANAGER_LAST_NAME CHAR(75) , 
	MANAGER_DESCRIPTION CHAR(100) , 
	MANAGER_DESCRIPTION_ALT CHAR(100) , 
	MANAGER_EMAIL_ADDRESS CHAR(75) , 
	MANAGER_POSITION_CODE CHAR(6) , 
	MANAGER_POSITION_TITLE CHAR(30) , 
	PTO_EARNED DECIMAL(9, 2) , 
	PTO_TAKEN DECIMAL(9, 2) , 
	PTO_UNAPPROVED DECIMAL(9, 2) , 
	PTO_PENDING DECIMAL(9, 2) , 
	PTO_PENDING_TMP DECIMAL(9, 2) , 
	PTO_PENDING_TOTAL DECIMAL(9, 2) , 
	PTO_REMAINING DECIMAL(9, 2) , 
	FLOAT_EARNED DECIMAL(9, 2) , 
	FLOAT_TAKEN DECIMAL(9, 2) , 
	FLOAT_UNAPPROVED DECIMAL(9, 2) , 
	FLOAT_PENDING DECIMAL(9, 2) , 
	FLOAT_PENDING_TMP DECIMAL(9, 2) , 
	FLOAT_PENDING_TOTAL DECIMAL(9, 2) , 
	FLOAT_REMAINING DECIMAL(9, 2) , 
	SICK_EARNED DECIMAL(9, 2) , 
	SICK_TAKEN DECIMAL(9, 2) , 
	SICK_UNAPPROVED DECIMAL(9, 2) , 
	SICK_PENDING DECIMAL(9, 2) , 
	SICK_PENDING_TMP DECIMAL(9, 2) , 
	SICK_PENDING_TOTAL DECIMAL(9, 2) , 
	SICK_REMAINING DECIMAL(9, 2) , 
	GF_EARNED DECIMAL(9, 2) , 
	GF_TAKEN DECIMAL(9, 2) , 
	GF_UNAPPROVED DECIMAL(9, 2) , 
	GF_PENDING DECIMAL(9, 2) , 
	GF_PENDING_TMP DECIMAL(9, 2) , 
	GF_PENDING_TOTAL DECIMAL(9, 2) , 
	GF_REMAINING DECIMAL(9, 2) , 
	UNEXCUSED_UNAPPROVED DECIMAL(9, 2) , 
	UNEXCUSED_PENDING DECIMAL(9, 2) , 
	UNEXCUSED_PENDING_TMP DECIMAL(9, 2) , 
	UNEXCUSED_PENDING_TOTAL DECIMAL(9, 2) , 
	BEREAVEMENT_UNAPPROVED DECIMAL(9, 2) , 
	BEREAVEMENT_PENDING DECIMAL(9, 2) , 
	BEREAVEMENT_PENDING_TMP DECIMAL(9, 2) , 
	BEREAVEMENT_PENDING_TOTAL DECIMAL(9, 2) , 
	CIVIC_DUTY_UNAPPROVED DECIMAL(9, 2) , 
	CIVIC_DUTY_PENDING DECIMAL(9, 2) , 
	CIVIC_DUTY_PENDING_TMP DECIMAL(9, 2) , 
	CIVIC_DUTY_PENDING_TOTAL DECIMAL(9, 2) , 
	UNPAID_UNAPPROVED DECIMAL(9, 2) , 
	UNPAID_PENDING DECIMAL(9, 2) , 
	UNPAID_PENDING_TMP DECIMAL(9, 2) , 
	UNPAID_PENDING_TOTAL DECIMAL(9, 2) )   
	LANGUAGE SQL 
	SPECIFIC HRTEST/TOGETEMPD 
	NOT DETERMINISTIC 
	MODIFIES SQL DATA 
	CALLED ON NULL INPUT 
	NO EXTERNAL ACTION 
	ALLOW PARALLEL 
	CARDINALITY 1 
	CONCURRENT ACCESS RESOLUTION USE CURRENTLY COMMITTED 
	SET OPTION  ALWBLK = *ALLREAD , 
	ALWCPYDTA = *OPTIMIZE , 
	COMMIT = *NONE , 
	DECRESULT = (31, 31, 00) , 
	DFTRDBCOL = *NONE , 
	DLYPRP = *NO , 
	DYNDFTCOL = *NO , 
	DYNUSRPRF = *USER , 
	SRTSEQ = *HEX   
	BEGIN RETURN WITH A ( EMPLOYER_NUMBER , EMPLOYEE_NUMBER , EMPLOYEE_NAME , EMPLOYEE_COMMON_NAME , EMPLOYEE_LAST_NAME , EMPLOYEE_EMAIL_ADDRESS , LEVEL_1 , LEVEL_2 , LEVEL_3 , LEVEL_4 , POSITION_CODE , POSITION_TITLE , EMPLOYEE_HIRE_DATE , SALARY_TYPE , MANAGER_EMPLOYEE_NUMBER , MANAGER_NAME , MANAGER_COMMON_NAME , MANAGER_LAST_NAME , MANAGER_EMAIL_ADDRESS , MANAGER_POSITION_CODE , MANAGER_POSITION_TITLE , PTO_EARNED , PTO_TAKEN , PTO_UNAPPROVED , PTO_PENDING , PTO_PENDING_TMP , FLOAT_EARNED , FLOAT_TAKEN , FLOAT_UNAPPROVED , FLOAT_PENDING , FLOAT_PENDING_TMP , SICK_EARNED , SICK_TAKEN , SICK_UNAPPROVED , SICK_PENDING , SICK_PENDING_TMP , GF_EARNED , GF_TAKEN , GF_UNAPPROVED , GF_PENDING , GF_PENDING_TMP , UNEXCUSED_UNAPPROVED , UNEXCUSED_PENDING , UNEXCUSED_PENDING_TMP , BEREAVEMENT_UNAPPROVED , BEREAVEMENT_PENDING , BEREAVEMENT_PENDING_TMP , CIVIC_DUTY_UNAPPROVED , CIVIC_DUTY_PENDING , CIVIC_DUTY_PENDING_TMP , UNPAID_UNAPPROVED , UNPAID_PENDING , UNPAID_PENDING_TMP ) AS ( SELECT EMPLOYEE . PRER , REFACTOR_EMPLOYEE_ID ( EMPLOYEE . PREN ) , EMPLOYEE . PRCKNM , EMPLOYEE . PRCOMN , EMPLOYEE . PRLNM , EMPLOYEE . PREML1 , EMPLOYEE . PRL01 , EMPLOYEE . PRL02 , EMPLOYEE . PRL03 , EMPLOYEE . PRL04 , EMPLOYEE . PRPOS , EMPLOYEE . PRTITL , EMPLOYEE . PRDOHE , EMPLOYEE . PRPAY , REFACTOR_EMPLOYEE_ID ( MANAGER_ADDONS . PREN ) , MANAGER_ADDONS . PRCKNM , MANAGER_ADDONS . PRCOMN , MANAGER_ADDONS . PRLNM , MANAGER_ADDONS . PREML1 , MANAGER_ADDONS . PRPOS , MANAGER_ADDONS . PRTITL , EMPLOYEE . PRVAC , EMPLOYEE . PRVAT , ( SELECT * FROM TABLE ( TIMEOFF_GET_UNAPPROVED_HOURS ( IN_EMPLOYEE_NUMBER , 'P' ) ) AS DATA ) , CASE WHEN EMPLOYEE . PRPAY = 'S' THEN ( SELECT PENDING FROM TABLE ( TIMEOFF_GET_SALARY_PENDING ( IN_EMPLOYER_NUMBER , IN_EMPLOYEE_NUMBER , 'P' ) ) AS DATA ) ELSE ( SELECT PENDING FROM TABLE ( TIMEOFF_GET_HOURLY_PENDING ( IN_EMPLOYER_NUMBER , IN_EMPLOYEE_NUMBER , 'P' ) ) AS DATA ) END , CASE WHEN EMPLOYEE . PRPAY = 'S' THEN ( 
SELECT PENDING_TMP FROM TABLE ( TIMEOFF_GET_SALARY_PENDING_TMP ( IN_EMPLOYER_NUMBER , IN_EMPLOYEE_NUMBER , 'P' ) ) AS DATA ) ELSE ( SELECT PENDING_TMP FROM TABLE ( TIMEOFF_GET_HOURLY_PENDING_TMP ( IN_EMPLOYER_NUMBER , IN_EMPLOYEE_NUMBER , 'P' ) ) AS DATA ) END , EMPLOYEE . PRSHA , EMPLOYEE . PRSHT , ( SELECT * FROM TABLE ( TIMEOFF_GET_UNAPPROVED_HOURS ( IN_EMPLOYEE_NUMBER , 'K' ) ) AS DATA ) , CASE WHEN EMPLOYEE . PRPAY = 'S' THEN ( SELECT PENDING FROM TABLE ( TIMEOFF_GET_SALARY_PENDING ( IN_EMPLOYER_NUMBER , IN_EMPLOYEE_NUMBER , 'K' ) ) AS DATA ) ELSE ( SELECT PENDING FROM TABLE ( TIMEOFF_GET_HOURLY_PENDING ( IN_EMPLOYER_NUMBER , IN_EMPLOYEE_NUMBER , 'K' ) ) AS DATA ) END , CASE WHEN EMPLOYEE . PRPAY = 'S' THEN ( SELECT PENDING_TMP FROM TABLE ( TIMEOFF_GET_SALARY_PENDING_TMP ( IN_EMPLOYER_NUMBER , IN_EMPLOYEE_NUMBER , 'K' ) ) AS DATA ) ELSE ( SELECT PENDING_TMP FROM 
TABLE ( TIMEOFF_GET_HOURLY_PENDING_TMP ( IN_EMPLOYER_NUMBER , IN_EMPLOYEE_NUMBER , 'K' ) ) AS DATA ) END , EMPLOYEE . PRSDA , EMPLOYEE . PRSDT , ( SELECT * FROM TABLE ( TIMEOFF_GET_UNAPPROVED_HOURS ( IN_EMPLOYEE_NUMBER , 'S' ) ) AS DATA ) , CASE WHEN EMPLOYEE . PRPAY = 'S' THEN ( SELECT PENDING FROM TABLE ( TIMEOFF_GET_SALARY_PENDING ( IN_EMPLOYER_NUMBER , IN_EMPLOYEE_NUMBER , 'S' ) ) AS DATA ) ELSE ( SELECT PENDING FROM TABLE ( TIMEOFF_GET_HOURLY_PENDING ( IN_EMPLOYER_NUMBER , IN_EMPLOYEE_NUMBER , 'S' ) ) AS DATA ) END , CASE WHEN EMPLOYEE . PRPAY = 'S' THEN ( SELECT PENDING_TMP FROM TABLE ( TIMEOFF_GET_SALARY_PENDING_TMP ( IN_EMPLOYER_NUMBER , IN_EMPLOYEE_NUMBER , 'S' ) ) AS DATA ) ELSE ( SELECT PENDING_TMP FROM TABLE ( TIMEOFF_GET_HOURLY_PENDING_TMP ( IN_EMPLOYER_NUMBER , IN_EMPLOYEE_NUMBER , 'S' ) ) AS DATA ) END , EMPLOYEE . PRAC5E , EMPLOYEE . PRAC5T , ( SELECT 
* FROM TABLE ( TIMEOFF_GET_UNAPPROVED_HOURS ( IN_EMPLOYEE_NUMBER , 'R' ) ) AS DATA ) , CASE WHEN EMPLOYEE . PRPAY = 'S' THEN ( SELECT PENDING FROM TABLE ( TIMEOFF_GET_SALARY_PENDING ( IN_EMPLOYER_NUMBER , IN_EMPLOYEE_NUMBER , 'R' ) ) AS DATA ) ELSE ( SELECT PENDING FROM TABLE ( TIMEOFF_GET_HOURLY_PENDING ( IN_EMPLOYER_NUMBER , IN_EMPLOYEE_NUMBER , 'R' ) ) AS DATA ) END , CASE WHEN EMPLOYEE . PRPAY = 'S' THEN ( SELECT PENDING_TMP FROM TABLE ( TIMEOFF_GET_SALARY_PENDING_TMP ( IN_EMPLOYER_NUMBER , IN_EMPLOYEE_NUMBER , 'R' ) ) AS DATA ) ELSE ( SELECT PENDING_TMP FROM TABLE ( TIMEOFF_GET_HOURLY_PENDING_TMP ( IN_EMPLOYER_NUMBER , IN_EMPLOYEE_NUMBER , 'R' ) ) AS DATA ) END , ( SELECT * FROM TABLE ( TIMEOFF_GET_UNAPPROVED_HOURS ( IN_EMPLOYEE_NUMBER , 'X' ) ) AS DATA ) , CASE WHEN EMPLOYEE . PRPAY 
= 'S' THEN ( SELECT PENDING FROM TABLE ( TIMEOFF_GET_SALARY_PENDING ( IN_EMPLOYER_NUMBER , IN_EMPLOYEE_NUMBER , 'X' ) ) AS DATA ) ELSE ( SELECT PENDING FROM TABLE ( TIMEOFF_GET_HOURLY_PENDING ( IN_EMPLOYER_NUMBER , IN_EMPLOYEE_NUMBER , 'X' 
) ) AS DATA ) END , CASE WHEN EMPLOYEE . PRPAY = 'S' THEN ( SELECT PENDING_TMP FROM TABLE ( TIMEOFF_GET_SALARY_PENDING_TMP ( IN_EMPLOYER_NUMBER , IN_EMPLOYEE_NUMBER , 'X' ) ) AS DATA ) ELSE ( SELECT PENDING_TMP FROM TABLE ( TIMEOFF_GET_HOURLY_PENDING_TMP ( IN_EMPLOYER_NUMBER , IN_EMPLOYEE_NUMBER , 'X' ) ) AS DATA ) END , ( SELECT * FROM TABLE ( TIMEOFF_GET_UNAPPROVED_HOURS ( IN_EMPLOYEE_NUMBER , 'B' ) ) AS DATA ) , CASE WHEN EMPLOYEE . PRPAY = 'S' THEN ( SELECT PENDING FROM TABLE ( TIMEOFF_GET_SALARY_PENDING ( IN_EMPLOYER_NUMBER , IN_EMPLOYEE_NUMBER , 'B' ) ) AS DATA ) ELSE ( SELECT PENDING FROM TABLE ( TIMEOFF_GET_HOURLY_PENDING ( 
IN_EMPLOYER_NUMBER , IN_EMPLOYEE_NUMBER , 'B' ) ) AS DATA ) END , CASE WHEN EMPLOYEE . PRPAY = 'S' THEN ( SELECT PENDING_TMP FROM TABLE ( TIMEOFF_GET_SALARY_PENDING_TMP ( IN_EMPLOYER_NUMBER , IN_EMPLOYEE_NUMBER , 'B' ) ) AS DATA ) ELSE ( SELECT PENDING_TMP FROM TABLE ( TIMEOFF_GET_HOURLY_PENDING_TMP ( IN_EMPLOYER_NUMBER , IN_EMPLOYEE_NUMBER , 'B' ) ) AS DATA ) END , ( SELECT * FROM TABLE ( TIMEOFF_GET_UNAPPROVED_HOURS ( IN_EMPLOYEE_NUMBER , 'J' ) ) AS DATA ) , CASE WHEN EMPLOYEE . PRPAY = 'S' THEN ( SELECT PENDING FROM TABLE ( TIMEOFF_GET_SALARY_PENDING 
( IN_EMPLOYER_NUMBER , IN_EMPLOYEE_NUMBER , 'J' ) ) AS DATA ) ELSE ( SELECT PENDING FROM TABLE ( TIMEOFF_GET_HOURLY_PENDING ( IN_EMPLOYER_NUMBER , IN_EMPLOYEE_NUMBER , 'J' ) ) AS DATA ) END , CASE WHEN EMPLOYEE . PRPAY = 'S' THEN ( SELECT PENDING_TMP FROM TABLE ( TIMEOFF_GET_SALARY_PENDING_TMP ( IN_EMPLOYER_NUMBER , IN_EMPLOYEE_NUMBER , 'J' ) ) AS DATA ) ELSE ( SELECT PENDING_TMP FROM TABLE ( TIMEOFF_GET_HOURLY_PENDING_TMP ( IN_EMPLOYER_NUMBER , IN_EMPLOYEE_NUMBER , 'J' ) ) AS DATA ) END , ( SELECT * FROM TABLE ( TIMEOFF_GET_UNAPPROVED_HOURS ( IN_EMPLOYEE_NUMBER , 'A' ) ) AS DATA ) , CASE WHEN EMPLOYEE . PRPAY = 'S' THEN ( SELECT PENDING FROM TABLE ( TIMEOFF_GET_SALARY_PENDING ( IN_EMPLOYER_NUMBER , IN_EMPLOYEE_NUMBER , 'A' ) ) AS DATA ) ELSE ( SELECT PENDING FROM TABLE ( TIMEOFF_GET_HOURLY_PENDING ( IN_EMPLOYER_NUMBER , IN_EMPLOYEE_NUMBER , 'A' ) ) AS DATA ) END , CASE WHEN EMPLOYEE . PRPAY = 'S' THEN ( SELECT PENDING_TMP FROM TABLE ( TIMEOFF_GET_SALARY_PENDING_TMP ( IN_EMPLOYER_NUMBER , IN_EMPLOYEE_NUMBER , 'A' ) ) AS DATA ) ELSE ( SELECT PENDING_TMP FROM TABLE ( TIMEOFF_GET_HOURLY_PENDING_TMP ( IN_EMPLOYER_NUMBER , IN_EMPLOYEE_NUMBER , 'A' ) ) AS DATA ) END FROM PRPMS EMPLOYEE LEFT JOIN PRPSP MANAGER ON EMPLOYEE . PREN = MANAGER . SPEN LEFT JOIN PRPMS MANAGER_ADDONS ON MANAGER_ADDONS . PREN = MANAGER . SPSPEN WHERE EMPLOYEE . PREN = REFACTOR_EMPLOYEE_ID ( IN_EMPLOYEE_NUMBER ) AND EMPLOYEE . PRER = IN_EMPLOYER_NUMBER ) SELECT A . EMPLOYER_NUMBER , A . EMPLOYEE_NUMBER , A . EMPLOYEE_NAME , A . 
EMPLOYEE_COMMON_NAME , A . EMPLOYEE_LAST_NAME , TRIM ( A . EMPLOYEE_LAST_NAME ) 
CONCAT ', ' CONCAT TRIM ( A . EMPLOYEE_COMMON_NAME ) CONCAT ' (' CONCAT TRIM ( A . EMPLOYEE_NUMBER ) CONCAT ')' , TRIM ( A . EMPLOYEE_COMMON_NAME ) CONCAT ' ' CONCAT TRIM ( A . EMPLOYEE_LAST_NAME ) CONCAT ' (' CONCAT TRIM ( A . EMPLOYEE_NUMBER ) CONCAT ')' , TRIM ( A . EMPLOYEE_EMAIL_ADDRESS ) , A . LEVEL_1 , A . LEVEL_2 , A . LEVEL_3 , A . LEVEL_4 , A . POSITION_CODE , TRIM ( A . POSITION_TITLE ) , A . EMPLOYEE_HIRE_DATE , A . SALARY_TYPE , A . MANAGER_EMPLOYEE_NUMBER , A . 
MANAGER_NAME , A . MANAGER_COMMON_NAME , A . MANAGER_LAST_NAME , TRIM ( A . MANAGER_LAST_NAME ) CONCAT ', ' CONCAT TRIM ( A . MANAGER_COMMON_NAME ) CONCAT ' (' 
CONCAT TRIM ( A . MANAGER_EMPLOYEE_NUMBER ) CONCAT ')' , TRIM ( A . MANAGER_COMMON_NAME ) CONCAT ' ' CONCAT TRIM ( A . MANAGER_LAST_NAME ) CONCAT ' (' CONCAT TRIM ( A . MANAGER_EMPLOYEE_NUMBER ) CONCAT ')' , TRIM ( A . MANAGER_EMAIL_ADDRESS ) , A . MANAGER_POSITION_CODE , A . MANAGER_POSITION_TITLE , A . PTO_EARNED , A . PTO_TAKEN , A . PTO_UNAPPROVED , A . PTO_PENDING , A . PTO_PENDING_TMP , CASE IN_INCLUDE_UNAPPROVED WHEN 'Y' THEN COALESCE ( ( A . PTO_UNAPPROVED + A . PTO_PENDING + A . PTO_PENDING_TMP ) , 0 ) ELSE COALESCE ( ( A . PTO_PENDING + A . PTO_PENDING_TMP ) , 0 ) END , CASE IN_INCLUDE_UNAPPROVED WHEN 'Y' THEN COALESCE ( ( A . PTO_EARNED - A . PTO_TAKEN - A . PTO_UNAPPROVED - A . PTO_PENDING - A . PTO_PENDING_TMP ) , 0 ) ELSE COALESCE ( ( A . PTO_EARNED - A . PTO_TAKEN - A . PTO_PENDING - A . PTO_PENDING_TMP ) , 0 ) END , A . FLOAT_EARNED , A . FLOAT_TAKEN , A . FLOAT_UNAPPROVED , A . FLOAT_PENDING , A . FLOAT_PENDING_TMP , CASE IN_INCLUDE_UNAPPROVED WHEN 'Y' THEN COALESCE ( ( A . FLOAT_UNAPPROVED + A . FLOAT_PENDING + A . FLOAT_PENDING_TMP ) , 0 ) ELSE COALESCE ( ( A . FLOAT_PENDING + A . FLOAT_PENDING_TMP ) , 0 ) END , CASE IN_INCLUDE_UNAPPROVED WHEN 'Y' THEN COALESCE ( ( A . FLOAT_EARNED - A . FLOAT_TAKEN - A . FLOAT_UNAPPROVED - A . FLOAT_PENDING - A . FLOAT_PENDING_TMP ) , 0 ) ELSE COALESCE ( ( A . FLOAT_EARNED - A . FLOAT_TAKEN - A . FLOAT_PENDING - A . FLOAT_PENDING_TMP ) , 0 ) END , A . SICK_EARNED 
, A . SICK_TAKEN , A . SICK_UNAPPROVED , A . SICK_PENDING , A . SICK_PENDING_TMP , CASE IN_INCLUDE_UNAPPROVED WHEN 'Y' THEN COALESCE ( ( A . SICK_UNAPPROVED + A . SICK_PENDING + A . SICK_PENDING_TMP ) , 0 ) ELSE COALESCE ( ( A . SICK_PENDING + A . SICK_PENDING_TMP ) , 0 ) END , CASE IN_INCLUDE_UNAPPROVED WHEN 'Y' THEN 
COALESCE ( ( A . SICK_EARNED - A . SICK_TAKEN - A . SICK_UNAPPROVED - A . SICK_PENDING - A . SICK_PENDING_TMP ) , 0 ) ELSE COALESCE ( ( A . SICK_EARNED - A . SICK_TAKEN - A . SICK_PENDING - A . SICK_PENDING_TMP ) , 0 ) END , A . GF_EARNED , A . GF_TAKEN , A . GF_UNAPPROVED , A . GF_PENDING , A . GF_PENDING_TMP , CASE IN_INCLUDE_UNAPPROVED WHEN 'Y' THEN COALESCE ( ( A . GF_UNAPPROVED + A . GF_PENDING + A . GF_PENDING_TMP ) , 0 ) ELSE COALESCE ( ( A . GF_PENDING + A . GF_PENDING_TMP ) , 0 ) END , CASE IN_INCLUDE_UNAPPROVED WHEN 'Y' THEN COALESCE ( ( A . GF_EARNED - A . GF_TAKEN - A . GF_UNAPPROVED - A . GF_PENDING - A . GF_PENDING_TMP ) , 0 ) ELSE COALESCE ( ( A . GF_EARNED - A . GF_TAKEN - A . GF_PENDING - A . GF_PENDING_TMP ) , 0 ) END , A . UNEXCUSED_UNAPPROVED , A . UNEXCUSED_PENDING , A . UNEXCUSED_PENDING_TMP , CASE IN_INCLUDE_UNAPPROVED WHEN 'Y' THEN COALESCE ( ( A . UNEXCUSED_UNAPPROVED + A . UNEXCUSED_PENDING + A . UNEXCUSED_PENDING_TMP ) 
, 0 ) ELSE COALESCE ( ( A . UNEXCUSED_PENDING + A . UNEXCUSED_PENDING_TMP ) , 0 
) END , A . BEREAVEMENT_UNAPPROVED , A . BEREAVEMENT_PENDING , A . BEREAVEMENT_PENDING_TMP , CASE IN_INCLUDE_UNAPPROVED WHEN 'Y' THEN COALESCE ( ( A . BEREAVEMENT_UNAPPROVED + A . BEREAVEMENT_PENDING + A . BEREAVEMENT_PENDING_TMP ) , 0 ) ELSE COALESCE ( ( A . BEREAVEMENT_PENDING + A . BEREAVEMENT_PENDING_TMP ) , 0 ) END , A . CIVIC_DUTY_UNAPPROVED , A . CIVIC_DUTY_PENDING , A . CIVIC_DUTY_PENDING_TMP , CASE IN_INCLUDE_UNAPPROVED WHEN 'Y' THEN COALESCE ( ( A . CIVIC_DUTY_UNAPPROVED + A . CIVIC_DUTY_PENDING + A . CIVIC_DUTY_PENDING_TMP ) , 0 ) ELSE COALESCE ( ( A . CIVIC_DUTY_PENDING + A . CIVIC_DUTY_PENDING_TMP ) , 0 ) END , A . UNPAID_UNAPPROVED , A . UNPAID_PENDING , A . UNPAID_PENDING_TMP , CASE IN_INCLUDE_UNAPPROVED WHEN 'Y' THEN COALESCE ( ( A . UNPAID_UNAPPROVED + A . UNPAID_PENDING + A . UNPAID_PENDING_TMP ) , 0 ) ELSE COALESCE ( ( A . UNPAID_PENDING + A . UNPAID_PENDING_TMP ) , 0 ) END FROM A ; END  ; 
  
GRANT ALTER , EXECUTE   
ON SPECIFIC FUNCTION HRTEST/TOGETEMPD 
TO ALDONCMS ; 
  
GRANT EXECUTE   
ON SPECIFIC FUNCTION HRTEST/TOGETEMPD 
TO PUBLIC ; 
  
GRANT ALTER , EXECUTE   
ON SPECIFIC FUNCTION HRTEST/TOGETEMPD 
TO S2KOBJOWNR ; 
  
