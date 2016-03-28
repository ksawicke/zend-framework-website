/**
 * Author:  sawik
 * Created: Mar 28, 2016
 */
cl: call sawik/ll;
cl: CHGCURLIB CURLIB(SAWIK) ;

create table TIMEOFF_REQUESTS for system name TOREQUESTS (
	REQUEST_ID for column REQUESTID decimal(13, 0) generated always as identity (
		start with 100000 increment by 1
		minvalue 100000 no maxvalue
		no cycle no order
		cache 20
	),
	
	EMPLOYEE_NUMBER			for column EMPNUM	 		varchar(9) not null default,
	REQUEST_STATUS			for column REQSTAT 			varchar(1) not null default,
	REQUEST_REASON			for column REASON			varchar(200) null default,
	EMPLOYEE_DATA			for column EMPDATA			char(4096) null default,
	CREATE_USER			for column CRTEMPID                     varchar(9) not null default,
	CREATE_TIMESTAMP		for column CRTTS			timestamp not null default,
	UPDATE_TIMESTAMP		for column UPDTS			timestamp not null 
                                                            generated always for each row on update as row change timestamp
)
rcdfmt TOREQUEST1;
cl: crtjrnrcv TIMEOFFRCV;;
cl: crtjrn JRN(SAWIK/TIMEOFFJRN) JRNRCV(SAWIK/TIMEOFFRCV);;
cl: STRJRNPF FILE(TOREQUESTS) JRN(TIMEOFFJRN) IMAGES(*BOTH);;
cl: grtobjaut obj(TOREQUESTS) objtype(*file) refobj(hrdbfa/prpms) refobjtype(*file);;      
cl: chgobjown obj(TOREQUESTS) objtype(*file) newown(s2kobjownr);;  