/**
 * Author:  sawik
 * Created: Mar 28, 2016
 */
cl: call sawik/ll;
cl: CHGCURLIB CURLIB(SAWIK) ;

create table TIMEOFF_REQUEST_ENTRIES for system name TOENTRY (
	ENTRY_ID for column ENTRYID decimal(13, 0) generated always as identity (
		start with 500 increment by 1
		minvalue 500 no maxvalue
		no cycle no order
		cache 20
	),
	
	REQUEST_ID			for column REQUESTID		decimal(13, 0) not null default,
	REQUEST_DATE			for column REQUESTDT		date not null default,
	REQUESTED_HOURS			for column REQUESTHRS		decimal(2, 1) not null default,
	REQUEST_CODE			for column REQCODE		varchar(1) not null default,
	WRITTMP				for column WRITTMP		char(1) not null default,
	LOCKED				for column LOCKED		char(1) not null default,
	REQUEST_DAY_OF_WEEK		for column REQDOW		varchar(3) null default
)
rcdfmt TOREQCODE1;
cl: crtjrnrcv TOSTATARCV;;
cl: crtjrn JRN(SAWIK/TOSTATJRN) JRNRCV(SAWIK/TOSTATARCV);;
cl: STRJRNPF FILE(TOSTATUS) JRN(TOSTATJRN) IMAGES(*BOTH);;
cl: grtobjaut obj(TOSTATUS) objtype(*file) refobj(hrdbfa/prpms) refobjtype(*file);;      
cl: chgobjown obj(TOSTATUS) objtype(*file) newown(s2kobjownr);;