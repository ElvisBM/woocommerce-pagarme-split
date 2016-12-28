<?php
/**
 * Pagar.me API
 *
 * @package WooCommerce_Pagarme/API
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * WC_Pagarme_API class.
 */
class WC_Pagarme_Fields_User{

	private $bank_fields;
	private $receiver_fields;

	public function __construct() { 

		//Bank Fields
		$this->bank_fields = array( 
		  	'bank_code'  		=> 'Código Banco',
		  	'agencia'    		=> 'Agência',
		  	'agencia_dv' 		=> 'Digito Agência',
		  	'conta'      		=> 'Conta',
		  	'conta_dv'   		=> 'Digito conta',
		  	'type'       		=> 'Tipo de Conta',
		  	'document_number'   => 'CPF ou CNPJ',
		  	'legal_name'		=> 'Nome Completo ou Razão Social',
		  	'bank_account_id'   => 'Id Conta Banco Pagarme',
		);

		//Receiver Fields
		$this->receiver_fields = array( 
		  	'transfer_interval'  				=> 'Frequência na qual o recebedor irá ser pago.',
		  	'transfer_day'    	 				=> 'Dia no qual o recebedor vai ser pago.',
		  	'transfer_enabled' 	 				=> 'Pode receber automaticamente',
		  	'automatic_anticipation_enabled'    => 'Percentual de antecipação',
			'anticipatable_volume_percentage'   => 'Percentual de antecipação',
		  	'receiver_id'    					=> 'Id do Recebedor',
		);

		add_action( 'show_user_profile', array( $this, 'create_fields' ) );
		add_action( 'edit_user_profile', array( $this, 'create_fields' ) );
		add_action( 'personal_options_update',  array( $this, 'save_fields' ) );
		add_action( 'edit_user_profile_update', array( $this, 'save_fields' ) );
		
	}

	public function create_fields( $user ){
		
		//Bank Fields
		$theme  = '<h3>Bank Account</h3>';
		$theme .= '<table class="form-table">';
		foreach( $this->bank_fields as $field => $label ){
			$theme .= '<tr>';
			$theme .= '<th><label for="'.$field.'">'.$label.'</label></th>';
			$theme .= '<td>';
			$theme .= '<input type="text" name="'.$field.'" id="'.$field.'" value="'.esc_attr( get_the_author_meta( $field, $user->ID ) ).'" class="regular-text" /><br />';
			$theme .='<span class="description">Por favor preencha '.$label.'</span>
				</td>';
			$theme .= '</tr>';
		}
		$theme .= '</table>';


		//Receiver Fields
		$theme .= '<h3>Receiver Infos</h3>';
		$theme .= '<table class="form-table">';
		foreach( $this->receiver_fields as $field => $label ){
			$theme .= '<tr>';
			$theme .= '<th><label for="'.$field.'">'.$label.'</label></th>';
			$theme .= '<td>';
			$theme .= '<input type="text" name="'.$field.'" id="'.$field.'" value="'.esc_attr( get_the_author_meta( $field, $user->ID ) ).'" class="regular-text" /><br />';
			$theme .='<span class="description">Por favor preencha '.$label.'</span>
				</td>';
			$theme .= '</tr>';
		}
		$theme .= '</table>';

		echo $theme;
	}

	public function save_fields ( $user_id ){

		if ( !current_user_can( 'edit_user', $user_id ) )
		return false;

		//Bank Fields
		foreach( $this->bank_fields as $field => $label ){
			update_usermeta( $user_id, $field, $_POST[ $field ] );
		}

		//Receiver Fields
		foreach( $this->receiver_fields as $field => $label ){
			update_usermeta( $user_id, $field, $_POST[ $field ] );
		}
	}

}

new WC_Pagarme_Fields_User();
