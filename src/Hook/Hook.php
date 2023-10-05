<?php
/**
 * Hook
 */

namespace Orpheus\Hook;

use Exception;

/**
 * The Hook class
 * 
 * This class is used by the core to trigger event with custom callbacks.
 * @deprecated We will remove hooks in a future release
*/
class Hook {
	
	/**
	 * @var array The registered hooks
	 */
	protected static array $hooks = [];//< A global array containing all registered hooks.
	
	/**
	 * @var string The name of this hook
	 */
	protected string $name;
	
	/**
	 * @var array The registered callback for this hook
	 */
	protected array $callbacks = [];
	
	/**
	 * Constructor
	 *
	 * @param string $name The name of the new hook.
	 */
	protected function __construct(string $name) {
		$this->name = $name;
	}
	
	/**
	 * Registers the $callback associating with this hook.
	 * The callback will be called when this hook will be triggered.
	 *
	 * @param callback $callback A callback.
	 * @return static
	 * @throws Exception
	 * @see register()
	 * @see http://php.net/manual/en/language.pseudo-types.php#language.types.callback
	 */
	public function registerHook(callable $callback) {
		if( in_array($callback, $this->callbacks) ) {
			throw new Exception('Callback already registered');
		}
		$this->callbacks[] = $callback;
		return $this;
	}
	
	/**
	 * Trigger this hook calling all associated callbacks.
	 * $params array is passed to the callback as its arguments.
	 * The first parameter, $params[0], is considered as the result of the trigger.
	 * If $params is not an array, its value is assigned to the second value of a new $params array.
	 *
	 * @param array|null $params Params sent to the callback.
	 * @return mixed The first param as result.
	 * @see trigger()
	 */
	public function triggerHook(?array $params = null): mixed {
		$params ??= [null];
		foreach($this->callbacks as $callback) {
			$result = call_user_func_array($callback, $params);
			if( $result !== null ) {
				// Apply only if a non-null value is provided
				$params[0] = $result;
			}
		}
		
		return $params[0] ?? null;
	}
	
	/**
	 * Convert name to slug
	 * 
	 * @param string $name The hook name.
	 * @return string The slug name.
	*/
	protected static function slug(string $name): string {
		return strtolower($name);
	}
	
	/**
	 * Create new Hook
	 * 
	 * @param string $name The new hook name.
	 * @return Hook The new hook.
	*/
	public static function create(string $name): static {
		$name = static::slug($name);
		static::$hooks[$name] = new static($name);
		return self::$hooks[$name];
	}
	
	/**
	 * Register a callback
	 *
	 * @param string $name The hook name.
	 * @param callback $callback The new callback.
	 * @return static
	 *
	 * Add the callback to those of the hook.
	 * @throws Exception
	 */
	public static function register($name, $callback) {
		$name = static::slug($name);
		if( empty(static::$hooks[$name]) ) {
			throw new Exception('No hook with this name');
		}
		return static::$hooks[$name]->registerHook($callback);
	}
	
	/**
	 * Trigger the hook named $name.
	 * e.g. trigger('MyHook', true, $parameter1); trigger('MyHook', $parameter1, $parameter2);
	 * We advise to always provide $silent parameters if you pass additional parameters to the callback
	 *
	 * @param string $name The hook name.
	 * @param boolean $silent Make it silent, no exception thrown. Default value is false.
	 * @return mixed The triggerHook() result, usually the first parameter.
	 */
	public static function trigger(string $name, bool $silent = false): mixed {
		$name = static::slug($name);
		$firstParam = null;
		if( !is_bool($silent) ) {
			$firstParam = $silent;
			$silent = false;// Default
		}
		if( empty(static::$hooks[$name]) ) {
			if( $silent ) {
				return null;
			}
			throw new Exception('No hook with this name');
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
