
<?php
$VALIDATOR_NAME = 'diendd.factory.shardnet.near';

function sendTelegramMessage($message)
{
	$apiToken = "hidden";
	$POOLNAME = "<b>POOL diendd.factory.shardnet.near </b>";
	$website="https://api.telegram.org/bot".$apiToken;
	$params=[
	    'chat_id'=>'@stakewars88888',
	    'parse_mode' => 'html',
	    'text'=>$POOLNAME.chr(10).$message,
	];
	$ch = curl_init($website . '/sendMessage');
	curl_setopt($ch, CURLOPT_HEADER, false);
	curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
	curl_setopt($ch, CURLOPT_POST, 1);
	curl_setopt($ch, CURLOPT_POSTFIELDS, ($params));
	curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
	$result = curl_exec($ch);
	var_dump($result);
	curl_close($ch);
}

function getNearData()
{
	$ch = curl_init();

	curl_setopt($ch, CURLOPT_URL,            "http://65.21.2.142:3030" );
	curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1 );
	curl_setopt($ch, CURLOPT_POST,           1 );
	curl_setopt($ch, CURLOPT_POSTFIELDS,     '{"jsonrpc": "2.0", "method": "validators", "id": "dontcare", "params": [null]}' ); 
	curl_setopt($ch, CURLOPT_HTTPHEADER,     array('Content-Type: application/json')); 

	$result = curl_exec($ch);

	$json_result = json_decode($result);
	curl_close($ch);
	return $json_result;
}

$json_result = getNearData();

if(!$json_result || !isset($json_result->result->current_validators)){
	sendTelegramMessage("SERVER DOWN. ERROR!");
	die();
}

foreach ($json_result->result->current_validators as $key => $validator) {
	if($validator->account_id == $VALIDATOR_NAME)
	{
		$num_expected_blocks = $validator->num_expected_blocks;
		$num_expected_chunks = $validator->num_expected_chunks;
		$num_produced_blocks = $validator->num_produced_blocks;
		$num_produced_chunks = $validator->num_produced_chunks;
		$chunkRate = round($num_produced_chunks/$num_expected_chunks, 2);
		$blockRate = round($num_produced_blocks/$num_expected_blocks, 2);
		
		$METRIC1="<b>Block Rate: </b> ".$blockRate . " <b> Block Produced: </b>".$num_produced_blocks . " <b> Block Expected: </b>".$num_expected_blocks;
		$METRIC2="<b>Chunk Rate: </b>".$chunkRate . " <b> Chunk Produced: </b>".$num_produced_chunks . " <b> Chunk Expected: </b>".$num_expected_chunks;

		
		if($num_expected_chunks > 5 && ($chunkRate <= 0.8 || $blockRate <= 0.8))
		{
			$text = "Your node has produced lower than expected".chr(10).$METRIC1.chr(10).$METRIC2;
			sendTelegramMessage($text);
		}
	}
}

$serverStatus = false;

foreach ($json_result->result->next_validators as $key => $validator) {
	if($validator->account_id == $VALIDATOR_NAME)
	{
		$serverStatus = true;
	}
}

if(!$serverStatus){
	sendTelegramMessage("NEXT KICKOUT!");
}



?>