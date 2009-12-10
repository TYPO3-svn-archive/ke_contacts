<?php
if (!defined ('TYPO3_MODE')) {
	die ('Access denied.');
}

t3lib_div::loadTCA('tt_content');
$TCA['tt_content']['types']['list']['subtypes_excludelist'][$_EXTKEY.'_pi1']='layout,select_key,pages';


t3lib_extMgm::addPlugin(array(
	'LLL:EXT:ke_contacts/locallang_db.xml:tt_content.list_type_pi1',
	$_EXTKEY . '_pi1',
	t3lib_extMgm::extRelPath($_EXTKEY) . 'ext_icon.gif'
),'list_type');

t3lib_extMgm::addStaticFile($_EXTKEY,"pi1/static/","Kontaktverwaltung");
t3lib_extMgm::addPiFlexFormValue($_EXTKEY.'_pi1','FILE:EXT:'.$_EXTKEY.'/pi1/flexform.xml');
$TCA['tt_content']['types']['list']['subtypes_addlist'][$_EXTKEY.'_pi1']='pi_flexform';

if (TYPO3_MODE == 'BE') {
	$TBE_MODULES_EXT['xMOD_db_new_content_el']['addElClasses']['tx_kecontacts_pi1_wizicon'] = t3lib_extMgm::extPath($_EXTKEY).'pi1/class.tx_kecontacts_pi1_wizicon.php';
}

$TCA['tx_kecontacts_comments'] = array (
	'ctrl' => array (
		'title'     => 'LLL:EXT:ke_contacts/locallang_db.xml:tx_kecontacts_comments',		
		'label'     => 'uid',	
		'tstamp'    => 'tstamp',
		'crdate'    => 'crdate',
		'cruser_id' => 'cruser_id',
		'default_sortby' => 'ORDER BY crdate',	
		'delete' => 'deleted',	
		'enablecolumns' => array (		
			'disabled' => 'hidden',	
			'starttime' => 'starttime',	
			'endtime' => 'endtime',	
			'fe_group' => 'fe_group',
		),
		'dynamicConfigFile' => t3lib_extMgm::extPath($_EXTKEY).'tca.php',
		'iconfile'          => t3lib_extMgm::extRelPath($_EXTKEY).'icon_tx_kecontacts_comments.gif',
	),
);

/*$tempColumns = array (
	'tx_kecontacts_members' => array (		
		'exclude' => 0,		
		'label' => 'LLL:EXT:ke_contacts/locallang_db.xml:tt_address.tx_kecontacts_members',		
		'config' => array (
			'type' => 'select',	
			'foreign_table' => 'tt_address',	
			'foreign_table_where' => 'ORDER BY tt_address.uid',	
			'size' => 1,	
			'minitems' => 0,
			'maxitems' => 1,	
			"MM" => "tt_address_tx_kecontacts_members_mm",	
			'wizards' => array(
				'_PADDING'  => 2,
				'_VERTICAL' => 1,
				'add' => array(
					'type'   => 'script',
					'title'  => 'Create new record',
					'icon'   => 'add.gif',
					'params' => array(
						'table'    => 'tt_address',
						'pid'      => '###CURRENT_PID###',
						'setValue' => 'prepend'
					),
					'script' => 'wizard_add.php',
				),
			),
		)
	),*/
	$tempColumns = array (
    'tx_kecontacts_members' => array (        
        'exclude' => 0,        
        'label' => 'LLL:EXT:ke_contacts/locallang_db.xml:tt_address.tx_kecontacts_members',        
        'config' => array (
            'type' => 'group',    
            'internal_type' => 'db',    
            'allowed' => 'tt_address',    
            'size' => 10,    
            'minitems' => 0,
            'maxitems' => 999,    
            "MM" => "tt_address_tx_kecontacts_members_mm",
        )
    ),
	'tx_kecontacts_comments' => array (		
		'exclude' => 0,		
		'label' => 'LLL:EXT:ke_contacts/locallang_db.xml:tt_address.tx_kecontacts_comments',		
		'config' => array (
			 'type' => 'group',    
            'internal_type' => 'db',    
            'allowed' => 'tx_kecontacts_comments',    
            'size' => 10,    
            'minitems' => 0,
            'maxitems' => 100,    
            "MM" => "tt_address_tx_kecontacts_comments_mm",
		)
	),
);


t3lib_div::loadTCA('tt_address');
t3lib_extMgm::addTCAcolumns('tt_address',$tempColumns,1);
t3lib_extMgm::addToAllTCAtypes('tt_address','tx_kecontacts_members;;;;1-1-1, tx_kecontacts_comments');

$tempColumns = array (
	'tx_kecontacts_type' => array (		
		'exclude' => 0,		
		'label' => 'LLL:EXT:ke_contacts/locallang_db.xml:tt_address.tx_kecontacts_type',		
		'config' => array (
			'type' => 'select',
			'items' => array (
				array('LLL:EXT:ke_contacts/locallang_db.xml:tt_address.tx_kecontacts_type.I.0', '0'),
				array('LLL:EXT:ke_contacts/locallang_db.xml:tt_address.tx_kecontacts_type.I.1', '1'),
				array('LLL:EXT:ke_contacts/locallang_db.xml:tt_address.tx_kecontacts_type.I.2', '2'),
			),
			'size' => 1,	
			'maxitems' => 1,
		)
	),
	'tx_kecontacts_function' => array (        
        'exclude' => 0,        
        'label' => 'LLL:EXT:ke_contacts/locallang_db.xml:tt_address.tx_kecontacts_function',
        'config' => array (
            'type' => 'input',    
            'size' => '30',
        )
    ),

);


t3lib_div::loadTCA('tt_address');
t3lib_extMgm::addTCAcolumns('tt_address',$tempColumns,1);
t3lib_extMgm::addToAllTCAtypes('tt_address','tx_kecontacts_function,tx_kecontacts_type;;;;1-1-1');
?>