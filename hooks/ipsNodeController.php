//<?php

class rules_hook_ipsNodeController extends _HOOK_CLASS_
{

	/**
	 * Get form
	 *
	 * @param	\IPS\Node\Model
	 * @return	\IPS\Helpers\Form
	 */
	protected function _addEditForm( \IPS\Node\Model $node )
	{
		$form = parent::_addEditForm( $node );
		
		/* Rules Custom Data */
		if ( \IPS\Db::i()->select( 'COUNT(*)', 'rules_data', array( 'data_class=? AND data_use_mode IN ( \'public\', \'admin\' )', $node::rulesDataClass() ) )->first() )
		{
			$form->addTab( 'rules_node_data_tab' );
			foreach ( \IPS\Db::i()->select( '*', 'rules_data', array( 'data_class=? AND data_use_mode IN ( \'public\', \'admin\' )', $node::rulesDataClass() ) ) as $row )
			{
				$data_field = \IPS\rules\Data::constructFromData( $row );
				
				$configuration 	= json_decode( $data_field->configuration, TRUE ) ?: array();
				$nodeClass 	= get_class( $node );
				$containers 	= 'containers-' . str_replace( '\\', '-', $nodeClass );
				
				if ( isset( $configuration[ $containers ] ) and is_array( $configuration[ $containers ] ) )
				{	
					if ( ! in_array( $node->_id, $configuration[ $containers ] ) )
					{
						continue;
					}
				}
				
				/**
				 * Check if user has permission to edit field
				 */
				if ( $data_field->can( 'edit' ) )
				{
					foreach( $data_field->formElements( $node ) as $name => $element )
					{
						$form->add( $element );
					}
				}
			}
		}
		
		/* Rules Custom Logs */
		foreach( \IPS\rules\Log\Custom::roots( 'view', NULL, array( array( 'custom_log_class=?', $node::rulesDataClass() ) ) ) as $log )
		{
			$tab_title = 'custom_log_' . $log->id;
			\IPS\Member::loggedIn()->language()->words[ 'custom_log_' . $log->id ] = $log->title;
			
			$form->addTab( $tab_title );
			$form->addHtml( $log->logsTable( $member ) );
		}		
		
		return $form;
	}
}