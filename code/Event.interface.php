<?php
/**
 * Interface for PewPew Events.
 * Makes sure events implement the minimum
 * functionality we need.
 *
 * @author		Simon Ljungberg <simon.ljungberg@nimnim.se>
 * @packages	pewpew
 * @subpackage	event_dispatcher
 */
interface pewEventInterface {
	// Gets the event name
	function getName();

	// Gets the subject (class that added this event)
	function getSubject();

	// Gets the parameters that are sent to the callbacks
	function getParams();

	// Gets an array of all return values from callbacks
	function getReturnValue();

	// Has run?
	function hasRun();

	// Get listener list
	function getListeners();

	// Add a listener
	function addListener($callable);

	// Sets the status of the event
	function setHasRun();

	// Notify the listeners, event raised
	function notify();

	// Set the return value
	function setReturnValue($value, $wipe = false);

	// Check listeners
	function hasListeners();

	// Reset run status, allowing an event to be raised more then once
	function reset();
}