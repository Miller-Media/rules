<?php


namespace IPS\rules\modules\admin\rules;

/* To prevent PHP errors (extending class does not exist) revealing path */
if ( !defined( '\IPS\SUITE_UNIQUE_KEY' ) )
{
	header( ( isset( $_SERVER['SERVER_PROTOCOL'] ) ? $_SERVER['SERVER_PROTOCOL'] : 'HTTP/1.0' ) . ' 403 Forbidden' );
	exit;
}

/**
 * admin
 */
class _admin extends \IPS\Node\Controller
{
	/**
	 * Node Class
	 */
	protected $nodeClass = '\IPS\rules\Rule';
	
	
	/**
	 * Constructor
	 *
	 * @param	\IPS\Http\Url|NULL	$url		The base URL for this controller or NULL to calculate automatically
	 * @return	void
	 */
	public function __construct( $url=NULL )
	{
		parent::__construct( $url );
	}
	
	/**
	 * Execute
	 *
	 * @return	void
	 */
	public function execute()
	{
		\IPS\Dispatcher::i()->checkAcpPermission( 'admin_manage' );
		
		/**
		 * Since nodes don't really support multiple subnode types
		 * out of the box, we do some smart switching based on url
		 * parameters.
		 */		
		if ( \IPS\Request::i()->aspect )
		{
			$nodeClass			= $this->nodeClass;
			$aspect				= \IPS\rules\Rule\Aspect::load( \IPS\Request::i()->aspect );
			
			/**
			 * If the subnode flag is set, our controller will be looking to work with
			 * the subnode class, otherwise, its gonna want to work with the regular
			 * node class.
			 */
			if ( \IPS\Request::i()->subnode )
			{
				$nodeClass::$subnodeClass	= $aspect->aspectClass;
			}
			else
			{
				$this->nodeClass 		= $aspect->aspectClass;
			}			
		}
		else if ( \IPS\Request::i()->subnode and \IPS\Request::i()->parent )
		{
			$nodeClass			= $this->nodeClass;
			$aspect				= \IPS\rules\Rule\Aspect::load( \IPS\Request::i()->parent );
			$nodeClass::$subnodeClass	= $aspect->aspectClass;
		}
		
		parent::execute();
	}
	
	/**
	 * Add/Edit Form
	 *
	 * @return void
	 */
	protected function form()
	{
		return parent::form();
	}
	
	/**
	 * Get Single Row
	 *
	 * @param	mixed	$id		May be ID number (or key) or an \IPS\Node\Model object
	 * @param	bool	$root	Format this as the root node?
	 * @param	bool	$noSort	If TRUE, sort options will be disabled (used for search results)
	 * @return	string
	 */
	public function _getRow( $id, $root=FALSE, $noSort=FALSE )
	{			
		return parent::_getRow( $this->_adaptId( $id ), $root, $noSort );
	}
	
	/**
	 * Get Child Rows
	 *
	 * @param	int|string	$id		Row ID
	 * @return	array
	 */
	public function _getChildren( $id )
	{			
		return parent::_getChildren( $this->_adaptId( $id ) );		
	}
	
	/**
	 * Get Row parent ID
	 *
	 * @param	int|string	$id		Row ID
	 * @return	int|string	Parent ID
	 */
	public function _getRowParentId( $id )
	{				
		return parent::_getRowParentId( $this->_adaptId( $id ) );
	}
	
	/**
	 * Adapt the controller based on our node ID
	 */
	public function _adaptId( $id )
	{
		/**
		 * Work out the nodeClass we should be using and the real
		 * id of our node by parsing our cryptic node IDS O_o
		 */
		if ( is_string( $id ) and mb_substr( $id, 0, 2 ) === 's.' )
		{
			$subId 	= mb_substr( $id, 2 );
			$parts 	= explode( '-', $subId );
			
			if ( count( $parts ) > 1 )
			{
				if ( count( $parts ) == 2 )
				{
					$id			= $subId;
					$this->nodeClass 	= '\IPS\rules\Rule\Aspect';
				}
				else if ( count( $parts ) == 3 )
				{
					$id			= $parts[ 2 ];
					$aspect 		= \IPS\rules\Rule\Aspect::load( $parts[ 0 ] . '-' . $parts[ 1 ] );
					$this->nodeClass	= $aspect->aspectClass;
				}
			}
		}

		return $id;
	}
}