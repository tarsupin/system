<?hh if(!defined("CONF_PATH")) { die("No direct script access allowed."); } /*

--------------------------------------
------ About the Credits Plugin ------
--------------------------------------

This plugin allows users to acquire, lose, or exchange credits. It can also set up transactions (which direct you to the UniJoule site and provide a shopping experience to make a payment).


-------------------------------------------
------ Example of Purchasing an Item ------
-------------------------------------------
#!
// Prepare Values
$uniID = 1;
$amount = 0.55;
$desc = "Purchasing a rare item.";

// Attempt to Purchase Item (one-click purchase)
$response = Credits::chargeInstant($uniID, $amount, $desc);

if($response && isset($response['transactionID']))
{
	echo "You have successfully purchased the item!";
}

var_dump($response);
##!

------------------------------------------
------ Example of Refunding an Item ------
------------------------------------------
#!
// Prepare Values
$desc = "Refund ID-" . $transactionID;

// Attempt to Purchase Item
$response = Credits::refundInstant($transactionID, $userID, $desc);

if($response && isset($response['transactionID']))
{
	echo "You have successfully refunded this transaction!";
}

var_dump($response);
##!

--------------------------------------------------
------ Example of Sending Credits to a User ------
--------------------------------------------------
#!
// Prepare Values
$senderUniID = 1;
$receipientUniID = 2;
$amount = 0.15;
$desc = "Tip";

// Attempt to Purchase Item
$response = Credits::exchangeInstant($senderUniID, $receipientUniID, $amount, $desc);

if($response && isset($response['sender_balance']))
{
	echo "You have successfully sent money!";
}

var_dump($response);
##!


-------------------------------
------ Methods Available ------
-------------------------------

$url = Credits::transactionURL($uniID, $title, $desc, $api, $returnURL, $imageURL, $minAmount, $maxAmount, $defAmount, $opts);

$response = Credits::chargeInstant($uniID, $amount, $desc);
$response = Credits::refundInstant($transactionID, $userID, $desc);
$response = Credits::exchangeInstant($senderUniID, $receipientUniID, 0.15, "Tip");

*/

abstract class Credits {
	
	
/****** Public Variables ******/
	public static string $transEncKey = "ujtk-uni-shop";		// <str> The encryption key for transactions.
	
	
/****** Direct the user to a transaction URL where a purchase can be made ******/
# Note: if the minimum amount and maximum amount are both set to the same value, the transaction will not provide
# any input values, and instead just allow the user to click to pay that amount.
	public static function transactionURL
	(
		int $uniID				// <int> The UniID of the user to setup the transaction for.
	,	string $title				// <str> The title of the transaction.
	,	string $desc				// <str> The description of what this particular transaction is purchasing.
	,	string $api				// <str> The API that the transaction will submit back to.
	,	string $returnURL			// <str> The URL to return to after the transaction is completed.
	,	bool $haveFee = true		// <bool> TRUE if there is a standard fee for this transaction, FALSE if not.
	,	string $imageURL = ""		// <str> The image to show for the transaction, if wanted.
	,	float $minAmount = 0.00	// <float> The minimum amount that can be spent on the transaction.
	,	float $maxAmount = 0.00	// <float> The maximum amount that can be spent on the transaction (0.00 is unlimited).
	,	float $defAmount = 0.00	// <float> The default amount for this transaction (0.00 is no default).
	,	array <int, float> $opts = array()		// <int:float> An array of default values that you can select between.
	,	array <str, mixed> $custom = array()	// <str:mixed> A string array of custom data to return back.
	): string						// RETURNS <str> A URL for a transaction.
	
	// $url = Credits::transactionURL($uniID, $title, $desc, $api, $returnURL, $haveFee, $imageURL, $minAmount, $maxAmount, $defAmount, $opts, $custom);
	{
		// Prepare the Transaction Packet
		$trans = array(
			"uni_id"		=> (int) $uniID
		,	"title"			=> Sanitize::safeword($title)
		,	"site"			=> SITE_HANDLE
		,	"api"			=> Sanitize::variable($api)
		,	"return_url"	=> Sanitize::url($returnURL)
		);
		
		// Set optional values for the transaction packet
		if($desc) { $trans['desc'] = Sanitize::safeword($desc, "?"); }
		if($minAmount) { $trans['min_amount'] = (float) $minAmount; }
		if($maxAmount) { $trans['max_amount'] = (float) $maxAmount; }
		if($defAmount) { $trans['def_amount'] = (float) $defAmount; }
		if(!$haveFee) { $trans['no_fee'] = 1; }
		
		if($opts)
		{
			$trans['amount_opts'] = array();
			
			foreach($opts as $key => $val)
			{
				$trans['amount_opts'][] = (float) $val;
			}
		}
		
		if($custom) { $trans['custom'] = $custom; }
		
		// Prepare the final data for the URL
		$transData = Encrypt::run(self::$transEncKey, json_encode($trans));
		
		// Return the URL for this transaction
		return URL::unijoule_com() . "/transaction/amount?d=" . urlencode($transData) . "&slg=" . $trans['uni_id'];
	}
	
	
/****** Charges a payment to a user instantly (one-click payment) ******/
	public static function chargeInstant
	(
		int $uniID			// <int> The UniID of the user to charge credits.
	,	float $amount			// <float> Sets how many credits was added to the recipient.
	,	string $desc = ""		// <str> A brief description about the transaction's purpose.
	,	bool $fee = false	// <bool> TRUE to charge a fee for this, FALSE if not.
	): bool					// RETURNS <bool> TRUE on successful charge, FALSE on failure.
	
