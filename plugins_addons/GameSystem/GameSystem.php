<?php if(!defined("CONF_PATH")) { die("No direct script access allowed."); } /*


// Note: To make sure developing continues normally, do NOT work on this system until after 6:30 PM, and only if
// the rest of the day was productive. Your time needs to be spent primarily on the rest of UniFaction. Games can wait.


-----------------------------------------
------ About the GameSystem Plugin ------
-----------------------------------------

The GameSystem plugin provides a set of tools to build 2D maps and worlds. It is designed to work with Canvas (javascript) and AJAX to produce interactive games.

There are three primary datasets that the database will keep track of:
	
	1. `world_map` tracks the location of things in the world.
		
		* The data is separated into individual maps (for optimization purposes).
		* Everything is saved as an object.
			* Objects can be generic, and just load a specific object type.
			* Objects can be specific (set the object_id value) and load a specific object.
		
	2. `object_data` tracks specific objects and their build instructions.
		
	3. `map_flags` tracks hidden flags throughout each map.
		
		* All flags that are within proximity (and which passes all tests) will activate their effects.
		
		* Flags can perform a large variety of behaviors and functions:
			* Can set the rules of the game (e.g. flag fills entire world, registers keys to events).
			* Register keys to events.
			* Register collisions to events.
			* Change the current difficulty (some areas are tougher than others).
			* Spawn random encounters.
			* Trigger quest events.
			* Set titles of the area or change dialogs (e.g. "You are now in Sherwood Forest")
			* Create or remove powers and abilities (slow heal, haste, poisoned status, etc).
			* Anything else the system can design.
	
	
----------------------------
------ The Map System ------
----------------------------

It's important to know that the map doesn't care about scaling or object sizes. Every game may have different sized tiles and objects. The map only cares about positioning. It is possible to use precision (decimals) with positioning, such as for placing objects askew on top of other objects (such as for placing an object above some tiles).



*/

abstract class GameSystem {
	
	
/****** Place an object onto a map ******/
	public static function placeMapObject
	(
		$mapID				// <int> The ID of the map to place an object instance on.
	,	$x					// <float> The X position to place an object instance on.
	,	$y					// <float> The Y position to place an object instance on.
	,	$object				// <str> The object to place.
	,	$objectType = ""	// <str> The object type to place (if specifying a type).
	,	$objectID = 0		// <int> The ID of the object to place (if placing a specific object).
	)						// RETURNS <bool> TRUE on success, FALSE on failure.
	
	// GameSystem::placeMapObject($mapID, $x, $y, $object, [$objectType], [$objectID]);
	{
		// Prepare Values
		$sliceY = floor($y / 20);
		
		// Add the object to the world
		return Database::query("INSERT INTO `world_map` (map_id, slice_y, x, y, object, object_type, object_id) VALUES (?, ?, ?, ?, ?, ?, ?)", array($mapID, $sliceY, $x, $y, $object, $objectType, $objectID));
	}
	
	
/****** Delete an object from the map ******/
	public static function deleteMapObject
	(
		$instanceID			// <int> The ID of the map instance.
	)						// RETURNS <bool> TRUE on success, FALSE on failure.
	
	// GameSystem::placeObject($mapID, $x, $y, $object, [$objectType], [$objectID]);
	{
		return Database::query("DELETE FROM `world_map` WHERE id=? LIMIT 1", array($instanceID));
	}
	
	
/****** Delete all objects from an area within a map ******/
	public static function deleteMapArea
	(
		$mapID					// <int> The ID of the map to delete objects from.
	,	$x						// <float> The X position to consider the left-most area of deletion.
	,	$y						// <float> The Y position to consider the top-most area of deletion.
	,	$toX					// <float> The X position to consider the right-most area of deletion.
	,	$toY					// <float> The Y position to consider the right-most area of deletion.
	,	$deleteFlags = false	// <bool> TRUE to delete flags in this area as well.
	)							// RETURNS <bool> TRUE on success, FALSE on failure.
	
	// GameSystem::placeObject($mapID, $x, $y, $toX, $toY);
	{
		// Make sure the area is valid
		if($toX < $x) { return false; }
		if($toY < $y) { return false; }
		
		// Prepare Values
		$sliceYMin = floor($y / 20);
		$sliceYMax = floor($toY / 20);
		
		return Database::query("DELETE FROM `world_map` WHERE map_id=? AND slice_y BETWEEN ? AND ? AND x BETWEEN ? AND ? AND y BETWEEN ? AND ? LIMIT 1", array($mapID, $sliceYMin, $sliceYMax, $x, $toX, $y, $toY));
	}
	
	
/****** Delete an entire map (including all objects and flags) ******/
	public static function deleteMap
	(
		$mapID		// <int> The ID of the map to delete objects from.
	)				// RETURNS <bool> TRUE on success, FALSE on failure.
	
	// GameSystem::placeObject($mapID, $x, $y, $toX, $toY);
	{
		// Make sure the area is valid
		if($toX < $x) { return false; }
		if($toY < $y) { return false; }
		
		// Prepare Values
		$sliceYMin = floor($y / 20);
		$sliceYMax = floor($toY / 20);
		
		return Database::query("DELETE FROM `world_map` WHERE map_id=? AND slice_y BETWEEN ? AND ? AND x BETWEEN ? AND ? AND y BETWEEN ? AND ? LIMIT 1", array($mapID, $sliceYMin, $sliceYMax, $x, $toX, $y, $toY));
	}
	
}
