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


		// Set the API.
		$this->api_receiver_account = new WC_Pagarme_Receiver_Account( $this );

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
		  	'bank_account_id'   	  => 'Id Conta Banco Pagarme',
		);

		//Receiver Fields
		$this->receiver_fields = array( 
		  	'transfer_interval'  				=> 'Frequência na qual o recebedor irá ser pago.',
		  	'transfer_day'    	 				=> 'Dia no qual o recebedor vai ser pago.',
		  	'transfer_enabled' 	 				=> 'Pode receber automaticamente',
		  	'automatic_anticipation_enabled'    => 'Percentual de antecipação',
			'anticipatable_volume_percentage'   => 'Percentual de antecipação',
		  	'receiver_id'    					=> 'Id do Recebedor',
		  	'percentage'   						=> 'Porcetagem de recebimento ex:85',
		);
		
		//WcVendor 
		add_action( 'wcvendors_settings_after_paypal', array( $this, 'add_fields_wc_vendor') );//add Front user
		add_action( 'init', array( $this, 'save_fields_front' ) );// save user front 
		add_action( 'wcvendors_admin_after_commission_due', array( $this, 'create_fields') );//add backend admin
		add_action( 'wcvendors_update_admin_user',  array( $this, 'save_fields') );//save backend admin
	}

	public function create_fields( $user ){
		
		//Bank Fields
		$theme  = '<h3>Bank Account</h3>';
		$theme .= '<table class="form-table">';
		foreach( $this->bank_fields as $field => $label ){

			$theme .= '<tr>';
			$theme .= '<th><label for="'.$field.'">'.$label.'</label></th>';
			$theme .= '<td>';
			if( $field == "percentage"){
				$value = esc_attr( get_the_author_meta( $field, $user->ID ) );
				if( empty( $value ) ){ $value = 85; };
				$theme .= '<input type="text" name="'.$field.'" id="'.$field.'" value="'.$value.'" class="regular-text" /><br />';
			}else{
				$theme .= '<input type="text" name="'.$field.'" id="'.$field.'" value="'.esc_attr( get_the_author_meta( $field, $user->ID ) ).'" class="regular-text" /><br />';
			}
			
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

	public function add_fields_wc_vendor( ){
		
		//Bank Fields
		$theme  = '<h3>Conta para Recebimento</h3>';
		$theme  = '<p>Informe os dados da conta que gostaria de receber de seus clientes, as transferências serão feitas pelo gateway PagarMe.</p>';
		$theme .= '<div id="conta_banco_wcvendor">';
		foreach( $this->bank_fields as $field => $label ){
			$theme .= '<div class="field '.$field.'">';
			$theme .= '<label for="'.$field.'">'.$label.'</label>';
			$theme .= ' <span class="description">- Por favor preencha '.$label.'</span>';
			$theme .= '<input type="text" name="'.$field.'" id="'.$field.'" value="'.get_user_meta( get_current_user_id(), $field, true ).'" class="regular-text" /><br />';
			$theme .= '</div>';
		}
		$theme .= '</div>';


		//Receiver Fields
		$theme .= '<h3>Infos de Recebedor</h3>';
		$theme .= '<div id="recebedor_wcvendor">';
		foreach( $this->receiver_fields as $field => $label ){
			$theme .= '<div class="field '.$field.'">';
			$theme .= '<label for="'.$field.'">'.$label.'</label>';
			$theme .= ' <span class="description">- Por favor preencha '.$label.'</span>';
			$theme .= '<input type="text" name="'.$field.'" id="'.$field.'" value="'.get_user_meta( get_current_user_id(), $field, true ).'" class="regular-text" /><br />';
			$theme .= '</div>';
		}
		$theme .= '</div>';

		echo $theme;
	}
	

	public function save_fields_front ( $user_id ){

		global $woocommerce;

		$user_id = get_current_user_id();

		//Bank Fields
		foreach( $this->bank_fields as $field => $label ){
			if ( isset(  $_POST[ $field ] ) ) {
				update_user_meta( $user_id, $field, $_POST[ $field ] );
			}
		}

		//Receiver Fields
		foreach( $this->receiver_fields as $field => $label ){
			if ( isset(  $_POST[ $field ] ) ) {
				update_user_meta( $user_id, $field, $_POST[ $field ] );
			}
		}

		do_action( 'wcvendors_shop_settings_saved', $user_id );

		//Create User
		$this->api_receiver_account->receiver_account( $user_id );
	}


	public function save_fields ( $user_id ){

		//Bank Fields
		foreach( $this->bank_fields as $field => $label ){
			if ( isset(  $_POST[ $field ] ) ) {
				update_user_meta( $user_id, $field, $_POST[ $field ] );
			}
		}

		//Receiver Fields
		foreach( $this->receiver_fields as $field => $label ){
			if ( isset(  $_POST[ $field ] ) ) {
				update_user_meta( $user_id, $field, $_POST[ $field ] );
			}
		}

	}

}

new WC_Pagarme_Fields_User();
