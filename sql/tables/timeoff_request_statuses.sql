/**
 * Author:  sawik
 * Created: Mar 28, 2016
 */
cl: call sawik/ll;
cl: CHGCURLIB CURLIB(SAWIK) ;

create table TIMEOFF_REQUEST_STATUSES for system name TOSTATUS (
	REQUEST_STATUS			for column REQSTAT 			varchar(1) not null default,
	DESCRIPTION			for column REQDESC			varchar(50) not null default
)
rcdfmt TOREQCODE1;
cl: crtjrnrcv TOSTATRCV;;
cl: crtjrn JRN(SAWIK/TOSTATJRN) JRNRCV(SAWIK/TOSTATRCV);;
cl: STRJRNPF FILE(TOSTATUS) JRN(TOSTATJRN) IMAGES(*BOTH);;
cl: grtobjaut obj(TOSTATUS) objtype(*file) refobj(hrdbfa/prpms) refobjtype(*file);;      
cl: chgobjown obj(TOSTATUS) objtype(*file) newown(s2kobjownr);;
