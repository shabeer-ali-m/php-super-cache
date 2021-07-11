<?php

/**
 * State.php
 *
 * @copyright      MIT
 * @author         Shabeer Ali M https://github.com/shabeer-ali-m
 * @since          0.0.9
 *
 */

namespace SuperCache;

trait State
{

	/**
     * setState
     * @param [array] $data
     */	
	public function setState($data)
	{
		foreach($data as $k => $v) {
			$this->{$k} = $v;
		}
	}

	/**
     * __set_state
     * @param [array] $data
     */
	public static function __set_state($data)
	{
		$self = new self();
		$self->setState($data);
		return $self;
	}
}