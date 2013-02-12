<?php
/**
 * PewPew Event Dispatcher
 *
 * Implements an object for managing event listeners
 * and dispatching events.
 *
 * @author		Simon Ljungberg <simon.ljungberg@nimnim.se>
 * @packages	pewpew
 * @subpackage	event_dispatcher
 */

require_once(dirname(__FILE__) . '/Event.interface.php');
require_once(dirname(__FILE__) . '/Event.class.php');

class pewEventDispatcherException extends Exception {}

class pewEventDispatcher {

	/**
	 * The events
	 *
	 * @var 	array
	 * @access 	protected
	 */
	protected $events		= array();

	/**
	 * The listeners
	 *
	 * @var 	array
	 * @access 	protected
	 */
	protected $listeners	= array();


	/**
	 * Adds an event to the registry
	 *
	 * @access 	public
	 *
	 * @param	pewEventInterface	The event to add
	 *
	 * @return	bool				true if event was added, false if it already existed
	 */
	public function add(pewEventInterface $event) {
		if(array_key_exists($event->getName(), $this->events)) {
			return false;
		}

		$this->events[$event->getName()] = $event;
		return true;
	}

	/**
	 * Create a new event
	 *
	 * @access	public
	 *
	 * @param		object		subject
	 * @param		string		name
	 * @param		array		parameters
	 * @param		string		event type, let's you define your own event class
	 *
	 * @return		pewEvent
	 */
	public function createEvent($subject, $name, $params = array(), $eventType = 'pewEvent') {
		if(!array_key_exists($name, $this->events)) {
			$this->events[$name] = new $eventType($subject, $name, $params);
			return $this->events[$name];
		}
		else {
			throw new pewEventDispatcherException('Event ' . $name . ' already exsists.');
		}
	}

	/**
	 * Removes an event from the registry
	 * Note: this does not remove the event object
	 *
	 * @access	public
	 *
	 * @param	pewEvent|string	Event / name of event to be removed
	 *
	 * @return	bool						true if event was removed, false if no such event existed
	 */
	public function remove($event) {
		if($event instanceof pewEventInterface) {
			$event = $event->getName();
		}
		elseif(!is_string($event)) {
			throw new pewEventDispatcherException('Event to remove must be object of type pewEvent or string.');
		}

		if(array_key_exists($event, $this->events)) {
			unset($this->events[$event]);
			return true;
		}

		return false;
	}

	/**
	 * Get an event object by name
	 *
	 * @access		public
	 *
	 * @param		string
	 *
	 * @return		pewEvent
	 */
	public function getEvent($name) {
		if(array_key_exists($name, $this->events)) {
			return $this->events[$name];
		}
	}

	/**
	 * Add listener to event
	 *
	 * @access		public
	 *
	 * @param		string		event name
	 * @param		callable	array(class, method) or function name
	 *
	 * @return		bool		true if added
	 */
	public function addListener($ename, $callable) {
		if(!is_string($ename)) {
			throw new pewEventDispatcherException('Event name must be string (add listener).');
		}

		if(!is_callable($callable)) {
			throw new pewEventDispatcherException('Event listener is not callable.');
		}

		if(!array_key_exists($ename, $this->events)) {
			throw new pewEventDispatcherException('No event named ' . $ename . ' exists!');
		}

		$this->events[$ename]->addListener($callable);
		return true;
	}

	/**
	 * Notifies all listeners of a given event that the event has triggered
	 *
	 * @access		public
	 *
	 * @param		pewEvent|string		event / event name
	 *
	 * @return		pewEvent			the notified event
	 */
	public function notify($event) {
		$event = $this->strToEvent($event);

		$this->checkEvent($event, 'Invalid event - can\'t notify.');

		if($event->hasRun()) {
			throw new pewEventDispatcherException('Event ' . $event->getName() . ' has already been run. Reset it before running again.');
		}

		$event->notify();
		$event->setHasRun(true);

		return $event;
	}

	/**
	 * Turn a string into an event
	 *
	 * @access		protected
	 *
	 * @param		string				event name
	 *
	 * @return		pewEvent
	 */
	protected function strToEvent($str) {
		if(is_string($str) and array_key_exists($str, $this->events)) {
			return $this->events[$str];
		}
		elseif($str instanceof pewEventInterface) {
			return $str;
		}
		else {
			return NULL;
		}
	}

	/**
	 * Check validatidy of an event
	 * Throws an exception if the event is invalid
	 *
	 * @access 		public
	 *
	 * @param		pewEvent		the event
	 * @param		string			exception message
	 *
	 * @return		bool			true if valid event
	 * @throws		pewEventDispatcherException
	 */
	public function checkEvent($event, $msg = 'Invalid event') {
		if(!($event instanceof pewEventInterface)) {
			throw new pewEventDispatcherException($msg);
		}
	}

	/**
	 * Does an event have listeners?
	 *
	 * @access		public
	 *
	 * @param		pewEvent|string		event / event name
	 *
	 * @return		bool				true if event has listeners, false otherwise
	 */
	public function hasListeners($event) {
		$event = $this->strToEvent($event);

		$this->checkEvent($event, 'Invalid event - can\'t check listeners.');

		return $event->hasListeners();
	}

	/**
	 * Get all listeners for an event
	 *
	 * @access		public
	 *
	 * @param		pewEvent|string		event / event name
	 *
	 * @return		array				listeners
	 */
	public function getListeners($event) {
		$event = $this->strToEvent($event);

		$this->checkEvent($event, 'Invalid event - can\'t get listeners.');

		return $event->getListeners();
	}

	/**
	 * Reset event
	 *
	 * @access		public
	 *
	 * @param		pewEvent|string		event / event name
	 *
	 * @return		void
	 */
	public function reset($event) {
		$event = $this->strToEvent($event);

		$event->reset();
	}
}