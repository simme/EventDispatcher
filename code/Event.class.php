<?php
/**
 * PewPew Event
 *
 * Implements an event. Objects can 'subscribe'
 * to theese events and get notified when they trigger.
 *
 * @author		Simon Ljungberg <simon.ljungberg@nimnim.se>
 * @packages	pewpew
 * @subpackage	event_dispatcher
 */ 
class pewEvent implements pewEventInterface, ArrayAccess {
	
	/**
	 * Event name
	 *
	 * @var 	string
	 * @access 	protected
	 */
	protected $name		= '';
	
	/**
	 * Has this event been run?
	 *
	 * @var 	boolean
	 * @access 	protected
	 */
	protected $hasRun	= false;
	
	/**
	 * Subject
	 * The one who created the event
	 *
	 * @var 	object
	 * @access 	protected
	 */
	protected $subject;
	
	/**
	 * Parameters to pass to listeners
	 *
	 * @var 	array
	 * @access 	protected
	 */
	protected $params	= array();
	
	/**
	 * Return value
	 *
	 * @var 	mixed
	 * @access 	protected
	 */
	protected $value	= null;
	
	/**
	 * Listeners
	 * The callback methods to be run on notify
	 *
	 * @var		array (callables)
	 * @access	protected
	 */
	protected $listeners = array();
	
	
	/**
	 * Creates a new event
	 *
	 * @access		public
	 *
	 * @param		object		subject
	 * @param		string		name
	 * @param		array		parameters
	 *
	 * @return		void
	 */
	public function __construct($subject, $name, $params = array()) {
		$this->subject 	= $subject;
		$this->name 	= (string)$name;
		$this->params 	= (array)$params;
	}
	
	/**
	 * Return the event name
	 *
	 * @access 		public
	 *
	 * @return		string		name
	 */
	public function getName() {
		return $this->name;
	}
	
	/**
	 * Return the subject
	 *
	 * @access 		public
	 *
	 * @return		object		subject
	 */
	public function getSubject() {
		return $this->subject;
	}
	
	/**
	 * Return the params
	 *
	 * @access 		public
	 *
	 * @return		array		params
	 */
	public function getParams() {
		return $this->params;
	}
	
	/**
	 * Gets the return value
	 *
	 * @access 		public
	 *
	 * @return		mixed		return value
	 */
	public function getReturnValue() {
		return $this->value;
	}
	
	/**
	 * Checks wheter event has ben run or not
	 *
	 * @access		public
	 *
	 * @return		bool		true if event has been run, false otherwise
	 */
	public function hasRun() {
		return $this->hasRun;
	}
	
	/**
	 * Returns a list of listeners
	 *
	 * @access		public
	 *
	 * @return		array		listeners
	 */
	public function getListeners() {
		return $this->listeners;
	}
	
	/**
	 * Set as run
	 *
	 * @access 		public
	 *
	 * @param		bool		has run
	 */
	public function setHasRun() {
		$this->hasRun = true;
	}
	
	/**
	 * Sets the return value
	 *
	 * @access		public
	 *
	 * @param		mixed		value
	 * @param		bool		if true removes anything currently in the return value, else adds another array entry
	 *
	 * @return		void
	 */
	public function setReturnValue($value, $wipe = false) {
		if($wipe) {
			$this->value = array();
		}
	
		if(is_array($value)) {
			foreach($value as $key => $val) {
				$this->value[$key] = $val;
			}
		}
		else {
			$this->value[] = $value;
		}
	}
	
	/**
	 * Add listener to this event
	 *
	 * @access		public
	 *
	 * @param		callable	callback
	 *
	 * @return		void
	 */
	public function addListener($callable) {
	
		if(is_array($callable)) {
			$id = $callable[0] . $callable[1];
		}
		elseif(is_string($callable)) {
			$id = $callable;
		}
		
		$this->listeners[$id] = $callable;
	}
	
	/**
	 * Notify all listeners (run callbacks)
	 *
	 * @access		public
	 *
	 * @return		return value
	 */
	public function notify() {
		$params = array($this);
		$params = array_merge($params, $this->params);
		foreach($this->listeners as $listener) {
			$this->setReturnValue(call_user_func_array($listener, $params));
		}
		
		return $this->value;
	}
	
	/**
	 * Has listeners?
	 *
	 * @access 		public
	 *
	 * @return		bool		trur if the event has listeners, false otherwise
	 */
	public function hasListeners() {
		return !empty($this->listeners);
	}
	
	/**
	 * Reset event, allowing it to run more then once
	 * Basically calls setHasRun(false)
	 *
	 * @access		public
	 *
	 * @return		void
	 */
	public function reset() {
		$this->setHasRun(false);
	}

	/**
	* Returns true if the parameter exists (implements the ArrayAccess interface).
	*
	* @param  		string  	the parameter name
	*
	* @return 		bool 		true if the parameter exists, false otherwise
	*/
	public function offsetExists($name) {
		return array_key_exists($name, $this->params);
	}
	
	/**
	 * Returns a parameter value (implements the ArrayAccess interface).
	 *
	 * @param  		string 		The parameter name
	 *
	 * @return 		mixed  		The parameter value
	 */
	public function offsetGet($name) {
		if (!array_key_exists($name, $this->params)) {
	  		throw new InvalidArgumentException(sprintf('The event "%s" has no "%s" parameter.', $this->name, $name));
		}
	
		return $this->params[$name];
	}
	
	/**
	 * Sets a parameter (implements the ArrayAccess interface).
	 *
	 * @param 		string  	The parameter name
	 * @param 		mixed 		The parameter value 
	 */
	public function offsetSet($name, $value) {
		$this->params[$name] = $value;
	}
	
	/**
	 * Removes a parameter (implements the ArrayAccess interface).
	 *
	 * @param 		string		The parameter name
	 */
	public function offsetUnset($name) {
		unset($this->params[$name]);
	}
	
}