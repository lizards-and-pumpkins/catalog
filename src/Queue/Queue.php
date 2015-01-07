<?php

namespace Brera\Queue;

interface Queue
{
	/**
	 * @return int
	 */
	public function count();

	/**
	 * @param mixed $data
	 * @return null
	 */
	public function add($data);

	/**
	 * @return mixed
	 */
	public function next();
}
