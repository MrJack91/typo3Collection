<?php
namespace LPC\LpcSermons\Domain\Session;

/**
 * Class SessionHandler
 * @url https://lbrmedia.net/codebase/Eintrag/extbase-backend-frontend-session-class/
 * @package LPC\LpcFlyer\Domain\Session
 */
class SessionHandler {
	/**
	 * Keeps TYPO3 mode.
	 * Either 'FE' or 'BE'.
	 *
	 * @var string
	 */
	protected $mode = NULL;

	/**
	 * The User-Object with the session-methods.
	 * Either $GLOBALS['BE_USER'] or $GLOBALS['TSFE']->fe_user.
	 *
	 * @var object
	 */
	protected $sessionObject = NULL;

	/**
	 * The key the data is stored in the session.
	 * @var string
	 */
	protected $storageKey = 'tx_myext';

	/**
	 * Class constructor.
	 * @param null $mode
	 * @throws \Exception
	 */
	public function __construct($mode = NULL) {
		if ($mode) {
			$this->mode = $mode;
		}

		if ($this->mode === NULL || ($this->mode != "BE" && $this->mode != "FE")) {
			throw new \Exception("Typo3-Mode is not defined!", 1388660107);
		}
		$this->sessionObject = ($this->mode == "BE") ? $GLOBALS['BE_USER'] : $GLOBALS['TSFE']->fe_user;
	}

	/**
	 * Setter for storageKey (tx_myext)
	 * @return void
	 */
	public function setStorageKey($storageKey) {
		$this->storageKey = $storageKey;
	}

	/**
	 * Store value in session
	 * @param string $key
	 * @param mixed $value
	 * @return void
	 */
	public function store($key, $value) {
		$sessionData = $this->sessionObject->getSessionData($this->storageKey);
		$sessionData[$key] = $value;
		$this->sessionObject->setAndSaveSessionData($this->storageKey, $sessionData);
	}

	/**
	 * Delete value in session
	 * @param string $key
	 * @return void
	 */
	public function delete($key) {
		$sessionData = $this->sessionObject->getSessionData($this->storageKey);
		unset($sessionData[$key]);
		$this->sessionObject->setAndSaveSessionData($this->storageKey, $sessionData);
	}

	/**
	 * Read value from session
	 * @param string $key
	 * @return mixed
	 */
	public function get($key) {
		$sessionData = $this->sessionObject->getSessionData($this->storageKey);
		return isset($sessionData[$key]) ? $sessionData[$key] : NULL;
	}
}
