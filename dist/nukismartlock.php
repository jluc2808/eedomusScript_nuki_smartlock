<?php
/** ****************************************************************************
* Nikya eedomus Script Nuki Smartlock
********************************************************************************
* Plugin version : 1.4
* Author : Nikya
* Mod: jluc2808
* Origine : https://github.com/Nikya/eedomusScript_nuki_smartlock
* Fork: https://github.com/jluc2808/eedomusScript_nuki_smartlock
* Nuki Bridge HTTP-API : 1.6

* contents 1.4
* Add door state
* Mod battery average instead of state only
*******************************************************************************/

/** Utile en cours de dev uniquement */
//$eedomusScriptsEmulatorDatasetPath = "eedomusScriptsEmulator_dataset.json";
//require_once ("eedomusScriptsEmulator.php");

/** Initialisation de la rÃ©ponse */
$response = null;

/** Lecture de la fonction */
$function = getArg('function');

/** ****************************************************************************
* Routeur de fonction
*/
switch($function) {
	case 'setup':
		sdk_setup(getArg('nukihost_port'), getArg('token'));
		break;
	case 'register':
		sdk_register(getArg('eedomushost'), getArg('nukiid'), getArg('periph_id_state'), getArg('periph_id_batterycritical'), getArg('periph_id_doorstate'));
		break;
	case 'list':
		sdk_callAPI('list');
		break;
	case 'callback_list':
		sdk_callAPI('callback/list');
		break;
	case 'callback_remove':
		sdk_callAPI('callback/remove', array('id'=> getArg('id')));
		break;
	case 'incomingcall':
		sdk_incomingCall();
		break;
	default:
		$response = '{ "success" : "false", "message" : "Unknown function '.$function.' " }';
}

/** ****************************************************************************
* Enregister les informations pour communiquer avec le Bridge Nuki et affiche
* la liste des serrures connues sur le pont ciblÃ©.
*
* @param $nukihost Host IP du Nuki
* @param $nukiport Port du Nuki
* @param $token Token du Nuki
*/
function sdk_setup($nukihost_port, $token) {
	saveVariable('nukihost_port', $nukihost_port);
	saveVariable('token', $token);

	sdk_callAPI('list');
}

/** ****************************************************************************
* Enregister les informations
* - CÃ´tÃ© eedomus : Les id des 4 pÃ©riphÃ©riques d'informations
* - CÃ´tÃ© Nuki : Enregistre ce script en tant que callBack
*/
function sdk_register($eedomushost, $nukiid, $periph_id_state, $periph_id_batterycritical, $periph_id_doorstate) {
	global $response;

	$eScript = explode( '/' , __FILE__);
	$scriptName = $eScript[count($eScript)-1];

	$callbackUrl = "http://$eedomushost/script/";
	$callbackUrlQuery = array(
		'exec' => $scriptName,
		'function' => 'incomingcall'
	);
	$fullUrl = "$callbackUrl?".http_build_query($callbackUrlQuery);

	saveVariable('nukiid', $nukiid);
	saveVariable("periph_id_state$nukiid", $periph_id_state);
	saveVariable("periph_id_batterycritical$nukiid", $periph_id_batterycritical);
	saveVariable("periph_id_doorstate$nukiid", $periph_id_doorstate);

	sdk_callAPI('callback/add', array('url' => $fullUrl));
}

/** ****************************************************************************
* Fonction appelÃ©e par un callback de la part de Nuki.
* Est rappeler Ã  chaque changement d'Ã©tat.
*/
function sdk_incomingCall() {
	global $response;

	// Le callback est accompagnÃ© d'un Json contenant les nouvelles valeurs
	//		{"nukiId": 11, "state": 1, "stateName": "locked", "batteryCritical": false}
	$backData = sdk_json_decode(sdk_get_input());
	$nukiid = $backData['nukiId'];
	$periph_value_state = $backData['state'];
	// $periph_value_batterycritical = $backData['batteryCritical'];  - removed old value
	$periph_value_batterychargestate = $backData['batteryChargeState'];
	$periph_value_doorstate = $backData['doorsensorState'];

	$periph_id_state = loadVariable("periph_id_state$nukiid");
	$periph_id_batterycritical = loadVariable("periph_id_batterycritical$nukiid");
	$periph_id_doorstate = loadVariable("periph_id_doorstate$nukiid");

	setValue($periph_id_state, $periph_value_state);
	// setValue($periph_id_batterycritical, $periph_value_batterycritical); - removed old value
	setValue($periph_id_batterycritical, $periph_value_batterychargestate);
	setValue($periph_id_doorstate, $periph_value_doorstate);

	$response = ' { ';
	$response.= ' "nukiid" : "'. $nukiid .'", ';
	$response.= ' "periph_id_state" : "'. $periph_id_state .'", ';
	$response.= ' "periph_id_batterycritical" : "'. $periph_id_batterycritical .'", ';
	$response.= ' "periph_value_state" : "'. $periph_value_state .'", ';
//	$response.= ' "periph_value_batterycritical" : "'. $periph_value_batterycritical .'" '; - removed old value
	$response.= ' "periph_value_batterychargestate" : "'. $periph_value_batterychargestate .'" ';
	$response.= ' "periph_value_doorstate" : "'. $periph_value_doorstate .'" ';
	$response.= ' } ';
}

/** ****************************************************************************
* Appeler l'API de Nuki
*
* @param $endpoint Endpoint ciblÃ©
* @param $params Tableau de paramÃ©tre Ã  envoyer sur la cible
*
* @return le rÃ©sulat de l'appel au format Json
*/
function sdk_callAPI($endpoint, $params=array()) {
	global $response;

	$nukihost_port = loadVariable('nukihost_port');
	$token = loadVariable('token');

	if(empty($nukihost_port) or empty($token)) {
		$response = '{ "success" : "false", "message" : "Need an execution of function:setup before !" }';
		return;
	}

	$params['token'] =$token;
	$url = "http://$nukihost_port/$endpoint?".http_build_query($params);

	$response = httpQuery($url);

	return $response;
}

/** ****************************************************************************
* Fin du script, affichage du rÃ©sultat au format XML
*/
sdk_header('text/xml');
echo jsonToXML($response);
?>
