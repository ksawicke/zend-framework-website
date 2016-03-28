/**
 * Author:  sawik
 * Created: Mar 28, 2016
 */
cl: call sawik/ll;
cl: CHGCURLIB CURLIB(SAWIK) ;

create table TIMEOFF_REQUEST_LOG for system name TOREQLOG (
	REQUEST_LOG_ID for column REQLOGID decimal(13, 0) generated always as identity (
		start with 1 increment by 1
		minvalue 1 no maxvalue
		no cycle no order
		cache 20
	),
	REQUEST_ID			for column REQUESTID		decimal(13,0) null default,
	EMPLOYEE_NUMBER			for column EMPNUM	 	varchar(9) not null default,
	COMMENT				for column COMMENT 		varchar(500) not null default,
	CREATE_USER			for column CRTEMPID 		varchar(9) not null default,
	CREATE_TIMESTAMP		for column CRTTS		timestamp not null default,
	UPDATE_TIMESTAMP		for column UPDTS		timestamp not null 
                                                            generated always for each row on update as row change timestamp
)
rcdfmt TOREQLOG;
cl: crtjrnrcv TIMEOFFRCV;;
cl: crtjrn JRN(SAWIK/TIMEOFFJRN) JRNRCV(SAWIK/TIMEOFFRCV);;
cl: STRJRNPF FILE(TOREQLOG) JRN(TIMEOFFJRN) IMAGES(*BOTH);;
cl: grtobjaut obj(TOREQLOG) objtype(*file) refobj(hrdbfa/prpms) refobjtype(*file);;      
cl: chgobjown obj(TOREQLOG) objtype(*file) newown(s2kobjownr);;  