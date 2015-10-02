<?php


namespace IPS\rules\modules\admin\rules;

/* To prevent PHP errors (extending class does not exist) revealing path */
if ( !defined( '\IPS\SUITE_UNIQUE_KEY' ) )
{
	header( ( isset( $_SERVER['SERVER_PROTOCOL'] ) ? $_SERVER['SERVER_PROTOCOL'] : 'HTTP/1.0' ) . ' 403 Forbidden' );
	exit;
}

/**
 * data
 */
class _data extends \IPS\Node\Controller
{
	/**
	 * Node Class
	 */
	protected $nodeClass = '\IPS\rules\Data';
	
	/**
	 * Title can contain HTML?
	 */
	public $_titleHtml = TRUE;
	
	/**
	 * Description can contain HTML?
	 */
	public $_descriptionHtml = TRUE;
	
	/**
	 * Execute
	 *
	 * @return	void
	 */
	public function execute()
	{
		\IPS\Dispatcher::i()->checkAcpPermission( 'rules_manage' );
		
		\IPS\Output::i()->jsFiles = array_merge( \IPS\Output::i()->jsFiles, \IPS\Output::i()->js( 'chosen.jquery.js', 'rules', 'interface' ) );	
		\IPS\Output::i()->cssFiles = array_merge( \IPS\Output::i()->cssFiles, \IPS\Theme::i()->css( 'chosen.css', 'rules', 'admin' ) );
		
		/* Javascript Controller */
		\IPS\Output::i()->jsFiles = array_merge( \IPS\Output::i()->jsFiles, \IPS\Output::i()->js( 'admin_ui.js', 'rules', 'admin' ) );
		
		parent::execute();
	}
	
	/**
	 * Manage
	 */
	protected function manage()
	{
		\IPS\Output::i()->sidebar[ 'actions' ][ 'documentation' ] = array(
			'icon'	=> 'file',
			'link'	=> \IPS\Http\Url::external( 'http://www.ipsguru.net/documentation/rules/general/fields' ),
			'title'	=> 'rules_documentation',
			'target' => '_blank',
			'data' => array(),
		);		

		if ( ! \IPS\Request::i()->isAjax() )
		{
			\IPS\Output::i()->output .= '<div style="font-size:1.25em"><i class="fa fa-info"></i> Custom data fields are used to attach, track, or display custom information for members, content, and nodes.</div>';
		}
		
		parent::manage();
	}
	
}