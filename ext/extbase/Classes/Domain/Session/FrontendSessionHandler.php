<?php
namespace LPC\LpcSermons\Domain\Session;

class FrontendSessionHandler extends SessionHandler {
	/**
	 * @var string
	 */
	protected $mode = "FE";
}