	// $response = Credits::chargeInstant($uniID, 15.00, "Rare purchase");
	{
		if($amount <= 0)
		{
			Alert::error("Charge Amount", "You must select a positive amount of credits to spend.", 10);
			return false;
		}
		
		// Prepare the Purchase Data
		$packet = array(
			"uni_id"		=> $uniID
		,	"amount"		=> $amount
		,	"desc"			=> $desc
		);
		
		// If you're applying a fee, include that in the packet
		if($fee)
		{
			$packet['apply_fee'] = true;
		}
		
		// Connect to the API
		return (bool) Connect::to("unijoule", "PurchaseAPI", $packet);
	}
	
	
/****** Refunds a payment to a user instantly (one-click refund) ******/
	public static function refundInstant
	(
		int $transactionID		// <int> The ID of the transaction.
	,	int $uniID				// <int> The Uni-Account to return the charge to.
	,	string $desc = ""			// <str> A brief description about the transaction's purpose.
	): mixed						// RETURNS <mixed>
	
	// $response = Credits::refundInstant($transactionID, $userID, "Refund");
	{
		// Prepare the Purchase Data
		$refundData = array(
			"transactionID"	=> $transactionID
		,	"uni_id"		=> $uniID
		,	"desc"			=> $desc
		);
		
		// Conntect to the API
		$siteData = Network::get("unijoule");
		return Connect::call($siteData['site_url'] . "/api/refund", $refundData, $siteData['site_key'], true);
	}
	
	
/****** Instantly sends credits to a user (generally after a confirmation of how much) ******/
# Note: This API will only function for trusted sites.
	public static function exchangeInstant
	(
		int $senderUniID		// <int> The Uni-Account of the user to charge.
	,	int $receipientUniID	// <int> The Uni-Account of the user to receive.
	,	float $amount				// <float> Sets how many credits was added to the recipient.
	,	string $desc = ""			// <str> A brief description about the transaction's purpose.
	): bool						// RETURNS <bool> TRUE on successful exchange, FALSE on failure.
	
	// $response = Credits::exchangeInstant($senderUniID, $receipientUniID, 0.15, "Tip");
	{
		if($amount <= 0)
		{
			Alert::saveError("Charge Amount", "The amount to charge is not a positive value.", 10);
			
			return false;
		}
		
		// Prepare the Purchase Data
		$packet = array(
			"sender_id"		=> $senderUniID
		,	"recipient_id"	=> $receipientUniID
		,	"unijoule"		=> $amount
		,	"desc"			=> $desc
		);
		
		// Conntect to the API
		$response = Connect::to("unijoule", "ExchangeAPI", $packet);
		
		// Get Alerts
		if(Connect::$alert)
		{
			Alert::saveError("Exchange Alert", Connect::$alert);
		}
		
		return $response ? true : false;
	}
	
	
/****** Instantly tip another user ******/
# Note: This API will only function for trusted sites.
	public static function tip
	(
		int $senderUniID		// <int> The Uni-Account of the user to charge.
	,	int $receipientUniID	// <int> The Uni-Account of the user to receive.
	,	string $tipType = "tip"	// <str> The type of tip (tiny, small, tip, big, large, huge, etc)
	): bool						// RETURNS <bool> TRUE on successful exchange, FALSE on failure.
	
	// $response = Credits::tip($senderUniID, $receipientUniID, [$tipType]);
	{
		// Prepare Values
		$amount = 0.25;
		$desc = "Tip";
		
		// Determine the tip info
		switch($tipType)
		{
			case "tiny":
				$amount = 0.08;
				$desc = "Tiny Tip";
				break;
			
			case "small":
				$amount = 0.15;
				$desc = "Small Tip";
				break;
				
			case "big":
			case "large":
				$amount = 0.50;
				$desc = "Big Tip";
				break;
				
			case "huge":
				$amount = 1.00;
				$desc = "Huge Tip";
				break;
				
			case "standard":
			case "average":
			case "tip":
			default:
				$amount = 0.25;
				$desc = "Tip";
				break;
		}
		
		// Run the exchange
		if(self::exchangeInstant($senderUniID, $receipientUniID, $amount, $desc))
		{
			Alert::saveSuccess("Tip Sent", "You have tipped " . number_format($amount, 2) . " UniJoule!");
			
			return true;
		}
		
		return false;
	}
	
}