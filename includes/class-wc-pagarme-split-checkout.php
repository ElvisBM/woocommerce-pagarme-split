<?php

// add_action( 'woocommerce_checkout_process', 'gastronomy_wc_minimum_order_amount' );
// add_action( 'woocommerce_before_cart' , 'gastronomy_wc_minimum_order_amount' );

// apply_filters('woocommerce_shipping_fields', addfield() );

// function addfield(){
// 	echo "<pre>";
// 		//print_r( "teste abcd" );
// 	echo "</pre>";
	

// }

function add_meta_on_checkout( $quantity , $cart_item , $cart_item_key  ) {



	// 	foreach( WC()->cart->get_cart() as $cart_item ) {

	// 	  var_dump( $cart_item );
	// 	  var_dump( WC()->cart->get_item_data( $cart_item ) );
	// 	}
	// // 

		// //Id Author item 
		// $user_id_item = $cart_item['data']->post->post_author;

		// $count = count($cart_item['data']);

		// //Valor a ser considerado
		// //$cart_item['line_total']

		// //Get Receiver Id
		// $receiver_id = get_user_meta( $user_id_item, 'receiver_id', true );

		$cart_itens = WC()->cart->get_cart();
		$count = count($cart_itens);

		 foreach( $cart_itens as $item => $values ) { 
            $_product = $values['data']->post; 

            echo $_product->post_author . "   ";



            // echo "<b>".$_product->post_author.'</b>  <br> Quantity: '.$values['quantity'].'<br>'; 
            // $price = get_post_meta($values['product_id'] , '_price', true);
            // echo "  Price: ".$price."<br>";
        } 


}
add_filter( 'woocommerce_checkout_cart_item_quantity', 'add_meta_on_checkout', 10, 3 );


function get_cart_item_author(){

	//Get Cart Item
	$cart_itens = WC()->cart->get_cart();

	//Array Authors
	$authors;
	$i = 1;

	//Get only id authors
	foreach( $cart_itens as $item => $values ) { 
       
       $_product = $values['data']->post; 

       $authors[$i] = $_product->post_author ;

       $i++;
    }

    //Check repeated authors
    $authors = array_unique($authors);

    /* Exemplo
     * Total: 100
     * Recebedor 1: Amount 100, Percentual 10
     * Recebedor 2: Amount 50,  Percentual 90
     * Recebedor 3: Amount 50,  Percentual 90	 
     */


    //Set Rules Get Authors
    for ($i=0; $i < count( $authors ) ; $i++) { 
    	//Add Rules
		$rules[$i]['recipient_id'] = get_user_meta( $user_id_item, 'receiver_id', true );
		//Define se o recebedor dessa regra irá ser cobrado pela taxa da Pagar.me
		$rules[$i]['charge_processing_fee'] = false;
		//Risco da Transação
		$rules[$i]['liable'] = false;
		//Porcentagem que o recebedor vai receber do valor da transação. 
		$rules[$i]['percentage'] = 83;
		//Valor que o recebedor vai receber da transação. 
		$rules[$i]['amount'] = null;
    }



}


