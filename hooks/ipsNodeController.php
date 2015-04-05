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
		
		if ( \IPS\Db::i()->select( 'COUNT(*)', 'rules_data', array( 'data_class=? AND data_use_mode IN ( \'public\', \'admin\' )', $node::rulesDataClass() ) )->first() )
		{
			$form->addTab( 'rules_node_data_tab' );
			foreach ( \IPS\Db::i()->select( '*', 'rules_data', array( 'data_class=? AND data_use_mode IN ( \'public\', \'admin\' )', $node::rulesDataClass() ) ) as $row )
			{
				$data_field = \IPS\rules\Data::constructFromData( $row );
				foreach( $data_field->formElements( $node ) as $name => $element )
				{
					$form->add( $element );
				}
			}
		}
		
		return $form;
	}
}