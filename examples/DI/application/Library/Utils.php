<?php
namespace YesfApp\library;

use DateTime;
use DateTimeInterface;
use Yesf\Plugin;
use Yesf\Logger;

class Utils {
	private $init_time = NULL;
	/**
	 * Constructor
	 * @Autowired time DateTime
	 */
	public function __construct(DateTimeInterface $time) {
		$this->init_time = $time;
	}
	public function getTime() {
		return $this->init_time->format('Y-m-d H:i:s');
	}
}