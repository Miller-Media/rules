<?php
/**
 * @brief		IPS4 Rules
 * @author		Kevin Carwile (http://www.linkedin.com/in/kevincarwile)
 * @copyright		(c) 2014 - Kevin Carwile
 * @package		Rules
 * @since		6 Feb 2015
 */


namespace IPS\rules\Log;

/* To prevent PHP errors (extending class does not exist) revealing path */
if ( !defined( '\IPS\SUITE_UNIQUE_KEY' ) )
{
	header( ( isset( $_SERVER['SERVER_PROTOCOL'] ) ? $_SERVER['SERVER_PROTOCOL'] : 'HTTP/1.0' ) . ' 403 Forbidden' );
	exit;
}

/**
 * Node
 */
class _Argument extends \IPS\rules\Action\Argument implements \IPS\Node\Permissions
{
	
	/**
	 * @brief	[ActiveRecord] Database Table
	 */
	public static $databaseTable = 'rules_log_arguments';
	
	/**
	 * @brief	[Node] Node Title
	 */
	public static $nodeTitle = 'custom_log_arguments';
		
	/**
	 * @brief	Parent Node Class
	 */
	public static $parentNodeClass = '\IPS\rules\Log\Custom';
	
	/**
	 * @brief	Original Data
	 */
	public $originalData = array();
	
	/**
	 * @brief	[Node] App for permission index
	 */
	public static $permApp = 'rules';
	
	/**
	 * @brief	[Node] Type for permission index
	 */
	public static $permType = 'custom_log_data';
	
	/**
	 * @brief	The map of permission columns
	 */
	public static $permissionMap = array
	(
		'view'			=> 'view',
	);
	
	/**
	 * @brief	[Node] Prefix string that is automatically prepended to permission matrix language strings
	 */
	public static $permissionLangPrefix = 'rules_log_data_';
	
	/** 
	 * @brief	Reserved variable names
	 */
	public static $reservedWords = array( 'entity', 'message' );
	
	/**
	 * @brief	Indicates if machine name needs to be unique to the argument class
	 */
	public static $uniqueToClass = TRUE;
	
	/**
	 * Init
	 *
	 * @return	void
	 */
	public function init()
	{
		$this->originalData = $this->_data;
	}
	
	/**
	 * [ActiveRecord] Save 
	 */
	public function save()
	{
		$type_class = $this->class == 'custom' ? $this->custom_class : $this->class;
		
		/**
		 * Make database changes if things have changed
		 */
		if ( ! $this->_new )
		{			
			/**
			 * Storing data for a new object class?
			 * 
			 * DROP COLUMN
			 */
			if 
			( 
				in_array( $this->type, array( 'object', 'array' ) ) and 
				( 
					$this->originalData[ 'type' ] != $this->type or 
					$this->originalData[ 'class' ] != $this->class or
					(
						$this->class == 'custom' and 
						$this->originalData[ 'custom_class' ] != $this->custom_class
					)
				)
			)
			{
				try
				{
					\IPS\Db::i()->dropColumn( $this->databaseTable(), 'data_' . $this->originalData[ 'varname' ] );
				}
				catch ( \IPS\Db\Exception $e ) { }				
			}
			
			/**
			 * Just changing the column name or data type?
			 *
			 * CHANGE COLUMN
			 */
			else if ( $this->originalData[ 'type' ] != $this->type or $this->originalData[ 'varname' ] != $this->varname )
			{
				\IPS\Db::i()->changeColumn( $this->databaseTable(), 'data_' . $this->originalData[ 'varname' ], \IPS\rules\Data::columnDefinition( $this->type, $type_class, $this->varname ) );
			}
		}
		
		/**
		 * If we don't have a column for this data... create one
		 */
		if ( ! \IPS\Db::i()->checkForColumn( \IPS\rules\Log\Custom::getTableName( $this->parent()->class ), 'data_' . $this->varname ) )
		{
			\IPS\Db::i()->addColumn( $this->databaseTable(), \IPS\rules\Data::columnDefinition( $this->type, $type_class, $this->varname ) );
		}
		
		/**
		 * Update the original data
		 */
		$this->originalData = $this->_data;
		
		parent::save();
	}
	
	/**
	 * Get Database Table Name
	 *
	 * @return 	string		Table Name
	 */
	public function databaseTable()
	{
		return \IPS\rules\Log\Custom::getTableName( $this->parent()->class );
	}
	
	/**
	 * [ActiveRecord] Delete Record
	 *
	 * @return	void
	 */
	public function delete()
	{		
		try
		{
			\IPS\Db::i()->dropColumn( $this->databaseTable(), 'data_' . $this->varname );
		}
		catch ( \IPS\Db\Exception $e ) { }
		
		return parent::delete();
	}
	
}