<?php
/**
 * Pagar.me API
 *
 * @package WooCommerce_Pagarme/API
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}
 
class WC_Pagarme_Split_checkout { 

	/**
	 * Constructor.
	 *
	 * @param WC_Pagarme_Split_checkout
	 */
	public function __construct( ) {
		add_filter( 'woocommerce_checkout_cart_item_quantity', array( $this, 'get_author_price_on_checkout' ) );
	}

	public function get_author_price_on_checkout( $user_id ) {

		global $wp;

		//Get Cart Item
		$cart_itens = WC()->cart->get_cart();

		$i = 1;
		//Get only id authors
		foreach( $cart_itens as $item => $values ) { 
	       
	       $_product = $values['data']->post; 
	       $authors[$i] = $_product->post_author;

	       $i++;
	    }

	    //Check repeated authors
	    $authors = array_unique($authors);

	    //Get value for author
	    for ($i=1; $i <= count( $authors ) ; $i++) { 
	    	
	    	foreach( $cart_itens as $item => $values ) { 
	  
	    		$_product = $values['data']->post; 
	       		$author = $_product->post_author;
	       		$price = $values['data']->price;
	       		
	       		//verify author
	       		if( $authors[$i] == $author ){
	       			$prices[$i] = $prices[$i] + $price;
	       		}
		    }
	    }

	    //Set Rules Get Authors
	    for ($i=1; $i <= count( $authors ) ; $i++) { 

	    	$percentage = get_user_meta( $authors[$i], 'percentage', true );
	    	$amount = $this->porcentagem( $percentage, $prices[$i] );

	    	//Add Rules
			$rules[$i]['recipient_id'] = get_user_meta( $authors[$i], 'receiver_id', true );
			//Define se o recebedor dessa regra irá ser cobrado pela taxa da Pagar.me
			$rules[$i]['charge_processing_fee'] = false;
			//Risco da Transação
			$rules[$i]['liable'] = false;
			//Porcentagem que o recebedor vai receber do valor da transação. 
			//$rules[$i]['percentage'] = 85;
			//Valor que o recebedor vai receber da transação. 
			$rules[$i]['amount'] = $amount;
	    }

 		/*
 		 * RULES ADMIN
 		 */
		$rules[0]['recipient_id'] = "re_cixqs8mzn004z4m6e4q7kml2l";
		//Define se o recebedor dessa regra irá ser cobrado pela taxa da Pagar.me
		$rules[0]['charge_processing_fee'] = true;
		//Risco da Transação
		$rules[0]['liable'] = true;
		//Porcentagem que o recebedor vai receber do valor da transação. 
		//$rules[$i]['percentage'] = 85;
		//Valor que o recebedor vai receber da transação. 
		$rules[0]['amount'] = $this->porcentagem( 15, array_sum( $prices ) );

		return $rules;
	}

	public function porcentagem ( $porcentagem, $total ) {
		$value_total = round(( $porcentagem / 100 ) * $total, 2);
		
		if( strpos( $value_total, "." ) ) {

			$count = explode( ".", $value_total);
			if( strlen( $count[1] ) === 1 ){
				$value_total = $value_total . "0";
			}

		}else{
			$value_total = $value_total . "00";
		}

		$formato_pagarme = str_replace( ".", "", $value_total );
		
		return $formato_pagarme;
	}
}




