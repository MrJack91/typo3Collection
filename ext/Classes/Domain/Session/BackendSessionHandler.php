<?php
namespace LPC\LpcSermons\Domain\Session;

class BackendSessionHandler extends SessionHandler {
	/**
	 * @var string
	 */
	protected $mode = "BE";
}