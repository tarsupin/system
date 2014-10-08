<?php if(!defined("CONF_PATH")) { die("No direct script access allowed."); }

if(ENVIRONMENT != "local") { exit; }

HHVMConvert::massConversion(true, true, true);