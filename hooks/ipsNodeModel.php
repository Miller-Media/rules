//<?php

abstract class rules_hook_ipsNodeModel extends _HOOK_CLASS_
{

	/**
	 * [Node] Add/Edit Form
	 *
	 * @param	\IPS\Helpers\Form	$form	The form
	 * @return	void
	 */
	public function form( &$form )
	{
		parent::form( $form );
		
		if ( \IPS\Db::i()->select( 'COUNT(*)', 'rules_data', array( 'data_class=? AND data_use_mode IN ( \'public\', \'admin\' )', static::rulesDataClass() ) )->first() )
		{
			$form->addTab( 'rules_node_data_tab' );
			foreach ( \IPS\Db::i()->select( '*', 'rules_data', array( 'data_class=? AND data_use_mode IN ( \'public\', \'admin\' )', static::rulesDataClass() ) ) as $row )
			{
				$data_field = \IPS\rules\Data::constructFromData( $row );
				foreach( $data_field->formElements( $this ) as $name => $element )
				{
					$form->add( $element );
				}
			}
		}
	}

	/**
	 * [Node] Save Add/Edit Form
	 *
	 * @param	array	$values	Values from the form
	 * @return	void
	 */
	public function saveForm( $values )
	{		
		foreach ( \IPS\Db::i()->select( '*', 'rules_data', array( 'data_class=? AND data_use_mode IN ( \'public\', \'admin\' )', $this::rulesDataClass() ) ) as $row )
		{
			if ( isset ( $values[ 'rules_data_' . $row[ 'data_column_name' ] ] ) )
			{
				$this->setRulesData( $row[ 'data_column_name' ], $values[ 'rules_data_' . $row[ 'data_column_name' ] ] );
				unset( $values[ 'rules_data_' . $row[ 'data_column_name' ] ] );
			}
		}
		
		parent::saveForm( $values );
	}
	
}