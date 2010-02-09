<?php
if (!defined ('TYPO3_MODE')) 	die ('Access denied.');

//Rename label of tt_address field "last_name" permanently as long as "ke_contacts" is installed
$TCA['tt_address']['columns']['last_name']['label'] = 'LLL:EXT:ke_contacts/locallang_db.xml:tt_address.last_name';

$TCA['tx_kecontacts_comments'] = array (
	'ctrl' => $TCA['tx_kecontacts_comments']['ctrl'],
	'interface' => array (
		'showRecordFieldList' => 'hidden,starttime,endtime,fe_group,comment,fe_user'
	),
	'feInterface' => $TCA['tx_kecontacts_comments']['feInterface'],
	'columns' => array (
		'hidden' => array (		
			'exclude' => 1,
			'label'   => 'LLL:EXT:lang/locallang_general.xml:LGL.hidden',
			'config'  => array (
				'type'    => 'check',
				'default' => '0'
			)
		),
		'starttime' => array (		
			'exclude' => 1,
			'label'   => 'LLL:EXT:lang/locallang_general.xml:LGL.starttime',
			'config'  => array (
				'type'     => 'input',
				'size'     => '8',
				'max'      => '20',
				'eval'     => 'date',
				'default'  => '0',
				'checkbox' => '0'
			)
		),
		'endtime' => array (		
			'exclude' => 1,
			'label'   => 'LLL:EXT:lang/locallang_general.xml:LGL.endtime',
			'config'  => array (
				'type'     => 'input',
				'size'     => '8',
				'max'      => '20',
				'eval'     => 'date',
				'checkbox' => '0',
				'default'  => '0',
				'range'    => array (
					'upper' => mktime(3, 14, 7, 1, 19, 2038),
					'lower' => mktime(0, 0, 0, date('m')-1, date('d'), date('Y'))
				)
			)
		),
		'fe_group' => array (		
			'exclude' => 1,
			'label'   => 'LLL:EXT:lang/locallang_general.xml:LGL.fe_group',
			'config'  => array (
				'type'  => 'select',
				'items' => array (
					array('', 0),
					array('LLL:EXT:lang/locallang_general.xml:LGL.hide_at_login', -1),
					array('LLL:EXT:lang/locallang_general.xml:LGL.any_login', -2),
					array('LLL:EXT:lang/locallang_general.xml:LGL.usergroups', '--div--')
				),
				'foreign_table' => 'fe_groups'
			)
		),
		'comment' => array (		
			'exclude' => 0,		
			'label' => 'LLL:EXT:ke_contacts/locallang_db.xml:tx_kecontacts_comments.comment',		
			'config' => array (
				'type' => 'text',
				'cols' => '30',	
				'rows' => '5',
			)
		),
		'fe_user' => array (		
			'exclude' => 0,		
			'label' => 'LLL:EXT:ke_contacts/locallang_db.xml:tx_kecontacts_comments.fe_user',		
			'config' => array (
				'type' => 'input',	
				'size' => '30',
			)
		),
		'organization' => array (        
            'exclude' => 0,        
            'label' => 'LLL:EXT:ke_contacts/locallang_db.xml:tx_kecontacts_comments.organization',        
            'config' => array (
                'type' => 'input',    
                'size' => '30',
            )
        ),

	),
	'types' => array (
		'0' => array('showitem' => 'hidden;;1;;1-1-1, comment, fe_user, organization')
	),
	'palettes' => array (
		'1' => array('showitem' => 'starttime, endtime, fe_group')
	)
);
?>