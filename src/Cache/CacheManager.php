<?php
namespace Hug\Group\Cache;

use Closure;
use Illuminate\Cache\ApcStore;
use Illuminate\Cache\ApcWrapper;
use Illuminate\Cache\ArrayStore;
use Illuminate\Cache\DatabaseStore;
use Illuminate\Cache\FileStore;
use Illuminate\Cache\MemcachedStore;
use Illuminate\Cache\NullStore;
use Illuminate\Cache\RedisStore;
use Illuminate\Cache\Repository;
use Illuminate\Contracts\Cache\Factory as FactoryContract;
use Illuminate\Contracts\Cache\Store;
use InvalidArgumentException;

class CacheManager implements FactoryContract
{
    /**
     * The application instance.
     *
     * @var \Illuminate\Foundation\Application
     */
    protected $oApp;

    /**
     * The array of resolved cache stores.
     *
     * @var array
     */
    protected $aStores = [];

    /**
     * The registered custom driver creators.
     *
     * @var array
     */
    protected $aCustomCreators = [];

    public function __construct($oApp)
    {
        $this->oApp = $oApp;
    }

    /**
     * Get a cache store instance by name.
     *
     * @param  string|null  $sName
     * @return mixed
     */
    public function store($sName = null)
    {
        $sName = $sName ?: $this->getDefaultDriver();

        return $this->aStores[$sName] = $this->get($sName);
    }

    /**
     * Attempt to get the store from the local cache.
     *
     * @param  string  $sName
     * @return \Illuminate\Contracts\Cache\Repository
     */
    protected function get($sName)
    {
        return isset($this->aStores[$sName]) ? $this->aStores[$sName] : $this->resolve($sName);
    }

    /**
     * Resolve the given store.
     *
     * @param  string  $sName
     * @return \Illuminate\Contracts\Cache\Repository
     */
    protected function resolve($sName)
    {
        $aConfig = $this->getConfig($sName);

        if (is_null($aConfig)) {
            throw new InvalidArgumentException("Cache store [{$sName}] is not defined.");
        }

        if (isset($this->aCustomCreators[$aConfig['driver']])) {
            return $this->callCustomCreator($aConfig);
        } else {
            return $this->{"create" . ucfirst($aConfig['driver']) . "Driver"}($aConfig);
        }
    }

    /**
     * Call a custom driver creator.
     *
     * @param  array  $aConfig
     * @return mixed
     */
    protected function callCustomCreator(array $aConfig)
    {
        return $this->customCreators[$aConfig['driver']]($this->oApp, $aConfig);
    }

    /**
     * Create an instance of the APC cache driver.
     *
     * @param  array  $aConfig
     * @return \Illuminate\Cache\ApcStore
     */
    protected function createApcDriver(array $aConfig)
    {
        $sPrefix = $this->getPrefix($aConfig);

        return $this->repository(new ApcStore(new ApcWrapper, $sPrefix));
    }

    /**
     * Create an instance of the array cache driver.
     *
     * @return \Illuminate\Cache\ArrayStore
     */
    protected function createArrayDriver()
    {
        return $this->repository(new ArrayStore);
    }

    /**
     * Create an instance of the file cache driver.
     *
     * @param  array  $aConfig
     * @return \Illuminate\Cache\FileStore
     */
    protected function createFileDriver(array $aConfig)
    {
        return $this->repository(new FileStore($this->oApp->make('files'), $aConfig['path']));
    }

    /**
     * Create an instance of the Memcached cache driver.
     *
     * @param  array  $aConfig
     * @return \Illuminate\Cache\MemcachedStore
     */
    protected function createMemcachedDriver(array $aConfig)
    {
        $sPrefix = $this->getPrefix($aConfig);

        $oMemcached = $this->oApp->make('memcached.connector')->connect($aConfig['servers']);

        return $this->repository(new MemcachedStore($oMemcached, $sPrefix));
    }

    /**
     * Create an instance of the Null cache driver.
     *
     * @return \Illuminate\Cache\NullStore
     */
    protected function createNullDriver()
    {
        return $this->repository(new NullStore);
    }

    /**
     * Create an instance of the Redis cache driver.
     *
     * @param  array  $aConfig
     * @return \Illuminate\Cache\RedisStore
     */
    protected function createRedisDriver(array $aConfig)
    {
        $oRedis = $this->oApp->make('redis');

        $oConnection = array_get($aConfig, 'connection', 'default') ?: 'default';

        return $this->repository(new RedisStore($oRedis, $this->getPrefix($aConfig), $oConnection));
    }

    /**
     * Create an instance of the database cache driver.
     *
     * @param  array  $aConfig
     * @return \Illuminate\Cache\DatabaseStore
     */
    protected function createDatabaseDriver(array $aConfig)
    {
        $aConnection = $this->oApp->make('db')->connection(array_get($aConfig, 'connection'));

        return $this->repository(
            new DatabaseStore(
                $aConnection, $this->oApp->make('encrypter'), $aConfig['table'], $this->getPrefix($aConfig)
            )
        );
    }

    /**
     * Create a new cache repository with the given implementation.
     *
     * @param  \Illuminate\Contracts\Cache\Store  $oStore
     * @return \Illuminate\Cache\Repository
     */
    public function repository(Store $oStore)
    {
        $oRepository = new Repository($oStore);

        if ($this->oApp->has('Illuminate\Contracts\Events\Dispatcher')) {
            $oRepository->setEventDispatcher(
                $this->oApp->make('Illuminate\Contracts\Events\Dispatcher')
            );
        }

        return $oRepository;
    }

    /**
     * Get the cache prefix.
     *
     * @param  array  $config
     * @return string
     */
    protected function getPrefix(array $aConfig)
    {
        return array_get($aConfig, 'prefix') ?: $this->oApp->make('config')->get('cache.prefix');
    }

    /**
     * Get the cache connection configuration.
     *
     * @param  string  $sName
     * @return array
     */
    protected function getConfig($sName)
    {
        return $this->oApp->make('config')->get("cache.stores.{$sName}");
    }

    /**
     * Get the default cache driver name.
     *
     * @return string
     */
    public function getDefaultDriver()
    {
        return $this->oApp->make('config')->get('cache.default');
    }

    /**
     * Register a custom driver creator Closure.
     *
     * @param  string    $sDriver
     * @param  \Closure  $oCallback
     * @return $this
     */
    public function extend($sDriver, Closure $oCallback)
    {
        $this->aCustomCreators[$sDriver] = $oCallback;

        return $this;
    }

    /**
     * Dynamically call the default driver instance.
     *
     * @param  string  $method
     * @param  array   $aParameters
     * @return mixed
     */
    public function __call($sMethod, $aParameters)
    {
        return call_user_func_array(array($this->store(), $sMethod), $aParameters);
    }
}
