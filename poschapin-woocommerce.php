<?php
/*
Plugin Name: POSchapin to WooCommerce
Plugin URI: https://github.com/hhklik/poschapin-woocommerce
Description: POSchapin payment collection for WooCommerce
Version: 0.0.8
Author: POSchapin
Author URI: https://github.com/hhklik/poschapin-woocommerce
Text Domain: poschapinwoo
Generated By: http://ensuredomains.com
*/

# actualizar complemento desde repositorio publico
add_action( 'init', 'github_plugin_updater_test_init' );
function github_plugin_updater_test_init() {

    include_once 'updater.php';

    define( 'WP_GITHUB_FORCE_UPDATE', true );

    if ( is_admin() ) { // note the use of is_admin() to double check that this is happening in the admin

        $config = array(
            'slug' => plugin_basename( __FILE__ ),
            'proper_folder_name' => dirname( plugin_basename( __FILE__ ) ),
            'api_url' => 'https://api.github.com/repos/hhklik/poschapin-woocommerce',
            'raw_url' => 'https://raw.githubusercontent.com/hhklik/poschapin-woocommerce/master',
            'github_url' => 'https://github.com/hhklik/poschapin-woocommerce',
            'zip_url' => 'https://github.com/hhklik/poschapin-woocommerce/archive/master.zip',
            'sslverify' => true,
            'requires' => '3.0',
            'tested' => '3.3',
            'readme' => 'README.md',
            'access_token' => '',
        );
        $data = new WP_GitHub_Updater( $config );
        
        

    }

}


// If this file is called directly, abort. //
if ( ! defined( 'WPINC' ) ) {die;} // end if

// Let's Initialize Everything
if ( file_exists( plugin_dir_path( __FILE__ ) . 'core-init.php' ) ) {
require_once( plugin_dir_path( __FILE__ ) . 'core-init.php' );
}

// Make sure WooCommerce is active
if ( ! in_array( 'woocommerce/woocommerce.php', apply_filters( 'active_plugins', get_option( 'active_plugins' ) ) ) ) {
    return;
}

function wc_POSchapin_add_to_gateways( $gateways ) {
    $gateways[] = 'WC_Gateway_POSchapin';
    return $gateways;
}
add_filter( 'woocommerce_payment_gateways', 'wc_POSchapin_add_to_gateways' );

function wc_POSchapin_gateway_plugin_links( $links ) {
    $plugin_links = array(
            '<a href="' . admin_url( 'admin.php?page=wc-settings&tab=checkout&section=POSchapin' ) . '">' . __( 'Configure', 'wc_POSchapin4WoOCommerce' ) . '</a>'
    );
    return array_merge( $plugin_links, $links );
}
add_filter( 'plugin_action_links_' . plugin_basename( __FILE__ ), 'wc_POSchapin_gateway_plugin_links' );

