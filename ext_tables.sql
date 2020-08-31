
CREATE TABLE tx_dfbsync_data (
	uid int(11) NOT NULL auto_increment,
	pid int(11) DEFAULT '0' NOT NULL,
	tstamp int(11) DEFAULT '0' NOT NULL,
	crdate int(11) DEFAULT '0' NOT NULL,
	cruser_id int(11) DEFAULT '0' NOT NULL,
	deleted tinyint(4) DEFAULT '0' NOT NULL,

	competition int(11) DEFAULT '0' NOT NULL,
	lastsync datetime DEFAULT NULL,
	success tinyint(4) DEFAULT '0' NOT NULL,

	PRIMARY KEY (uid),
	KEY parent (pid)
);

CREATE TABLE sys_log (
	NEWid varchar(40) DEFAULT '' NOT NULL,
);
