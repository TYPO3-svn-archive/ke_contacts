#
# Table structure for table 'tx_kecontacts_comments'
#
CREATE TABLE tx_kecontacts_comments (
	uid int(11) NOT NULL auto_increment,
	pid int(11) DEFAULT '0' NOT NULL,
	tstamp int(11) DEFAULT '0' NOT NULL,
	crdate int(11) DEFAULT '0' NOT NULL,
	cruser_id int(11) DEFAULT '0' NOT NULL,
	deleted tinyint(4) DEFAULT '0' NOT NULL,
	hidden tinyint(4) DEFAULT '0' NOT NULL,
	starttime int(11) DEFAULT '0' NOT NULL,
	endtime int(11) DEFAULT '0' NOT NULL,
	fe_group int(11) DEFAULT '0' NOT NULL,
	comment text,
	fe_user tinytext,
	organization int(11) DEFAULT '0' NOT NULL,
	
	PRIMARY KEY (uid),
	KEY parent (pid)
);




#
# Table structure for table 'tt_address_tx_kecontacts_members_mm'
# 
#
CREATE TABLE tt_address_tx_kecontacts_members_mm (
  uid_local int(11) DEFAULT '0' NOT NULL,
  uid_foreign int(11) DEFAULT '0' NOT NULL,
  tablenames varchar(30) DEFAULT '' NOT NULL,
  sorting int(11) DEFAULT '0' NOT NULL,
  KEY uid_local (uid_local),
  KEY uid_foreign (uid_foreign)
);




#
# Table structure for table 'tt_address_tx_kecontacts_comments_mm'
# 
#
CREATE TABLE tt_address_tx_kecontacts_comments_mm (
  uid_local int(11) DEFAULT '0' NOT NULL,
  uid_foreign int(11) DEFAULT '0' NOT NULL,
  tablenames varchar(30) DEFAULT '' NOT NULL,
  sorting int(11) DEFAULT '0' NOT NULL,
  KEY uid_local (uid_local),
  KEY uid_foreign (uid_foreign)
);



#
# Table structure for table 'tt_address'
#
CREATE TABLE tt_address (
	tx_kecontacts_members int(11) DEFAULT '0' NOT NULL,
	tx_kecontacts_comments int(11) DEFAULT '0' NOT NULL,
	tx_kecontacts_function tinytext
);



#
# Table structure for table 'tt_address'
#
CREATE TABLE tt_address (
	tx_kecontacts_type int(11) DEFAULT '0' NOT NULL
);