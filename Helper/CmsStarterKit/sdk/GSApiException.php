<?php

namespace Gigya\GigyaIM\Helper\CmsStarterKit\sdk;

class GSApiException extends \Exception
{
	private $longMessage;

	private $callId;

	/**
	 * GSApiException constructor.
	 *
	 * @param string $message
	 * @param int    $errorCode
	 * @param string $longMessage
	 * @param string $callId
	 */
	public function __construct($message, $errorCode, $longMessage = null, $callId = null) {
		parent::__construct($message, $errorCode);
		$this->longMessage = $longMessage;
		$this->callId      = $callId;
	}

	/**
	 * @return int
	 */
	public function getErrorCode() {
		return $this->getCode();
	}

	/**
	 * @return null
	 */
	public function getLongMessage() {
		return $this->longMessage;
	}

	/**
	 * @return null
	 */
	public function getCallId() {
		return $this->callId;
	}


}