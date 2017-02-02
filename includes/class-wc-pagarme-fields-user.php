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
		  	'bank_account_id'  	=> 'Id Conta Banco Pagarme',
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
		  	'bank_account_id_old'				=> 'Contas Antigas de Recebimentos',
		);

		//WcVendor 
		add_action( 'wcvendors_settings_after_paypal', array( $this, 'add_fields_wc_vendor') );//add Front user
		add_action( 'init', array( $this, 'save_fields_front') );// save user front 
		add_action( 'wcvendors_admin_after_commission_due', array( $this, 'create_fields') );//add backend admin
		add_action( 'wcvendors_update_admin_user',  array( $this, 'save_fields') );//save backend admin

		//Add Scripts Painel vendor;
		add_action( 'wp_enqueue_scripts', array( $this, 'add_scripts_painel_vendor') );
	}


	//Add Scripts Painel vendor;
	public function add_scripts_painel_vendor(){

		$current_page_id 	= get_the_ID(); 
		$dashboard_page_id 	= WCVendors_Pro::get_option( 'dashboard_page_id' ); 
		$dir = plugins_url();

		//Add Scripts Mask
		$view_dashboard	= apply_filters( 'wcv_view_dashboard', 	$current_page_id == $dashboard_page_id ? true : false );

		if ( $view_dashboard ) { 
			if ( is_user_logged_in() ) {
				
				wp_enqueue_script( 'wcvendors-pro-select-search-ajax','https://maxcdn.bootstrapcdn.com/bootstrap/3.3.4/js/bootstrap.min.js', true);
				wp_enqueue_script( 'wcvendors-pro-select-search-js', $dir . '/woocommerce-pagarme-receiver/assets/js/bootstrap-select.min.js', true);
				wp_enqueue_script( 'wcvendors-pro-mask', $dir . '/woocommerce-pagarme-receiver/assets/js/jquery.mask.min.js', true);
				wp_enqueue_script( 'wcvendors-pro-split', $dir . '/woocommerce-pagarme-receiver/assets/js/split.js', true);

				wp_register_style( 'wcvendors-pro-select-search-boostrap', 'https://maxcdn.bootstrapcdn.com/bootstrap/3.3.4/css/bootstrap.min.css');
				wp_register_style( 'wcvendors-pro-select-search-select', $dir . '/woocommerce-pagarme-receiver/assets/css/bootstrap-select.min.css' );
				wp_enqueue_style( 'wcvendors-pro-select-search-boostrap' );
				wp_enqueue_style( 'wcvendors-pro-select-search-select' );
			}
		}
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
			$theme .= '</tr>';
		}
		$theme .= '</table>';

		echo $theme;
	}

	public function add_fields_wc_vendor( ){
		
		$list_bank = '<option value="1">BANCO DO BRASIL S/A</option><option value="2">BANCO CENTRAL DO BRASIL</option><option value="3">BANCO DA AMAZONIA S.A</option><option value="4">BANCO DO NORDESTE DO BRASIL S.A</option><option value="7">BANCO NAC DESENV. ECO. SOCIAL S.A</option><option value="8">BANCO MERIDIONAL DO BRASIL</option><option value="20">BANCO DO ESTADO DE ALAGOAS S.A</option><option value="21">BANCO DO ESTADO DO ESPIRITO SANTO S.A</option><option value="22">BANCO DE CREDITO REAL DE MINAS GERAIS SA</option><option value="24">BANCO DO ESTADO DE PERNAMBUCO</option><option value="25">BANCO ALFA S/A</option><option value="26">BANCO DO ESTADO DO ACRE S.A</option><option value="27">BANCO DO ESTADO DE SANTA CATARINA S.A</option><option value="28">BANCO DO ESTADO DA BAHIA S.A</option><option value="29">BANCO DO ESTADO DO RIO DE JANEIRO S.A</option><option value="30">BANCO DO ESTADO DA PARAIBA S.A</option><option value="31">BANCO DO ESTADO DE GOIAS S.A</option><option value="32">BANCO DO ESTADO DO MATO GROSSO S.A.</option><option value="33">BANCO DO ESTADO DE SAO PAULO S.A</option><option value="34">BANCO DO ESADO DO AMAZONAS S.A</option><option value="35">BANCO DO ESTADO DO CEARA S.A</option><option value="36">BANCO DO ESTADO DO MARANHAO S.A</option><option value="37">BANCO DO ESTADO DO PARA S.A</option><option value="38">BANCO DO ESTADO DO PARANA S.A</option><option value="39">BANCO DO ESTADO DO PIAUI S.A</option><option value="41">BANCO DO ESTADO DO RIO GRANDE DO SUL S.A</option><option value="47">BANCO DO ESTADO DE SERGIPE S.A</option><option value="48">BANCO DO ESTADO DE MINAS GERAIS S.A</option><option value="59">BANCO DO ESTADO DE RONDONIA S.A</option><option value="70">BANCO DE BRASILIA S.A</option><option value="104">CAIXA ECONOMICA FEDERAL</option><option value="106">BANCO ITABANCO S.A.</option><option value="107">BANCO BBM S.A</option><option value="109">BANCO CREDIBANCO S.A</option><option value="116">BANCO B.N.L DO BRASIL S.A</option><option value="148">MULTI BANCO S.A</option><option value="151">CAIXA ECONOMICA DO ESTADO DE SAO PAULO</option><option value="153">CAIXA ECONOMICA DO ESTADO DO R.G.SUL</option><option value="165">BANCO NORCHEM S.A</option><option value="166">BANCO INTER-ATLANTICO S.A</option><option value="168">BANCO C.C.F. BRASIL S.A</option><option value="175">CONTINENTAL BANCO S.A</option><option value="184">BBA - CREDITANSTALT S.A</option><option value="199">BANCO FINANCIAL PORTUGUES</option><option value="200">BANCO FRICRISA AXELRUD S.A</option><option value="201">BANCO AUGUSTA INDUSTRIA E COMERCIAL S.A</option><option value="204">BANCO S.R.L S.A</option><option value="205">BANCO SUL AMERICA S.A</option><option value="206">BANCO MARTINELLI S.A</option><option value="208">BANCO PACTUAL S.A</option><option value="210">DEUTSCH SUDAMERIKANICHE BANK AG</option><option value="211">BANCO SISTEMA S.A</option><option value="212">BANCO MATONE S.A</option><option value="213">BANCO ARBI S.A</option><option value="214">BANCO DIBENS S.A</option><option value="215">BANCO AMERICA DO SUL S.A</option><option value="216">BANCO REGIONAL MALCON S.A</option><option value="217">BANCO AGROINVEST S.A</option><option value="218">BBS - BANCO BONSUCESSO S.A.</option><option value="219">BANCO DE CREDITO DE SAO PAULO S.A</option><option value="220">BANCO CREFISUL</option><option value="221">BANCO GRAPHUS S.A</option><option value="222">BANCO AGF BRASIL S. A.</option><option value="223">BANCO INTERUNION S.A</option><option value="224">BANCO FIBRA S.A</option><option value="225">BANCO BRASCAN S.A</option><option value="228">BANCO ICATU S.A</option><option value="229">BANCO CRUZEIRO S.A</option><option value="230">BANCO BANDEIRANTES S.A</option><option value="231">BANCO BOAVISTA S.A</option><option value="232">BANCO INTERPART S.A</option><option value="233">BANCO MAPPIN S.A</option><option value="234">BANCO LAVRA S.A.</option><option value="235">BANCO LIBERAL S.A</option><option value="236">BANCO CAMBIAL S.A</option><option value="237">BANCO BRADESCO S.A</option><option value="239">BANCO BANCRED S.A</option><option value="240">BANCO DE CREDITO REAL DE MINAS GERAIS S.</option><option value="241">BANCO CLASSICO S.A</option><option value="242">BANCO EUROINVEST S.A</option><option value="243">BANCO STOCK S.A</option><option value="244">BANCO CIDADE S.A</option><option value="245">BANCO EMPRESARIAL S.A</option><option value="246">BANCO ABC ROMA S.A</option><option value="247">BANCO OMEGA S.A</option><option value="249">BANCO INVESTCRED S.A</option><option value="250">BANCO SCHAHIN CURY S.A</option><option value="251">BANCO SAO JORGE S.A.</option><option value="252">BANCO FININVEST S.A</option><option value="254">BANCO PARANA BANCO S.A</option><option value="255">MILBANCO S.A.</option><option value="256">BANCO GULVINVEST S.A</option><option value="258">BANCO INDUSCRED S.A</option><option value="261">BANCO VARIG S.A</option><option value="262">BANCO BOREAL S.A</option><option value="263">BANCO CACIQUE</option><option value="264">BANCO PERFORMANCE S.A</option><option value="265">BANCO FATOR S.A</option><option value="266">BANCO CEDULA S.A</option><option value="267">BANCO BBM-COM.C.IMOB.CFI S.A.</option><option value="275">BANCO REAL S.A</option><option value="277">BANCO PLANIBANC S.A</option><option value="282">BANCO BRASILEIRO COMERCIAL</option><option value="291">BANCO DE CREDITO NACIONAL S.A</option><option value="294">BCR - BANCO DE CREDITO REAL S.A</option><option value="295">BANCO CREDIPLAN S.A</option><option value="300">BANCO DE LA NACION ARGENTINA S.A</option><option value="302">BANCO DO PROGRESSO S.A</option><option value="303">BANCO HNF S.A.</option><option value="304">BANCO PONTUAL S.A</option><option value="308">BANCO COMERCIAL BANCESA S.A.</option><option value="318">BANCO B.M.G. S.A</option><option value="320">BANCO INDUSTRIAL E COMERCIAL</option><option value="341">BANCO ITAU S.A</option><option value="346">BANCO FRANCES E BRASILEIRO S.A</option><option value="347">BANCO SUDAMERIS BRASIL S.A</option><option value="351">BANCO BOZANO SIMONSEN S.A</option><option value="353">BANCO GERAL DO COMERCIO S.A</option><option value="356">ABN AMRO S.A</option><option value="366">BANCO SOGERAL S.A</option><option value="369">PONTUAL</option><option value="370">BEAL - BANCO EUROPEU PARA AMERICA LATINA</option><option value="372">BANCO ITAMARATI S.A</option><option value="375">BANCO FENICIA S.A</option><option value="376">CHASE MANHATTAN BANK S.A</option><option value="388">BANCO MERCANTIL DE DESCONTOS S/A</option><option value="389">BANCO MERCANTIL DO BRASIL S.A</option><option value="392">BANCO MERCANTIL DE SAO PAULO S.A</option><option value="394">BANCO B.M.C. S.A</option><option value="399">BANCO BAMERINDUS DO BRASIL S.A</option><option value="409">UNIBANCO - UNIAO DOS BANCOS BRASILEIROS</option><option value="412">BANCO NACIONAL DA BAHIA S.A</option><option value="415">BANCO NACIONAL S.A</option><option value="420">BANCO NACIONAL DO NORTE S.A</option><option value="422">BANCO SAFRA S.A</option><option value="424">BANCO NOROESTE S.A</option><option value="434">BANCO FORTALEZA S.A</option><option value="453">BANCO RURAL S.A</option><option value="456">BANCO TOKIO S.A</option><option value="464">BANCO SUMITOMO BRASILEIRO S.A</option><option value="466">BANCO MITSUBISHI BRASILEIRO S.A</option><option value="472">LLOYDS BANK PLC</option><option value="473">BANCO FINANCIAL PORTUGUES S.A</option><option value="477">CITIBANK N.A</option><option value="479">BANCO DE BOSTON S.A</option><option value="480">BANCO PORTUGUES DO ATLANTICO-BRASIL S.A</option><option value="483">BANCO AGRIMISA S.A.</option><option value="487">DEUTSCHE BANK S.A - BANCO ALEMAO</option><option value="488">BANCO J. P. MORGAN S.A</option><option value="489">BANESTO BANCO URUGAUAY S.A</option><option value="492">INTERNATIONALE NEDERLANDEN BANK N.V.</option><option value="493">BANCO UNION S.A.C.A</option><option value="494">BANCO LA REP. ORIENTAL DEL URUGUAY</option><option value="495">BANCO LA PROVINCIA DE BUENOS AIRES</option><option value="496">BANCO EXTERIOR DE ESPANA S.A</option><option value="498">CENTRO HISPANO BANCO</option><option value="499">BANCO IOCHPE S.A</option><option value="501">BANCO BRASILEIRO IRAQUIANO S.A.</option><option value="502">BANCO SANTANDER S.A</option><option value="504">BANCO MULTIPLIC S.A</option><option value="505">BANCO GARANTIA S.A</option><option value="600">BANCO LUSO BRASILEIRO S.A</option><option value="601">BFC BANCO S.A.</option><option value="602">BANCO PATENTE S.A</option><option value="604">BANCO INDUSTRIAL DO BRASIL S.A</option><option value="607">BANCO SANTOS NEVES S.A</option><option value="608">BANCO OPEN S.A</option><option value="610">BANCO V.R. S.A</option><option value="611">BANCO PAULISTA S.A</option><option value="612">BANCO GUANABARA S.A</option><option value="613">BANCO PECUNIA S.A</option><option value="616">BANCO INTERPACIFICO S.A</option><option value="617">BANCO INVESTOR S.A.</option><option value="618">BANCO TENDENCIA S.A</option><option value="621">BANCO APLICAP S.A.</option><option value="622">BANCO DRACMA S.A</option><option value="623">BANCO PANAMERICANO S.A</option><option value="624">BANCO GENERAL MOTORS S.A</option><option value="625">BANCO ARAUCARIA S.A</option><option value="626">BANCO FICSA S.A</option><option value="627">BANCO DESTAK S.A</option><option value="628">BANCO CRITERIUM S.A</option><option value="629">BANCORP BANCO COML. E. DE INVESTMENTO</option><option value="630">BANCO INTERCAP S.A</option><option value="633">BANCO REDIMENTO S.A</option><option value="634">BANCO TRIANGULO S.A</option><option value="635">BANCO DO ESTADO DO AMAPA S.A</option><option value="637">BANCO SOFISA S.A</option><option value="638">BANCO PROSPER S.A</option><option value="639">BIG S.A. - BANCO IRMAOS GUIMARAES</option><option value="640">BANCO DE CREDITO METROPOLITANO S.A</option><option value="641">BANCO EXCEL ECONOMICO S/A</option><option value="643">BANCO SEGMENTO S.A</option><option value="645">BANCO DO ESTADO DE RORAIMA S.A</option><option value="647">BANCO MARKA S.A</option><option value="648">BANCO ATLANTIS S.A</option><option value="649">BANCO DIMENSAO S.A</option><option value="650">BANCO PEBB S.A</option><option value="652">BANCO FRANCES E BRASILEIRO SA</option><option value="653">BANCO INDUSVAL S.A</option><option value="654">BANCO A. J. RENNER S.A</option><option value="655">BANCO VOTORANTIM S.A.</option><option value="656">BANCO MATRIX S.A</option><option value="657">BANCO TECNICORP S.A</option><option value="658">BANCO PORTO REAL S.A</option><option value="702">BANCO SANTOS S.A</option><option value="705">BANCO INVESTCORP S.A.</option><option value="707">BANCO DAYCOVAL S.A</option><option value="711">BANCO VETOR S.A.</option><option value="713">BANCO CINDAM S.A</option><option value="715">BANCO VEGA S.A</option><option value="718">BANCO OPERADOR S.A</option><option value="719">BANCO PRIMUS S.A</option><option value="720">BANCO MAXINVEST S.A</option><option value="721">BANCO CREDIBEL S.A</option><option value="722">BANCO INTERIOR DE SAO PAULO S.A</option><option value="724">BANCO PORTO SEGURO S.A</option><option value="725">BANCO FINABANCO S.A</option><option value="726">BANCO UNIVERSAL S.A</option><option value="728">BANCO FITAL S.A</option><option value="729">BANCO FONTE S.A</option><option value="730">BANCO COMERCIAL PARAGUAYO S.A</option><option value="731">BANCO GNPP S.A.</option><option value="732">BANCO PREMIER S.A.</option><option value="733">BANCO NACOES S.A.</option><option value="734">BANCO GERDAU S.A</option><option value="735">BACO POTENCIAL</option><option value="736">BANCO UNITED S.A</option><option value="737">THECA</option><option value="738">MARADA</option><option value="739">BGN</option><option value="740">BCN BARCLAYS</option><option value="741">BRP</option><option value="742">EQUATORIAL</option><option value="743">BANCO EMBLEMA S.A</option><option value="744">THE FIRST NATIONAL BANK OF BOSTON</option><option value="745">CITIBAN N.A.</option><option value="746">MODAL S\A</option><option value="747">RAIBOBANK DO BRASIL</option><option value="748">SICREDI</option><option value="749">BRMSANTIL SA</option><option value="750">BANCO REPUBLIC NATIONAL OF NEW YORK (BRA</option><option value="751">DRESDNER BANK LATEINAMERIKA-BRASIL S/A</option><option value="752">BANCO BANQUE NATIONALE DE PARIS BRASIL S</option><option value="753">BANCO COMERCIAL URUGUAI S.A.</option><option value="755">BANCO MERRILL LYNCH S.A</option><option value="756">BANCO COOPERATIVO DO BRASIL S.A.</option><option value="757">BANCO KEB DO BRASIL S.A.</option>';

		//Bank Fields
		$theme  = '<h3>Dados Bancários</h3>';
		$theme .= '<p>Informe os dados da conta que gostaria de receber de seus clientes.</p>';
		$theme .= '<p>Utilizamos o sistema de pagamentos do PagarMe.</p>';
		$theme .= '<span>Todos os campos com <span class="red">*</span> são obrigatorios.</span>';
		$theme .= '<div id="conta_banco_wcvendor">';
		$theme .= '<div class="field legal_name">';
		$theme .= '<label for="legal_name">Nome Completo <span class="red">*</span></label>';
		$theme .= '<input type="text" name="legal_name" id="legal_name" value="'.get_user_meta( get_current_user_id(), 'legal_name', true ).'" class="regular-text" />';
		$theme .= '</div>';

		$theme .= '<div class="field document_number">';
		$theme .= '<label for="document_number">CPF ou CNPJ <span class="red">*</span></label>';
		$theme .= '<input type="text" name="document_number" id="document_number" value="'.get_user_meta( get_current_user_id(), 'document_number', true ).'" class="regular-text" />';
		$theme .= '</div>';

		$theme .= '<div class="field bank_code">';
		$theme .= '<label for="bank_code">Banco <span class="red">*</span></label>';

		$bank_code = get_user_meta( get_current_user_id(), 'bank_code', true );
		if( !empty( $bank_code ) ){
			$bank_code_str = '"'.$bank_code.'"';
			$bank_code_selected = '"'.$bank_code.'" selected';
			$list_bank =  str_replace( $bank_code_str, $bank_code_selected, $list_bank );
		}
		$theme .='<select name="bank_code" id="bank_code" class="selectpicker" data-live-search="true"><option value="">Escolha o banco</option>';
		$theme .= $list_bank;
		$theme .='</select>';
		$theme .= '</div>';

		$theme .= '<div class="field type">';
		$theme .= '<label for="type">Conta <span class="red">*</span></label>';
		$type_conta = get_user_meta( get_current_user_id(), 'type', true );
		$list_conta = '<option value="conta_corrente">Conta Corrente</option><option value="conta_poupanca">Conta Poupança</option><option value="conta_corrente_conjunta">Conta Corrente Conjunta</option><option value="conta_poupanca_conjunta">Conta Poupança Conjunta</option>';
		if( !empty( $type_conta ) ){
			$type_str = '"'.$type_conta.'"';
			$type_selected = '"'.$type_conta.'" selected';
			$list_conta =  str_replace( $type_str, $type_selected, $list_conta );
		}
		$theme .='<select name="type" id="type"><option value="">Escolha o tipo de conta <span class="red">*</span></option>';
		$theme .= $list_conta;
		$theme .='</select>';
		$theme .= '</div>';

		$theme .= '<div class="field agencia wcv-cols-group wcv-horizontal-gutters">';
		$theme .= '<div class="all-50 tiny-100" >';
		$theme .= '<label for="agencia">Agência <span class="red">*</span></label>';
		$theme .= '<input type="text" name="agencia" id="agencia" value="'.get_user_meta( get_current_user_id(), 'agencia', true ).'" class="regular-text" />';
		$theme .= '</div>';
		$theme .= '<div class="all-50 tiny-100" >';
		$theme .= '<label for="agencia_dv">Digito da Agência <span class="red">*</span></label>';
		$theme .= '<input type="text" name="agencia_dv" id="agencia_dv" value="'.get_user_meta( get_current_user_id(), 'agencia_dv', true ).'" class="regular-text" />';
		$theme .= '</div>';
		$theme .= '</div>';

		$theme .= '<div class="field conta wcv-cols-group wcv-horizontal-gutters">';
		$theme .= '<div class="all-50 tiny-100" >';
		$theme .= '<label for="conta">Conta <span class="red">*</span></label>';
		$theme .= '<input type="text" name="conta" id="conta" value="'.get_user_meta( get_current_user_id(), 'conta', true ).'" class="regular-text" />';
		$theme .= '</div>';
		$theme .= '<div class="all-50 tiny-100" >';
		$theme .= '<label for="conta_dv">Digito da Conta <span class="red">*</span></label>';
		$theme .= '<input type="text" name="conta_dv" id="conta_dv" value="'.get_user_meta( get_current_user_id(), 'conta_dv', true ).'" class="regular-text" />';
		$theme .= '</div>';
		$theme .= '</div>';

		$theme .= '<div class="field bank_account_id" style="display:none;">';
		$theme .= '<input type="text" name="bank_account_id" id="bank_account_id" value="'.get_user_meta( get_current_user_id(), 'bank_account_id', true ).'" class="regular-text" />';
		$theme .= '</div>';

		$theme .= '</div>';


		//Receiver Fields
		
		$theme .= '<div id="recebedor_wcvendor">';
		$theme .= '<h3>Preferências para recebimento</h3>';
		$theme .= '<p>Escolha quando gostaria de receber</p><br >';

		$transfer_interval = get_user_meta( get_current_user_id(), 'transfer_interval', true );
		$selectedweekly = "";
		$selectedmonthly = "";
		if ( $transfer_interval === "weekly" ) {
			$selectedweekly = "selected";
			$selectedmonthly = "";
			$transfer_day_weekly = "true";
		}
		if ( $transfer_interval === "monthly" ) {
			$selectedweekly = "";
			$selectedmonthly = "selected";
			$transfer_day_monthly = "true";
		}
		$theme .= '<div class="field agencia wcv-cols-group wcv-horizontal-gutters">';
		$theme .= '<div class="all-50 tiny-100" >';
	  	$theme .= '<div class="field transfer_interval">';
		$theme .= '<label for="transfer_interval">Frequência <span class="red">*</span></label>';
		$theme .='<select name="transfer_interval" id="transfer_interval"><option value="">Escolha uma opção</option>';
		$theme .='<option value="weekly" '.$selectedweekly .'>Semanal</option><option value="monthly" '.$selectedmonthly.'>Mensal</option>';
		$theme .='</select>';
		$theme .='</div>';
		$theme .='</div>';

		$transfer_day = get_user_meta( get_current_user_id(), 'transfer_day', true );
		$selectedweekly = "";
		$selectedmonthly = "";
		if ( $transfer_day_weekly == "true" ) {
			$day_weekly = "inline_block";
			$day_monthly = 'style="display:none;"';
			$day_monthly_select = "disabled";
			switch ( $transfer_day ){
			  case 1:
			    $day1 = 'selected';
			    break;
			  case 2:
			    $day2 = 'selected';
			    break;
			  case 3:
			    $day3 = 'selected';
			    break;
			  case 4:
			    $day4 = 'selected';
			    break;
			  case 5:
			    $day5 = 'selected';
			    break;
			  default:
			    $day1 = '';
			    $day2 = ''; 
			    $day3 = ''; 
			    $day4 = ''; 
			    $day5 = '';  
			}
		}
		if ( $transfer_day_monthly == "true" ) {
			$day_monthly = "inline_block";
			$day_weekly = 'style="display:none;"';
			$day_weekly_select = "disabled";
		}
		$theme .= '<div class="all-50 tiny-100" >';
		$theme .= '<div class="field transfer_day weekly" '.$day_weekly.'>';
		$theme .= '<label for="transfer_day">Dia da Semana <span class="red">*</span></label>';
		$theme .='<select name="transfer_day" id="transfer_day" class="disable_day" '.$day_weekly_select.'><option value="">Escolha uma opção</option>';
		$theme .='<option value="1" '.$day1.'>Segunda</option><option value="2" '.$day2.'>Terça</option><option value="3" '.$day3.'>Quarta</option><option value="4" '.$day4.'>Quinta</option><option value="5" '.$day5.'>Sexta</option>';
		$theme .='</select>';
		$theme .= '</div>';
		$theme .= '</div>';

		$theme .= '<div class="all-50 tiny-100" >';
		$theme .= '<div class="field transfer_day monthly" '.$day_monthly.'>';
		$theme .= '<label for="transfer_interval">Dia do mês(1 a 31) <span class="red">*</span></label>';
		$theme .='<input type="number" name="transfer_day" id="transfer_day" class="disable_day" value="'.$transfer_day.'" min="1" max="31" '.$day_monthly_select.'>';
		$theme .= '</div>';
		$theme .= '</div>';
		$theme .= '</div>';

		$theme .= '<div class="field transfer_enabled" style="display:none;">';
		$theme .= '<input type="text" name="transfer_enabled" id="transfer_enabled" value="true" class="regular-text" />';
		$theme .= '</div>';

		$theme .= '<div class="field automatic_anticipation_enabled" style="display:none;">';
		$theme .= '<input type="text" name="automatic_anticipation_enabled" id="automatic_anticipation_enabled" value="true" class="regular-text" />';
		$theme .= '</div>';

		$theme .= '<div class="field anticipatable_volume_percentage" style="display:none;">';
		$theme .= '<input type="text" name="anticipatable_volume_percentage" id="anticipatable_volume_percentage" value="100" class="regular-text" />';
		$theme .= '</div>';

		$theme .= '<div class="field receiver_id" style="display:none;">';
		$theme .= '<input type="text" name="receiver_id" id="receiver_id" value="'.get_user_meta( get_current_user_id(), 'receiver_id', true ).'" class="regular-text" />';
		$theme .= '</div>';

		$theme .= '<div class="field percentage" style="display:none;">';
		$theme .= '<input type="text" name="percentage" id="percentage" value="'.get_user_meta( get_current_user_id(), 'percentage', true ).'" class="regular-text" />';
		$theme .= '</div>';

		$theme .= '<div class="field bank_account_id_old" style="display:none;">';
		$theme .= '<input type="text" name="bank_account_id_old" id="bank_account_id_old" value="'.get_user_meta( get_current_user_id(), 'bank_account_id_old', true ).'" class="regular-text" />';
		$theme .= '</div>';

		$theme .= '</div>';

		echo $theme;
	}
	

	public function save_fields_front ( ){

		$user_id = get_current_user_id();
		$change = false;
		$bank_account_id 	= get_user_meta( $user_id, 'bank_account_id', true );
		$receiver_id 	 	= get_user_meta( $user_id, 'receiver_id', true );
		$bank_code 			= get_user_meta( $user_id, 'bank_code', true );

		$post = array(
				'bank_code'    						=> $_POST['bank_code'],
				'agencia'    						=> $_POST['agencia'],
				'agencia_dv'    					=> $_POST['agencia_dv'],
				'conta'    							=> $_POST['conta'],
				'conta_dv'    						=> $_POST['conta_dv'],
				'type'    							=> $_POST['type'],
				'document_number'   				=> $_POST['document_number'],
				'legal_name'    					=> $_POST['legal_name'],
				'transfer_interval'    				=> $_POST['transfer_interval'],
				'transfer_day'    					=> $_POST['transfer_day'],
				'transfer_enabled'    				=> $_POST['transfer_enabled'],
				'automatic_anticipation_enabled'    => $_POST['automatic_anticipation_enabled'],
				'anticipatable_volume_percentage'   => $_POST['anticipatable_volume_percentage'],
				'bank_account_id'   				=> $_POST['bank_account_id'],
				'receiver_id'   					=> $_POST['receiver_id'],
			);
		
		if ( !empty( $post['bank_code'] ) ) {	

			//Valid change Bank
			foreach( $this->bank_fields as $field => $label ){
				
				$field_change = get_user_meta( $user_id, $field, true );

				if ( isset( $post[ $field ] )!= $field_change ) {
					$change =  true;
				}
			}

			//Valid change Receiver
			foreach( $this->receiver_fields as $field => $label ){
				
				$field_change = get_user_meta( $user_id, $field, true );

				if ( isset( $post[ $field ] ) != $field_change ) {
					$change =  true;
				}
			}

			if( $change ){

				//update receiver
				if( !empty( $bank_account_id ) && !empty( $receiver_id ) ){
					$this->api_receiver_account->updating_receiver( $post, $receiver_id, $user_id );
				}

				//Bank Save Fields WP
				foreach( $this->bank_fields as $field => $label ){
					if ( isset(  $post[ $field ] ) ) {
						if( empty( $bank_account_id ) ){
							update_user_meta( $user_id, $field, $_POST[ $field ] );
						}else{
							if( $field != "bank_account_id" ){
								update_user_meta( $user_id, $field, $_POST[ $field ] );
							}
						}
					}
				}

				//Receiver Save Fields WP
				foreach( $this->receiver_fields as $field => $label ){
					if ( isset(  $post[ $field ] ) ) {
						update_user_meta( $user_id, $field, $_POST[ $field ] );
					}
				}

				//Create receiver
				if( empty( $bank_code ) ){
					if( $this->api_receiver_account->create_bank_account( $user_id ) ) 
						$this->api_receiver_account->create_receiver( $user_id );
				}
			}
		}
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
