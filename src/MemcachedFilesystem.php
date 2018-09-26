<?php

namespace duncan3dc\Laravel;

use ErrorException;
use FilesystemIterator;
use Symfony\Component\Finder\Finder;
use Illuminate\Contracts\Filesystem\FileNotFoundException;

class MemcachedFilesystem extends \Illuminate\Filesystem\Filesystem
{
	/**
	 * @var \Memcached $memcached
	 */
	private $memcached;
	
	/**
	 * MemcachedFilesystem constructor.
	 * @param \Memcached $memcached
	 */
	public function __construct($memcached)
	{
		$this->memcached = $memcached;
	}
	
	/**
	 * @param $key
	 * @return string
	 */
	private function getTimeKey($key)
	{
		return $key . "::lastModified";
	}
	
	/**
	 * Determine if a file or directory exists.
	 *
	 * @param  string $key
	 * @return bool
	 */
	public function exists($key)
	{
		$expire = $this->memcached->get($this->getTimeKey($key));
		return !empty($expire);
	}
	
	/**
	 * Get the contents of a file.
	 *
	 * @param  string $key
	 * @param  bool $lock
	 * @return string
	 *
	 * @throws \Illuminate\Contracts\Filesystem\FileNotFoundException
	 */
	public function get($key, $lock = false)
	{
		$data = $this->memcached->get($key);
		
		if (empty($data)) {
			throw new FileNotFoundException("File does not exist at path {$key}");
		}
		return $data;
	}
	
	/**
	 * Write the contents of a file.
	 *
	 * @param  string $key
	 * @param  string $contents
	 * @param  bool $lock
	 * @return int
	 */
	public function put($key, $contents, $lock = false)
	{
		return $this->memcached->set($key, $contents);
	}
	
	/**
	 * Append to a file.
	 *
	 * @param  string $key
	 * @param  string $data
	 * @return int
	 * @throws FileNotFoundException
	 */
	public function append($key, $data)
	{
		if ($this->exists($key)) {
			return $this->put($key, $this->get($key).$data);
		}
		
		return $this->put($key, $data);
	}
	
	/**
	 * Get or set UNIX mode of a file or directory.
	 *
	 * @param  string $key
	 * @param  int $mode
	 * @return mixed
	 */
	public function chmod($key, $mode = null)
	{
		return true;
	}
	
	/**
	 * Delete the file at a given path.
	 *
	 * @param  string|array $keys
	 * @return bool
	 */
	public function delete($keys)
	{
		$keys = is_array($keys) ? $keys : func_get_args();
		return $this->memcached->deleteMulti($keys);
	}
	
	/**
	 * Move a file to a new location.
	 *
	 * @param  string $key
	 * @param  string $target
	 * @return bool
	 * @throws FileNotFoundException
	 */
	public function move($key, $target)
	{
		$this->put($target, $this->get($key));
		$this->delete($key);
		return true;
	}
	
	/**
	 * Copy a file to a new location.
	 *
	 * @param  string $key
	 * @param  string $target
	 * @return bool
	 * @throws FileNotFoundException
	 */
	public function copy($key, $target)
	{
		return $this->put($target, $this->get($key));
	}
	
	/**
	 * Create a hard link to the target file or directory.
	 *
	 * @param  string $target
	 * @param  string $link
	 * @return void
	 * @throws FileNotFoundException
	 */
	public function link($target, $link)
	{
		$this->put($link, $this->get($target));
	}
	
	/**
	 * Get the file's last modification time.
	 *
	 * @param  string $key
	 * @return int
	 */
	public function lastModified($key)
	{
		$time = $this->memcached->get($this->getTimeKey($key));
		if(empty($time)){
			return 0;
		}
		return $time;
	}
	
	/**
	 * Create a directory.
	 *
	 * @param  string $key
	 * @param  int $mode
	 * @param  bool $recursive
	 * @param  bool $force
	 * @return bool
	 */
	public function makeDirectory($key, $mode = 0755, $recursive = false, $force = false)
	{
		return true;
	}
	
	/**
	 * Move a directory.
	 *
	 * @param  string $from
	 * @param  string $to
	 * @param  bool $overwrite
	 * @return bool
	 */
	public function moveDirectory($from, $to, $overwrite = false)
	{
		return true;
	}
	
	/**
	 * Copy a directory from one location to another.
	 *
	 * @param  string $directory
	 * @param  string $destination
	 * @param  int $options
	 * @return bool
	 */
	public function copyDirectory($directory, $destination, $options = null)
	{
		return true;
	}
	
	/**
	 * Recursively delete a directory.
	 *
	 * The directory itself may be optionally preserved.
	 *
	 * @param  string $directory
	 * @param  bool $preserve
	 * @return bool
	 */
	public function deleteDirectory($directory, $preserve = false)
	{
		return true;
	}
	
	/**
	 * Remove all of the directories within a given directory.
	 *
	 * @param  string $directory
	 * @return bool
	 */
	public function deleteDirectories($directory)
	{
		
		return true;
	}
	
	/**
	 * Empty the specified directory of all files and folders.
	 *
	 * @param  string $directory
	 * @return bool
	 */
	public function cleanDirectory($directory)
	{
		return true;
	}
}
