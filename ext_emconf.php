<?php

########################################################################
# Extension Manager/Repository config file for ext "ke_contacts".
#
# Auto generated 13-05-2011 15:20
#
# Manual updates:
# Only the data in the array - everything else is removed by next
# writing. "version" and "dependencies" must not be touched!
########################################################################

$EM_CONF[$_EXTKEY] = array(
	'title' => 'Kontaktverwaltung',
	'description' => 'Manage tt_address contacts (part of keWorks, http://www.keworks.de)',
	'category' => 'plugin',
	'author' => 'F. Friedrich, I. Gaisinskaia (www.kennziffer.com GmbH)',
	'author_email' => 'info@kennziffer.com',
	'shy' => '',
	'dependencies' => 'static_info_tables_de,tt_address',
	'conflicts' => '',
	'priority' => '',
	'module' => '',
	'state' => 'stable',
	'internal' => '',
	'uploadfolder' => 0,
	'createDirs' => '',
	'modify_tables' => '',
	'clearCacheOnLoad' => 1,
	'lockType' => '',
	'author_company' => 'www.kennziffer.com GmbH',
	'version' => '1.1.1',
	'constraints' => array(
		'depends' => array(
			'static_info_tables_de' => '2.0.0-',
			'tt_address' => '2.2.1-',
			'typo3' => '4.3.0-',
			'php' => '5.2.0-',
		),
		'conflicts' => array(
		),
		'suggests' => array(
		),
	),
	'_md5_values_when_last_written' => 'a:43:{s:9:"ChangeLog";s:4:"23d4";s:10:"README.txt";s:4:"ee2d";s:12:"ext_icon.gif";s:4:"b7e8";s:17:"ext_localconf.php";s:4:"2a52";s:14:"ext_tables.php";s:4:"3b48";s:14:"ext_tables.sql";s:4:"483e";s:31:"icon_tx_kecontacts_comments.gif";s:4:"e9c7";s:13:"locallang.xml";s:4:"63e1";s:16:"locallang_db.xml";s:4:"9e6d";s:7:"tca.php";s:4:"cdda";s:14:"doc/manual.sxw";s:4:"d241";s:19:"doc/wizard_form.dat";s:4:"f935";s:20:"doc/wizard_form.html";s:4:"90f3";s:14:"pi1/ce_wiz.gif";s:4:"b7e8";s:31:"pi1/class.tx_kecontacts_pi1.php";s:4:"0d57";s:39:"pi1/class.tx_kecontacts_pi1_wizicon.php";s:4:"84c4";s:13:"pi1/clear.gif";s:4:"cc11";s:16:"pi1/flexform.xml";s:4:"1f5d";s:17:"pi1/locallang.xml";s:4:"2e0d";s:20:"pi1/static/setup.txt";s:4:"4b1c";s:23:"res/css/ke_contacts.css";s:4:"80f8";s:16:"res/img/Edit.png";s:4:"88f4";s:22:"res/img/background.gif";s:4:"b7e6";s:20:"res/img/backlink.png";s:4:"a64e";s:22:"res/img/buttons_bg.gif";s:4:"aac3";s:28:"res/img/comments_head_bg.gif";s:4:"3f37";s:19:"res/img/company.png";s:4:"1c87";s:25:"res/img/css_gradient1.gif";s:4:"e42b";s:23:"res/img/data_delete.gif";s:4:"19be";s:21:"res/img/data_edit.gif";s:4:"6d0e";s:20:"res/img/error_bg.gif";s:4:"c982";s:25:"res/img/header_single.gif";s:4:"6758";s:27:"res/img/icon_new_ticket.png";s:4:"148a";s:36:"res/img/list_item_head_bg_normal.gif";s:4:"bd6f";s:39:"res/img/list_item_userhead_bg_norma.gif";s:4:"4378";s:18:"res/img/person.png";s:4:"54e8";s:16:"res/img/save.gif";s:4:"9142";s:30:"res/img/status_message_bgr.gif";s:4:"0de1";s:16:"res/img/tick.png";s:4:"c9b5";s:25:"res/templates/delete.html";s:4:"74c1";s:23:"res/templates/edit.html";s:4:"e4d6";s:27:"res/templates/listview.html";s:4:"ddcf";s:29:"res/templates/singleview.html";s:4:"02a2";}',
	'suggests' => array(
	),
);

?>