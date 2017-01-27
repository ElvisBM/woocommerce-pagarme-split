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
class WC_Pagarme_Receiver_Account{
	
	/**
	 * Constructor for the gateway.
	 */
	public function __construct() {

		// Set the API.
		$this->api = new WC_Pagarme_API( $this );

	}

	//Create Bank Account
	public function create_bank_account( $user_id ) {

		$data = array(
			'bank_code'    		=> get_user_meta( $user_id, 'bank_code', true ),
			'agencia'    		=> get_user_meta( $user_id, 'agencia' , true),
			'agencia_dv'    	=> get_user_meta( $user_id, 'agencia_dv', true ),
			'conta'    			=> get_user_meta( $user_id, 'conta' , true),
			'conta_dv'    		=> get_user_meta( $user_id, 'conta_dv' , true),
			'type'    			=> get_user_meta( $user_id, 'type', true ),
			'document_number'   => get_user_meta( $user_id, 'document_number', true ),
			'legal_name'    	=> get_user_meta( $user_id, 'legal_name', true ),
		);

		$response = $this->api->create_bank_account( $data );

		//Update User Meta Id Account Bank
		update_user_meta( $user_id, 'bank_account_id', $response->id );

		return $response = true;
	}

	//Create Receiver
	public function create_receiver( $user_id ){	

		$data = array(
			'transfer_interval'    				=> get_user_meta( $user_id, 'transfer_interval', true ),
			'transfer_day'    					=> get_user_meta( $user_id, 'transfer_day' , true),
			'transfer_enabled'    				=> get_user_meta( $user_id, 'transfer_enabled', true ),
			'automatic_anticipation_enabled'    => get_user_meta( $user_id, 'automatic_anticipation_enabled' , true),
			'anticipatable_volume_percentage'   => get_user_meta( $user_id, 'anticipatable_volume_percentage' , true),
			'bank_account_id'   				=> get_user_meta( $user_id, 'bank_account_id' , true),
		);

		$response = $this->api->create_receiver( $data );

		//Update User Meta Id Receiver
		update_user_meta( $user_id, 'receiver_id', $response->id );
	}
	

	//Updating Receiver
	public function updating_receiver( $post, $receiver_id, $user_id ) {

		//Data Bank Account
		$data = array(
			'bank_code'    		=> get_user_meta( $user_id, 'bank_code', true ),
			'agencia'    		=> get_user_meta( $user_id, 'agencia', true ),
			'agencia_dv'    	=> get_user_meta( $user_id, 'agencia_dv', true ),
			'conta'    			=> get_user_meta( $user_id, 'conta', true ),
			'conta_dv'    		=> get_user_meta( $user_id, 'conta_dv', true ),
			'type'    			=> get_user_meta( $user_id, 'type', true ),
			'document_number'   => get_user_meta( $user_id, 'document_number', true ),
			'legal_name'    	=> get_user_meta( $user_id, 'legal_name', true ),
		);

		//Data Post Bank Account
		$data_post = array(
			'bank_code'    		=> $post['bank_code'],
			'agencia'    		=> $post['agencia'],
			'agencia_dv'    	=> $post['agencia_dv'],
			'conta'    			=> $post['conta'],
			'conta_dv'    		=> $post['conta_dv'],
			'type'    			=> $post['type'],
			'document_number'   => $post['document_number'],
			'legal_name'    	=> $post['legal_name'],
		);

		//Valid Modification Account Bak
		if( $post['bank_code'] != $data['bank_code'] || $post['agencia'] != $data['agencia'] || $post['agencia_dv'] != $data['agencia_dv'] || $post['conta'] != $data['conta'] || $post['conta_dv'] != $data['conta_dv'] || $post['type'] != $data['type'] || $post['document_number'] != $data['document_number'] || $post['legal_name'] != $data['legal_name'] )
		 {
		  	$response = $this->api->create_bank_account( $data_post );

			$bank_account_id 	 = get_user_meta( $user_id, 'bank_account_id', true );
			$bank_account_id_old = get_user_meta( $user_id, 'bank_account_id_old', true );
			$bank_account_id_old = $bank_account_id . " / " . $bank_account_id_old;

			//Update User Meta Id Account Bank OLD
			update_user_meta( $user_id, 'bank_account_id_old', $bank_account_id_old );

			//Update User Meta Id Account Bank
			update_user_meta( $user_id, 'bank_account_id', $response->id );
		}

		//Data Receiver
		$data = array(
			'transfer_interval'    				=> get_user_meta( $user_id, 'transfer_interval', true ),
			'transfer_day'    					=> get_user_meta( $user_id, 'transfer_day' , true),
			'transfer_enabled'    				=> get_user_meta( $user_id, 'transfer_enabled', true ),
			'automatic_anticipation_enabled'    => get_user_meta( $user_id, 'automatic_anticipation_enabled' , true),
			'anticipatable_volume_percentage'   => get_user_meta( $user_id, 'anticipatable_volume_percentage' , true),
			'bank_account_id'   				=> get_user_meta( $user_id, 'bank_account_id', true ),
		);

		//Data Receiver Post
		$data_post = array(
			'transfer_interval'    				=> $post['transfer_interval'],
			'transfer_day'    					=> $post['transfer_day'],
			'transfer_enabled'    				=> $post['transfer_enabled'],
			'automatic_anticipation_enabled'    => $post['automatic_anticipation_enabled'],
			'anticipatable_volume_percentage'   => $post['anticipatable_volume_percentage'],
			'bank_account_id'   				=> get_user_meta( $user_id, 'bank_account_id', true ),
		);

		//Valid Modification Receiver
		if( $post['transfer_interval'] != $data['transfer_interval'] || $post['transfer_day'] != $data['transfer_day'] || $post['transfer_enabled'] != $data['transfer_enabled'] || $post['automatic_anticipation_enabled'] != $data['automatic_anticipation_enabled'] || $post['anticipatable_volume_percentage'] != $data['anticipatable_volume_percentage'])
		{
		 	$response = $this->api->updating_receiver( $receiver_id, $data_post );
		}	
	}
}
new WC_Pagarme_Receiver_Account();