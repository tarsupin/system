<?hh if(!defined("CONF_PATH")) { die("No direct script access allowed."); } /*

------------------------------------------
------ Important note about Widgets ------
------------------------------------------

All widgets are instantiated and use "processWidget" as their constructor instead of __construct(). See "Widget" plugin for more details.


---------------------------------------------
------ About the ChatWidget Plugin -------
---------------------------------------------

This widget loads a chat with a designated handle.


-------------------------------------------------
------ Example of using the ChatWidget -------
-------------------------------------------------

// Prepare the Chat Widget
$channel = "UniFaction";

// Create a new widget
$chatWidget = new ChatWidget($channel);

// Load the widget into a WidgetLoader container
$chatWidget->load("WidgetPanel");

// If you want to display the widget by itself:
echo $chatWidget->get();


-------------------------------
------ Methods Available ------
-------------------------------

*/


class ChatWidget extends Widget {
	
	
/****** Process the widget's behavior ******/
	public function processWidget
	(
		string $channel		// <str> The name of the chat channel to access.
	): void					// RETURNS <void>
	
	// ChatWidget::processWidget($channel);
	{
		$this->content = '
		<div id="chat-wrap" class="chat-wrap">
			<div class="chat-header" class="chat-header">#' . $channel . ' Chat</div>
			<div id="chat-inner" class="chat-inner"></div>
			<div id="chat-form-wrap">';
			
			if(Me::$loggedIn)
			{
				$this->content .= '
				<form id="chat-form" class="uniform" action="javascript:void(0);" method="post" onsubmit="submitChatForm();">' . Form::prepare("chat-post") . '
				<input id="chat_message" type="text" name="chat_message" value="" placeholder="Chat something . . ." style="width:100%; box-sizing:border-box;" maxlength="200" tabindex="10" autofocus autocomplete="off" />
				<input type="submit" name="submit" value="Post Chat" style="display:none;" />
				<input id="chat_username" type="text" name="chat_username" value="' . Me::$vals['handle'] . '" style="display:none;" />
				</form>';
			}
			
			$this->content .= '
				<input id="chat_channel" type="hidden" name="chat_channel" value="' . $channel . '" />
				<input id="chat_time" type="hidden" name="chat_time" value="0" />
			</div>
		</div>';
	}
}