<?php
/**
 * Hook
 */

namespace Orpheus\Hook;

/**
 * The Hook class
 * 
 * This class is used by the core to trigger event with custom callbacks.
*/
class Hook {
	
	/**
	 * The registered hooks
	 * 
	 * @var array
	 */
	protected static $hooks = array();//< A global array containing all registered hooks.
	
	/**
	 * The name of this hook
	 * 
	 * @var string
	 */
	protected $name;
	
	/**
	 * The registered callback for this hook
	 * 
	 * @var array
	 */
	protected $callbacks = array();
	
	/**
	 * Constructor
	 * 
	 * @param $name The name of the new hook.
	 */
	protected function __construct($name) {
		$this->name = $name;
	}
	
	/**
	 * Register a new callback for this hook.
	 * 
	 * @param callback $callback A callback.
	 * @throws \Exception
	 * @return \Orpheus\Hook\Hook
	 * @see register()
	 * @see http://php.net/manual/en/language.pseudo-types.php#language.types.callback
	 * 
	 * Registers the $callback associating with this hook.
	 * The callback will be called when this hook will be triggered.
	 */
	public function registerHook($callback) {
		if( !is_callable($callback) ) {
			throw new \Exception('Callback not callable');
		}
		if( in_array($callback, $this->callbacks) ) {
			throw new \Exception('Callback already registered');
		}
		$this->callbacks[] = $callback;
		return $this;
	}
	
	/**
	 * Trigger this hook.
	 * 
	 * @param array $params Params sent to the callback.
	 * @return mixed The first param as result.
	 * @see trigger()
	 * 
	 * Trigger this hook calling all associated callbacks.
	 * $params array is passed to the callback as its arguments.
	 * The first parameter, $params[0], is considered as the result of the trigger.
	 * If $params is not an array, its value is assigned to the second value of a new $params array.
	*/
	public function triggerHook($params=NULL) {
		if( !isset($params) ) {
			$params = [
				0 => null,
			];
		} elseif( !is_array($params) ) {
			$params = [
				0 => $params,
			];
		}
		foreach($this->callbacks as $callback) {
			$r	= call_user_func_array($callback, $params);
			if( $r !== NULL ) {
				$params[0] = $r;
			}
		}
		return isset($params[0]) ? $params[0] : null;
	}
	
	/**
	 * Convert name to slug
	 * 
	 * @param string $name The hook name.
	 * @return string The slug name.
	 * 
	 * Extract the slug of a hook name.
	*/
	protected static function slug($name) {
		return strtolower($name);
	}
	
	/**
	 * Create new Hook
	 * 
	 * @param string $name The new hook name.
	 * @return Hook The new hook.
	*/
	public static function create($name) {
		$name = static::slug($name);
		static::$hooks[$name] = new static($name);
		return self::$hooks[$name];
	}
	
	/**
	 * Register a callback
	 * 
	 * @param string $name The hook name.
	 * @param callback $callback The new callback.
	 * @throws \Exception
	 * @return \Orpheus\Hook\Hook
	 * 
	 * Add the callback to those of the hook.
	*/
	public static function register($name, $callback) {
		$name = static::slug($name);
		if( empty(static::$hooks[$name]) ) {
			throw new \Exception('No hook with this name');
		}
		return static::$hooks[$name]->registerHook($callback);
	}
	
	/**
	 * Trigger a hook by name
	 * 
	 * @param string $name The hook name.
	 * @param boolean $silent Make it silent, no exception thrown. Default value is false.
	 * @return The triggerHook() result, usually the first parameter.
	 * 
	 * Trigger the hook named $name.
	 * e.g trigger('MyHook', true, $parameter1); trigger('MyHook', $parameter1, $parameter2);
	 * We advise to always provide $silent parameters if you pass additional parameters to the callback
	*/
	public static function trigger($name, $silent=false) {
		$name = static::slug($name);
		$firstParam = null;
		if( !is_bool($silent) ) {
			$firstParam = $silent;
			$silent = false;// Default
		}
		if( empty(static::$hooks[$name]) ) {
			if( $silent ) { return; }
			throw new \Exception('No hook with this name');
		}
		$params = null;
		if( func_num_args() > 2 ) {
			$params = func_get_args();
			unset($params[0], $params[1]);
			if( isset($firstParam) ) {
				$params[0] = $firstParam;
			}
			$params = array_values($params);
		}
		return static::$hooks[$name]->triggerHook($params);
	}
}