add_action( 'plugins_loaded', 'wc_POSchapin_gateway_init', 0 );
function wc_POSchapin_gateway_init() {

    class WC_Gateway_POSchapin extends WC_Payment_Gateway {
        public function __construct(){
            $this->id                 = 'poschapin';
			$this->method_title       = __( 'POSchapin', 'wc_POSchapin4WoOCommerce' );
            $this->method_description = __( 'Permitir pagos con cualquier tarjeta de debito y credito', 'wc_POSchapin4WoOCommerce' );
            $this->supports = array( 'products' );

            // Bool. Can be set to true if you want payment fields to show on the checkout
            // if doing a direct integration, which we are doing in this case
            $this->has_fields = true;
            // Supports the default credit card form
            //$this->supports = array( 'default_credit_card_form' );

            error_reporting(E_ALL);
            ini_set('display_errors', 1);
            
            
            // Load the settings.
            $this->init_form_fields();
            $this->init_settings();

            
            $this->enabled = $this->get_option('enabled');
            // Define user set variables
            //$this->title        = strlen($this->get_option( 'title' )) > 0 ? $this->get_option( 'title' ) : 'Tarjeta de Crédito via POSchapin';
            $this->title        = 'Tarjeta de Crédito/Débito por POSchapin';
            //$this->description  = $this->get_option( 'description' );
            //$this->instructions = $this->get_option( 'instructions', $this->description );

            // Actions
            add_action( 'woocommerce_update_options_payment_gateways_' . $this->id, array( &$this, 'process_admin_options' ) );
            add_action( 'woocommerce_thankyou_' . $this->id, array( $this, 'thankyou_page' ) );

            // Customer Emails
            //add_action( 'woocommerce_email_before_order_table', array( $this, 'email_instructions' ), 10, 3 );

            add_action( 'wp_enqueue_scripts', array( $this, 'poschapin_woocommerce_script' ) );

            if(isset($_GET['response'])){
            	global $woocommerce;
            	$order_id = $_GET['orderid'];
            	$order = new WC_Order( $order_id );
            	
            	$response = sanitize_text_field($_GET['response']);
            	$response_code = sanitize_text_field($_GET['response_code']);
            	$transactionid = sanitize_text_field($_GET['transactionid']);
            	$operationid = sanitize_text_field($_GET['operationid']);
            	$authcode = sanitize_text_field($_GET['authcode']);
            	$responsetext = sanitize_text_field($_GET['responsetext']);
            	$avsresponse = sanitize_text_field($_GET['avsresponse']);
            	$cvvresponse = sanitize_text_field($_GET['cvvresponse']);
            	$orderid_res = sanitize_text_field($_GET['orderid']);
            	$amount_res = sanitize_text_field($_GET['amount']);
            	$time_res = sanitize_text_field($_GET['time']);
            	$hash_res = sanitize_text_field($_GET['hash']);
            	date_default_timezone_set('UTC');
            	$time = time();
            	$time_diff = $time - intval($time_res);
            	$privatekey = (string) trim($this->get_option('privatekey',true));
            	if($time_diff >= 0 && $time_diff <= 60){
            	    $hash = md5($orderid_res . '|' . $amount_res . '|' . $response . '|' . $transactionid . '|' . $avsresponse . '|' . $cvvresponse . '|' . $time_res . '|' . $privatekey);
            	    
            	    if($hash_res == $hash){
            	        $localStepNum = 3;
            	        switch ($response) {
            	            case '1':
            	            case 1:
            	                switch ($response_code) {
            	                	case '100':
            	                	case 100:
            	                		//$order->add_order_note( __( 'POSchapin payment completed.', 'wc_POSchapin4WoOCommerce' ) );
            	                		// Mark order as Paid
            	                		//$order->payment_complete();
                                        $order->update_status('processing', 'POSchapin payment completed.');
            	                		// Empty the cart (Very important step)
            	                		$woocommerce->cart->empty_cart();
            	                		// Reduce stock levels
            	                		$order->reduce_order_stock();
            	                		// Redirect to thank you page
            	                		unset( $woocommerce->session->order_awaiting_payment );
            	                		#Redirect to tanks_you page
            	                		wp_redirect($this->get_return_url( $order ));
            	                		exit;
            	                		break;
            	                	case '200':
            	                	case 200:
            	                		$logo = '<img src="'.plugins_url('assets/img/icon/rechazado.svg', __FILE__).'" style="width: 75px;"/>';
            	                		wc_add_notice( $logo." Notice - ".$responsetext, 'error' );
            	                		wc_add_notice( "Codigo de transacción - <strong>".$transactionid."</strong>", 'error' );
            	                		wc_add_notice( "Codigo de operación  - " . $operationid,'error' );
            	                		$note = $responsetext . '|' . $transactionid . '|' . $operationid . '| response:' . $response . '| response_code:' . $response_code;
            	                		//$order->add_order_note( 'POSchapin Error: '. print_r($note,true)  );
                                        $order->update_status('pending', 'POSchapin Error: '. print_r($note,true)); 
            	                		break;
            	                	case '300':
            	                	case 300:
            	                		$logo = '<img src="'.plugins_url('assets/img/icon/atencion.svg', __FILE__).'" style="width: 75px;"/>';
            	                		wc_add_notice( $logo." Error - ".$responsetext, 'error' );
            	                		$note = $responsetext . '| response:' . $response . '| response_code:' . $response_code;
            	                		//$order->add_order_note( 'POSchapin Error: '. print_r($note,true)  );
                                        $order->update_status('failed', 'POSchapin Error: '. print_r($note,true)); 
            	                		break;
            	              
            	                }

            	                break;
            	            case '2':
            	            case 2:
            	                $logo = '<img src="'.plugins_url('assets/img/icon/rechazado.svg', __FILE__).'" style="width: 75px;"/>';
            	                wc_add_notice( $logo." Notice - ".$responsetext, 'error' );
            	                wc_add_notice( "Codigo de transacción - <strong>".$transactionid."</strong>", 'error' );
            	                wc_add_notice( "Codigo de operación  - " . $operationid,'error' );
            	                $note = $responsetext . '|' . $transactionid . '|' . $operationid . '| response:' . $response . '| response_code:' . $response_code;
            	                $order->add_order_note( 'POSchapin Error: '. print_r($note,true)  );
            	                break;
            	             case '3':
            	            case 3:
            	                $logo = '<img src="'.plugins_url('assets/img/icon/atencion.svg', __FILE__).'" style="width: 75px;"/>';
            	                wc_add_notice( $logo." Error - ".$responsetext, 'error' );
            	                $note = $responsetext . '| response:' . $response . '| response_code:' . $response_code;
            	                $order->add_order_note( 'POSchapin Error: '. print_r($note,true)  );
            	                break;
            	        }
            	        
            	    }else{
            	        //wp_die("Error - hash invalid ✋");
            	        wc_add_notice( "Error - hash invalid ✋".$order->id, 'error' );
            	    }
            	}else{
            	    //wp_die("Error - hash invalid time ✋");
            	    wc_add_notice( "Error - hash invalid time ✋".$order->id, 'error' );
            	}
            }
            

        }

        function poschapin_woocommerce_script(){
        	wp_register_script('poschapin-woo-js', plugins_url('assets/js/poschapin.woo.js', __FILE__), array('jquery'), '0.0.1', true );

        }

        function is_valid_for_use() {
            return true;
        }

        function is_available() {
            $return = true;
            if($this->enabled != 'yes') {
                $return = false;
            }
            return $return;
        }
        function payment_fields() {
            global $woocommerce;
            
            if ($this->description) {
                echo wpautop(wptexturize($this->description));
            }
            
            $sessionID = uniqid();
            WC()->session->set('sessionID' , $sessionID);

            $year_options = '';
            for ($i = date('Y'); $i <= (date('Y') + 10); $i++) {
                //$twoYearDigit  = substr($i, -2);
                $year_options .= '<option value="' . $i . '">' . $i . '</option>';
            }
			
            echo '	<fieldset>
                        <p><img src="'.plugins_url('assets/img/logo/logo_poschapin_woocommerce.png', __FILE__).'" width="100%;"" style="max-height: none;height: auto;margin-left: 0;"></p>

                        <p class="form-row ">
                                <label for="is_ccnum">Nombre Completo:<span class="required">*</span></label>
                                <input type="text" class="input-text" id="poschapin_ccnum" name="poschapin_ccname" required="" maxlength="60">
                        </p>
                          <div class="clear"></div>
                        <p class="form-row ">
                                <label for="is_ccnum">Número de Tarjeta:<span class="required">*</span>
                                	<img class="bankid" src="" style="width: 44px;display: none;">
                                </label>
                                <input type="text" class="input-text number" id="poschapin_card_number" name="poschapin_card_number" maxlength="19" onkeypress="return event.charCode >= 48 && event.charCode <= 57" required  >
                        </p>

                        <div class="clear"></div>

                        <p class="form-row" style="width:100%">
                               <label for="cc-expire-month">Fecha Expiraci&oacute;n:<span class="required">*</span></label>
                               <select name="poschapin_expmonth" id="poschapin_expmonth" class="input-text woocommerce-select woocommerce-cc-month cc_exp" required style="width:auto; -webkit-appearance: menulist; margin-right:15px;width: auto;
                                    -webkit-appearance: menulist;
                                    margin-right: 15px;
                                    -moz-appearance: menulist;
                                    float: left;
                                    background: none;">
                                        <option value="">Mes</option>
                                        <option value="01">01</option>
                                        <option value="02">02</option>
                                        <option value="03">03</option>
                                        <option value="04">04</option>
                                        <option value="05">05</option>
                                        <option value="06">06</option>
                                        <option value="07">07</option>
                                        <option value="08">08</option>
                                        <option value="09">09</option>
                                        <option value="10">10</option>
                                        <option value="11">11</option>
                                        <option value="12">12</option>
                               </select>
                                <select name="poschapin_expyear" id="poschapin_expyear" class="input-text woocommerce-select woocommerce-cc-year cc_exp" required style="width:auto; -webkit-appearance: menulist;width: auto;
                                -webkit-appearance: menulist;
                                margin-right: 15px;
                                -moz-appearance: menulist;
                                float:left;
                                background:none;">
                                      <option value="">A&ntilde;o</option>
                                      ' . $year_options . '
                               </select>
                        </p>
                        
                        <div class="clear"></div>
						
                        <p class="form-row ">
                                <label for="is_cvv">CVV <span class="required">*</span></label>
                                <input type="password" class="input-text ccv" id="poschapin_cvv" name="poschapin_cvv" max="9999" min="0" style="width:80px; float:left" required maxlength="4">
								<span style="padding-left: 10px;">3 &oacute; 4 d&iacute;gitos.</span>
                        </p>
						<br>
						<div class="clear"></div>

						<input type="hidden" name="expiryDate" id="expiryDate" value="">

                        <div class="clear"></div>
                 </fieldset>	'.$this->fields_script();
                 //wp_enqueue_script('poschapin-woo-js');
        }

        private function fields_script(){
        	return '
        		<script>
        		jQuery(function($){
        		   
        		   window.fpLayer = window.fpLayer || [];
        		   function fp() { fpLayer.push(arguments); }
        		   fp("config", "client", "cUXvdB6hHu");
        		   fp("config", "loaded", function (fp) {
        		     fp.send().then(function (data) {
        		       let finger = data.visitorId;
        		       
        		       var s = document.getElementById("finger");
        		       s.value = data.visitorId;
        		     })
        		   });

        		   //-------------

        		   $("#order_review").on("submit", function(s){
        		       $("#place_order").attr("disabled", "disabled");
        		       s.submit();
        		   });


        		   //-------------
        		   
        		 
        		   $(".cc_exp").on("load change", function(){
        		       var someday = new Date();
        		       $("#expiryDate").val(
        		           $("#poschapin_expmonth").val() +
        		           "/" +
        		       $("#poschapin_expyear").val()
        		       ).trigger("change");
        		   });

        		   	var ___pasteCard = false;
        			$("#poschapin_card_number").keyup(function(event){
        		      
        		       number = $(this).val();
        		   
        		       var validarCC = validateCreditcard_number(number);
        		       if(validarCC["status"] == false){
        		           selected_card = -1; 
        		       
        		       }else{
        		           selected_card = validarCC["card_code"];
        		       }
        		   
        		       if(selected_card != -1){
        		           if(selected_card == cards.americanExpress){
        		               $(".front .seccode").show().html("&#x25CF;&#x25CF;&#x25CF;&#x25CF;");;
        		               $(".ccv").prop("maxLength", 4).val("");
        		           }else{
        		               $(".front .seccode").hide().html("&#x25CF;&#x25CF;&#x25CF;");;
        		               $(".ccv").prop("maxLength", 3).val("");
        		           }
        		           //html.setAttribute("style", "--card-color: " + selected_card.colore);  
        		           $(".bankid").attr("src", selected_card.src).show();
        		       }else{
        		           //html.setAttribute("style", "--card-color: #cecece");
        		           $(".bankid").attr("src", "").hide();
        		           $(".front .seccode").hide();
        		           $(".ccv").prop("maxLength", 3);
        		           //html.setAttribute("style", "--card-color: " + "red");  
        		           //$(".bankid").attr("src", cards[6].src).show();
        		       }
        		   

        		   }).focus(function(){
        		       $(this).css("border","none");
        		       $(".card_number").css("color", "white");
        		   }).on("keydown input",function(){
        		       switch(event.type){
        		           case "keydown":

        		           break;
        		           case "input":
        		               if(event.data){
        		                   var keyCode = event.data.charCodeAt(0);
        		                   var key = String.fromCharCode(keyCode);
        		                   event.key = key;
        		                   event.keyCode;
        		               }
        		           break;
        		       }

        		       $(".card_number").text($(this).val());
        		       
        		       if((event.key >= 0 && event.key <= 9) || event.keyCode == 45){
        		           action_number = true;
        		           if(parseInt($(this).val().substring(0, 1)) == 3){
        		               $(this).prop("maxlength",17);
        		               if($(this).val().length === 4 || $(this).val().length === 11){
        		                   $(this).val($(this).val() +  "-");
        		               }
        		           }else{
        		               if($(this).val().length === 4 || $(this).val().length === 9 || $(this).val().length === 14){
        		                   $(this).prop("maxlength",19);
        		                   $(this).val($(this).val() +  "-");
        		               }
        		           }
        		          
        		       }

        		       if($(this).val().length === 0){
        		           $(this).prop("maxlength",19);
        		       }

        		       if(___pasteCard){
        		           ___pasteCard = false;
        		           var card_numero = $(this).val().replace(/[ /_\*@]/g, "-").trim();
        		           $(this).val(card_numero);

        		           switch(card_numero.length){
        		               case 16:
        		                   $(this).prop("maxlength",19);
        		                   $(this).val(card_numero.substring(0, 4) + "-" + card_numero.substring(4, 8) + "-" + card_numero.substring(8, 12) + "-" + card_numero.substring(12, 16));
        		               break;
        		               case 15:
        		                   $(this).prop("maxlength",17);
        		                   $(this).val(card_numero.substring(0, 4) + "-" + card_numero.substring(4, 10) + "-" + card_numero.substring(10, 16));
        		               break;
        		               case 14:
        		                   $(this).prop("maxlength",16);
        		                   $(this).val(card_numero.substring(0, 4) + "-" + card_numero.substring(4, 10) + "-" + card_numero.substring(10, 15));
        		               break;
        		           }
        		       }
        		   }).bind("paste", function() {
        		    ___pasteCard = true;
        		   }).bind("copy",function(){
        		       alert("No se puede copiar la informacion de tarjeta");
        		       return false;
        		   }).bind("cut",function(){
        		       alert("No se puede cortar la informacion de tarjeta");
        		       return false;
        		   });

        		   	    var cards = {
        		   	    "mastercard" : {
        		   	        nome: "mastercard",
        		   	        colore: "#0061A8",
        		   	        src: "'.plugins_url('assets/img/card/tarjeta-mastercard.svg',__FILE__).'" ,
        		   	        cvv : 3
        		   	    },
        		   	    "visa" : {
        		   	        nome: "visa",
        		   	        colore: "#E2CB38",
        		   	        src: "'.plugins_url('assets/img/card/visa-de-tarjeta-de-credito.svg',__FILE__).'",
        		   	        cvv : 3
        		   	    },
        		   	    "americanExpress" : {
        		   	        nome: "americanExpress",
        		   	        colore: "#108168",
        		   	        src: "'.plugins_url('assets/img/card/american-express.svg',__FILE__).'",
        		   	        cvv : 4
        		   	    },
        		   	    "dinersclub" : {
        		   	        nome: "dinersclub",
        		   	        colore: "#888",
        		   	        src: "'.plugins_url('assets/img/card/dinner-club.svg',__FILE__).'",
        		   	        cvv : 4
        		   	    },
        		   	    "discover" : {
        		   	        nome: "discover",
        		   	        colore: "#86B8CF",
        		   	        src: "'.plugins_url('assets/img/card/tarjeta-discover.svg',__FILE__).'",
        		   	        cvv : 3
        		   	    },
        		   	    "dankort" : {
        		   	        nome: "dankort",
        		   	        colore: "#0061A8",
        		   	        src: "",
        		   	        cvv : 3
        		   	    }
        		   	}

        		   	function validateCreditcard_number(cc_num){
    		           let credit_card_number = sanitize(cc_num);
    		           // Get the first digit
    		           let data = new Array();
    		           let firstnumber = parseInt(credit_card_number.substring(0, 1))
    		           let secondnumber = parseInt(credit_card_number.substring(1, 2))
    		           // Make sure it is the correct amount of digits. Account for dashes being present.
    		           let re = undefined;
    		           switch (firstnumber){
    		               case 3:
    		                   switch(secondnumber){
    		                       case 4:
    		                       case 7:
    		                           data["card_type"] ="American Express";
    		                           data["card_code"] = cards.americanExpress;
    		                           re = /^3(4|7)\d{2}[ \-]?\d{6}[ \-]?\d{5}/
    		                           if (!re.test(credit_card_number))
    		                           {
    		                               //return "This is not a valid American Express card number";
    		                               data["status"]=false; 
    		                               data["card_code"] = -1;
    		                               return data;
    		                           }
    		                           break;
    		                       case 6:
    		                       case 8:
    		                       case 9:
    		                       case 0:
    		                           data["card_type"] ="DinersClub";
    		                           data["card_code"] = cards.dinersclub;
    		                           re = /^3(6|8|9|0)\d{2}[ \-]?\d{6}[ \-]?\d{4}[\d{1}]?/
    		                           if (!re.test(credit_card_number))
    		                           {
    		                               //return "This is not a valid American Express card number";
    		                               data["status"]=false; 
    		                               data["card_code"] = -1;
    		                               return data;
    		                           }
    		                           break;
    		                       default:
    		                           data["status"]=false; 
    		                           data["card_code"] = -1;
    		                           return data;
    		                           break;
    		                   }
    		                   break;
    		               case 4:
    		                   data["card_type"] ="Visa";
    		                   data["card_code"] = cards.visa;
    		                   re = /^4\d{3}[ \-]?\d{4}[ \-]?\d{4}[ \-]?\d{4}/
    		                   if (!re.test(credit_card_number))
    		                   {
    		                       //return "This is not a valid Visa card number";
    		                       data["status"]=false; 
    		                       data["card_code"] = -1;
    		                       return data;
    		                   }
    		                   break;
    		               case 5:
    		                   data["card_type"] ="MasterCard";
    		                   data["card_code"] = cards.mastercard;
    		                   re = /^5\d{3}[ \-]?\d{4}[ \-]?\d{4}[ \-]?\d{4}/
    		                   if (!re.test(credit_card_number))
    		                   {
    		                       //return "This is not a valid MasterCard card number";
    		                       data["status"]=false; 
    		                       data["card_code"] = -1;
    		                       return data;
    		                   }
    		                   break;
    		               case 6:
    		                   data["card_type"] ="Discover";
    		                   data["card_code"] = cards.discover;
    		                   re = /^6011[ \-]?\d{4}[ \-]?\d{4}[ \-]?\d{4}/
    		                   if (!re.test(credit_card_number))
    		                   {
    		                       //return "This is not a valid Discover card number";
    		                       data["status"]=false; 
    		                       data["card_code"] = -1;
    		                       return data;
    		                   }
    		                   break;
    		               default:
    		                   //return "This is not a valid credit card number";
    		                   data["card_type"] ="Invalid";
    		                   data["card_code"] = -1;
    		                   data["status"]=false; 
    		                   return data;
    		                   break;
    		           }
    		           // Here"s where we use the Luhn Algorithm
    		           //credit_card_number = str_replace("-", "", credit_card_number);
    		           credit_card_number = credit_card_number.replace(/-/ig, "").replace(/ /ig, "");
    		           let credit_card_numberArray = credit_card_number.split("");
    		         
    		           let map = [0, 1, 2, 3, 4, 5, 6, 7, 8, 9,0, 2, 4, 6, 8, 1, 3, 5, 7, 9];
    		           let sum = 0;
    		           let last = credit_card_number.length - 1;
    		           for (let i = 0; i <= last; i++)
    		           {
    		               sum += map[parseInt(credit_card_numberArray[last - i]) + (i & 1) * 10];
    		           }

    		           if (sum % 10 != 0)
    		           {
    		               //return "This is not a valid credit card number";
    		               data["status"]=false; 
    		               data["card_code"] = -1;
    		               return data;
    		           }
    		           // If we made it this far the credit card number is in a valid format
    		           data["status"]=true; 
    		           return data;
        		    }

        		    function sanitize(value){
        		        const regex = /(<([^>]+)>)/ig;
        		        const result = value.replace(regex, "");
        		        return result.trim();
        		    }




        		});
        		</script>
        	';
        }

        public function init_form_fields() {

            $this->form_fields = apply_filters( 'wc_POSchapin_form_fields', array(

                'enabled' => array(
                        'title'   => __( 'Habilitar/Deshabilitar', 'wc_POSchapin4WoOCommerce' ),
                        'type'    => 'checkbox',
                        'label'   => __( 'Activar pasarela de pago', 'wc_POSchapin4WoOCommerce' ),
                        'default' => 'yes'
                ),
                'title' => array(
                        'title'       => __( 'Titulo', 'woocommerce' ),
                        'type'        => 'text',
                        'description' => __( 'POSchapin', 'woocommerce' ),
                        'default'     => __( 'POSchapin', 'woocommerce' ),
                        'desc_tip'    => true,
                ),
                'publickey' => array(
                        'title'       => __( 'Public Key', 'wc_POSchapin4WoOCommerce' ),
                        'type'        => 'text',
                        'description' => __( 'POSchapin Public Key.', 'wc_POSchapin4WoOCommerce' ),
                        'default'     => __( '', 'wc_POSchapin4WoOCommerce' ),
                        'desc_tip'    => true,
                ),

                'privatekey' => array(
                        'title'       => __( 'Private Key', 'wc_POSchapin4WoOCommerce' ),
                        'type'        => 'text',
                        'description' => __( 'POSchapin Private Key.', 'wc_POSchapin4WoOCommerce' ),
                        'default'     => __( '', 'wc_POSchapin4WoOCommerce' ),
                        'desc_tip'    => true,
                )
            ) );
        }
       public function admin_options(){
            echo '<h3>'.__('POSchapin Payment Gateway', 'wc_POSchapin4WoOCommerce').'</h3>';
            echo '<p>'.__('POSchapin Payment Gateway').'</p>';
            echo '<table class="form-table">';
            // Generate the HTML For the settings form.
            $this -> generate_settings_html();
            echo '</table>';
        }
        /*public function email_instructions( $order, $sent_to_admin, $plain_text = false ) {
            if ( ! $sent_to_admin && $this->id === $order->payment_method && $order->has_status( 'on-hold' ) ) {
				if ( $this->instructions ){
                    echo wpautop( wptexturize( $this->instructions ) ) . PHP_EOL;
				}
            }
        }*/
        /**
         * Output for the order received page.
         */
        public function thankyou_page() {
           	if ( @$this->instructions ) {
                    echo wpautop( wptexturize( $this->instructions ) );
            }
        }

        function luhn_check($number)
        {
        	$number = str_replace('-', '', $number);
	        $odd = true;
	        $sum = 0;

	        foreach ( array_reverse(str_split($number)) as $num)
	        {
	          $sum += array_sum( str_split(($odd = !$odd) ? $num*2 : $num) );
	        }

	        return (($sum % 10 == 0) and ($sum != 0));
        }

        public function validateDateExpiration($month, $year){
            $expires = \DateTime::createFromFormat('mY', $month.$year);
            $now     = new \DateTime();

            if ($expires < $now) {
                // expired
                return false;
            }
            return true;
        }

        public function validateCreditcard_number($number){
        	//american express
        	if(preg_match('/^3(4|7)\d{2}[ \-]?\d{6}[ \-]?\d{5}/',$number)) {
        		return true;
        	}else{
        		//dinner club
        		if(preg_match('/^3(6|8|9|0)\d{2}[ \-]?\d{6}[ \-]?\d{4}[\d{1}]?/',$number)){
        			return true;
        		}else{
        			//visa
        			if(preg_match('/^4\d{3}[ \-]?\d{4}[ \-]?\d{4}[ \-]?\d{4}/',$number)){
        				return true;
        			}else{
        				//mastercard
        				if(preg_match('/^5\d{3}[ \-]?\d{4}[ \-]?\d{4}[ \-]?\d{4}/',$number)){
        					return true;
        				}else{
        					//discover
        					if(preg_match('/^6011[ \-]?\d{4}[ \-]?\d{4}[ \-]?\d{4}/',$number)){
        						return true;
        					}else{
        						return false;
        					}
        				}
        			}
        		}
        	}
        }

        function process_payment( $order_id ) {
            global $woocommerce;
            $order = new WC_Order( $order_id );

            if ( !$_POST['poschapin_ccname']){
                wc_add_notice( __( 'Nombre del titular de la tarjeta requerido.' ), 'error' );
            }
            if ( !$_POST['poschapin_card_number']){
                wc_add_notice( __( 'Número de tarjeta de crédito requerido.' ), 'error' );
            }
            if ( !$_POST['poschapin_expmonth']){
                wc_add_notice( __( 'Mes de expiración requerido.' ), 'error' );
            }
            if ( !$_POST['poschapin_expyear']){
                wc_add_notice( __( 'Año de tarjeta de crédito requerido.' ), 'error' );
            }
            if ( !$_POST['poschapin_cvv']){
                wc_add_notice( __( 'Código CVV requerido.' ), 'error' );
            }

            if($_POST['poschapin_expmonth'] && $_POST['poschapin_expyear']){
	            $validateDateExpiration = $this->validateDateExpiration($_POST['poschapin_expmonth'],$_POST['poschapin_expyear']);

	            if(!$validateDateExpiration){
	                wc_add_notice( __( 'La fecha de vencimiento de la tarjeta de crédito no es válida.' ), 'error' );
	            }
	         }
            if($_POST['poschapin_card_number']){
                $validateNumberCard = $this->luhn_check($_POST['poschapin_card_number']);
                $validateCreditcard_number = $this->validateCreditcard_number($_POST['poschapin_card_number']);

                if(!$validateNumberCard){
                    wc_add_notice( __( 'El número de la tarjeta de crédito no es válido.' ), 'error' );
                }

                if(!$validateCreditcard_number){
                	wc_add_notice( __( 'Tarjeta valida, pero no aceptada por el procesador de pago' ), 'error' );
                }

             }
            if ( $_POST['poschapin_ccname'] &&  $_POST['poschapin_card_number'] &&  $_POST['poschapin_expmonth'] &&  $_POST['poschapin_expyear']  &&  $_POST['poschapin_cvv'] && $validateDateExpiration && $validateNumberCard){
				$privatekey = (string) trim($this->get_option('privatekey',true));
				date_default_timezone_set('UTC');
				$time = time();

				$orderid = (string) trim($order->id);
				$amount = (string) trim($order->total);

				$string_hash = $orderid . '|' . $amount . '|' . $time . '|' .  $privatekey;
				$hash = md5($string_hash);

				$data['key_public'] = (string) $this->get_option('publickey');
				$data['linkpago_key_public'] = '';
				$data['amount'] = $amount;
				$data['orderid'] = $orderid;
				$data['hash'] = (string) $hash;
				$data['time'] = (string) $time;
				$data['redirect'] = wc_get_checkout_url();//$this->get_return_url( $order );
				$data['email'] = (string) $order->billing_email;
				$data['card_number'] = str_replace(' ', '', $_POST['poschapin_card_number']);
				$data['first_name'] = $_POST['poschapin_ccname'];
				$data['ccexp'] = str_replace(' ', '',$_POST['poschapin_expmonth'])."/".str_replace(' ', '',$_POST['poschapin_expyear']);
				$data['cvv'] = $_POST['poschapin_cvv'];

				$data['address1'] = html_entity_decode($order->billing_address_1, ENT_QUOTES, 'UTF-8');
				$data['city'] = html_entity_decode($order->billing_city, ENT_QUOTES, 'UTF-8');
				$data['state'] = html_entity_decode($order->billing_state, ENT_QUOTES, 'UTF-8');
				$data['zip'] = html_entity_decode($order->billing_postcode, ENT_QUOTES, 'UTF-8');
				$data['country'] = html_entity_decode($order->billing_country, ENT_QUOTES, 'UTF-8');
				$data['phone'] = $order->billing_phone;
				$data['payment_origin'] = 'woocommerce';
				$data['currency_code'] = get_woocommerce_currency();


				
	            // Realizar pago
	            //http://poschapin.com:3000/transaccion/json
	            return array(
	                'result' => 'success',
	                'redirect' => 'https://pos-chapin.appspot.com/transaccion/json?'.http_build_query($data)
	            );
	        }
        }
    }
}
