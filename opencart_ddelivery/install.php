<?php
$checkColumnSQL = "SELECT *
FROM information_schema.COLUMNS
WHERE TABLE_SCHEMA = '".DB_DATABASE."'
AND TABLE_NAME = '".DB_PREFIX."order'
AND COLUMN_NAME = 'ddelivery_id'";

/**
 * @var $_db DB
 */
$_db = $this->db;
$columnExists = $_db->query($checkColumnSQL);
if(1>$columnExists->num_rows){
	$addColumnSQL = "ALTER TABLE `".DB_PREFIX."order` ADD `ddelivery_id` char(128) NOT NULL";
	$_db->query($addColumnSQL);
}