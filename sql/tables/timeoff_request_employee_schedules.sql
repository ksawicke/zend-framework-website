/**
 * Author:  sawik
 * Created: Mar 28, 2016
 */
cl: call sawik/ll;
cl: CHGCURLIB CURLIB(SAWIK) ;

create table TIMEOFF_REQUEST_EMPLOYEE_SCHEDULES for system name TOEMPSCH (
	EMPLOYEE_ID			for column EMPNUM			varchar(9) null default,
	SCHEDULE_MON			for column SCHMON			decimal(7,2) null default,
	SCHEDULE_TUE			for column SCHTUE			decimal(7,2) null default,
	SCHEDULE_WED			for column SCHWED			decimal(7,2) null default,
	SCHEDULE_THU			for column SCHTHU			decimal(7,2) null default,
	SCHEDULE_FRI			for column SCHFRI			decimal(7,2) null default,
	SCHEDULE_SAT			for column SCHSAT			decimal(7,2) null default,
	SCHEDULE_SUN			for column SCHSUN			decimal(7,2) null default
)
rcdfmt TOEMPSCH;
cl: crtjrnrcv TOSTATARCV;;
cl: crtjrn JRN(SAWIK/TOSTATJRN) JRNRCV(SAWIK/TOSTATARCV);;
cl: STRJRNPF FILE(TOSTATUS) JRN(TOSTATJRN) IMAGES(*BOTH);;
cl: grtobjaut obj(TOSTATUS) objtype(*file) refobj(hrdbfa/prpms) refobjtype(*file);;      
cl: chgobjown obj(TOSTATUS) objtype(*file) newown(s2kobjownr);;