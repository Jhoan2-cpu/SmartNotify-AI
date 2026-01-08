<?php
/**
 * Dependency Injection Container
 *
 * @package SmartNotifyAI\Core
 */

namespace SmartNotifyAI\Core;

// Prevent direct access
if (!defined('ABSPATH')) {
    exit;
}

/**
 * Simple dependency injection container
 */
class Container {

    /**
     * Container bindings
     *
     * @var array
     */
    private $bindings = [];

    /**
     * Singleton instances
     *
     * @var array
     */
    private $instances = [];

    /**
     * Bind a service
     *
     * @param string $abstract Service identifier
     * @param callable $concrete Service factory
     * @param bool $singleton Whether to treat as singleton
     */
    public function bind($abstract, callable $concrete, $singleton = false) {
        $this->bindings[$abstract] = [
            'concrete' => $concrete,
            'singleton' => $singleton,
        ];
    }

    /**
     * Bind a singleton service
     *
     * @param string $abstract Service identifier
     * @param callable $concrete Service factory
     */
    public function singleton($abstract, callable $concrete) {
        $this->bind($abstract, $concrete, true);
    }

    /**
     * Get a service
     *
     * @param string $abstract Service identifier
     * @return mixed
     */
    public function get($abstract) {
        // Return existing instance if singleton
        if (isset($this->instances[$abstract])) {
            return $this->instances[$abstract];
        }

        // Check if binding exists
        if (!isset($this->bindings[$abstract])) {
            throw new \Exception("Service {$abstract} not found in container");
        }

        $binding = $this->bindings[$abstract];
        $instance = call_user_func($binding['concrete'], $this);

        // Store instance if singleton
        if ($binding['singleton']) {
            $this->instances[$abstract] = $instance;
        }

        return $instance;
    }

    /**
     * Check if service exists
     *
     * @param string $abstract Service identifier
     * @return bool
     */
    public function has($abstract) {
        return isset($this->bindings[$abstract]) || isset($this->instances[$abstract]);
    }

    /**
     * Make an instance without storing
     *
     * @param string $abstract Service identifier
     * @return mixed
     */
    public function make($abstract) {
        if (!isset($this->bindings[$abstract])) {
            throw new \Exception("Service {$abstract} not found in container");
        }

        return call_user_func($this->bindings[$abstract]['concrete'], $this);
    }
}
